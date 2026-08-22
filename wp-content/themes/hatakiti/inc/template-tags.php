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
        $activity_date = get_post_meta( $post_id, 'hatakiti_activity_date', true );
        $meta_line = $activity_date ? esc_html( $activity_date ) : get_the_date( 'Y.m.d', $post_id );
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
            <div class="hk-card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 40 ) ); ?></div>
            <?php if ( $meta_line ) : ?>
                <div class="hk-card-meta"><?php echo esc_html( $meta_line ); ?></div>
            <?php endif; ?>
        </div>
    </article>
    <?php
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
 * Structured info box for a 観劇記録 (theatre viewing record).
 */
function hatakiti_render_theatre_record_box( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();

    $troupe    = get_post_meta( $post_id, 'hatakiti_troupe', true );
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
    $viewing = get_post_meta( $post_id, 'hatakiti_viewing_date', true );

    if ( 'theatre_record' === $type ) {
        $sub = get_post_meta( $post_id, 'hatakiti_troupe', true );
    } else {
        $sub = get_post_meta( $post_id, 'hatakiti_director', true );
    }
    ?>
    <li>
        <div class="hk-record-date"><?php echo esc_html( $viewing ? $viewing : get_the_date( 'Y.m.d', $post_id ) ); ?></div>
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
