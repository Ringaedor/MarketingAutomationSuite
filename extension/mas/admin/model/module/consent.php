<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;

class Consent extends \Opencart\System\Engine\Model {
    /**
     * Adds a new consent definition to the database.
     *
     * @param array $data The consent definition data.
     * @return int The ID of the inserted definition.
     */
    public function addConsentDefinition(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_consent_definition` SET `code` = '" . $this->db->escape($data['code']) . "', `name` = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', `date_added` = NOW()");
        return $this->db->getLastId();
    }

    /**
     * Edits an existing consent definition.
     *
     * @param int $consent_definition_id The ID of the definition to edit.
     * @param array $data The new data for the definition.
     * @return void
     */
    public function editConsentDefinition(int $consent_definition_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_consent_definition` SET `code` = '" . $this->db->escape($data['code']) . "', `name` = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "' WHERE `consent_definition_id` = '" . (int)$consent_definition_id . "'");
    }

    /**
     * Deletes a consent definition.
     *
     * @param int $consent_definition_id The ID of the definition to delete.
     * @return void
     */
    public function deleteConsentDefinition(int $consent_definition_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_consent_definition` WHERE `consent_definition_id` = '" . (int)$consent_definition_id . "'");
        // We should also consider what to do with existing logs for this definition.
        // For now, we leave them for historical tracking.
    }

    /**
     * Retrieves a single consent definition by its ID.
     *
     * @param int $consent_definition_id The ID of the definition.
     * @return array The definition data.
     */
    public function getConsentDefinition(int $consent_definition_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_consent_definition` WHERE `consent_definition_id` = '" . (int)$consent_definition_id . "'");
        return $query->row;
    }

    /**
     * Retrieves all consent definitions.
     *
     * @param array $data Filter data.
     * @return array A list of definitions.
     */
    public function getConsentDefinitions(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_consent_definition` ORDER BY `name` ASC";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Gets the total number of consent definitions.
     *
     * @return int
     */
    public function getTotalConsentDefinitions(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "mas_consent_definition`");
        return (int)$query->row['total'];
    }
}