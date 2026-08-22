<?php
/**
 * film_genre taxonomy for 映画記録 (docs/03-ContentModel.md §4).
 *
 * Deliberately non-hierarchical and open — the category list is a starting
 * point ("Animation, Horror, Romantic comedy, ... The list can grow
 * naturally"), not a fixed structure.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_register_film_genre_taxonomy() {
    register_taxonomy( 'film_genre', array( 'film_record' ), array(
        'labels' => array(
            'name'          => 'ジャンル',
            'singular_name' => 'ジャンル',
            'search_items'  => 'ジャンルを検索',
            'all_items'     => 'すべてのジャンル',
            'edit_item'     => 'ジャンルを編集',
            'update_item'   => 'ジャンルを更新',
            'add_new_item'  => '新規ジャンルを追加',
            'new_item_name' => '新規ジャンル名',
            'menu_name'     => 'ジャンル',
        ),
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'film-genre' ),
        // Checkbox list (same UI WordPress uses for Categories) instead of
        // the default tag-style autocomplete box — this is a fixed-choice
        // multi-select field, not free-form tagging. Still includes an
        // "add new" control, so the list can grow later.
        'meta_box_cb'       => 'post_categories_meta_box',
    ) );
}
add_action( 'init', 'hatakiti_register_film_genre_taxonomy' );

/**
 * Starting set of film genres and theatre-essay tags, so the author has
 * something to pick from right away. Safe to run more than once — terms
 * that already exist are simply skipped by wp_insert_term's duplicate check.
 */
function hatakiti_seed_default_terms() {
    $genres = array(
        'アニメ', 'ホラー', '恋愛コメディ', 'ドラマ', 'コメディ',
        'アクション', 'スリラー', 'サスペンス', 'ドキュメンタリー', 'SF',
    );
    foreach ( $genres as $genre ) {
        if ( ! term_exists( $genre, 'film_genre' ) ) {
            wp_insert_term( $genre, 'film_genre' );
        }
    }

    $theatre_tags = array(
        '演技', 'セリフ', '身体', '感情', '演出', '台本', '稽古', '視線', '間', '距離', '熱量',
    );
    foreach ( $theatre_tags as $tag ) {
        if ( ! term_exists( $tag, 'post_tag' ) ) {
            wp_insert_term( $tag, 'post_tag' );
        }
    }
}
