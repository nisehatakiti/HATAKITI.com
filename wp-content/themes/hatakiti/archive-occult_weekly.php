<?php
/**
 * 週刊オカルト新聞 過去号一覧. Newest issue_date first (see
 * hatakiti_order_occult_weekly_archive in occult-cpt.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1>週刊オカルト新聞</h1>
        <p>HATAKITI OCCULT WEEKLY — 過去号一覧</p>
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
        <?php hatakiti_coming_soon( 'まだ号がありません。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
