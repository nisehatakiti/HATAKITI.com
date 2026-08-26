<?php
/**
 * RSS fetch for 週刊オカルト新聞 (指示書 §15–17).
 *
 * Manual only for this pass — a "最新ニュースを取得" button in wp-admin,
 * not a WP-Cron/ConoHa-cron job (docs/07 §自動実行 explicitly defers that
 * to a later implementation pass).
 *
 * Uses WordPress core's own fetch_feed() (SimplePie) rather than a new
 * dependency. Only title / short summary / URL / published date are
 * stored — never the feed's full content:encoded body — per 指示書 §14's
 * explicit "元記事本文をそのままコピー・転載する機能は作らない".
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_register_occult_rss_page() {
    add_submenu_page(
        'edit.php?post_type=occult_weekly',
        'RSS取得',
        'RSS取得',
        'edit_posts',
        'hatakiti-occult-rss-fetch',
        'hatakiti_render_occult_rss_page'
    );
}
add_action( 'admin_menu', 'hatakiti_register_occult_rss_page' );

function hatakiti_render_occult_rss_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '権限がありません。' );
    }

    $results   = null;
    $seed_result = null;
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_occult_rss_nonce'] ) ) {
        check_admin_referer( 'hatakiti_occult_rss_fetch', 'hatakiti_occult_rss_nonce' );
        $results = hatakiti_fetch_all_occult_sources();
    } elseif ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_occult_seed_nonce'] ) ) {
        check_admin_referer( 'hatakiti_occult_seed_test_data', 'hatakiti_occult_seed_nonce' );
        $seed_result = hatakiti_seed_occult_test_data();
    }

    $sources = get_posts( array(
        'post_type'      => 'occult_news_source',
        'post_status'    => 'any',
        'posts_per_page' => -1,
    ) );
    ?>
    <div class="wrap hatakiti-record-form">
        <h1>週刊オカルト新聞 — RSS取得</h1>

        <?php if ( is_array( $results ) ) : ?>
            <div class="notice notice-success">
                <p>
                    取得件数: <?php echo (int) $results['fetched_total']; ?> /
                    新規保存: <?php echo (int) $results['created_total']; ?> /
                    重複スキップ: <?php echo (int) $results['duplicate_total']; ?>
                </p>
            </div>
            <table class="widefat striped">
                <thead><tr><th>ソース</th><th>取得</th><th>新規</th><th>重複</th><th>備考</th></tr></thead>
                <tbody>
                    <?php foreach ( $results['per_source'] as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( $row['name'] ); ?></td>
                            <td><?php echo (int) $row['fetched']; ?></td>
                            <td><?php echo (int) $row['created']; ?></td>
                            <td><?php echo (int) $row['duplicate']; ?></td>
                            <td><?php echo esc_html( $row['note'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>登録済みソース</h2>
        <?php if ( $sources ) : ?>
            <table class="widefat striped">
                <thead><tr><th>ソース名</th><th>RSS URL</th><th>状態</th></tr></thead>
                <tbody>
                    <?php foreach ( $sources as $source ) : ?>
                        <?php
                        $enabled = get_post_meta( $source->ID, 'hatakiti_occult_enabled', true );
                        $rss_url = get_post_meta( $source->ID, 'hatakiti_occult_rss_url', true );
                        ?>
                        <tr>
                            <td><a href="<?php echo esc_url( get_edit_post_link( $source->ID ) ); ?>"><?php echo esc_html( get_the_title( $source ) ); ?></a></td>
                            <td><code><?php echo esc_html( $rss_url ); ?></code></td>
                            <td><?php echo '1' === $enabled ? '有効' : '無効'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=occult_news_source' ) ); ?>">ニュースソースを追加</a>してください。</p>
        <?php endif; ?>

        <?php if ( is_array( $seed_result ) ) : ?>
            <div class="notice notice-success">
                <p>
                    テストデータ投入: 新規 <?php echo (int) $seed_result['created']; ?> 件 /
                    既存（スキップ） <?php echo (int) $seed_result['duplicate']; ?> 件
                </p>
            </div>
        <?php endif; ?>

        <form method="post" style="margin-top:20px;">
            <?php wp_nonce_field( 'hatakiti_occult_rss_fetch', 'hatakiti_occult_rss_nonce' ); ?>
            <button type="submit" class="button button-primary">最新ニュースを取得</button>
        </form>

        <h2 style="margin-top:32px;">テストデータ（開発用）</h2>
        <p class="description">外部サイトへ実際にアクセスしなくても、動作確認用の固定ダミーニュース5件（うち2件は同一事件想定でクラスタ統合の確認用）を投入できます。タイトルはすべて「【テストデータ】」から始まり、URLは実在しないexample.comドメインです。再実行しても重複追加はされません。</p>
        <form method="post">
            <?php wp_nonce_field( 'hatakiti_occult_seed_test_data', 'hatakiti_occult_seed_nonce' ); ?>
            <button type="submit" class="button">テストデータを投入</button>
        </form>
    </div>
    <?php
}

/**
 * Fetches every enabled occult_news_source and saves new occult_news_item
 * posts. Never fatals on one bad feed — a failed source is reported and
 * the rest still run.
 */
