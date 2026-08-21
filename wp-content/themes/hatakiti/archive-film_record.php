<?php
/**
 * 映画記録 archive. Ordered by 鑑賞日 (see hatakiti-core's pre_get_posts hook),
 * newest first.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1>映画記録</h1>
        <p>これまでに観た映画の記録です。</p>
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
        <?php hatakiti_coming_soon( 'まだ映画記録がありません。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
