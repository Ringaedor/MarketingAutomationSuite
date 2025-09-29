<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;
class MasSegment extends \Opencart\System\Engine\Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/mas/module/mas_segment');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_segment');
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
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token'] . $url)];

        $data['add'] = $this->url->link('extension/mas/module/mas_segment.add', 'user_token=' . $this->session->data['user_token'] . $url);
        $data['delete'] = $this->url->link('extension/mas/module/mas_segment.delete', 'user_token=' . $this->session->data['user_token']);

        $data['segments'] = [];
        $filter_data = ['sort' => $sort, 'order' => $order, 'start' => ($page - 1) * $this->config->get('config_pagination'), 'limit' => $this->config->get('config_pagination')];

        $segment_total = $this->model_extension_mas_module_mas_segment->getTotalSegments();
        $results = $this->model_extension_mas_module_mas_segment->getSegments($filter_data);

        foreach ($results as $result) {
            $data['segments'][] = [
                'segment_id'  => $result['segment_id'],
                'name'        => $result['name'],
                'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'edit'        => $this->url->link('extension/mas/module/mas_segment.edit', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $result['segment_id'] . $url)
            ];
        }

        $url = '';
        if ($order == 'ASC') $url .= '&order=DESC'; else $url .= '&order=ASC';
        $data['sort_name'] = $this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
        $data['sort_date_added'] = $this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url);

        $data['pagination'] = $this->load->controller('common/pagination', ['total' => $segment_total, 'page'  => $page, 'limit' => $this->config->get('config_pagination'), 'url' => $this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')]);
		$data['results'] = sprintf($this->language->get('text_pagination'), ($segment_total) ? (($page - 1) * $this->config->get('config_pagination')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination')) > ($segment_total - $this->config->get('config_pagination'))) ? $segment_total : ((($page - 1) * $this->config->get('config_pagination')) + $this->config->get('config_pagination')), $segment_total, ceil($segment_total / $this->config->get('config_pagination')));

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/mas/module/mas_segment_list', $data));
    }

    public function add(): void {
        $this->load->language('extension/mas/module/mas_segment');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_segment');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mas_module_mas_segment->addSegment($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token']));
        }
        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('extension/mas/module/mas_segment');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/mas/module/mas_segment');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_mas_module_mas_segment->editSegment($this->request->get['segment_id'], $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token']));
        }
        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('extension/mas/module/mas_segment');
        $this->load->model('extension/mas/module/mas_segment');
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $segment_id) {
                $this->model_extension_mas_module_mas_segment->deleteSegment($segment_id);
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token']));
        } else {
             $this->session->data['error_warning'] = $this->language->get('error_permission');
             $this->response->redirect($this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token']));
        }
    }

    protected function getForm(): void {
        $data['text_form'] = !isset($this->request->get['segment_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_name'] = $this->error['name'] ?? '';

        $url = '';
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token'] . $url)];

        if (!isset($this->request->get['segment_id'])) {
            $data['action'] = $this->url->link('extension/mas/module/mas_segment.add', 'user_token=' . $this->session->data['user_token'] . $url);
        } else {
            $data['action'] = $this->url->link('extension/mas/module/mas_segment.edit', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $this->request->get['segment_id'] . $url);
        }
        $data['cancel'] = $this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token'] . $url);

        if (isset($this->request->get['segment_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $segment_info = $this->model_extension_mas_module_mas_segment->getSegment($this->request->get['segment_id']);
        }

        $data['name'] = $this->request->post['name'] ?? ($segment_info['name'] ?? '');

        if (isset($this->request->post['rules'])) {
            $rules = $this->request->post['rules'];
        } elseif (!empty($segment_info)) {
            $rules = $this->model_extension_mas_module_mas_segment->getSegmentRules($this->request->get['segment_id']);
        } else {
            $rules = [];
        }
        $data['rules'] = $rules;

        $data['rule_definitions'] = $this->mas->getSegmentManager()->getRuleDefinitions();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/mas/module/mas_segment_form', $data));
    }

    protected function validateForm(): bool {
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas_segment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }
        if (!isset($this->request->post['rules']) || !is_array($this->request->post['rules'])) {
            $this->error['warning'] = $this->language->get('error_rules_required');
        }
        return !$this->error;
    }

    protected function validateDelete(): bool {
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas_segment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}