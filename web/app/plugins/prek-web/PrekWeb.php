<?php

namespace PrekWeb;

use PrekWeb\Includes\{Admin, Coworker, Helpers, Image, Loader, Common, Options, Fasad, Translations};

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
 * Plugin Name:       Prek Web
 * Plugin URI:        https://prek.se/
 * Description:       Various nice things for the websites that we build.
 * Version:           2.37.0
 * Author:            Dennis Germundal, Andreas Lundgren
 * Author URI:        https://prek.se/
 * License:           Apache
 * Text Domain:       prek-web
 */
class PrekWeb
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /** @var Common */
    public $common;

    /** @var Options */
    public $options;

    /** @var Admin */
    public $admin;

    /** @var Helpers */
    public $helpers;

    /** @var Fasad */
    public $fasad;

    /** @var Image */
    public $image;

    /** @var Coworker  */
    public $coworker;

    /** @var Translations */
    public $translations;

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

    protected static $instance;

    /**
     * FasadBridge constructor.
     */
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        // If this file is called directly, abort.
        if (!defined('WPINC')) {
            die;
        }

        if ( ! function_exists( 'is_plugin_active' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        $this->setPluginData();

        $this->defineConstants();

        $this->loader = new Loader();

        $this->options = new Options($this->loader);
        $this->options->run();

        $this->common  = new Common($this->loader, $this->options);
        $this->common->run();

        $this->admin  = new Admin($this->loader, $this->options);
        $this->admin->run();

        if ($this->check_for_fasad()) {
            $this->fasad = new Fasad($this->loader, $this->options);
            $this->fasad->run();
            $this->image = new Image($this->loader, $this->options);
            $this->image->run();
            $this->translations = new Translations($this->loader, $this->options);
            $this->translations->run();
            $this->coworker = new Coworker($this->loader, $this->options);
            $this->coworker->run();
        }

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
        if (!$this->check_for_acf()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('ACF krävs för att aktivera detta plugin', $this->textdomain), '', array('back_link' => true));
        }
    }

    private function defineConstants()
    {
        /*if (!defined("IMAGE_PROCESS_URL")) {
            define("IMAGE_PROCESS_URL", "https://process.fasad.eu/rimage.php?url=");
        }*/
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     */
    public function run()
    {
        $this->loader->run();
    }

    private function check_for_acf() {
        return \is_plugin_active('advanced-custom-fields/acf.php') ||
            \is_plugin_active('advanced-custom-fields-pro/acf.php');
    }

    private function check_for_fasad() {
        return \is_plugin_active('fasad-api-connect/FasadApiConnect.php') || class_exists('FasadApiConnect\FasadApiConnect');
    }

    public function setPluginData()
    {
        $data = get_plugin_data(__FILE__, false, false);
        $this->version    = $data['Version'];
        $this->pluginName = $data['Name'];
        $this->textDomain = $data['TextDomain'];
    }

    public function getPluginData()
    {
        $data = new \stdClass();
        $data->version    = $this->version;
        $data->pluginName = $this->pluginName;
        $data->textDomain = $this->textDomain;
        return $data;
    }

}

$prekWeb = new PrekWeb();
$prekWeb->run();
