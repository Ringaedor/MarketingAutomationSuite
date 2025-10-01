<?php
namespace Opencart\System\Library\Extension\Mas;

require_once DIR_EXTENSION . 'mas/system/library/Sys/SegmentManager.php';
require_once DIR_EXTENSION . 'mas/system/library/Sys/WorkflowEngine.php';
require_once DIR_EXTENSION . 'mas/system/library/Sys/ConsentManager.php';

use Opencart\System\Library\Extension\Mas\Sys\SegmentManager;
use Opencart\System\Library\Extension\Mas\Sys\WorkflowEngine;
use Opencart\System\Library\Extension\Mas\Sys\ConsentManager;

/**
 * MAS Core Library (Facade)
 *
 * The central operational hub for the Marketing Automation Suite.
 * It acts as a facade, providing access to specialized engines and managing providers.
 */
class Mas {
    private object $registry;
    private array $providers = [];
    private ?SegmentManager $segmentManager = null;
    private ?WorkflowEngine $workflowEngine = null;
    private ?ConsentManager $consentManager = null;

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
     * Magic method to provide access to OpenCart's services (db, config, etc.).
     */
    public function __get(string $key): ?object {
        return $this->registry->get($key);
    }

    /**
     * Magic method to allow setting services in the registry.
     */
    public function __set(string $key, $value): void {
        $this->registry->set($key, $value);
    }

    /**
     * Dynamically loads and registers all active providers from the database.
     */
    private function loadProviders(): void {
        $this->load->model('extension/mas/module/provider');
        $active_providers = $this->model_extension_mas_module_provider->getProviders(['filter_status' => 1]);

        foreach ($active_providers as $provider_data) {
            $provider_type = $provider_data['type'];
            $class_name = ucfirst($provider_type);
            $file_path = DIR_EXTENSION . 'mas/system/library/provider/' . $class_name . '.php';

            if (is_file($file_path)) {
                include_once($file_path);
                $full_class_name = 'Opencart\\System\\Library\\Extension\\Mas\\Provider\\' . $class_name;
                if (class_exists($full_class_name)) {
                    $settings = json_decode($provider_data['settings'], true) ?? [];
                    $provider_instance = new $full_class_name($settings, $this->registry);
                    $this->registerProvider($provider_data['name'], $provider_instance);
                }
            }
        }
    }

    /**
     * Registers a new provider.
     */
    public function registerProvider(string $providerName, object $providerInstance): void {
        $this->providers[$providerName] = $providerInstance;
    }

    /**
     * Retrieves a registered provider.
     */
    public function getProvider(string $providerName): ?object {
        return $this->providers[$providerName] ?? null;
    }

    /**
     * Returns the Segment Manager instance.
     */
    public function getSegmentManager(): SegmentManager {
        if ($this->segmentManager === null) {
            $this->segmentManager = new SegmentManager($this->registry);
        }
        return $this->segmentManager;
    }

    /**
     * Returns the Workflow Engine instance.
     */
    public function getWorkflowEngine(): WorkflowEngine {
        if ($this->workflowEngine === null) {
            // The engine needs a reference to this class to access providers.
            $this->workflowEngine = new WorkflowEngine($this->registry, $this);
        }
        return $this->workflowEngine;
    }

    /**
     * Returns the Consent Manager instance.
     */
    public function getConsentManager(): ConsentManager {
        if ($this->consentManager === null) {
            $this->consentManager = new ConsentManager($this->registry);
        }
        return $this->consentManager;
    }
}