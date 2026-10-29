<?php
/**
 * Automatically link textbook terminology to HATAKITIpedia entries.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Read canonical term names and slugs from the HATAKITIpedia index template.
 *
 * The index remains the source of truth so new glossary entries are picked up
 * without maintaining a second, drifting dictionary.
 */
function hatakiti_hakatipedia_get_autolink_terms() {
    static $terms = null;
    if ( null !== $terms ) {
        return $terms;
    }

    $terms = array();
    $path  = get_template_directory() . '/page-theatre-glossary.php';
    if ( ! is_readable( $path ) ) {
        return $terms;
    }

    $source = file_get_contents( $path );
    if ( ! is_string( $source ) ) {
        return $terms;
    }

    if ( preg_match_all( "/'([^']+)'\\s*=>\\s*array\\('slug'\\s*=>\\s*'([^']+)'/u", $source, $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $match ) {
            $name = trim( $match[1] );
            $slug = trim( $match[2] );
            if ( '' === $name || '' === $slug || 0 !== strpos( $slug, 'detail-' ) ) {
                continue;
            }
            // Prefer the longest spelling where terms overlap (e.g. 舞台 / 舞台袖).
            $terms[ $name ] = $slug;
        }
    }

    uksort(
        $terms,
        static function ( $a, $b ) {
            $length_a = function_exists( 'mb_strlen' ) ? mb_strlen( $a, 'UTF-8' ) : strlen( $a );
            $length_b = function_exists( 'mb_strlen' ) ? mb_strlen( $b, 'UTF-8' ) : strlen( $b );
            return $length_b <=> $length_a;
        }
    );

    return $terms;
}

/**
 * Link terms in eligible text nodes only; never rewrite attributes or existing links.
 */
/**
 * Reject matches that are only a fragment of a Japanese word.
 *
 * Kanji-to-kanji boundaries usually indicate that a shorter glossary term was
 * found inside a compound (人間 -> 間, 劇場 -> 場). A kanji term followed by
 * kana is accepted only when the kana is a common particle, preventing links
 * such as 歌 inside 歌う while preserving links such as 演劇の.
 */
function hatakiti_hakatipedia_is_partial_word_match( $text, $byte_offset, $term ) {
    $before_text = substr( $text, 0, $byte_offset );
    $after_text  = substr( $text, $byte_offset + strlen( $term ) );

    $before_chars = preg_split( '//u', $before_text, -1, PREG_SPLIT_NO_EMPTY );
    $term_chars   = preg_split( '//u', $term, -1, PREG_SPLIT_NO_EMPTY );
    $after_chars  = preg_split( '//u', $after_text, -1, PREG_SPLIT_NO_EMPTY );

    if ( empty( $term_chars ) ) {
        return true;
    }

    $first = $term_chars[0];
    $last  = $term_chars[ count( $term_chars ) - 1 ];
    $prev  = empty( $before_chars ) ? '' : $before_chars[ count( $before_chars ) - 1 ];
    $next  = empty( $after_chars ) ? '' : $after_chars[0];

    $is_kanji = static function ( $char ) {
        return '' !== $char && 1 === preg_match( '/^[\\p{Han}々〆ヵヶ]$/u', $char );
    };
    $is_kana = static function ( $char ) {
        return '' !== $char && 1 === preg_match( '/^[\\p{Hiragana}\\p{Katakana}ー]$/u', $char );
    };

    if ( $is_kanji( $first ) && $is_kanji( $prev ) ) {
        return true;
    }
    if ( $is_kanji( $last ) && $is_kanji( $next ) ) {
        return true;
    }

    if ( $is_kanji( $last ) && $is_kana( $next ) ) {
        $particles = preg_split( '//u', 'はがをにでとへもやのねよかぞさ', -1, PREG_SPLIT_NO_EMPTY );
        if ( ! in_array( $next, $particles, true ) ) {
            return true;
        }
    }

    return false;
}

