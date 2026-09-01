<?php
/**
 * AI週次編集機能 — docs/07-OccultWeekly.md / docs/16-OccultWeekly-
 * AutomationDesign.md の「週次AI編集処理」を実際に呼び出す実装。
 *
 * Flow:
 *   対象期間のoccult_news_item取得 (hatakiti_get_occult_weekly_candidates,
 *   occult-weekly-admin-form.php で既存)
 *   -> プロンプト生成
 *   -> hatakiti_call_occult_ai_text() (occult-ai.php)
 *   -> JSON抽出・検証
 *   -> articles_json 組み立て (hatakiti_finalize_occult_weekly_groups,
 *      occult-weekly-admin-form.php で既存 — 手動編集フォームと全く同じ
 *      保存経路を使うため、AIが作ったdraftもそのまま「号を編集」画面で
 *      人間が続きを編集できる)
 *   -> occult_weekly をdraftとして新規作成
 *
 * 自動公開は一切行わない。cronからの自動実行も今回は実装しない — 人が
 * 管理画面から都度実行する運用（指示書の明示的な要求）。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * headline/major/minor (指示書が使った呼び名) <-> large/medium/small
 * (occult_weekly の既存tierボキャブラリ, HATAKITI_OCCULT_TIERS) の対応。
 * AIへの指示・出力はheadline/major/minorで統一し、保存時にだけ既存の
 * tierキーへ変換する — 既存の管理画面・公開テンプレートは無変更で動く。
 */
function hatakiti_occult_ai_importance_to_tier( $importance ) {
    $map = array(
        'headline' => 'large',
        'major'    => 'medium',
        'minor'    => 'small',
    );
    return isset( $map[ $importance ] ) ? $map[ $importance ] : 'small';
}

