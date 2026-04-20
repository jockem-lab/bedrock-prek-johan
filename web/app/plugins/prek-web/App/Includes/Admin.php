<?php

namespace PrekWeb\Includes;

class Admin {

    protected $loader;
    protected $options;

    public function __construct(\PrekWeb\Includes\Loader $loader, \PrekWeb\Includes\Options $options)
    {
        $this->loader  = $loader;
        $this->options = $options;
    }

    public function run()
    {
        $this->cleanUpMenuBar();
        $this->cleanUpMenu();
    }

    private function cleanUpMenuBar()
    {
        add_action('admin_bar_menu', function($wp_admin_bar){
            $wp_admin_bar->remove_node('comments');
            $wp_admin_bar->remove_node('new-fasad_listing');
            $wp_admin_bar->remove_node('new-fasad_office');
            $wp_admin_bar->remove_node('new-fasad_realtor');
        }, 999);
    }

    private function cleanUpMenu()
    {
        add_action('admin_menu', function (){
            if(!Helpers::isPrekUser()){
                $hidemenus = $this->options->getOption('hidemenus');
                if (!empty($hidemenus)) {
                    foreach ($hidemenus as $menu) {
                        if (strpos($menu, '==')) {
                            // Submenu
                            $menuParts = explode('==', $menu);
                            if (strpos($menuParts[1], '?page=')) {
                                $menuParts[1] = str_replace('?page=', '', $menuParts[1]);
                            } elseif (strpos($menuParts[1], '?return=')) {
                                // Customizer special
                                $subpage = preg_replace('/^([^?]+)(\?.*)$/', '$1', $menuParts[1]);
                                $menuParts[1] = $subpage . '?return=' . urlencode($_SERVER['REQUEST_URI']);
                            }
                            remove_submenu_page($menuParts[0], $menuParts[1]);
                        } else {
                            remove_menu_page( $menu );
                        }
                    }
                }
            }
        }, 120);

    }
}
