<?php
/**
 * AI API call layer for 週刊オカルト新聞's AI週次編集機能
 * (occult-weekly-ai-edit.php). Deliberately narrow and swappable:
 *
 *   hatakiti_call_occult_ai_text( $prompt, $system ) -> string|WP_Error
 *
 * is the only entry point the rest of the codebase should use — nothing
 * else in this plugin should know which provider or SDK is behind it.
 *
 * No API key is ever hardcoded or committed. It is read, in order:
 *   1. PHP constants HATAKITI_OCCULT_AI_PROVIDER / _MODEL / _API_KEY,
 *      if defined (e.g. in wp-config.php on the server — never in git)
 *   2. WordPress options, set via 週刊オカルト新聞 → AI設定
 * Constants win when present, so a server-level override always beats
 * whatever is stored in the database.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_occult_ai_provider() {
    if ( defined( 'HATAKITI_OCCULT_AI_PROVIDER' ) && HATAKITI_OCCULT_AI_PROVIDER ) {
        return HATAKITI_OCCULT_AI_PROVIDER;
    }
    return get_option( 'hatakiti_occult_ai_provider', 'anthropic' );
}

/**
 * Model/API key are stored per-provider (hatakiti_occult_ai_model_openai,
 * hatakiti_occult_ai_model_anthropic, etc.) so switching the provider
 * dropdown to test one doesn't overwrite the other's already-working
 * credentials. Falls back to the older, unprefixed option name (from
 * before per-provider storage existed) so an already-configured provider
 * keeps working without needing to be re-entered.
 */
function hatakiti_occult_ai_model() {
    if ( defined( 'HATAKITI_OCCULT_AI_MODEL' ) && HATAKITI_OCCULT_AI_MODEL ) {
        return HATAKITI_OCCULT_AI_MODEL;
    }
    $provider = hatakiti_occult_ai_provider();
    $value    = get_option( 'hatakiti_occult_ai_model_' . $provider, '' );
    return $value ? $value : get_option( 'hatakiti_occult_ai_model', '' );
}

function hatakiti_occult_ai_api_key() {
    if ( defined( 'HATAKITI_OCCULT_AI_API_KEY' ) && HATAKITI_OCCULT_AI_API_KEY ) {
        return HATAKITI_OCCULT_AI_API_KEY;
    }
    $provider = hatakiti_occult_ai_provider();
    $value    = get_option( 'hatakiti_occult_ai_api_key_' . $provider, '' );
    return $value ? $value : get_option( 'hatakiti_occult_ai_api_key', '' );
}

function hatakiti_occult_ai_is_configured() {
    return (bool) ( hatakiti_occult_ai_api_key() && hatakiti_occult_ai_model() );
}

/**
 * Settings page: 週刊オカルト新聞 → AI設定. Provider/model/API key only —
 * separate from the RSS取得 page since these are more sensitive and
 * site-admin-level config, not day-to-day editorial actions.
 */
function hatakiti_register_occult_ai_settings_page() {
    add_submenu_page(
        'edit.php?post_type=occult_weekly',
        'AI設定',
        'AI設定',
        'manage_options',
        'hatakiti-occult-ai-settings',
        'hatakiti_render_occult_ai_settings_page'
    );
}
add_action( 'admin_menu', 'hatakiti_register_occult_ai_settings_page' );

