<?php
/**
 * 週刊オカルト新聞 — 完全自動発行パイプライン。
 *
 * RSS取得 -> 新規ニュース判定 -> 元記事取得異常チェック -> AI週次編集
 * (hatakiti_generate_occult_weekly_draft_via_ai(), occult-weekly-ai-edit.php
 * で既存・無変更) -> DB再検証 -> PDF生成
 * (hatakiti_get_occult_weekly_pdf_path(), occult-weekly-pdf.php で既存・
 * 無変更) -> PDF検証 -> 公開ゲート -> (publishできるrun_typeのみ)publish ->
 * 公開URL実検証、までを1回の呼び出しでまとめて実行するラッパー。
 *
 * run_typeは3種類。cronからの本番実行と、管理画面からの臨時発行は、
 * この同じ`hatakiti_run_occult_weekly_auto_pipeline()`を通る — 別実装を
 * コピーして作らない。
 *
 *   - scheduled:       cron専用。新規ニュースのみを対象期間から選定し、
 *                       今週すでにpublish済みならスキップする。全ゲート
 *                       通過時のみpublishする。
 *   - manual_test:      管理画面「臨時発行（テスト）」用。既存号で使用済み
 *                       のニュースも含めて直近のものから候補を選び直す
 *                       （新規ニュースが無くてもテストできるようにする
 *                       ため）。今週の重複判定は適用しない。全ゲートを
 *                       通過しても絶対にpublishしない（常にdraft）。
 *   - manual_publish:   管理画面「臨時公開」用。候補選定はmanual_testと
 *                       同じ（使用済みニュース可）だが、今週の重複判定は
 *                       scheduledと同様に適用する（同週の二重公開を防ぐ）。
 *                       全ゲート通過時のみpublishする。
 *
 * どのrun_typeも判定ロジック自体は完全に同一 — テスト実行だけ甘い判定に
 * なることはない。
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
 * にかかわらず、実行時点から遡って1週間になるようこの単純なローリング窓
 * とした。
 */
function hatakiti_occult_auto_publish_default_week_range() {
    $end   = current_time( 'Y-m-d' );
    $start = date( 'Y-m-d', strtotime( $end . ' -6 days' ) );
    return array( $start, $end );
}

/**
 * ISO年-週番号。新しいpostmetaは追加せず、既存のhatakiti_occult_week_end
 * （手動・AI生成どちらの号にも既に保存されている）から導出する。
 */
function hatakiti_occult_auto_publish_week_key( $week_end ) {
    $ts = strtotime( (string) $week_end );
    if ( ! $ts ) {
        return '';
    }
    return date( 'oW', $ts );
}

/**
 * 今週分がpublish済みの号として既に存在するか。draft（前回の失敗など）は
 * 対象外 — 同じ週でも失敗後の再試行を妨げないため。
 */
function hatakiti_occult_auto_publish_already_published_this_week( $week_end ) {
    $key = hatakiti_occult_auto_publish_week_key( $week_end );
    if ( '' === $key ) {
        return false;
    }

    $published = get_posts( array(
        'post_type'      => 'occult_weekly',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_key'       => 'hatakiti_occult_week_end',
    ) );

    foreach ( $published as $pid ) {
        $end = get_post_meta( $pid, 'hatakiti_occult_week_end', true );
        if ( $end && hatakiti_occult_auto_publish_week_key( $end ) === $key ) {
            return $pid;
        }
    }
    return false;
}

/**
 * RSS取得そのものが全滅していないか（個別ソースの失敗は既存仕様通り許容
 * — ここで見るのは「有効なソースが1件以上あるのに、全件が失敗した」場合
 * のみ）。
 */
function hatakiti_occult_auto_publish_rss_anomaly( $rss_result ) {
    $total = count( $rss_result['per_source'] );
    if ( 0 === $total ) {
        return false;
    }
    $failed = 0;
    foreach ( $rss_result['per_source'] as $row ) {
        if ( '' !== $row['note'] && 0 === $row['fetched'] ) {
            $failed++;
        }
    }
    return $failed === $total;
}

