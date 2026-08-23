<?php
/**
 * Field definitions shared by 観劇記録 / 映画記録.
 *
 * These are deliberately NOT free-form WordPress posts — they are a small,
 * fixed set of fields HATAKITI fills in the same way every time. The
 * actual data-entry UI lives entirely in includes/admin-forms.php (a
 * dedicated form screen, not the native post editor); this file only
 * defines what the fields are, so both the form and the REST draft
 * endpoint (includes/rest-draft-endpoint.php) share one definition.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const HATAKITI_THEATRE_METHOD_OPTIONS = array( '劇場', '配信', '録画', 'その他' );
const HATAKITI_FILM_METHOD_OPTIONS    = array( '映画館', '配信', '録画', 'その他' );

function hatakiti_theatre_record_fields() {
    return array(
        'hatakiti_troupe'       => array( 'label' => '劇団名', 'type' => 'text' ),
        'hatakiti_direction'    => array( 'label' => '演出', 'type' => 'text' ),
        'hatakiti_script'       => array( 'label' => '脚本', 'type' => 'text' ),
        'hatakiti_viewing_date' => array( 'label' => '観劇日', 'type' => 'date' ),
        'hatakiti_run_start'    => array( 'label' => '公演開始日', 'type' => 'date' ),
        'hatakiti_run_end'      => array( 'label' => '公演終了日', 'type' => 'date' ),
        'hatakiti_venue'        => array( 'label' => '劇場', 'type' => 'text' ),
        'hatakiti_method'       => array( 'label' => '観劇方法', 'type' => 'select', 'options' => HATAKITI_THEATRE_METHOD_OPTIONS ),
    );
}

function hatakiti_film_record_fields() {
    return array(
        'hatakiti_viewing_date' => array( 'label' => '鑑賞日', 'type' => 'date' ),
        'hatakiti_director'     => array( 'label' => '監督', 'type' => 'text' ),
        'hatakiti_screenwriter' => array( 'label' => '脚本', 'type' => 'text' ),
        'hatakiti_release_year' => array( 'label' => '公開年', 'type' => 'text' ),
        'hatakiti_cast'         => array( 'label' => '出演者', 'type' => 'text' ),
        'hatakiti_method'       => array( 'label' => '鑑賞方法', 'type' => 'select', 'options' => HATAKITI_FILM_METHOD_OPTIONS ),
    );
}

/**
 * Registers meta with REST support, so a future ChatGPT draft-creation
 * request (see includes/rest-draft-endpoint.php) can set these same fields.
 */
function hatakiti_register_post_meta() {
    $all_fields = hatakiti_theatre_record_fields() + hatakiti_film_record_fields();
    foreach ( array_keys( $all_fields ) as $key ) {
        register_post_meta( '', $key, array(
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function() {
                return current_user_can( 'edit_posts' );
            },
        ) );
    }
}
add_action( 'init', 'hatakiti_register_post_meta' );