function hatakiti_render_occult_ai_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( '権限がありません。' );
    }

    $saved = false;
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_occult_ai_settings_nonce'] ) ) {
        check_admin_referer( 'hatakiti_occult_ai_settings', 'hatakiti_occult_ai_settings_nonce' );

        $provider = isset( $_POST['hatakiti_occult_ai_provider'] ) ? sanitize_text_field( wp_unslash( $_POST['hatakiti_occult_ai_provider'] ) ) : 'anthropic';
        $provider = in_array( $provider, array( 'anthropic', 'openai' ), true ) ? $provider : 'anthropic';
        update_option( 'hatakiti_occult_ai_provider', $provider, false );

        $model = isset( $_POST['hatakiti_occult_ai_model'] ) ? sanitize_text_field( wp_unslash( $_POST['hatakiti_occult_ai_model'] ) ) : '';
        update_option( 'hatakiti_occult_ai_model_' . $provider, $model, false );

        // Only overwrite the stored key if a new one was actually typed —
        // the field is left blank on reload so the key is never echoed
        // back into the page source.
        $new_key = isset( $_POST['hatakiti_occult_ai_api_key'] ) ? trim( wp_unslash( $_POST['hatakiti_occult_ai_api_key'] ) ) : '';
        if ( '' !== $new_key ) {
            update_option( 'hatakiti_occult_ai_api_key_' . $provider, $new_key, false );
        }
        if ( isset( $_POST['hatakiti_occult_ai_clear_key'] ) ) {
            update_option( 'hatakiti_occult_ai_api_key_' . $provider, '', false );
        }

        $saved = true;
    }

    $provider           = hatakiti_occult_ai_provider();
    $model              = hatakiti_occult_ai_model();
    $key_from_constant  = defined( 'HATAKITI_OCCULT_AI_API_KEY' ) && HATAKITI_OCCULT_AI_API_KEY;
    $key_set            = hatakiti_occult_ai_is_configured() || hatakiti_occult_ai_api_key();
    ?>
    <div class="wrap hatakiti-record-form">
        <h1>週刊オカルト新聞 — AI設定</h1>
        <p class="description">
            「AIで週刊号を作成」機能が呼び出すAI APIの接続先を設定します。APIキーはGitHubへは一切コミットされず、
            WordPressのデータベース（または <code>wp-config.php</code> の定数）にのみ保存されます。
        </p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success"><p>保存しました。</p></div>
        <?php endif; ?>

        <?php if ( $key_from_constant ) : ?>
            <div class="notice notice-info"><p><code>wp-config.php</code> の定数 <code>HATAKITI_OCCULT_AI_API_KEY</code> が設定されているため、そちらが優先して使用されます。下のフォームでの変更はこの定数を上書きしません。</p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'hatakiti_occult_ai_settings', 'hatakiti_occult_ai_settings_nonce' ); ?>
            <table class="form-table" role="presentation"><tbody>
                <tr>
                    <th scope="row"><label for="hatakiti_occult_ai_provider">AIプロバイダー</label></th>
                    <td>
                        <select id="hatakiti_occult_ai_provider" name="hatakiti_occult_ai_provider">
                            <option value="anthropic"<?php selected( $provider, 'anthropic' ); ?>>Anthropic（Claude）</option>
                            <option value="openai"<?php selected( $provider, 'openai' ); ?>>OpenAI（GPT）</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="hatakiti_occult_ai_model">モデル名</label></th>
                    <td>
                        <input type="text" class="regular-text" id="hatakiti_occult_ai_model" name="hatakiti_occult_ai_model" value="<?php echo esc_attr( $model ); ?>" placeholder="例: claude-sonnet-4-5 / gpt-4.1 など">
                        <p class="description">選択したプロバイダーのAPIがそのまま受け付けるモデル名を、プロバイダーの最新ドキュメントで確認して入力してください（このプラグインはモデル名を固定していません）。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="hatakiti_occult_ai_api_key">APIキー</label></th>
                    <td>
                        <input type="password" class="regular-text" id="hatakiti_occult_ai_api_key" name="hatakiti_occult_ai_api_key" value="" autocomplete="off" placeholder="<?php echo $key_set ? '設定済み（変更する場合のみ入力）' : '未設定'; ?>">
                        <?php if ( $key_set && ! $key_from_constant ) : ?>
                            <label style="display:block;margin-top:6px;"><input type="checkbox" name="hatakiti_occult_ai_clear_key" value="1"> 保存済みのAPIキーを削除する</label>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody></table>
            <p class="hatakiti-form-actions">
                <button type="submit" class="button button-primary">保存</button>
            </p>
        </form>

        <h2>現在の状態</h2>
        <p>
            AI呼び出し設定: <strong><?php echo hatakiti_occult_ai_is_configured() ? '設定済み' : '未設定（モデル名・APIキーの両方が必要です）'; ?></strong>
        </p>
    </div>
    <?php
}

