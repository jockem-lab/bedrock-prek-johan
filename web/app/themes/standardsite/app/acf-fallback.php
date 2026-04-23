<?php
// Global ACF fallback - ingen namespace
if (!function_exists('get_field')) {
    function get_field($field, $post_id = false, $format_value = true) {
        return null;
    }
}
if (!function_exists('get_fields')) {
    function get_fields($post_id = false, $format_value = true) {
        return [];
    }
}
if (!function_exists('have_rows')) {
    function have_rows($field, $post_id = false) {
        return false;
    }
}
if (!function_exists('get_sub_field')) {
    function get_sub_field($field, $format_value = true) {
        return null;
    }
}
