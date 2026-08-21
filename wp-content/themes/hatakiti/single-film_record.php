<?php
/**
 * Single 映画記録 (film viewing record).
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
                <div class="hk-card-type">映画記録</div>
                <h1><?php the_title(); ?></h1>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="hk-article-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
            <?php endif; ?>

            <?php hatakiti_render_film_record_box(); ?>

            <div class="hk-article-body">
                <?php the_content(); ?>
            </div>

            <?php hatakiti_render_tags(); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
