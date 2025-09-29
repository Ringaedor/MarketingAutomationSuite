<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;
class MasWorkflow extends \Opencart\System\Engine\Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/mas/module/mas_workflow');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_workflow');
        $this->getList();
    }

    public function getList(): void {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';
        if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
        if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
        if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token'] . $url)];

        $data['add'] = $this->url->link('extension/mas/module/mas_workflow.add', 'user_token=' . $this->session->data['user_token'] . $url);
        $data['delete'] = $this->url->link('extension/mas/module/mas_workflow.delete', 'user_token=' . $this->session->data['user_token']);

        $data['workflows'] = [];
        $filter_data = ['sort' => $sort, 'order' => $order, 'start' => ($page - 1) * $this->config->get('config_pagination'), 'limit' => $this->config->get('config_pagination')];

        $workflow_total = $this->model_extension_mas_module_mas_workflow->getTotalWorkflows();
        $results = $this->model_extension_mas_module_mas_workflow->getWorkflows($filter_data);

        foreach ($results as $result) {
            $data['workflows'][] = [
                'workflow_id'  => $result['workflow_id'],
                'name'         => $result['name'],
                'status'       => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'date_added'   => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'edit'         => $this->url->link('extension/mas/module/mas_workflow.edit', 'user_token=' . $this->session->data['user_token'] . '&workflow_id=' . $result['workflow_id'] . $url)
            ];
        }

        $url = '';
        if ($order == 'ASC') $url .= '&order=DESC'; else $url .= '&order=ASC';
        $data['sort_name'] = $this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
        $data['sort_status'] = $this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url);
        $data['sort_date_added'] = $this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url);

        $data['pagination'] = $this->load->controller('common/pagination', ['total' => $workflow_total, 'page'  => $page, 'limit' => $this->config->get('config_pagination'), 'url' => $this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')]);
		$data['results'] = sprintf($this->language->get('text_pagination'), ($workflow_total) ? (($page - 1) * $this->config->get('config_pagination')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination')) > ($workflow_total - $this->config->get('config_pagination'))) ? $workflow_total : ((($page - 1) * $this->config->get('config_pagination')) + $this->config->get('config_pagination')), $workflow_total, ceil($workflow_total / $this->config->get('config_pagination')));

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/mas/module/mas_workflow_list', $data));
    }

    public function add(): void {
        $this->load->language('extension/mas/module/mas_workflow');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_workflow');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mas_module_mas_workflow->addWorkflow($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token']));
        }
        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('extension/mas/module/mas_workflow');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_workflow');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mas_module_mas_workflow->editWorkflow($this->request->get['workflow_id'], $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token']));
        }
        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('extension/mas/module/mas_workflow');
        $this->load->model('extension/mas/module/mas_workflow');
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $workflow_id) {
                $this->model_extension_mas_module_mas_workflow->deleteWorkflow($workflow_id);
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token']));
        } else {
             $this->session->data['error_warning'] = $this->language->get('error_permission');
             $this->response->redirect($this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token']));
        }
    }

    protected function getForm(): void {
        $data['text_form'] = !isset($this->request->get['workflow_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_name'] = $this->error['name'] ?? '';

        $url = '';
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token'] . $url)];

        if (!isset($this->request->get['workflow_id'])) {
            $data['action'] = $this->url->link('extension/mas/module/mas_workflow.add', 'user_token=' . $this->session->data['user_token'] . $url);
        } else {
            $data['action'] = $this->url->link('extension/mas/module/mas_workflow.edit', 'user_token=' . $this->session->data['user_token'] . '&workflow_id=' . $this->request->get['workflow_id'] . $url);
        }
        $data['cancel'] = $this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token'] . $url);

        if (isset($this->request->get['workflow_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $workflow_info = $this->model_extension_mas_module_mas_workflow->getWorkflow($this->request->get['workflow_id']);
        }

        $data['name'] = $this->request->post['name'] ?? ($workflow_info['name'] ?? '');
        $data['status'] = $this->request->post['status'] ?? ($workflow_info['status'] ?? 0);
        $data['workflow_data'] = $this->request->post['workflow_data'] ?? ($workflow_info['workflow_data'] ?? '');

        $data['node_definitions'] = $this->mas->getWorkflowEngine()->getNodeDefinitions();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/mas/module/mas_workflow_form', $data));
    }

    protected function validateForm(): bool {
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas_workflow')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }
        if (empty($this->request->post['workflow_data'])) {
            $this->error['warning'] = $this->language->get('error_workflow_data');
        } else {
            $decoded = json_decode(html_entity_decode($this->request->post['workflow_data']), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error['warning'] = $this->language->get('error_workflow_data_invalid');
            }
        }
        return !$this->error;
    }

    protected function validateDelete(): bool {
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas_workflow')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}