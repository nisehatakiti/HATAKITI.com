<?php
/**
 * 元記事本文の取得・抽出 — 週刊オカルト新聞のAI記事生成が、短いRSS要約
 * だけでなく元記事に書かれた事実を参照できるようにする（指示書「RSS＝
 * ニュース発見用、元記事＝記事作成用」の役割分離）。
 *
 * 保存目的ではなく参照目的: 抽出したテキストは記事執筆の材料として一時
 * 的にAIへ渡すためのものであり、元記事の転載・再配布を目的としない
 * （指示書§15）。そのため長さの上限を設けて保存し、無制限の全文保存は
 * 行わない。
 *
 * 対象媒体はDB上のoccult_news_source（現在: Webムー・TOCANA）に登録
 * されたホストのみ — 勝手に対象を増やさない（指示書§9）。robots.txt・
 * 利用規約を事前確認済み（docs/16参照）: 記事ページの自動取得を明確に
 * 禁止する記載はなし。TOCANAは著作権ページで「引用」の枠内での利用
 * （原文との区別・出典明示）を明示的に許容している。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 元記事取得を許可するホスト — 実際に登録済みのoccult_news_sourceの
 * website_url/rss_urlから動的に取得する。コードに固定リストを書かず、
 * DBの登録内容がそのまま許可リストになる（ソースを追加・無効化すれば
 * 自動的に反映される）。
 */
function hatakiti_occult_allowed_fetch_hosts() {
    $sources = get_posts( array(
        'post_type'      => 'occult_news_source',
        'post_status'    => 'any',
        'posts_per_page' => -1,
    ) );

    $hosts = array();
    foreach ( $sources as $source ) {
        foreach ( array( 'hatakiti_occult_website_url', 'hatakiti_occult_rss_url' ) as $key ) {
            $url  = get_post_meta( $source->ID, $key, true );
            $host = $url ? wp_parse_url( $url, PHP_URL_HOST ) : '';
            if ( $host ) {
                $hosts[ strtolower( $host ) ] = true;
            }
        }
    }
    return array_keys( $hosts );
}

/**
 * サイトごとの本文抽出ルール。実際に取得した記事HTMLを直接確認した上
 * で決定した値（指示書§11: 共通抽出を第一候補とし、必要な場合のみ媒体
 * 別ルールを追加する — ここでは2媒体ともHTML構造が異なったため、媒体
 * 別のコンテナID＋段落セレクタを指定している）。
 *
 *   web-mu.jp: 本文コンテナ #singleContents、段落は
 *              <p class="wp-block-paragraph">（WordPress Gutenberg）
 *   tocana.jp: 本文コンテナ #entryBody、本文の<p>はclass属性を持たない
 *              （関連記事・広告ブロック内の<p>は class="entryTitle" 等
 *              を持つため、それらは除外できる）
 *
 * 未登録ホスト（将来ソースが増えた場合）は汎用フォールバックへ。
 */
function hatakiti_occult_extraction_rule_for_host( $host ) {
    $rules = array(
        'web-mu.jp' => array(
            'container_id'    => 'singleContents',
            'paragraph_xpath' => './/p[contains(concat(" ", normalize-space(@class), " "), " wp-block-paragraph ")] | .//h2[contains(@class,"wp-block-heading")] | .//h3[contains(@class,"wp-block-heading")]',
        ),
        'tocana.jp' => array(
            'container_id'    => 'entryBody',
            'paragraph_xpath' => './/p[not(@class)] | .//h2[not(@class) or not(contains(@class,"moduleTitle"))][not(ancestor::div[contains(@class,"module")])] ',
        ),
    );
    return isset( $rules[ $host ] ) ? $rules[ $host ] : null;
}

/**
 * SSRFガード: 許可ホストへのhttp/https、かつ解決先IPが非公開アドレス
 * （ループバック・プライベート・リンクローカル等）でないことを確認。
 */
