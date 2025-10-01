<?php
namespace Opencart\System\Library\Extension\Mas;

/**
 * MAS Core Library
 *
 * The central operational hub for the Marketing Automation Suite.
 * It acts as a service locator, providing access to OpenCart's core components
 * and managing the suite's plug-and-play providers.
 */
class Mas {
    private object $registry;
    private array $providers = [];

    /**
     * Constructor.
     *
     * @param object $registry The OpenCart registry object.
     */
    public function __construct(object $registry) {
        $this->registry = $registry;
        $this->loadProviders();
    }

    /**
     * Dynamically loads and registers all active providers from the database.
     * This makes the system plug-and-play.
     *
     * @return void
     */
    private function loadProviders(): void {
        $this->load->model('extension/mas/module/provider');
        $active_providers = $this->model_extension_mas_module_provider->getProviders(['filter_status' => 1]);

        foreach ($active_providers as $provider_data) {
            $provider_type = $provider_data['type']; // e.g., 'smtp', 'anthropic'
            $class_name = ucfirst($provider_type);   // 'Smtp', 'Anthropic'
            $file_path = DIR_EXTENSION . 'mas/system/library/provider/' . $class_name . '.php';

            if (is_file($file_path)) {
                include_once($file_path);

                $full_class_name = 'Opencart\\System\\Library\\Extension\\Mas\\Provider\\' . $class_name;

                if (class_exists($full_class_name)) {
                    $settings = json_decode($provider_data['settings'], true) ?? [];
                    $provider_instance = new $full_class_name($settings);
                    $this->registerProvider($provider_data['name'], $provider_instance);
                }
            }
        }
    }

    /**
     * Magic method to provide access to OpenCart's services (db, config, etc.).
     *
     * @param string $key The service key.
     * @return object|null The requested service from the registry.
     */
    public function __get(string $key): ?object {
        return $this->registry->get($key);
    }

    /**
     * Magic method to allow setting services in the registry.
     *
     * @param string $key The service key.
     * @param mixed $value The service object.
     */
    public function __set(string $key, $value): void {
        $this->registry->set($key, $value);
    }

    /**
     * Registers a new provider in the suite.
     *
     * This allows for a plug-and-play architecture where new
     * functionalities (e.g., email services, AI providers) can be added dynamically.
     *
     * @param string $providerName The unique name of the provider (e.g., 'mailchimp', 'openai').
     * @param object $providerInstance The instance of the provider class.
     * @return void
     */
    public function registerProvider(string $providerName, object $providerInstance): void {
        $this->providers[$providerName] = $providerInstance;
    }

    /**
     * Retrieves a registered provider.
     *
     * @param string $providerName The name of the provider to retrieve.
     * @return object|null The provider instance or null if not found.
     */
    public function getProvider(string $providerName): ?object {
        return $this->providers[$providerName] ?? null;
    }

    /**
     * Returns all registered providers.
     *
     * @return array
     */
    public function getAllProviders(): array {
        return $this->providers;
    }

    /**
     * Executes a segment's rules and returns a list of matching customer IDs.
     *
     * @param int $segment_id The ID of the segment to execute.
     * @return array A list of customer IDs.
     */
    public function executeSegment(int $segment_id): array {
        $this->load->model('extension/mas/module/segment');
        $segment_info = $this->model_extension_mas_module_segment->getSegment($segment_id);

        if (!$segment_info || empty($segment_info['rules'])) {
            return [];
        }

        $sql = "SELECT DISTINCT c.customer_id FROM `" . DB_PREFIX . "customer` c";
        $joins = [];
        $where_clauses = [];

        // Dynamically add joins based on rule types
        foreach ($segment_info['rules'] as $rule) {
            if ($rule['type'] == 'customer_country') {
                $joins['address'] = " LEFT JOIN `" . DB_PREFIX . "address` a ON (c.address_id = a.address_id)";
            }
        }

        $sql .= implode('', $joins);

        foreach ($segment_info['rules'] as $rule) {
            $operator = $this->db->escape($rule['operator']);
            $value = $this->db->escape($rule['value']);

            switch ($rule['type']) {
                case 'customer_total_orders':
                    $where_clauses[] = "(SELECT COUNT(o.order_id) FROM `" . DB_PREFIX . "order` o WHERE o.customer_id = c.customer_id) " . $operator . " '" . (int)$value . "'";
                    break;
                case 'customer_group':
                    $where_clauses[] = "c.customer_group_id " . $operator . " '" . (int)$value . "'";
                    break;
                case 'customer_total_spent':
                    $where_clauses[] = "(SELECT SUM(o.total) FROM `" . DB_PREFIX . "order` o WHERE o.customer_id = c.customer_id AND o.order_status_id > 0) " . $operator . " '" . (float)$value . "'";
                    break;
                case 'customer_country':
                    $where_clauses[] = "a.country_id " . $operator . " '" . (int)$value . "'";
                    break;
                case 'customer_last_login':
                    $where_clauses[] = "DATE(c.last_login) " . $operator . " '" . $value . "'";
                    break;
            }
        }

        if ($where_clauses) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $query = $this->db->query($sql);

        $customer_ids = [];
        foreach ($query->rows as $row) {
            $customer_ids[] = $row['customer_id'];
        }

        return $customer_ids;
    }

    /**
     * Executes a workflow.
     *
     * @param int $workflow_id The ID of the workflow to execute.
     * @param array $context The initial context (e.g., customer_id).
     * @return bool
     */
    public function executeWorkflow(int $workflow_id, array $context = []): bool {
        $this->load->model('extension/mas/module/workflow');
        $workflow_info = $this->model_extension_mas_module_workflow->getWorkflow($workflow_id);

        if (!$workflow_info || !$workflow_info['status'] || empty($workflow_info['workflow_data']['nodes'])) {
            return false;
        }

        // A real engine would be a state machine. This is a simplified linear processor.
        $nodes = $workflow_info['workflow_data']['nodes'];

        foreach ($nodes as $node) {
            // In a real engine, we'd check connections and triggers.
            // Here, we just process conditions and actions sequentially.

            if ($node['type'] == 'condition' && $node['condition_type'] == 'segment_check') {
                $customer_id = $context['customer_id'] ?? 0;
                $segment_id = (int)$node['segment_id'];

                $matching_customers = $this->executeSegment($segment_id);

                if (!in_array($customer_id, $matching_customers)) {
                    // Customer does not match the segment, stop the workflow for this context.
                    return false;
                }
            }

            if ($node['type'] == 'action' && $node['action_type'] == 'send_email') {
                $provider_name = $node['provider'];
                $provider = $this->getProvider($provider_name);

                if ($provider) {
                    // In a real scenario, we would fetch the template and customer email.
                    $email_data = [
                        'to' => 'customer@example.com', // Placeholder
                        'subject' => 'A message from our workflow',
                        'body' => 'You have triggered a workflow action.'
                    ];
                    $provider->send($email_data);
                }
            }
        }

        return true;
    }
}