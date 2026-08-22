<?php
/**
 * 活動履歴一覧. Newest first (WordPress's default post_date ordering — no
 * custom sort needed, unlike 観劇記録/映画記録 which sort by a separate
 * viewing-date field). A simple 活動種別 filter row is offered; nothing
 * more elaborate than that.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1>活動履歴</h1>
        <p>HATAKITI自身の出演・活動の記録です。</p>
    </div>

    <?php
    $hk_types = get_terms( array( 'taxonomy' => 'activity_type', 'hide_empty' => true ) );
    if ( $hk_types && ! is_wp_error( $hk_types ) && count( $hk_types ) > 1 ) :
        ?>
        <ul class="hk-tag-list hk-activity-filter">
            <?php foreach ( $hk_types as $hk_type ) : ?>
                <li><a href="<?php echo esc_url( get_term_link( $hk_type ) ); ?>"><?php echo esc_html( $hk_type->name ); ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

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
        <?php hatakiti_coming_soon( 'まだ活動履歴がありません。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
