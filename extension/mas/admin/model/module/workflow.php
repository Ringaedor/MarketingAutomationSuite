<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;

class Workflow extends \Opencart\System\Engine\Model {
    /**
     * Adds a new workflow to the database.
     *
     * @param array $data The workflow data.
     * @return int The ID of the inserted workflow.
     */
    public function addWorkflow(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_workflow` SET `name` = '" . $this->db->escape($data['name']) . "', `status` = '" . (int)$data['status'] . "', `workflow_data` = '" . $this->db->escape(json_encode($data['workflow_data'])) . "', `date_added` = NOW(), `date_modified` = NOW()");
        return $this->db->getLastId();
    }

    /**
     * Edits an existing workflow.
     *
     * @param int $workflow_id The ID of the workflow to edit.
     * @param array $data The new data for the workflow.
     * @return void
     */
    public function editWorkflow(int $workflow_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_workflow` SET `name` = '" . $this->db->escape($data['name']) . "', `status` = '" . (int)$data['status'] . "', `workflow_data` = '" . $this->db->escape(json_encode($data['workflow_data'])) . "', `date_modified` = NOW() WHERE `workflow_id` = '" . (int)$workflow_id . "'");
    }

    /**
     * Deletes a workflow.
     *
     * @param int $workflow_id The ID of the workflow to delete.
     * @return void
     */
    public function deleteWorkflow(int $workflow_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_workflow` WHERE `workflow_id` = '" . (int)$workflow_id . "'");
    }

    /**
     * Retrieves a single workflow by its ID.
     *
     * @param int $workflow_id The ID of the workflow.
     * @return array The workflow data.
     */
    public function getWorkflow(int $workflow_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_workflow` WHERE `workflow_id` = '" . (int)$workflow_id . "'");

        if ($query->num_rows) {
            $query->row['workflow_data'] = json_decode($query->row['workflow_data'], true);
            return $query->row;
        } else {
            return [];
        }
    }

    /**
     * Retrieves all workflows.
     *
     * @param array $data Filter data.
     * @return array A list of workflows.
     */
    public function getWorkflows(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_workflow`";

        $sort_data = [
            'name',
            'status',
            'date_added'
        ];

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
     * Gets the total number of workflows.
     *
     * @return int
     */
    public function getTotalWorkflows(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "mas_workflow`");
        return (int)$query->row['total'];
    }
}