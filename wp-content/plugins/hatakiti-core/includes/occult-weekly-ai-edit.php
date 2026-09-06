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

/**
 * 1件のニュースをAIプロンプトへ渡す行に変換する。STEP1(選定・分類)・
 * STEP2(執筆)どちらのプロンプトからも共通で呼ばれる — 元記事取得
 * (hatakiti_fetch_occult_source_article()) はmetaにキャッシュされる
 * 既存仕様なので、両ステップから呼んでも二重ネットワークアクセスには
 * ならない。
 */
function hatakiti_occult_ai_item_lines( $item ) {
    $fetch_status = hatakiti_fetch_occult_source_article( $item->ID );

    $lines = array(
        'id: ' . $item->ID,
        '媒体: ' . get_post_meta( $item->ID, 'hatakiti_occult_source_name', true ),
        'タイトル: ' . get_the_title( $item->ID ),
        '公開日時: ' . get_post_meta( $item->ID, 'hatakiti_occult_published_at', true ),
        'URL: ' . get_post_meta( $item->ID, 'hatakiti_occult_original_url', true ),
        'RSS要約: ' . $item->post_content,
    );

    if ( 'success' === $fetch_status ) {
        $article_text = get_post_meta( $item->ID, 'hatakiti_occult_source_article_text', true );
        if ( $article_text ) {
            $lines[] = '元記事本文（事実確認・記事執筆のための資料。転載・引用元ではなく参考情報として扱うこと）: ' . $article_text;
        }
    }
    // 取得失敗・対象外ホスト等はRSS要約のみで続行する（指示書§13の
    // 明示的なフォールバック方針、無変更）。

    return implode( "\n", $lines );
}

function hatakiti_occult_ai_category_guide_text() {
    return <<<CATS
- UMA・未確認生物: 未確認生物、怪物、謎の生物、未知の動物など
- UFO・宇宙: UFO、UAP、宇宙人、異星人、宇宙現象、地球外生命など
- 心霊・怪談: 幽霊、霊、心霊現象、怪談、怪奇現象、呪われた場所など
- 超常現象: 発光現象、念力、テレパシー、時間・空間異常など説明困難な現象（UMA/UFO/心霊など明確に別カテゴリに該当する場合はそちらを優先）
- 古代・歴史: 古文書、古代文明、歴史上の謎、遺跡、過去の記録など
- 民俗・呪術: 民間伝承、風習、呪術、祭祀、まじない、民話など
- 科学・人体: 科学、医学、人体、脳、心理、生物学など（オカルト的話題でも記事の中心が科学・人体研究の場合はこちら）
- 事件・ミステリー: 未解決事件、失踪、謎の死亡、犯罪、不可解な事件など
- 予言・終末: 予言、未来予知、終末論、世界滅亡、災害予言など
- その他: 上記のどれにも明確に分類できないもの
CATS;
}

/**
 * STEP1（選定・クラスタリング・重要度／カテゴリ判定）専用プロンプト。
 *
 * 2026-09-07の自動発行失敗（18記事分の「クラスタリング＋執筆＋分類」を
 * 1回のAnthropic呼び出しにまとめた結果、cURL error 28で280秒×3回とも
 * 応答0バイトのまま終了）を受けて、旧hatakiti_build_occult_ai_prompt()
 * の単一プロンプトを2段階へ分割した。このSTEP1は本文執筆を一切行わない
 * ため出力が小さく、入力に全ニュースの元記事本文を含めてもクラスタ
 * 判定の精度は変えずに応答時間を短く保てる（LLMの応答時間は一般に
 * 出力トークン数に強く依存し、入力サイズの影響は相対的に小さいため）。
 */
