<?php
namespace Opencart\System\Library\Extension\Mas\Sys;

class ConsentManager {
    private object $registry;

    public function __construct(object $registry) {
        $this->registry = $registry;
    }

    public function __get(string $key): object {
        return $this->registry->get($key);
    }

    /**
     * Grants a specific consent for a customer.
     *
     * @param int    $customer_id The customer's ID.
     * @param string $consent_code The unique code of the consent to grant.
     * @param string $source The source of the consent (e.g., 'registration_form').
     * @return void
     */
    public function grantConsent(int $customer_id, string $consent_code, string $source = 'system'): void {
        $consent_definition = $this->getConsentDefinitionByCode($consent_code);
        if ($consent_definition) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_consent_log` SET `customer_id` = '" . (int)$customer_id . "', `consent_definition_id` = '" . (int)$consent_definition['consent_definition_id'] . "', `status` = 1, `source` = '" . $this->db->escape($source) . "', `date_added` = NOW()");
        }
    }

    /**
     * Revokes a specific consent for a customer.
     *
     * @param int    $customer_id The customer's ID.
     * @param string $consent_code The unique code of the consent to revoke.
     * @param string $source The source of the revocation (e.g., 'profile_update').
     * @return void
     */
    public function revokeConsent(int $customer_id, string $consent_code, string $source = 'system'): void {
        $consent_definition = $this->getConsentDefinitionByCode($consent_code);
        if ($consent_definition) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_consent_log` SET `customer_id` = '" . (int)$customer_id . "', `consent_definition_id` = '" . (int)$consent_definition['consent_definition_id'] . "', `status` = 0, `source` = '" . $this->db->escape($source) . "', `date_added` = NOW()");
        }
    }

    /**
     * Checks if a customer has given a specific consent.
     *
     * @param int    $customer_id The customer's ID.
     * @param string $consent_code The unique code of the consent to check.
     * @return bool True if consent is granted, false otherwise.
     */
    public function hasConsent(int $customer_id, string $consent_code): bool {
        $consent_definition = $this->getConsentDefinitionByCode($consent_code);
        if (!$consent_definition) {
            return false;
        }

        $query = $this->db->query("SELECT `status` FROM `" . DB_PREFIX . "mas_consent_log` WHERE `customer_id` = '" . (int)$customer_id . "' AND `consent_definition_id` = '" . (int)$consent_definition['consent_definition_id'] . "' ORDER BY `date_added` DESC LIMIT 1");

        if ($query->num_rows) {
            return (bool)$query->row['status'];
        }

        return false;
    }

    /**
     * Retrieves a consent definition by its unique code.
     *
     * @param string $code The code to look for.
     * @return array The definition data or an empty array if not found.
     */
    private function getConsentDefinitionByCode(string $code): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_consent_definition` WHERE `code` = '" . $this->db->escape($code) . "'");
        return $query->row;
    }
}