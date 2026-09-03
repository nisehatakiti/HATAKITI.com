<?php
/**
 * 週刊オカルト新聞 — 完全自動発行パイプライン（第1段階）。
 *
 * RSS取得 -> 新規ニュース判定 -> AI週次編集
 * (hatakiti_generate_occult_weekly_draft_via_ai(), occult-weekly-ai-edit.php
 * で既存・無変更) -> PDF生成 (hatakiti_get_occult_weekly_pdf_path(),
 * occult-weekly-pdf.php で既存・無変更) までを1回の呼び出しでまとめて実行する
 * ラッパー。
 *
 * この段階では自動公開は行わない — 生成される号は必ずdraftのまま。
 * 実行トリガーも今回はこのファイル内の管理画面「今すぐ実行」ボタンのみで、
 * 実際のWP-Cronスケジュール登録（wp_schedule_event）はまだ行わない
 * （曜日・時刻の決定は別途）。
 *
 * AI呼び出し・保存経路・PDF生成そのものは一切変更しない。既存関数を
 * そのまま呼び出すだけ。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HATAKITI_OCCULT_AUTO_PUBLISH_LOCK_OPTION', 'hatakiti_occult_auto_publish_lock' );
define( 'HATAKITI_OCCULT_AUTO_PUBLISH_LOCK_TTL', 30 * MINUTE_IN_SECONDS );

/**
 * add_option() は同名オプションが既に存在する場合INSERTに失敗して false を
 * 返す — get→set の2手順を踏むtransientよりも競合に強い、単純な排他ロック。
 * 前回の実行が異常終了してロックが残っている場合に備え、TTLを超えていれば
 * 一度だけ古いロックを削除して取り直す。
 */
function hatakiti_occult_auto_publish_acquire_lock() {
    if ( add_option( HATAKITI_OCCULT_AUTO_PUBLISH_LOCK_OPTION, time(), '', 'no' ) ) {
        return true;
    }

    $existing = get_option( HATAKITI_OCCULT_AUTO_PUBLISH_LOCK_OPTION );
    if ( $existing && ( time() - (int) $existing ) > HATAKITI_OCCULT_AUTO_PUBLISH_LOCK_TTL ) {
        delete_option( HATAKITI_OCCULT_AUTO_PUBLISH_LOCK_OPTION );
        return add_option( HATAKITI_OCCULT_AUTO_PUBLISH_LOCK_OPTION, time(), '', 'no' );
    }

    return false;
}

function hatakiti_occult_auto_publish_release_lock() {
    delete_option( HATAKITI_OCCULT_AUTO_PUBLISH_LOCK_OPTION );
}

/**
 * 直近7日間（本日を終了日とする）を対象期間とする。cronの実行曜日・時刻
 * そのものは未決定のため、対象期間はどの日に実行されても「その時点から
 * 遡って1週間」になるようこの単純なローリング窓とした。
 */
function hatakiti_occult_auto_publish_default_week_range() {
    $end   = current_time( 'Y-m-d' );
    $start = date( 'Y-m-d', strtotime( $end . ' -6 days' ) );
    return array( $start, $end );
}

/**
 * フルパイプライン本体。戻り値は常に配列：
 *   'status' => 'success' | 'no_new_news' | 'skipped_locked' | 'error'
 *   'message' => 人間向け説明
 *   'post_id' => 生成された号のID（successのみ）
 *   'pdf' => PDF生成結果（successのみ）
 * WP_Errorは返さない — 呼び出し側（手動トリガー画面）がそのまま表示できる
 * よう常に配列で返す。
 */
function hatakiti_run_occult_weekly_auto_pipeline() {
    if ( ! hatakiti_occult_auto_publish_acquire_lock() ) {
        hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'step' => 'lock', 'outcome' => 'skipped_locked' ) );
        return array(
            'status'  => 'skipped_locked',
            'message' => '既に自動発行処理が実行中のため、今回はスキップしました。',
        );
    }

    $started_at = microtime( true );
    hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'step' => 'start', 'outcome' => 'running' ) );

    // 1. RSS取得（新規ニュース検出）。1ソースの失敗で全体を止めない既存仕様
    // をそのまま利用。
    $rss_result = hatakiti_fetch_all_occult_sources();
    hatakiti_occult_ai_log( array(
        'source'   => 'auto_publish',
        'step'     => 'rss_fetch',
        'fetched'  => (int) $rss_result['fetched_total'],
        'created'  => (int) $rss_result['created_total'],
        'outcome'  => 'done',
    ) );

    list( $week_start, $week_end ) = hatakiti_occult_auto_publish_default_week_range();

    $candidates = hatakiti_get_occult_weekly_candidates( $week_start, $week_end, 0 );
    if ( ! $candidates ) {
        hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'step' => 'candidates', 'count' => 0, 'outcome' => 'no_new_news' ) );
        hatakiti_occult_auto_publish_release_lock();
        return array(
            'status'  => 'no_new_news',
            'message' => sprintf( '対象期間（%s〜%s）に、まだどの号にも使われていない新規ニュースがありませんでした。号の生成は行いませんでした。', $week_start, $week_end ),
        );
    }

    // 2. AI週次編集（クラスタリング・重要度判定・執筆・JSON完全性チェック
    // 込みのリトライ・保存ガード）— 既存関数を無変更で呼び出すのみ。
    $post_id = hatakiti_generate_occult_weekly_draft_via_ai( $week_start, $week_end );
    if ( is_wp_error( $post_id ) ) {
        hatakiti_occult_ai_log( array(
            'source'  => 'auto_publish',
            'step'    => 'ai_generate',
            'outcome' => 'error',
            'code'    => $post_id->get_error_code(),
        ) );
        hatakiti_occult_auto_publish_release_lock();
        return array(
            'status'  => 'error',
            'message' => 'AI週次編集でエラーが発生しました： ' . $post_id->get_error_message(),
        );
    }

    hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'step' => 'ai_generate', 'post_id' => $post_id, 'outcome' => 'success' ) );

    // 3. PDF生成（既存のキャッシュ付き生成関数を無変更で呼び出すのみ）。
    $pdf_result = hatakiti_get_occult_weekly_pdf_path( $post_id );
    if ( is_wp_error( $pdf_result ) ) {
        hatakiti_occult_ai_log( array(
            'source'  => 'auto_publish',
            'step'    => 'pdf_generate',
            'post_id' => $post_id,
            'outcome' => 'error',
            'code'    => $pdf_result->get_error_code(),
        ) );
        hatakiti_occult_auto_publish_release_lock();
        return array(
            'status'  => 'error',
            'message' => sprintf( '号（下書き #%d）の生成までは成功しましたが、PDF生成でエラーが発生しました： %s', $post_id, $pdf_result->get_error_message() ),
            'post_id' => $post_id,
        );
    }

    $elapsed = round( microtime( true ) - $started_at, 1 );
    hatakiti_occult_ai_log( array(
        'source'   => 'auto_publish',
        'step'     => 'pdf_generate',
        'post_id'  => $post_id,
        'pages'    => isset( $pdf_result['pages'] ) ? $pdf_result['pages'] : 'n/a',
        'warnings' => count( $pdf_result['warnings'] ),
        'elapsed_s' => $elapsed,
        'outcome'  => 'success',
    ) );

    hatakiti_occult_auto_publish_release_lock();

    return array(
        'status'  => 'success',
        'message' => sprintf( '週刊号の下書き（#%d）とPDFを生成しました。公開は行っていません — 内容を確認のうえ、手動で公開してください。', $post_id ),
        'post_id' => $post_id,
        'pdf'     => $pdf_result,
    );
}

