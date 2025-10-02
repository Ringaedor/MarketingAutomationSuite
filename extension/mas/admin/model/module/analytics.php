<?php
namespace Opencart\Admin\Model\Extension\Mas\Module;

class Analytics extends \Opencart\System\Engine\Model {
    /**
     * Adds a new analytics event to the log.
     *
     * @param array $data The event data.
     * @return int The ID of the inserted event.
     */
    public function addEvent(array $data): int {
        $sql = "INSERT INTO `" . DB_PREFIX . "mas_analytics` SET `campaign_id` = '" . (int)($data['campaign_id'] ?? 0) . "', `workflow_id` = '" . (int)($data['workflow_id'] ?? 0) . "', `node_id` = '" . $this->db->escape($data['node_id'] ?? '') . "', `customer_id` = '" . (int)($data['customer_id'] ?? 0) . "', `event_type` = '" . $this->db->escape($data['event_type']) . "', `event_data` = '" . $this->db->escape(json_encode($data['event_data'])) . "', `date_added` = NOW()";
        $this->db->query($sql);
        return $this->db->getLastId();
    }

    /**
     * Retrieves analytics events.
     *
     * @param array $data Filter data.
     * @return array A list of events.
     */
    public function getEvents(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_analytics`";

        // Add filtering if needed in the future

        $sql .= " ORDER BY `date_added` DESC";

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
     * Gets the total number of analytics events.
     *
     * @return int
     */
    public function getTotalEvents(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "mas_analytics`");
        return (int)$query->row['total'];
    }

    /**
     * Returns a summary of events grouped by type.
     *
     * @return array
     */
    public function getEventsSummaryByType(): array {
        $query = $this->db->query("SELECT `event_type`, COUNT(*) AS `total` FROM `" . DB_PREFIX . "mas_analytics` GROUP BY `event_type`");
        return $query->rows;
    }

    /**
     * Returns the total number of events per day for the last 7 days.
     *
     * @return array
     */
    public function getDailyActivity(): array {
        $query = $this->db->query("SELECT DATE(`date_added`) AS `date`, COUNT(*) AS `total` FROM `" . DB_PREFIX . "mas_analytics` WHERE `date_added` >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(`date_added`) ORDER BY `date` ASC");
        return $query->rows;
    }
}