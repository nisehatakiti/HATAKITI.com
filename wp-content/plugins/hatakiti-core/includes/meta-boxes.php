<?php
/**
 * Custom fields for 観劇記録 / 映画記録.
 *
 * Kept as plain post meta + a hand-written meta box rather than pulling in
 * a fields plugin (e.g. ACF) — the blueprint asks for the simplest
 * structure that supports easy entry, not an extra dependency.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const HATAKITI_METHOD_OPTIONS = array( '劇場', '配信', '録画', 'その他' );

function hatakiti_theatre_record_fields() {
    return array(
        'hatakiti_troupe'       => array( 'label' => '劇団名', 'type' => 'text' ),
        'hatakiti_viewing_date' => array( 'label' => '観劇日', 'type' => 'date' ),
        'hatakiti_method'       => array( 'label' => '観劇方法', 'type' => 'select' ),
        'hatakiti_run_start'    => array( 'label' => '公演期間（開始）', 'type' => 'date' ),
        'hatakiti_run_end'      => array( 'label' => '公演期間（終了）', 'type' => 'date' ),
        'hatakiti_venue'        => array( 'label' => '会場', 'type' => 'text' ),
    );
}

function hatakiti_film_record_fields() {
    return array(
        'hatakiti_viewing_date'  => array( 'label' => '鑑賞日', 'type' => 'date' ),
        'hatakiti_director'      => array( 'label' => '監督', 'type' => 'text' ),
        'hatakiti_screenwriter'  => array( 'label' => '脚本', 'type' => 'text' ),
        'hatakiti_release_year'  => array( 'label' => '公開年', 'type' => 'text' ),
        'hatakiti_cast'          => array( 'label' => '出演者', 'type' => 'text' ),
        'hatakiti_method'        => array( 'label' => '鑑賞方法', 'type' => 'select' ),
    );
}

function hatakiti_add_meta_boxes() {
    add_meta_box(
        'hatakiti_theatre_record_fields',
        '観劇記録の詳細',
        'hatakiti_render_meta_box',
        'theatre_record',
        'normal',
        'high',
        array( 'fields' => hatakiti_theatre_record_fields() )
    );

    add_meta_box(
        'hatakiti_film_record_fields',
        '映画記録の詳細',
        'hatakiti_render_meta_box',
        'film_record',
        'normal',
        'high',
        array( 'fields' => hatakiti_film_record_fields() )
    );
}
add_action( 'add_meta_boxes', 'hatakiti_add_meta_boxes' );

function hatakiti_render_meta_box( $post, $box ) {
    wp_nonce_field( 'hatakiti_save_record_fields', 'hatakiti_record_fields_nonce' );
    $fields = $box['args']['fields'];
    echo '<table class="form-table"><tbody>';
    foreach ( $fields as $key => $field ) {
        $value = get_post_meta( $post->ID, $key, true );
        printf( '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td>', esc_attr( $key ), esc_html( $field['label'] ) );

        if ( 'select' === $field['type'] && 'hatakiti_method' === $key ) {
            printf( '<select name="%s" id="%s">', esc_attr( $key ), esc_attr( $key ) );
            echo '<option value="">' . esc_html__( '選択してください', 'hatakiti' ) . '</option>';
            foreach ( HATAKITI_METHOD_OPTIONS as $option ) {
                printf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $option ), selected( $value, $option, false ) );
            }
            echo '</select>';
        } elseif ( 'date' === $field['type'] ) {
            printf( '<input type="date" name="%1$s" id="%1$s" value="%2$s">', esc_attr( $key ), esc_attr( $value ) );
        } else {
            printf( '<input type="text" class="regular-text" name="%1$s" id="%1$s" value="%2$s">', esc_attr( $key ), esc_attr( $value ) );
        }

        echo '</td></tr>';
    }
    echo '</tbody></table>';
}

function hatakiti_save_record_meta( $post_id, $post ) {
    if ( ! isset( $_POST['hatakiti_record_fields_nonce'] ) ||
        ! wp_verify_nonce( $_POST['hatakiti_record_fields_nonce'], 'hatakiti_save_record_fields' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    $type = $post->post_type;
    if ( 'theatre_record' === $type ) {
        $fields = hatakiti_theatre_record_fields();
    } elseif ( 'film_record' === $type ) {
        $fields = hatakiti_film_record_fields();
    } else {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    foreach ( $fields as $key => $field ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            continue;
        }
        $raw = wp_unslash( $_POST[ $key ] );

        if ( 'date' === $field['type'] ) {
            $value = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
        } elseif ( 'hatakiti_method' === $key ) {
            $value = in_array( $raw, HATAKITI_METHOD_OPTIONS, true ) ? $raw : '';
        } else {
            $value = sanitize_text_field( $raw );
        }

        update_post_meta( $post_id, $key, $value );
    }
}
add_action( 'save_post_theatre_record', 'hatakiti_save_record_meta', 10, 2 );
add_action( 'save_post_film_record', 'hatakiti_save_record_meta', 10, 2 );

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
