<?php
/**
 * Reusable template helpers.
 *
 * HATAKITI.com keeps three "ordinary post" buckets distinguished by category
 * slug: nikki (日々の所感) and engeki (演劇について). Everything else
 * (観劇記録 / 映画記録) is a dedicated custom post type registered by the
 * hatakiti-core plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Defined by the hatakiti-core plugin; guarded here so the theme still
// works (in a degraded way) if that plugin is ever inactive.
if ( ! defined( 'HATAKITI_CAT_NIKKI' ) ) {
    define( 'HATAKITI_CAT_NIKKI', 'nikki' );
}
if ( ! defined( 'HATAKITI_CAT_ENGEKI' ) ) {
    define( 'HATAKITI_CAT_ENGEKI', 'engeki' );
}

/**
 * Human-readable content-type label for a given post, used on cards.
 */
function hatakiti_content_type_label( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $type    = get_post_type( $post_id );

    if ( 'theatre_record' === $type ) {
        return '観劇記録';
    }
    if ( 'film_record' === $type ) {
        return '映画記録';
    }
    if ( 'activity_record' === $type ) {
        return '活動履歴';
    }
    if ( 'folktale' === $type ) {
        return '日本民話';
    }
    if ( 'occult_weekly' === $type ) {
        return '週刊オカルト新聞';
    }
    if ( has_category( HATAKITI_CAT_ENGEKI, $post_id ) ) {
        return '演劇について';
    }
    return '日々の所感';
}

/**
 * Whether the given post belongs to the 演劇について (theatre essay) category.
 */
function hatakiti_is_theatre_essay( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    return 'post' === get_post_type( $post_id ) && has_category( HATAKITI_CAT_ENGEKI, $post_id );
}

/**
 * Query used for the front page "latest 3" cards. Automatically picks up
 * new content across every public content type, so nothing needs to be
 * hand-curated as the site grows.
 */
function hatakiti_latest_content_query( $count = 3 ) {
    return new WP_Query( array(
        'post_type'           => array( 'post', 'theatre_record', 'film_record' ),
        'post_status'         => 'publish',
        'posts_per_page'      => $count,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ) );
}

/**
 * Renders one article/record card. Used on the front page and on archives.
 */
function hatakiti_render_card( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $type    = get_post_type( $post_id );
    $label   = hatakiti_content_type_label( $post_id );
    $permalink = get_permalink( $post_id );
    $title   = get_the_title( $post_id );

    if ( 'theatre_record' === $type ) {
        $troupe = get_post_meta( $post_id, 'hatakiti_troupe', true );
        $meta_line = $troupe ? esc_html( $troupe ) : '';
    } elseif ( 'film_record' === $type ) {
        $director = get_post_meta( $post_id, 'hatakiti_director', true );
        $meta_line = $director ? esc_html( $director ) : '';
    } elseif ( 'activity_record' === $type ) {
        $activity_date     = get_post_meta( $post_id, 'hatakiti_activity_date', true );
        $activity_date_end = get_post_meta( $post_id, 'hatakiti_activity_date_end', true );
        $range = hatakiti_format_date_range( $activity_date, $activity_date_end );
        $meta_line = $range ? esc_html( $range ) : get_the_date( 'Y.m.d', $post_id );
    } elseif ( 'folktale' === $type ) {
        $prefecture = get_post_meta( $post_id, 'hatakiti_folktale_region_prefecture', true );
        $meta_line = $prefecture ? esc_html( $prefecture ) : '';
    } elseif ( 'occult_weekly' === $type ) {
        $week_start = get_post_meta( $post_id, 'hatakiti_occult_week_start', true );
        $week_end   = get_post_meta( $post_id, 'hatakiti_occult_week_end', true );
        $range = hatakiti_format_date_range( $week_start, $week_end );
        $meta_line = $range ? esc_html( $range ) : '';
    } else {
        $meta_line = get_the_date( 'Y.m.d', $post_id );
    }
    ?>
    <article class="hk-card">
        <?php if ( has_post_thumbnail( $post_id ) ) : ?>
            <div class="hk-card-thumb">
                <a href="<?php echo esc_url( $permalink ); ?>"><?php echo get_the_post_thumbnail( $post_id, 'hatakiti-card' ); ?></a>
            </div>
        <?php endif; ?>
        <div class="hk-card-body">
            <div class="hk-card-type"><?php echo esc_html( $label ); ?></div>
            <h3 class="hk-card-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
            <div class="hk-card-excerpt"><?php echo esc_html( wp_trim_words( hatakiti_card_excerpt( $post_id, $type ), 40 ) ); ?></div>
            <?php if ( $meta_line ) : ?>
                <div class="hk-card-meta"><?php echo esc_html( $meta_line ); ?></div>
            <?php endif; ?>
        </div>
    </article>
    <?php
}

