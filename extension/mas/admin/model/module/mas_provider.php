<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;
class MasProvider extends \Opencart\System\Engine\Model {
    public function addProvider(array $data): int {
        $settings = json_encode($data['settings'] ?? []);
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_provider` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `type` = '" . $this->db->escape((string)$data['type']) . "', `settings` = '" . $this->db->escape($settings) . "'");
        return $this->db->getLastId();
    }

    public function editProvider(int $provider_id, array $data): void {
        $settings = json_encode($data['settings'] ?? []);
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_provider` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `type` = '" . $this->db->escape((string)$data['type']) . "', `settings` = '" . $this->db->escape($settings) . "' WHERE `provider_id` = '" . (int)$provider_id . "'");
    }

    public function deleteProvider(int $provider_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_provider` WHERE `provider_id` = '" . (int)$provider_id . "'");
    }

    public function getProvider(int $provider_id): array {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "mas_provider` WHERE `provider_id` = '" . (int)$provider_id . "'");
        return $query->row;
    }

    public function getProviders(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_provider`";
        $sort_data = ['name', 'type'];

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

    public function getTotalProviders(): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "mas_provider`");
        return (int)$query->row['total'];
    }
}