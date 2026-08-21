<?php
/**
 * Header: site logo + global navigation.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="hk-site-header">
    <a class="hk-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?>">
        <?php
        /**
         * PC logo is the confirmed main logo (docs/01-Vision.md §2). For now,
         * small screens simply scale it down via CSS. A dedicated mobile
         * logo can be added later with zero template changes: just drop a
         * file in at assets/img/hatakiti-mobile.png and it is picked up
         * automatically below 700px width.
         */
        $hk_main_logo   = get_template_directory_uri() . '/assets/img/hatakiti-main.png';
        $hk_mobile_path = get_template_directory() . '/assets/img/hatakiti-mobile.png';
        ?>
        <?php if ( file_exists( $hk_mobile_path ) ) : ?>
            <picture>
                <source media="(max-width: 700px)" srcset="<?php echo esc_url( get_template_directory_uri() . '/assets/img/hatakiti-mobile.png' ); ?>">
                <img src="<?php echo esc_url( $hk_main_logo ); ?>" alt="HATAKITI">
            </picture>
        <?php else : ?>
            <img src="<?php echo esc_url( $hk_main_logo ); ?>" alt="HATAKITI">
        <?php endif; ?>
    </a>

    <div class="hk-nav-wrap">
        <nav class="hk-nav" id="hk-nav" aria-label="<?php esc_attr_e( 'グローバルメニュー', 'hatakiti' ); ?>">
            <button class="hk-nav-toggle" id="hk-nav-toggle" aria-expanded="false" aria-controls="hk-nav">
                <?php esc_html_e( 'メニュー', 'hatakiti' ); ?>
            </button>
            <?php
            if ( has_nav_menu( 'primary' ) ) {
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => '',
                    'depth'          => 1,
                ) );
            } else {
                hatakiti_fallback_menu();
            }
            ?>
        </nav>
    </div>
</header>
