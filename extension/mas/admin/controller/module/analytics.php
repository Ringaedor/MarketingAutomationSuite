<?php
namespace Opencart\Admin\Controller\Extension\Mas;

class Analytics extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_analytics_list'));

        if (!$this->user->hasPermission('access', 'extension/mas/analytics')) {
            $this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token']));
        }

        $this->load->model('extension/mas/module/analytics');

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_mas_suite_menu'), 'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_analytics_list'), 'href' => $this->url->link('extension/mas/module/analytics', 'user_token=' . $this->session->data['user_token'])];

        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

        $filter_data = [
            'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
            'limit' => $this->config->get('config_pagination_admin')
        ];

        $event_total = $this->model_extension_mas_module_analytics->getTotalEvents();
        $results = $this->model_extension_mas_module_analytics->getEvents($filter_data);

        $data['events'] = [];
        $data['customer_link'] = $this->url->link('customer/customer.form', 'user_token=' . $this->session->data['user_token']);

        foreach ($results as $result) {
            $data['events'][] = [
                'event_type'  => $result['event_type'],
                'customer_id' => $result['customer_id'],
                'event_data'  => json_decode($result['event_data'], true),
                'date_added'  => date($this->language->get('datetime_format'), strtotime($result['date_added']))
            ];
        }

        $data['pagination'] = $this->load->controller('common/pagination', [
            'total' => $event_total,
            'page'  => $page,
            'limit' => $this->config->get('config_pagination_admin'),
            'url'   => $this->url->link('extension/mas/module/analytics', 'user_token=' . $this->session->data['user_token'] . '&page={page}')
        ]);

        $data['results'] = sprintf($this->language->get('text_pagination'), ($event_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($event_total - $this->config->get('config_pagination_admin'))) ? $event_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $event_total, ceil($event_total / $this->config->get('config_pagination_admin')));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/analytics_list', $data));
    }
}