/**
 * Single entry point for calling the configured AI provider with a
 * system + user prompt. Returns the raw text response, or WP_Error.
 */
function hatakiti_call_occult_ai_text( $prompt, $system = '' ) {
    $api_key = hatakiti_occult_ai_api_key();
    $model   = hatakiti_occult_ai_model();

    if ( ! $api_key || ! $model ) {
        return new WP_Error(
            'hatakiti_ai_not_configured',
            'AI APIが設定されていません。週刊オカルト新聞 → AI設定 でモデル名とAPIキーを設定してください。'
        );
    }

    $provider = hatakiti_occult_ai_provider();

    switch ( $provider ) {
        case 'openai':
            return hatakiti_call_occult_ai_openai( $prompt, $system, $api_key, $model );
        case 'anthropic':
        default:
            return hatakiti_call_occult_ai_anthropic( $prompt, $system, $api_key, $model );
    }
}

/**
 * Minimal logging for AI call attempts — provider, attempt number, HTTP
 * status, error type, final outcome only. Never logs the API key,
 * Authorization header, prompt text, or news body content (§11 of the
 * instruction). Uses PHP's own error_log() rather than a new logging
 * mechanism, so nothing new has to be built or maintained to read it.
 */
function hatakiti_occult_ai_log( $fields ) {
    $parts = array();
    foreach ( $fields as $key => $value ) {
        $parts[] = $key . '=' . $value;
    }
    error_log( '[hatakiti_occult_ai] ' . implode( ' ', $parts ) );
}

/**
 * A one-time-transient error worth retrying (rate limit / server-side
 * trouble / network hiccup) vs. one that will just fail the same way
 * again (bad auth, bad request, etc.) and shouldn't be retried.
 */
function hatakiti_occult_ai_is_retryable( $is_wp_error, $http_code ) {
    if ( $is_wp_error ) {
        return true; // network error / timeout — worth another attempt
    }
    return in_array( $http_code, array( 429, 500, 502, 503, 504 ), true );
}

/**
 * Shared retry wrapper for both provider adapters — up to 3 attempts
 * total, exponential backoff (2s, then 4s) between retryable failures.
 * Not a general-purpose HTTP client: it only decides retry/no-retry and
 * logs each attempt: the caller still does its own response-code and
 * body handling exactly as before, using whatever this returns (a
 * WP_Error or the last HTTP response, same shape wp_remote_post() itself
 * returns).
 */
function hatakiti_occult_ai_post_with_retry( $url, $args, $provider_label ) {
    $max_attempts = 3;
    $backoff      = array( 2, 4 );
    $response     = null;

    for ( $attempt = 1; $attempt <= $max_attempts; $attempt++ ) {
        $response = wp_remote_post( $url, $args );

        $is_wp_error = is_wp_error( $response );
        $http_code   = $is_wp_error ? 0 : wp_remote_retrieve_response_code( $response );
        $success     = ! $is_wp_error && 200 === $http_code;

        hatakiti_occult_ai_log( array(
            'provider'    => $provider_label,
            'attempt'     => $attempt . '/' . $max_attempts,
            'http_status' => $is_wp_error ? 'n/a' : $http_code,
            'error_type'  => $is_wp_error ? $response->get_error_code() : ( $success ? '' : 'http_error' ),
            'outcome'     => $success ? 'success' : 'failed',
        ) );

        if ( $success ) {
            return $response;
        }

        $retryable = hatakiti_occult_ai_is_retryable( $is_wp_error, $http_code );
        if ( ! $retryable || $attempt === $max_attempts ) {
            hatakiti_occult_ai_log( array(
                'provider' => $provider_label,
                'outcome'  => $retryable ? 'gave_up_after_max_attempts' : 'not_retryable',
            ) );
            return $response;
        }

        sleep( $backoff[ $attempt - 1 ] );
    }

    return $response;
}

