<?php
/**
 * Category archive — used for 日々の所感 (nikki) and 演劇について (engeki),
 * and any other category the author adds later.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1><?php single_cat_title(); ?></h1>
        <?php
        $hk_cat_desc = category_description();
        if ( $hk_cat_desc ) {
            echo wp_kses_post( wpautop( $hk_cat_desc ) );
        }
        ?>
    </div>

    <?php if ( have_posts() ) : ?>
        <div class="hk-card-grid">
            <?php
            while ( have_posts() ) :
                the_post();
                hatakiti_render_card( get_the_ID() );
            endwhile;
            ?>
        </div>
        <?php hatakiti_pagination(); ?>
    <?php else : ?>
        <?php hatakiti_coming_soon( 'まだ記事がありません。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
