<?php
/**
 * HATAKITI MCP OAuth 2.1 authorization server.
 *
 * Implements authorization-code + PKCE (S256) for the ChatGPT MCP
 * connector. Access and refresh tokens are opaque and stored server-side
 * as hashes in WordPress transients.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const HATAKITI_OAUTH_ISSUER      = 'https://hatakiti.com';
const HATAKITI_MCP_RESOURCE      = 'https://hatakiti.com/wp-json/hatakiti/v1/mcp';
const HATAKITI_OAUTH_SCOPE       = 'hatakiti:draft';
const HATAKITI_OAUTH_ACCESS_TTL  = 3600;
const HATAKITI_OAUTH_REFRESH_TTL = 2592000;

function hatakiti_oauth_add_routes() {
    register_rest_route( 'hatakiti/v1', '/oauth/authorize', array(
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'hatakiti_oauth_authorize',
            'permission_callback' => '__return_true',
        ),
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'hatakiti_oauth_authorize',
            'permission_callback' => '__return_true',
        ),
    ) );

    register_rest_route( 'hatakiti/v1', '/oauth/token', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'hatakiti_oauth_token',
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'hatakiti_oauth_add_routes' );

function hatakiti_oauth_metadata_response( $data ) {
    nocache_headers();
    status_header( 200 );
    header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
    echo wp_json_encode( $data );
    exit;
}

function hatakiti_oauth_metadata_template() {
    $path = wp_parse_url( home_url( add_query_arg( array(), isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/' ) ), PHP_URL_PATH );
    $request_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $request_path = strtok( $request_path, '?' );

    if ( '/.well-known/oauth-protected-resource' === $request_path ) {
        hatakiti_oauth_metadata_response( array(
            'resource'               => HATAKITI_MCP_RESOURCE,
            'authorization_servers'  => array( HATAKITI_OAUTH_ISSUER ),
            'scopes_supported'       => array( HATAKITI_OAUTH_SCOPE ),
            'resource_documentation' => home_url( '/theatre-textbook/' ),
        ) );
    }

    if ( '/.well-known/oauth-authorization-server' === $request_path ) {
        hatakiti_oauth_metadata_response( array(
            'issuer' => HATAKITI_OAUTH_ISSUER,
            'authorization_response_iss_parameter_supported' => true,
            'authorization_endpoint' => rest_url( 'hatakiti/v1/oauth/authorize' ),
            'token_endpoint' => rest_url( 'hatakiti/v1/oauth/token' ),
            'client_id_metadata_document_supported' => true,
            'token_endpoint_auth_methods_supported' => array( 'none' ),
            'code_challenge_methods_supported' => array( 'S256' ),
            'scopes_supported' => array( HATAKITI_OAUTH_SCOPE ),
        ) );
    }
}
add_action( 'template_redirect', 'hatakiti_oauth_metadata_template', 0 );

// Serve OAuth discovery before normal theme/template routing so the
// well-known endpoints are not intercepted by theme or 404 handling.
function hatakiti_oauth_metadata_parse_request( $wp ) {
    $request_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $request_path = strtok( $request_path, '?' );

    if ( '/.well-known/oauth-protected-resource' === $request_path ) {
        hatakiti_oauth_metadata_response( array(
            'resource'              => HATAKITI_MCP_RESOURCE,
            'authorization_servers' => array( HATAKITI_OAUTH_ISSUER ),
            'scopes_supported'      => array( HATAKITI_OAUTH_SCOPE ),
        ) );
    }

    if ( '/.well-known/oauth-authorization-server' === $request_path ) {
        hatakiti_oauth_metadata_response( array(
            'issuer' => HATAKITI_OAUTH_ISSUER,
            'authorization_response_iss_parameter_supported' => true,
            'authorization_endpoint' => rest_url( 'hatakiti/v1/oauth/authorize' ),
            'token_endpoint' => rest_url( 'hatakiti/v1/oauth/token' ),
            'client_id_metadata_document_supported' => true,
            'token_endpoint_auth_methods_supported' => array( 'none' ),
            'code_challenge_methods_supported' => array( 'S256' ),
            'scopes_supported' => array( HATAKITI_OAUTH_SCOPE ),
        ) );
    }
}
add_action( 'parse_request', 'hatakiti_oauth_metadata_parse_request', 0 );

function hatakiti_oauth_client_metadata( $client_id ) {
    if ( ! is_string( $client_id ) || 0 !== strpos( $client_id, 'https://' ) ) {
        return new WP_Error( 'invalid_client', 'Invalid client_id.' );
    }

    $parts = wp_parse_url( $client_id );
    if ( empty( $parts['host'] ) || 'chatgpt.com' !== strtolower( $parts['host'] ) ) {
        return new WP_Error( 'invalid_client', 'Only ChatGPT client metadata documents are accepted.' );
    }

    $path = isset( $parts['path'] ) ? $parts['path'] : '';
    if ( ! preg_match( '#^/oauth(?:/[^/]+)?/client\.json$#', $path ) ) {
        return new WP_Error( 'invalid_client', 'Unsupported client metadata document.' );
    }

    $response = wp_remote_get( $client_id, array(
        'timeout'     => 5,
        'redirection' => 0,
        'headers'     => array( 'Accept' => 'application/json' ),
    ) );
    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'invalid_client', 'Unable to retrieve client metadata.' );
    }

    if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return new WP_Error( 'invalid_client', 'Client metadata returned an invalid status.' );
    }

    $metadata = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! is_array( $metadata ) || empty( $metadata['client_id'] ) || $metadata['client_id'] !== $client_id ) {
        return new WP_Error( 'invalid_client', 'Client metadata is invalid.' );
    }

    if ( empty( $metadata['redirect_uris'] ) || ! is_array( $metadata['redirect_uris'] ) ) {
        return new WP_Error( 'invalid_client', 'Client metadata does not declare redirect URIs.' );
    }

    return $metadata;
}

function hatakiti_oauth_base64url( $data ) {
    return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
}

function hatakiti_oauth_validate_request( $params ) {
    foreach ( array( 'response_type', 'client_id', 'redirect_uri', 'code_challenge', 'code_challenge_method', 'resource' ) as $key ) {
        if ( empty( $params[ $key ] ) ) {
            return new WP_Error( 'invalid_request', 'Missing required OAuth parameter: ' . $key );
        }
    }

    if ( 'code' !== $params['response_type'] ) {
        return new WP_Error( 'unsupported_response_type', 'Only response_type=code is supported.' );
    }
    if ( 'S256' !== $params['code_challenge_method'] ) {
        return new WP_Error( 'invalid_request', 'Only PKCE S256 is supported.' );
    }
    if ( HATAKITI_MCP_RESOURCE !== $params['resource'] ) {
        return new WP_Error( 'invalid_target', 'Invalid resource.' );
    }

    $client = hatakiti_oauth_client_metadata( $params['client_id'] );
    if ( is_wp_error( $client ) ) {
        return $client;
    }
    if ( ! in_array( $params['redirect_uri'], $client['redirect_uris'], true ) ) {
        return new WP_Error( 'invalid_request', 'The redirect_uri is not registered for this client.' );
    }

    $requested_scopes = empty( $params['scope'] ) ? array() : preg_split( '/\s+/', trim( $params['scope'] ) );
    if ( ! in_array( HATAKITI_OAUTH_SCOPE, $requested_scopes, true ) ) {
        return new WP_Error( 'invalid_scope', 'The required HATAKITI draft scope was not requested.' );
    }

    return $client;
}

function hatakiti_oauth_error_redirect( $params, $error, $description ) {
    if ( empty( $params['redirect_uri'] ) || empty( $params['client_id'] ) ) {
        return new WP_Error( $error, $description );
    }

    $client = hatakiti_oauth_client_metadata( $params['client_id'] );
    if ( is_wp_error( $client ) || empty( $client['redirect_uris'] ) || ! in_array( $params['redirect_uri'], $client['redirect_uris'], true ) ) {
        return new WP_Error( $error, $description );
    }

    $query = array(
        'error'             => $error,
        'error_description' => $description,
        'iss'               => HATAKITI_OAUTH_ISSUER,
    );
    if ( ! empty( $params['state'] ) ) {
        $query['state'] = $params['state'];
    }

    wp_redirect( add_query_arg( $query, $params['redirect_uri'] ) );
    exit;
}

function hatakiti_oauth_authorize( WP_REST_Request $request ) {
    $params = 'POST' === $request->get_method() ? $request->get_body_params() : $request->get_query_params();

    $validation = hatakiti_oauth_validate_request( $params );
    if ( is_wp_error( $validation ) ) {
        hatakiti_oauth_error_redirect( $params, $validation->get_error_code(), $validation->get_error_message() );
        return $validation;
    }

    if ( ! is_user_logged_in() ) {
        // Keep the OAuth flow on the same HATAKITI origin instead of bouncing
        // through wp-login.php. This avoids losing the OAuth request/session
        // state when ChatGPT follows the external authorization redirect.
        if ( 'POST' === $request->get_method() && isset( $params['hatakiti_login'] ) ) {
            if ( empty( $params['_wpnonce'] ) || ! wp_verify_nonce( $params['_wpnonce'], 'hatakiti_oauth_login' ) ) {
                return new WP_Error( 'invalid_request', 'Invalid login request.' );
            }

            $credentials = array(
                'user_login'    => isset( $params['log'] ) ? sanitize_user( $params['log'] ) : '',
                'user_password' => isset( $params['pwd'] ) ? $params['pwd'] : '',
                'remember'      => ! empty( $params['rememberme'] ),
            );

            $user = wp_signon( $credentials, is_ssl() );
            if ( is_wp_error( $user ) ) {
                $login_error = $user->get_error_message();
            } else {
                wp_set_current_user( $user->ID );
                $redirect_url = add_query_arg( $params, rest_url( 'hatakiti/v1/oauth/authorize' ) );
                wp_redirect( $redirect_url );
                exit;
            }
        }

        $hidden = '';
        foreach ( $params as $key => $value ) {
            if ( in_array( $key, array( '_wpnonce', 'hatakiti_login', 'log', 'pwd', 'rememberme' ), true ) || is_array( $value ) ) {
                continue;
            }
            $hidden .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
        }

        $error_html = isset( $login_error ) ? '<p style="color:#b32d2e;background:#fbeaea;padding:10px;border-radius:4px">' . wp_kses_post( $login_error ) . '</p>' : '';

        $login_html = '<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>HATAKITI Draft ログイン</title></head><body style="font-family:sans-serif;max-width:520px;margin:48px auto;padding:24px"><h1>HATAKITI Draft</h1><p>HATAKITI.comのWordPressアカウントでログインしてください。</p>' . $error_html . '<form method="post">' . wp_nonce_field( 'hatakiti_oauth_login', '_wpnonce', true, false ) . $hidden . '<p><label>ユーザー名またはメールアドレス<br><input type="text" name="log" autocomplete="username" required style="width:100%;padding:10px;box-sizing:border-box"></label></p><p><label>パスワード<br><input type="password" name="pwd" autocomplete="current-password" required style="width:100%;padding:10px;box-sizing:border-box"></label></p><p><label><input type="checkbox" name="rememberme" value="forever"> ログイン状態を保存する</label></p><button type="submit" name="hatakiti_login" value="1" style="padding:10px 20px">ログインして続行</button></form></body></html>';

        wp_die( $login_html, 'HATAKITI Draft', array(
            'response' => 200,
            'type'     => 'text/html',
            'exit'     => true,
        ) );
    }

    if ( ! hatakiti_current_user_can_create_draft() ) {
        hatakiti_oauth_error_redirect(
            $params,
            'access_denied',
            'This WordPress account is not allowed to create HATAKITI drafts.'
        );
    }

    if ( 'POST' === $request->get_method() ) {
        if ( empty( $params['_wpnonce'] ) || ! wp_verify_nonce( $params['_wpnonce'], 'hatakiti_oauth_consent' ) ) {
            return new WP_Error( 'invalid_request', 'Invalid consent request.' );
        }

        $code = hatakiti_oauth_base64url( random_bytes( 48 ) );
        set_transient( 'hatakiti_oauth_code_' . hash( 'sha256', $code ), array(
            'user_id'        => get_current_user_id(),
            'client_id'      => $params['client_id'],
            'redirect_uri'   => $params['redirect_uri'],
            'scope'          => HATAKITI_OAUTH_SCOPE,
            'code_challenge' => $params['code_challenge'],
            'resource'       => HATAKITI_MCP_RESOURCE,
        ), 300 );

        $redirect_url = add_query_arg( array(
            'code'  => $code,
            'iss'   => HATAKITI_OAUTH_ISSUER,
            'state' => isset( $params['state'] ) ? $params['state'] : '',
        ), $params['redirect_uri'] );
        // ChatGPT's OAuth callback is an external host, so use the normal
        // redirect function rather than WordPress's internal-host-only helper.
        wp_redirect( $redirect_url );
        exit;
    }

    $hidden = '';
    foreach ( $params as $key => $value ) {
        if ( in_array( $key, array( '_wpnonce' ), true ) || is_array( $value ) ) {
            continue;
        }
        $hidden .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
    }

    $html = '<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>HATAKITI Draftの接続</title></head><body style="font-family:sans-serif;max-width:640px;margin:48px auto;padding:24px"><h1>HATAKITI Draft</h1><p>ChatGPTからHATAKITI.comへ記事を<strong>下書きとして登録</strong>する権限を要求しています。</p><p>公開・削除・更新・管理者操作は許可されません。</p><form method="post">' . wp_nonce_field( 'hatakiti_oauth_consent', '_wpnonce', true, false ) . $hidden . '<button type="submit" style="padding:10px 20px">許可して接続</button></form></body></html>';

    wp_die( $html, 'HATAKITI Draft', array(
        'response' => 200,
        'type'     => 'text/html',
        'exit'     => true,
    ) );
}

function hatakiti_oauth_token( WP_REST_Request $request ) {
    $params = $request->get_body_params();
    $grant_type = isset( $params['grant_type'] ) ? $params['grant_type'] : '';
    $client_id  = isset( $params['client_id'] ) ? $params['client_id'] : '';

    if ( ! $client_id ) {
        return new WP_Error( 'invalid_client', 'client_id is required.' );
    }

    $client = hatakiti_oauth_client_metadata( $client_id );
    if ( is_wp_error( $client ) ) {
        return $client;
    }

    if ( HATAKITI_MCP_RESOURCE !== ( isset( $params['resource'] ) ? $params['resource'] : HATAKITI_MCP_RESOURCE ) ) {
        return new WP_Error( 'invalid_target', 'Invalid resource.' );
    }

    if ( 'authorization_code' === $grant_type ) {
        $code = isset( $params['code'] ) ? $params['code'] : '';
        $redirect_uri = isset( $params['redirect_uri'] ) ? $params['redirect_uri'] : '';
        $verifier = isset( $params['code_verifier'] ) ? $params['code_verifier'] : '';

        if ( ! $code || ! $redirect_uri || ! $verifier ) {
            return new WP_Error( 'invalid_request', 'code, redirect_uri and code_verifier are required.' );
        }

        $key = 'hatakiti_oauth_code_' . hash( 'sha256', $code );
        $record = get_transient( $key );
        delete_transient( $key );

        if ( ! is_array( $record ) || $record['client_id'] !== $client_id || $record['redirect_uri'] !== $redirect_uri ) {
            return new WP_Error( 'invalid_grant', 'Invalid or expired authorization code.' );
        }

        $expected = hatakiti_oauth_base64url( hash( 'sha256', $verifier, true ) );
        if ( ! hash_equals( $record['code_challenge'], $expected ) ) {
            return new WP_Error( 'invalid_grant', 'PKCE verification failed.' );
        }

        $access_token  = hatakiti_oauth_base64url( random_bytes( 48 ) );
        $refresh_token = hatakiti_oauth_base64url( random_bytes( 48 ) );

        set_transient( 'hatakiti_oauth_access_' . hash( 'sha256', $access_token ), array(
            'user_id'   => (int) $record['user_id'],
            'client_id' => $client_id,
            'resource'  => HATAKITI_MCP_RESOURCE,
            'scope'     => HATAKITI_OAUTH_SCOPE,
        ), HATAKITI_OAUTH_ACCESS_TTL );

        set_transient( 'hatakiti_oauth_refresh_' . hash( 'sha256', $refresh_token ), array(
            'user_id'   => (int) $record['user_id'],
            'client_id' => $client_id,
            'resource'  => HATAKITI_MCP_RESOURCE,
            'scope'     => HATAKITI_OAUTH_SCOPE,
        ), HATAKITI_OAUTH_REFRESH_TTL );

        return hatakiti_oauth_token_response( $access_token, $refresh_token );
    }

    if ( 'refresh_token' === $grant_type ) {
        $refresh_token = isset( $params['refresh_token'] ) ? $params['refresh_token'] : '';
        $record = get_transient( 'hatakiti_oauth_refresh_' . hash( 'sha256', $refresh_token ) );

        if ( ! is_array( $record ) || $record['client_id'] !== $client_id ) {
            return new WP_Error( 'invalid_grant', 'Invalid or expired refresh token.' );
        }
        if ( ! user_can( (int) $record['user_id'], HATAKITI_DRAFT_CAP ) ) {
            return new WP_Error( 'invalid_grant', 'The WordPress account no longer has draft permission.' );
        }

        $access_token = hatakiti_oauth_base64url( random_bytes( 48 ) );
        set_transient( 'hatakiti_oauth_access_' . hash( 'sha256', $access_token ), $record, HATAKITI_OAUTH_ACCESS_TTL );

        return hatakiti_oauth_token_response( $access_token, $refresh_token );
    }

    return new WP_Error( 'unsupported_grant_type', 'Only authorization_code and refresh_token are supported.' );
}

function hatakiti_oauth_token_response( $access_token, $refresh_token ) {
    nocache_headers();

    return new WP_REST_Response( array(
        'access_token'  => $access_token,
        'token_type'    => 'Bearer',
        'expires_in'    => HATAKITI_OAUTH_ACCESS_TTL,
        'refresh_token' => $refresh_token,
        'scope'         => HATAKITI_OAUTH_SCOPE,
    ), 200, array(
        'Cache-Control' => 'no-store',
        'Pragma'        => 'no-cache',
    ) );
}

function hatakiti_oauth_validate_access_token( WP_REST_Request $request ) {
    $header = $request->get_header( 'authorization' );

    if ( ! $header || 0 !== stripos( $header, 'Bearer ' ) ) {
        return new WP_Error( 'unauthorized', 'Bearer access token required.', array( 'status' => 401 ) );
    }

    $token = trim( substr( $header, 7 ) );
    $record = get_transient( 'hatakiti_oauth_access_' . hash( 'sha256', $token ) );

    if ( ! is_array( $record ) || $record['resource'] !== HATAKITI_MCP_RESOURCE || $record['scope'] !== HATAKITI_OAUTH_SCOPE ) {
        return new WP_Error( 'unauthorized', 'Invalid or expired access token.', array( 'status' => 401 ) );
    }

    if ( ! user_can( (int) $record['user_id'], HATAKITI_DRAFT_CAP ) ) {
        return new WP_Error( 'forbidden', 'The authenticated account cannot create drafts.', array( 'status' => 403 ) );
    }

    return $record;
}
