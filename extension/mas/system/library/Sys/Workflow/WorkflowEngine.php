<?php
namespace Opencart\System\Library\Extension\Mas\Sys\Workflow;

class WorkflowEngine {
    private object $registry;

    public function __construct(object $registry) {
        $this->registry = $registry;
    }

    /**
     * Returns the definitions for all available workflow nodes.
     * This is used to build the visual workflow builder in the admin form.
     *
     * @return array
     */
    public function getNodeDefinitions(): array {
        $this->registry->get('load')->language('extension/mas/module/mas_workflow');

        // Each node has a name, a type (for styling/logic), and the number of inputs/outputs.
        // Configuration options will be added later.
        return [
            'trigger_customer_registered' => [
                'name'    => $this->registry->get('language')->get('text_trigger_customer_registered'),
                'type'    => 'trigger',
                'inputs'  => 0,
                'outputs' => 1,
            ],
            'trigger_order_placed' => [
                'name'    => $this->registry->get('language')->get('text_trigger_order_placed'),
                'type'    => 'trigger',
                'inputs'  => 0,
                'outputs' => 1,
            ],
            'action_send_email' => [
                'name'    => $this->registry->get('language')->get('text_action_send_email'),
                'type'    => 'action',
                'inputs'  => 1,
                'outputs' => 1,
            ],
            'condition_segment_membership' => [
                'name'    => $this->registry->get('language')->get('text_condition_segment'),
                'type'    => 'condition',
                'inputs'  => 1,
                'outputs' => 2, // Path for 'Yes' and 'No'
            ],
            'delay' => [
                'name'    => $this->registry->get('language')->get('text_delay'),
                'type'    => 'delay',
                'inputs'  => 1,
                'outputs' => 1,
            ],
        ];
    }
}