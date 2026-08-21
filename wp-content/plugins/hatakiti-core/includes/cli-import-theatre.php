<?php
/**
 * WP-CLI helper to import the theatre essays already written in
 * content/theatre/*.md as WordPress drafts.
 *
 * This deliberately only imports as DRAFT. Publish date/scheduling
 * (docs/03-ContentModel.md "サイトそのものの実装と記事の公開日時は分離")
 * is left entirely to HATAKITI, set per-article in wp-admin using
 * WordPress's normal 予約投稿 (schedule) feature.
 *
 * Article text is preserved as written, including the required
 * "文責：チャッピー" line. The only transformation applied is turning
 * Markdown syntax (heading, ```text fenced blocks, **bold**, > blockquote)
 * into the equivalent HTML so it renders correctly — no wording is added,
 * removed, or reworded.
 *
 * Usage:
 *   wp hatakiti import-theatre-essays --dir=/absolute/path/to/content/theatre
 *   wp hatakiti import-theatre-essays --dry-run
 *
 * Note: the option is --dir, not --path — wp-cli reserves --path as a
 * global flag for the WordPress install location, and reusing it here
 * would collide with that.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}

class Hatakiti_CLI_Import {

    /**
     * Import theatre essay markdown files as draft posts.
     *
     * ## OPTIONS
     *
     * [--dir=<path>]
     * : Directory containing the .md essay files. Defaults to the
     *   content/theatre folder alongside wp-content (this repo's layout).
     *
     * [--dry-run]
     * : Parse and report without creating any posts.
     *
     * @when after_wp_load
     */
    public function import_theatre_essays( $args, $assoc_args ) {
        $path = isset( $assoc_args['dir'] )
            ? untrailingslashit( $assoc_args['dir'] )
            : WP_CONTENT_DIR . '/../content/theatre';

        $dry_run = isset( $assoc_args['dry-run'] );

        if ( ! is_dir( $path ) ) {
            WP_CLI::error( "ディレクトリが見つかりません: {$path}" );
            return;
        }

        $files = glob( $path . '/*.md' );
        if ( empty( $files ) ) {
            WP_CLI::warning( "Markdownファイルが見つかりません: {$path}" );
            return;
        }

        sort( $files );

        $category = get_term_by( 'slug', HATAKITI_CAT_ENGEKI, 'category' );
        if ( ! $category && ! $dry_run ) {
            $inserted = wp_insert_term( '演劇について', 'category', array( 'slug' => HATAKITI_CAT_ENGEKI ) );
            if ( ! is_wp_error( $inserted ) ) {
                $category = get_term( $inserted['term_id'], 'category' );
            }
        }

        foreach ( $files as $file ) {
            $raw = file_get_contents( $file );
            list( $title, $body_html ) = $this->parse_markdown( $raw );

            WP_CLI::log( basename( $file ) . ' → ' . $title );

            if ( $dry_run ) {
                continue;
            }

            $existing = get_posts( array(
                'post_type'      => 'post',
                'post_status'    => 'any',
                'title'          => $title,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ) );
            if ( ! empty( $existing ) ) {
                WP_CLI::log( "  既に存在するためスキップ: #{$existing[0]}" );
                continue;
            }

            $postarr = array(
                'post_type'    => 'post',
                'post_title'   => $title,
                'post_content' => $body_html,
                'post_status'  => 'draft',
            );
            if ( $category && ! is_wp_error( $category ) ) {
                $postarr['post_category'] = array( $category->term_id );
            }

            $post_id = wp_insert_post( $postarr, true );

            if ( is_wp_error( $post_id ) ) {
                WP_CLI::warning( '  失敗: ' . $post_id->get_error_message() );
            } else {
                WP_CLI::success( "  下書きを作成しました: #{$post_id}" );
            }
        }

        WP_CLI::log( "\n下書きの公開日時は wp-admin から個別に設定してください（予約投稿可）。" );
    }

    /**
     * Very small Markdown → HTML conversion, limited to what these essays
     * actually use: an H1 title line, ```text fenced blocks, **bold**,
     * and "> " blockquotes. Plain paragraphs are left to WordPress's
     * normal wpautop handling.
     */
    private function parse_markdown( $raw ) {
        $lines = preg_split( '/\r\n|\r|\n/', $raw );
        $title = '';

        if ( isset( $lines[0] ) && preg_match( '/^#\s+(.+)$/u', trim( $lines[0] ), $m ) ) {
            $title = trim( $m[1] );
            array_shift( $lines );
        }

        $body = implode( "\n", $lines );
        $body = trim( $body );

        // Fenced ```text blocks → <pre>.
        $body = preg_replace_callback( '/```[a-z]*\n(.*?)```/su', function ( $m ) {
            return '<pre>' . esc_html( trim( $m[1], "\n" ) ) . '</pre>';
        }, $body );

        // Blockquote lines (> ...) → <blockquote><p>...</p></blockquote>.
        $body = preg_replace_callback( '/(^>.*(?:\n>.*)*)/mu', function ( $m ) {
            $quoted = preg_replace( '/^>\s?/m', '', $m[1] );
            return '<blockquote><p>' . trim( $quoted ) . '</p></blockquote>';
        }, $body );

        // **bold** → <strong>.
        $body = preg_replace( '/\*\*(.+?)\*\*/su', '<strong>$1</strong>', $body );

        return array( $title, $body );
    }
}

WP_CLI::add_command( 'hatakiti', 'Hatakiti_CLI_Import' );
