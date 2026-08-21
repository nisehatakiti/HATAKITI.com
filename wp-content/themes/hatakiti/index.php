<?php
/**
 * Fallback template, required by WordPress. In normal use, front-page.php,
 * single.php, single-*.php, archive-*.php, category.php, tag.php and
 * search.php handle every page HATAKITI.com actually shows.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
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
