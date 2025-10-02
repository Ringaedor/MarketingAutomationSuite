<?php
namespace Opencart\Admin\Controller\Extension\Mas\Module;

class Campaign extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_campaign_list'));

        if (!$this->user->hasPermission('access', 'extension/mas/module/campaign')) {
            $this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token']));
        }

        $this->load->model('extension/mas/module/campaign');

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_mas_suite_menu'), 'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_campaign_list'), 'href' => $this->url->link('extension/mas/module/campaign', 'user_token=' . $this->session->data['user_token'])];

        $data['add'] = $this->url->link('extension/mas/module/campaign.form', 'user_token=' . $this->session->data['user_token']);
        $data['delete'] = $this->url->link('extension/mas/module/campaign.delete', 'user_token=' . $this->session->data['user_token']);

        $data['campaigns'] = [];
        $results = $this->model_extension_mas_module_campaign->getCampaigns();

        foreach ($results as $result) {
            $data['campaigns'][] = [
                'campaign_id' => $result['campaign_id'],
                'name'        => $result['name'],
                'status'      => $result['status'] ? $this->language->get('text_active') : $this->language->get('text_inactive'),
                'edit'        => $this->url->link('extension/mas/module/campaign.form', 'user_token=' . $this->session->data['user_token'] . '&campaign_id=' . $result['campaign_id'])
            ];
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/campaign_list', $data));
    }

    public function delete(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (isset($this->request->post['selected']) && $this->user->hasPermission('modify', 'extension/mas/module/campaign')) {
            $this->load->model('extension/mas/module/campaign');
            foreach ($this->request->post['selected'] as $campaign_id) {
                $this->model_extension_mas_module_campaign->deleteCampaign($campaign_id);
            }
            $json['success'] = $this->language->get('text_success');
            $json['redirect'] = $this->url->link('extension/mas/module/campaign', 'user_token=' . $this->session->data['user_token']);
        } else {
            $json['error'] = $this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function form(): void {
        $this->load->language('extension/mas/module/mas');
        $this->document->setTitle($this->language->get('heading_campaign_form'));

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('text_mas_suite_menu'), 'href' => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_campaign_list'), 'href' => $this->url->link('extension/mas/module/campaign', 'user_token=' . $this->session->data['user_token'])];
        $data['breadcrumbs'][] = ['text' => $this->language->get('heading_campaign_form'), 'href' => $this->url->link('extension/mas/module/campaign.form', 'user_token=' . $this->session->data['user_token'] . (isset($this->request->get['campaign_id']) ? '&campaign_id=' . $this->request->get['campaign_id'] : ''))];

        $data['save'] = $this->url->link('extension/mas/module/campaign.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('extension/mas/module/campaign', 'user_token=' . $this->session->data['user_token']);

        $this->load->model('extension/mas/module/campaign');

        if (isset($this->request->get['campaign_id'])) {
			$campaign_info = $this->model_extension_mas_module_campaign->getCampaign((int)$this->request->get['campaign_id']);
		}

        $data['campaign_id'] = $this->request->get['campaign_id'] ?? 0;
        $data['name'] = $campaign_info['name'] ?? '';
        $data['description'] = $campaign_info['description'] ?? '';
        $data['status'] = $campaign_info['status'] ?? 1;
        $data['assets'] = $campaign_info['assets'] ?? ['workflows' => [], 'segments' => [], 'templates' => []];

        // Load assets for selection
        $this->load->model('extension/mas/module/workflow');
        $data['all_workflows'] = $this->model_extension_mas_module_workflow->getWorkflows();
        $this->load->model('extension/mas/module/segment');
        $data['all_segments'] = $this->model_extension_mas_module_segment->getSegments();
        $this->load->model('extension/mas/module/template');
        $data['all_templates'] = $this->model_extension_mas_module_template->getTemplates();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/mas/campaign_form', $data));
    }

    public function save(): void {
        $this->load->language('extension/mas/module/mas');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/mas/module/campaign')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 255)) {
            $json['error']['name'] = $this->language->get('error_name');
        }

        if (!$json) {
            $this->load->model('extension/mas/module/campaign');

            if ($this->request->post['campaign_id']) {
                $this->model_extension_mas_module_campaign->editCampaign((int)$this->request->post['campaign_id'], $this->request->post);
            } else {
                $this->model_extension_mas_module_campaign->addCampaign($this->request->post);
            }

            $json['success'] = $this->language->get('text_success');
            $json['redirect'] = $this->url->link('extension/mas/module/campaign', 'user_token=' . $this->session->data['user_token']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}