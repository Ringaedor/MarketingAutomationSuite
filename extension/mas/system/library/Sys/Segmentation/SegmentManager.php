<?php
namespace Opencart\System\Library\Extension\Mas\Sys\Segmentation;

class SegmentManager {
    private object $registry;

    public function __construct(object $registry) {
        $this->registry = $registry;
    }

    /**
     * Returns the definitions for all available segment rules.
     * This is used to build the dynamic rule builder in the admin form.
     *
     * @return array
     */
    public function getRuleDefinitions(): array {
        $this->registry->get('load')->language('extension/mas/module/mas_segment');

        // Each rule needs a label, input type, a list of operators, and optionally, a list of options (for select inputs).
        return [
            'customer_group' => [
                'label'     => $this->registry->get('language')->get('text_rule_customer_group'),
                'input'     => 'select',
                'operators' => [
                    ['id' => 'is', 'text' => $this->registry->get('language')->get('text_operator_is')],
                    ['id' => 'is_not', 'text' => $this->registry->get('language')->get('text_operator_is_not')]
                ],
                'options'   => $this->getCustomerGroups()
            ],
            'total_spent' => [
                'label'     => $this->registry->get('language')->get('text_rule_total_spent'),
                'input'     => 'number',
                'operators' => [
                    ['id' => 'eq', 'text' => $this->registry->get('language')->get('text_operator_eq')],
                    ['id' => 'neq', 'text' => $this->registry->get('language')->get('text_operator_neq')],
                    ['id' => 'gt', 'text' => $this->registry->get('language')->get('text_operator_gt')],
                    ['id' => 'lt', 'text' => $this->registry->get('language')->get('text_operator_lt')]
                ],
                'options'   => []
            ],
            'order_count' => [
                'label'     => $this->registry->get('language')->get('text_rule_order_count'),
                'input'     => 'number',
                'operators' => [
                    ['id' => 'eq', 'text' => $this->registry->get('language')->get('text_operator_eq')],
                    ['id' => 'gt', 'text' => $this->registry->get('language')->get('text_operator_gt')],
                    ['id' => 'lt', 'text' => $this->registry->get('language')->get('text_operator_lt')]
                ],
                'options'   => []
            ]
        ];
    }

    /**
     * Fetches customer groups from the database to be used as options in a rule.
     *
     * @return array
     */
    private function getCustomerGroups(): array {
        $this->registry->get('load')->model('customer/customer_group');
        $customer_groups = $this->registry->get('model_customer_customer_group')->getCustomerGroups();
        $options = [];
        foreach ($customer_groups as $group) {
            $options[] = ['id' => $group['customer_group_id'], 'name' => $group['name']];
        }
        return $options;
    }
}