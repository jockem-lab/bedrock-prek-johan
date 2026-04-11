<?php
/**
 * Configuration overrides for WP_ENV === 'production'
 */

use Roots\WPConfig\Config;

ini_set('display_errors', 0);

Config::define('WP_DEBUG', false);
Config::define('WP_DEBUG_DISPLAY', false);
Config::define('WP_DEBUG_LOG', false);
Config::define('WPLANG', 'sv_SE');
Config::define('SCRIPT_DEBUG', false);
Config::define('WP_SENTRY_PHP_DSN', 'http://0f70cbbdc9f54c80ad2e8930b1f7ae61@sentry.prek.srv:9000/7');
Config::define('COOKIE_DOMAIN', false);
// Disable the plugin and theme file editor in the admin
Config::define('DISALLOW_FILE_EDIT', true);

/** Disable all file modifications including updates and update notifications */
Config::define('DISALLOW_FILE_MODS', true);
Config::define('FORCE_SSL_ADMIN', true);
if (!empty($_SERVER['HTTP_X_SCHEME']) && strpos($_SERVER['HTTP_X_SCHEME'], 'https') !== false){
    $_SERVER['HTTPS']='on';
}