/**
 * 管理画面: 手動「今すぐ実行」トリガー。実際のcronスケジュール登録は
 * まだ行わないため、この画面が現時点で唯一の実行入口。
 */
function hatakiti_register_occult_auto_publish_page() {
    add_submenu_page(
        'edit.php?post_type=occult_weekly',
        '自動発行（テスト実行）',
        '自動発行（テスト実行）',
        'edit_posts',
        'hatakiti-occult-auto-publish',
        'hatakiti_render_occult_auto_publish_page'
    );
}
add_action( 'admin_menu', 'hatakiti_register_occult_auto_publish_page' );

function hatakiti_render_occult_auto_publish_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '権限がありません。' );
    }

    $result = null;
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_occult_auto_publish_nonce'] ) ) {
        check_admin_referer( 'hatakiti_occult_auto_publish_run', 'hatakiti_occult_auto_publish_nonce' );
        $result = hatakiti_run_occult_weekly_auto_pipeline();
    }

    list( $preview_start, $preview_end ) = hatakiti_occult_auto_publish_default_week_range();
    ?>
    <div class="wrap hatakiti-record-form">
        <h1>週刊オカルト新聞 — 自動発行（テスト実行）</h1>
        <p class="description">
            RSS取得 → 新規ニュース判定 → AI週次編集 → PDF生成 を1回の操作でまとめて実行します。<br>
            この段階では<strong>自動公開は行いません</strong>。生成される号は必ず下書き状態のままです。実際のスケジュール実行（cron）はまだ設定されておらず、実行できるのはこの画面からの手動実行のみです。<br>
            対象期間は実行時点から遡って7日間（本日実行した場合: <?php echo esc_html( $preview_start ); ?> 〜 <?php echo esc_html( $preview_end ); ?>）です。
        </p>

        <?php if ( ! hatakiti_occult_ai_is_configured() ) : ?>
            <div class="notice notice-warning">
                <p>AI APIが未設定です。<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=occult_weekly&page=hatakiti-occult-ai-settings' ) ); ?>">AI設定</a>でモデル名・APIキーを設定してから実行してください。</p>
            </div>
        <?php endif; ?>

        <?php if ( null !== $result ) : ?>
            <?php
            $notice_class = 'success';
            if ( 'error' === $result['status'] ) {
                $notice_class = 'error';
            } elseif ( in_array( $result['status'], array( 'no_new_news', 'skipped_locked' ), true ) ) {
                $notice_class = 'warning';
            }
            ?>
            <div class="notice notice-<?php echo esc_attr( $notice_class ); ?>">
                <p><?php echo esc_html( $result['message'] ); ?></p>
                <?php if ( ! empty( $result['post_id'] ) ) : ?>
                    <p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=hatakiti-occult-weekly-form&post_id=' . $result['post_id'] ) ); ?>">「号を編集」で内容を確認する</a>
                        <?php if ( ! empty( $result['pdf'] ) && ! is_wp_error( $result['pdf'] ) ) : ?>
                            ／ <a href="<?php echo esc_url( add_query_arg( 'hatakiti_pdf', '1', get_permalink( $result['post_id'] ) ) ); ?>">生成されたPDFを見る</a>
                            （<?php echo isset( $result['pdf']['pages'] ) ? (int) $result['pdf']['pages'] : '?'; ?>ページ、警告<?php echo count( $result['pdf']['warnings'] ); ?>件）
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'hatakiti_occult_auto_publish_run', 'hatakiti_occult_auto_publish_nonce' ); ?>
            <p class="hatakiti-form-actions">
                <button type="submit" class="button button-primary"<?php disabled( ! hatakiti_occult_ai_is_configured() ); ?>>今すぐ実行する</button>
            </p>
        </form>
    </div>
    <?php
}
