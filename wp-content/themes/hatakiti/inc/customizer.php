<?php
/**
 * Theme Customizer settings.
 *
 * StageArt is an independent product/site (see docs/01-Vision.md, section 5).
 * We never hard-code its URL in the theme; HATAKITI enters it once the
 * real address is ready, and until then the site shows "Coming Soon".
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_customize_register( $wp_customize ) {
    $wp_customize->add_section( 'hatakiti_links', array(
        'title'    => __( 'HATAKITI 外部リンク', 'hatakiti' ),
        'priority' => 160,
    ) );

    $wp_customize->add_setting( 'hatakiti_stageart_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'hatakiti_stageart_url', array(
        'section'     => 'hatakiti_links',
        'label'       => __( 'StageArt サイトURL', 'hatakiti' ),
        'description' => __( '未入力の場合、StageArtへの導線は「Coming Soon」として表示されます。', 'hatakiti' ),
        'type'        => 'url',
    ) );

    $wp_customize->add_setting( 'hatakiti_intro_text', array(
        'default'           => "HATAKITIは演劇をこよなく愛する個人である。\nここはHATAKITI個人が演劇のことを考えたりする場所である。\n観てきたものや考えたことが雑多に並べられている場所だと思ってほしい。",
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'hatakiti_intro_text', array(
        'section' => 'title_tagline',
        'label'   => __( 'トップページ紹介文', 'hatakiti' ),
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'hatakiti_booklog_user', array(
        'default'           => 'nisehatakiti',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'hatakiti_booklog_user', array(
        'section'     => 'hatakiti_links',
        'label'       => __( 'ブクログ ユーザーID', 'hatakiti' ),
        'description' => __( 'https://booklog.jp/users/【ここ】。本棚ページと「ブクログの本棚を見る」リンクに使われます。', 'hatakiti' ),
        'type'        => 'text',
    ) );
}
add_action( 'customize_register', 'hatakiti_customize_register' );

/**
 * Returns the configured StageArt URL, or empty string if not yet set.
 */
function hatakiti_get_stageart_url() {
    $url = get_theme_mod( 'hatakiti_stageart_url', '' );
    return is_string( $url ) ? trim( $url ) : '';
}

/**
 * Returns the configured Booklog user ID (default: HATAKITI's own,
 * confirmed via the nisehatakiti.online site's existing 本棚 menu link).
 */
function hatakiti_get_booklog_user() {
    $user = get_theme_mod( 'hatakiti_booklog_user', 'nisehatakiti' );
    return is_string( $user ) ? trim( $user ) : '';
}

/**
 * Returns the front-page introduction statement as an array of lines.
 */
function hatakiti_get_intro_lines() {
    $default = "HATAKITIは演劇をこよなく愛する個人である。\nここはHATAKITI個人が演劇のことを考えたりする場所である。\n観てきたものや考えたことが雑多に並べられている場所だと思ってほしい。";
    $text    = get_theme_mod( 'hatakiti_intro_text', $default );
    $lines   = preg_split( '/\r\n|\r|\n/', trim( $text ) );
    return array_filter( array_map( 'trim', $lines ) );
}