function hatakiti_hakatipedia_linkify_textbook_html( $html ) {
    if ( ! is_string( $html ) || false === strpos( $html, '<main' ) ) {
        return $html;
    }

    $terms = hatakiti_hakatipedia_get_autolink_terms();
    if ( empty( $terms ) ) {
        return $html;
    }

    $alternatives = array_map(
        static function ( $term ) {
            return preg_quote( $term, '/' );
        },
        array_keys( $terms )
    );
    $pattern = '/(' . implode( '|', $alternatives ) . ')/u';

    $tokens       = preg_split( '/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
    $stack        = array();
    $main_depth   = 0;
    $linked_html  = '';
    $text_context = array( 'p', 'li', 'td', 'th', 'blockquote', 'figcaption' );
    $blocked      = array( 'a', 'script', 'style', 'code', 'pre', 'textarea', 'button', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'nav' );

    foreach ( $tokens as $token ) {
        if ( '' === $token ) {
            continue;
        }

        if ( '<' === $token[0] ) {
            if ( preg_match( '/^<\\s*\\/?\\s*([a-zA-Z0-9:-]+)/', $token, $tag_match ) ) {
                $tag = strtolower( $tag_match[1] );
                $closing = preg_match( '/^<\\s*\\//', $token );
                $void = preg_match( '/\\/\\s*>$/', $token ) || in_array( $tag, array( 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr' ), true );

                if ( $closing ) {
                    if ( 'main' === $tag && $main_depth > 0 ) {
                        $main_depth--;
                    }
                    for ( $i = count( $stack ) - 1; $i >= 0; $i-- ) {
                        $popped = array_pop( $stack );
                        if ( $popped === $tag ) {
                            break;
                        }
                    }
                } else {
                    if ( 'main' === $tag ) {
                        $main_depth++;
                    }
                    if ( ! $void ) {
                        $stack[] = $tag;
                    }
                }
            }
            $linked_html .= $token;
            continue;
        }

        if ( $main_depth < 1 ) {
            $linked_html .= $token;
            continue;
        }

        $inside_blocked = (bool) array_intersect( $blocked, $stack );
        $inside_text    = (bool) array_intersect( $text_context, $stack );
        if ( $inside_blocked || ! $inside_text ) {
            $linked_html .= $token;
            continue;
        }

        $match_count = 0;
        $linked_html .= preg_replace_callback(
            $pattern,
            static function ( $match ) use ( $terms, $token ) {
                $name   = $match[1][0];
                $offset = $match[1][1];
                if ( hatakiti_hakatipedia_is_partial_word_match( $token, $offset, $name ) ) {
                    return $name;
                }

                $slug = $terms[ $name ] ?? '';
                if ( '' === $slug ) {
                    return $name;
                }
                $url = home_url( '/theatre-textbook/hakatipedia/' . rawurlencode( $slug ) . '/' );
                return '<a class="hk-hakatipedia-autolink" href="' . esc_url( $url ) . '" title="' . esc_attr( $name . '｜HATAKITIpedia' ) . '">' . $name . '</a>';
            },
            $token,
            -1,
            $match_count,
            PREG_OFFSET_CAPTURE
        );
    }

    return $linked_html;
}

/**
 * Start buffering only on textbook pages; the callback edits text nodes inside main.
 */
function hatakiti_hakatipedia_start_textbook_autolinks() {
    $path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
    $base = trim( (string) parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
    if ( $base && 0 === strpos( $path, $base . '/' ) ) {
        $path = substr( $path, strlen( $base ) + 1 );
    }

    if ( 0 !== strpos( $path, 'theatre-textbook/' ) && 'theatre-textbook' !== $path ) {
        return;
    }
    // HATAKITIpedia already has its own term and related-item links.
    if ( 0 === strpos( $path, 'theatre-textbook/hakatipedia/' ) || 0 === strpos( $path, 'theatre-textbook/glossary/' ) ) {
        return;
    }

    ob_start( 'hatakiti_hakatipedia_linkify_textbook_html' );
}
add_action( 'template_redirect', 'hatakiti_hakatipedia_start_textbook_autolinks', 0 );
