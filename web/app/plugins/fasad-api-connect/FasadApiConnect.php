<?php

namespace FasadApiConnect;

use FasadApiConnect\Includes\AdminSettings;
use FasadApiConnect\Includes\Loader;

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
 * Plugin Name:       Fasad API Connect
 * Plugin URI:        https://prek.se/
 * Description:       Simplify connection to Fasad API
 * Version:           3.13.0
 * Author:            Andreas Lundgren
 * Author URI:        https://prek.se/
 * License:           Apache
 * Text Domain:       fasad-api-connect
 */
class FasadApiConnect
{
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
     * The current version of the plugin.
     *
     * @var string $version The current version of the plugin.
     */
    protected $version;

    /**
     * FasadApi constructor.
     */
    public function __construct()
    {
        /**
         * Register deactivation hook, deactivating dependent plugins
         */
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        // If this file is called directly, abort.
        if ( ! defined('WPINC')) {
            die;
        }

        /**
         * Current plugin version.
         * Start at version 1.0.0 and use SemVer - https://semver.org
         * Rename this for your plugin and update it as you release new versions.
         */
        $this->version    = "2.2.0";
        $this->pluginName = "fasad-api-connect";

        $this->initLoader();
        $this->defineAdminHooks();
        $this->defineConstants();
    }

    /**
     * Deactivate plugins that depends on this plugin
     */
    public function deactivate()
    {
        if (is_plugin_active('fasad-bridge/FasadBridge.php')) {
            add_action(
                'update_option_active_plugins',
                function () {
                    deactivate_plugins('fasad-bridge/FasadBridge.php');
                }
            );
        }
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
        $pluginAdmin = new AdminSettings($this->pluginName, $this->version);

        //$this->loader->addAction("admin_enqueue_scripts", $pluginAdmin, "enqueueStyles");
        //$this->loader->addAction("admin_enqueue_scripts", $pluginAdmin, "enqueueScripts");
        $this->loader->addAction("admin_menu", $pluginAdmin, "addPluginPage");
        $this->loader->addAction("admin_init", $pluginAdmin, "addSettings");
        $this->loader->addAction("update_option_" . $this->pluginName . "-options", $pluginAdmin, "clearAccessToken", 10, 3);
    }

    private function defineConstants()
    {
        define("API_URL", "https://api.fasad.eu");
    }

    public function run()
    {
        $this->loader->run();
    }
}

$plugin = new FasadApiConnect();
$plugin->run();