/**
 * 元記事取得が対象ニュース全件で失敗していないか。
 * hatakiti_fetch_occult_source_article() は取得結果をpostmetaにキャッシュ
 * して二度目以降は再取得しない既存仕様のため、ここで先に呼んでも
 * AI編集プロンプト生成時の呼び出し（occult-weekly-ai-edit.php、無変更）と
 * 重複ネットワークアクセスにはならない。
 */
function hatakiti_occult_auto_publish_source_fetch_anomaly( $candidates ) {
    if ( empty( $candidates ) ) {
        return false;
    }
    $success = 0;
    foreach ( $candidates as $item ) {
        if ( 'success' === hatakiti_fetch_occult_source_article( $item->ID ) ) {
            $success++;
        }
    }
    return 0 === $success;
}

/**
 * 保存直後のDB再読込による独立検証（在庫のhatakiti_validate_occult_weekly_
 * groups_for_save() は保存"前"のゲートであり、これはその後にDBから読み直す
 * 別レイヤーの確認）。
 */
function hatakiti_occult_auto_publish_verify_db( $post_id ) {
    $raw      = get_post_meta( $post_id, 'hatakiti_occult_articles_json', true );
    $articles = json_decode( (string) $raw, true );

    if ( ! is_array( $articles ) || empty( $articles ) ) {
        return array( 'ok' => false, 'reason' => 'articles_jsonが空、または配列として読み取れません。' );
    }

    $body_total_chars = 0;
    $empty_body_count = 0;

    foreach ( $articles as $article ) {
        if ( empty( $article['headline'] ) || empty( $article['tier'] ) || empty( $article['news_item_ids'] ) ) {
            return array( 'ok' => false, 'reason' => 'headline / tier / news_item_ids のいずれかが欠落した記事があります。' );
        }
        $len = mb_strlen( wp_strip_all_tags( (string) ( $article['body'] ?? '' ) ) );
        if ( 0 === $len ) {
            $empty_body_count++;
        }
        $body_total_chars += $len;
    }

    if ( $empty_body_count > 0 ) {
        return array( 'ok' => false, 'reason' => "本文が空の記事が{$empty_body_count}件あります。" );
    }
    if ( 0 === $body_total_chars ) {
        return array( 'ok' => false, 'reason' => '本文の総文字数が0です。' );
    }

    return array(
        'ok'     => true,
        'reason' => '',
        'stats'  => array(
            'article_count'    => count( $articles ),
            'body_total_chars' => $body_total_chars,
        ),
    );
}

/**
 * PDF構造検証: 存在・サイズ>0・正常なPDFヘッダ・ページ数>=1・生成警告なし。
 * pdftotext等の本文抽出ツールはこのサーバー環境に無いため、テキスト抽出
 * チェックは行わない（指示書も「重い処理は必須にしない」と明示）。
 */
function hatakiti_occult_auto_publish_verify_pdf( $pdf_result ) {
    if ( is_wp_error( $pdf_result ) ) {
        return array( 'ok' => false, 'reason' => 'PDF生成エラー： ' . $pdf_result->get_error_message() );
    }
    if ( empty( $pdf_result['path'] ) || ! file_exists( $pdf_result['path'] ) ) {
        return array( 'ok' => false, 'reason' => 'PDFファイルが存在しません。' );
    }

    $size = filesize( $pdf_result['path'] );
    if ( ! $size ) {
        return array( 'ok' => false, 'reason' => 'PDFファイルサイズが0バイトです。' );
    }

    $fh   = fopen( $pdf_result['path'], 'rb' );
    $head = $fh ? fread( $fh, 5 ) : '';
    if ( $fh ) {
        fclose( $fh );
    }
    if ( '%PDF-' !== $head ) {
        return array( 'ok' => false, 'reason' => 'PDFファイルのヘッダが不正です。' );
    }

    $pages = isset( $pdf_result['pages'] ) ? (int) $pdf_result['pages'] : 0;
    if ( $pages < 1 ) {
        return array( 'ok' => false, 'reason' => 'PDFのページ数が1未満です。' );
    }

    if ( ! empty( $pdf_result['warnings'] ) ) {
        return array( 'ok' => false, 'reason' => 'PDF生成に警告があります： ' . implode( ' / ', $pdf_result['warnings'] ) );
    }

    return array( 'ok' => true, 'reason' => '', 'stats' => array( 'size' => $size, 'pages' => $pages ) );
}

