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

        // Here we could run a SQL script to drop tables,
        // but for now, we leave them for data preservation.
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
}