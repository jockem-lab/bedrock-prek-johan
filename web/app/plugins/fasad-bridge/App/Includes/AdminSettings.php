<?php

namespace FasadBridge\Includes;

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

    /**
     * The version of this plugin.
     *
     * @var string $fasadApiConnectName The name of FasAd API plugin.
     */
    private $fasadApiConnectName;

    const SETTINGS_OPTION_NAME = "fasad-bridge-options"; // The name of the options
    const SETTINGS_OPTION_GROUP = "settings-group"; // The name of the option group

    const SETTINGS_SYNCHRONIZE_SECTION = "synchronize-section";
    const SETTINGS_CLEAR_SECTION = "clear-section";

    const SETTINGS_SYNC_SLUG_NAME = "sync-settings";
    const SETTINGS_CLEAR_SLUG_NAME = "clear-settings";

    /**
     * Initialize the class and set its properties.
     *
     * @param string $pluginName   The name of this plugin.
     * @param string $version      The version of this plugin.
     * @param string $fasadApiConnectName The name of FasAd API Connect plugin.
     */
    public function __construct($pluginName, $version, $fasadApiConnectName)
    {
        $this->pluginName = $pluginName;
        $this->version = $version;
        $this->fasadApiConnectName = $fasadApiConnectName;
    }

    /**
     * Add the plugin options page
     */
    public function addPluginPage()
    {
        // This page will be under "Settings"
        add_submenu_page(
            $this->fasadApiConnectName,
            "FasAd Bridge",
            "FasAd Bridge",
            'manage_options',
            $this->pluginName,
            array($this, 'createAdminPage'),
            0
        );
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
        $this->addSyncSettings();
    }

    public function addSyncSettings()
    {
        add_settings_section(
            self::SETTINGS_SYNCHRONIZE_SECTION,
            __("Synkronisering", "fasad-bridge"),
            array($this, 'printSyncSection'),
            self::SETTINGS_SYNC_SLUG_NAME
        );

        // Clear section
        add_settings_section(
            self::SETTINGS_CLEAR_SECTION,
            __("Rensa", "fasad-bridge"),
            array($this, 'printClearSectionInfo'),
            self::SETTINGS_CLEAR_SLUG_NAME
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

        //if (isset($input['client-secret'])) {
        //    $new_input['client-secret'] = sanitize_text_field($input['client-secret']);
        //}
        //
        //if (isset($input['client-password'])) {
        //    $new_input['client-password'] = sanitize_text_field($input['client-password']);
        //}

        return $new_input;
    }

    /**
     * Print the Sync Section iframe
     */
    public function printSyncSection()
    {
        print __("Hämta data från FasAd API och spara till WordPress", "fasad-bridge");
        ?>
        <iframe id="sync-log"></iframe>
        <div class="synchronizing">
            <span class="spinner" id="sync_spinner"><?php _e("Synkroniserar med FasAd, vänta...", "fasad-bridge") ?></span>
        </div>
        <?php

    }

    /**
     * Print the Clear section text
     */
    public function printClearSectionInfo()
    {
        print(wp_nonce_field('clear-listings', '_wpfasadnonce'));
        print __("Tar bort FasAd poster", "fasad-bridge"); ?>
        <div class="clearing">
            <span class="spinner" id="clear_spinner"><?php _e("Rensar, vänta...", "fasad-bridge"); ?></span>
        </div>
        <?php

    }

    /**
     * Get the settings option array and print one of its values
     */
    public function clientSecretCallback()
    {
        // TODO: change input type to password?
        printf(
            '<input type="text" id="client-secret" name="%s[client-secret]" value="%s" size="50" />',
            self::SETTINGS_OPTION_NAME,
            isset($this->options['client-secret']) ? esc_attr($this->options['client-secret']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function clientPasswordCallback()
    {
        // TODO: change input type to password?
        printf(
            '<input type="text" id="client-password" name="%s[client-password]" value="%s" size="50" />',
            self::SETTINGS_OPTION_NAME,
            isset($this->options['client-password']) ? esc_attr($this->options['client-password']) : ''
        );
    }

    /**
     * Sync button
     */
    public function syncButtonCallback()
    {
        printf(
            '<button type="button" class="button button-primary" id="sync" name="%s[sync]">Synkronisera</button>',
            self::SETTINGS_OPTION_NAME
        );
    }

}