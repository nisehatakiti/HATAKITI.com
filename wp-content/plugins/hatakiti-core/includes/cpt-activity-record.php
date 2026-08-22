<?php
/**
 * 活動履歴 (ActivityRecord) — HATAKITI's own performance/activity history,
 * as distinct from 観劇記録/映画記録 (things HATAKITI watched).
 *
 * Deliberately NOT a rigid fixed form like theatre_record/film_record —
 * this uses WordPress's normal title + editor + featured image + tags,
 * plus one lightweight taxonomy (活動種別) and one custom field (関連
 * リンク). No dedicated admin screen, no block-editor lockout.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_register_activity_record_cpt() {
    register_post_type( 'activity_record', array(
        'labels' => array(
            'name'          => '活動履歴',
            'singular_name' => '活動履歴',
            'add_new_item'  => '活動履歴を追加',
            'edit_item'     => '活動履歴を編集',
            'new_item'      => '新規活動履歴',
            'view_item'     => '活動履歴を表示',
            'search_items'  => '活動履歴を検索',
            'not_found'     => '活動履歴が見つかりません',
            'all_items'     => 'すべての活動履歴',
            'menu_name'     => '活動履歴',
        ),
        'public'        => true,
        'has_archive'   => 'katsudo-rireki',
        'rewrite'       => array( 'slug' => 'katsudo', 'with_front' => false ),
        'menu_icon'     => 'dashicons-groups',
        'menu_position' => 7,
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'taxonomies'    => array( 'post_tag', 'activity_type' ),
        'show_in_rest'  => true,
    ) );
}
add_action( 'init', 'hatakiti_register_activity_record_cpt' );

function hatakiti_register_activity_type_taxonomy() {
    register_taxonomy( 'activity_type', array( 'activity_record' ), array(
        'labels' => array(
            'name'          => '活動種別',
            'singular_name' => '活動種別',
            'search_items'  => '活動種別を検索',
            'all_items'     => 'すべての活動種別',
            'edit_item'     => '活動種別を編集',
            'update_item'   => '活動種別を更新',
            'add_new_item'  => '新規活動種別を追加',
            'new_item_name' => '新規活動種別名',
            'menu_name'     => '活動種別',
        ),
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'katsudo-shubetsu' ),
        // Checkbox list, same as film_genre — a fixed-ish starter list that
        // can still grow, not free tagging.
        'meta_box_cb'       => 'post_categories_meta_box',
    ) );
}
add_action( 'init', 'hatakiti_register_activity_type_taxonomy' );

function hatakiti_seed_activity_type_terms() {
    $types = array( '出演', '演出', '制作', '脚本', 'その他' );
    foreach ( $types as $type ) {
        if ( ! term_exists( $type, 'activity_type' ) ) {
            wp_insert_term( $type, 'activity_type' );
        }
    }
}

/**
 * 関連リンク (related link) — the one custom field this content type
 * needs. A plain native meta box, not the dedicated-form treatment used
 * for theatre_record/film_record.
 */
function hatakiti_add_activity_link_meta_box() {
    add_meta_box(
        'hatakiti_activity_link',
        '関連リンク',
        'hatakiti_render_activity_link_meta_box',
        'activity_record',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'hatakiti_add_activity_link_meta_box' );

function hatakiti_render_activity_link_meta_box( $post ) {
    wp_nonce_field( 'hatakiti_save_activity_link', 'hatakiti_activity_link_nonce' );
    $value = get_post_meta( $post->ID, 'hatakiti_related_link', true );
    printf(
        '<label for="hatakiti_related_link" class="screen-reader-text">関連リンクURL</label><input type="url" class="widefat" id="hatakiti_related_link" name="hatakiti_related_link" value="%s" placeholder="https://...">',
        esc_attr( $value )
    );
}

function hatakiti_save_activity_link_meta( $post_id ) {
    if ( ! isset( $_POST['hatakiti_activity_link_nonce'] ) ||
        ! wp_verify_nonce( $_POST['hatakiti_activity_link_nonce'], 'hatakiti_save_activity_link' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['hatakiti_related_link'] ) ) {
        update_post_meta( $post_id, 'hatakiti_related_link', esc_url_raw( wp_unslash( $_POST['hatakiti_related_link'] ) ) );
    }
}
add_action( 'save_post_activity_record', 'hatakiti_save_activity_link_meta' );

function hatakiti_register_activity_link_meta() {
    register_post_meta( 'activity_record', 'hatakiti_related_link', array(
        'type'          => 'string',
        'single'        => true,
        'show_in_rest'  => true,
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
}
add_action( 'init', 'hatakiti_register_activity_link_meta' );
