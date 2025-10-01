<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;

class Mas extends \Opencart\System\Engine\Controller {
    private array $error = [];

    /**
     * Entry point for the module configuration page.
     *
     * @return void
     */
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mas/module/mas', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['save'] = $this->url->link('extension/mas/module/mas.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        // Load settings
        $data['module_mas_status'] = $this->config->get('module_mas_status');
        // Placeholder for provider settings
        $data['module_mas_providers'] = $this->config->get('module_mas_providers');

        $this->load->model('extension/mas/module/provider');

        $data['providers'] = [];

        $results = $this->model_extension_mas_module_provider->getProviders();

        foreach ($results as $result) {
            $data['providers'][] = [
                'provider_id' => $result['provider_id'],
                'name'        => $result['name'],
                'type'        => $result['type'],
                'status'      => $result['status'],
                'edit'        => $this->url->link('extension/mas/module/mas.provider_form', 'user_token=' . $this->session->data['user_token'] . '&provider_id=' . $result['provider_id'])
            ];
        }

        $data['add'] = $this->url->link('extension/mas/module/mas.provider_form', 'user_token=' . $this->session->data['user_token']);
        $data['delete'] = $this->url->link('extension/mas/module/mas.delete', 'user_token=' . $this->session->data['user_token']);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/module/mas', $data));
    }

    /**
     * Handles saving the module settings.
     *
     * @return void
     */
    public function save(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas')) {
            $json['error'] = $this->language->get('error_permission');
        }

        // Add validation for provider settings if needed

        if (!$json) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_mas', $this->request->post);
            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Installation logic for the MAS Suite.
     *
     * @return void
     */
    public function install(): void {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_mas', ['module_mas_status' => 1]);

        // Add event for admin menu integration
        $this->load->model('setting/event');
        $event_data = [
            'code'        => 'mas_admin_menu',
            'description' => 'MAS Suite: Add Admin Menu Link',
            'trigger'     => 'admin/view/common/column_left/before',
            'action'      => 'extension/mas/event/menu/addColumnLeftLink',
            'status'      => 1,
            'sort_order'  => 500
        ];
        $this->model_setting_event->addEvent($event_data);

        // Add events for workflow triggers
        $this->model_setting_event->addEvent([
            'code'        => 'mas_workflow_customer_register',
            'description' => 'MAS Suite: Trigger on new customer registration.',
            'trigger'     => 'catalog/model/account/customer/addCustomer/after',
            'action'      => 'extension/mas/event/workflow_trigger/handleCustomerRegister',
            'status'      => 1,
            'sort_order'  => 1
        ]);
        $this->model_setting_event->addEvent([
            'code'        => 'mas_workflow_order_complete',
            'description' => 'MAS Suite: Trigger on order history update (for completed orders).',
            'trigger'     => 'catalog/model/checkout/order/addHistory/after',
            'action'      => 'extension/mas/event/workflow_trigger/handleOrderComplete',
            'status'      => 1,
            'sort_order'  => 1
        ]);


        // Run the installation SQL script to create database tables
        $this->runInstallSql();

        // Add permissions for the administrator user group
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mas/segment');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mas/segment');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mas/workflow');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mas/workflow');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mas/template');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mas/template');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mas/analytics');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mas/analytics');

        // Load and register the core MAS library
        $this->loadLibrary();
    }

    /**
     * Uninstallation logic for the MAS Suite.
     *
     * @return void
     */
    public function uninstall(): void {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_mas');

        // Remove event
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('mas_admin_menu');
        $this->model_setting_event->deleteEventByCode('mas_workflow_customer_register');
        $this->model_setting_event->deleteEventByCode('mas_workflow_order_complete');

        // Remove permissions for the administrator user group
        $this->load->model('user/user_group');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mas/segment');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mas/segment');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mas/workflow');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mas/workflow');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mas/template');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mas/template');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mas/analytics');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mas/analytics');

        // Drop the custom tables
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_provider`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_segment`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_segment_rule`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_workflow`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_template`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_consent_definition`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_consent_log`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_analytics`");
    }

    /**
     * Loads and registers the core MAS library into the OpenCart registry.
     *
     * @return void
     */
    private function loadLibrary(): void {
        $file = DIR_EXTENSION . 'mas/system/library/mas.php';
        if (is_file($file)) {
            include_once($file);
            $this->registry->set('mas', new \Opencart\System\Library\Extension\Mas\Mas($this->registry));
        }
    }

    /**
     * Executes the installation SQL script.
     *
     * @return void
     */
    private function runInstallSql(): void {
        $sql_file = DIR_EXTENSION . 'mas/install.sql';
        if (is_file($sql_file)) {
            $sql = file_get_contents($sql_file);
            $lines = explode(';', $sql);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line) {
                    // Replace the placeholder table prefix
                    $this->db->query(str_replace('`oc_', '`' . DB_PREFIX, $line));
                }
            }
        }
    }

    public function provider_form(): void {
		$this->load->language('extension/mas/module/mas');

		$this->document->setTitle($this->language->get('heading_provider_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];

        $data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/mas/module/mas', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_provider_title'),
			'href' => $this->url->link('extension/mas/module/mas.provider_form', 'user_token=' . $this->session->data['user_token'] . (isset($this->request->get['provider_id']) ? '&provider_id=' . $this->request->get['provider_id'] : ''))
		];

		$data['save'] = $this->url->link('extension/mas/module/mas.saveProvider', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('extension/mas/module/mas', 'user_token=' . $this->session->data['user_token']);

		$this->load->model('extension/mas/module/provider');

		if (isset($this->request->get['provider_id'])) {
			$provider_info = $this->model_extension_mas_module_provider->getProvider((int)$this->request->get['provider_id']);
		}

		$data['provider_id'] = $this->request->get['provider_id'] ?? 0;
		$data['name'] = $provider_info['name'] ?? '';
		$data['type'] = $provider_info['type'] ?? '';
		$data['status'] = $provider_info['status'] ?? 1;
		$data['settings'] = $provider_info['settings'] ?? [];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/mas/module/provider_form', $data));
	}

    public function saveProvider(): void {
        $this->load->language('extension/mas/module/mas');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $json['error']['name'] = $this->language->get('error_name');
        }

        if (empty($this->request->post['type'])) {
            $json['error']['type'] = $this->language->get('error_type');
        }

        if (!$json) {
            $this->load->model('extension/mas/module/provider');

            if ($this->request->post['provider_id']) {
                $this->model_extension_mas_module_provider->editProvider((int)$this->request->post['provider_id'], $this->request->post);
            } else {
                $this->model_extension_mas_module_provider->addProvider($this->request->post);
            }

            $json['success'] = $this->language->get('text_success');
            $json['redirect'] = $this->url->link('extension/mas/module/mas', 'user_token=' . $this->session->data['user_token']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function delete(): void {
		$this->load->language('extension/mas/module/mas');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'extension/mas/module/mas')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('extension/mas/module/provider');

			foreach ($selected as $provider_id) {
				$this->model_extension_mas_module_provider->deleteProvider((int)$provider_id);
			}

			$json['success'] = $this->language->get('text_success');
            $json['redirect'] = $this->url->link('extension/mas/module/mas', 'user_token=' . $this->session->data['user_token']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}