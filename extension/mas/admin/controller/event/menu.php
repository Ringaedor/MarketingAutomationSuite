<?php
namespace Opencart\Admin\Controller\Extension\Mas\Event;

class Menu extends \Opencart\System\Engine\Controller {
    /**
     * Event handler to add the MAS Suite link to the admin navigation menu.
     *
     * This method is triggered by the 'admin/view/common/column_left/before' event.
     * It injects a new menu item for the MAS Suite dashboard into the admin panel's
     * left navigation, positioning it above the 'Marketing' section for prominence.
     *
     * @param string $route The current route.
     * @param array  $data  The data array for the view, passed by reference.
     * @param mixed  $output The output.
     * @return void
     */
    public function addColumnLeftLink(string &$route, array &$data, mixed &$output): void {
        if ($this->user->hasPermission('access', 'extension/mas/module/mas')) {
            $this->load->language('extension/mas/module/mas');

            $mas_children = [];

            $mas_children[] = [
                'name'     => 'Dashboard',
                'href'     => $this->url->link('extension/mas/dashboard/mas', 'user_token=' . $this->session->data['user_token']),
                'children' => []
            ];

            $mas_children[] = [
                'name'     => 'Providers',
                'href'     => $this->url->link('extension/mas/module/mas', 'user_token=' . $this->session->data['user_token']),
                'children' => []
            ];

            $mas_children[] = [
                'name'     => 'Segments',
                'href'     => $this->url->link('extension/mas/segment', 'user_token=' . $this->session->data['user_token']),
                'children' => []
            ];

            $mas_children[] = [
                'name'     => 'Workflows',
                'href'     => $this->url->link('extension/mas/workflow', 'user_token=' . $this->session->data['user_token']),
                'children' => []
            ];

            $mas_children[] = [
                'name'     => 'Templates',
                'href'     => $this->url->link('extension/mas/template', 'user_token=' . $this->session->data['user_token']),
                'children' => []
            ];

            $mas_menu = [
                'id'       => 'menu-mas',
                'icon'     => 'fa-solid fa-rocket',
                'name'     => $this->language->get('text_mas_suite_menu'),
                'href'     => '',
                'children' => $mas_children
            ];

            // Find the 'Marketing' menu item to insert before it
            $marketing_menu_key = null;
            if (isset($data['menus'])) {
                foreach ($data['menus'] as $key => $menu) {
                    if (isset($menu['id']) && $menu['id'] == 'menu-marketing') {
                        $marketing_menu_key = $key;
                        break;
                    }
                }
            }

            if ($marketing_menu_key !== null) {
                // Insert the new menu item before 'Marketing'
                array_splice($data['menus'], $marketing_menu_key, 0, [$mas_menu]);
            } else {
                // Fallback: add to the end if 'Marketing' menu is not found
                $data['menus'][] = $mas_menu;
            }
        }
    }
}