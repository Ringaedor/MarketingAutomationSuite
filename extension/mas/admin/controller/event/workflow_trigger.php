<?php
namespace Opencart\Admin\Controller\Extension\Mas\Event;

class WorkflowTrigger extends \Opencart\System\Engine\Controller {
    /**
     * Handles the event after a new customer is added.
     *
     * @param string $route The route of the controller that triggered the event.
     * @param array $args The arguments passed to the controller method.
     * @param int $output The output of the controller method (customer_id).
     * @return void
     */
    public function handleCustomerRegister(string &$route, array &$args, int &$output): void {
        $customer_id = $output;
        $this->triggerWorkflows('customer_register', ['customer_id' => $customer_id]);
    }

    /**
     * Handles the event after an order is confirmed.
     * (Note: This is a simplified trigger point. A real implementation might use a more specific event like `order_history_add` for completed orders).
     *
     * @param string $route
     * @param array $args
     * @param int $output
     * @return void
     */
    public function handleOrderComplete(string &$route, array &$args, int &$output): void {
        $order_id = $output;
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            $this->triggerWorkflows('order_complete', [
                'customer_id' => $order_info['customer_id'],
                'order_id' => $order_id
            ]);
        }
    }

    /**
     * Finds and executes all active workflows for a given trigger.
     *
     * @param string $trigger_code The code of the trigger event.
     * @param array $context The context data to pass to the workflow.
     * @return void
     */
    private function triggerWorkflows(string $trigger_code, array $context): void {
        $this->load->model('extension/mas/module/workflow');
        $workflows = $this->model_extension_mas_module_workflow->getWorkflows(['filter_status' => 1]);

        foreach ($workflows as $workflow) {
            $workflow_data = json_decode($workflow['workflow_data'], true);
            if (isset($workflow_data['trigger']) && $workflow_data['trigger'] === $trigger_code) {
                // Load the MAS library if it's not already loaded
                if (!$this->registry->has('mas')) {
                    $file = DIR_EXTENSION . 'mas/system/library/mas.php';
                    if (is_file($file)) {
                        include_once($file);
                        $this->registry->set('mas', new \Opencart\System\Library\Extension\Mas\Mas($this->registry));
                    }
                }
                // Execute the workflow
                if ($this->registry->has('mas')) {
                    $this->mas->executeWorkflow($workflow['workflow_id'], $context);
                }
            }
        }
    }
}