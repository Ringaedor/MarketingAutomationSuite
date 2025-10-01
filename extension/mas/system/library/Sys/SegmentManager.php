<?php
namespace Opencart\System\Library\Extension\Mas\Sys;

class SegmentManager {
    private object $registry;

    public function __construct(object $registry) {
        $this->registry = $registry;
    }

    public function __get(string $key): object {
        return $this->registry->get($key);
    }

    /**
     * Executes a segment's rules and returns a list of matching customer IDs.
     *
     * @param int $segment_id The ID of the segment to execute.
     * @return array A list of customer IDs.
     */
    public function getCustomers(int $segment_id): array {
        $this->load->model('extension/mas/module/segment');
        $segment_info = $this->model_extension_mas_module_segment->getSegment($segment_id);

        if (!$segment_info || empty($segment_info['rules'])) {
            return [];
        }

        $sql = "SELECT DISTINCT c.customer_id FROM `" . DB_PREFIX . "customer` c";
        $joins = [];
        $where_clauses = [];

        // Dynamically add joins based on rule types
        foreach ($segment_info['rules'] as $rule) {
            if ($rule['type'] == 'customer_country') {
                $joins['address'] = " LEFT JOIN `" . DB_PREFIX . "address` a ON (c.address_id = a.address_id)";
            }
        }

        $sql .= implode('', $joins);

        foreach ($segment_info['rules'] as $rule) {
            $operator = $this->db->escape($rule['operator']);
            $value = $this->db->escape($rule['value']);

            switch ($rule['type']) {
                case 'customer_total_orders':
                    $where_clauses[] = "(SELECT COUNT(o.order_id) FROM `" . DB_PREFIX . "order` o WHERE o.customer_id = c.customer_id) " . $operator . " '" . (int)$value . "'";
                    break;
                case 'customer_group':
                    $where_clauses[] = "c.customer_group_id " . $operator . " '" . (int)$value . "'";
                    break;
                case 'customer_total_spent':
                    $where_clauses[] = "(SELECT SUM(o.total) FROM `" . DB_PREFIX . "order` o WHERE o.customer_id = c.customer_id AND o.order_status_id > 0) " . $operator . " '" . (float)$value . "'";
                    break;
                case 'customer_country':
                    $where_clauses[] = "a.country_id " . $operator . " '" . (int)$value . "'";
                    break;
                case 'customer_last_login':
                    $where_clauses[] = "DATE(c.last_login) " . $operator . " '" . $value . "'";
                    break;
            }
        }

        if ($where_clauses) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $query = $this->db->query($sql);

        $customer_ids = [];
        foreach ($query->rows as $row) {
            $customer_ids[] = $row['customer_id'];
        }

        return $customer_ids;
    }

    public function getRuleDefinitions(): array {
        // In a real advanced system, this could come from files or a database
        // allowing for dynamic addition of new rule types.
        $this->load->language('extension/mas/module/mas');

        $definitions = [
            'customer_total_orders' => [
                'name' => 'Total Orders',
                'type' => 'text',
                'operators' => ['>', '<', '=', '!=']
            ],
            'customer_total_spent' => [
                'name' => 'Total Spent',
                'type' => 'text',
                'operators' => ['>', '<', '=', '!=']
            ],
            'customer_group' => [
                'name' => 'Customer Group',
                'type' => 'select',
                'operators' => ['=', '!='],
                'source' => 'customer_groups' // This will map to the data we pass from the controller
            ],
            'customer_country' => [
                'name' => 'Country',
                'type' => 'select',
                'operators' => ['=', '!='],
                'source' => 'countries'
            ],
            'customer_last_login' => [
                'name' => 'Date Last Login',
                'type' => 'date', // We can handle this as a text input with a placeholder
                'operators' => ['>', '<', '=', '!=']
            ]
        ];

        return $definitions;
    }
}