function hatakiti_build_occult_ai_planning_prompt( $items, $week_start, $week_end ) {
    $lines = array();
    foreach ( $items as $item ) {
        $lines[] = hatakiti_occult_ai_item_lines( $item );
    }
    $items_text     = implode( "\n\n---\n\n", $lines );
    $category_guide = hatakiti_occult_ai_category_guide_text();

    $system = <<<SYS
あなたは「週刊オカルト新聞」（HATAKITI.com）のAI編集者です。複数の情報源から集まった1週間分のオカルト関連ニュースを分析し、クラスタリング・重要度判定・カテゴリ分類を行います。この段階では記事本文は執筆しません（本文執筆は別の担当者が後続の工程で行います）。

【入力データについて】
各ニュースには「RSS要約」（短い、記事発見用の情報）に加えて、可能な場合は「元記事本文」（元記事ページから抽出した本文）が付いています。クラスタリング（同一事件かどうかの判断）の材料として使ってください。

【クラスタリングのルール — 最重要】
- 単純なタイトルの類似だけで同一事件と判断してはいけません。人物、場所、日付、事件内容、固有名詞、発生経緯を具体的に照らし合わせて判断してください。
- 同一事件・続報と判断できる場合のみ、それらのニュースを1つのグループにまとめてください。
- 関連事件・同じテーマではあるが別の出来事の場合は、無理に1つにまとめず、別々のグループとして残してください。
- 判断に自信が持てない場合は、統合せず別グループのままにしてください。誤って別の事件を同一事件として統合するより、別グループとして残すことを優先してください。

【重要度判定】
各グループに、次の3段階のいずれかを付けてください。
- headline（大見出し）: 今週で最も重要・注目度の高い事件。原則として1〜2本程度。
- major（主要記事）: 一定の関心を集める話題。
- minor（小記事）: 単独ニュースで扱いが小さいもの。

【カテゴリ分類】
各グループに、次の10カテゴリのうち最も近いもの1つを category として付けてください（複数選択・新規カテゴリの作成は不可、必ず下記の表記のまま使用）。
{$category_guide}
分類に迷った場合は、無理に複数カテゴリを付けず、最も近い1カテゴリを選んでください。

【出典の追跡】
- 各グループには、含めたニュースのidを必ず item_ids に列挙してください。
- id は下記に与えられたものだけを使い、絶対に新しいidを作らないでください。

【story_noteについて】
story_noteは、後で本文を執筆する担当者への引き継ぎメモです。読者向けの文章ではなく、「何が起きたか」を1〜2文で簡潔に書いてください（詳細な執筆は担当者が元記事を読んで行います）。

【出力形式】
説明文やMarkdownのコードフェンスを一切付けず、以下の構造のJSONオブジェクトのみを出力してください。本文（body）はここでは出力しないでください。

{
  "issue_title": "その号の内容を表す短いタイトル案（号数・回数は含めない。実在しない号数を作らないこと。例: 週刊オカルト新聞 ― 終末予言と奇跡の遺物）",
  "editorial_summary": "今週全体を振り返る編集後記（2〜4文程度）",
  "groups": [
    {
      "importance": "headline または major または minor",
      "category": "超常現象",
      "item_ids": [123, 456],
      "story_note": "執筆担当への引き継ぎメモ（1〜2文）"
    }
  ]
}
SYS;

    $prompt = "対象期間: {$week_start} 〜 {$week_end}\n\n以下は今週収集されたオカルト関連ニュースです（各項目のidを必ずitem_idsで参照してください）。\n\n{$items_text}\n\n上記を分析し、指示された構造のJSONのみを出力してください。";

    return array( $system, $prompt );
}

/**
 * STEP2（執筆）専用プロンプト。1回の呼び出しが扱うグループ数は
 * HATAKITI_OCCULT_AI_WRITING_BATCH_SIZE 件までに限定し、STEP1が既に
 * 決定したimportance/category/item_idsはそのまま使う — 執筆担当が
 * 決めるのはheadline/bodyのみ。
 */
