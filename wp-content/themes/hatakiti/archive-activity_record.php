<?php
/**
 * 活動履歴一覧. List display (not the card grid used elsewhere), grouped
 * by tag — each tag's own entries newest first. Entries with no tag are
 * grouped under a "タグなし" heading at the end so nothing is silently
 * dropped from the listing.
 *
 * The main query is already ordered newest-first by 活動日 and fetches
 * every record in one page (see hatakiti_order_activity_record_archive in
 * the plugin) — grouping by tag doesn't paginate well otherwise, and the
 * total is small enough that this stays simple.
 *
 * Groups themselves are NOT alphabetised — that would put e.g. a group
 * whose newest entry is from 2 years ago ahead of a group whose newest
 * entry is from last week, defeating "newest first" for anyone reading
 * top to bottom. Instead groups keep the order their first (= most
 * recent, since the query is already sorted) post was encountered in,
 * which PHP's associative arrays preserve automatically — no explicit
 * sort needed.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$hk_groups   = array(); // tag name => array of post IDs, in encounter (= newest-first) order
$hk_untagged = array();

if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        $hk_post_id = get_the_ID();
        $hk_tags    = get_the_terms( $hk_post_id, 'post_tag' );

        if ( $hk_tags && ! is_wp_error( $hk_tags ) ) {
            foreach ( $hk_tags as $hk_tag ) {
                $hk_groups[ $hk_tag->name ][] = $hk_post_id;
            }
        } else {
            $hk_untagged[] = $hk_post_id;
        }
    }
    wp_reset_postdata();
    if ( $hk_untagged ) {
        $hk_groups['タグなし'] = $hk_untagged;
    }
}
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1>活動履歴</h1>
        <p>HATAKITI自身の出演・活動の記録です。</p>
    </div>

    <?php
    $hk_categories = get_terms( array( 'taxonomy' => 'activity_category', 'hide_empty' => true ) );
    if ( $hk_categories && ! is_wp_error( $hk_categories ) && count( $hk_categories ) > 1 ) :
        ?>
        <ul class="hk-tag-list hk-activity-filter">
            <?php foreach ( $hk_categories as $hk_category ) : ?>
                <li><a href="<?php echo esc_url( get_term_link( $hk_category ) ); ?>"><?php echo esc_html( $hk_category->name ); ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

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

    <?php if ( $hk_groups ) : ?>
        <?php foreach ( $hk_groups as $hk_tag_name => $hk_post_ids ) : ?>
            <?php $hk_tag_obj = 'タグなし' === $hk_tag_name ? null : get_term_by( 'name', $hk_tag_name, 'post_tag' ); ?>
            <section class="hk-section">
                <div class="hk-section-head">
                    <h2>
                        <?php if ( $hk_tag_obj ) : ?>
                            <a href="<?php echo esc_url( get_tag_link( $hk_tag_obj ) ); ?>">#<?php echo esc_html( $hk_tag_name ); ?></a>
                        <?php else : ?>
                            #<?php echo esc_html( $hk_tag_name ); ?>
                        <?php endif; ?>
                    </h2>
                </div>
                <ul class="hk-record-list">
                    <?php foreach ( $hk_post_ids as $hk_post_id ) : ?>
                        <?php hatakiti_render_record_list_item( $hk_post_id ); ?>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
    <?php else : ?>
        <?php hatakiti_coming_soon( 'まだ活動履歴がありません。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
