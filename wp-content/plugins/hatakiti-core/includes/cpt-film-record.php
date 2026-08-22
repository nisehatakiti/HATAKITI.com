<?php
/**
 * 映画記録 (FilmViewingRecord) custom post type.
 * docs/03-ContentModel.md §4.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_register_film_record_cpt() {
    register_post_type( 'film_record', array(
        'labels' => array(
            'name'               => '映画記録',
            'singular_name'      => '映画記録',
            'add_new_item'       => '映画記録を追加',
            'edit_item'          => '映画記録を編集',
            'new_item'           => '新規映画記録',
            'view_item'          => '映画記録を表示',
            'search_items'       => '映画記録を検索',
            'not_found'          => '映画記録が見つかりません',
            'all_items'          => 'すべての映画記録',
            'menu_name'          => '映画記録',
        ),
        'public'        => true,
        'has_archive'   => 'eiga-kiroku',
        'rewrite'       => array( 'slug' => 'eiga', 'with_front' => false ),
        'menu_icon'     => 'dashicons-format-video',
        'menu_position' => 6,
        // No 'title' or 'editor' support: 映画記録 is entered exclusively
        // through the dedicated form in includes/admin-forms.php, never
        // through WordPress's native title field / block editor screen.
        'supports'      => array( 'thumbnail' ),
        'taxonomies'    => array( 'post_tag', 'film_genre' ),
        'show_in_rest'  => true,
    ) );
}
add_action( 'init', 'hatakiti_register_film_record_cpt' );

/**
 * 映画記録一覧 (archive) is ordered by 鑑賞日 rather than publish date.
 */
function hatakiti_order_film_record_archive( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'film_record' ) ) {
        $query->set( 'meta_key', 'hatakiti_viewing_date' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'DESC' );
    }
}
add_action( 'pre_get_posts', 'hatakiti_order_film_record_archive' );