function hatakiti_build_occult_ai_writing_prompt( $group_batch, $items_by_id, $week_start, $week_end ) {
    $blocks = array();
    foreach ( $group_batch as $group ) {
        $item_lines = array();
        foreach ( $group['item_ids'] as $iid ) {
            if ( isset( $items_by_id[ $iid ] ) ) {
                $item_lines[] = hatakiti_occult_ai_item_lines( $items_by_id[ $iid ] );
            }
        }
        $blocks[] = "[group_key: {$group['group_key']}]\n"
            . "重要度: {$group['importance']}\n"
            . "編集メモ（内部情報。方針の参考にするだけで、本文にそのまま書き写さないこと）: {$group['story_note']}\n"
            . "対象ニュース:\n" . implode( "\n\n---\n\n", $item_lines );
    }
    $groups_text = implode( "\n\n====\n\n", $blocks );

    $system = <<<SYS
あなたは「週刊オカルト新聞」（HATAKITI.com）のAI記者です。編集部が既にクラスタリング・重要度判定・カテゴリ分類を終えた複数のニューストピックについて、それぞれ新聞記事の本文を執筆します。グループ分け・重要度・使用ニュースは変更しないでください。あなたの仕事は各グループのheadline（見出し）とbody（本文）の執筆だけです。

【この新聞の基本方針 — 最重要】
- あなたの役割はニュースを数行に「要約」することではありません。読者がHATAKITI.com上の記事本文だけを読めば、そのニュースで何が起きたのか、いつ・どこで・誰が関係し・どのような経緯があり・何が分かっていて何が分かっていないのかを理解できるよう、新聞記事として再構成してください。
- 出典リンクは「リンク先を読まないと内容が分からない」状態を補うためのものではありません。まずHATAKITI側の記事を完成させ、そのうえで、さらに詳しく調べたい読者のために元記事への入口を用意するものです。
- 元記事の本文（RSS要約・元記事本文どちらも）をそのまま転載・長文引用してはいけません。読んで理解した事実を、あなた自身の言葉で新聞記事として再構成してください。
- 入力情報に存在しない事実、人物、発言、日時、場所、因果関係などを創作してはいけません。情報が不足している場合は、不足していることを明示してください。
- 同一グループ内に複数の元記事がある場合、(a) 複数の情報源で共通して確認できる事実、(b) 一方の情報源だけが報じている内容、(c) 情報源間で食い違っている部分、を区別して扱ってください。食い違いがある場合、どちらが正しいかをあなたが勝手に決めないでください。

【重要度別の文字数の目安】
- headline（大見出し）: 800〜1200字程度。複数ソースの情報を統合し、出来事の背景、経緯、現在分かっていること、争点や不可解な点まで含めて、読者が単独で読んでも内容を理解できる記事にしてください。
- major（主要記事）: 500〜800字程度。事件・出来事の概要だけでなく、経緯や注目されている理由まで説明してください。
- minor（小記事）: 200〜350字程度。短くても「何が起きたのか」が分かる完結した記事にしてください。

文字数は目安であり、絶対的な上限・下限ではありません。優先順位は「事実性 ＞ 読者がニュースを理解できること ＞ 情報の整理 ＞ 読みやすさ ＞ 文字数」です。元記事本文などから十分な事実が得られる場合は、目安の文字数を満たすように詳しく書いてください。一方、情報が少ないニュースについて、文字数を埋めるためだけに一般論・推測・同じ内容の言い換えを追加することは禁止します。情報が少ない場合は、無理に長くせず、確認できる範囲で簡潔にまとめてください。

【記事の構成】
可能な範囲で、次の流れを意識してください。
1. 何が起きたのか — 冒頭でニュースの核心を明確にする。
2. いつ・どこで・誰が関係したのか — 入力に確認できる範囲で具体的に説明する。
3. これまでの経緯 — 続報や背景がある場合は時系列で整理する。
4. 何が確認されているのか／何が確認されていないのか — 事実と伝聞・推測を分ける。
5. オカルト的に何が興味深いのか — UFO、UMA、怪異、予言など、読者が注目するポイントを説明する。ただし、超常現象だと断定できないものを断定しない。
6. 現時点での結論 — 分かっていない場合は「分かっていない」と明確にする。

【著作権と表現】
- 元記事本文・RSS要約のいずれも、そのまま長文コピー・引用してはいけません。元記事はあなたが事実を確認するための資料であり、転載元ではありません。
- 複数の情報を要約・比較・整理した、週刊オカルト新聞独自の編集記事を書いてください。
- 入力に含まれる情報だけで不足する部分を、想像で補って文字数を稼がないでください。
- 「報じられている」「〜という」「現時点では確認されていない」など、確認された事実と未確認情報を区別する表現を使ってください。

【出力形式】
説明文やMarkdownのコードフェンスを一切付けず、以下の構造のJSONオブジェクトのみを出力してください。group_keyは与えられたものをそのまま使い、新しく作らないでください。

{
  "articles": [
    {
      "group_key": "g0",
      "headline": "記事の見出し",
      "body": "記事本文"
    }
  ]
}
SYS;

    $prompt = "対象期間: {$week_start} 〜 {$week_end}\n\n以下の各グループについて、指定された重要度に応じた新聞記事を執筆してください。グループ分け・重要度・使用ニュースは既に決定済みです。あなたの仕事は本文の執筆のみです。\n\n{$groups_text}\n\n各グループについて、指定されたgroup_keyをそのまま使い、指定された構造のJSONのみを出力してください。";

    return array( $system, $prompt );
}

