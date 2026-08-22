<?php
/**
 * Single 観劇記録 (theatre viewing record).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <?php while ( have_posts() ) : the_post(); ?>
        <article class="hk-article">
            <header class="hk-article-header">
                <div class="hk-card-type">観劇記録</div>
                <h1><?php the_title(); ?></h1>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="hk-article-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
            <?php endif; ?>

            <?php hatakiti_render_theatre_record_box(); ?>
            <?php hatakiti_render_divider(); ?>
            <?php hatakiti_render_review_heading(); ?>

            <div class="hk-article-body">
                <?php the_content(); ?>
            </div>

            <?php hatakiti_render_tags( get_the_ID(), true ); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
