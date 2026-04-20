<?php

namespace PrekAdmin\Includes;

use PrekWebHelper\Includes\Helpers;
//use PrekWeb\Includes\Helpers; //both works

class PrekAdminHandler
{
    protected $loader;

    public function __construct(\PrekAdmin\Includes\Loader $loader)
    {
        $this->loader = $loader;
    }

    public function run()
    {
        $this->loader->addAction('admin_menu', $this, 'adminMenu');
        $this->loader->addAction('current_screen', $this, 'currentScreen');
        $this->loader->addAction('wp_dashboard_setup', $this, 'dashboardSetup');
        $this->loader->addFilter('get_user_metadata', $this, 'hideCFWelcome', 10, 5);
        $this->loader->addFilter('default_user_metadata', $this, 'hideCFWelcome', 10, 5);
    }

    public function adminMenu()
    {
        //remove site health from menu
        if (!Helpers::isPrekUser()) {
            remove_submenu_page('tools.php', 'site-health.php');
        }
    }

    public function currentScreen()
    {
        //redirect to admin if visiting site health
        if (is_admin() && !Helpers::isPrekUser()) {
            $screen = get_current_screen();

            // if screen id is site health
            if ('site-health' == $screen->id) {
                wp_redirect(admin_url());
                exit;
            }
        }
    }

    public function dashboardSetup()
    {
        if (!Helpers::isPrekUser()) {
            //remove site health from dashboard
            remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
            //remove php update nag from dashboard
            remove_meta_box('dashboard_php_nag', 'dashboard', 'normal');
        }
    }

    public function hideCFWelcome($value, $object_id, $meta_key, $single, $meta_type = '')
    {
        if ($meta_key === 'wpcf7_hide_welcome_panel_on' && defined('WPCF7_VERSION')) {
            $value = WPCF7_VERSION;
        }
        return $value;
    }

}
