<?php

namespace FasadApiConnect\Includes;

class AdminSettings
{
    /**
     * The ID of this plugin.
     *
     * @var string $pluginName The ID of this plugin.
     */
    private $pluginName;

    /**
     * @var mixed Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * The version of this plugin.
     *
     * @var string $version The current version of this plugin.
     */
    private $version;

    const SETTINGS_OPTION_NAME  = "fasad-api-connect-options"; // The name of the options
    const SETTINGS_OPTION_GROUP = "settings-group"; // The name of the option group

    const SETTINGS_GENERAL_SECTION = "general-section";

    const SETTINGS_GENERAL_SLUG_NAME = "general-settings";

    /**
     * Initialize the class and set its properties.
     *
     * @param string $pluginName The name of this plugin.
     * @param string $version    The version of this plugin.
     */
    public function __construct($pluginName, $version)
    {
        $this->pluginName = $pluginName;
        $this->version    = $version;
    }

    /**
     * Add the plugin options page
     */
    public function addPluginPage()
    {
        // This page will be under "Settings"
        add_menu_page(
            __("FasAd API", "fasad-api-connect"),
            __("FasAd API", "fasad-api-connect"),
            'manage_options',
            $this->pluginName,
            '',
            plugins_url('', __DIR__) . "/assets/img/fasad-icon-16.png",
            23
        );

        add_submenu_page(
            $this->pluginName,
            __("Inställningar", "fasad-api-connect"),
            __("Inställningar", "fasad-api-connect"),
            'manage_options',
            $this->pluginName . '_settings',
            array($this, 'createAdminPage'),
            4
        );

        //Remove placeholder for menu
        remove_submenu_page($this->pluginName, $this->pluginName);
    }

    /**
     * The template for admin setting
     */
    public function createAdminPage()
    {
        require_once (dirname(__DIR__)) . "/assets/partials/admin-settings.php";
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueueStyles()
    {
        wp_enqueue_style($this->pluginName, plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueueScripts()
    {
        wp_enqueue_script($this->pluginName, plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin.js', array(), $this->version, 'all');
    }

    /**
     * Register the settings for admin area
     */
    public function addSettings()
    {
        // Register the settings
        register_setting(
            self::SETTINGS_OPTION_GROUP,
            self::SETTINGS_OPTION_NAME,
            array($this, 'sanitize')
        );

        $this->addGeneralSettings();
    }

    public function addGeneralSettings()
    {
        add_settings_section(
            self::SETTINGS_GENERAL_SECTION,
            __("Inställningar", "fasad-api-connect"),
            array($this, 'printGeneralSectionInfo'),
            self::SETTINGS_GENERAL_SLUG_NAME
        );

        add_settings_field(
            'client-secret',
            __("FasAd API-nyckel", "fasad-api-connect"),
            array($this, 'clientSecretCallback'),
            self::SETTINGS_GENERAL_SLUG_NAME,
            self::SETTINGS_GENERAL_SECTION
        );

        add_settings_field(
            'client-password',
            __("FasAd API-lösenord", "fasad-api-connect"),
            array($this, 'clientPasswordCallback'),
            self::SETTINGS_GENERAL_SLUG_NAME,
            self::SETTINGS_GENERAL_SECTION
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();

        if (isset($input['client-secret'])) {
            $new_input['client-secret'] = sanitize_text_field($input['client-secret']);
        }

        if (isset($input['client-password'])) {
            $new_input['client-password'] = sanitize_text_field($input['client-password']);
        }

        return $new_input;
    }

    /**
     * Print the General section text
     */
    public function printGeneralSectionInfo()
    {
        //print __("Placeholder printGeneralSectionInfo", "fasad-api-connect");
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function clientSecretCallback()
    {
        printf(
            '<input type="text" id="client-secret" name="%s[client-secret]" value="%s" size="50" %s />',
            self::SETTINGS_OPTION_NAME,
            isset($this->options['client-secret']) ? esc_attr($this->options['client-secret']) : '',
            (defined('WP_ENV') && WP_ENV == 'production') ? 'disabled="disabled"' : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function clientPasswordCallback()
    {
        // TODO: change input type to password?
        printf(
            '<input type="password" id="client-password" name="%s[client-password]" value="%s" size="50" %s />',
            self::SETTINGS_OPTION_NAME,
            isset($this->options['client-password']) ? esc_attr($this->options['client-password']) : '',
            (defined('WP_ENV') && WP_ENV == 'production') ? 'disabled="disabled"' : ''
        );
    }

    public function clearAccessToken($option_name, $old_value, $value)
    {
        $cacheToken = new CacheTokenHandler();
        $cacheToken->delete();
    }
}