/**
 * 1回のSTEP2呼び出しが扱うグループ数の上限。「適切な単位で分割して
 * 生成」（2026-09-07の指示書§3）の実装値 — 巨大すぎず、かつ呼び出し
 * 回数が増えすぎない値として4を選んだ。
 */
define( 'HATAKITI_OCCULT_AI_WRITING_BATCH_SIZE', 4 );

/**
 * バッチ内のグループ構成から、この呼び出しに必要そうなmax_tokensを
 * 見積もる。重要度ごとの目安文字数に応じた大まかな予算＋thinking用の
 * 余裕を積むだけの単純な見積もりで、正確なトークン換算はしない
 * （既存のfullwidth文字カウント等と同じ「目安でよい」方針）。
 */
function hatakiti_occult_ai_writing_max_tokens( $group_batch ) {
    $budget = array( 'headline' => 3000, 'major' => 2000, 'minor' => 1000 );
    $total  = 1500; // thinking + JSON構造のオーバーヘッド分の余裕
    foreach ( $group_batch as $group ) {
        $total += isset( $budget[ $group['importance'] ] ) ? $budget[ $group['importance'] ] : 1000;
    }
    return max( 4000, min( 20000, $total ) );
}

/**
 * STEP1呼び出し。返り値は正規化済みの
 * ['issue_title'=>string, 'editorial_summary'=>string,
 *   'groups'=>[['group_key','importance','category','item_ids','story_note'], ...]]
 * または WP_Error。
 */
function hatakiti_call_occult_ai_planning( $items, $week_start, $week_end ) {
    list( $system, $prompt ) = hatakiti_build_occult_ai_planning_prompt( $items, $week_start, $week_end );
    $valid_ids = wp_list_pluck( $items, 'ID' );

    $body_check = function ( $decoded ) use ( $valid_ids ) {
        return hatakiti_occult_ai_planning_body_check( $decoded, $valid_ids );
    };

    $log_context = array(
        'phase'           => 'planning',
        'source_articles' => count( $items ),
        'prompt_chars'    => mb_strlen( $system ) + mb_strlen( $prompt ),
    );

    // 本文は出力しないが、18記事規模の実測でmax_tokens=4000は
    // stop_reason=max_tokensで打ち切られた（thinking+テキスト合算で
    // 消費されるため）。実測(約70〜80 token/秒)を踏まえ16000/260秒とし、
    // 元の単一巨大リクエスト(20000/280秒)より小さいがSTEP1単体としては
    // 十分な余裕を持たせる。
    $ai_text = hatakiti_call_occult_ai_text( $prompt, $system, $body_check, 16000, 260, $log_context );
    if ( is_wp_error( $ai_text ) ) {
        return $ai_text;
    }

    $decoded = hatakiti_extract_json_from_ai_text( $ai_text );
    $valid   = hatakiti_occult_ai_validate_planning_structure( $decoded, $valid_ids );
    if ( is_wp_error( $valid ) ) {
        return $valid;
    }

    $valid_categories = hatakiti_occult_category_terms();
    $groups           = array();
    foreach ( $decoded['groups'] as $idx => $group ) {
        $item_ids = array();
        foreach ( (array) $group['item_ids'] as $sid ) {
            $sid = (int) $sid;
            if ( $sid && in_array( $sid, $valid_ids, true ) ) {
                $item_ids[] = $sid;
            }
        }
        if ( empty( $item_ids ) ) {
            continue;
        }

        $category = isset( $group['category'] ) && in_array( $group['category'], $valid_categories, true )
            ? $group['category']
            : 'その他';

        $groups[] = array(
            'group_key'  => 'g' . $idx,
            'importance' => $group['importance'],
            'category'   => $category,
            'item_ids'   => $item_ids,
            'story_note' => isset( $group['story_note'] ) ? (string) $group['story_note'] : '',
        );
    }

    if ( empty( $groups ) ) {
        return new WP_Error( 'hatakiti_ai_no_valid_groups', 'AIの応答から有効なグループを1件も生成できませんでした。' );
    }

    return array(
        'issue_title'       => ( ! empty( $decoded['issue_title'] ) && is_string( $decoded['issue_title'] ) ) ? $decoded['issue_title'] : '',
        'editorial_summary' => ( ! empty( $decoded['editorial_summary'] ) && is_string( $decoded['editorial_summary'] ) ) ? $decoded['editorial_summary'] : '',
        'groups'            => $groups,
    );
}

/**
 * STEP2呼び出し（1バッチ分）。返り値は group_key => ['headline','body']
 * の連想配列、または WP_Error。
 */
