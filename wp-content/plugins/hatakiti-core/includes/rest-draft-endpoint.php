<?php
/**
 * HATAKITI-specific REST endpoint for the future ChatGPT → WordPress
 * draft workflow (docs/03-ContentModel.md §8):
 *
 *   HATAKITI → ChatGPT → authenticated API connection →
 *   /wp-json/hatakiti/v1/draft → WordPress draft → HATAKITI reviews & publishes
 *
 * This intentionally exposes only a narrow, purpose-built surface instead
 * of the full WordPress REST API. It can only ever create a DRAFT — the
 * post_status is hardcoded and never taken from the request. Publishing
 * remains a manual step HATAKITI performs in wp-admin.
 *
 * This is the smallest useful version of the integration described in the
 * blueprint; it is deliberately not the priority of this implementation
 * pass and can be extended later without changing the site itself.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_register_draft_rest_route() {
    register_rest_route( 'hatakiti/v1', '/draft', array(
        'methods'             => 'POST',
        'callback'            => 'hatakiti_handle_draft_request',
        'permission_callback' => function () {
            return hatakiti_current_user_can_create_draft();
        },
        'args' => array(
            'content_type' => array(
                'required' => true,
                'type'     => 'string',
                'enum'     => array( 'daily', 'theatre_essay', 'theatre_record', 'film_record' ),
            ),
            'title' => array(
                'required' => true,
                'type'     => 'string',
            ),
            'body' => array(
                'required' => true,
                'type'     => 'string',
            ),
            'tags' => array(
                'required' => false,
                'type'     => 'array',
                'items'    => array( 'type' => 'string' ),
            ),
            'meta' => array(
                'required' => false,
                'type'     => 'object',
            ),
        ),
    ) );
}
add_action( 'rest_api_init', 'hatakiti_register_draft_rest_route' );

function hatakiti_handle_draft_request( WP_REST_Request $request ) {
    $content_type = $request->get_param( 'content_type' );
    $title        = sanitize_text_field( $request->get_param( 'title' ) );
    $body         = wp_kses_post( $request->get_param( 'body' ) );
    $tags         = (array) $request->get_param( 'tags' );
    $meta         = (array) $request->get_param( 'meta' );

    $post_type = 'post';
    $category_slug = '';

    switch ( $content_type ) {
        case 'daily':
            $post_type      = 'post';
            $category_slug  = HATAKITI_CAT_NIKKI;
            break;
        case 'theatre_essay':
            $post_type      = 'post';
            $category_slug  = HATAKITI_CAT_ENGEKI;
            if ( ! hatakiti_content_has_credit_line( $body ) ) {
                $body .= "\n\n文責：チャッピー";
            }
            break;
        case 'theatre_record':
            $post_type = 'theatre_record';
            break;
        case 'film_record':
            $post_type = 'film_record';
            break;
    }

    $postarr = array(
        'post_type'    => $post_type,
        'post_title'   => $title,
        'post_content' => $body,
        'post_status'  => 'draft', // Never anything else — publishing is HATAKITI's manual step.
    );

    if ( $category_slug ) {
        $term = get_term_by( 'slug', $category_slug, 'category' );
        if ( $term ) {
            $postarr['post_category'] = array( $term->term_id );
        }
    }

    $post_id = wp_insert_post( $postarr, true );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'hatakiti_draft_failed', $post_id->get_error_message(), array( 'status' => 500 ) );
    }

    if ( ! empty( $tags ) ) {
        wp_set_post_tags( $post_id, array_map( 'sanitize_text_field', $tags ), false );
    }

    if ( in_array( $post_type, array( 'theatre_record', 'film_record' ), true ) && ! empty( $meta ) ) {
        $allowed = ( 'theatre_record' === $post_type )
            ? array_keys( hatakiti_theatre_record_fields() )
            : array_keys( hatakiti_film_record_fields() );

        foreach ( $meta as $key => $value ) {
            if ( in_array( $key, $allowed, true ) ) {
                update_post_meta( $post_id, $key, sanitize_text_field( $value ) );
            }
        }
    }

    return rest_ensure_response( array(
        'id'        => $post_id,
        'post_type' => $post_type,
        'status'    => 'draft',
        'edit_link' => get_edit_post_link( $post_id, 'raw' ),
    ) );
}