/**
 * Card excerpt text, branched per post type. folktale is special-cased:
 * its post_content may hold the raw research summary/notes (internal
 * working text — research status, dedup reasoning, "本文未確認" etc.),
 * which must never reach a public card. public_summary is generated from
 * confirmed structured fields only — see
 * hatakiti_generate_folktale_public_summary() in cpt-folktale.php.
 */
function hatakiti_card_excerpt( $post_id, $type ) {
    if ( 'folktale' === $type ) {
        $public_summary = get_post_meta( $post_id, 'hatakiti_folktale_public_summary', true );
        return $public_summary ? $public_summary : '';
    }
    return get_the_excerpt( $post_id );
}

/**
 * Public-facing phrasing for a folktale's related_records relationship
 * type (docs/12 §14) — never the record's own internal `note` text, which
 * may describe HATAKITI's data-management reasoning rather than something
 * a visitor needs to read. Unconfirmed relationships are phrased as
 * tentative, never asserted as fact.
 */
function hatakiti_folktale_relationship_label( $relationship ) {
    $labels = array(
        'same_tradition'   => '同じ伝承群に伝わる話です。',
        'regional_variant' => '地域による異伝の可能性がある伝承です。',
        'similar_theme'    => '似たテーマを持つ伝承です。',
        'same_being'       => '同じ存在が登場する伝承です。',
        'related_place'    => '関連する場所に伝わる伝承です。',
        'variant_candidate' => '同じ話の可能性がある伝承です（内容が同じかどうかはまだ確認されていません）。',
    );
    return isset( $labels[ $relationship ] ) ? $labels[ $relationship ] : '関連する伝承です。';
}

/**
 * Detects a pre-existing "文責：チャッピー" line inside post content.
 */
function hatakiti_content_has_credit_line( $content ) {
    return (bool) preg_match( '/文責\s*[:：]\s*チャッピー/u', $content );
}

/**
 * Renders the 文責：チャッピー credit badge for theatre essays.
 *
 * The credit text is required by the blueprint (docs/03-ContentModel.md §6)
 * to appear verbatim on every theatre essay. When the author's original text
 * already contains the line (all current essays do), we do not touch it —
 * this only adds a fallback badge when a future draft forgets it.
 */
function hatakiti_render_credit_badge_if_missing( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();

    if ( ! hatakiti_is_theatre_essay( $post_id ) ) {
        return;
    }

    $content = get_post_field( 'post_content', $post_id );
    if ( hatakiti_content_has_credit_line( $content ) ) {
        return;
    }

    echo '<p class="hk-credit-badge">文責：チャッピー</p>';
}

/**
 * Wraps an existing "文責：チャッピー" paragraph in the credit badge style,
 * without altering the stored text.
 */
function hatakiti_style_credit_line( $content ) {
    if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }
    if ( ! hatakiti_is_theatre_essay() ) {
        return $content;
    }
    return preg_replace(
        '/<p>\s*(文責\s*[:：]\s*チャッピー)\s*<\/p>/u',
        '<p class="hk-credit-badge">$1</p>',
        $content
    );
}
add_filter( 'the_content', 'hatakiti_style_credit_line', 20 );

