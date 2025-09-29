<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;
class MasWorkflow extends \Opencart\System\Engine\Model {
    public function addWorkflow(array $data): int {
        $workflow_data = html_entity_decode($data['workflow_data'], ENT_QUOTES, 'UTF-8');
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_workflow` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `status` = '" . (int)$data['status'] . "', `workflow_data` = '" . $this->db->escape($workflow_data) . "', `date_added` = NOW(), `date_modified` = NOW()");
        return $this->db->getLastId();
    }

    public function editWorkflow(int $workflow_id, array $data): void {
        $workflow_data = html_entity_decode($data['workflow_data'], ENT_QUOTES, 'UTF-8');
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_workflow` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `status` = '" . (int)$data['status'] . "', `workflow_data` = '" . $this->db->escape($workflow_data) . "', `date_modified` = NOW() WHERE `workflow_id` = '" . (int)$workflow_id . "'");
    }

    public function deleteWorkflow(int $workflow_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_workflow` WHERE `workflow_id` = '" . (int)$workflow_id . "'");
        // We might want to delete analytics data as well, but for now, we leave it for historical analysis.
    }

    public function getWorkflow(int $workflow_id): array {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "mas_workflow` WHERE `workflow_id` = '" . (int)$workflow_id . "'");
        return $query->row;
    }

    public function getWorkflows(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_workflow`";
        $sort_data = ['name', 'status', 'date_added'];

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
            if ($data['start'] < 0) $data['start'] = 0;
            if ($data['limit'] < 1) $data['limit'] = 20;
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getTotalWorkflows(): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "mas_workflow`");
        return (int)$query->row['total'];
    }
}