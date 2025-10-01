<?php
namespace Opencart\Admin\Controller\Extension\Mas\Dashboard;

class Mas extends \Opencart\System\Engine\Controller {
    /**
     * Entry point for the MAS Suite dashboard page.
     *
     * @return void
     */
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_dashboard_title')); // A new language var

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_mas_suite_menu'),
            'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])
        ];

        $this->load->model('extension/mas/module/provider');

        $data['total_providers'] = $this->model_extension_mas_module_provider->getTotalProviders();
        $data['active_providers'] = $this->model_extension_mas_module_provider->getTotalProviders(['filter_status' => 1]);

        // Load real data
        $this->load->model('account/customer');
        $data['total_customers'] = $this->model_account_customer->getTotalCustomers();

        $this->load->model('extension/mas/module/segment');
        $data['total_segments'] = $this->model_extension_mas_module_segment->getTotalSegments();

        // Links for the dashboard cards
        $data['customer_link'] = $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token']);
        $data['segment_link'] = $this->url->link('extension/mas/segment', 'user_token=' . $this->session->data['user_token']);
        $data['provider_link'] = $this->url->link('extension/mas/module/mas', 'user_token=' . $this->session->data['user_token']);


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // This will be the main view for the suite's dashboard
        $this->response->setOutput($this->load->view('extension/mas/dashboard/mas', $data));
    }
}