function hatakiti_fetch_all_occult_sources() {
    include_once ABSPATH . WPINC . '/feed.php';

    $sources = get_posts( array(
        'post_type'      => 'occult_news_source',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'meta_key'       => 'hatakiti_occult_enabled',
        'meta_value'     => '1',
    ) );

    $fetched_total   = 0;
    $created_total   = 0;
    $duplicate_total = 0;
    $per_source      = array();

    foreach ( $sources as $source ) {
        $rss_url = get_post_meta( $source->ID, 'hatakiti_occult_rss_url', true );
        $row = array(
            'name'      => get_the_title( $source ),
            'fetched'   => 0,
            'created'   => 0,
            'duplicate' => 0,
            'note'      => '',
        );

        if ( ! $rss_url ) {
            $row['note'] = 'RSS URL未設定のためスキップ';
            $per_source[] = $row;
            continue;
        }

        $feed = fetch_feed( $rss_url );
        if ( is_wp_error( $feed ) ) {
            $row['note'] = '取得エラー: ' . $feed->get_error_message();
            $per_source[] = $row;
            continue;
        }

        $items = $feed->get_items( 0, $feed->get_item_quantity( 50 ) );
        foreach ( $items as $item ) {
            $row['fetched']++;
            $fetched_total++;

            $result = hatakiti_save_occult_news_item( $source, $item );
            if ( 'created' === $result ) {
                $row['created']++;
                $created_total++;
            } else {
                $row['duplicate']++;
                $duplicate_total++;
            }
        }

        $per_source[] = $row;
    }

    return array(
        'fetched_total'   => $fetched_total,
        'created_total'   => $created_total,
        'duplicate_total' => $duplicate_total,
        'per_source'      => $per_source,
    );
}

/**
 * Some feeds (confirmed: TOCANA) emit titles that are entity-encoded
 * more than once at the source — e.g. a real curly quote gets encoded
 * to "&#8220;" and then THAT gets escaped again as "&amp;#8220;" before
 * being written into the feed's XML. SimplePie's XML parsing only
 * undoes the outer layer, leaving literal "&#8220;" text behind.
 * Decoding once is not enough in that case, so decode repeatedly until
 * a pass makes no further change (capped so a malformed string can't
 * loop forever). A normally single-encoded or plain title is unaffected
 * — the second pass is simply a no-op.
 */
function hatakiti_fully_decode_entities( $text ) {
    for ( $i = 0; $i < 5; $i++ ) {
        $decoded = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
        if ( $decoded === $text ) {
            break;
        }
        $text = $decoded;
    }
    return $text;
}

/**
 * Saves one feed item as occult_news_item, or skips it if already
 * present. Dedup priority per 指示書 §8: original_url first, then
 * content_hash.
 */
