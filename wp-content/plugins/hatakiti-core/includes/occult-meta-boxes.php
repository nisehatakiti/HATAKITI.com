<?php
/**
 * Native meta boxes for occult_news_source and occult_news_item — simple
 * fixed fields, same lightweight pattern as 日本民話's identity/region
 * boxes (no dedicated-form bypass needed for these two).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_add_occult_meta_boxes() {
    add_meta_box(
        'hatakiti_occult_source_fields',
        'ソース設定',
        'hatakiti_render_occult_source_box',
        'occult_news_source',
        'normal',
        'high'
    );
    add_meta_box(
        'hatakiti_occult_news_item_fields',
        'ニュース情報',
        'hatakiti_render_occult_news_item_box',
        'occult_news_item',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'hatakiti_add_occult_meta_boxes' );

function hatakiti_render_occult_source_box( $post ) {
    wp_nonce_field( 'hatakiti_save_occult_source', 'hatakiti_occult_source_nonce' );
    $rss_url     = get_post_meta( $post->ID, 'hatakiti_occult_rss_url', true );
    $website_url = get_post_meta( $post->ID, 'hatakiti_occult_website_url', true );
    $enabled     = get_post_meta( $post->ID, 'hatakiti_occult_enabled', true );
    ?>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="hatakiti_occult_rss_url">RSS URL</label></th>
            <td><input type="url" class="large-text code" id="hatakiti_occult_rss_url" name="hatakiti_occult_rss_url" value="<?php echo esc_attr( $rss_url ); ?>" placeholder="https://example.com/feed/"></td>
        </tr>
        <tr>
            <th scope="row"><label for="hatakiti_occult_website_url">サイトURL</label></th>
            <td><input type="url" class="large-text code" id="hatakiti_occult_website_url" name="hatakiti_occult_website_url" value="<?php echo esc_attr( $website_url ); ?>" placeholder="https://example.com/"></td>
        </tr>
        <tr>
            <th scope="row">取得対象にする</th>
            <td>
                <label><input type="checkbox" name="hatakiti_occult_enabled" value="1"<?php checked( '1', $enabled ); ?>> 有効（「最新ニュースを取得」の対象にする）</label>
                <p class="description">RSSが確認できないソースはチェックを外し、無効のままにしてください。</p>
            </td>
        </tr>
    </table>
    <?php
}

function hatakiti_save_occult_source_meta( $post_id ) {
    if ( ! isset( $_POST['hatakiti_occult_source_nonce'] ) ||
        ! wp_verify_nonce( $_POST['hatakiti_occult_source_nonce'], 'hatakiti_save_occult_source' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    update_post_meta( $post_id, 'hatakiti_occult_rss_url', isset( $_POST['hatakiti_occult_rss_url'] ) ? esc_url_raw( wp_unslash( $_POST['hatakiti_occult_rss_url'] ) ) : '' );
    update_post_meta( $post_id, 'hatakiti_occult_website_url', isset( $_POST['hatakiti_occult_website_url'] ) ? esc_url_raw( wp_unslash( $_POST['hatakiti_occult_website_url'] ) ) : '' );
    update_post_meta( $post_id, 'hatakiti_occult_enabled', isset( $_POST['hatakiti_occult_enabled'] ) ? '1' : '0' );
}
add_action( 'save_post_occult_news_source', 'hatakiti_save_occult_source_meta' );

function hatakiti_render_occult_news_item_box( $post ) {
    $source_name  = get_post_meta( $post->ID, 'hatakiti_occult_source_name', true );
    $original_url = get_post_meta( $post->ID, 'hatakiti_occult_original_url', true );
    $published_at = get_post_meta( $post->ID, 'hatakiti_occult_published_at', true );
    $fetched_at   = get_post_meta( $post->ID, 'hatakiti_occult_fetched_at', true );
    $issue_id     = get_post_meta( $post->ID, 'hatakiti_occult_issue_post_id', true );
    ?>
    <table class="form-table" role="presentation">
        <tr><th scope="row">情報源</th><td><?php echo esc_html( $source_name ); ?></td></tr>
        <tr><th scope="row">元記事URL</th><td><a href="<?php echo esc_url( $original_url ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $original_url ); ?></a></td></tr>
        <tr><th scope="row">公開日時</th><td><?php echo esc_html( $published_at ); ?></td></tr>
        <tr><th scope="row">取得日時</th><td><?php echo esc_html( $fetched_at ); ?></td></tr>
        <tr>
            <th scope="row">紐付け済みの号</th>
            <td>
                <?php if ( $issue_id && get_post( $issue_id ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link( $issue_id ) ); ?>"><?php echo esc_html( get_the_title( $issue_id ) ); ?></a>
                <?php else : ?>
                    未使用
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <p class="description">タイトル・本文（RSSの概要のみ）・カテゴリは編集できます。元記事の全文はこの画面にも保存されません。</p>
    <?php
}
