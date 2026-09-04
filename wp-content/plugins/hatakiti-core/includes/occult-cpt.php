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

/**
 * オカルトニュースのカテゴリ体系（固定10種）。AI自動分類・手動分類の
 * どちらでもこの配列を唯一の正としてvalidateする — 別名・追加カテゴリを
 * 増やさない（指示書「オカルトニュースのカテゴリ自動分類」§2）。
 */
function hatakiti_occult_category_terms() {
    return array(
        'UMA・未確認生物', 'UFO・宇宙', '心霊・怪談', '超常現象', '古代・歴史',
        '民俗・呪術', '科学・人体', '事件・ミステリー', '予言・終末', 'その他',
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
        // hierarchical=true -> checkbox UI (fixed list), not a free-text
        // tag box — the category set is a closed set of 10 (指示書
        // 「オカルトニュースのカテゴリ自動分類」§2), so manual editing
        // should not be able to introduce ad-hoc variants either.
        'hierarchical'      => true,
        'public'            => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => false,
    ) );
}
add_action( 'init', 'hatakiti_register_occult_taxonomy' );

/**
 * 固定10カテゴリを投入し、それ以外の未使用（count=0）タームは削除する
 * — 冪等（何度呼んでも安全）。使用中（count>0）のタームは絶対に削除
 * しない。
 */
function hatakiti_seed_occult_category_terms() {
    $valid = hatakiti_occult_category_terms();
    foreach ( $valid as $term ) {
        if ( ! term_exists( $term, 'occult_category' ) ) {
            wp_insert_term( $term, 'occult_category' );
        }
    }

    $existing = get_terms( array( 'taxonomy' => 'occult_category', 'hide_empty' => false ) );
    if ( ! is_wp_error( $existing ) ) {
        foreach ( $existing as $term ) {
            if ( ! in_array( $term->name, $valid, true ) && 0 === (int) $term->count ) {
                wp_delete_term( $term->term_id, 'occult_category' );
            }
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
        'hatakiti_occult_pdf_cache_key',
        'hatakiti_occult_pdf_warnings',
        // occult_news_item
        'hatakiti_occult_source_post_id',
        'hatakiti_occult_source_name',
        'hatakiti_occult_original_url',
        'hatakiti_occult_published_at',
        'hatakiti_occult_fetched_at',
        'hatakiti_occult_content_hash',
        'hatakiti_occult_issue_post_id',
        'hatakiti_occult_raw_metadata',
        'hatakiti_occult_source_article_text',
        'hatakiti_occult_source_article_fetch_status',
        'hatakiti_occult_source_article_fetch_error',
        'hatakiti_occult_source_article_fetched_at',
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
 * 「一般公開してよい号」の除外条件（臨時発行のmanual_testを除く）。
 * publish状態は常に別途クエリ側で指定する前提 — ここではrun_typeのみを
 * 見る。バックナンバー一覧・フロントページ最新号カードなど、公開表示
 * すべてで同じ定義を使い回すための共有ヘルパー。
 */
function hatakiti_occult_weekly_public_meta_query() {
    return array(
        'relation' => 'OR',
        array( 'key' => 'hatakiti_occult_run_type', 'compare' => 'NOT EXISTS' ),
        array( 'key' => 'hatakiti_occult_run_type', 'value' => 'manual_test', 'compare' => '!=' ),
    );
}

/**
 * 一般公開してよい最新号（publishかつmanual_testでない）を1件返す。
 * 無ければnull。
 */
function hatakiti_get_latest_published_occult_weekly() {
    $posts = get_posts( array(
        'post_type'      => 'occult_weekly',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => 'hatakiti_occult_issue_date',
        'orderby'        => 'meta_value',
        'order'          => 'DESC',
        'meta_query'     => hatakiti_occult_weekly_public_meta_query(),
    ) );
    return $posts ? $posts[0] : null;
}

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
        // 臨時発行の「テスト発行」（hatakiti_occult_run_type=manual_test）
        // は常にdraftなので通常はこのクエリに出てこないが、多重防御として
        // 明示的にも除外する — 一般ユーザーが正式発行号と混同しないため。
        $query->set( 'meta_query', hatakiti_occult_weekly_public_meta_query() );
    }
}
add_action( 'pre_get_posts', 'hatakiti_order_occult_weekly_archive' );

/**
 * 管理画面「ニュース一覧」にカテゴリ絞り込みドロップダウンを追加。
 */
function hatakiti_occult_news_item_category_filter() {
    global $typenow;
    if ( 'occult_news_item' !== $typenow ) {
        return;
    }
    $selected = isset( $_GET['occult_category'] ) ? sanitize_text_field( wp_unslash( $_GET['occult_category'] ) ) : '';
    $terms    = get_terms( array( 'taxonomy' => 'occult_category', 'hide_empty' => false ) );
    if ( is_wp_error( $terms ) || ! $terms ) {
        return;
    }
    ?>
    <select name="occult_category">
        <option value=""><?php esc_html_e( 'すべてのカテゴリ', 'hatakiti' ); ?></option>
        <?php foreach ( $terms as $term ) : ?>
            <option value="<?php echo esc_attr( $term->slug ); ?>"<?php selected( $selected, $term->slug ); ?>><?php echo esc_html( $term->name ); ?> (<?php echo (int) $term->count; ?>)</option>
        <?php endforeach; ?>
    </select>
    <?php
}
add_action( 'restrict_manage_posts', 'hatakiti_occult_news_item_category_filter' );

/**
 * occult_categoryはpublic=falseのため、上のドロップダウンのGETパラメータ
 * だけでは自動的に絞り込まれない — 管理一覧のクエリに明示的に反映する。
 */
function hatakiti_occult_news_item_category_filter_query( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    global $typenow, $pagenow;
    if ( 'occult_news_item' !== $typenow || 'edit.php' !== $pagenow ) {
        return;
    }
    if ( empty( $_GET['occult_category'] ) ) {
        return;
    }
    $query->set( 'tax_query', array( array(
        'taxonomy' => 'occult_category',
        'field'    => 'slug',
        'terms'    => sanitize_text_field( wp_unslash( $_GET['occult_category'] ) ),
    ) ) );
}
add_action( 'pre_get_posts', 'hatakiti_occult_news_item_category_filter_query' );
