<?php
/**
 * 観劇記録 (TheatreViewingRecord) custom post type.
 * docs/03-ContentModel.md §3.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_register_theatre_record_cpt() {
    register_post_type( 'theatre_record', array(
        'labels' => array(
            'name'               => '観劇記録',
            'singular_name'      => '観劇記録',
            'add_new_item'       => '観劇記録を追加',
            'edit_item'          => '観劇記録を編集',
            'new_item'           => '新規観劇記録',
            'view_item'          => '観劇記録を表示',
            'search_items'       => '観劇記録を検索',
            'not_found'          => '観劇記録が見つかりません',
            'all_items'          => 'すべての観劇記録',
            'menu_name'          => '観劇記録',
        ),
        'public'        => true,
        'has_archive'   => 'kangeki-kiroku',
        'rewrite'       => array( 'slug' => 'kangeki', 'with_front' => false ),
        'menu_icon'     => 'dashicons-tickets-alt',
        'menu_position' => 5,
        // No 'title' or 'editor' support: 観劇記録 is entered exclusively
        // through the dedicated form in includes/admin-forms.php, never
        // through WordPress's native title field / block editor screen.
        'supports'      => array( 'thumbnail' ),
        'taxonomies'    => array( 'post_tag' ),
        'show_in_rest'  => true,
    ) );
}
add_action( 'init', 'hatakiti_register_theatre_record_cpt' );

/**
 * 観劇記録一覧 (archive) is ordered by the actual 観劇日 rather than the
 * WordPress publish date, since those can differ once records are entered
 * after the fact.
 */
function hatakiti_order_theatre_record_archive( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'theatre_record' ) ) {
        $query->set( 'meta_key', 'hatakiti_viewing_date' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'DESC' );
    }
}
add_action( 'pre_get_posts', 'hatakiti_order_theatre_record_archive' );
