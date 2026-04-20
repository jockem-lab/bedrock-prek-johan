<?php

namespace FasadSearchCriterias;

use FasadSearchCriterias\Includes\{Loader, Handler, SyncService, Repository, Form};

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
 * Plugin Name:       Fasad Search Criterias
 * Plugin URI:        https://prek.se/
 * Description:       Enable forms with search criterias from Fasad
 * Version:           1.0.0
 * Author:            PREK
 * Author URI:        https://prek.se/
 * License:           MIT License
 * Text Domain:       fasad-search-criterias
 */
class FasadSearchCriterias
{
    protected static $instance;

    /** @var Handler */
    public $handler;

    /** @var SyncService */
    public $syncService;

    /** @var Repository */
    public $repository;

    /** @var Form */
    public $form;

    /** @var Helpers */
    //public $helpers;

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
    protected $metaPrefix = 'fasad_search_criterias_';

    const FASAD_SEARCH_OBJECT_TYPE_POST_TYPE = "fasad_searchtypes";
    const FASAD_SEARCH_CRITERIA_POST_TYPE = "fasad_searchcriteria";
    const FASAD_SEARCH_DISTRICT_POST_TYPE = "fasad_searchdistrict";
    const FASAD_SEARCH_OBJECT_TYPE_NAME = "objecttypes";
    const FASAD_SEARCH_CRITERIA_NAME = "criterias";
    const FASAD_SEARCH_DISTRICT_NAME = "districts";
    const FASAD_SEARCH_PRICE_NAME = "price";
    const FASAD_SEARCH_ROOMS_NAME = "rooms";
    const FASAD_SEARCH_SIZE_NAME = "size";

    /**
     * FasadSearchCriterias constructor
     */
    public function __construct()
    {
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }
        $this->setPluginData();
        $this->loader = new Loader();

        $this->handler = new Handler($this->loader, $this->textDomain);
        $this->handler->run();

        $this->syncService = new SyncService($this->loader, $this->textDomain, $this->metaPrefix);

        $this->repository = new Repository($this->loader, $this->metaPrefix);

        $this->form = new Form($this->loader, $this->repository, $this->metaPrefix);
        $this->form->run();

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

    public static function fieldNames() : array
    {
        return [
            FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_POST_TYPE => FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_NAME,
            FasadSearchCriterias::FASAD_SEARCH_CRITERIA_POST_TYPE    => FasadSearchCriterias::FASAD_SEARCH_CRITERIA_NAME,
            FasadSearchCriterias::FASAD_SEARCH_DISTRICT_POST_TYPE    => FasadSearchCriterias::FASAD_SEARCH_DISTRICT_NAME,
            FasadSearchCriterias::FASAD_SEARCH_PRICE_NAME            => FasadSearchCriterias::FASAD_SEARCH_PRICE_NAME,
            FasadSearchCriterias::FASAD_SEARCH_ROOMS_NAME            => FasadSearchCriterias::FASAD_SEARCH_ROOMS_NAME,
            FasadSearchCriterias::FASAD_SEARCH_SIZE_NAME             => FasadSearchCriterias::FASAD_SEARCH_SIZE_NAME
        ];
    }

}

$plugin = new FasadSearchCriterias();
$plugin->run();

