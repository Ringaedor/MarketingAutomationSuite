<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;
class MasSegment extends \Opencart\System\Engine\Model {
    public function addSegment(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_segment` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `date_added` = NOW(), `date_modified` = NOW()");

        $segment_id = $this->db->getLastId();

        if (isset($data['rules'])) {
            foreach ($data['rules'] as $rule) {
                if (empty($rule['type']) || empty($rule['operator']) || !isset($rule['value'])) continue;
                $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_segment_rule` SET `segment_id` = '" . (int)$segment_id . "', `type` = '" . $this->db->escape($rule['type']) . "', `operator` = '" . $this->db->escape($rule['operator']) . "', `value` = '" . $this->db->escape($rule['value']) . "'");
            }
        }

        return $segment_id;
    }

    public function editSegment(int $segment_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_segment` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `date_modified` = NOW() WHERE `segment_id` = '" . (int)$segment_id . "'");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment_rule` WHERE `segment_id` = '" . (int)$segment_id . "'");

        if (isset($data['rules'])) {
            foreach ($data['rules'] as $rule) {
                if (empty($rule['type']) || empty($rule['operator']) || !isset($rule['value'])) continue;
                $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_segment_rule` SET `segment_id` = '" . (int)$segment_id . "', `type` = '" . $this->db->escape($rule['type']) . "', `operator` = '" . $this->db->escape($rule['operator']) . "', `value` = '" . $this->db->escape($rule['value']) . "'");
            }
        }
    }

    public function deleteSegment(int $segment_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment` WHERE `segment_id` = '" . (int)$segment_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment_rule` WHERE `segment_id` = '" . (int)$segment_id . "'");
    }

    public function getSegment(int $segment_id): array {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "mas_segment` WHERE `segment_id` = '" . (int)$segment_id . "'");
        return $query->row;
    }

    public function getSegmentRules(int $segment_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_segment_rule` WHERE `segment_id` = '" . (int)$segment_id . "'");
        return $query->rows;
    }

    public function getSegments(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_segment`";
        $sort_data = ['name', 'date_added'];

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

    public function getTotalSegments(): int {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "mas_segment`");
        return (int)$query->row['total'];
    }
}