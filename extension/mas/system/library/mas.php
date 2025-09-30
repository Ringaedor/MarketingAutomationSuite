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
        // Future initialization logic can go here (e.g., loading config)
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
}