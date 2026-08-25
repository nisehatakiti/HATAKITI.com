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

    $results = null;
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_occult_rss_nonce'] ) ) {
        check_admin_referer( 'hatakiti_occult_rss_fetch', 'hatakiti_occult_rss_nonce' );
        $results = hatakiti_fetch_all_occult_sources();
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

        <form method="post" style="margin-top:20px;">
            <?php wp_nonce_field( 'hatakiti_occult_rss_fetch', 'hatakiti_occult_rss_nonce' ); ?>
            <button type="submit" class="button button-primary">最新ニュースを取得</button>
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

    update_post_meta( $post_id, 'hatakiti_occult_source_post_id', $source_post->ID );
    update_post_meta( $post_id, 'hatakiti_occult_source_name', get_the_title( $source_post ) );
    update_post_meta( $post_id, 'hatakiti_occult_original_url', $original_url );
    update_post_meta( $post_id, 'hatakiti_occult_published_at', $published ? $published : '' );
    update_post_meta( $post_id, 'hatakiti_occult_fetched_at', current_time( 'mysql' ) );
    update_post_meta( $post_id, 'hatakiti_occult_content_hash', $content_hash );
    update_post_meta( $post_id, 'hatakiti_occult_issue_post_id', '' );

    return 'created';
}
