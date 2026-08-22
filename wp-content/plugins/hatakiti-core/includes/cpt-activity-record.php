<?php
/**
 * 活動履歴 (ActivityRecord) — HATAKITI's own performance/activity history,
 * as distinct from 観劇記録/映画記録 (things HATAKITI watched).
 *
 * Entered through a dedicated form (includes/admin-form-activity.php),
 * same pattern as 観劇記録/映画記録: no native title/editor screen. 活動日
 * is a required custom field, independent of WordPress's own post_date,
 * so it can never go missing the way it did when this only used the
 * native "Published on" date.
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
        // No 'title' or 'editor' support: entered exclusively through the
        // dedicated form, never WordPress's native title field / block
        // editor screen (same reasoning as 観劇記録/映画記録).
        'supports'      => array( 'thumbnail' ),
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
 * Field definitions for the dedicated 活動履歴 form
 * (includes/admin-form-activity.php).
 */
function hatakiti_activity_record_fields() {
    return array(
        'hatakiti_activity_date' => array( 'label' => '活動日', 'type' => 'date', 'required' => true ),
        'hatakiti_related_link'  => array( 'label' => '関連リンク', 'type' => 'url' ),
    );
}

function hatakiti_register_activity_record_meta() {
    register_post_meta( 'activity_record', 'hatakiti_activity_date', array(
        'type'          => 'string',
        'single'        => true,
        'show_in_rest'  => true,
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'activity_record', 'hatakiti_related_link', array(
        'type'          => 'string',
        'single'        => true,
        'show_in_rest'  => true,
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
}
add_action( 'init', 'hatakiti_register_activity_record_meta' );

/**
 * 活動履歴一覧 is ordered by 活動日 rather than WordPress's own post_date,
 * same reasoning as 観劇記録/映画記録 — the two can differ once a record
 * is entered after the fact.
 */
function hatakiti_order_activity_record_archive( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( is_post_type_archive( 'activity_record' ) || is_tax( 'activity_type' ) ) {
        $query->set( 'meta_key', 'hatakiti_activity_date' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'DESC' );
    }
    // The 活動履歴一覧 template groups everything by tag on one page rather
    // than paginating, so it needs the full set, not just one page of it.
    if ( is_post_type_archive( 'activity_record' ) ) {
        $query->set( 'posts_per_page', -1 );
    }
}
add_action( 'pre_get_posts', 'hatakiti_order_activity_record_archive' );
