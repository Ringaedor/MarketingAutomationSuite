<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;

class Template extends \Opencart\System\Engine\Model {
    /**
     * Adds a new template to the database.
     *
     * @param array $data The template data.
     * @return int The ID of the inserted template.
     */
    public function addTemplate(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_template` SET `name` = '" . $this->db->escape($data['name']) . "', `subject` = '" . $this->db->escape($data['subject']) . "', `html_content` = '" . $this->db->escape($data['html_content']) . "', `date_added` = NOW(), `date_modified` = NOW()");
        return $this->db->getLastId();
    }

    /**
     * Edits an existing template.
     *
     * @param int $template_id The ID of the template to edit.
     * @param array $data The new data for the template.
     * @return void
     */
    public function editTemplate(int $template_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_template` SET `name` = '" . $this->db->escape($data['name']) . "', `subject` = '" . $this->db->escape($data['subject']) . "', `html_content` = '" . $this->db->escape($data['html_content']) . "', `date_modified` = NOW() WHERE `template_id` = '" . (int)$template_id . "'");
    }

    /**
     * Deletes a template.
     *
     * @param int $template_id The ID of the template to delete.
     * @return void
     */
    public function deleteTemplate(int $template_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_template` WHERE `template_id` = '" . (int)$template_id . "'");
    }

    /**
     * Retrieves a single template by its ID.
     *
     * @param int $template_id The ID of the template.
     * @return array The template data.
     */
    public function getTemplate(int $template_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_template` WHERE `template_id` = '" . (int)$template_id . "'");
        return $query->row;
    }

    /**
     * Retrieves all templates.
     *
     * @param array $data Filter data.
     * @return array A list of templates.
     */
    public function getTemplates(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_template`";

        $sort_data = ['name', 'date_added'];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY `name`";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Gets the total number of templates.
     *
     * @return int
     */
    public function getTotalTemplates(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "mas_template`");
        return (int)$query->row['total'];
    }
}