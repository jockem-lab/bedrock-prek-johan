<?php
add_action('plugins_loaded', function() {
    $active = get_option('active_plugins');
    $acf = function_exists('get_field');
    error_log('ACTIVE_PLUGINS: ' . print_r($active, true));
    error_log('ACF_LOADED: ' . ($acf ? 'YES' : 'NO'));
}, 1);