function hatakiti_save_occult_news_item( $source_post, $item ) {
    $original_url = esc_url_raw( (string) $item->get_permalink() );
    // esc_html() at output time re-encodes correctly, so the DB should
    // hold plain text, not markup — see hatakiti_fully_decode_entities().
    $title = hatakiti_fully_decode_entities( (string) $item->get_title() );
    $title = sanitize_text_field( $title );

    if ( ! $original_url || ! $title ) {
        return 'duplicate'; // nothing usable to store; treat as skipped.
    }

    $content_hash = hash( 'sha256', $title );

    $existing_by_url = get_posts( array(
        'post_type'      => 'occult_news_item',
        'post_status'    => 'any',
        'meta_key'       => 'hatakiti_occult_original_url',
        'meta_value'     => $original_url,
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( $existing_by_url ) {
        return 'duplicate';
    }

    $existing_by_hash = get_posts( array(
        'post_type'      => 'occult_news_item',
        'post_status'    => 'any',
        'meta_key'       => 'hatakiti_occult_content_hash',
        'meta_value'     => $content_hash,
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( $existing_by_hash ) {
        return 'duplicate';
    }

    // RSS summary only — never the full content:encoded body (指示書 §14).
    $summary = (string) $item->get_description();
    $summary = wp_strip_all_tags( $summary );
    $summary = hatakiti_fully_decode_entities( $summary );
    $summary = wp_trim_words( $summary, 60, '…' );

    $published = $item->get_date( 'Y-m-d H:i:s' );

    $post_id = wp_insert_post( array(
        'post_type'    => 'occult_news_item',
        'post_title'   => $title,
        'post_content' => $summary,
        'post_status'  => 'publish', // internal working data; "publish" here just means "usable", not public (post_type is not public).
    ), true );

    if ( is_wp_error( $post_id ) ) {
        return 'duplicate';
    }

    // Keep whatever else the feed provided (guid, author, categories) as a
    // JSON blob rather than adding more first-class meta keys per field —
    // this is provenance/debugging data, not something templates render.
    $author = $item->get_author();
    $raw_metadata = array(
        'guid'       => (string) $item->get_id(),
        'author'     => $author ? (string) $author->get_name() : '',
        'categories' => array_map(
            function ( $cat ) { return (string) $cat->get_label(); },
            (array) $item->get_categories()
        ),
    );

    update_post_meta( $post_id, 'hatakiti_occult_source_post_id', $source_post->ID );
    update_post_meta( $post_id, 'hatakiti_occult_source_name', get_the_title( $source_post ) );
    update_post_meta( $post_id, 'hatakiti_occult_original_url', $original_url );
    update_post_meta( $post_id, 'hatakiti_occult_published_at', $published ? $published : '' );
    update_post_meta( $post_id, 'hatakiti_occult_fetched_at', current_time( 'mysql' ) );
    update_post_meta( $post_id, 'hatakiti_occult_content_hash', $content_hash );
    update_post_meta( $post_id, 'hatakiti_occult_issue_post_id', '' );
    update_post_meta( $post_id, 'hatakiti_occult_raw_metadata', wp_json_encode( $raw_metadata, JSON_UNESCAPED_UNICODE ) );

    return 'created';
}

/**
 * MVP §8: a way to exercise source_item → article_cluster → weekly_issue
 * end-to-end even when no live feed is reachable. Fixed, obviously-fake
 * items (title-prefixed "【テストデータ】", URLs on example.com — the
 * IANA-reserved documentation domain, so nothing real is ever linked to)
 * under a dedicated, disabled occult_news_source so they never appear
 * mixed into a real RSS fetch. Re-running is safe: the fixed URLs make
 * this idempotent via the normal original_url dedup check.
 */
function hatakiti_ensure_occult_test_source() {
    $existing = get_posts( array(
        'post_type'      => 'occult_news_source',
        'post_status'    => 'any',
        'title'          => 'テストソース（ダミー・開発用）',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( $existing ) {
        return $existing[0];
    }

    $source_id = wp_insert_post( array(
        'post_type'   => 'occult_news_source',
        'post_title'  => 'テストソース（ダミー・開発用）',
        'post_status' => 'publish',
    ), true );
    if ( is_wp_error( $source_id ) ) {
        return 0;
    }

    update_post_meta( $source_id, 'hatakiti_occult_rss_url', '' );
    update_post_meta( $source_id, 'hatakiti_occult_website_url', '' );
    // Disabled — hatakiti_fetch_all_occult_sources() filters on this meta,
    // so this source is never touched by a real fetch run.
    update_post_meta( $source_id, 'hatakiti_occult_enabled', '0' );

    return $source_id;
}

function hatakiti_seed_occult_test_data() {
    $source_id = hatakiti_ensure_occult_test_source();
    if ( ! $source_id ) {
        return array( 'created' => 0, 'duplicate' => 0 );
    }
    $source_post = get_post( $source_id );

    $now = current_time( 'timestamp' );
    $dummy_items = array(
        array( 'title' => '【テストデータ】〇〇山で謎の発光現象、複数の目撃者', 'url' => 'https://example.com/test-occult-news/1', 'summary' => 'MVP動作確認用のダミーデータ。実在のニュースではない。同一事件を伝える関連記事が別ソースにもある想定。', 'days_ago' => 2 ),
        array( 'title' => '【テストデータ】〇〇山の発光現象、警察にも通報相次ぐ', 'url' => 'https://example.com/test-occult-news/2', 'summary' => 'MVP動作確認用のダミーデータ。前項と同一事件を報じる別ソースの記事という想定（クラスタ統合の確認用）。', 'days_ago' => 2 ),
        array( 'title' => '【テストデータ】山中で謎の足跡を発見、UMAの仕業か', 'url' => 'https://example.com/test-occult-news/3', 'summary' => 'MVP動作確認用のダミーデータ。実在のニュースではない。', 'days_ago' => 4 ),
        array( 'title' => '【テストデータ】江戸時代の古文書に類似の記録', 'url' => 'https://example.com/test-occult-news/4', 'summary' => 'MVP動作確認用のダミーデータ。実在のニュースではない。', 'days_ago' => 5 ),
        array( 'title' => '【テストデータ】近所の猫が急に喋り出したという噂', 'url' => 'https://example.com/test-occult-news/5', 'summary' => 'MVP動作確認用のダミーデータ。実在のニュースではない。', 'days_ago' => 1 ),
    );

    $created   = 0;
    $duplicate = 0;
    foreach ( $dummy_items as $dummy ) {
        $original_url = $dummy['url'];
        $title        = $dummy['title'];
        $content_hash = hash( 'sha256', $title );

        $existing_by_url = get_posts( array(
            'post_type'      => 'occult_news_item',
            'post_status'    => 'any',
            'meta_key'       => 'hatakiti_occult_original_url',
            'meta_value'     => $original_url,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ) );
        if ( $existing_by_url ) {
            $duplicate++;
            continue;
        }

        $published = gmdate( 'Y-m-d H:i:s', $now - ( (int) $dummy['days_ago'] * DAY_IN_SECONDS ) );

        $post_id = wp_insert_post( array(
            'post_type'    => 'occult_news_item',
            'post_title'   => $title,
            'post_content' => $dummy['summary'],
            'post_status'  => 'publish',
        ), true );
        if ( is_wp_error( $post_id ) ) {
            continue;
        }

        update_post_meta( $post_id, 'hatakiti_occult_source_post_id', $source_id );
        update_post_meta( $post_id, 'hatakiti_occult_source_name', get_the_title( $source_post ) );
        update_post_meta( $post_id, 'hatakiti_occult_original_url', $original_url );
        update_post_meta( $post_id, 'hatakiti_occult_published_at', $published );
        update_post_meta( $post_id, 'hatakiti_occult_fetched_at', current_time( 'mysql' ) );
        update_post_meta( $post_id, 'hatakiti_occult_content_hash', $content_hash );
        update_post_meta( $post_id, 'hatakiti_occult_issue_post_id', '' );
        update_post_meta( $post_id, 'hatakiti_occult_raw_metadata', wp_json_encode( array( 'test_data' => true ), JSON_UNESCAPED_UNICODE ) );

        $created++;
    }

    return array( 'created' => $created, 'duplicate' => $duplicate );
}
