<?php
/**
 * 観劇記録 archive. Ordered by 観劇日 (see hatakiti-core's pre_get_posts hook),
 * newest first, so past records stay easy to browse.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1>観劇記録</h1>
        <p>これまでに観た舞台の記録です。</p>
    </div>

    <?php if ( have_posts() ) : ?>
        <ul class="hk-record-list">
            <?php
            while ( have_posts() ) :
                the_post();
                hatakiti_render_record_list_item( get_the_ID() );
            endwhile;
            ?>
        </ul>
        <?php hatakiti_pagination(); ?>
    <?php else : ?>
        <?php hatakiti_coming_soon( 'まだ観劇記録がありません。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