/**
 * Method (観劇方法 / 鑑賞方法) badge.
 */
function hatakiti_render_method_badge( $method ) {
    if ( ! $method ) {
        return;
    }
    printf( '<span class="hk-method-badge">%s</span>', esc_html( $method ) );
}

/**
 * Formats a start/end date pair as "start ～ end", or just "start" when
 * there is no end date (or vice versa). Used for 活動履歴's 活動日.
 */
function hatakiti_format_date_range( $start, $end ) {
    if ( ! $start && ! $end ) {
        return '';
    }
    return trim( $start . ' ～ ' . $end, ' ～' );
}

/**
 * Structured info box for a 観劇記録 (theatre viewing record).
 */
function hatakiti_render_theatre_record_box( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();

    $troupe    = get_post_meta( $post_id, 'hatakiti_troupe', true );
    $direction = get_post_meta( $post_id, 'hatakiti_direction', true );
    $script    = get_post_meta( $post_id, 'hatakiti_script', true );
    $viewing   = get_post_meta( $post_id, 'hatakiti_viewing_date', true );
    $run_start = get_post_meta( $post_id, 'hatakiti_run_start', true );
    $run_end   = get_post_meta( $post_id, 'hatakiti_run_end', true );
    $venue     = get_post_meta( $post_id, 'hatakiti_venue', true );
    $method    = get_post_meta( $post_id, 'hatakiti_method', true );

    $run_label = '';
    if ( $run_start || $run_end ) {
        $run_label = trim( $run_start . ' ～ ' . $run_end, ' ～' );
    }
    ?>
    <div class="hk-record-box">
        <dl>
            <?php if ( $troupe ) : ?>
                <dt>劇団名</dt><dd><?php echo esc_html( $troupe ); ?></dd>
            <?php endif; ?>
            <?php if ( $direction ) : ?>
                <dt>演出</dt><dd><?php echo esc_html( $direction ); ?></dd>
            <?php endif; ?>
            <?php if ( $script ) : ?>
                <dt>脚本</dt><dd><?php echo esc_html( $script ); ?></dd>
            <?php endif; ?>
            <?php if ( $viewing ) : ?>
                <dt>観劇日</dt><dd><?php echo esc_html( $viewing ); ?></dd>
            <?php endif; ?>
            <?php if ( $run_label ) : ?>
                <dt>公演期間</dt><dd><?php echo esc_html( $run_label ); ?></dd>
            <?php endif; ?>
            <?php if ( $venue ) : ?>
                <dt>劇場</dt><dd><?php echo esc_html( $venue ); ?></dd>
            <?php endif; ?>
            <?php if ( $method ) : ?>
                <dt>観劇方法</dt><dd><?php hatakiti_render_method_badge( $method ); ?></dd>
            <?php endif; ?>
        </dl>
    </div>
    <?php
}

/**
 * Structured info box for a 映画記録 (film viewing record).
 */
function hatakiti_render_film_record_box( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();

    $viewing      = get_post_meta( $post_id, 'hatakiti_viewing_date', true );
    $director     = get_post_meta( $post_id, 'hatakiti_director', true );
    $screenwriter = get_post_meta( $post_id, 'hatakiti_screenwriter', true );
    $year         = get_post_meta( $post_id, 'hatakiti_release_year', true );
    $cast         = get_post_meta( $post_id, 'hatakiti_cast', true );
    $method       = get_post_meta( $post_id, 'hatakiti_method', true );
    $genres       = get_the_terms( $post_id, 'film_genre' );
    ?>
    <div class="hk-record-box">
        <dl>
            <?php if ( $director ) : ?>
                <dt>監督</dt><dd><?php echo esc_html( $director ); ?></dd>
            <?php endif; ?>
            <?php if ( $screenwriter ) : ?>
                <dt>脚本</dt><dd><?php echo esc_html( $screenwriter ); ?></dd>
            <?php endif; ?>
            <?php if ( $year ) : ?>
                <dt>公開年</dt><dd><?php echo esc_html( $year ); ?></dd>
            <?php endif; ?>
            <?php if ( $cast ) : ?>
                <dt>出演</dt><dd><?php echo esc_html( $cast ); ?></dd>
            <?php endif; ?>
            <?php if ( $viewing ) : ?>
                <dt>鑑賞日</dt><dd><?php echo esc_html( $viewing ); ?></dd>
            <?php endif; ?>
            <?php if ( $method ) : ?>
                <dt>鑑賞方法</dt><dd><?php hatakiti_render_method_badge( $method ); ?></dd>
            <?php endif; ?>
            <?php if ( $genres && ! is_wp_error( $genres ) ) : ?>
                <dt>ジャンル</dt><dd><?php echo esc_html( implode( ' / ', wp_list_pluck( $genres, 'name' ) ) ); ?></dd>
            <?php endif; ?>
        </dl>
    </div>
    <?php
}

