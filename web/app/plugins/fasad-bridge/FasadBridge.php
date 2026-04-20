<?php

namespace FasadBridge;

use FasadBridge\Includes\AdminSettings;
use FasadBridge\Includes\Loader;
use FasadBridge\Includes\PublicSettings;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin
 * and defines a function that starts the plugin.
 *
 *
 * @wordpress-plugin
 * Plugin Name:       FasAd Bridge
 * Plugin URI:        https://prek.se/
 * Description:       A WordPress plugin that is fetching data from FasAd API Connect.
 * Version:           3.37.0
 * Author:            Emil Lindström
 * Author URI:        https://prek.se/
 * License:           Apache
 * Text Domain:       fasad-bridge
 */
class FasadBridge
{
    protected static $instance;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @var string $pluginName The string used to uniquely identify this plugin.
     */
    protected $pluginName;

    /**
     * The name of FasAd API Connect plugin
     *
     * @var string $fasadApiConnectName The string used to uniquely identify Api Connect plugin.
     */
    protected $fasadApiConnectName;

    /**
     * The current version of the plugin.
     *
     * @var string $version The current version of the plugin.
     */
    protected $version;

    /**
     * FasadBridge constructor.
     */
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        // If this file is called directly, abort.
        if (!defined('WPINC')) {
            die;
        }

        /**
         * Currently plugin version.
         * Start at version 1.0.0 and use SemVer - https://semver.org
         * Rename this for your plugin and update it as you release new versions.
         */
        $this->version = "3.6.0";
        $this->pluginName = 'fasad-bridge';
        $this->fasadApiConnectName = 'fasad-api-connect';
        $this->initLoader();
        $this->defineAdminHooks();
        $this->definePublicHooks();
        $this->defineConstants();

        self::$instance = $this;
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function activate()
    {
        if (!class_exists('FasadApiConnect\FasadApiConnect') && !is_plugin_active('fasad-api-connect/FasadApiConnect.php')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('FasAd API Connect krävs för att aktivera FasAd Bridge', 'fasad-bridge'), '');
        }
    }

    public function deactivate()
    {
        //clean up cron jobs. No effect as mu-plugins though, but good to do
        $hookName  = 'sync_listings_with_showings';
        $timestamp = wp_next_scheduled($hookName);
        wp_unschedule_event($timestamp, $hookName);
        $hookName  = 'sync_all_listings';
        $timestamp = wp_next_scheduled($hookName);
        wp_unschedule_event($timestamp, $hookName);
    }

    /**
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     */
    private function initLoader()
    {
        $this->loader = new Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     */
    private function defineAdminHooks()
    {
        $pluginAdmin = new AdminSettings($this->pluginName, $this->version, $this->fasadApiConnectName);

        $this->loader->addAction("admin_enqueue_scripts", $pluginAdmin, "enqueueStyles");
        $this->loader->addAction("admin_enqueue_scripts", $pluginAdmin, "enqueueScripts");
        $this->loader->addAction("admin_menu", $pluginAdmin, "addPluginPage");
        $this->loader->addAction("admin_init", $pluginAdmin, "addSettings");
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     */
    private function definePublicHooks()
    {
        $pluginPublic = new PublicSettings($this->pluginName, $this->version);

        $this->loader->addAction("init", $pluginPublic, "registerEndpoints");
        $this->loader->addAction("init", $pluginPublic, "registerCustomPostTypes");
        $this->loader->addAction("template_redirect", $pluginPublic, "urlRedirect");
        $this->loader->addAction("template_redirect", $pluginPublic, "redirectMinilist");
        $this->loader->addAction("pre_get_posts", $pluginPublic, "trigger");
        $this->loader->addAction("wp_ajax_clear_listings", $pluginPublic, "clearFasadDataCallback");
        $this->loader->addFilter("template_include", $pluginPublic, "previewTemplate", 1);
        $this->loader->addFilter("body_class", $pluginPublic, "bodyClass", 1);
        $this->loader->addFilter("wp_head", $pluginPublic, "metaTags", 1);
        $this->loader->addFilter("query_vars", $pluginPublic, "registerQueryVars");
        $this->loader->addAction('fasadSync', $pluginPublic, 'doSync');
        $this->loader->addAction('cron_schedules', $pluginPublic, 'addCronInterval');
        $this->loader->addAction('after_setup_theme', $pluginPublic, 'setupCron');
    }

    private function defineConstants()
    {
        if (!defined("IMAGE_PROCESS_URL")) {
            define("IMAGE_PROCESS_URL", "https://process.fasad.eu/rimage.php?url=");
        }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     */
    public function run()
    {
        $this->loader->run();
    }

    public static function log($message, $params, $data = [])
    {
        if (!empty($params['verbose'])) {
            $preMessage = isset($params['uuid']) ? $params['uuid'] . ': ' : '';
            $message    = $preMessage . $message;
            do_action_ref_array('prek_log_verbose_message', [$message, $params['verbose'], $data]);
        }
    }

}

$plugin = new FasadBridge();
$plugin->run();


