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
/**
 * @param callable|null $body_check Optional override for the Anthropic
 *   body_check passed to hatakiti_occult_ai_post_with_retry(). Default
 *   null preserves the exact existing behavior (the newspaper-article
 *   structure check) for every existing caller. Only a caller expecting
 *   a different JSON shape (e.g. occult-news-category-classify.php's
 *   single-field category prompt) needs to pass one — the OpenAI adapter
 *   ignores this entirely, unchanged.
 */
/**
 * @param int   $max_tokens  Anthropic/OpenAI max_tokens for this call.
 *   Defaults to the original hardcoded 20000 so every caller that doesn't
 *   pass this is completely unaffected.
 * @param int   $timeout     wp_remote_post() timeout (seconds) for this
 *   call. Same default-preserving rationale as $max_tokens.
 * @param array $log_context Extra fields merged into every attempt's log
 *   line (source_articles, prompt_chars, phase, batch_index, ...) — purely
 *   diagnostic, never affects behavior. See §1 of the 2026-09-07 timeout
 *   investigation instruction for the field list this exists to satisfy.
 */
function hatakiti_call_occult_ai_text( $prompt, $system = '', $body_check = null, $max_tokens = 20000, $timeout = 280, $log_context = array() ) {
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
            return hatakiti_call_occult_ai_openai( $prompt, $system, $api_key, $model, $max_tokens, $timeout, $log_context );
        case 'anthropic':
        default:
            return null !== $body_check
                ? hatakiti_call_occult_ai_anthropic( $prompt, $system, $api_key, $model, $body_check, $max_tokens, $timeout, $log_context )
                : hatakiti_call_occult_ai_anthropic( $prompt, $system, $api_key, $model, 'hatakiti_occult_ai_anthropic_body_check', $max_tokens, $timeout, $log_context );
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
 * Minimal structural check on the article JSON a provider is supposed to
 * have returned (the payload hatakiti_extract_json_from_ai_text() decoded,
 * not the provider's own response envelope). Deliberately shallow — this
 * only asks "is this response usable at all", not "is every field valid
 * for saving" (hatakiti_process_occult_ai_response() in
 * occult-weekly-ai-edit.php already owns that, unchanged by this).
 * A response that fails this check is exactly the shape a body cut off
 * mid-stream produces: valid-looking JSON at the start, then missing or
 * malformed structure — so it's routed back into the retry loop instead
 * of being handed to the save path.
 */
function hatakiti_occult_ai_validate_response_structure( $decoded ) {
    if ( ! is_array( $decoded ) ) {
        return new WP_Error( 'hatakiti_ai_invalid_json', 'AI応答をJSONとして解析できませんでした。' );
    }
    if ( empty( $decoded['articles'] ) || ! is_array( $decoded['articles'] ) ) {
        return new WP_Error( 'hatakiti_ai_no_articles', 'articlesが存在しないか空です。' );
    }
    foreach ( $decoded['articles'] as $i => $article ) {
        if ( ! is_array( $article ) ) {
            return new WP_Error( 'hatakiti_ai_bad_article', "articles[{$i}]が配列ではありません。" );
        }
        foreach ( array( 'headline', 'importance', 'body', 'source_item_ids' ) as $field ) {
            if ( ! isset( $article[ $field ] ) ) {
                return new WP_Error( 'hatakiti_ai_missing_field', "articles[{$i}]に{$field}がありません。" );
            }
        }
    }
    return true;
}

/**
 * Anthropic-specific body_check for hatakiti_occult_ai_post_with_retry():
 * extracts the text block from Anthropic's response envelope, runs the
 * existing hatakiti_extract_json_from_ai_text() on it, and validates the
 * result's structure. Also surfaces stop_reason/token usage so the retry
 * wrapper can log them regardless of outcome — this is the diagnostic
 * visibility that was missing when a truncated-but-HTTP-200 response was
 * previously treated as a successful call.
 */
function hatakiti_occult_ai_anthropic_body_check( $decoded_response_body ) {
    $diag = array();
    if ( is_array( $decoded_response_body ) ) {
        $diag['stop_reason']   = $decoded_response_body['stop_reason'] ?? 'n/a';
        $diag['input_tokens']  = $decoded_response_body['usage']['input_tokens'] ?? 'n/a';
        $diag['output_tokens'] = $decoded_response_body['usage']['output_tokens'] ?? 'n/a';
    }

    if ( ! is_array( $decoded_response_body ) ) {
        return array( 'ok' => false, 'error_message' => 'Anthropicレスポンスの本体がJSONとして解析できません。', 'diag' => $diag );
    }

    $text = '';
    foreach ( (array) ( $decoded_response_body['content'] ?? array() ) as $block ) {
        if ( isset( $block['type'] ) && 'text' === $block['type'] && isset( $block['text'] ) ) {
            $text = $block['text'];
            break;
        }
    }
    if ( '' === $text ) {
        $diag['json'] = 'n/a';
        return array( 'ok' => false, 'error_message' => 'Anthropicレスポンスにtextブロックがありません。', 'diag' => $diag );
    }

    $article_json = hatakiti_extract_json_from_ai_text( $text );
    $structure_ok = hatakiti_occult_ai_validate_response_structure( $article_json );
    if ( is_wp_error( $structure_ok ) ) {
        $diag['json'] = 'failed';
        return array( 'ok' => false, 'error_message' => $structure_ok->get_error_message(), 'diag' => $diag );
    }

    $diag['json'] = 'success';
    return array( 'ok' => true, 'error_message' => null, 'diag' => $diag );
}

/**
 * Shallow structural check for the STEP1 (planning/clustering) response —
 * docs/16, 2026-09-07 timeout-investigation instruction §3. This response
 * carries no article body text (that's STEP2's job), only the clustering
 * decision, so a truncated/incomplete response here is far cheaper to
 * detect and retry than the old single-mega-call design ever was.
 */
function hatakiti_occult_ai_validate_planning_structure( $decoded, $valid_ids ) {
    if ( ! is_array( $decoded ) ) {
        return new WP_Error( 'hatakiti_ai_invalid_json', 'AI応答をJSONとして解析できませんでした。' );
    }
    if ( empty( $decoded['groups'] ) || ! is_array( $decoded['groups'] ) ) {
        return new WP_Error( 'hatakiti_ai_no_groups', 'groupsが存在しないか空です。' );
    }
    foreach ( $decoded['groups'] as $i => $group ) {
        if ( ! is_array( $group ) ) {
            return new WP_Error( 'hatakiti_ai_bad_group', "groups[{$i}]が配列ではありません。" );
        }
        if ( empty( $group['importance'] ) || ! in_array( $group['importance'], array( 'headline', 'major', 'minor' ), true ) ) {
            return new WP_Error( 'hatakiti_ai_bad_importance', "groups[{$i}]のimportanceが不正です。" );
        }
        if ( empty( $group['item_ids'] ) || ! is_array( $group['item_ids'] ) ) {
            return new WP_Error( 'hatakiti_ai_bad_item_ids', "groups[{$i}]のitem_idsが存在しないか空です。" );
        }
        $has_valid = false;
        foreach ( $group['item_ids'] as $sid ) {
            if ( in_array( (int) $sid, $valid_ids, true ) ) {
                $has_valid = true;
                break;
            }
        }
        if ( ! $has_valid ) {
            return new WP_Error( 'hatakiti_ai_no_valid_ids', "groups[{$i}]に有効なitem_idsがありません。" );
        }
    }
    return true;
}

/**
 * Anthropic-specific body_check wrapper for the planning call — same
 * envelope-unwrap pattern as hatakiti_occult_ai_anthropic_body_check(),
 * just validating the planning-shaped JSON instead of the full article
 * structure. $valid_ids must be captured by the caller's closure since
 * body_check callables only receive the decoded response body.
 */
function hatakiti_occult_ai_planning_body_check( $decoded_response_body, $valid_ids ) {
    $diag = array();
    if ( is_array( $decoded_response_body ) ) {
        $diag['stop_reason']   = $decoded_response_body['stop_reason'] ?? 'n/a';
        $diag['input_tokens']  = $decoded_response_body['usage']['input_tokens'] ?? 'n/a';
        $diag['output_tokens'] = $decoded_response_body['usage']['output_tokens'] ?? 'n/a';
    }

    if ( ! is_array( $decoded_response_body ) ) {
        return array( 'ok' => false, 'error_message' => 'Anthropicレスポンスの本体がJSONとして解析できません。', 'diag' => $diag );
    }

    $text = '';
    foreach ( (array) ( $decoded_response_body['content'] ?? array() ) as $block ) {
        if ( isset( $block['type'] ) && 'text' === $block['type'] && isset( $block['text'] ) ) {
            $text = $block['text'];
            break;
        }
    }
    if ( '' === $text ) {
        $diag['json'] = 'n/a';
        return array( 'ok' => false, 'error_message' => 'Anthropicレスポンスにtextブロックがありません。', 'diag' => $diag );
    }

    $decoded      = hatakiti_extract_json_from_ai_text( $text );
    $structure_ok = hatakiti_occult_ai_validate_planning_structure( $decoded, $valid_ids );
    if ( is_wp_error( $structure_ok ) ) {
        $diag['json'] = 'failed';
        return array( 'ok' => false, 'error_message' => $structure_ok->get_error_message(), 'diag' => $diag );
    }

    $diag['json'] = 'success';
    return array( 'ok' => true, 'error_message' => null, 'diag' => $diag );
}

/**
 * Shallow structural check for a STEP2 (writing) batch response — a batch
 * covers a bounded subset of groups (HATAKITI_OCCULT_AI_WRITING_BATCH_SIZE
 * per call), so this only needs headline/body for each group_key already
 * assigned by STEP1, and requires every expected key to be present —
 * a partial batch (some group_keys missing) is exactly the shape a
 * response cut off mid-stream produces, so it's treated as retryable
 * rather than silently dropping those articles.
 */
function hatakiti_occult_ai_validate_writing_structure( $decoded, $expected_keys ) {
    if ( ! is_array( $decoded ) ) {
        return new WP_Error( 'hatakiti_ai_invalid_json', 'AI応答をJSONとして解析できませんでした。' );
    }
    if ( empty( $decoded['articles'] ) || ! is_array( $decoded['articles'] ) ) {
        return new WP_Error( 'hatakiti_ai_no_articles', 'articlesが存在しないか空です。' );
    }
    $seen = array();
    foreach ( $decoded['articles'] as $i => $article ) {
        if ( ! is_array( $article ) ) {
            return new WP_Error( 'hatakiti_ai_bad_article', "articles[{$i}]が配列ではありません。" );
        }
        foreach ( array( 'group_key', 'headline', 'body' ) as $field ) {
            if ( empty( $article[ $field ] ) ) {
                return new WP_Error( 'hatakiti_ai_missing_field', "articles[{$i}]に{$field}がありません。" );
            }
        }
        $seen[ $article['group_key'] ] = true;
    }
    foreach ( $expected_keys as $key ) {
        if ( empty( $seen[ $key ] ) ) {
            return new WP_Error( 'hatakiti_ai_missing_group', "期待されたgroup_key {$key} の記事が応答に含まれていません。" );
        }
    }
    return true;
}

function hatakiti_occult_ai_writing_body_check( $decoded_response_body, $expected_keys ) {
    $diag = array();
    if ( is_array( $decoded_response_body ) ) {
        $diag['stop_reason']   = $decoded_response_body['stop_reason'] ?? 'n/a';
        $diag['input_tokens']  = $decoded_response_body['usage']['input_tokens'] ?? 'n/a';
        $diag['output_tokens'] = $decoded_response_body['usage']['output_tokens'] ?? 'n/a';
    }

    if ( ! is_array( $decoded_response_body ) ) {
        return array( 'ok' => false, 'error_message' => 'Anthropicレスポンスの本体がJSONとして解析できません。', 'diag' => $diag );
    }

    $text = '';
    foreach ( (array) ( $decoded_response_body['content'] ?? array() ) as $block ) {
        if ( isset( $block['type'] ) && 'text' === $block['type'] && isset( $block['text'] ) ) {
            $text = $block['text'];
            break;
        }
    }
    if ( '' === $text ) {
        $diag['json'] = 'n/a';
        return array( 'ok' => false, 'error_message' => 'Anthropicレスポンスにtextブロックがありません。', 'diag' => $diag );
    }

    $decoded      = hatakiti_extract_json_from_ai_text( $text );
    $structure_ok = hatakiti_occult_ai_validate_writing_structure( $decoded, $expected_keys );
    if ( is_wp_error( $structure_ok ) ) {
        $diag['json'] = 'failed';
        return array( 'ok' => false, 'error_message' => $structure_ok->get_error_message(), 'diag' => $diag );
    }

    $diag['json'] = 'success';
    return array( 'ok' => true, 'error_message' => null, 'diag' => $diag );
}

/**
 * Lightweight body_check for single-field classification calls (e.g. the
 * occult-news-category-classify.php category-only prompt) — checks only
 * that the decoded response has a non-empty "category" string, not the
 * full newspaper "articles" structure hatakiti_occult_ai_anthropic_body_
 * check() expects. Never used unless a caller explicitly passes it —
 * hatakiti_call_occult_ai_anthropic()'s default is unchanged, so the
 * newspaper pipeline's validation is completely unaffected by this.
 */
function hatakiti_occult_ai_category_body_check( $decoded_response_body ) {
    $diag = array();
    if ( is_array( $decoded_response_body ) ) {
        $diag['stop_reason']   = $decoded_response_body['stop_reason'] ?? 'n/a';
        $diag['output_tokens'] = $decoded_response_body['usage']['output_tokens'] ?? 'n/a';
    }

    if ( ! is_array( $decoded_response_body ) ) {
        return array( 'ok' => false, 'error_message' => 'Anthropicレスポンスの本体がJSONとして解析できません。', 'diag' => $diag );
    }

    $text = '';
    foreach ( (array) ( $decoded_response_body['content'] ?? array() ) as $block ) {
        if ( isset( $block['type'] ) && 'text' === $block['type'] && isset( $block['text'] ) ) {
            $text = $block['text'];
            break;
        }
    }
    if ( '' === $text ) {
        $diag['json'] = 'n/a';
        return array( 'ok' => false, 'error_message' => 'Anthropicレスポンスにtextブロックがありません。', 'diag' => $diag );
    }

    $data = hatakiti_extract_json_from_ai_text( $text );
    if ( ! is_array( $data ) || empty( $data['category'] ) || ! is_string( $data['category'] ) ) {
        $diag['json'] = 'failed';
        return array( 'ok' => false, 'error_message' => 'categoryが存在しないか不正です。', 'diag' => $diag );
    }

    $diag['json'] = 'success';
    return array( 'ok' => true, 'error_message' => null, 'diag' => $diag );
}

/**
 * Shared retry wrapper for both provider adapters — up to 3 attempts
 * total, exponential backoff (2s, then 4s) between retryable failures.
 * $body_check is optional: a callable(decoded_response_body_array): array
 * returning array('ok'=>bool, 'error_message'=>string|null, 'diag'=>array)
 * that inspects the response BODY, not just the HTTP status. Passing one
 * (the Anthropic adapter does; the OpenAI adapter deliberately does not,
 * to leave that untouched/untested code path exactly as it was) makes
 * "HTTP 200 but the article JSON is incomplete or missing required
 * fields" a retryable outcome instead of being treated as success purely
 * because the transport layer succeeded — the actual gap this whole
 * change addresses (found in the 580 full-flow test: HTTP 200, but the
 * AI response's JSON was truncated partway through and nothing here
 * caught that before it reached the save path).
 */
function hatakiti_occult_ai_post_with_retry( $url, $args, $provider_label, $body_check = null, $log_context = array() ) {
    $max_attempts = 3;
    $backoff      = array( 2, 4 );
    $response     = null;

    for ( $attempt = 1; $attempt <= $max_attempts; $attempt++ ) {
        $attempt_started_at = gmdate( 'Y-m-d\TH:i:s\Z' );
        $attempt_start_ts   = microtime( true );
        $response           = wp_remote_post( $url, $args );
        $elapsed_sec        = round( microtime( true ) - $attempt_start_ts, 1 );

        $is_wp_error = is_wp_error( $response );
        $http_code   = $is_wp_error ? 0 : wp_remote_retrieve_response_code( $response );
        $http_ok     = ! $is_wp_error && 200 === $http_code;

        $check = array( 'ok' => $http_ok, 'error_message' => null, 'diag' => array() );
        if ( $http_ok && null !== $body_check ) {
            $decoded_body = json_decode( wp_remote_retrieve_body( $response ), true );
            $check        = call_user_func( $body_check, $decoded_body );
        }

        $success = $http_ok && $check['ok'];

        $log_fields = array_merge(
            array( 'provider' => $provider_label ),
            $log_context,
            array(
                'attempt'     => $attempt . '/' . $max_attempts,
                'started_at'  => $attempt_started_at,
                'elapsed_sec' => $elapsed_sec,
                'http_status' => $is_wp_error ? 'n/a' : $http_code,
                'error_type'  => $is_wp_error ? $response->get_error_code() : ( $success ? '' : 'http_error' ),
            )
        );
        foreach ( $check['diag'] as $diag_key => $diag_value ) {
            $log_fields[ $diag_key ] = $diag_value;
        }
        if ( null !== $body_check ) {
            $log_fields['json'] = $http_ok ? ( $check['ok'] ? 'success' : 'failed' ) : 'n/a';
        }
        $log_fields['outcome'] = $success ? 'success' : 'failed';
        hatakiti_occult_ai_log( $log_fields );

        if ( $success ) {
            return $response;
        }

        // JSON body validation failing on an otherwise-200 response is
        // exactly as retryable as a transient network/server error — it's
        // the failure mode this change exists to catch.
        $retryable = $is_wp_error
            ? true
            : ( in_array( $http_code, array( 429, 500, 502, 503, 504 ), true ) || ( $http_ok && ! $check['ok'] ) );

        if ( ! $retryable || $attempt === $max_attempts ) {
            hatakiti_occult_ai_log( array(
                'provider' => $provider_label,
                'outcome'  => $retryable ? 'gave_up_after_max_attempts' : 'not_retryable',
            ) );
            // On the final attempt, if transport succeeded but body
            // validation didn't, return a specific WP_Error instead of the
            // raw (invalid) response — otherwise the caller would silently
            // re-extract the same bad text and produce a less useful
            // generic "empty response" message.
            if ( $http_ok && ! $check['ok'] && $check['error_message'] ) {
                return new WP_Error( 'hatakiti_ai_invalid_response', $check['error_message'] );
            }
            return $response;
        }

        sleep( $backoff[ $attempt - 1 ] );
    }

    return $response;
}

function hatakiti_call_occult_ai_anthropic( $prompt, $system, $api_key, $model, $body_check = 'hatakiti_occult_ai_anthropic_body_check', $max_tokens = 20000, $timeout = 280, $log_context = array() ) {
    $body = wp_json_encode( array(
        'model'      => $model,
        'max_tokens' => $max_tokens,
        'system'     => $system,
        'messages'   => array(
            array( 'role' => 'user', 'content' => $prompt ),
        ),
    ) );

    // 2026-09-07の実運用失敗（18記事分を1リクエストにまとめた結果、
    // cURL error 28で280秒×3回とも0バイト応答のまま終了）を受けて、
    // 週刊号生成は単一の巨大リクエストではなくSTEP1(選定・分類)/
    // STEP2(執筆、複数バッチ)に分割した — occult-weekly-ai-edit.php
    // 参照。$max_tokens/$timeoutは呼び出し側がリクエストの実サイズに
    // 応じて指定し、指定が無い場合は従来どおり20000/280。
    $log_context = array_merge( array(
        'model'           => $model,
        'max_tokens'      => $max_tokens,
        'timeout'         => $timeout,
        'connect_timeout' => 'n/a(WordPress HTTP APIはtimeoutと共通)',
        'request_bytes'   => strlen( $body ),
    ), $log_context );

    $response = hatakiti_occult_ai_post_with_retry( 'https://api.anthropic.com/v1/messages', array(
        'timeout' => $timeout,
        'headers' => array(
            'x-api-key'         => $api_key,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ),
        'body' => $body,
    ), 'anthropic', $body_check, $log_context );

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

function hatakiti_call_occult_ai_openai( $prompt, $system, $api_key, $model, $max_tokens = 20000, $timeout = 280, $log_context = array() ) {
    $messages = array();
    if ( $system ) {
        $messages[] = array( 'role' => 'system', 'content' => $system );
    }
    $messages[] = array( 'role' => 'user', 'content' => $prompt );

    $body = wp_json_encode( array(
        'model'       => $model,
        'messages'    => $messages,
        'temperature' => 0.3,
        'max_tokens'  => $max_tokens,
    ) );

    $log_context = array_merge( array(
        'model'           => $model,
        'max_tokens'      => $max_tokens,
        'timeout'         => $timeout,
        'connect_timeout' => 'n/a(WordPress HTTP APIはtimeoutと共通)',
        'request_bytes'   => strlen( $body ),
    ), $log_context );

    $response = hatakiti_occult_ai_post_with_retry( 'https://api.openai.com/v1/chat/completions', array(
        'timeout' => $timeout,
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'content-type'  => 'application/json',
        ),
        'body' => $body,
    ), 'openai', null, $log_context );

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
