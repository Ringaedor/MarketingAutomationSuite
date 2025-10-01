<?php
namespace Opencart\System\Library\Extension\Mas\Sys;

class WorkflowEngine {
    private object $registry;
    private object $mas; // Main MAS library for accessing providers

    public function __construct(object $registry, object $mas) {
        $this->registry = $registry;
        $this->mas = $mas;
    }

    public function __get(string $key): object {
        return $this->registry->get($key);
    }

    /**
     * Executes a workflow.
     *
     * @param int $workflow_id The ID of the workflow to execute.
     * @param array $context The initial context (e.g., customer_id).
     * @return bool
     */
    public function execute(int $workflow_id, array $context = []): bool {
        $this->load->model('extension/mas/module/workflow');
        $workflow_info = $this->model_extension_mas_module_workflow->getWorkflow($workflow_id);

        if (!$workflow_info || !$workflow_info['status'] || empty($workflow_info['workflow_data']['nodes'])) {
            return false;
        }

        $nodes = $workflow_info['workflow_data']['nodes'];

        foreach ($nodes as $node) {
            if ($node['type'] == 'condition' && $node['condition_type'] == 'segment_check') {
                $customer_id = $context['customer_id'] ?? 0;
                $segment_id = (int)$node['segment_id'];

                $segment_manager = new SegmentManager($this->registry);
                $matching_customers = $segment_manager->getCustomers($segment_id);

                if (!in_array($customer_id, $matching_customers)) {
                    return false; // Customer does not match the segment, stop.
                }
            }

            if ($node['type'] == 'action' && $node['action_type'] == 'send_email') {
                $provider_name = $node['provider'] ?? '';
                $template_id = (int)($node['template_id'] ?? 0);
                $customer_id = (int)($context['customer_id'] ?? 0);

                $provider = $this->mas->getProvider($provider_name);

                if ($provider && $template_id && $customer_id) {
                    $this->load->model('extension/mas/module/template');
                    $this->load->model('account/customer');

                    $template_info = $this->model_extension_mas_module_template->getTemplate($template_id);
                    $customer_info = $this->model_account_customer->getCustomer($customer_id);

                    if ($template_info && $customer_info) {
                        $subject = str_replace(['{firstname}', '{lastname}', '{email}'], [$customer_info['firstname'], $customer_info['lastname'], $customer_info['email']], $template_info['subject']);
                        $body = str_replace(['{firstname}', '{lastname}', '{email}'], [$customer_info['firstname'], $customer_info['lastname'], $customer_info['email']], $template_info['html_content']);

                        $provider->send(['to' => $customer_info['email'], 'subject' => $subject, 'body' => $body]);
                    }
                }
            } elseif ($node['type'] == 'action' && $node['action_type'] == 'generate_text_ai') {
                $provider_name = $node['provider'] ?? '';
                $prompt = $node['prompt'] ?? '';
                $customer_id = (int)($context['customer_id'] ?? 0);

                $provider = $this->mas->getProvider($provider_name);

                if ($provider && $prompt && $customer_id) {
                    $this->load->model('account/customer');
                    $customer_info = $this->model_account_customer->getCustomer($customer_id);

                    if ($customer_info) {
                        $personalized_prompt = str_replace(['{firstname}', '{lastname}', '{email}'], [$customer_info['firstname'], $customer_info['lastname'], $customer_info['email']], $prompt);

                        $response = $provider->complete(['messages' => [['role' => 'user', 'content' => $personalized_prompt]]]);

                        if (isset($response['error'])) {
                             $this->log->write('MAS AI Action Error: ' . json_encode($response));
                        } else {
                             $this->log->write('MAS AI Action Success: ' . json_encode($response));
                        }
                    }
                }
            }
        }

        return true;
    }
}