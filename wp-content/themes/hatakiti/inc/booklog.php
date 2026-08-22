<?php
/**
 * 本棚 (bookshelf) — reads from Booklog rather than storing books in
 * WordPress, so HATAKITI only manages his reading list in one place.
 *
 * IMPORTANT: api.booklog.jp/v2/json/ is not an officially documented or
 * supported Booklog API. It is a leftover of Booklog's own "ブログパーツ"
 * embeddable-widget feature, which Booklog discontinued in 2019 — the
 * underlying JSON endpoint still responds (confirmed manually), but it
 * could stop working at any time without notice, and it does not return
 * author, reading status, rating, or tags — only title/cover/link.
 *
 * Every render of the bookshelf page therefore always shows the "ブクログ
 * の本棚を見る" link regardless of whether this fetch succeeds, and
 * hatakiti_get_booklog_books() fails soft (empty array) on any error so a
 * broken/changed API can never break the page — see page-bookshelf.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_get_booklog_books( $count = 24 ) {
    $user = hatakiti_get_booklog_user();
    if ( ! $user ) {
        return array();
    }

    $cache_key = 'hatakiti_booklog_' . md5( $user . '_' . $count );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    $response = wp_remote_get(
        sprintf( 'http://api.booklog.jp/v2/json/%s?count=%d', rawurlencode( $user ), (int) $count ),
        array( 'timeout' => 8 )
    );

    if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
        return array();
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $data['books'] ) || ! is_array( $data['books'] ) ) {
        return array();
    }

    $books = array();
    foreach ( $data['books'] as $book ) {
        if ( empty( $book['title'] ) || empty( $book['url'] ) ) {
            continue;
        }
        $books[] = array(
            'title' => sanitize_text_field( $book['title'] ),
            'url'   => esc_url_raw( $book['url'] ),
            'image' => isset( $book['image'] ) ? esc_url_raw( $book['image'] ) : '',
        );
    }

    // Cache even an empty result, so a temporary outage doesn't hammer the
    // remote endpoint on every page view.
    set_transient( $cache_key, $books, 6 * HOUR_IN_SECONDS );

    return $books;
}
