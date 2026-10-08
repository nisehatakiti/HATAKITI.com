<?php
/**
 * HATAKITI-specific MCP Streamable HTTP endpoint.
 *
 * This exposes exactly one write tool to ChatGPT:
 *   create_hatakiti_draft
 *
 * The tool delegates to the existing HATAKITI draft REST handler, so the
 * WordPress-side draft rules remain the single source of truth:
 *   - post_status is always draft
 *   - theatre_essay is placed in 演劇について
 *   - theatre_essay receives 文責：チャッピー when absent
 *
 * No publish/update/delete tool is exposed here.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once HATAKITI_CORE_DIR . 'includes/mcp-oauth.php';

function hatakiti_register_mcp_route() {
    register_rest_route( 'hatakiti/v1', '/mcp', array(
        'methods'             => 'POST',
        'callback'            => 'hatakiti_handle_mcp_request',
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'hatakiti_register_mcp_route' );

/**
 * Return an MCP JSON-RPC response.
 */
function hatakiti_mcp_response( $id, $result ) {
    return new WP_REST_Response(
        array(
            'jsonrpc' => '2.0',
            'id'      => $id,
            'result'  => $result,
        ),
        200,
        array( 'Content-Type' => 'application/json' )
    );
}

/**
 * Return an MCP JSON-RPC error.
 */
function hatakiti_mcp_error( $id, $code, $message, $data = null ) {
    $error = array(
        'code'    => $code,
        'message' => $message,
    );

    if ( null !== $data ) {
        $error['data'] = $data;
    }

    return new WP_REST_Response(
        array(
            'jsonrpc' => '2.0',
            'id'      => $id,
            'error'   => $error,
        ),
        200,
        array( 'Content-Type' => 'application/json' )
    );
}

/**
 * Return the MCP OAuth challenge that causes ChatGPT to launch account linking.
 */
function hatakiti_mcp_auth_required( $id, $error ) {
    $description = $error->get_error_message();
    return new WP_REST_Response(
        array(
            'jsonrpc' => '2.0',
            'id'      => $id,
            'result'  => array(
                'content' => array(
                    array(
                        'type' => 'text',
                        'text' => 'Authentication required: ' . $description,
                    ),
                ),
                '_meta' => array(
                    'mcp/www_authenticate' => array(
                        'Bearer resource_metadata="' . HATAKITI_OAUTH_ISSUER . '/.well-known/oauth-protected-resource", error="invalid_token", error_description="' . $description . '"',
                    ),
                ),
                'isError' => true,
            ),
        ),
        200,
        array( 'Content-Type' => 'application/json' )
    );
}

/**
 * MCP Streamable HTTP request handler.
 *
 * Supports the initialize/tools/list/tools/call lifecycle used by ChatGPT.
 * The endpoint is intentionally stateless.
 */
