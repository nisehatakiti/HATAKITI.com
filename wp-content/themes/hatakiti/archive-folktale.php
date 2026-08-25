<?php
/**
 * 日本の民話 (folktale archive). Simple filtering only — 都道府県 (a
 * <select> since region.prefecture is a single value per record, not a
 * taxonomy — see cpt-folktale.php), plus link rows for テーマ and
 * 登場する存在, matching the same lightweight pattern already used for
 * 活動履歴's 活動種別/カテゴリ filters. No faceted/combined search yet —
 * docs/12 §4 explicitly asks not to over-build this initially.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// Only prefectures that actually have at least one published folktale —
// showing the full 47-item list before any data exists would be misleading.
global $wpdb;
$hk_used_prefectures = $wpdb->get_col( $wpdb->prepare(
    "SELECT DISTINCT pm.meta_value
     FROM {$wpdb->postmeta} pm
     INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
     WHERE pm.meta_key = %s AND pm.meta_value != '' AND p.post_type = 'folktale' AND p.post_status = 'publish'
     ORDER BY pm.meta_value ASC",
    'hatakiti_folktale_region_prefecture'
) );

$hk_current_prefecture = isset( $_GET['prefecture'] ) ? sanitize_text_field( wp_unslash( $_GET['prefecture'] ) ) : '';
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1>日本の民話</h1>
        <p>全国各地の民話・昔話・伝説を、地域・テーマ・登場する存在から辿れます。</p>
    </div>

    <?php if ( $hk_used_prefectures ) : ?>
        <form method="get" action="<?php echo esc_url( get_post_type_archive_link( 'folktale' ) ); ?>" class="hk-search-form">
            <label class="hk-screen-reader-text" for="hk-folktale-prefecture">都道府県で絞り込む</label>
            <select id="hk-folktale-prefecture" name="prefecture" onchange="this.form.submit()">
                <option value="">都道府県で絞り込む</option>
                <?php foreach ( $hk_used_prefectures as $hk_pref ) : ?>
                    <option value="<?php echo esc_attr( $hk_pref ); ?>"<?php selected( $hk_current_prefecture, $hk_pref ); ?>><?php echo esc_html( $hk_pref ); ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit">絞り込む</button></noscript>
        </form>
    <?php endif; ?>

    <?php
    $hk_themes = get_terms( array( 'taxonomy' => 'folktale_theme', 'hide_empty' => true ) );
    if ( $hk_themes && ! is_wp_error( $hk_themes ) && count( $hk_themes ) > 0 ) :
        ?>
        <ul class="hk-tag-list hk-activity-filter">
            <?php foreach ( $hk_themes as $hk_theme ) : ?>
                <li><a href="<?php echo esc_url( get_term_link( $hk_theme ) ); ?>">#<?php echo esc_html( $hk_theme->name ); ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php
    $hk_beings = get_terms( array( 'taxonomy' => 'folktale_being', 'hide_empty' => true ) );
    if ( $hk_beings && ! is_wp_error( $hk_beings ) && count( $hk_beings ) > 0 ) :
        ?>
        <ul class="hk-tag-list hk-activity-filter">
            <?php foreach ( $hk_beings as $hk_being ) : ?>
                <li><a href="<?php echo esc_url( get_term_link( $hk_being ) ); ?>"><?php echo esc_html( $hk_being->name ); ?></a></li>
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
        <?php hatakiti_coming_soon( 'まだ民話が登録されていません。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
