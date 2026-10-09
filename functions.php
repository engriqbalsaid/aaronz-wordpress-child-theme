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

/**
 * Landing page slider images (logos/backgrounds) are hard-coded as
 * /images/*.webp, a folder that exists on production but not on staging.
 * On staging, point those URLs at the production copy so they load.
 * Remove once the /images folder is uploaded to the staging web root.
 */
function aaronz_fix_staging_image_urls($html) {
    $prod = 'https://www.aaronz.co/images/';

    return preg_replace(
        array(
            '#https?://(?:www\.)?staging20\.aaronz\.co/images/#i',
            '#(\ssrc=["\'])images/#i',
        ),
        array(
            $prod,
            '$1' . $prod,
        ),
        $html
    );
}

function aaronz_start_staging_image_fix() {
    if (is_admin() || is_feed()) {
        return;
    }
    if (false === stripos(home_url(), 'staging20.aaronz.co')) {
        return;
    }
    ob_start('aaronz_fix_staging_image_urls');
}

add_action('template_redirect', 'aaronz_start_staging_image_fix', 0);


/**
 * Easy Property Listings sets an epl_wp_session cookie on every request.
 * A Set-Cookie header stops the SiteGround dynamic cache from storing the
 * page, so anonymous home page views were never cached (~1.8s TTFB).
 * Drop the cookie for logged-out visitors on the home page only.
 */
function aaronz_drop_epl_session_cookie_on_home() {
    if (is_user_logged_in() || is_admin() || !is_front_page()) {
        return;
    }

    header_remove('Set-Cookie');
}

add_action('send_headers', 'aaronz_drop_epl_session_cookie_on_home', 99);


/**
 * Hide the mobile off-canvas menu until the mmenu script has initialised it
 * (it adds .mm-menu). Printed inline because the child style.css is not
 * included in SiteGround's combined stylesheet, and deferred JS otherwise
 * lets the raw menu list flash on mobile.
 */
function aaronz_hide_uninitialised_mobile_menu() {
    echo '<style id="aaronz-offcanvas-fix">#navbar-offcanvas:not(.mm-menu){display:none}</style>' . "\n";
}

add_action('wp_head', 'aaronz_hide_uninitialised_mobile_menu', 1);
