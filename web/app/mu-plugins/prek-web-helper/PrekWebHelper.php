<?php

namespace PrekWebHelper;

use PrekWebHelper\Includes\{Common, Form, Image, Loader, PrekWebHelperHandler, Helpers, Cookies};

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
 * Plugin Name:       Prek Web Helper
 * Plugin URI:        https://prek.se/
 * Description:       Helper functions for PREK
 * Version:           1.25.1
 * Author:            PREK
 * Author URI:        https://prek.se/
 * License:           MIT License
 * Text Domain:       prek-web-helper
 */
class PrekWebHelper
{
    protected static $instance;

    /** @var PrekWebHelperHandler */
    public $handler;

    /** @var Common */
    public $common;

    /** @var Image */
    public $image;

    /** @var Form */
    public $form;

    /** @var Helpers */
    public $helpers;

    /** @var Cookies */
    public $cookies;

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
     * PrekWebHelper constructor
     */

    public function __construct()
    {
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }
        $this->setPluginData();
        $this->loader = new Loader();

        $this->handler = new PrekWebHelperHandler($this->loader);
        $this->handler->run();

        $this->common = new Common($this->loader);
        $this->common->run();

        $this->image = new Image($this->loader);

        $this->form = new Form($this->loader);
        $this->form->run();

        $this->cookies = new Cookies($this->loader);
        $this->cookies->run();

        self::$instance = $this;
    }

    public function setPluginData()
    {
        $data             = get_plugin_data(__FILE__, false, false);
        $this->version    = $data['Version'];
        $this->pluginName = $data['Name'];
        $this->textDomain = $data['TextDomain'];
    }

    public static function getInstance()
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
        $this->loader->run();
    }

    public function getPluginData()
    {
        $data             = new \stdClass();
        $data->version    = $this->version;
        $data->pluginName = $this->pluginName;
        $data->textDomain = $this->textDomain;
        return $data;
    }

}

$plugin = new PrekWebHelper();
$plugin->run();

