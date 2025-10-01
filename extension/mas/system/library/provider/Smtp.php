<?php
namespace Opencart\System\Library\Extension\Mas\Provider;

/**
 * A concrete implementation for an SMTP provider.
 */
class Smtp {
    private array $settings;

    /**
     * Constructor.
     *
     * @param array $settings The provider's settings (hostname, username, password, etc.).
     */
    public function __construct(array $settings = []) {
        $this->settings = $settings;
    }

    /**
     * Simulates sending an email.
     *
     * @param array $data Data for the email (e.g., to, subject, body).
     * @return bool True on success, false on failure.
     */
    public function send(array $data): bool {
        // In a real-world scenario, this method would use the settings
        // to configure a mailer library (like PHPMailer or Symfony Mailer)
        // and send the email.
        // For example:
        // $host = $this->settings['hostname'];
        // $user = $this->settings['username'];
        // ... mailer logic ...
        return true; // Simulate success for now
    }

    /**
     * Simulates testing the SMTP connection.
     *
     * @return bool True if connection is successful, false otherwise.
     */
    public function testConnection(): bool {
        // Here, we would attempt to connect to the SMTP server to validate credentials.
        return true; // Simulate success
    }

    /**
     * Returns the display name of the provider.
     *
     * @return string
     */
    public static function getName(): string {
        return 'Standard SMTP';
    }

    /**
     * Returns the unique type identifier for the provider.
     *
     * @return string
     */
    public static function getType(): string {
        return 'smtp';
    }
}