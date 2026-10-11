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


/**
 * Home page (/home/, page ID 3191) heading sizes modelled on bayut.com:
 * Lato font, menu 14px, hero H1 40px, hero subtitle 20px, section titles
 * 24px, card titles 16px, body text 14px.
 * Scoped to this page only; Elementor sets sizes per widget, hence !important.
 */
function aaronz_home_typography() {
    if (!is_page(3191)) {
        return;
    }

    echo '<style id="aaronz-home-typography">
.page-id-3191 #primary-menu>li>a{font-size:14px!important}
.page-id-3191 .elementor-element-f34ea30 .elementor-heading-title{font-size:40px!important;line-height:1.25!important;font-weight:700!important}
.page-id-3191 .elementor-element-38ddc28 p{font-size:20px!important;line-height:1.3!important;font-weight:700!important}
.page-id-3191 h2.elementor-heading-title,.page-id-3191 h2.title{font-size:24px!important;line-height:1.4!important;font-weight:700!important}
.page-id-3191 h2.property-title,.page-id-3191 .elementor-image-box-title{font-size:16px!important;line-height:1.5!important;font-weight:700!important}
.page-id-3191 .elementor-image-box-description{font-size:14px!important;line-height:1.6!important}
.page-id-3191 button[type=submit]{font-size:14px!important}
@media(max-width:767px){
.page-id-3191 .elementor-element-f34ea30 .elementor-heading-title{font-size:28px!important}
.page-id-3191 .elementor-element-38ddc28 p{font-size:16px!important}
.page-id-3191 h2.elementor-heading-title,.page-id-3191 h2.title{font-size:20px!important}
}
</style>' . "\n";
}

add_action('wp_head', 'aaronz_home_typography', 20);


/**
 * Site-wide font: Lato, then Helvetica. Icon fonts (Font Awesome, eicons, EPL
 * and theme icons) are excluded so icons keep rendering.
 */
function aaronz_global_fonts() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;1,400&display=swap">' . "\n";

    echo '<style id="aaronz-global-fonts">
body,body :is(h1,h2,h3,h4,h5,h6,p,a,li,span,div,button,select,input,textarea,label,td,th,dt,dd,blockquote,strong,b,em):not([class*="icon"]):not(.fa):not(.fas):not(.far):not(.fab):not(.fal):not([class*="fa-"]):not([class*="eicon"]):not([class*="epl-"]):not([class*="glyph"]):not(#wpadminbar):not(#wpadminbar *){font-family:"Lato","Helvetica Neue",Helvetica,Arial,sans-serif!important}
</style>' . "\n";
}

add_action('wp_head', 'aaronz_global_fonts', 5);
