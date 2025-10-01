<?php
namespace Opencart\System\Library\Extension\Mas\Sys;

class WorkflowEngine {
    private object $registry;
    private object $mas; // Main MAS library for accessing providers

    public function __construct(object $registry, object $mas) {
        $this->registry = $registry;
        $this->mas = $mas;
    }

    public function __get(string $key): object {
        return $this->registry->get($key);
    }

    /**
     * Executes a workflow.
     *
     * @param int $workflow_id The ID of the workflow to execute.
     * @param array $context The initial context (e.g., customer_id).
     * @return bool
     */
    public function execute(int $workflow_id, array $context = []): bool {
        $this->load->model('extension/mas/module/workflow');
        $workflow_info = $this->model_extension_mas_module_workflow->getWorkflow($workflow_id);

        if (!$workflow_info || !$workflow_info['status'] || empty($workflow_info['workflow_data']['nodes'])) {
            return false;
        }

        $nodes = $workflow_info['workflow_data']['nodes'];
        $workflow_state = $context; // Initialize the state for this execution

        foreach ($nodes as $node) {
            if ($node['type'] == 'condition' && $node['condition_type'] == 'segment_check') {
                $customer_id = $workflow_state['customer_id'] ?? 0;
                $segment_id = (int)$node['segment_id'];

                $segment_manager = new SegmentManager($this->registry);
                $matching_customers = $segment_manager->getCustomers($segment_id);

                if (!in_array($customer_id, $matching_customers)) {
                    return false; // Customer does not match the segment, stop.
                }
            } elseif ($node['type'] == 'condition' && $node['condition_type'] == 'consent_check') {
                $customer_id = $workflow_state['customer_id'] ?? 0;
                $consent_code = $node['consent_code'] ?? '';

                if ($customer_id && $consent_code) {
                    $consent_manager = $this->mas->getConsentManager();
                    if (!$consent_manager->hasConsent($customer_id, $consent_code)) {
                        return false; // Customer has not given consent, stop.
                    }
                } else {
                    return false; // Stop if data is missing
                }
            }

            if ($node['type'] == 'action' && $node['action_type'] == 'send_email') {
                $provider_name = $node['provider'] ?? '';
                $template_id = (int)($node['template_id'] ?? 0);
                $customer_id = (int)($workflow_state['customer_id'] ?? 0);

                $provider = $this->mas->getProvider($provider_name);

                if ($provider && $template_id && $customer_id) {
                    $this->load->model('extension/mas/module/template');
                    $this->load->model('account/customer');
                    $this->load->model('extension/mas/module/analytics');

                    $template_info = $this->model_extension_mas_module_template->getTemplate($template_id);
                    $customer_info = $this->model_account_customer->getCustomer($customer_id);

                    if ($template_info && $customer_info) {
                        $subject = str_replace(['{firstname}', '{lastname}', '{email}'], [$customer_info['firstname'], $customer_info['lastname'], $customer_info['email']], $template_info['subject']);
                        $body = str_replace(['{firstname}', '{lastname}', '{email}'], [$customer_info['firstname'], $customer_info['lastname'], $customer_info['email']], $template_info['html_content']);

                        $provider->send(['to' => $customer_info['email'], 'subject' => $subject, 'body' => $body]);

                        $this->model_extension_mas_module_analytics->addEvent([
                            'workflow_id' => $workflow_id,
                            'node_id'     => $node['id'],
                            'customer_id' => $customer_id,
                            'event_type'  => 'email_sent',
                            'event_data'  => ['provider' => $provider_name, 'template_id' => $template_id]
                        ]);
                    }
                }
            } elseif ($node['type'] == 'action' && $node['action_type'] == 'send_ai_email') {
                $ai_provider_name = $node['ai_provider'] ?? '';
                $smtp_provider_name = $node['smtp_provider'] ?? '';
                $prompt = $node['prompt'] ?? '';
                $subject_template = $node['subject'] ?? 'A message for you';
                $customer_id = (int)($workflow_state['customer_id'] ?? 0);

                $ai_provider = $this->mas->getProvider($ai_provider_name);
                $smtp_provider = $this->mas->getProvider($smtp_provider_name);

                if ($ai_provider && $smtp_provider && $prompt && $customer_id) {
                    $this->load->model('account/customer');
                    $this->load->model('extension/mas/module/analytics');
                    $customer_info = $this->model_account_customer->getCustomer($customer_id);

                    if ($customer_info) {
                        $personalized_prompt = str_replace(['{firstname}', '{lastname}', '{email}'], [$customer_info['firstname'], $customer_info['lastname'], $customer_info['email']], $prompt);
                        $final_subject = str_replace(['{firstname}', '{lastname}', '{email}'], [$customer_info['firstname'], $customer_info['lastname'], $customer_info['email']], $subject_template);

                        $ai_response = $ai_provider->complete(['messages' => [['role' => 'user', 'content' => $personalized_prompt]]]);

                        if (!isset($ai_response['error']) && isset($ai_response['content'][0]['text'])) {
                            $email_body = $ai_response['content'][0]['text'];

                            $smtp_provider->send([
                                'to'      => $customer_info['email'],
                                'subject' => $final_subject,
                                'body'    => $email_body
                            ]);

                            $this->model_extension_mas_module_analytics->addEvent([
                                'workflow_id' => $workflow_id,
                                'node_id'     => $node['id'],
                                'customer_id' => $customer_id,
                                'event_type'  => 'ai_email_sent',
                                'event_data'  => ['ai_provider' => $ai_provider_name, 'smtp_provider' => $smtp_provider_name]
                            ]);
                        } else {
                            $this->log->write('MAS AI Email Error: Failed to generate email content. API response: ' . json_encode($ai_response));
                        }
                    }
                }
            }
        }

        return true;
    }
}