function hatakiti_occult_url_is_safe( $url, array $allowed_hosts ) {
    $parts = wp_parse_url( $url );
    if ( empty( $parts['scheme'] ) || ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
        return false;
    }
    if ( empty( $parts['host'] ) ) {
        return false;
    }
    $host = strtolower( $parts['host'] );
    if ( ! in_array( $host, $allowed_hosts, true ) ) {
        return false;
    }

    $ip = filter_var( $host, FILTER_VALIDATE_IP ) ? $host : gethostbyname( $host );
    if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
        return false; // DNS resolution failed
    }
    if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
        return false; // loopback / private / reserved / link-local
    }

    return true;
}

/**
 * 安全に元記事HTMLを取得する。リダイレクトは自動追従させず（WordPress
 * のHTTP APIが内部で許可リストを再検証しないまま追従してしまうため）、
 * 各ホップを自前で検証しながら手動でたどる。
 */
function hatakiti_occult_fetch_html_safely( $url, array $allowed_hosts, $max_redirects = 3 ) {
    for ( $hop = 0; $hop <= $max_redirects; $hop++ ) {
        if ( ! hatakiti_occult_url_is_safe( $url, $allowed_hosts ) ) {
            return new WP_Error( 'hatakiti_occult_fetch_unsafe_url', '許可されていないURL、または安全性を確認できないURLです。' );
        }

        $response = wp_remote_get( $url, array(
            'timeout'             => 15,
            'redirection'         => 0, // 手動でホップごとに検証する
            'limit_response_size' => 2 * MB_IN_BYTES,
            'headers'             => array(
                'User-Agent' => 'HATAKITI.com-OccultWeekly/1.0 (+https://hatakiti.com/)',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );

        if ( in_array( $code, array( 301, 302, 303, 307, 308 ), true ) ) {
            $location = wp_remote_retrieve_header( $response, 'location' );
            if ( ! $location ) {
                return new WP_Error( 'hatakiti_occult_fetch_bad_redirect', 'リダイレクト先が不明です。' );
            }
            // 相対リダイレクトも考慮
            $url = wp_parse_url( $location, PHP_URL_HOST ) ? $location : trailingslashit( 'https://' . wp_parse_url( $url, PHP_URL_HOST ) ) . ltrim( $location, '/' );
            continue;
        }

        if ( 200 !== $code ) {
            return new WP_Error( 'hatakiti_occult_fetch_http_error', 'HTTPステータス ' . $code );
        }

        $content_type = wp_remote_retrieve_header( $response, 'content-type' );
        if ( $content_type && false === stripos( $content_type, 'text/html' ) ) {
            return new WP_Error( 'hatakiti_occult_fetch_bad_content_type', 'HTML以外のコンテンツです: ' . $content_type );
        }

        return wp_remote_retrieve_body( $response );
    }

    return new WP_Error( 'hatakiti_occult_fetch_too_many_redirects', 'リダイレクトが多すぎます。' );
}

/**
 * 取得したHTMLから本文らしきテキストを抽出する。script/style/figureは
 * 常に除去し、可能ならサイト別ルール、なければ汎用フォールバック
 * （<article>優先、なければ<body>全体からscript等を除いたテキスト）。
 * 抽出結果は転載目的ではなく事実参照用の資料として上限文字数で切る
 * （指示書§15, §23, §25）。
 */
function hatakiti_occult_extract_article_text( $html, $host, $max_chars = 4000 ) {
    if ( '' === trim( (string) $html ) ) {
        return '';
    }

    libxml_use_internal_errors( true );
    $dom = new DOMDocument();
    $dom->loadHTML( '<?xml encoding="utf-8"?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR );
    libxml_clear_errors();

    // 常に不要な要素を除去(script/style/noscript/figure=画像+キャプション)。
    $xpath = new DOMXPath( $dom );
    foreach ( array( '//script', '//style', '//noscript', '//figure', '//iframe' ) as $q ) {
        foreach ( iterator_to_array( $xpath->query( $q ) ) as $node ) {
            $node->parentNode->removeChild( $node );
        }
    }
    // XPathの結果はDOM変更後は再取得し直す必要がある。
    $xpath = new DOMXPath( $dom );

    $rule      = hatakiti_occult_extraction_rule_for_host( $host );
    $container = null;

    if ( $rule ) {
        $container = $dom->getElementById( $rule['container_id'] );
    }

    $texts = array();

    if ( $container && $rule ) {
        $nodes = $xpath->query( $rule['paragraph_xpath'], $container );
        foreach ( $nodes as $node ) {
            $t = trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );
            if ( '' !== $t ) {
                $texts[] = $t;
            }
        }
    }

    // フォールバック: サイト別ルールが無い、またはコンテナ・段落が
    // 見つからなかった場合は <article> タグ、それも無ければ <body>
    // 全体からテキストを拾う。精度は保証しない（指示書§10: 完全自動
    // 抽出を前提としない）。
    if ( empty( $texts ) ) {
        $fallback_node = $dom->getElementsByTagName( 'article' )->item( 0 );
        if ( ! $fallback_node ) {
            $fallback_node = $dom->getElementsByTagName( 'body' )->item( 0 );
        }
        if ( $fallback_node ) {
            foreach ( $xpath->query( './/p', $fallback_node ) as $node ) {
                $t = trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );
                if ( mb_strlen( $t ) > 10 ) { // 極端に短い断片(ナビ等)を除外
                    $texts[] = $t;
                }
            }
        }
    }

    $text = implode( "\n\n", $texts );
    $text = trim( $text );

    if ( mb_strlen( $text ) > $max_chars ) {
        $text = mb_substr( $text, 0, $max_chars ) . '…（以下省略）';
    }

    return $text;
}

/**
 * 1件のoccult_news_itemについて、元記事の取得・抽出を行い結果を
 * meta保存する。取得成功/失敗どちらでもfetch_statusを記録し、失敗時は
 * RSS要約のみへのフォールバックが自然に成立する（本文は空のまま残る
 * だけなので、プロンプト生成側は「本文なければRSS要約のみ使う」で
 * 対応できる）。二重取得を避けるため、既にstatusが記録済みなら再取得
 * しない（指示書§24: 簡易キャッシュ、新しい仕組みは作らずmetaで足りる）。
 */
function hatakiti_fetch_occult_source_article( $item_id ) {
    $existing_status = get_post_meta( $item_id, 'hatakiti_occult_source_article_fetch_status', true );
    if ( $existing_status ) {
        return $existing_status; // 既に取得試行済み — 再取得しない
    }

    $url = get_post_meta( $item_id, 'hatakiti_occult_original_url', true );
    if ( ! $url ) {
        update_post_meta( $item_id, 'hatakiti_occult_source_article_fetch_status', 'no_url' );
        return 'no_url';
    }

    $host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
    $allowed_hosts = hatakiti_occult_allowed_fetch_hosts();

    $html = hatakiti_occult_fetch_html_safely( $url, $allowed_hosts );

    if ( is_wp_error( $html ) ) {
        update_post_meta( $item_id, 'hatakiti_occult_source_article_fetch_status', 'failed' );
        update_post_meta( $item_id, 'hatakiti_occult_source_article_fetch_error', $html->get_error_code() );
        update_post_meta( $item_id, 'hatakiti_occult_source_article_fetched_at', current_time( 'mysql' ) );
        return 'failed';
    }

    $text = hatakiti_occult_extract_article_text( $html, $host );

    if ( '' === $text ) {
        update_post_meta( $item_id, 'hatakiti_occult_source_article_fetch_status', 'extract_failed' );
        update_post_meta( $item_id, 'hatakiti_occult_source_article_fetched_at', current_time( 'mysql' ) );
        return 'extract_failed';
    }

    update_post_meta( $item_id, 'hatakiti_occult_source_article_text', $text );
    update_post_meta( $item_id, 'hatakiti_occult_source_article_fetch_status', 'success' );
    update_post_meta( $item_id, 'hatakiti_occult_source_article_fetched_at', current_time( 'mysql' ) );

    return 'success';
}
