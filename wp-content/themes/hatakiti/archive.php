<?php
/**
 * Generic archive fallback (dates, uncategorised taxonomies, etc.).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1><?php the_archive_title(); ?></h1>
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
        <?php hatakiti_coming_soon(); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
