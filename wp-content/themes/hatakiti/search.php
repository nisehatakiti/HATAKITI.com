<?php
/**
 * Search results — spans regular posts and both custom post types.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1><?php printf( esc_html__( '「%s」の検索結果', 'hatakiti' ), esc_html( get_search_query() ) ); ?></h1>
    </div>

    <?php get_search_form(); ?>

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
        <?php hatakiti_coming_soon( '該当する記事は見つかりませんでした。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