/**
 * Section divider used between the record info box, the review body, and
 * the tag list on 観劇記録 / 映画記録 single pages.
 */
function hatakiti_render_divider() {
    echo '<hr class="hk-divider">';
}

/**
 * "HATAKITIの感想" heading, shown above the review body on 観劇記録 /
 * 映画記録 single pages so the free-form text reads as its own section
 * rather than just more form fields.
 */
function hatakiti_render_review_heading() {
    echo '<h2 class="hk-review-heading">HATAKITIの感想</h2>';
}

/**
 * Tag list for the current post.
 */
function hatakiti_render_tags( $post_id = null, $with_divider = false ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $tags    = get_the_terms( $post_id, 'post_tag' );

    if ( ! $tags || is_wp_error( $tags ) ) {
        return;
    }
    if ( $with_divider ) {
        hatakiti_render_divider();
    }
    ?>
    <div class="hk-article-tags">
        <ul class="hk-tag-list">
            <?php foreach ( $tags as $tag ) : ?>
                <li><a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>">#<?php echo esc_html( $tag->name ); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

/**
 * One row in the 観劇記録 / 映画記録 archive list.
 */
function hatakiti_render_record_list_item( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $type    = get_post_type( $post_id );

    if ( 'theatre_record' === $type ) {
        $date = get_post_meta( $post_id, 'hatakiti_viewing_date', true );
        $sub  = get_post_meta( $post_id, 'hatakiti_troupe', true );
    } elseif ( 'film_record' === $type ) {
        $date = get_post_meta( $post_id, 'hatakiti_viewing_date', true );
        $sub  = get_post_meta( $post_id, 'hatakiti_director', true );
    } elseif ( 'activity_record' === $type ) {
        $date       = hatakiti_format_date_range(
            get_post_meta( $post_id, 'hatakiti_activity_date', true ),
            get_post_meta( $post_id, 'hatakiti_activity_date_end', true )
        );
        $types      = get_the_terms( $post_id, 'activity_type' );
        $categories = get_the_terms( $post_id, 'activity_category' );
        $sub_parts  = array();
        if ( $categories && ! is_wp_error( $categories ) ) {
            $sub_parts[] = implode( ' / ', wp_list_pluck( $categories, 'name' ) );
        }
        if ( $types && ! is_wp_error( $types ) ) {
            $sub_parts[] = implode( ' / ', wp_list_pluck( $types, 'name' ) );
        }
        $sub = implode( ' ・ ', $sub_parts );
    } elseif ( 'occult_weekly' === $type ) {
        $date = get_post_meta( $post_id, 'hatakiti_occult_issue_date', true );
        $sub  = hatakiti_format_date_range(
            get_post_meta( $post_id, 'hatakiti_occult_week_start', true ),
            get_post_meta( $post_id, 'hatakiti_occult_week_end', true )
        );
    } else {
        $date = '';
        $sub  = '';
    }
    ?>
    <li>
        <div class="hk-record-date"><?php echo esc_html( $date ? $date : get_the_date( 'Y.m.d', $post_id ) ); ?></div>
        <div class="hk-record-info">
            <div class="hk-record-title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></div>
            <?php if ( $sub ) : ?>
                <div class="hk-record-sub"><?php echo esc_html( $sub ); ?></div>
            <?php endif; ?>
        </div>
    </li>
    <?php
}

/**
 * Simple pagination wrapper.
 */
function hatakiti_pagination() {
    $links = paginate_links( array(
        'prev_text' => '← 前へ',
        'next_text' => '次へ →',
        'type'      => 'array',
    ) );

    if ( ! $links ) {
        return;
    }

    echo '<nav class="hk-pagination">';
    foreach ( $links as $link ) {
        echo wp_kses_post( $link );
    }
    echo '</nav>';
}

/**
 * "Coming Soon" placeholder block. Used sparingly, only where the blueprint
 * explicitly allows an empty section to say so rather than being padded out.
 */
function hatakiti_coming_soon( $message = '' ) {
    ?>
    <div class="hk-coming-soon">
        <span class="hk-badge-soon">Coming Soon</span>
        <?php if ( $message ) : ?>
            <p><?php echo esc_html( $message ); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Decodes any of this theme's JSON-blob meta fields — 日本民話's
 * locations/characters/beings/sources/related_records/ai_processing
 * (cpt-folktale.php) and 週刊オカルト新聞's articles (occult-cpt.php)
 * all use this same "structured array stored as a JSON string" shape.
 * Always returns an array, even for missing/invalid JSON, so callers
 * never need their own is_array() guard.
 */
function hatakiti_json_meta( $post_id, $key ) {
    $raw     = get_post_meta( $post_id, $key, true );
    $decoded = json_decode( (string) $raw, true );
    return is_array( $decoded ) ? $decoded : array();
}

/**
 * One 週刊オカルト新聞 article (a merged group of one or more source
 * news items — see occult-weekly-admin-form.php). $tier only changes the
 * heading level; body/sources rendering is identical for 大見出し/主要.
 * 小記事 is rendered separately (a compact list) in single-occult_weekly.php.
 */
function hatakiti_render_occult_article( $article, $tier ) {
    ?>
    <div class="hk-record-box">
        <?php if ( 'large' === $tier ) : ?>
            <h3><?php echo esc_html( $article['headline'] ); ?></h3>
        <?php else : ?>
            <h4><?php echo esc_html( $article['headline'] ); ?></h4>
        <?php endif; ?>
        <?php if ( ! empty( $article['body'] ) ) : ?>
            <div class="hk-article-body"><?php echo wpautop( esc_html( $article['body'] ) ); ?></div>
        <?php endif; ?>
        <div class="hk-record-sub"><?php hatakiti_render_occult_sources( $article ); ?></div>
    </div>
    <?php
}

/**
 * "情報源：媒体名「元記事タイトル」" for every news item behind one
 * article — required on every article, not just a footer list
 * (docs/07-OccultWeekly.md 情報源の明示).
 */
function hatakiti_render_occult_sources( $article ) {
    $item_ids = isset( $article['news_item_ids'] ) ? (array) $article['news_item_ids'] : array();
    if ( ! $item_ids ) {
        return;
    }
    echo '情報源: ';
    $links = array();
    foreach ( $item_ids as $item_id ) {
        $name = get_post_meta( $item_id, 'hatakiti_occult_source_name', true );
        $url  = get_post_meta( $item_id, 'hatakiti_occult_original_url', true );
        $title = get_the_title( $item_id );
        if ( $url ) {
            $links[] = sprintf(
                '<a href="%s" target="_blank" rel="noopener nofollow">%s「%s」</a>',
                esc_url( $url ),
                esc_html( $name ),
                esc_html( $title )
            );
        } else {
            $links[] = esc_html( $name . '「' . $title . '」' );
        }
    }
    echo wp_kses_post( implode( ' / ', $links ) );
}
