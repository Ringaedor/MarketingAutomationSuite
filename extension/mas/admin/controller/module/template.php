<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;

class Template extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_template_list'));

        if (!$this->user->hasPermission('access', 'extension/mas/module/template')) {
            $this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token']));
        }

        $this->load->model('extension/mas/module/template');

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_mas_suite_menu'), 'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_template_list'), 'href' => $this->url->link('extension/mas/module/template', 'user_token=' . $this->session->data['user_token'])];

        $data['add'] = $this->url->link('extension/mas/module/template.form', 'user_token=' . $this->session->data['user_token']);
        $data['delete'] = $this->url->link('extension/mas/module/template.delete', 'user_token=' . $this->session->data['user_token']);

        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $sort = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'name';
        $order = isset($this->request->get['order']) ? $this->request->get['order'] : 'ASC';

        $filter_data = [
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
            'limit' => $this->config->get('config_pagination_admin')
        ];

        $template_total = $this->model_extension_mas_module_template->getTotalTemplates();
        $results = $this->model_extension_mas_module_template->getTemplates($filter_data);

        $data['templates'] = [];
        foreach ($results as $result) {
            $data['templates'][] = [
                'template_id' => $result['template_id'],
                'name'        => $result['name'],
                'subject'     => $result['subject'],
                'edit'        => $this->url->link('extension/mas/module/template.form', 'user_token=' . $this->session->data['user_token'] . '&template_id=' . $result['template_id'])
            ];
        }

        $url = '';
        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }
        $data['sort_name'] = $this->url->link('extension/mas/module/template', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);

        $data['pagination'] = $this->load->controller('common/pagination', [
            'total' => $template_total,
            'page'  => $page,
            'limit' => $this->config->get('config_pagination_admin'),
            'url'   => $this->url->link('extension/mas/module/template', 'user_token=' . $this->session->data['user_token'] . '&page={page}')
        ]);

        $data['results'] = sprintf($this->language->get('text_pagination'), ($template_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($template_total - $this->config->get('config_pagination_admin'))) ? $template_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $template_total, ceil($template_total / $this->config->get('config_pagination_admin')));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/template_list', $data));
    }

    public function delete(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (isset($this->request->post['selected']) && $this->user->hasPermission('modify', 'extension/mas/module/template')) {
            $this->load->model('extension/mas/module/template');
            foreach ($this->request->post['selected'] as $template_id) {
                $this->model_extension_mas_module_template->deleteTemplate($template_id);
            }
            $json['success'] = $this->language->get('text_success_template');
            $json['redirect'] = $this->url->link('extension/mas/module/template', 'user_token=' . $this->session->data['user_token']);
        } else {
            $json['error'] = $this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function form(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_template_form'));

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_mas_suite_menu'),'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_template_list'), 'href' => $this->url->link('extension/mas/module/template', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_template_form'), 'href' => $this->url->link('extension/mas/module/template.form', 'user_token=' . $this->session->data['user_token'] . (isset($this->request->get['template_id']) ? '&template_id=' . $this->request->get['template_id'] : ''))];

        $data['save'] = $this->url->link('extension/mas/module/template.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('extension/mas/module/template', 'user_token=' . $this->session->data['user_token']);

        $this->load->model('extension/mas/module/template');

        if (isset($this->request->get['template_id'])) {
			$template_info = $this->model_extension_mas_module_template->getTemplate((int)$this->request->get['template_id']);
		}

        $data['template_id'] = $this->request->get['template_id'] ?? 0;
        $data['name'] = $template_info['name'] ?? '';
        $data['subject'] = $template_info['subject'] ?? '';
        $data['html_content'] = $template_info['html_content'] ?? '';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/template_form', $data));
    }

    public function save(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/mas/module/template')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 255)) {
            $json['error']['name'] = $this->language->get('error_name');
        }

        if ((utf8_strlen($this->request->post['subject']) < 3) || (utf8_strlen($this->request->post['subject']) > 255)) {
            $json['error']['subject'] = $this->language->get('error_subject');
        }

        if (!$json) {
            $this->load->model('extension/mas/module/template');

            if ($this->request->post['template_id']) {
                $this->model_extension_mas_module_template->editTemplate((int)$this->request->post['template_id'], $this->request->post);
            } else {
                $this->model_extension_mas_module_template->addTemplate($this->request->post);
            }

            $json['success'] = $this->language->get('text_success_template');
            $json['redirect'] = $this->url->link('extension/mas/module/template', 'user_token=' . $this->session->data['user_token']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}