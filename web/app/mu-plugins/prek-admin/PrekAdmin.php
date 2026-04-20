<?php

namespace PrekAdmin;

use PrekAdmin\Includes\{Loader, PrekAdminHandler};

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
 * Plugin Name:       Prek Admin
 * Plugin URI:        https://prek.se/
 * Description:       Admin functions for PREK
 * Version:           1.1.1
 * Author:            PREK
 * Author URI:        https://prek.se/
 * License:           MIT License
 * Text Domain:       prek-admin
 */
class PrekAdmin
{
    protected static $instance;
    /** @var PrekWebHelperHandler */
    public $handler;
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
    protected $textDomain;

    /**
     * PrekLog constructor
     */

    public function __construct()
    {
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }
        $this->setPluginData();
        $this->loader = new Loader();
        $this->handler = new PrekAdminHandler($this->loader);

        self::$instance = $this;
    }

    public function setPluginData()
    {
        $data             = get_plugin_data(__FILE__, false, false);
        $this->version    = $data['Version'];
        $this->pluginName = $data['Name'];
        $this->textDomain = $data['TextDomain'];
    }

    public static function getInstance(): PrekAdmin
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     */
    public function run()
    {
        $this->handler->run();
        $this->loader->run();
    }

    public function getPluginData(): \stdClass
    {
        $data             = new \stdClass();
        $data->version    = $this->version;
        $data->pluginName = $this->pluginName;
        $data->textDomain = $this->textDomain;
        return $data;
    }
}

$plugin = new PrekAdmin();
$plugin->run();

