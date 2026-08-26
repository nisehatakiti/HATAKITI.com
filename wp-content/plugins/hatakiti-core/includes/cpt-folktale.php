<?php
/**
 * 日本民話 (FolktaleRecord) — docs/12-JapaneseFolktale-DataContract.md and
 * docs/13-JapaneseFolktale-CollectionOperations.md are the authoritative
 * spec for this content type.
 *
 * Unlike 観劇記録/映画記録/活動履歴, this CPT keeps WordPress's native
 * title + editor screen (title = 民話タイトル, editor = 民話の概要) —
 * the data contract explicitly expects normal wp-admin editing to remain
 * available alongside JSON import (see includes/folktale-json-import.php),
 * and its own instructions ask for the admin screen to stay simple rather
 * than a from-scratch dedicated form like the other CPTs.
 *
 * Storage split (see docs/12 for the full field list):
 *   - Filterable/searchable dimensions (地域=都道府県, テーマ, 分類,
 *     登場する存在) are real taxonomies/meta so 検索・絞り込み works with
 *     plain WP_Query / meta_query, no custom index needed.
 *   - Everything else that's a repeating structured object (locations,
 *     characters, beings' full detail, sources, related_records) is
 *     stored as verbatim JSON in post meta — this preserves exact
 *     round-trip fidelity for re-import (docs/12 §19) without inventing
 *     a relational schema this initial pass doesn't need.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The 47 prefectures, in standard order. region.prefecture is a single
 * value per record (not an array), so this drives a <select>, not a
 * taxonomy checkbox list.
 */
function hatakiti_folktale_prefectures() {
    return array(
        '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
        '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
        '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県',
        '岐阜県', '静岡県', '愛知県', '三重県',
        '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
        '鳥取県', '島根県', '岡山県', '広島県', '山口県',
        '徳島県', '香川県', '愛媛県', '高知県',
        '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県',
        '沖縄県',
    );
}

/**
 * beings.type — docs/12 §11.
 */
function hatakiti_folktale_being_types() {
    return array(
        'yokai'                => '妖怪',
        'monster'               => '怪異',
        'ghost'                 => '幽霊',
        'deity'                 => '神',
        'buddha'                => '仏',
        'animal'                => '動物',
        'supernatural_entity'   => 'その他の超自然的存在',
        'other'                 => 'その他',
    );
}

function hatakiti_register_folktale_cpt() {
    register_post_type( 'folktale', array(
        'labels' => array(
            'name'          => '日本民話',
            'singular_name' => '日本民話',
            'add_new_item'  => '民話を追加',
            'edit_item'     => '民話を編集',
            'new_item'      => '新規民話',
            'view_item'     => '民話を表示',
            'search_items'  => '民話を検索',
            'not_found'     => '民話が見つかりません',
            'all_items'     => '民話一覧',
            'menu_name'     => '日本民話',
        ),
        'public'        => true,
        'has_archive'   => 'nihon-no-minwa',
        'rewrite'       => array( 'slug' => 'minwa', 'with_front' => false ),
        'menu_icon'     => 'dashicons-book-alt',
        'menu_position' => 8,
        // Native title + editor, deliberately — see file docblock.
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
        'taxonomies'    => array( 'folktale_theme', 'folktale_story_type', 'folktale_being' ),
        'show_in_rest'  => true,
    ) );
}
add_action( 'init', 'hatakiti_register_folktale_cpt' );

function hatakiti_register_folktale_taxonomies() {
    register_taxonomy( 'folktale_theme', array( 'folktale' ), array(
        'labels' => array(
            'name'          => 'テーマ',
            'singular_name' => 'テーマ',
            'search_items'  => 'テーマを検索',
            'all_items'     => 'すべてのテーマ',
            'edit_item'     => 'テーマを編集',
            'update_item'   => 'テーマを更新',
            'add_new_item'  => '新規テーマを追加',
            'new_item_name' => '新規テーマ名',
            'menu_name'     => 'テーマ',
        ),
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'minwa-theme' ),
        // Default tag-style (autocomplete) meta box — this vocabulary is
        // meant to grow into the hundreds/thousands, unlike the small
        // fixed checkbox lists used elsewhere (film_genre, activity_type).
    ) );

    register_taxonomy( 'folktale_story_type', array( 'folktale' ), array(
        'labels' => array(
            'name'          => '分類',
            'singular_name' => '分類',
            'search_items'  => '分類を検索',
            'all_items'     => 'すべての分類',
            'edit_item'     => '分類を編集',
            'update_item'   => '分類を更新',
            'add_new_item'  => '新規分類を追加',
            'new_item_name' => '新規分類名',
            'menu_name'     => '分類',
        ),
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'minwa-story-type' ),
    ) );

    register_taxonomy( 'folktale_being', array( 'folktale' ), array(
        'labels' => array(
            'name'          => '登場する存在',
            'singular_name' => '存在',
            'search_items'  => '存在を検索',
            'all_items'     => 'すべての存在',
            'edit_item'     => '存在を編集',
            'update_item'   => '存在を更新',
            'add_new_item'  => '新規の存在を追加',
            'new_item_name' => '新規の存在名',
            'menu_name'     => '登場する存在',
        ),
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'minwa-being' ),
    ) );
}
add_action( 'init', 'hatakiti_register_folktale_taxonomies' );

