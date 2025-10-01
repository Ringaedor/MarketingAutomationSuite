<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;

class Provider extends \Opencart\System\Engine\Model {
    /**
     * Adds a new provider to the database.
     *
     * @param array $data The provider data.
     * @return int The ID of the inserted provider.
     */
    public function addProvider(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_provider` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `type` = '" . $this->db->escape((string)$data['type']) . "', `settings` = '" . $this->db->escape(json_encode($data['settings'])) . "', `status` = '" . (int)$data['status'] . "', `date_added` = NOW(), `date_modified` = NOW()");
        return $this->db->getLastId();
    }

    /**
     * Edits an existing provider.
     *
     * @param int $provider_id The ID of the provider to edit.
     * @param array $data The new data for the provider.
     * @return void
     */
    public function editProvider(int $provider_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_provider` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `type` = '" . $this->db->escape((string)$data['type']) . "', `settings` = '" . $this->db->escape(json_encode($data['settings'])) . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW() WHERE `provider_id` = '" . (int)$provider_id . "'");
    }

    /**
     * Deletes a provider.
     *
     * @param int $provider_id The ID of the provider to delete.
     * @return void
     */
    public function deleteProvider(int $provider_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_provider` WHERE `provider_id` = '" . (int)$provider_id . "'");
    }

    /**
     * Retrieves a single provider by its ID.
     *
     * @param int $provider_id The ID of the provider.
     * @return array The provider data.
     */
    public function getProvider(int $provider_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_provider` WHERE `provider_id` = '" . (int)$provider_id . "'");

        if ($query->num_rows) {
            $query->row['settings'] = json_decode($query->row['settings'], true);
            return $query->row;
        } else {
            return [];
        }
    }

    /**
     * Retrieves all providers.
     *
     * @param array $data Filter data.
     * @return array A list of providers.
     */
    public function getProviders(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_provider`";

        $where_data = [];

        if (!empty($data['filter_name'])) {
            $where_data[] = "`name` LIKE '" . $this->db->escape((string)$data['filter_name'] . '%') . "'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $where_data[] = "`status` = '" . (int)$data['filter_status'] . "'";
        }

        if ($where_data) {
            $sql .= " WHERE " . implode(" AND ", $where_data);
        }

        $sort_data = [
            'name',
            'type',
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
     * Gets the total number of providers.
     *
     * @return int
     */
    public function getTotalProviders(array $data = []): int {
        $sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "mas_provider`";

        $where_data = [];

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $where_data[] = "`status` = '" . (int)$data['filter_status'] . "'";
        }

        if ($where_data) {
            $sql .= " WHERE " . implode(" AND ", $where_data);
        }

        $query = $this->db->query($sql);
        return (int)$query->row['total'];
    }
}