/**
 * 通知メール用に、直近のAI呼び出しログ（occult-ai.phpのhatakiti_occult_ai_
 * log()が書き込む行）をerror_logの末尾から読み取るだけの関数。
 * occult-ai.php自体は一切変更しない — 既にそこが書いているログファイルを
 * 読むだけ。retry回数・stop_reason等の詳細診断はこの行にしか存在しない
 * ため、通知メールで参照できるようにする。本文・元記事全文・APIキーは
 * そもそもこのログに書き込まれていない（既存のログ設計）。
 */
function hatakiti_occult_auto_publish_recent_ai_log( $max_lines = 15 ) {
    $log_path = ini_get( 'error_log' );
    if ( ! $log_path || ! is_readable( $log_path ) ) {
        return '(サーバーのerror_logを確認してください)';
    }

    $lines = @file( $log_path, FILE_IGNORE_NEW_LINES );
    if ( ! $lines ) {
        return '(サーバーのerror_logを確認してください)';
    }

    $matched = array();
    for ( $i = count( $lines ) - 1; $i >= 0 && count( $matched ) < $max_lines; $i-- ) {
        if ( false !== strpos( $lines[ $i ], '[hatakiti_occult_ai]' ) ) {
            $matched[] = $lines[ $i ];
        }
    }

    return $matched ? implode( "\n", array_reverse( $matched ) ) : '(該当ログなし)';
}

/**
 * 失敗時のみ管理者へメール通知する（毎週の成功メールは送らない —
 * 指示書§22）。AI本文・元記事全文・APIキーは一切含めない。
 */
function hatakiti_occult_auto_publish_notify_admin( $stage, $reason, $context = array() ) {
    $to      = get_option( 'admin_email' );
    $subject = '【週刊オカルト新聞】自動発行失敗';

    $lines   = array();
    $lines[] = '実行日時: ' . current_time( 'Y-m-d H:i:s' );
    $lines[] = 'エラー段階: ' . $stage;
    if ( ! empty( $context['post_id'] ) ) {
        $lines[] = 'post_id: ' . $context['post_id'];
    }
    $lines[] = 'エラー概要: ' . $reason;
    $lines[] = '';
    $lines[] = '--- 直近のAI呼び出しログ（error_logより抜粋。本文・元記事全文・APIキーは含まれません） ---';
    $lines[] = hatakiti_occult_auto_publish_recent_ai_log();

    wp_mail( $to, $subject, implode( "\n", $lines ) );
}

/**
 * manual_test / manual_publish 用の候補選定。新規ニュース判定
 * （hatakiti_get_occult_weekly_candidates()、本番scheduled専用・無変更）
 * とは別に、既存号で使用済みのニュースも含めて直近の投稿日時順に一定数を
 * 選ぶ — 「新規ニュースが無いのでテストできない」状態を避けるための、
 * 臨時発行専用の選定ロジック。
 */
function hatakiti_occult_auto_publish_test_candidates( $limit = 8 ) {
    return get_posts( array(
        'post_type'      => 'occult_news_item',
        'post_status'    => 'any',
        'posts_per_page' => $limit,
        'orderby'        => 'meta_value',
        'meta_key'       => 'hatakiti_occult_published_at',
        'order'          => 'DESC',
    ) );
}

