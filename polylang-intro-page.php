<?php

require __dir__ . '/is_front_page_workaround.php';

/*
Plugin Name: Polylang Extension: Intro
Description: Adds support for an intro / language selection page to Polylang
Author:      strarsis
Version:     1.0.2
Text Domain: polylang-intro-page
Domain Path: /lang/
*/
__('Adds support for an intro / language selection page to Polylang', 'polylang-intro-page');

add_action('plugins_loaded', 'pll_ext_intro_page_load_textdomain');
function pll_ext_intro_page_load_textdomain() {
    load_plugin_textdomain( 'polylang-intro-page', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}


// Clear permalinks (e.g. just by updating permalinks settings).
// Recommended: Disable Polylang option 'Hide URL language information for default language'.


function pll_ext_intro_page_activate() {
    flush_rewrite_rules();
    add_filter('wpseo_enable_xml_sitemap_transient_caching', '__return_false');
}
register_activation_hook( __FILE__, 'pll_ext_intro_page_activate' );
add_filter('wpseo_enable_xml_sitemap_transient_caching', '__return_false');


// UI extra

// Show intro page indicator
function pll_ext_intro_page_state( $post_states, $post ) {
    global $post;
    if( $post->post_name !== 'intro' ) return $post_states;

    $post_states[] = __( 'Intro page', 'polylang-intro-page' );
    return $post_states;
}
if ( is_admin() ) add_filter( 'display_post_states', 'pll_ext_intro_page_state', 10, 2 );


// Intro page links to front page without language
function pll_ext_intro_page_link( $url ) {
    global $post;
    if ( $post->post_name !== 'intro' ) return $url;

    $frontpage_id = get_option( 'page_on_front' );
    $url = get_permalink($frontpage_id);
    exit;
    return $url;
}
add_filter( 'post_link', 'pll_ext_intro_page_link', 10, 1 );


// Language switcher shortcode (to front page) as shortcode
function pll_ext_intro_flags_shortcode() {
    $frontpage_id = get_option( 'page_on_front' );

    ob_start();
    pll_the_languages(
        array( 'show_flags' => 1,
               'show_names' => 1,
               'post_id'    => $frontpage_id, // front page post ID
             )
    );
    $flags = ob_get_clean();
    return '<ul class="polylang-flags intro-flags">' . $flags . '</ul>';
}
add_shortcode('POLYLANG_SWITCHER_INTRO', 'pll_ext_intro_flags_shortcode');




// Primary query (change to intro page)
$is_intro_page = false;

function pll_ext_intro_query($query) {
    global $is_intro_page;

    if ( !($query->is_front_page()) ) return;
        // (must use workaround here for is_front_page() in query related actions)
 
    if ( !$query->is_main_query() ) return;
    if ( !isset($query->query) )    return;
    remove_action('pre_get_posts', 'pll_ext_intro_query');


    if ( !empty($query->query['lang']) ) return;

    // intro page by slug 'intro'
    $query->query['page']     = '';
    $query->query['pagename'] = 'intro';

    $tempQuery = new \WP_Query((array) json_decode(json_encode($query->query), true));

    if ( !is_a($tempQuery->queried_object, 'WP_Post') ) return;
    if ( !$query->is_main_query() ) return;

    $is_intro_page = true;


    global $wp_query;
    $wp_query = $tempQuery;

    $query = $tempQuery;

    add_action('pre_get_posts', 'pll_ext_intro_query');
}
if ( !is_admin() ) add_action('pre_get_posts', 'pll_ext_intro_query');


// Prevent redirections from / (home),
// in case Polylang language auto-redirect is enabled
if ( !is_admin() ) add_filter('pll_redirect_home', '__return_false');

// also prevent canonical redirects for newly queried intro page
if ( !is_admin() ) add_filter('pll_check_canonical_url', function() {
    global $is_intro_page;

    if($is_intro_page) return false;
});


// Redirect to front page when requesting the intro page own permalink
// prevents/removes indexing of intro page as separate page
function pll_ext_intro_page_redirect() {
    global $is_intro_page, $post;

    if ( $is_intro_page ) return;
    if ( $post->post_name !== 'intro' ) return;

    wp_redirect(  home_url( '/' )  );
    die;
}
add_action( 'template_redirect', 'pll_ext_intro_page_redirect' );

// Exclude intro page (as separate page) also from (Yoast) sitemap
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function () {
    $intro_page = get_page_by_path( 'intro' );
    return array( $intro_page->ID );
} );


// TODO: pll_translation_url instead supressing redirect?


// Add intro body class
add_filter( 'body_class', function( $classes ) {
    global $is_intro_page;
    if ( !$is_intro_page ) return $classes;

    $classes[] = 'intro';
    return $classes;
}, 10, 1 );
