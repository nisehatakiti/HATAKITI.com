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

            <?php if ( is_page( 'about' ) ) : ?>
                <div class="hk-tile-grid hk-about-subnav">
                    <a class="hk-tile" href="<?php echo esc_url( get_post_type_archive_link( 'activity_record' ) ); ?>">
                        <span class="hk-tile-label">活動履歴を見る</span>
                    </a>
                    <a class="hk-tile" href="<?php echo esc_url( home_url( '/bookshelf/' ) ); ?>">
                        <span class="hk-tile-label">本棚を見る</span>
                    </a>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