function hatakiti_build_occult_ai_prompt( $items, $week_start, $week_end ) {
    $lines = array();
    foreach ( $items as $item ) {
        $lines[] = sprintf(
            "id: %d\n媒体: %s\nタイトル: %s\n公開日時: %s\n要約: %s\nURL: %s",
            $item->ID,
            get_post_meta( $item->ID, 'hatakiti_occult_source_name', true ),
            get_the_title( $item->ID ),
            get_post_meta( $item->ID, 'hatakiti_occult_published_at', true ),
            $item->post_content,
            get_post_meta( $item->ID, 'hatakiti_occult_original_url', true )
        );
    }
    $items_text = implode( "\n\n---\n\n", $lines );

    $system = <<<SYS
あなたは「週刊オカルト新聞」（HATAKITI.com）のAI編集者です。複数の情報源から集まった1週間分のオカルト関連ニュースを分析し、クラスタリング・重要度判定・新聞記事としての執筆を行います。

【この新聞の基本方針 — 最重要】
- あなたの役割はニュースを数行に「要約」することではありません。読者がHATAKITI.com上の記事本文だけを読めば、そのニュースで何が起きたのか、いつ・どこで・誰が関係し・どのような経緯があり・何が分かっていて何が分かっていないのかを理解できるよう、新聞記事として再構成してください。
- 出典リンクは「リンク先を読まないと内容が分からない」状態を補うためのものではありません。まずHATAKITI側の記事を完成させ、そのうえで、さらに詳しく調べたい読者のために元記事への入口を用意するものです。
- 元記事の本文をそのまま転載してはいけません。複数の情報を整理・比較・再構成した独自記事を書いてください。
- 入力情報に存在しない事実、人物、発言、日時、場所、因果関係などを創作してはいけません。情報が不足している場合は、不足していることを明示してください。

【クラスタリングのルール — 最重要】
- 単純なタイトルの類似だけで同一事件と判断してはいけません。人物、場所、日付、事件内容、固有名詞、発生経緯を具体的に照らし合わせて判断してください。
- 同一事件・続報と判断できる場合のみ、それらのニュースを1つの記事にまとめてください。
- 関連事件・同じテーマではあるが別の出来事の場合は、無理に1つにまとめず、別々の記事として残してください。
- 判断に自信が持てない場合は、統合せず別記事のままにしてください。誤って別の事件を同一事件として統合するより、別記事として残すことを優先してください。

【重要度判定と記事の長さ】
各記事に、次の3段階のいずれかを付けてください。重要度は、そのまま新聞上の記事サイズと本文量に反映されます。

- headline（大見出し）: 今週で最も重要・注目度の高い事件。原則として1〜2本程度。本文は800〜1200字程度を目安にしてください。複数ソースの情報を統合し、出来事の背景、経緯、現在分かっていること、争点や不可解な点まで含めて、読者が単独で読んでも内容を理解できる記事にしてください。
- major（主要記事）: 一定の関心を集める話題。本文は500〜800字程度を目安にしてください。事件・出来事の概要だけでなく、経緯や注目されている理由まで説明してください。
- minor（小記事）: 単独ニュースで扱いが小さいもの。本文は200〜350字程度を目安にしてください。短くても「何が起きたのか」が分かる完結した記事にしてください。

文字数は絶対的な上限・下限ではありませんが、重要度に応じた情報量を確保してください。特にheadlineを300〜600字程度の短い要約で済ませてはいけません。情報が十分にある場合は、指定目安を満たすように詳しく書いてください。

【記事の構成】
可能な範囲で、次の流れを意識してください。
1. 何が起きたのか — 冒頭でニュースの核心を明確にする。
2. いつ・どこで・誰が関係したのか — 入力に確認できる範囲で具体的に説明する。
3. これまでの経緯 — 続報や背景がある場合は時系列で整理する。
4. 何が確認されているのか／何が確認されていないのか — 事実と伝聞・推測を分ける。
5. オカルト的に何が興味深いのか — UFO、UMA、怪異、予言など、読者が注目するポイントを説明する。ただし、超常現象だと断定できないものを断定しない。
6. 現時点での結論 — 分かっていない場合は「分かっていない」と明確にする。

【著作権と表現】
- 元記事の本文をそのまま長文コピー・引用してはいけません。
- 複数の情報を要約・比較・整理した、週刊オカルト新聞独自の編集記事を書いてください。
- 入力に含まれる要約だけで不足する情報を、想像で補って文字数を稼がないでください。
- 「報じられている」「〜という」「現時点では確認されていない」など、確認された事実と未確認情報を区別する表現を使ってください。

【出典の追跡】
- 各記事には、使用した元ニュースのidを必ず source_item_ids に列挙してください。
- id は下記に与えられたものだけを使い、絶対に新しいidを作らないでください。
- 1つの記事が複数の元ニュース（同一事件の複数ソース）を統合した場合、その全てのidを含めてください。

【出力形式】
説明文やMarkdownのコードフェンスを一切付けず、以下の構造のJSONオブジェクトのみを出力してください。

{
  "issue_title": "その号の内容を表す短いタイトル案（号数・回数は含めない。実在しない号数を作らないこと。例: 週刊オカルト新聞 ― 終末予言と奇跡の遺物）",
  "editorial_summary": "今週全体を振り返る編集後記（2〜4文程度）",
  "articles": [
    {
      "headline": "記事の見出し",
      "importance": "headline または major または minor",
      "body": "記事本文",
      "source_item_ids": [123, 456]
    }
  ]
}
SYS;

    $prompt = "対象期間: {$week_start} 〜 {$week_end}\n\n以下は今週収集されたオカルト関連ニュースです（各項目のidを必ずsource_item_idsで参照してください）。\n\n{$items_text}\n\n上記を分析し、重要度に応じた十分な文字量の新聞記事を執筆したうえで、指示された構造のJSONのみを出力してください。";

    return array( $system, $prompt );
}

/**
 * The full pipeline. Returns the new occult_weekly post ID (always
 * draft), or WP_Error.
 */
function hatakiti_generate_occult_weekly_draft_via_ai( $week_start, $week_end ) {
    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $week_start ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $week_end ) ) {
        return new WP_Error( 'hatakiti_ai_bad_range', '対象期間（開始・終了）の形式が正しくありません。' );
    }

    $items = hatakiti_get_occult_weekly_candidates( $week_start, $week_end, 0 );
    if ( ! $items ) {
        return new WP_Error( 'hatakiti_ai_no_items', '対象期間内に、まだどの号にも使われていないニュースがありません。先にRSS取得（またはテストデータ投入）を行ってください。' );
    }

    $valid_ids = wp_list_pluck( $items, 'ID' );

    list( $system, $prompt ) = hatakiti_build_occult_ai_prompt( $items, $week_start, $week_end );

    $ai_text = hatakiti_call_occult_ai_text( $prompt, $system );
    if ( is_wp_error( $ai_text ) ) {
        return $ai_text;
    }

    return hatakiti_process_occult_ai_response( $ai_text, $valid_ids, $week_start, $week_end );
}

