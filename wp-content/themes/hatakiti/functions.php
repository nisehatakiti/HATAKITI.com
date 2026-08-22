<?php
/**
 * HATAKITI theme bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HATAKITI_THEME_VERSION', '1.0.0' );

/**
 * Theme setup.
 */
function hatakiti_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'responsive-embeds' );

    register_nav_menus( array(
        'primary' => __( 'グローバルメニュー', 'hatakiti' ),
        'footer'  => __( 'フッターメニュー', 'hatakiti' ),
    ) );

    set_post_thumbnail_size( 800, 450, true );
    add_image_size( 'hatakiti-card', 480, 270, true );
}
add_action( 'after_setup_theme', 'hatakiti_setup' );

/**
 * Assets.
 */
function hatakiti_scripts() {
    wp_enqueue_style( 'google-fonts-noto-sans-jp', 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap', array(), null );
    wp_enqueue_style( 'hatakiti-style', get_stylesheet_uri(), array(), HATAKITI_THEME_VERSION );
    wp_enqueue_script( 'hatakiti-main', get_template_directory_uri() . '/assets/js/main.js', array(), HATAKITI_THEME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'hatakiti_scripts' );

/**
 * HATAKITI.com is a personal record site. Comments are not part of the blueprint
 * and are disabled site-wide to keep maintenance simple.
 */
function hatakiti_disable_comments_support() {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
}
add_action( 'init', 'hatakiti_disable_comments_support', 100 );
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );

/**
 * Include the custom post types in front-end keyword search, so 観劇記録 /
 * 映画記録 are actually reachable through 検索・アーカイブ (docs/02-SiteMap.md).
 */
function hatakiti_search_post_types( $query ) {
    if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        $query->set( 'post_type', array( 'post', 'page', 'theatre_record', 'film_record', 'activity_record' ) );
    }
}
add_action( 'pre_get_posts', 'hatakiti_search_post_types' );

/**
 * Includes.
 */
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/booklog.php';

/**
 * Fallback menu markup when no "primary" menu has been registered yet
 * in wp-admin. Mirrors the blueprint's fixed main navigation.
 */
function hatakiti_fallback_menu() {
    // StageArt is an independent site (docs/01-Vision.md §5); until its
    // URL is set in the Customizer, point at the home page's StageArt
    // teaser instead of a non-existent internal page.
    $stageart_url = hatakiti_get_stageart_url();
    if ( ! $stageart_url ) {
        $stageart_url = home_url( '/#stageart' );
    }

    $items = array(
        array( 'label' => 'HATAKITIとは', 'url' => home_url( '/about/' ) ),
        array( 'label' => '日々の所感', 'url' => home_url( '/category/nikki/' ) ),
        array( 'label' => '演劇について', 'url' => home_url( '/category/engeki/' ) ),
        array( 'label' => '観劇記録', 'url' => get_post_type_archive_link( 'theatre_record' ) ),
        array( 'label' => '映画記録', 'url' => get_post_type_archive_link( 'film_record' ) ),
        array( 'label' => 'StageArt', 'url' => $stageart_url ),
    );
    echo '<ul>';
    foreach ( $items as $item ) {
        printf( '<li><a href="%s">%s</a></li>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
    }
    echo '</ul>';
}
