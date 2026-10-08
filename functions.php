<?php

/**
 * Homeo Child Theme
 */

function aaronz_child_enqueue_styles() {
    wp_enqueue_style(
        'homeo-parent-style',
        get_template_directory_uri() . '/style.css'
    );

    wp_enqueue_style(
        'homeo-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('homeo-parent-style'),
        wp_get_theme()->get('Version')
    );
}

add_action('wp_enqueue_scripts', 'aaronz_child_enqueue_styles');