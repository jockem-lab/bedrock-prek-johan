<?php

namespace PrekWeb\Includes;

class Cache
{

    protected $loader;
    protected $options;

    public function __construct(\PrekWeb\Includes\Loader $loader, \PrekWeb\Includes\Options $options)
    {
        $this->loader = $loader;
        $this->options = $options;
    }

    public function run()
    {
    }

    function objects_synced($importedObjects, $deletedObjects)
    {
        if (class_exists('WP_REST_Cache')) {
            \WP_REST_Cache::empty_cache();
        }

        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
    }
    //add_action('fasad_import_objects_complete', __NAMESPACE__ . '\\objects_synced', 10, 2);

    function w3_flush_cache()
    {
        if (function_exists('w3tc_flush_all')) {
            try {
                w3tc_flush_all();
                //exec('wp w3-total-cache flush all', $result);
                //$o = \W3TC\Dispatcher::component( 'CacheFlush' );
                //$result = $o->flush_all( [] );
            } catch (\Exception $e) {
            }
        }
    }

    // Empty cache every night (or at first visit after midnight)
    function w3tc_cache_flush()
    {
        if (!wp_next_scheduled('w3_flush_cache')) {
            $ve = get_option('gmt_offset') > 0 ? '-' : '+';
            wp_schedule_event(
                strtotime('00:00 tomorrow ' . $ve . get_option('gmt_offset') . ' HOURS'),
                'daily',
                'w3_flush_cache'
            );
        }
    }

    //add_action('wp', __NAMESPACE__ . '\\w3tc_cache_flush');
    //add_action('w3_flush_cache', __NAMESPACE__ . '\\w3_flush_cache');


    function assets()
    {
        wp_add_inline_script('contact-form-7', "if(wpcf7) {wpcf7.cached = 0;}");
    }
    //add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\assets', 100);

}