function hatakiti_call_occult_ai_writing_batch( $group_batch, $items_by_id, $week_start, $week_end, $batch_index ) {
    list( $system, $prompt ) = hatakiti_build_occult_ai_writing_prompt( $group_batch, $items_by_id, $week_start, $week_end );
    $expected_keys = wp_list_pluck( $group_batch, 'group_key' );

    $body_check = function ( $decoded ) use ( $expected_keys ) {
        return hatakiti_occult_ai_writing_body_check( $decoded, $expected_keys );
    };

    $source_article_count = 0;
    foreach ( $group_batch as $group ) {
        $source_article_count += count( $group['item_ids'] );
    }

    $log_context = array(
        'phase'           => 'writing',
        'batch_index'     => $batch_index,
        'group_count'     => count( $group_batch ),
        'source_articles' => $source_article_count,
        'prompt_chars'    => mb_strlen( $system ) + mb_strlen( $prompt ),
    );

    $max_tokens = hatakiti_occult_ai_writing_max_tokens( $group_batch );
    $ai_text    = hatakiti_call_occult_ai_text( $prompt, $system, $body_check, $max_tokens, 280, $log_context );
    if ( is_wp_error( $ai_text ) ) {
        return $ai_text;
    }

    $decoded = hatakiti_extract_json_from_ai_text( $ai_text );
    $valid   = hatakiti_occult_ai_validate_writing_structure( $decoded, $expected_keys );
    if ( is_wp_error( $valid ) ) {
        return $valid;
    }

    $result = array();
    foreach ( $decoded['articles'] as $article ) {
        $result[ $article['group_key'] ] = array(
            'headline' => sanitize_text_field( (string) $article['headline'] ),
            'body'     => (string) $article['body'],
        );
    }
    return $result;
}

/**
 * The full pipeline. Returns the new occult_weekly post ID (always
 * draft), or WP_Error.
 *
 * @param array|null $override_items When non-empty, used as the candidate
 *   news items instead of calling hatakiti_get_occult_weekly_candidates().
 *   Existing callers (cron, the manual "AIで週刊号を作成" page) never pass
 *   this and are completely unaffected. Introduced for 臨時発行
 *   (occult-weekly-auto-publish.php) so a manual test run can reuse
 *   already-linked news instead of being blocked by "no new news".
 */
/**
 * STEP1（選定・クラスタリング・分類）→ STEP2（バッチ分割執筆）→
 * STEP3（結合）の順で実行する。旧実装は「クラスタリング＋執筆＋分類」
 * を1回のAnthropic呼び出しにまとめており、2026-09-07にニュース18件の
 * 号でcURL error 28（280秒×3回とも応答0バイト）を起こして自動発行が
 * 失敗した。articles_json等の保存形式・hatakiti_process_occult_ai_
 * response()は完全に無変更 — STEP1/STEP2の結果を同じ
 * {issue_title, editorial_summary, articles:[...]} 形式へ組み立て直して
 * から渡すだけなので、DBスキーマ・PDF生成・保存経路への影響は無い。
 */
