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
 * Returns the front-page introduction statement as an array of lines.
 */
function hatakiti_get_intro_lines() {
    $default = "HATAKITIは演劇をこよなく愛する個人である。\nここはHATAKITI個人が演劇のことを考えたりする場所である。\n観てきたものや考えたことが雑多に並べられている場所だと思ってほしい。";
    $text    = get_theme_mod( 'hatakiti_intro_text', $default );
    $lines   = preg_split( '/\r\n|\r|\n/', trim( $text ) );
    return array_filter( array_map( 'trim', $lines ) );
}
