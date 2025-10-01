<?php
namespace Opencart\System\Library\Extension\Mas\Provider;

/**
 * A concrete and functional implementation for an SMTP provider.
 */
class Smtp {
    private array $settings;
    private object $registry;

    /**
     * Constructor.
     *
     * @param array $settings The provider's settings (hostname, username, password, etc.).
     * @param object $registry The OpenCart registry.
     */
    public function __construct(array $settings = [], object $registry) {
        $this->settings = $settings;
        $this->registry = $registry;
    }

    /**
     * Sends an email using OpenCart's native mail library.
     *
     * @param array $data Data for the email (to, subject, body).
     * @return bool True on success, false on failure.
     */
    public function send(array $data): bool {
        if (empty($this->settings['hostname']) || empty($this->settings['username'])) {
            return false;
        }

        $mail = new \Opencart\System\Library\Mail($this->registry->get('config')->get('mail_engine'));
        $mail->isSmtp();
        $mail->setHostname($this->settings['hostname']);
        $mail->setUsername($this->settings['username']);
        $mail->setPassword(html_entity_decode($this->settings['password'] ?? '', ENT_QUOTES, 'UTF-8'));
        $mail->setPort((int)($this->settings['port'] ?? 587));
        $mail->setFrom($this->registry->get('config')->get('config_email'));
        $mail->setSender($this->registry->get('config')->get('config_name'));

        $mail->setTo($data['to']);
        $mail->setSubject($data['subject']);
        $mail->setText(strip_tags($data['body']));
        $mail->setHtml($data['body']);

        $mail->send();

        return true;
    }

    /**
     * Simulates testing the SMTP connection.
     *
     * @return bool True if connection is successful, false otherwise.
     */
    public function testConnection(): bool {
        // A real test would involve a more complex check, but for now,
        // we ensure essential settings are present.
        return !empty($this->settings['hostname']) && !empty($this->settings['username']);
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