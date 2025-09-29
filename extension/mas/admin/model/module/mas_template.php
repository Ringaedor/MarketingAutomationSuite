<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;
class MasTemplate extends \Opencart\System\Engine\Model {
    public function addTemplate(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_template` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `subject` = '" . $this->db->escape((string)$data['subject']) . "', `html_content` = '" . $this->db->escape((string)$data['html_content']) . "', `text_content` = '" . $this->db->escape((string)$data['text_content']) . "', `type` = '" . $this->db->escape($data['type']) . "', `date_added` = NOW(), `date_modified` = NOW()");
        return $this->db->getLastId();
    }

    public function editTemplate(int $template_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_template` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `subject` = '" . $this->db->escape((string)$data['subject']) . "', `html_content` = '" . $this->db->escape((string)$data['html_content']) . "', `text_content` = '" . $this->db->escape((string)$data['text_content']) . "', `type` = '" . $this->db->escape($data['type']) . "', `date_modified` = NOW() WHERE `template_id` = '" . (int)$template_id . "'");
    }

    public function deleteTemplate(int $template_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_template` WHERE `template_id` = '" . (int)$template_id . "'");
    }

    public function getTemplate(int $template_id): array {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "mas_template` WHERE `template_id` = '" . (int)$template_id . "'");
        return $query->row;
    }

    public function getTemplates(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_template`";
        $sort_data = ['name', 'type', 'date_added'];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY name";
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

    public function getTotalTemplates(): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "mas_template`");
        return (int)$query->row['total'];
    }
}