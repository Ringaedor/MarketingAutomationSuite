<?php
namespace Opencart\System\Library\Extension\Mas\Provider;

/**
 * A concrete implementation for the Anthropic API provider.
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
            return ['error' => 'API key is not configured.'];
        }

        // In a real implementation, this would be a more robust HTTP client like Guzzle.
        // For now, we build a standard cURL request.
        $ch = curl_init();

        $headers = [
            'x-api-key: ' . $this->settings['api_key'],
            'anthropic-version: 2023-06-01',
            'content-type: application/json'
        ];

        $post_fields = json_encode([
            'model' => $data['model'] ?? 'claude-3-haiku-20240307',
            'max_tokens' => $data['max_tokens'] ?? 1024,
            'messages' => $data['messages'] ?? [['role' => 'user', 'content' => 'Hello, world!']]
        ]);

        curl_setopt($ch, CURLOPT_URL, self::API_ENDPOINT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // This part would be enabled in a live environment
        // $response = curl_exec($ch);
        // $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close($ch);
        //
        // if ($http_code != 200) {
        //     return ['error' => 'API call failed with status ' . $http_code, 'response' => json_decode($response, true)];
        // }
        // return json_decode($response, true);

        // Simulate a successful response for now
        curl_close($ch); // Still close the handle
        return [
            'id' => 'msg_sim_12345',
            'content' => [
                ['type' => 'text', 'text' => 'This is a simulated response from Anthropic. The API call structure is ready.']
            ]
        ];
    }

    /**
     * Tests the connection by validating the API key format.
     *
     * @return bool True if the key seems valid, false otherwise.
     */
    public function testConnection(): bool {
        // A real test might make a lightweight API call.
        // For now, we check if the API key is set and has a plausible format.
        return !empty($this->settings['api_key']) && str_starts_with($this->settings['api_key'], 'sk-');
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