/**
 * フルパイプライン本体。
 *
 * @param string $run_type 'scheduled'（cron専用。新規ニュースのみ・今週の
 *   重複防止あり・全ゲート通過時にpublish） / 'manual_test'（管理画面の
 *   臨時発行「テスト」用。使用済みニュースも候補にでき、今週の重複防止も
 *   無視するが、絶対にpublishしない） / 'manual_publish'（管理画面の
 *   「臨時公開」用。候補選定はmanual_testと同じだが、今週の重複防止は
 *   scheduledと同様に適用し、全ゲート通過時にpublishする）。
 *   不正な値はmanual_test（最も安全側）に丸める。
 *
 * 戻り値は常に配列（WP_Errorは返さない — 呼び出し側がそのまま表示できる
 * ように統一）:
 *   'status'  => success | skipped_locked | skipped_already_published |
 *                no_new_news | error | published
 *   'message' => 人間向け説明
 *   'post_id' => 生成された号のID（該当する場合）
 *   'pdf'     => PDF生成結果（該当する場合）
 */
function hatakiti_run_occult_weekly_auto_pipeline( $run_type = 'manual_test' ) {
    if ( ! in_array( $run_type, array( 'scheduled', 'manual_test', 'manual_publish' ), true ) ) {
        $run_type = 'manual_test';
    }

    $can_publish       = in_array( $run_type, array( 'scheduled', 'manual_publish' ), true );
    $check_week_dedup  = in_array( $run_type, array( 'scheduled', 'manual_publish' ), true );
    $use_test_candidates = ( 'scheduled' !== $run_type );
    $notify_on_failure = ( 'scheduled' === $run_type ); // 手動実行は実行者が画面で直接結果を見るため、失敗メールはscheduledのみ。

    if ( ! hatakiti_occult_auto_publish_acquire_lock() ) {
        hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'lock', 'outcome' => 'skipped_locked' ) );
        return array(
            'status'  => 'skipped_locked',
            'message' => '既に自動発行処理が実行中のため、今回はスキップしました。',
        );
    }

    $started_at = microtime( true );
    hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'started', 'outcome' => 'running' ) );

    // 1. RSS取得（新規ニュース検出）。1ソースの失敗で全体を止めない既存
    // 仕様はそのまま。全ソース失敗だけを異常として扱う。
    $rss_result = hatakiti_fetch_all_occult_sources();
    hatakiti_occult_ai_log( array(
        'source'   => 'auto_publish',
        'run_type' => $run_type,
        'step'     => 'rss_fetch',
        'fetched'  => (int) $rss_result['fetched_total'],
        'created'  => (int) $rss_result['created_total'],
        'outcome'  => 'done',
    ) );

    if ( hatakiti_occult_auto_publish_rss_anomaly( $rss_result ) ) {
        hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'rss_fetch', 'outcome' => 'anomaly_all_sources_failed' ) );
        if ( $notify_on_failure ) {
            hatakiti_occult_auto_publish_notify_admin( 'rss_fetch', '有効な全ニュースソースの取得に失敗しました。' );
        }
        hatakiti_occult_auto_publish_release_lock();
        return array( 'status' => 'error', 'message' => '全ニュースソースの取得に失敗したため、処理を中止しました。既存号には影響していません。' );
    }

    list( $week_start, $week_end ) = hatakiti_occult_auto_publish_default_week_range();

    // 7. 同週重複防止: scheduled/manual_publishのみ適用。manual_testは
    // 何度でも実行できるよう無視する（指示書§6/§14）。
    if ( $check_week_dedup ) {
        $already_published_id = hatakiti_occult_auto_publish_already_published_this_week( $week_end );
        if ( $already_published_id ) {
            hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'week_check', 'existing_post_id' => $already_published_id, 'outcome' => 'skipped_already_published' ) );
            hatakiti_occult_auto_publish_release_lock();
            return array(
                'status'  => 'skipped_already_published',
                'message' => ( 'manual_publish' === $run_type )
                    ? sprintf( '今週（%s〜%s）は既に号 #%d が公開済みのため、臨時公開は行いませんでした（安全のため同週の重複公開はできません）。', $week_start, $week_end, $already_published_id )
                    : sprintf( '今週（%s〜%s）は既に号 #%d が公開済みのため、新しい号は作成しませんでした。', $week_start, $week_end, $already_published_id ),
            );
        }
    }

    // 候補選定: scheduledは新規ニュースのみ。manual_test/manual_publishは
    // 既存号で使用済みのニュースも含めて直近のものから選び直す。
    if ( $use_test_candidates ) {
        $candidates = hatakiti_occult_auto_publish_test_candidates();
        if ( ! $candidates ) {
            hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'candidates', 'count' => 0, 'outcome' => 'no_test_candidates' ) );
            hatakiti_occult_auto_publish_release_lock();
            return array(
                'status'  => 'no_new_news',
                'message' => 'テストに使用できるニュースが1件もありません。先にRSS取得（またはテストデータ投入）を行ってください。',
            );
        }
    } else {
        $candidates = hatakiti_get_occult_weekly_candidates( $week_start, $week_end, 0 );
        if ( ! $candidates ) {
            hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'candidates', 'count' => 0, 'outcome' => 'no_new_news' ) );
            hatakiti_occult_auto_publish_release_lock();
            return array(
                'status'  => 'no_new_news',
                'message' => sprintf( '対象期間（%s〜%s）に、まだどの号にも使われていない新規ニュースがありませんでした。号の生成は行いませんでした。', $week_start, $week_end ),
            );
        }
    }

    if ( hatakiti_occult_auto_publish_source_fetch_anomaly( $candidates ) ) {
        hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'source_fetch', 'outcome' => 'anomaly_all_failed' ) );
        if ( $notify_on_failure ) {
            hatakiti_occult_auto_publish_notify_admin( 'source_fetch', '対象ニュース全件で元記事本文の取得に失敗しました。' );
        }
        hatakiti_occult_auto_publish_release_lock();
        return array( 'status' => 'error', 'message' => '元記事本文の取得が対象ニュース全件で失敗したため、処理を中止しました。既存号には影響していません。' );
    }

    // 2. AI週次編集（クラスタリング・重要度判定・執筆・JSON完全性チェック
    // 込みのリトライ・保存ガード）— 既存関数を無変更で呼び出すのみ。
    // manual_test/manual_publishは選定済みの候補リストをそのまま渡す
    // （$override_items）— hatakiti_get_occult_weekly_candidates()の
    // 「新規ニュースのみ」判定はscheduledでのみ使われる。
    $post_id = hatakiti_generate_occult_weekly_draft_via_ai( $week_start, $week_end, $use_test_candidates ? $candidates : null );
    if ( is_wp_error( $post_id ) ) {
        hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'ai_generate', 'outcome' => 'error', 'code' => $post_id->get_error_code() ) );
        if ( $notify_on_failure ) {
            hatakiti_occult_auto_publish_notify_admin( 'ai_generate', $post_id->get_error_message() );
        }
        hatakiti_occult_auto_publish_release_lock();
        return array( 'status' => 'error', 'message' => 'AI週次編集でエラーが発生しました： ' . $post_id->get_error_message() );
    }
    hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'ai_generate', 'post_id' => $post_id, 'outcome' => 'success' ) );

    // 臨時発行由来であることを識別できるようにする（指示書§7）。
    // バックナンバー除外はmanual_testのみ（occult-cpt.php）。
    if ( 'scheduled' !== $run_type ) {
        update_post_meta( $post_id, 'hatakiti_occult_run_type', $run_type );
    }

    // 3. 保存直後のDB再読込による独立検証。
    $db_check = hatakiti_occult_auto_publish_verify_db( $post_id );
    hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'db_verify', 'post_id' => $post_id, 'outcome' => $db_check['ok'] ? 'success' : 'failed' ) );
    if ( ! $db_check['ok'] ) {
        if ( $notify_on_failure ) {
            hatakiti_occult_auto_publish_notify_admin( 'db_verify', $db_check['reason'], array( 'post_id' => $post_id ) );
        }
        hatakiti_occult_auto_publish_release_lock();
        return array(
            'status'  => 'error',
            'message' => sprintf( '号（下書き #%d）の保存後検証で問題が見つかりました： %s。draftのまま残しています。', $post_id, $db_check['reason'] ),
            'post_id' => $post_id,
        );
    }

    // 4. PDF生成（既存のキャッシュ付き生成関数を無変更で呼び出すのみ）。
    $pdf_result = hatakiti_get_occult_weekly_pdf_path( $post_id );
    $pdf_check  = hatakiti_occult_auto_publish_verify_pdf( $pdf_result );
    hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'pdf_verify', 'post_id' => $post_id, 'outcome' => $pdf_check['ok'] ? 'success' : 'failed' ) );
    if ( ! $pdf_check['ok'] ) {
        if ( $notify_on_failure ) {
            hatakiti_occult_auto_publish_notify_admin( 'pdf_generate', $pdf_check['reason'], array( 'post_id' => $post_id ) );
        }
        hatakiti_occult_auto_publish_release_lock();
        return array(
            'status'  => 'error',
            'message' => sprintf( '号（下書き #%d）のPDF検証に失敗しました： %s。draftのまま残しています（publishしていません）。', $post_id, $pdf_check['reason'] ),
            'post_id' => $post_id,
            'pdf'     => is_wp_error( $pdf_result ) ? null : $pdf_result,
        );
    }

    $elapsed = round( microtime( true ) - $started_at, 1 );

    // 全ゲート通過。publishできないrun_type（manual_test）はここで終了 —
    // 絶対にpublishしない。
    if ( ! $can_publish ) {
        hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'gate', 'post_id' => $post_id, 'elapsed_s' => $elapsed, 'outcome' => 'gate_passed_would_publish_in_production' ) );
        hatakiti_occult_auto_publish_release_lock();
        return array(
            'status'      => 'success',
            'message'     => sprintf( '週刊号の下書き（#%d）とPDFを生成しました。すべての公開ゲートを通過しています。テスト実行のため公開は行っていません。', $post_id ),
            'post_id'     => $post_id,
            'pdf'         => $pdf_result,
            'gate_passed' => true,
        );
    }

    // 13. 全条件を満たした場合のみpublish（scheduled / manual_publish）。
    // 14. publish直前の最終確認 — ここまでの各ステップで既に post_id /
    // articles_count / body_total_chars / pdf_path をログしているため、
    // ここでは公開直前スナップショットとして改めて記録する。
    hatakiti_occult_ai_log( array(
        'source'           => 'auto_publish',
        'run_type'         => $run_type,
        'step'             => 'pre_publish_snapshot',
        'post_id'          => $post_id,
        'article_count'    => $db_check['stats']['article_count'],
        'body_total_chars' => $db_check['stats']['body_total_chars'],
        'pdf_pages'        => $pdf_check['stats']['pages'],
    ) );

    $GLOBALS['hatakiti_occult_weekly_trusted_save'] = true;
    $publish_result = wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ), true );
    $GLOBALS['hatakiti_occult_weekly_trusted_save'] = false;

    if ( is_wp_error( $publish_result ) ) {
        hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'publish', 'post_id' => $post_id, 'outcome' => 'error' ) );
        hatakiti_occult_auto_publish_notify_admin( 'publish', $publish_result->get_error_message(), array( 'post_id' => $post_id ) );
        hatakiti_occult_auto_publish_release_lock();
        return array(
            'status'  => 'error',
            'message' => sprintf( '号（下書き #%d）のPDFは正常でしたが、公開処理自体に失敗しました： %s。PDFは削除していません。', $post_id, $publish_result->get_error_message() ),
            'post_id' => $post_id,
            'pdf'     => $pdf_result,
        );
    }

    // 12. PDF公開URL確認: ローカルファイルの存在だけでなく、公開直後に
    // 実際の匿名HTTPリクエストで取得できるかを確認する。draft状態では
    // PDF配信ハンドラがログイン編集者以外を拒否する既存の安全設計（変更
    // しない）と両立させるため、publish後に検証しダメならdraftへ戻す
    // 順序にしている。
    $pdf_url   = add_query_arg( 'hatakiti_pdf', '1', get_permalink( $post_id ) );
    $url_check = wp_remote_get( $pdf_url, array( 'timeout' => 15, 'cookies' => array() ) );
    $url_ok    = ! is_wp_error( $url_check )
        && 200 === wp_remote_retrieve_response_code( $url_check )
        && 0 === strpos( (string) wp_remote_retrieve_body( $url_check ), '%PDF-' );

    hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'pdf_url_verify', 'post_id' => $post_id, 'outcome' => $url_ok ? 'success' : 'failed' ) );

    if ( ! $url_ok ) {
        // 15. PDFは正常だがpublishが機能的に失敗したケース。PDFファイルは
        // 削除せず、公開状態だけdraftへ差し戻す。
        $GLOBALS['hatakiti_occult_weekly_trusted_save'] = true;
        wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ), true );
        $GLOBALS['hatakiti_occult_weekly_trusted_save'] = false;

        hatakiti_occult_auto_publish_notify_admin( 'pdf_url_verify', 'PDFの公開URLへの実アクセス確認に失敗したため、公開をdraftへ差し戻しました。', array( 'post_id' => $post_id ) );
        hatakiti_occult_auto_publish_release_lock();
        return array(
            'status'  => 'error',
            'message' => sprintf( '号（下書き #%d）はpublishしましたが、公開URLの実確認に失敗したためdraftへ差し戻しました。PDFファイル自体は削除していません。', $post_id ),
            'post_id' => $post_id,
            'pdf'     => $pdf_result,
        );
    }

    hatakiti_occult_ai_log( array( 'source' => 'auto_publish', 'run_type' => $run_type, 'step' => 'published', 'post_id' => $post_id, 'elapsed_s' => $elapsed, 'outcome' => 'success' ) );
    hatakiti_occult_auto_publish_release_lock();

    return array(
        'status'  => 'published',
        'message' => sprintf( '週刊号 #%d を公開しました。', $post_id ),
        'post_id' => $post_id,
        'pdf'     => $pdf_result,
        'pdf_url' => $pdf_url,
    );
}