/**
 * Everything after the AI call: parse, validate, build articles_json,
 * create the draft. Split out from hatakiti_generate_occult_weekly_draft_via_ai()
 * so this half — the part with no network dependency — can be exercised
 * directly with a synthetic AI response (e.g. in testing, or if a
 * response was captured from elsewhere).
 */
function hatakiti_process_occult_ai_response( $ai_text, $valid_ids, $week_start, $week_end ) {
    $data = hatakiti_extract_json_from_ai_text( $ai_text );
    if ( ! is_array( $data ) || empty( $data['articles'] ) || ! is_array( $data['articles'] ) ) {
        return new WP_Error(
            'hatakiti_ai_bad_json',
            'AIの応答を想定した構造のJSONとして解釈できませんでした。応答の先頭200文字: ' . mb_substr( (string) $ai_text, 0, 200 )
        );
    }

    $groups   = array();
    $used_ids = array();
    $order    = 0;

    foreach ( $data['articles'] as $article ) {
        $importance = isset( $article['importance'] ) ? (string) $article['importance'] : 'minor';
        $tier       = hatakiti_occult_ai_importance_to_tier( $importance );
        $headline   = isset( $article['headline'] ) ? sanitize_text_field( $article['headline'] ) : '';
        $body       = isset( $article['body'] ) ? wp_kses_post( $article['body'] ) : '';

        $source_ids = array();
        foreach ( (array) ( isset( $article['source_item_ids'] ) ? $article['source_item_ids'] : array() ) as $sid ) {
            $sid = (int) $sid;
            // Defensive: only ids that were actually offered to the AI are
            // trusted — silently drops anything hallucinated.
            if ( $sid && in_array( $sid, $valid_ids, true ) ) {
                $source_ids[] = $sid;
            }
        }

        // An article with no headline or no real source is unusable —
        // drop it rather than save a broken/unsourced entry.
        if ( '' === $headline || empty( $source_ids ) ) {
            continue;
        }

        $order++;
        $groups[] = array(
            'key'           => 'ai::' . $order . '::' . substr( md5( $headline . $order ), 0, 8 ),
            'tier'          => $tier,
            'headline'      => $headline,
            'body'          => $body,
            'order'         => $order,
            'news_item_ids' => $source_ids,
        );
        $used_ids = array_merge( $used_ids, $source_ids );
    }

    if ( empty( $groups ) ) {
        return new WP_Error( 'hatakiti_ai_no_valid_articles', 'AIの応答から有効な記事を1件も生成できませんでした（見出し・出典ニュースidの欠落など）。' );
    }

    $issue_title = ! empty( $data['issue_title'] ) && is_string( $data['issue_title'] )
        ? sanitize_text_field( $data['issue_title'] )
        : ( '週刊オカルト新聞 ' . $week_start . '〜' . $week_end . '（AI編集）' );

    $editorial_summary = ! empty( $data['editorial_summary'] ) && is_string( $data['editorial_summary'] )
        ? wp_kses_post( $data['editorial_summary'] )
        : '';

    $post_id = wp_insert_post( array(
        'post_type'   => 'occult_weekly',
        'post_title'  => $issue_title,
        'post_status' => 'draft', // AI出力は必ずdraft — 人間の確認・公開が別工程
    ), true );
    if ( is_wp_error( $post_id ) ) {
        return $post_id;
    }

    update_post_meta( $post_id, 'hatakiti_occult_issue_id', 'occult-ai-' . $week_end );
    update_post_meta( $post_id, 'hatakiti_occult_week_start', $week_start );
    update_post_meta( $post_id, 'hatakiti_occult_week_end', $week_end );
    update_post_meta( $post_id, 'hatakiti_occult_issue_date', $week_end );
    update_post_meta( $post_id, 'hatakiti_occult_editorial_summary', $editorial_summary );

    // Same save path the manual 号編集フォーム uses — the resulting draft
    // is a completely normal occult_weekly issue, editable by hand
    // afterward with no special-casing needed anywhere else.
    hatakiti_finalize_occult_weekly_groups( $post_id, $groups, array_values( array_unique( $used_ids ) ) );

    return $post_id;
}

/**
 * Admin page: 週刊オカルト新聞 → AIで週刊号を作成. Two-step, no JS:
 * entering a date range and submitting "件数を確認" just previews the
 * candidate count; "AIで作成する" runs the full pipeline.
 */
