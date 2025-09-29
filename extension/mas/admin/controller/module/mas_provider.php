<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;
class MasProvider extends \Opencart\System\Engine\Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/mas/module/mas_provider');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_provider');
        $this->getList();
    }

    public function getList(): void {
        $url = '';

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/mas/module/mas_provider', 'user_token=' . $this->session->data['user_token'] . $url)];

        $data['add'] = $this->url->link('extension/mas/module/mas_provider.add', 'user_token=' . $this->session->data['user_token'] . $url);
        $data['delete'] = $this->url->link('extension/mas/module/mas_provider.delete', 'user_token=' . $this->session->data['user_token']);

        $data['providers'] = [];
        $results = $this->model_extension_mas_module_mas_provider->getProviders();

        foreach ($results as $result) {
            $data['providers'][] = [
                'provider_id' => $result['provider_id'],
                'name'        => $result['name'],
                'type'        => $this->language->get('text_type_' . $result['type']),
                'edit'        => $this->url->link('extension/mas/module/mas_provider.edit', 'user_token=' . $this->session->data['user_token'] . '&provider_id=' . $result['provider_id'] . $url)
            ];
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/mas/module/mas_provider_list', $data));
    }

    public function add(): void {
        $this->load->language('extension/mas/module/mas_provider');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_provider');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mas_module_mas_provider->addProvider($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_provider', 'user_token=' . $this->session->data['user_token']));
        }
        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('extension/mas/module/mas_provider');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_provider');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mas_module_mas_provider->editProvider($this->request->get['provider_id'], $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_provider', 'user_token=' . $this->session->data['user_token']));
        }
        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('extension/mas/module/mas_provider');
        $this->load->model('extension/mas/module/mas_provider');
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $provider_id) {
                $this->model_extension_mas_module_mas_provider->deleteProvider($provider_id);
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_provider', 'user_token=' . $this->session->data['user_token']));
        } else {
             $this->session->data['error_warning'] = $this->language->get('error_permission');
             $this->response->redirect($this->url->link('extension/mas/module/mas_provider', 'user_token=' . $this->session->data['user_token']));
        }
    }

    protected function getForm(): void {
        $data['text_form'] = !isset($this->request->get['provider_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_name'] = $this->error['name'] ?? '';
        $data['error_settings'] = $this->error['settings'] ?? [];

        $url = '';
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/mas/module/mas_provider', 'user_token=' . $this->session->data['user_token'] . $url)];

        if (!isset($this->request->get['provider_id'])) {
            $data['action'] = $this->url->link('extension/mas/module/mas_provider.add', 'user_token=' . $this->session->data['user_token'] . $url);
        } else {
            $data['action'] = $this->url->link('extension/mas/module/mas_provider.edit', 'user_token=' . $this->session->data['user_token'] . '&provider_id=' . $this->request->get['provider_id'] . $url);
        }
        $data['cancel'] = $this->url->link('extension/mas/module/mas_provider', 'user_token=' . $this->session->data['user_token'] . $url);

        if (isset($this->request->get['provider_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $provider_info = $this->model_extension_mas_module_mas_provider->getProvider($this->request->get['provider_id']);
        }

        $data['name'] = $this->request->post['name'] ?? ($provider_info['name'] ?? '');
        $data['type'] = $this->request->post['type'] ?? ($provider_info['type'] ?? 'smtp');
        $data['settings'] = $this->request->post['settings'] ?? (isset($provider_info['settings']) ? json_decode($provider_info['settings'], true) : []);

        $data['provider_types'] = [['value' => 'smtp', 'text' => $this->language->get('text_type_smtp')]];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/mas/module/mas_provider_form', $data));
    }

    protected function validateForm(): bool {
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas_provider')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }
        if ($this->request->post['type'] == 'smtp') {
            if (empty($this->request->post['settings']['hostname'])) {
                 $this->error['settings']['hostname'] = $this->language->get('error_smtp_hostname');
            }
            if (empty($this->request->post['settings']['port'])) {
                 $this->error['settings']['port'] = $this->language->get('error_smtp_port');
            }
        }
        return !$this->error;
    }

    protected function validateDelete(): bool {
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas_provider')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}