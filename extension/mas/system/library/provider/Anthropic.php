<?php
namespace Opencart\System\Library\Extension\Mas\Provider;

/**
 * A functional implementation for the Anthropic API provider.
 */
class Anthropic {
    private array $settings;
    private object $registry;
    private const API_ENDPOINT = 'https://api.anthropic.com/v1/messages';

    /**
     * Constructor.
     *
     * @param array $settings The provider's settings (e.g., API key).
     * @param object $registry The OpenCart registry.
     */
    public function __construct(array $settings = [], object $registry) {
        $this->settings = $settings;
        $this->registry = $registry;
    }

    /**
     * Sends a request to the Anthropic API to get a text completion.
     *
     * @param array $data The request payload (e.g., model, messages).
     * @return array The API response, decoded from JSON.
     */
    public function complete(array $data): array {
        if (empty($this->settings['api_key'])) {
            return ['error' => 'API key is not configured for Anthropic provider.'];
        }

        $ch = curl_init();

        $headers = [
            'x-api-key: ' . $this->settings['api_key'],
            'anthropic-version: 2023-06-01',
            'content-type: application/json'
        ];

        $post_fields = json_encode([
            'model'      => $data['model'] ?? 'claude-3-haiku-20240307',
            'max_tokens' => $data['max_tokens'] ?? 1024,
            'messages'   => $data['messages'] ?? [['role' => 'user', 'content' => 'Hello, world!']]
        ]);

        curl_setopt($ch, CURLOPT_URL, self::API_ENDPOINT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Add a timeout

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => 'cURL Error: ' . $error];
        }

        if ($http_code != 200) {
            return ['error' => 'API call failed with status ' . $http_code, 'response' => json_decode($response, true)];
        }

        return json_decode($response, true);
    }

    /**
     * Tests the connection by making a lightweight API call.
     *
     * @return bool True if the key is valid, false otherwise.
     */
    public function testConnection(): bool {
        // Make a simple API call with a very short response to validate the key.
        $response = $this->complete([
            'max_tokens' => 1,
            'messages' => [['role' => 'user', 'content' => 'Hi']]
        ]);

        return !isset($response['error']);
    }

    /**
     * Returns the display name of the provider.
     *
     * @return string
     */
    public static function getName(): string {
        return 'Anthropic (Claude)';
    }

    /**
     * Returns the unique type identifier for the provider.
     *
     * @return string
     */
    public static function getType(): string {
        return 'anthropic';
    }
}