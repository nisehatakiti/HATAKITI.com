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
        $query->set( 'post_type', array( 'post', 'page', 'theatre_record', 'film_record', 'activity_record', 'folktale', 'occult_weekly' ) );
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
        array( 'label' => '日本民話', 'url' => get_post_type_archive_link( 'folktale' ) ),
        array( 'label' => '週刊オカルト新聞', 'url' => get_post_type_archive_link( 'occult_weekly' ) ),
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


/**
 * Virtual page: 演劇の教科書.
 *
 * The first version is intentionally a virtual route so the content can be
 * published immediately without requiring a wp-admin Page record.
 */
function hatakiti_theatre_textbook_route( $template ) {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
    $base = trim( parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

    if ( $base && 0 === strpos( $path, $base . '/' ) ) {
        $path = substr( $path, strlen( $base ) + 1 );
    }

    $theatre_textbook_routes = array(
        'theatre-textbook',
        'theatre-textbook/stanislavski',
        'theatre-textbook/method',
        'theatre-textbook/meisner',
        'theatre-textbook/lecoq',
        'theatre-textbook/theatre-world',
        'theatre-textbook/theatre-world/high-school',
        'theatre-textbook/theatre-world/commercial',
        'theatre-textbook/theatre-world/student',
        'theatre-textbook/theatre-world/small-theatre',
        'theatre-textbook/theatre-world/production',
        'theatre-textbook/staff',
        'theatre-textbook/staff/lighting',
        'theatre-textbook/staff/lighting/chapter-1',
        'theatre-textbook/staff/lighting/chapter-2',
        'theatre-textbook/staff/lighting/chapter-3',
        'theatre-textbook/staff/lighting/chapter-4',
        'theatre-textbook/staff/lighting/chapter-5',
	'theatre-textbook/staff/lighting/chapter-6',
        'theatre-textbook/staff/lighting/chapter-7',
        'theatre-textbook/staff/lighting/chapter-8',
        'theatre-textbook/staff/lighting/chapter-9',
        'theatre-textbook/staff/lighting/filters',
        'theatre-textbook/staff/sound',
        'theatre-textbook/staff/stage-management',
    );
    $is_gel_filter_route = 'theatre-textbook/staff/lighting/filters' === $path
        || 0 === strpos( $path, 'theatre-textbook/staff/lighting/filters/' );

    if ( ! in_array( $path, $theatre_textbook_routes, true ) && ! $is_gel_filter_route ) {
        return $template;
    }

    status_header( 200 );

    if ( 'theatre-textbook' === $path ) {
        return get_template_directory() . '/page-theatre-textbook.php';
    }

    if ( 'theatre-textbook/staff/lighting/filters' === $path || 0 === strpos( $path, 'theatre-textbook/staff/lighting/filters/' ) ) {
        return get_template_directory() . '/page-theatre-lighting-filters.php';
    }

    if ( 0 === strpos( $path, 'theatre-textbook/theatre-world' ) ) {
        return get_template_directory() . '/page-theatre-world.php';
    }

    if ( 0 === strpos( $path, 'theatre-textbook/staff' ) ) {
        return get_template_directory() . '/page-theatre-staff.php';
    }

    return get_template_directory() . '/page-theatre-method.php';
}
add_filter( 'template_include', 'hatakiti_theatre_textbook_route', 99 );

function hatakiti_theatre_textbook_title( $parts ) {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
    if ( 'theatre-textbook' === $path ) {
        $parts['title'] = '演劇の教科書';
    } elseif ( false !== strpos( $path, 'theatre-textbook/stanislavski' ) ) {
        $parts['title'] = 'スタニスラフスキー・システム｜演劇の教科書';
    } elseif ( false !== strpos( $path, 'theatre-textbook/method' ) ) {
        $parts['title'] = 'メソッド演技｜演劇の教科書';
    } elseif ( false !== strpos( $path, 'theatre-textbook/meisner' ) ) {
        $parts['title'] = 'マイズナー・テクニック｜演劇の教科書';
    } elseif ( false !== strpos( $path, 'theatre-textbook/lecoq' ) ) {
        $parts['title'] = 'ルコック・システム｜演劇の教科書';
    } elseif ( 'theatre-textbook/theatre-world' === $path ) {
        $parts['title'] = '演劇の現場を知る｜演劇の教科書';
    } elseif ( false !== strpos( $path, 'theatre-textbook/theatre-world/' ) ) {
        $parts['title'] = '演劇の現場｜演劇の教科書';
    } elseif ( 'theatre-textbook/staff' === $path ) {
        $parts['title'] = '舞台スタッフの仕事｜演劇の教科書';
    } elseif ( false !== strpos( $path, 'theatre-textbook/staff/' ) ) {
        $parts['title'] = '舞台スタッフ｜演劇の教科書';
    }
    return $parts;
}
add_filter( 'document_title_parts', 'hatakiti_theatre_textbook_title', 99 );