function hatakiti_seed_folktale_story_type_terms() {
    $types = array( '昔話', '民話', '伝説', '地域伝承', '妖怪伝承', '神話的伝承', '由来譚', '信仰伝承' );
    foreach ( $types as $type ) {
        if ( ! term_exists( $type, 'folktale_story_type' ) ) {
            wp_insert_term( $type, 'folktale_story_type' );
        }
    }
}

/**
 * Gets/creates the folktale_being term for a given normalized name, and
 * records its type as term meta (docs/12 §11 — "資料上の存在とAIによる
 * 一般分類は区別できるようにする": the term itself is the cross-record
 * identity; `folktale_being_type` on it is AI's classification of it).
 */
function hatakiti_get_or_create_being_term( $normalized_name, $type = '' ) {
    $normalized_name = trim( $normalized_name );
    if ( '' === $normalized_name ) {
        return 0;
    }

    $term = term_exists( $normalized_name, 'folktale_being' );
    if ( ! $term ) {
        $term = wp_insert_term( $normalized_name, 'folktale_being' );
    }
    if ( is_wp_error( $term ) ) {
        return 0;
    }
    $term_id = (int) $term['term_id'];

    if ( $type ) {
        update_term_meta( $term_id, 'folktale_being_type', $type );
    }

    return $term_id;
}

/**
 * 地域 (region) is a single object per record, not filterable via
 * taxonomy the way テーマ/存在 are — plain post meta + meta_query on the
 * archive page (see archive-folktale.php) is simpler and correct here.
 */
function hatakiti_register_folktale_meta() {
    $string_fields = array(
        'hatakiti_folktale_record_id',
        'hatakiti_folktale_title_normalized',
        'hatakiti_folktale_title_origin',
        'hatakiti_folktale_schema_version',
        'hatakiti_folktale_confidence',
        'hatakiti_folktale_region_prefecture',
        'hatakiti_folktale_region_historical_province',
        'hatakiti_folktale_region_municipality',
        'hatakiti_folktale_region_area_name',
        'hatakiti_folktale_region_source_description',
        'hatakiti_folktale_locations_json',
        'hatakiti_folktale_characters_json',
        'hatakiti_folktale_beings_json',
        'hatakiti_folktale_sources_json',
        'hatakiti_folktale_related_records_json',
        'hatakiti_folktale_ai_processing_json',
        'hatakiti_folktale_summary_based_on_json',
        'hatakiti_folktale_public_summary',
        'hatakiti_folktale_story_status',
        'hatakiti_folktale_story_content',
    );

    foreach ( $string_fields as $key ) {
        register_post_meta( 'folktale', $key, array(
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ) );
    }
}
add_action( 'init', 'hatakiti_register_folktale_meta' );

/**
 * The three public content levels a folktale record can be at
 * (docs/12 §5.3の方針を反映: "内容が分かっているか" と "サイト表示" を
 * 分離する):
 *
 *   researching        伝承の存在・出典は確認できているが、物語内容は
 *                       未確認。あらすじ・内容を書けるだけの材料がない。
 *   summary_confirmed   物語の一部または概要が確認できている。
 *   content_confirmed   本文または信頼できる詳細な再話まで確認できて
 *                       いる。あらすじ＋内容の両方を表示できる。
 *
 * story_status に応じて、hatakiti_folktale_public_summary が実際に
 * 何を意味するかが変わる: researching では正直な「調査中」通知、それ
 * 以外では本物のあらすじ。region/locations/beings/sourcesから機械的に
 * 組み立てた「○○に伝わる…資料に収録されています」という文は、もはや
 * 生成しない — 内容を説明していないため、これ自体が「薄い代替説明文」
 * になってしまうと判断した。
 */
function hatakiti_folktale_story_statuses() {
    return array( 'researching', 'summary_confirmed', 'content_confirmed' );
}

/**
 * Fallback for records with no confirmed story content yet: a plainly
 * honest notice, never a manufactured description. Only mentions that a
 * source exists (not what it says) when sources are actually on file.
 */
function hatakiti_generate_folktale_researching_notice( $post_id ) {
    $sources = json_decode( (string) get_post_meta( $post_id, 'hatakiti_folktale_sources_json', true ), true );
    $has_sources = is_array( $sources ) && count( array_filter( $sources, function ( $s ) { return ! empty( $s['title'] ); } ) ) > 0;

    $text = 'この民話は現在、詳しい内容を調査中です。';
    if ( $has_sources ) {
        $text .= '資料に収録されていることは確認されています。';
    }
    return $text;
}

/**
 * 地域（都道府県）で絞り込む — see archive-folktale.php.
 */
function hatakiti_filter_folktale_archive_by_prefecture( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( ! is_post_type_archive( 'folktale' ) ) {
        return;
    }
    if ( empty( $_GET['prefecture'] ) ) {
        return;
    }
    $prefecture = sanitize_text_field( wp_unslash( $_GET['prefecture'] ) );
    $query->set( 'meta_key', 'hatakiti_folktale_region_prefecture' );
    $query->set( 'meta_value', $prefecture );
}
add_action( 'pre_get_posts', 'hatakiti_filter_folktale_archive_by_prefecture' );
