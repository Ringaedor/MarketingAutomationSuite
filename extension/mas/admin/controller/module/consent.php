<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;

class Consent extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_consent_list'));

        if (!$this->user->hasPermission('access', 'extension/mas/module/consent')) {
            $this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token']));
        }

        $this->load->model('extension/mas/module/consent');

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_mas_suite_menu'), 'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_consent_list'), 'href' => $this->url->link('extension/mas/module/consent', 'user_token=' . $this->session->data['user_token'])];

        $data['add'] = $this->url->link('extension/mas/module/consent.form', 'user_token=' . $this->session->data['user_token']);
        $data['delete'] = $this->url->link('extension/mas/module/consent.delete', 'user_token=' . $this->session->data['user_token']);

        $data['definitions'] = [];
        $results = $this->model_extension_mas_module_consent->getConsentDefinitions();

        foreach ($results as $result) {
            $data['definitions'][] = [
                'consent_definition_id' => $result['consent_definition_id'],
                'name'                  => $result['name'],
                'code'                  => $result['code'],
                'edit'                  => $this->url->link('extension/mas/consent.form', 'user_token=' . $this->session->data['user_token'] . '&consent_definition_id=' . $result['consent_definition_id'])
            ];
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/consent_list', $data));
    }

    public function delete(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (isset($this->request->post['selected']) && $this->user->hasPermission('modify', 'extension/mas/module/consent')) {
            $this->load->model('extension/mas/module/consent');
            foreach ($this->request->post['selected'] as $consent_definition_id) {
                $this->model_extension_mas_module_consent->deleteConsentDefinition($consent_definition_id);
            }
            $json['success'] = $this->language->get('text_success');
            $json['redirect'] = $this->url->link('extension/mas/module/consent', 'user_token=' . $this->session->data['user_token']);
        } else {
            $json['error'] = $this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function form(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_consent_form'));

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_mas_suite_menu'),'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_consent_list'), 'href' => $this->url->link('extension/mas/module/consent', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_consent_form'), 'href' => $this->url->link('extension/mas/module/consent.form', 'user_token=' . $this->session->data['user_token'] . (isset($this->request->get['consent_definition_id']) ? '&consent_definition_id=' . $this->request->get['consent_definition_id'] : ''))];

        $data['save'] = $this->url->link('extension/mas/module/consent.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('extension/mas/module/consent', 'user_token=' . $this->session->data['user_token']);

        $this->load->model('extension/mas/module/consent');

        if (isset($this->request->get['consent_definition_id'])) {
			$definition_info = $this->model_extension_mas_module_consent->getConsentDefinition((int)$this->request->get['consent_definition_id']);
		}

        $data['consent_definition_id'] = $this->request->get['consent_definition_id'] ?? 0;
        $data['name'] = $definition_info['name'] ?? '';
        $data['code'] = $definition_info['code'] ?? '';
        $data['description'] = $definition_info['description'] ?? '';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/consent_form', $data));
    }

    public function save(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/mas/module/consent')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 255)) {
            $json['error']['name'] = $this->language->get('error_name');
        }

        if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 64)) {
            $json['error']['code'] = $this->language->get('error_code');
        }

        if (!$json) {
            $this->load->model('extension/mas/module/consent');

            if ($this->request->post['consent_definition_id']) {
                $this->model_extension_mas_module_consent->editConsentDefinition((int)$this->request->post['consent_definition_id'], $this->request->post);
            } else {
                $this->model_extension_mas_module_consent->addConsentDefinition($this->request->post);
            }

            $json['success'] = $this->language->get('text_success');
            $json['redirect'] = $this->url->link('extension/mas/module/consent', 'user_token=' . $this->session->data['user_token']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}