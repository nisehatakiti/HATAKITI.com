<?php
/**
 * オカルトニュース（occult_news_item）のカテゴリ自動分類 — 既存記事の
 * 一括分類ツール（管理画面から手動実行、大量API呼び出しを避けるため
 * 自動実行はしない）。
 *
 * 新規ニュースのカテゴリ決定は、週刊オカルト新聞のAI編集処理
 * （occult-weekly-ai-edit.php の hatakiti_process_occult_ai_response()）
 * の中で既に行われる — このファイルは「まだカテゴリが付いていない
 * 既存記事」を後から分類するための、独立した軽量ツール。
 *
 * 週刊新聞のAI編集プロンプト・JSON検証・PDF生成・自動発行パイプラインは
 * 一切変更しない。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1件のニュースだけをAIに渡し、カテゴリのみを判定させる軽量プロンプト。
 * 週刊新聞編集用のプロンプト（記事執筆・クラスタリング等）とは完全に
 * 別物 — 本文生成は一切行わない。
 */
function hatakiti_build_occult_category_only_prompt( $item ) {
    $category_guide = <<<CATS
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

    $system = <<<SYS
あなたはオカルトニュースを分類するアシスタントです。与えられた1件のニュースを、次の10カテゴリのうち最も近いもの1つに分類してください。複数選択・新規カテゴリの作成は不可、必ず下記の表記のまま使用してください。分類に迷った場合は、記事の主題に最も近い1カテゴリを選んでください。

{$category_guide}

説明文やMarkdownのコードフェンスを一切付けず、以下の構造のJSONオブジェクトのみを出力してください。

{"category": "カテゴリ名"}
SYS;

    $lines = array(
        'タイトル: ' . get_the_title( $item ),
        '概要: ' . $item->post_content,
    );
    $source_text = get_post_meta( $item->ID, 'hatakiti_occult_source_article_text', true );
    if ( $source_text ) {
        $lines[] = '本文（参考、先頭のみ）: ' . mb_substr( (string) $source_text, 0, 2000 );
    }

    return array( $system, implode( "\n", $lines ) );
}

/**
 * カテゴリ未設定のoccult_news_itemを最大$limit件、1件ずつAIで分類する。
 * 失敗した記事は既存状態（タームなし）のまま変更しない — headline / body
 * 等のニュース本文フィールドには一切触れない。
 */
function hatakiti_occult_classify_unset_categories( $limit = 10 ) {
    $items = get_posts( array(
        'post_type'      => 'occult_news_item',
        'post_status'    => 'any',
        'posts_per_page' => $limit,
        'tax_query'      => array( array(
            'taxonomy' => 'occult_category',
            'operator' => 'NOT EXISTS',
        ) ),
    ) );

    $valid_categories = hatakiti_occult_category_terms();
    $success = 0;
    $failed  = 0;

    foreach ( $items as $item ) {
        list( $system, $prompt ) = hatakiti_build_occult_category_only_prompt( $item );
        $ai_text = hatakiti_call_occult_ai_text( $prompt, $system );

        if ( is_wp_error( $ai_text ) ) {
            hatakiti_occult_ai_log( array( 'source' => 'category_classify', 'item_id' => $item->ID, 'outcome' => 'ai_error' ) );
            $failed++;
            continue;
        }

        $data     = hatakiti_extract_json_from_ai_text( $ai_text );
        $category = ( is_array( $data ) && isset( $data['category'] ) ) ? $data['category'] : '';

        if ( ! in_array( $category, $valid_categories, true ) ) {
            hatakiti_occult_ai_log( array( 'source' => 'category_classify', 'item_id' => $item->ID, 'outcome' => 'invalid_category' ) );
            $failed++;
            continue;
        }

        wp_set_object_terms( $item->ID, $category, 'occult_category' );
        hatakiti_occult_ai_log( array( 'source' => 'category_classify', 'item_id' => $item->ID, 'category' => $category, 'outcome' => 'success' ) );
        $success++;
    }

    return array(
        'processed' => count( $items ),
        'success'   => $success,
        'failed'    => $failed,
    );
}

function hatakiti_register_occult_category_classify_page() {
    add_submenu_page(
        'edit.php?post_type=occult_weekly',
        'カテゴリ未設定の記事を分類',
        'カテゴリ未設定の記事を分類',
        'edit_posts',
        'hatakiti-occult-category-classify',
        'hatakiti_render_occult_category_classify_page'
    );
}
add_action( 'admin_menu', 'hatakiti_register_occult_category_classify_page' );

function hatakiti_render_occult_category_classify_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '権限がありません。' );
    }

    $result = null;
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_occult_category_classify_nonce'] ) ) {
        check_admin_referer( 'hatakiti_occult_category_classify_run', 'hatakiti_occult_category_classify_nonce' );
        $limit  = isset( $_POST['hatakiti_classify_limit'] ) ? (int) $_POST['hatakiti_classify_limit'] : 10;
        $limit  = in_array( $limit, array( 10, 20, 50 ), true ) ? $limit : 10;
        $result = hatakiti_occult_classify_unset_categories( $limit );
    }

    $unset_count = count( get_posts( array(
        'post_type'      => 'occult_news_item',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => array( array(
            'taxonomy' => 'occult_category',
            'operator' => 'NOT EXISTS',
        ) ),
    ) ) );
    ?>
    <div class="wrap hatakiti-record-form">
        <h1>カテゴリ未設定の記事を分類</h1>
        <p class="description">
            カテゴリが未設定のオカルトニュースを、1件ずつAIで分類します。成功した記事だけカテゴリを保存し、失敗した記事は既存状態のまま変更しません。ニュースの本文・見出し等は一切変更されません。
        </p>
        <p>現在カテゴリ未設定: <strong><?php echo (int) $unset_count; ?></strong> 件</p>

        <?php if ( ! hatakiti_occult_ai_is_configured() ) : ?>
            <div class="notice notice-warning">
                <p>AI APIが未設定です。<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=occult_weekly&page=hatakiti-occult-ai-settings' ) ); ?>">AI設定</a>でモデル名・APIキーを設定してから実行してください。</p>
            </div>
        <?php endif; ?>

        <?php if ( null !== $result ) : ?>
            <div class="notice notice-success">
                <p>処理件数: <?php echo (int) $result['processed']; ?> ／ 成功: <?php echo (int) $result['success']; ?> ／ 失敗（未変更）: <?php echo (int) $result['failed']; ?></p>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'hatakiti_occult_category_classify_run', 'hatakiti_occult_category_classify_nonce' ); ?>
            <p>
                <label>一度に処理する件数:
                    <select name="hatakiti_classify_limit">
                        <option value="10">10件</option>
                        <option value="20">20件</option>
                        <option value="50">50件</option>
                    </select>
                </label>
            </p>
            <p class="hatakiti-form-actions">
                <button type="submit" class="button button-primary"<?php disabled( ! hatakiti_occult_ai_is_configured() || 0 === $unset_count ); ?>>分類を実行する</button>
            </p>
        </form>
    </div>
    <?php
}
