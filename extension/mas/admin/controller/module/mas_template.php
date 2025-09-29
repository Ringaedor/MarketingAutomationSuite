<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;
class MasTemplate extends \Opencart\System\Engine\Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/mas/module/mas_template');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_template');
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
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token'] . $url)];

        $data['add'] = $this->url->link('extension/mas/module/mas_template.add', 'user_token=' . $this->session->data['user_token'] . $url);
        $data['delete'] = $this->url->link('extension/mas/module/mas_template.delete', 'user_token=' . $this->session->data['user_token']);

        $data['templates'] = [];
        $filter_data = ['sort' => $sort, 'order' => $order, 'start' => ($page - 1) * $this->config->get('config_pagination'), 'limit' => $this->config->get('config_pagination')];

        $template_total = $this->model_extension_mas_module_mas_template->getTotalTemplates();
        $results = $this->model_extension_mas_module_mas_template->getTemplates($filter_data);

        foreach ($results as $result) {
            $data['templates'][] = [
                'template_id' => $result['template_id'],
                'name'        => $result['name'],
                'subject'     => $result['subject'],
                'type'        => $this->language->get('text_type_' . $result['type']),
                'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'edit'        => $this->url->link('extension/mas/module/mas_template.edit', 'user_token=' . $this->session->data['user_token'] . '&template_id=' . $result['template_id'] . $url)
            ];
        }

        $url = '';
        if ($order == 'ASC') $url .= '&order=DESC'; else $url .= '&order=ASC';
        $data['sort_name'] = $this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
        $data['sort_type'] = $this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token'] . '&sort=type' . $url);
        $data['sort_date_added'] = $this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url);

        $data['pagination'] = $this->load->controller('common/pagination', ['total' => $template_total, 'page' => $page, 'limit' => $this->config->get('config_pagination'), 'url' => $this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')]);
		$data['results'] = sprintf($this->language->get('text_pagination'), ($template_total) ? (($page - 1) * $this->config->get('config_pagination')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination')) > ($template_total - $this->config->get('config_pagination'))) ? $template_total : ((($page - 1) * $this->config->get('config_pagination')) + $this->config->get('config_pagination')), $template_total, ceil($template_total / $this->config->get('config_pagination')));

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/mas/module/mas_template_list', $data));
    }

    public function add(): void {
        $this->load->language('extension/mas/module/mas_template');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_template');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mas_module_mas_template->addTemplate($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token']));
        }
        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('extension/mas/module/mas_template');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_template');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mas_module_mas_template->editTemplate($this->request->get['template_id'], $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token']));
        }
        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('extension/mas/module/mas_template');
        $this->load->model('extension/mas/module/mas_template');
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $template_id) {
                $this->model_extension_mas_module_mas_template->deleteTemplate($template_id);
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token']));
        } else {
             $this->session->data['error_warning'] = $this->language->get('error_permission');
             $this->response->redirect($this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token']));
        }
    }

    protected function getForm(): void {
        $data['text_form'] = !isset($this->request->get['template_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_name'] = $this->error['name'] ?? '';
        $data['error_subject'] = $this->error['subject'] ?? '';
        $data['error_html_content'] = $this->error['html_content'] ?? '';

        $url = '';
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token'] . $url)];

        if (!isset($this->request->get['template_id'])) {
            $data['action'] = $this->url->link('extension/mas/module/mas_template.add', 'user_token=' . $this->session->data['user_token'] . $url);
        } else {
            $data['action'] = $this->url->link('extension/mas/module/mas_template.edit', 'user_token=' . $this->session->data['user_token'] . '&template_id=' . $this->request->get['template_id'] . $url);
        }
        $data['cancel'] = $this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token'] . $url);

        if (isset($this->request->get['template_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $template_info = $this->model_extension_mas_module_mas_template->getTemplate($this->request->get['template_id']);
        }
        $data['user_token'] = $this->session->data['user_token'];

        $data['name'] = $this->request->post['name'] ?? ($template_info['name'] ?? '');
        $data['subject'] = $this->request->post['subject'] ?? ($template_info['subject'] ?? '');
        $data['html_content'] = $this->request->post['html_content'] ?? ($template_info['html_content'] ?? '');
        $data['text_content'] = $this->request->post['text_content'] ?? ($template_info['text_content'] ?? '');
        $data['type'] = $this->request->post['type'] ?? ($template_info['type'] ?? 'email');

        $data['template_types'] = [
            ['value' => 'email', 'text' => $this->language->get('text_type_email')],
            ['value' => 'sms', 'text' => $this->language->get('text_type_sms')]
        ];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/mas/module/mas_template_form', $data));
    }

    protected function validateForm(): bool {
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas_template')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 255)) {
            $this->error['name'] = $this->language->get('error_name');
        }
        if ($this->request->post['type'] == 'email' && ((utf8_strlen($this->request->post['subject']) < 3) || (utf8_strlen($this->request->post['subject']) > 255))) {
            $this->error['subject'] = $this->language->get('error_subject');
        }
        if (utf8_strlen($this->request->post['html_content']) < 10) {
            $this->error['html_content'] = $this->language->get('error_html_content');
        }
        return !$this->error;
    }

    protected function validateDelete(): bool {
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas_template')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}