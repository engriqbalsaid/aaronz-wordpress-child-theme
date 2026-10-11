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


/**
 * Site-wide type scale modelled on bayut.com: body, menu and buttons 14px,
 * H1 40px, H2 24px, H3 18px, H4 16px (smaller on phones). Elementor sets sizes
 * per widget, hence !important. Page-specific rules (e.g. the home page) are
 * more specific and still win. The home page "4.9" rating is left as designed.
 */
function aaronz_global_type_scale() {
    echo '<style id="aaronz-type-scale">
body{font-size:14px!important;line-height:1.6}
body p,body li,body td,body th,body label,body input,body select,body textarea,body button,body dt,body dd,body blockquote{font-size:14px!important}
body #primary-menu>li>a,body .navbar-nav>li>a{font-size:14px!important}
body h1:not(.elementor-element-9f60759 *),body h1.elementor-heading-title:not(.elementor-element-9f60759 *){font-size:40px!important;line-height:1.25!important;font-weight:700!important}
body h2{font-size:24px!important;line-height:1.35!important;font-weight:700!important}
body h3{font-size:18px!important;line-height:1.4!important;font-weight:700!important}
body h4{font-size:16px!important;line-height:1.4!important;font-weight:700!important}
body h5,body h6{font-size:14px!important;line-height:1.4!important;font-weight:700!important}
@media(max-width:767px){
body h1:not(.elementor-element-9f60759 *){font-size:28px!important}
body h2{font-size:20px!important}
body h3{font-size:16px!important}
body h4{font-size:15px!important}
}
</style>' . "\n";
}

add_action('wp_head', 'aaronz_global_type_scale', 10);


/**
 * Property listing cards (WP RealEstate "property-grid-v2") restyled like a
 * Bayut card: rounded white card, rounded image, bold title, type and location,
 * a grey price panel, then beds/baths and the agent row.
 * The theme wraps the image, price and details in nested containers, so those
 * wrappers use display:contents and the pieces are re-ordered with flexbox.
 */
function aaronz_listing_cards() {
    echo '<style id="aaronz-listing-cards">
body article.property-grid-v2{position:relative;display:flex!important;flex-direction:column;background:#fff;border:0!important;border-radius:16px;padding:14px;box-shadow:0 2px 14px rgba(0,0,0,.09);overflow:hidden;height:100%}
body article.property-grid-v2 .top-info,body article.property-grid-v2 .property-thumbnail-wrapper,body article.property-grid-v2 .property-information{display:contents!important}
body article.property-grid-v2 .image-thumbnail{order:1;display:block;border-radius:12px;overflow:hidden;margin:0 0 14px}
body article.property-grid-v2 .image-thumbnail img{width:100%;display:block;border-radius:12px}
body article.property-grid-v2 .top-label{position:absolute!important;top:24px;left:24px;right:auto;bottom:auto;z-index:2}
body article.property-grid-v2 .top-label a{border-radius:6px;font-size:12px!important;padding:3px 10px}
body article.property-grid-v2 h2.property-title{order:2;font-size:16px!important;line-height:1.4!important;font-weight:700!important;margin:0 0 6px!important;color:#1b1b1b}
body article.property-grid-v2 h2.property-title a{color:inherit}
body article.property-grid-v2 .property-type{order:3;margin:0 0 8px;font-size:14px;color:#3a3a3a}
body article.property-grid-v2 .property-type a{color:inherit}
body article.property-grid-v2 .property-location{order:4;margin:0 0 12px;font-size:14px;color:#3a3a3a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
body article.property-grid-v2 .property-location a{color:inherit}
body article.property-grid-v2 .bottom-label{order:5;position:static!important;display:block!important;background:#f3f3f3!important;border-radius:10px;padding:12px 10px;margin:0 0 10px;text-align:center}
body article.property-grid-v2 .bottom-label .property-price{color:#245f6d!important;font-size:16px!important;font-weight:700!important}
body article.property-grid-v2 .bottom-label .property-price *{color:inherit!important;font-size:inherit!important;font-weight:inherit!important}
body article.property-grid-v2 .property-metas{order:6;margin:0 0 12px;font-size:14px;color:#3a3a3a;gap:14px}
body article.property-grid-v2 .property-metas-bottom{order:7;margin-top:auto;padding-top:12px;border-top:1px solid #ececec;font-size:12px}
body article.property-grid-v2 .property-location i{display:inline-block!important;width:14px;height:16px;margin-right:6px;vertical-align:-3px;background:url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23333\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z\'/><circle cx=\'12\' cy=\'10\' r=\'2.5\'/></svg>") no-repeat center/contain}
body article.property-grid-v2 .property-location i:before{content:none!important}
body .widget-properties .slick-track{display:flex!important}
body .widget-properties .slick-slide{height:auto!important}
body .widget-properties .slick-slide>div,body .widget-properties .slick-slide .item{height:100%}
body .widget-properties .slick-list{padding:6px 0 10px!important}
@media(max-width:767px){body article.property-grid-v2{padding:12px}}
</style>' . "\n";
}

add_action('wp_head', 'aaronz_listing_cards', 30);