function hatakiti_handle_mcp_request( WP_REST_Request $request ) {
    $body = $request->get_json_params();

    if ( ! is_array( $body ) ) {
        return hatakiti_mcp_error( null, -32600, 'Invalid JSON-RPC request.' );
    }

    $id     = array_key_exists( 'id', $body ) ? $body['id'] : null;
    $method = isset( $body['method'] ) ? (string) $body['method'] : '';

    switch ( $method ) {
        case 'initialize':
            return hatakiti_mcp_response( $id, array(
                'protocolVersion' => '2025-11-25',
                'capabilities'    => array(
                    'tools' => array(),
                ),
                'serverInfo'      => array(
                    'name'    => 'HATAKITI Draft',
                    'version' => '1.0.0',
                ),
                'instructions'    => 'Use create_hatakiti_draft to create HATAKITI.com WordPress drafts. Never publish content. The WordPress API enforces draft-only behavior.',
            ) );

        case 'notifications/initialized':
            return new WP_REST_Response( null, 202 );

        case 'ping':
            return hatakiti_mcp_response( $id, array() );

        case 'tools/list':
            return hatakiti_mcp_response( $id, array(
                'tools' => array(
                    array(
                        'name'        => 'create_hatakiti_draft',
                        'title'       => 'Create HATAKITI draft',
                        'description' => 'Use this when the user explicitly wants an article or theatre essay registered as a WordPress draft on HATAKITI.com. This tool can create drafts only; it cannot publish, update, or delete posts.',
                        'inputSchema' => array(
                            'type'                 => 'object',
                            'properties'           => array(
                                'content_type' => array(
                                    'type'        => 'string',
                                    'enum'        => array( 'daily', 'theatre_essay', 'theatre_record', 'film_record' ),
                                    'description' => 'HATAKITI content type.',
                                ),
                                'title' => array(
                                    'type'        => 'string',
                                    'description' => 'WordPress draft title.',
                                ),
                                'body' => array(
                                    'type'        => 'string',
                                    'description' => 'WordPress draft body. HTML is allowed.',
                                ),
                                'tags' => array(
                                    'type'        => 'array',
                                    'items'       => array( 'type' => 'string' ),
                                    'description' => 'Optional WordPress tags.',
                                ),
                                'meta' => array(
                                    'type'        => 'object',
                                    'description' => 'Optional structured metadata for theatre_record or film_record.',
                                ),
                            ),
                            'required'             => array( 'content_type', 'title', 'body' ),
                            'additionalProperties' => false,
                        ),
                        'annotations' => array(
                            'readOnlyHint'   => false,
                            'destructiveHint'=> false,
                            'openWorldHint'  => false,
                        ),
                        'securitySchemes' => array(
                            array(
                                'type'   => 'oauth2',
                                'scopes' => array( HATAKITI_OAUTH_SCOPE ),
                            ),
                        ),
                    ),
                ),
            ) );

        case 'tools/call':
            $auth = hatakiti_oauth_validate_access_token( $request );
            if ( is_wp_error( $auth ) ) {
                return hatakiti_mcp_auth_required( $id, $auth );
            }

            $params = isset( $body['params'] ) && is_array( $body['params'] )
                ? $body['params']
                : array();

            $tool_name = isset( $params['name'] ) ? (string) $params['name'] : '';
            if ( 'create_hatakiti_draft' !== $tool_name ) {
                return hatakiti_mcp_error( $id, -32602, 'Unknown tool.' );
            }

            $arguments = isset( $params['arguments'] ) && is_array( $params['arguments'] )
                ? $params['arguments']
                : array();

            foreach ( array( 'content_type', 'title', 'body' ) as $required ) {
                if ( ! array_key_exists( $required, $arguments ) || '' === trim( (string) $arguments[ $required ] ) ) {
                    return hatakiti_mcp_error( $id, -32602, 'Missing required argument: ' . $required );
                }
            }

            $allowed_types = array( 'daily', 'theatre_essay', 'theatre_record', 'film_record' );
            if ( ! in_array( $arguments['content_type'], $allowed_types, true ) ) {
                return hatakiti_mcp_error( $id, -32602, 'Invalid content_type.' );
            }

            // Reuse the existing WordPress endpoint handler so all existing
            // draft-only rules remain authoritative. Bind the OAuth identity
            // to the WordPress user for the duration of this operation.
            $previous_user_id = get_current_user_id();
            wp_set_current_user( (int) $auth['user_id'] );
            $draft_request = new WP_REST_Request( 'POST', '/hatakiti/v1/draft' );
            $draft_request->set_param( 'content_type', $arguments['content_type'] );
            $draft_request->set_param( 'title', $arguments['title'] );
            $draft_request->set_param( 'body', $arguments['body'] );
            $draft_request->set_param( 'tags', isset( $arguments['tags'] ) ? $arguments['tags'] : array() );
            $draft_request->set_param( 'meta', isset( $arguments['meta'] ) ? $arguments['meta'] : array() );

            $draft_result = hatakiti_handle_draft_request( $draft_request );

            if ( is_wp_error( $draft_result ) ) {
                return hatakiti_mcp_error(
                    $id,
                    -32000,
                    $draft_result->get_error_message(),
                    array( 'code' => $draft_result->get_error_code() )
                );
            }

            $data = $draft_result instanceof WP_REST_Response
                ? $draft_result->get_data()
                : $draft_result;

            $text = sprintf(
                'HATAKITI.com draft created: ID %d, status %s, edit link %s',
                (int) $data['id'],
                (string) $data['status'],
                (string) $data['edit_link']
            );

            return hatakiti_mcp_response( $id, array(
                'content' => array(
                    array(
                        'type' => 'text',
                        'text' => $text,
                    ),
                ),
                'structuredContent' => array(
                    'id'        => (int) $data['id'],
                    'post_type' => (string) $data['post_type'],
                    'status'    => (string) $data['status'],
                    'edit_link' => (string) $data['edit_link'],
                ),
                'isError' => false,
            ) );

        default:
            return hatakiti_mcp_error( $id, -32601, 'Method not found.' );
    }
}
