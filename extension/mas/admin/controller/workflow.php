<?php
namespace Opencart\Admin\Controller\Extension\Mas;

class Workflow extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_workflow_list'));

        $this->load->model('extension/mas/module/workflow');

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
			'text' => $this->language->get('text_mas_suite_menu'),
			'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])
		];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_workflow_list'),
            'href' => $this->url->link('extension/mas/workflow', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['add'] = $this->url->link('extension/mas/workflow.form', 'user_token=' . $this->session->data['user_token']);
        $data['delete'] = $this->url->link('extension/mas/workflow.delete', 'user_token=' . $this->session->data['user_token']);

        $data['workflows'] = [];
        $results = $this->model_extension_mas_module_workflow->getWorkflows();

        foreach ($results as $result) {
            $data['workflows'][] = [
                'workflow_id'  => $result['workflow_id'],
                'name'         => $result['name'],
                'status'       => $result['status'],
                'edit'         => $this->url->link('extension/mas/workflow.form', 'user_token=' . $this->session->data['user_token'] . '&workflow_id=' . $result['workflow_id'])
            ];
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/workflow_list', $data));
    }

    public function delete(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (isset($this->request->post['selected']) && $this->user->hasPermission('modify', 'extension/mas/workflow')) {
            $this->load->model('extension/mas/module/workflow');
            foreach ($this->request->post['selected'] as $workflow_id) {
                $this->model_extension_mas_module_workflow->deleteWorkflow($workflow_id);
            }
            $json['success'] = $this->language->get('text_success');
            $json['redirect'] = $this->url->link('extension/mas/workflow', 'user_token=' . $this->session->data['user_token']);
        } else {
            $json['error'] = $this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function form(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_workflow_form'));

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_mas_suite_menu'),'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_workflow_list'), 'href' => $this->url->link('extension/mas/workflow', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_workflow_form'), 'href' => $this->url->link('extension/mas/workflow.form', 'user_token=' . $this->session->data['user_token'] . (isset($this->request->get['workflow_id']) ? '&workflow_id=' . $this->request->get['workflow_id'] : ''))];

        $data['save'] = $this->url->link('extension/mas/workflow.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('extension/mas/workflow', 'user_token=' . $this->session->data['user_token']);

        $this->load->model('extension/mas/module/workflow');

        if (isset($this->request->get['workflow_id'])) {
			$workflow_info = $this->model_extension_mas_module_workflow->getWorkflow((int)$this->request->get['workflow_id']);
		}

        $data['workflow_id'] = $this->request->get['workflow_id'] ?? 0;
        $data['name'] = $workflow_info['name'] ?? '';
        $data['status'] = $workflow_info['status'] ?? 1;
        $data['workflow_data'] = isset($workflow_info['workflow_data']) ? json_encode($workflow_info['workflow_data'], JSON_PRETTY_PRINT) : '';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/workflow_form', $data));
    }

    public function save(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/mas/workflow')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 255)) {
            $json['error']['name'] = $this->language->get('error_name');
        }

        $workflow_data = json_decode(html_entity_decode($this->request->post['workflow_data']), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             $json['error']['workflow_data'] = $this->language->get('error_json');
        }

        if (!$json) {
            $this->load->model('extension/mas/module/workflow');

            $save_data = [
                'name' => $this->request->post['name'],
                'status' => $this->request->post['status'],
                'workflow_data' => $workflow_data
            ];

            if ($this->request->post['workflow_id']) {
                $this->model_extension_mas_module_workflow->editWorkflow((int)$this->request->post['workflow_id'], $save_data);
            } else {
                $this->model_extension_mas_module_workflow->addWorkflow($save_data);
            }

            $json['success'] = $this->language->get('text_success');
            $json['redirect'] = $this->url->link('extension/mas/workflow', 'user_token=' . $this->session->data['user_token']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}