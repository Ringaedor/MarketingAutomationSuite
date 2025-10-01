<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;

class Segment extends \Opencart\System\Engine\Model {
    /**
     * Adds a new segment and its rules.
     *
     * @param array $data The segment data, including rules.
     * @return int The ID of the inserted segment.
     */
    public function addSegment(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_segment` SET `name` = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', `date_added` = NOW(), `date_modified` = NOW()");

        $segment_id = $this->db->getLastId();

        if (isset($data['rules'])) {
            foreach ($data['rules'] as $rule) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_segment_rule` SET `segment_id` = '" . (int)$segment_id . "', `type` = '" . $this->db->escape($rule['type']) . "', `operator` = '" . $this->db->escape($rule['operator']) . "', `value` = '" . $this->db->escape($rule['value']) . "'");
            }
        }

        return $segment_id;
    }

    /**
     * Edits an existing segment and its rules.
     *
     * @param int $segment_id The ID of the segment to edit.
     * @param array $data The new data for the segment.
     * @return void
     */
    public function editSegment(int $segment_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "mas_segment` SET `name` = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', `date_modified` = NOW() WHERE `segment_id` = '" . (int)$segment_id . "'");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment_rule` WHERE `segment_id` = '" . (int)$segment_id . "'");

        if (isset($data['rules'])) {
            foreach ($data['rules'] as $rule) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "mas_segment_rule` SET `segment_id` = '" . (int)$segment_id . "', `type` = '" . $this->db->escape($rule['type']) . "', `operator` = '" . $this->db->escape($rule['operator']) . "', `value` = '" . $this->db->escape($rule['value']) . "'");
            }
        }
    }

    /**
     * Deletes a segment and its associated rules.
     *
     * @param int $segment_id The ID of the segment to delete.
     * @return void
     */
    public function deleteSegment(int $segment_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment` WHERE `segment_id` = '" . (int)$segment_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment_rule` WHERE `segment_id` = '" . (int)$segment_id . "'");
    }

    /**
     * Retrieves a single segment and its rules.
     *
     * @param int $segment_id The ID of the segment.
     * @return array The segment data.
     */
    public function getSegment(int $segment_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_segment` WHERE `segment_id` = '" . (int)$segment_id . "'");

        if ($query->num_rows) {
            $rule_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mas_segment_rule` WHERE `segment_id` = '" . (int)$segment_id . "'");
            $query->row['rules'] = $rule_query->rows;
            return $query->row;
        } else {
            return [];
        }
    }

    /**
     * Retrieves all segments.
     *
     * @param array $data Filter data.
     * @return array A list of segments.
     */
    public function getSegments(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_segment`";
        // Add sorting and pagination if needed in the future
        $sql .= " ORDER BY `name` ASC";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Gets the total number of segments.
     *
     * @return int
     */
    public function getTotalSegments(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "mas_segment`");
        return (int)$query->row['total'];
    }
}