function hatakiti_generate_occult_weekly_draft_via_ai( $week_start, $week_end, $override_items = null ) {
    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $week_start ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $week_end ) ) {
        return new WP_Error( 'hatakiti_ai_bad_range', '対象期間（開始・終了）の形式が正しくありません。' );
    }

    $items = ! empty( $override_items ) ? $override_items : hatakiti_get_occult_weekly_candidates( $week_start, $week_end, 0 );
    if ( ! $items ) {
        return new WP_Error( 'hatakiti_ai_no_items', '対象期間内に、まだどの号にも使われていないニュースがありません。先にRSS取得（またはテストデータ投入）を行ってください。' );
    }

    $valid_ids   = wp_list_pluck( $items, 'ID' );
    $items_by_id = array();
    foreach ( $items as $item ) {
        $items_by_id[ $item->ID ] = $item;
    }

    // STEP1: 選定・クラスタリング・重要度／カテゴリ判定（本文執筆なし）。
    $planning = hatakiti_call_occult_ai_planning( $items, $week_start, $week_end );
    if ( is_wp_error( $planning ) ) {
        return $planning;
    }

    // STEP2: グループをHATAKITI_OCCULT_AI_WRITING_BATCH_SIZE件ずつの
    // バッチへ分割し、バッチごとに本文を執筆。1バッチでも失敗（リトライ
    // 上限到達）した場合は全体を失敗として返す — 号は
    // hatakiti_process_occult_ai_response()内でのみ作成されるため、
    // ここでエラーになっても中途半端な下書きは残らない。
    $batches = array_chunk( $planning['groups'], HATAKITI_OCCULT_AI_WRITING_BATCH_SIZE );
    $written = array();
    foreach ( $batches as $batch_index => $batch ) {
        $batch_result = hatakiti_call_occult_ai_writing_batch( $batch, $items_by_id, $week_start, $week_end, $batch_index );
        if ( is_wp_error( $batch_result ) ) {
            return $batch_result;
        }
        $written = $written + $batch_result;
    }

    // STEP3: STEP1の判定（importance/category/item_ids）とSTEP2の執筆
    // 結果（headline/body）を、旧単一呼び出し応答と同じ形へ結合する。
    $articles = array();
    foreach ( $planning['groups'] as $group ) {
        $key = $group['group_key'];
        if ( ! isset( $written[ $key ] ) ) {
            continue; // hatakiti_occult_ai_validate_writing_structure()が全key網羅を保証するため通常到達しない
        }
        $articles[] = array(
            'headline'        => $written[ $key ]['headline'],
            'importance'      => $group['importance'],
            'category'        => $group['category'],
            'body'            => $written[ $key ]['body'],
            'source_item_ids' => $group['item_ids'],
        );
    }

    if ( empty( $articles ) ) {
        return new WP_Error( 'hatakiti_ai_no_valid_articles', 'STEP1/STEP2の結果から有効な記事を1件も組み立てられませんでした。' );
    }

    $merged = array(
        'issue_title'       => $planning['issue_title'],
        'editorial_summary' => $planning['editorial_summary'],
        'articles'          => $articles,
    );

    return hatakiti_process_occult_ai_response( wp_json_encode( $merged ), $valid_ids, $week_start, $week_end );
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

    $groups            = array();
    $used_ids          = array();
    $order             = 0;
    $valid_categories  = hatakiti_occult_category_terms();

    foreach ( $data['articles'] as $article ) {
        $importance = isset( $article['importance'] ) ? (string) $article['importance'] : 'minor';
        $tier       = hatakiti_occult_ai_importance_to_tier( $importance );
        $headline   = isset( $article['headline'] ) ? sanitize_text_field( $article['headline'] ) : '';
        $body       = isset( $article['body'] ) ? wp_kses_post( $article['body'] ) : '';

        // カテゴリはニュース本文の生成・保存を一切左右しない — 欠落・
        // 不正な値は静かに「その他」へフォールバックする（週刊新聞の
        // 発行自体をカテゴリ分類の失敗で止めない、という方針）。
        $category = isset( $article['category'] ) && in_array( $article['category'], $valid_categories, true )
            ? $article['category']
            : 'その他';

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

        // カテゴリはoccult_weekly側のarticles_json（本文JSON）には一切
        // 持たせず、occult_news_item側のtaxonomyへの割り当てだけで完結
        // させる — 過去に発生した本文消失問題の経路（articles_json全体
        // の再構築・保存）に一切触れないため。失敗しても記事生成・保存
        // 自体には影響しない。
        foreach ( $source_ids as $sid ) {
            wp_set_object_terms( $sid, $category, 'occult_category' );
        }
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
    // wp_slash(): see the comment on the articles_json save in
    // hatakiti_finalize_occult_weekly_groups() — update_post_meta()
    // unslashes string values internally, so anything not coming
    // straight from $_POST needs to be pre-slashed or a literal
    // backslash in the text gets silently stripped.
    update_post_meta( $post_id, 'hatakiti_occult_editorial_summary', wp_slash( $editorial_summary ) );

    // Same save path the manual 号編集フォーム uses — the resulting draft
    // is a completely normal occult_weekly issue, editable by hand
    // afterward with no special-casing needed anywhere else. This also
    // means the same save guard (empty/degraded body protection) applies
    // here: if the AI response parsed structurally but produced no real
    // body text, finalize refuses to write it.
    $finalize_result = hatakiti_finalize_occult_weekly_groups( $post_id, $groups, array_values( array_unique( $used_ids ) ) );
    if ( is_wp_error( $finalize_result ) ) {
        // This post was just created for this generation and never had
        // any real content — safe to remove rather than leave a blank
        // orphan draft behind.
        wp_delete_post( $post_id, true );
        return $finalize_result;
    }

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
