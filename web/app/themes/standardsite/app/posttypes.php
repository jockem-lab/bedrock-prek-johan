<?php


namespace App;

const POST_TYPE_PUFF = 'puff';
/**
 * Register posttype (puff)
 */
add_action(
    'init',
    function () {
        $labels = [
            'name' => __('Puffar', 'sage'),
            'singular_name' => __('Puff', 'sage'),
            'menu_name' => __('Puffar', 'sage'),
            'add_new' => __('Skapa ny', 'sage'),
        ];

        $args = [
            'labels' => $labels,
            'menu_icon' => 'dashicons-pressthis',
            'description' => '',
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => array('title'),
            'has_archive' => false,
            'menu_position' => null,
            'query_var' => true,
            'can_export' => true
        ];

        register_post_type(POST_TYPE_PUFF, $args);
    }

);