function hatakiti_call_occult_ai_anthropic( $prompt, $system, $api_key, $model ) {
    $response = hatakiti_occult_ai_post_with_retry( 'https://api.anthropic.com/v1/messages', array(
        // A real 14-item run at the newspaper-length article targets
        // measured stop_reason:"max_tokens" at 8000 (thinking + text
        // together) after ~113s — both the budget and the HTTP timeout
        // need real headroom for a full week's worth of items, not just
        // the small batches used in earlier testing.
        'timeout' => 280,
        'headers' => array(
            'x-api-key'         => $api_key,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ),
        'body' => wp_json_encode( array(
            'model'      => $model,
            'max_tokens' => 20000,
            'system'     => $system,
            'messages'   => array(
                array( 'role' => 'user', 'content' => $prompt ),
            ),
        ) ),
    ), 'anthropic' );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( 200 !== $code ) {
        $message = isset( $body['error']['message'] ) ? $body['error']['message'] : ( 'HTTPステータス ' . $code );
        return new WP_Error( 'hatakiti_ai_http_error', 'Anthropic API エラー: ' . $message );
    }

    // content is an array of blocks, not always just one — extended-
    // thinking models put a "thinking" block first, so the text block is
    // not reliably index 0 (confirmed against a live response: block[0]
    // type "thinking", block[1] type "text"). Take the first block that
    // is actually text, not the first block.
    $text = '';
    foreach ( (array) ( isset( $body['content'] ) ? $body['content'] : array() ) as $block ) {
        if ( isset( $block['type'] ) && 'text' === $block['type'] && isset( $block['text'] ) ) {
            $text = $block['text'];
            break;
        }
    }
    if ( '' === $text ) {
        return new WP_Error( 'hatakiti_ai_empty_response', 'Anthropic APIから空の応答が返されました（応答にtextブロックが含まれていません）。' );
    }

    return $text;
}

function hatakiti_call_occult_ai_openai( $prompt, $system, $api_key, $model ) {
    $messages = array();
    if ( $system ) {
        $messages[] = array( 'role' => 'system', 'content' => $system );
    }
    $messages[] = array( 'role' => 'user', 'content' => $prompt );

    $response = hatakiti_occult_ai_post_with_retry( 'https://api.openai.com/v1/chat/completions', array(
        // Matched to the Anthropic adapter's headroom (see there for the
        // real measurement this is based on).
        'timeout' => 280,
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'content-type'  => 'application/json',
        ),
        'body' => wp_json_encode( array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => 0.3,
            'max_tokens'  => 20000,
        ) ),
    ), 'openai' );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( 200 !== $code ) {
        $message = isset( $body['error']['message'] ) ? $body['error']['message'] : ( 'HTTPステータス ' . $code );
        return new WP_Error( 'hatakiti_ai_http_error', 'OpenAI API エラー: ' . $message );
    }

    $text = isset( $body['choices'][0]['message']['content'] ) ? $body['choices'][0]['message']['content'] : '';
    if ( '' === $text ) {
        return new WP_Error( 'hatakiti_ai_empty_response', 'OpenAI APIから空の応答が返されました。' );
    }

    return $text;
}

/**
 * LLMs sometimes wrap JSON in ```json fences or add a stray sentence
 * despite being told not to. Tries a direct decode first, then strips
 * fences, then falls back to the outermost {...} substring.
 */
function hatakiti_extract_json_from_ai_text( $text ) {
    $text = trim( (string) $text );

    $decoded = json_decode( $text, true );
    if ( is_array( $decoded ) ) {
        return $decoded;
    }

    if ( preg_match( '/```(?:json)?\s*(.*?)\s*```/s', $text, $m ) ) {
        $decoded = json_decode( trim( $m[1] ), true );
        if ( is_array( $decoded ) ) {
            return $decoded;
        }
    }

    $first = strpos( $text, '{' );
    $last  = strrpos( $text, '}' );
    if ( false !== $first && false !== $last && $last > $first ) {
        $decoded = json_decode( substr( $text, $first, $last - $first + 1 ), true );
        if ( is_array( $decoded ) ) {
            return $decoded;
        }
    }

    return null;
}
