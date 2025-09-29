<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;
class Mas extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_title'));

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

        $data['module_mas_status'] = $this->config->get('module_mas_status');

        // Links will be populated by the menu event

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/module/mas', $data));
    }

    public function save(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_mas', $this->request->post);
            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install(): void {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_mas', ['module_mas_status' => 1]);

        // Add events
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent([
            'code'        => 'mas_menu',
            'description' => 'MAS Add Menu',
            'trigger'     => 'admin/view/common/column_left/before',
            'action'      => 'extension/mas/event/mas.menu',
            'status'      => 1,
            'sort_order'  => 1
        ]);

        // Run SQL
        $sql = file_get_contents(DIR_EXTENSION . 'mas/install.sql');
        if ($sql) {
            $lines = explode(';', $sql);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line) {
                    $this->db->query(str_replace('oc_', DB_PREFIX, $line));
                }
            }
        }
    }

    public function uninstall(): void {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_mas');

        // Delete events
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('mas_menu');

        // Drop tables
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_template`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_provider`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_segment`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_segment_rule`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_workflow`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mas_analytics`");
    }
}