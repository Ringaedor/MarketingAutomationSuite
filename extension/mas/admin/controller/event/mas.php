<?php
namespace Opencart\Admin\Controller\Extension\Mas\Event;
class Mas extends \Opencart\System\Engine\Controller {
    /**
     * @param string $route
     * @param array $args
     * @param mixed $output
     * @return void
     */
    public function menu(string &$route, array &$args, mixed &$output): void {
        if ($this->user->hasPermission('access', 'extension/mas/module/mas')) {

            $this->load->language('extension/mas/module/mas');

            $mas_children = [];

            if ($this->user->hasPermission('access', 'extension/mas/module/mas_template')) {
                 $mas_children[] = [
                    'name'     => $this->language->get('text_templates'),
                    'href'     => $this->url->link('extension/mas/module/mas_template', 'user_token=' . $this->session->data['user_token']),
                    'children' => []
                ];
            }

            if ($this->user->hasPermission('access', 'extension/mas/module/mas_provider')) {
                $mas_children[] = [
                   'name'     => $this->language->get('text_providers'),
                   'href'     => $this->url->link('extension/mas/module/mas_provider', 'user_token=' . $this->session->data['user_token']),
                   'children' => []
               ];
           }

            if ($this->user->hasPermission('access', 'extension/mas/module/mas_segment')) {
                $mas_children[] = [
                   'name'     => $this->language->get('text_segments'),
                   'href'     => $this->url->link('extension/mas/module/mas_segment', 'user_token=' . $this->session->data['user_token']),
                   'children' => []
               ];
           }

           if ($this->user->hasPermission('access', 'extension/mas/module/mas_workflow')) {
                $mas_children[] = [
                   'name'     => $this->language->get('text_workflows'),
                   'href'     => $this->url->link('extension/mas/module/mas_workflow', 'user_token=' . $this->session->data['user_token']),
                   'children' => []
               ];
           }

            // --- Add other module links here in the future ---

            if ($mas_children) {
                $mas_menu = [
                    'id'       => 'menu-mas',
                'icon'	   => 'fa-solid fa-rocket',
                'name'	   => 'Marketing Suite',
                'href'     => $this->url->link('extension/mas/module/mas', 'user_token=' . $this->session->data['user_token']),
                'children' => $mas_children
            ];

            // Find the 'Design' menu item to insert before it
            $design_menu_key = null;
            foreach ($args['menus'] as $key => $menu) {
                if ($menu['id'] == 'menu-design') {
                    $design_menu_key = $key;
                    break;
                }
            }

            if ($design_menu_key !== null) {
                array_splice($args['menus'], $design_menu_key, 0, [$mas_menu]);
            } else {
                // Fallback if 'Design' menu is not found
                $args['menus'][] = $mas_menu;
            }
        }
    }
}