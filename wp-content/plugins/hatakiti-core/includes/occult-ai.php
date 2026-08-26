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

function hatakiti_occult_ai_model() {
    if ( defined( 'HATAKITI_OCCULT_AI_MODEL' ) && HATAKITI_OCCULT_AI_MODEL ) {
        return HATAKITI_OCCULT_AI_MODEL;
    }
    return get_option( 'hatakiti_occult_ai_model', '' );
}

function hatakiti_occult_ai_api_key() {
    if ( defined( 'HATAKITI_OCCULT_AI_API_KEY' ) && HATAKITI_OCCULT_AI_API_KEY ) {
        return HATAKITI_OCCULT_AI_API_KEY;
    }
    return get_option( 'hatakiti_occult_ai_api_key', '' );
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
        update_option( 'hatakiti_occult_ai_model', $model, false );

        // Only overwrite the stored key if a new one was actually typed —
        // the field is left blank on reload so the key is never echoed
        // back into the page source.
        $new_key = isset( $_POST['hatakiti_occult_ai_api_key'] ) ? trim( wp_unslash( $_POST['hatakiti_occult_ai_api_key'] ) ) : '';
        if ( '' !== $new_key ) {
            update_option( 'hatakiti_occult_ai_api_key', $new_key, false );
        }
        if ( isset( $_POST['hatakiti_occult_ai_clear_key'] ) ) {
            update_option( 'hatakiti_occult_ai_api_key', '', false );
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

function hatakiti_call_occult_ai_anthropic( $prompt, $system, $api_key, $model ) {
    $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', array(
        'timeout' => 120,
        'headers' => array(
            'x-api-key'         => $api_key,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ),
        'body' => wp_json_encode( array(
            'model'      => $model,
            'max_tokens' => 8000,
            'system'     => $system,
            'messages'   => array(
                array( 'role' => 'user', 'content' => $prompt ),
            ),
        ) ),
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( 200 !== $code ) {
        $message = isset( $body['error']['message'] ) ? $body['error']['message'] : ( 'HTTPステータス ' . $code );
        return new WP_Error( 'hatakiti_ai_http_error', 'Anthropic API エラー: ' . $message );
    }

    $text = isset( $body['content'][0]['text'] ) ? $body['content'][0]['text'] : '';
    if ( '' === $text ) {
        return new WP_Error( 'hatakiti_ai_empty_response', 'Anthropic APIから空の応答が返されました。' );
    }

    return $text;
}

function hatakiti_call_occult_ai_openai( $prompt, $system, $api_key, $model ) {
    $messages = array();
    if ( $system ) {
        $messages[] = array( 'role' => 'system', 'content' => $system );
    }
    $messages[] = array( 'role' => 'user', 'content' => $prompt );

    $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
        'timeout' => 120,
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'content-type'  => 'application/json',
        ),
        'body' => wp_json_encode( array(
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => 0.3,
        ) ),
    ) );

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
