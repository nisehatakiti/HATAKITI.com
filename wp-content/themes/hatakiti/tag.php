<?php
/**
 * Tag archive. Theatre-essay tags (演技 / セリフ / 身体 / 感情 / 演出 / 台本 /
 * 稽古 / 視線 / 間 / 距離 / 熱量, etc.) become the de facto index for that
 * content as it accumulates — see docs/04-UXandWordPress.md §3.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1>#<?php single_tag_title(); ?></h1>
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
        <?php hatakiti_coming_soon( 'このタグの記事はまだありません。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
