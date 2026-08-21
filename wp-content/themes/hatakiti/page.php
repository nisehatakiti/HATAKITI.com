<?php
/**
 * Generic page template (used for HATAKITIとは / 活動・制作, etc).
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
                <h1><?php the_title(); ?></h1>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="hk-article-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
            <?php endif; ?>

            <div class="hk-article-body">
                <?php
                if ( trim( get_the_content() ) === '' ) {
                    hatakiti_coming_soon();
                } else {
                    the_content();
                }
                ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
