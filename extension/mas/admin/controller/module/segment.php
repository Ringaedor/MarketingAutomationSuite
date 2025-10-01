<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;

class Segment extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_segment_list'));

        if (!$this->user->hasPermission('access', 'extension/mas/module/segment')) {
            $this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token']));
        }

        $this->load->model('extension/mas/module/segment');

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
            'text' => $this->language->get('heading_segment_list'),
            'href' => $this->url->link('extension/mas/module/segment', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['add'] = $this->url->link('extension/mas/module/segment.form', 'user_token=' . $this->session->data['user_token']);
        $data['delete'] = $this->url->link('extension/mas/module/segment.delete', 'user_token=' . $this->session->data['user_token']);

        $data['segments'] = [];

        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $sort = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'name';
        $order = isset($this->request->get['order']) ? $this->request->get['order'] : 'ASC';

        $filter_data = [
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
            'limit' => $this->config->get('config_pagination_admin')
        ];

        $segment_total = $this->model_extension_mas_module_segment->getTotalSegments();
        $results = $this->model_extension_mas_module_segment->getSegments($filter_data);

        foreach ($results as $result) {
            $data['segments'][] = [
                'segment_id'  => $result['segment_id'],
                'name'        => $result['name'],
                'description' => $result['description'],
                'edit'        => $this->url->link('extension/mas/module/segment.form', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $result['segment_id'])
            ];
        }

        $url = '';
        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }
        $data['sort_name'] = $this->url->link('extension/mas/module/segment', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);

        $data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $segment_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('extension/mas/module/segment', 'user_token=' . $this->session->data['user_token'] . '&page={page}')
		]);

        $data['results'] = sprintf($this->language->get('text_pagination'), ($segment_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($segment_total - $this->config->get('config_pagination_admin'))) ? $segment_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $segment_total, ceil($segment_total / $this->config->get('config_pagination_admin')));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/segment_list', $data));
    }

    public function delete(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (isset($this->request->post['selected']) && $this->user->hasPermission('modify', 'extension/mas/module/segment')) {
            $this->load->model('extension/mas/module/segment');
            foreach ($this->request->post['selected'] as $segment_id) {
                $this->model_extension_mas_module_segment->deleteSegment($segment_id);
            }
            $json['success'] = $this->language->get('text_success');
            $json['redirect'] = $this->url->link('extension/mas/module/segment', 'user_token=' . $this->session->data['user_token']);
        } else {
            $json['error'] = $this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function form(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_segment_form'));

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_mas_suite_menu'),'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_segment_list'), 'href' => $this->url->link('extension/mas/module/segment', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_segment_form'), 'href' => $this->url->link('extension/mas/module/segment.form', 'user_token=' . $this->session->data['user_token'] . (isset($this->request->get['segment_id']) ? '&segment_id=' . $this->request->get['segment_id'] : ''))];

        $data['save'] = $this->url->link('extension/mas/module/segment.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('extension/mas/module/segment', 'user_token=' . $this->session->data['user_token']);

        $this->load->model('extension/mas/module/segment');

        if (isset($this->request->get['segment_id'])) {
			$segment_info = $this->model_extension_mas_module_segment->getSegment((int)$this->request->get['segment_id']);
		}

        $data['segment_id'] = $this->request->get['segment_id'] ?? 0;
        $data['name'] = $segment_info['name'] ?? '';
        $data['description'] = $segment_info['description'] ?? '';
        $data['rules'] = $segment_info['rules'] ?? [];

        $this->load->model('customer/customer_group');
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        $this->load->model('localisation/country');
        $data['countries'] = $this->model_localisation_country->getCountries();

        // Load rule definitions dynamically
        if (!$this->registry->has('mas')) {
            $file = DIR_EXTENSION . 'mas/system/library/mas.php';
            if (is_file($file)) {
                include_once($file);
                $this->registry->set('mas', new \Opencart\System\Library\Extension\Mas\Mas($this->registry));
            }
        }
        $data['rule_definitions'] = $this->mas->getSegmentManager()->getRuleDefinitions();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/segment_form', $data));
    }

    public function save(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/mas/module/segment')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 255)) {
            $json['error']['name'] = $this->language->get('error_name');
        }

        // Add more validation for rules if needed

        if (!$json) {
            $this->load->model('extension/mas/module/segment');

            if ($this->request->post['segment_id']) {
                $this->model_extension_mas_module_segment->editSegment((int)$this->request->post['segment_id'], $this->request->post);
            } else {
                $this->model_extension_mas_module_segment->addSegment($this->request->post);
            }

            $json['success'] = $this->language->get('text_success_segment');
            $json['redirect'] = $this->url->link('extension/mas/module/segment', 'user_token=' . $this->session->data['user_token']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}