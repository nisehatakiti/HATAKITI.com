<?php
/**
 * 週刊オカルト新聞 — docs/07-OccultWeekly.md is the concept blueprint;
 * this file wires up the three content types it needs.
 *
 *   occult_weekly     1週間=1号の新聞そのもの（構造化された articles JSON
 *                     を持つ — dedicated admin form, see
 *                     occult-weekly-admin-form.php）
 *   occult_news_item  RSSから取得した個々のニュース（非公開・社内データ。
 *                     元記事の全文は保存しない — タイトル/概要/URL/日付
 *                     のみ、著作権上の注意（指示書 §14）どおり）
 *   occult_news_source ニュースソース設定（source_name/rss_url/
 *                     website_url/enabled）
 *
 * occult_news_item and occult_news_source keep WordPress's native
 * title+editor screen plus a small meta box, same lighter-weight pattern
 * as 日本民話 — occult_weekly does not (see occult-weekly-admin-form.php
 * for why).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_occult_category_terms() {
    return array(
        'UFO', 'UMA', '幽霊', '心霊', '妖怪', '都市伝説', '未解決事件',
        '超常現象', 'オーパーツ', '古代文明', '陰謀論', '超能力', '宗教',
        '呪術', '予言', '科学と超常', 'その他',
    );
}

function hatakiti_register_occult_cpts() {
    register_post_type( 'occult_weekly', array(
        'labels' => array(
            'name'          => '週刊オカルト新聞',
            'singular_name' => '週刊オカルト新聞',
            'add_new_item'  => '号を追加',
            'edit_item'     => '号を編集',
            'new_item'      => '新規号',
            'view_item'     => '号を表示',
            'search_items'  => '号を検索',
            'not_found'     => '号が見つかりません',
            'all_items'     => '号一覧',
            'menu_name'     => '週刊オカルト新聞',
        ),
        'public'        => true,
        'has_archive'   => 'occult-weekly',
        'rewrite'       => array( 'slug' => 'occult', 'with_front' => false ),
        'menu_icon'     => 'dashicons-visibility',
        'menu_position' => 9,
        // No native title/editor — the newspaper's content is entirely
        // the structured `articles` JSON built by the dedicated admin
        // screen (occult-weekly-admin-form.php), not freeform text.
        'supports'      => array( 'thumbnail' ),
        'show_in_rest'  => true,
    ) );

    register_post_type( 'occult_news_item', array(
        'labels' => array(
            'name'          => 'オカルトニュース',
            'singular_name' => 'オカルトニュース',
            'add_new_item'  => 'ニュースを追加',
            'edit_item'     => 'ニュースを編集',
            'new_item'      => '新規ニュース',
            'view_item'     => 'ニュースを表示',
            'search_items'  => 'ニュースを検索',
            'not_found'     => 'ニュースが見つかりません',
            'all_items'     => 'ニュース一覧',
            'menu_name'     => 'ニュース一覧',
        ),
        // Internal working data, not a public content type — no single
        // page / archive of its own (docs/07: this is not a reprint of
        // the source article, just the raw material for an issue).
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'edit.php?post_type=occult_weekly',
        'supports'      => array( 'title', 'editor' ),
        'taxonomies'    => array( 'occult_category' ),
        'show_in_rest'  => false,
    ) );

    register_post_type( 'occult_news_source', array(
        'labels' => array(
            'name'          => 'ニュースソース',
            'singular_name' => 'ニュースソース',
            'add_new_item'  => 'ソースを追加',
            'edit_item'     => 'ソースを編集',
            'new_item'      => '新規ソース',
            'not_found'     => 'ソースが見つかりません',
            'all_items'     => 'ニュースソース',
            'menu_name'     => 'ニュースソース',
        ),
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'edit.php?post_type=occult_weekly',
        'supports'      => array( 'title' ),
        'show_in_rest'  => false,
    ) );
}
add_action( 'init', 'hatakiti_register_occult_cpts' );

function hatakiti_register_occult_taxonomy() {
    register_taxonomy( 'occult_category', array( 'occult_news_item' ), array(
        'labels' => array(
            'name'          => 'カテゴリ',
            'singular_name' => 'カテゴリ',
            'search_items'  => 'カテゴリを検索',
            'all_items'     => 'すべてのカテゴリ',
            'edit_item'     => 'カテゴリを編集',
            'update_item'   => 'カテゴリを更新',
            'add_new_item'  => '新規カテゴリを追加',
            'new_item_name' => '新規カテゴリ名',
            'menu_name'     => 'カテゴリ',
        ),
        'hierarchical'      => false,
        'public'            => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => false,
        // Fixed-ish starter list, but not locked — plain default tag box
        // so new categories can always be typed in (指示書 §9).
    ) );
}
add_action( 'init', 'hatakiti_register_occult_taxonomy' );

function hatakiti_seed_occult_category_terms() {
    foreach ( hatakiti_occult_category_terms() as $term ) {
        if ( ! term_exists( $term, 'occult_category' ) ) {
            wp_insert_term( $term, 'occult_category' );
        }
    }
}

function hatakiti_register_occult_meta() {
    $string_fields = array(
        // occult_weekly
        'hatakiti_occult_issue_id',
        'hatakiti_occult_week_start',
        'hatakiti_occult_week_end',
        'hatakiti_occult_issue_date',
        'hatakiti_occult_articles_json',
        'hatakiti_occult_source_count',
        'hatakiti_occult_article_count',
        'hatakiti_occult_main_topic_count',
        'hatakiti_occult_editorial_summary',
        'hatakiti_occult_generated_at',
        // occult_news_item
        'hatakiti_occult_source_post_id',
        'hatakiti_occult_source_name',
        'hatakiti_occult_original_url',
        'hatakiti_occult_published_at',
        'hatakiti_occult_fetched_at',
        'hatakiti_occult_content_hash',
        'hatakiti_occult_issue_post_id',
        'hatakiti_occult_raw_metadata',
        // occult_news_source
        'hatakiti_occult_rss_url',
        'hatakiti_occult_website_url',
        'hatakiti_occult_enabled',
    );
    foreach ( $string_fields as $key ) {
        register_post_meta( '', $key, array(
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => false,
            'auth_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ) );
    }
}
add_action( 'init', 'hatakiti_register_occult_meta' );

/**
 * 号一覧は発行日（issue_date）の新しい順 — WordPressの投稿日ではなく、
 * 号として編集上意味を持つ日付で並べる（観劇記録などと同じ考え方）。
 */
function hatakiti_order_occult_weekly_archive( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( is_post_type_archive( 'occult_weekly' ) ) {
        $query->set( 'meta_key', 'hatakiti_occult_issue_date' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'DESC' );
    }
}
add_action( 'pre_get_posts', 'hatakiti_order_occult_weekly_archive' );