/**
 * 管理画面: 臨時発行（テスト／臨時公開）。
 *
 * デフォルトは常に「臨時発行（テスト）」— 誤クリックしてもpublishされない
 * （指示書§4）。「臨時公開」は別フォーム・別nonce・より高い権限
 * （manage_options）・確認ダイアログ付きで、明確に区別する。
 */
function hatakiti_register_occult_auto_publish_page() {
    add_submenu_page(
        'edit.php?post_type=occult_weekly',
        '臨時発行（テスト）',
        '臨時発行（テスト）',
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

    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_occult_manual_test_nonce'] ) ) {
        check_admin_referer( 'hatakiti_occult_manual_test_run', 'hatakiti_occult_manual_test_nonce' );
        $result = hatakiti_run_occult_weekly_auto_pipeline( 'manual_test' );
    } elseif ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_occult_manual_publish_nonce'] ) ) {
        check_admin_referer( 'hatakiti_occult_manual_publish_run', 'hatakiti_occult_manual_publish_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( '臨時公開の実行には管理者権限が必要です。' );
        }
        $result = hatakiti_run_occult_weekly_auto_pipeline( 'manual_publish' );
    }

    list( $preview_start, $preview_end ) = hatakiti_occult_auto_publish_default_week_range();
    ?>
    <div class="wrap hatakiti-record-form">
        <h1>週刊オカルト新聞 — 臨時発行</h1>
        <p class="description">
            レイアウト確認・AI記事生成確認・PDF確認・回帰テスト・本番発行前の事前確認などのために、任意のタイミングで1号分の生成を試せます。<br>
            候補ニュースは、対象期間内の新規ニュースが無い場合でも、既存号で使用済みのものを含めて直近のニュースから自動選択されます（毎週月曜6:00の本番自動発行はこれとは別に、新規ニュースのみを対象にした従来どおりの判定で動作します）。
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
            } elseif ( in_array( $result['status'], array( 'no_new_news', 'skipped_locked', 'skipped_already_published' ), true ) ) {
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

        <h2>臨時発行（テスト）</h2>
        <p class="description">最新ニュースを使って新聞PDFを生成します。<strong>公開はされません</strong>（結果は必ず下書きのまま）。何度でも実行でき、毎週月曜の本番自動発行には影響しません。</p>
        <form method="post">
            <?php wp_nonce_field( 'hatakiti_occult_manual_test_run', 'hatakiti_occult_manual_test_nonce' ); ?>
            <p class="hatakiti-form-actions">
                <button type="submit" class="button button-primary"<?php disabled( ! hatakiti_occult_ai_is_configured() ); ?>>臨時発行（テスト）を実行する</button>
            </p>
        </form>

        <?php if ( current_user_can( 'manage_options' ) ) : ?>
            <h2 style="margin-top:40px; color:#a00;">臨時公開</h2>
            <p class="description"><strong>公開すると一般公開されます。</strong>通常のテスト用途では使用しないでください。今週すでに正式号が公開済みの場合は実行できません（安全のため同週の重複公開はできない設計です）。</p>
            <form method="post" onsubmit="return confirm('本当に臨時公開しますか？\nこの号は一般に公開されます。');">
                <?php wp_nonce_field( 'hatakiti_occult_manual_publish_run', 'hatakiti_occult_manual_publish_nonce' ); ?>
                <p class="hatakiti-form-actions">
                    <button type="submit" class="button"<?php disabled( ! hatakiti_occult_ai_is_configured() ); ?>>臨時公開を実行する（一般公開されます）</button>
                </p>
            </form>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * WP-CLI: 本番用の実行入口。サーバー側crontabから直接呼び出す想定
 * （wp hatakiti-occult-auto-publish run）。run_type='scheduled'で実行する
 * ため、新規ニュースのみを対象にし、全ゲート通過時は実際にpublishされる。
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    class Hatakiti_CLI_Occult_Auto_Publish {

        /**
         * 週刊オカルト新聞の自動発行パイプラインを本番（scheduled）
         * モードで実行する。全ての公開ゲートを通過した場合のみpublishする。
         *
         * ## EXAMPLES
         *
         *     wp hatakiti-occult-auto-publish run
         */
        public function run( $args, $assoc_args ) {
            $result = hatakiti_run_occult_weekly_auto_pipeline( 'scheduled' );

            WP_CLI::log( 'status: ' . $result['status'] );
            WP_CLI::log( 'message: ' . $result['message'] );
            if ( ! empty( $result['post_id'] ) ) {
                WP_CLI::log( 'post_id: ' . $result['post_id'] );
            }

            if ( 'error' === $result['status'] ) {
                WP_CLI::halt( 1 );
            }
        }
    }
    WP_CLI::add_command( 'hatakiti-occult-auto-publish', 'Hatakiti_CLI_Occult_Auto_Publish' );
}