function hatakiti_register_occult_ai_generate_page() {
    add_submenu_page(
        'edit.php?post_type=occult_weekly',
        'AIで週刊号を作成',
        'AIで週刊号を作成',
        'edit_posts',
        'hatakiti-occult-ai-generate',
        'hatakiti_render_occult_ai_generate_page'
    );
}
add_action( 'admin_menu', 'hatakiti_register_occult_ai_generate_page' );

function hatakiti_render_occult_ai_generate_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '権限がありません。' );
    }

    $week_start = isset( $_REQUEST['hatakiti_ai_week_start'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hatakiti_ai_week_start'] ) ) : '';
    $week_end   = isset( $_REQUEST['hatakiti_ai_week_end'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hatakiti_ai_week_end'] ) ) : '';

    $candidate_count = null;
    if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $week_start ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $week_end ) ) {
        $candidate_count = count( hatakiti_get_occult_weekly_candidates( $week_start, $week_end, 0 ) );
    }

    $generated_post_id = null;
    $error              = null;

    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_occult_ai_generate_nonce'] ) && isset( $_POST['hatakiti_ai_generate'] ) ) {
        check_admin_referer( 'hatakiti_occult_ai_generate', 'hatakiti_occult_ai_generate_nonce' );

        $result = hatakiti_generate_occult_weekly_draft_via_ai( $week_start, $week_end );
        if ( is_wp_error( $result ) ) {
            $error = $result->get_error_message();
        } else {
            $generated_post_id = $result;
        }
    }
    ?>
    <div class="wrap hatakiti-record-form">
        <h1>AIで週刊号を作成</h1>
        <p class="description">
            対象期間内の、まだどの号にも使われていないニュースをAIに渡し、クラスタリング・重要度判定・新聞記事としての執筆を行って、週刊号の<strong>下書き</strong>を作成します。
            AI処理中に公開されることはありません — 生成される号は必ず下書き状態です。内容は「号を編集」画面から人間が確認・修正できます。
        </p>

        <?php if ( ! hatakiti_occult_ai_is_configured() ) : ?>
            <div class="notice notice-warning">
                <p>AI APIが未設定です。<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=occult_weekly&page=hatakiti-occult-ai-settings' ) ); ?>">AI設定</a>でモデル名・APIキーを設定してから実行してください。</p>
            </div>
        <?php endif; ?>

        <?php if ( $error ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
        <?php endif; ?>

        <?php if ( $generated_post_id ) : ?>
            <div class="notice notice-success">
                <p>
                    週刊号の下書きを作成しました。
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=hatakiti-occult-weekly-form&post_id=' . $generated_post_id ) ); ?>">「号を編集」で内容を確認・修正する</a>
                </p>
            </div>
        <?php endif; ?>

        <form method="get">
            <input type="hidden" name="post_type" value="occult_weekly">
            <input type="hidden" name="page" value="hatakiti-occult-ai-generate">
            <table class="form-table" role="presentation"><tbody>
                <?php
                hatakiti_form_date_row( '対象開始日', 'hatakiti_ai_week_start', $week_start );
                hatakiti_form_date_row( '対象終了日', 'hatakiti_ai_week_end', $week_end );
                ?>
            </tbody></table>
            <p class="hatakiti-form-actions">
                <button type="submit" class="button">対象ニュース件数を確認</button>
            </p>
        </form>

        <?php if ( null !== $candidate_count ) : ?>
            <p class="description">
                対象期間: <?php echo esc_html( $week_start ); ?> 〜 <?php echo esc_html( $week_end ); ?> /
                対象ニュース件数: <strong><?php echo (int) $candidate_count; ?></strong> 件
            </p>

            <?php if ( $candidate_count > 0 ) : ?>
                <form method="post">
                    <?php wp_nonce_field( 'hatakiti_occult_ai_generate', 'hatakiti_occult_ai_generate_nonce' ); ?>
                    <input type="hidden" name="hatakiti_ai_week_start" value="<?php echo esc_attr( $week_start ); ?>">
                    <input type="hidden" name="hatakiti_ai_week_end" value="<?php echo esc_attr( $week_end ); ?>">
                    <p class="hatakiti-form-actions">
                        <button type="submit" name="hatakiti_ai_generate" value="1" class="button button-primary"<?php disabled( ! hatakiti_occult_ai_is_configured() ); ?>>この期間のニュースでAI週刊号を作成する</button>
                    </p>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}
