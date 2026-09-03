<?php
/**
 * 週刊オカルト新聞 号編集フォーム — same dedicated-form pattern as
 * 観劇記録/映画記録/活動履歴 (see includes/admin-forms.php), because this
 * content type's actual content (articles) is a structured, repeating
 * thing a fixed screen suits, not freeform text.
 *
 * Workflow (指示書 §17, §19 — kept deliberately simple, no dynamic JS):
 *   1. Set 対象期間 (week_start/week_end) and save.
 *   2. Below, every occult_news_item published in that range (or already
 *      linked to this issue) appears as one row: include? / 扱い
 *      (large/medium/small) / グループ (free text — same tier + same
 *      group text on 2+ rows merges them into one article citing every
 *      one of their sources) / 並び順. Saving builds/updates
 *      `articles` JSON from this table.
 *   3. Once at least one group exists, a 本文編集 section appears below
 *      with one textarea per article to write its body text (nothing
 *      generates this automatically in this pass — 指示書 explicitly
 *      does not require AI text generation yet). Saving again keeps the
 *      grouping and stores that text.
 *
 * This two-pass shape (group, save, write text, save) was chosen over a
 * single dynamic form specifically to avoid needing custom JS for
 * something 指示書 §19 itself asks not to over-build.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const HATAKITI_OCCULT_TIERS = array(
    'large'  => '大見出し',
    'medium' => '主要記事',
    'small'  => '小記事',
);

function hatakiti_register_occult_weekly_form_page() {
    $hook = add_submenu_page(
        null,
        '号を編集',
        '号を編集',
        'edit_posts',
        'hatakiti-occult-weekly-form',
        'hatakiti_render_occult_weekly_form'
    );
    add_action( 'load-' . $hook, 'hatakiti_handle_occult_weekly_page_load' );

    if ( empty( $GLOBALS['hatakiti_form_hooks'] ) ) {
        $GLOBALS['hatakiti_form_hooks'] = array();
    }
    $GLOBALS['hatakiti_form_hooks'][] = $hook;
}
add_action( 'admin_menu', 'hatakiti_register_occult_weekly_form_page', 12 );

function hatakiti_handle_occult_weekly_page_load() {
    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['hatakiti_record_nonce'] ) ) {
        return;
    }

    $submitted_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $result       = hatakiti_handle_occult_weekly_submit( $submitted_id );

    if ( is_wp_error( $result ) ) {
        $GLOBALS['hatakiti_form_error'] = $result->get_error_message();
        return;
    }

    wp_safe_redirect( admin_url( 'admin.php?page=hatakiti-occult-weekly-form&post_id=' . $result . '&updated=1' ) );
    exit;
}

function hatakiti_handle_occult_weekly_submit( $post_id ) {
    check_admin_referer( 'hatakiti_save_occult_weekly', 'hatakiti_record_nonce' );

    $title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
    if ( '' === trim( $title ) ) {
        return new WP_Error( 'hatakiti_missing_title', 'タイトルを入力してください。' );
    }

    $week_start = isset( $_POST['hatakiti_occult_week_start'] ) ? wp_unslash( $_POST['hatakiti_occult_week_start'] ) : '';
    $week_end   = isset( $_POST['hatakiti_occult_week_end'] ) ? wp_unslash( $_POST['hatakiti_occult_week_end'] ) : '';
    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $week_start ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $week_end ) ) {
        return new WP_Error( 'hatakiti_missing_range', '対象期間（開始・終了）を入力してください。' );
    }

    $issue_id   = isset( $_POST['hatakiti_occult_issue_id'] ) ? sanitize_text_field( wp_unslash( $_POST['hatakiti_occult_issue_id'] ) ) : '';
    $issue_date_raw = isset( $_POST['hatakiti_occult_issue_date'] ) ? wp_unslash( $_POST['hatakiti_occult_issue_date'] ) : '';
    $issue_date = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $issue_date_raw ) ? $issue_date_raw : $week_end;
    $editorial_summary = isset( $_POST['hatakiti_occult_editorial_summary'] ) ? wp_kses_post( wp_unslash( $_POST['hatakiti_occult_editorial_summary'] ) ) : '';

    $status = ( isset( $_POST['hatakiti_action'] ) && 'publish' === $_POST['hatakiti_action'] ) ? 'publish' : 'draft';

    $postarr = array(
        'post_type'   => 'occult_weekly',
        'post_title'  => $title,
        'post_status' => $status,
    );
    if ( $post_id ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return new WP_Error( 'hatakiti_forbidden', 'この号を編集する権限がありません。' );
        }
        $postarr['ID'] = $post_id;
        // Only this call is allowed to actually change an existing
        // occult_weekly post's status — see the wp_insert_post_data
        // guard in admin-forms.php.
        $GLOBALS['hatakiti_occult_weekly_trusted_save'] = true;
        $result = wp_update_post( $postarr, true );
        $GLOBALS['hatakiti_occult_weekly_trusted_save'] = false;
    } else {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return new WP_Error( 'hatakiti_forbidden', '号を作成する権限がありません。' );
        }
        $result = wp_insert_post( $postarr, true );
    }
    if ( is_wp_error( $result ) ) {
        return $result;
    }
    $post_id = $result;

    update_post_meta( $post_id, 'hatakiti_occult_issue_id', $issue_id );
    update_post_meta( $post_id, 'hatakiti_occult_week_start', $week_start );
    update_post_meta( $post_id, 'hatakiti_occult_week_end', $week_end );
    update_post_meta( $post_id, 'hatakiti_occult_issue_date', $issue_date );
    // wp_slash(): update_post_meta() internally unslashes string values
    // before storing (WordPress's standard convention for data that
    // hasn't come straight from $_POST) — passing already-unslashed text
    // through unchanged causes it to be unslashed a second time, silently
    // stripping any literal backslash the text legitimately contains.
    update_post_meta( $post_id, 'hatakiti_occult_editorial_summary', wp_slash( $editorial_summary ) );

    $save_result = hatakiti_save_occult_weekly_articles( $post_id );
    if ( is_wp_error( $save_result ) ) {
        return $save_result;
    }

    return $post_id;
}

/**
 * Rebuilds this issue's `articles` JSON from the submitted news-item
 * selection table, merging rows with the same tier+group into one
 * article, then applies any submitted body-text edits, then links/
 * unlinks occult_news_item posts to this issue accordingly.
 */
function hatakiti_save_occult_weekly_articles( $post_id ) {
    $include   = isset( $_POST['include'] ) ? array_map( 'absint', (array) $_POST['include'] ) : array();
    $tier_map  = isset( $_POST['tier'] ) ? (array) $_POST['tier'] : array();
    $group_map = isset( $_POST['group'] ) ? (array) $_POST['group'] : array();
    $order_map = isset( $_POST['order'] ) ? (array) $_POST['order'] : array();
    $body_map  = isset( $_POST['group_body'] ) ? (array) $_POST['group_body'] : array();

    $existing = json_decode( (string) get_post_meta( $post_id, 'hatakiti_occult_articles_json', true ), true );
    if ( ! is_array( $existing ) ) {
        $existing = array();
    }
    $existing_by_key = array();
    // item_id -> its existing article, used as the STABLE fallback for
    // preserving body text. Matching by `key` alone silently lost every
    // AI-generated draft's body text on the first manual save: the AI
    // path keys articles "ai::N::hash" (occult-weekly-ai-edit.php), while
    // this function has always synthesized "tier::group"/"single::id"
    // keys of its own for grouping selected rows — those two schemes
    // never intersect, so `$existing_by_key[$key]` (and the body_map
    // lookup below, since the form renders textareas under the
    // ORIGINAL stored key) always missed, and body defaulted to ''.
    // news_item_ids are the one identity that's stable across both save
    // paths, so preservation is keyed on that instead.
    $item_to_existing_article = array();
    foreach ( $existing as $a ) {
        if ( ! empty( $a['key'] ) ) {
            $existing_by_key[ $a['key'] ] = $a;
        }
        foreach ( (array) ( $a['news_item_ids'] ?? array() ) as $existing_item_id ) {
            $item_to_existing_article[ $existing_item_id ] = $a;
        }
    }

    $groups = array();
    foreach ( $include as $item_id ) {
        $tier_raw = isset( $tier_map[ $item_id ] ) ? $tier_map[ $item_id ] : 'small';
        $tier     = array_key_exists( $tier_raw, HATAKITI_OCCULT_TIERS ) ? $tier_raw : 'small';
        $group    = isset( $group_map[ $item_id ] ) ? sanitize_text_field( wp_unslash( $group_map[ $item_id ] ) ) : '';
        $order    = isset( $order_map[ $item_id ] ) ? (int) $order_map[ $item_id ] : 0;

        $key = ( '' !== $group ) ? ( $tier . '::' . $group ) : ( 'single::' . $item_id );

        if ( ! isset( $groups[ $key ] ) ) {
            $prior = isset( $item_to_existing_article[ $item_id ] ) ? $item_to_existing_article[ $item_id ] : array();
            $groups[ $key ] = array(
                'key'           => $key,
                'tier'          => $tier,
                'headline'      => ( '' !== $group ) ? $group : get_the_title( $item_id ),
                'body'          => isset( $prior['body'] ) ? $prior['body'] : '',
                'order'         => $order,
                'news_item_ids' => array(),
            );
        }
        $groups[ $key ]['news_item_ids'][] = $item_id;
    }

    // Apply body-text edits submitted for this save. The textarea for a
    // group that already existed is rendered (and therefore submitted)
    // under its ORIGINAL key, which may not equal the key just computed
    // above — so if the new key has no direct match, check whether any
    // of the group's item_ids point back to an existing article and try
    // that article's key too, before giving up and keeping whatever
    // preservation default was set above.
    foreach ( $groups as $key => &$group_data ) {
        if ( isset( $body_map[ $key ] ) ) {
            $group_data['body'] = wp_kses_post( wp_unslash( $body_map[ $key ] ) );
            continue;
        }
        foreach ( $group_data['news_item_ids'] as $iid ) {
            if ( isset( $item_to_existing_article[ $iid ]['key'] ) && isset( $body_map[ $item_to_existing_article[ $iid ]['key'] ] ) ) {
                $group_data['body'] = wp_kses_post( wp_unslash( $body_map[ $item_to_existing_article[ $iid ]['key'] ] ) );
                break;
            }
        }
    }
    unset( $group_data );

    return hatakiti_finalize_occult_weekly_groups( $post_id, array_values( $groups ), $include );
}

/**
 * Save guard: never let an AI (re-)generation or a manual form submit
 * overwrite good existing article data with something empty or badly
 * degraded. Added after real data loss on drafts 539/558/565 — tracing
 * it back, the actual mechanism was a key-matching bug in
 * hatakiti_save_occult_weekly_articles() (fixed above) that silently
 * discarded body text on manual saves of AI-generated drafts; this
 * function is the backstop that makes that class of bug (and any other
 * future one with the same effect) fail safe instead of destroying data.
 *
 * @return array array('ok'=>bool, 'reason'=>string, 'existing_article_count'=>int,
 *               'existing_body_chars'=>int, 'new_article_count'=>int, 'new_body_chars'=>int)
 */
function hatakiti_validate_occult_weekly_groups_for_save( $post_id, $groups ) {
    $stats = array(
        'existing_article_count' => 0,
        'existing_body_chars'    => 0,
        'new_article_count'      => is_array( $groups ) ? count( $groups ) : 0,
        'new_body_chars'         => 0,
    );

    if ( ! is_array( $groups ) ) {
        return array_merge( $stats, array( 'ok' => false, 'reason' => '記事データが配列ではありません。' ) );
    }
    if ( count( $groups ) < 1 ) {
        return array_merge( $stats, array( 'ok' => false, 'reason' => '記事が1件もありません。' ) );
    }

    $empty_body_count = 0;
    foreach ( $groups as $g ) {
        if ( empty( $g['headline'] ) || ! isset( $g['tier'] ) || empty( $g['news_item_ids'] ) ) {
            return array_merge( $stats, array( 'ok' => false, 'reason' => '見出し・扱い・出典ニュースidのいずれかが欠落した記事があります。' ) );
        }
        $blen = mb_strlen( (string) ( $g['body'] ?? '' ) );
        $stats['new_body_chars'] += $blen;
        if ( 0 === $blen ) {
            $empty_body_count++;
        }
    }

    if ( 0 === $stats['new_body_chars'] ) {
        return array_merge( $stats, array( 'ok' => false, 'reason' => '全記事の本文が空です。' ) );
    }
    if ( $empty_body_count > 0 ) {
        return array_merge( $stats, array( 'ok' => false, 'reason' => "本文が空の記事が{$empty_body_count}件あります。" ) );
    }

    $existing = json_decode( (string) get_post_meta( $post_id, 'hatakiti_occult_articles_json', true ), true );
    if ( is_array( $existing ) ) {
        $stats['existing_article_count'] = count( $existing );
        foreach ( $existing as $a ) {
            $stats['existing_body_chars'] += mb_strlen( (string) ( $a['body'] ?? '' ) );
        }
    }

    // 既存に十分な本文があるのに、新しい結果が半分未満まで縮んでいる
    // 場合は「明らかな劣化」とみなして保存しない（記事を1〜2件だけ
    // 外すような通常の編集は、通常この比率までは落ちない）。
    if ( $stats['existing_body_chars'] > 0 && $stats['new_body_chars'] < $stats['existing_body_chars'] * 0.5 ) {
        return array_merge( $stats, array(
            'ok'     => false,
            'reason' => sprintf(
                '新しい内容（%d字）が既存の内容（%d字）より大幅に少ないため、保存を中止しました。',
                $stats['new_body_chars'],
                $stats['existing_body_chars']
            ),
        ) );
    }

    return array_merge( $stats, array( 'ok' => true, 'reason' => '' ) );
}

/**
 * Shared tail for both the manual form save and the AI-generated draft
 * path (occult-weekly-ai-edit.php): sorts articles, stores articles_json,
 * links/unlinks occult_news_item posts to this issue, and recomputes the
 * summary counts. $relevant_item_ids is the full set of item ids that
 * SHOULD end up linked to this issue once saved — anything previously
 * linked but not in this set gets unlinked.
 *
 * @return int|WP_Error $post_id on success, WP_Error (nothing written) if
 *         hatakiti_validate_occult_weekly_groups_for_save() rejects the result.
 */
function hatakiti_finalize_occult_weekly_groups( $post_id, $groups, $relevant_item_ids ) {
    $validation = hatakiti_validate_occult_weekly_groups_for_save( $post_id, $groups );
    if ( ! $validation['ok'] ) {
        error_log( sprintf(
            '[hatakiti occult_weekly save guard] post #%d BLOCKED: %s (existing: %d articles / %d body chars, new: %d articles / %d body chars)',
            $post_id,
            $validation['reason'],
            $validation['existing_article_count'],
            $validation['existing_body_chars'],
            $validation['new_article_count'],
            $validation['new_body_chars']
        ) );
        return new WP_Error( 'hatakiti_occult_save_blocked', 'AI生成結果が不完全なため保存しませんでした： ' . $validation['reason'] );
    }

    $tier_rank = array( 'large' => 0, 'medium' => 1, 'small' => 2 );
    usort( $groups, function ( $a, $b ) use ( $tier_rank ) {
        if ( $a['order'] !== $b['order'] ) {
            return $a['order'] <=> $b['order'];
        }
        return $tier_rank[ $a['tier'] ] <=> $tier_rank[ $b['tier'] ];
    } );

    // wp_slash() here is required, not optional: wp_json_encode() escapes
    // every real newline in an article body as the two literal characters
    // \ and n (valid JSON syntax) — without slashing, update_post_meta()
    // strips that backslash on the way in, leaving orphaned "n" text
    // where a paragraph break should be. This was the actual cause of a
    // bug previously (mis)diagnosed as an AI text-generation quirk and
    // patched with a narrow string-replace at the call site instead of
    // here — confirmed via a direct before/after save round-trip test,
    // and that workaround has been removed now that the real cause is
    // fixed at the source.
    update_post_meta( $post_id, 'hatakiti_occult_articles_json', wp_slash( wp_json_encode( $groups, JSON_UNESCAPED_UNICODE ) ) );

    foreach ( $relevant_item_ids as $item_id ) {
        update_post_meta( $item_id, 'hatakiti_occult_issue_post_id', $post_id );
    }

    // Un-link items that were linked to this issue but are no longer selected.
    $previously_linked = get_posts( array(
        'post_type'      => 'occult_news_item',
        'post_status'    => 'any',
        'meta_key'       => 'hatakiti_occult_issue_post_id',
        'meta_value'     => $post_id,
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ) );
    foreach ( $previously_linked as $linked_id ) {
        if ( ! in_array( $linked_id, $relevant_item_ids, true ) ) {
            update_post_meta( $linked_id, 'hatakiti_occult_issue_post_id', '' );
        }
    }

    $source_ids       = array();
    $article_count    = count( $groups );
    $main_topic_count = 0;
    foreach ( $groups as $group_data ) {
        if ( 'large' === $group_data['tier'] ) {
            $main_topic_count++;
        }
        foreach ( $group_data['news_item_ids'] as $item_id ) {
            $sid = get_post_meta( $item_id, 'hatakiti_occult_source_post_id', true );
            if ( $sid ) {
                $source_ids[ $sid ] = true;
            }
        }
    }
    update_post_meta( $post_id, 'hatakiti_occult_source_count', count( $source_ids ) );
    update_post_meta( $post_id, 'hatakiti_occult_article_count', $article_count );
    update_post_meta( $post_id, 'hatakiti_occult_main_topic_count', $main_topic_count );
    update_post_meta( $post_id, 'hatakiti_occult_generated_at', current_time( 'mysql' ) );
}

/**
 * The "candidate" news items for a period: already linked to $post_id (if
 * editing an existing issue), or unlinked and published within
 * [$week_start, $week_end]. Shared by the manual edit form and the AI
 * draft generator so both ever agree on what "this week's news" means.
 */
function hatakiti_get_occult_weekly_candidates( $week_start, $week_end, $post_id = 0 ) {
    $meta_query = array( 'relation' => 'OR' );
    if ( $post_id ) {
        $meta_query[] = array( 'key' => 'hatakiti_occult_issue_post_id', 'value' => $post_id );
    }
    $unlinked_clause = array(
        'relation' => 'AND',
        array(
            'relation' => 'OR',
            array( 'key' => 'hatakiti_occult_issue_post_id', 'value' => '', 'compare' => '=' ),
            array( 'key' => 'hatakiti_occult_issue_post_id', 'compare' => 'NOT EXISTS' ),
        ),
    );
    if ( $week_start && $week_end ) {
        $unlinked_clause[] = array(
            'key'     => 'hatakiti_occult_published_at',
            'value'   => array( $week_start . ' 00:00:00', $week_end . ' 23:59:59' ),
            'compare' => 'BETWEEN',
            'type'    => 'DATETIME',
        );
    }
    $meta_query[] = $unlinked_clause;

    return get_posts( array(
        'post_type'      => 'occult_news_item',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'meta_query'     => $meta_query,
        'orderby'        => 'meta_value',
        'meta_key'       => 'hatakiti_occult_published_at',
        'order'          => 'DESC',
    ) );
}

function hatakiti_render_occult_weekly_form() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '権限がありません。' );
    }

    $error   = $GLOBALS['hatakiti_form_error'];
    $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
    $is_edit = $post_id > 0;

    $title      = '';
    $issue_id   = '';
    $week_start = '';
    $week_end   = '';
    $issue_date = '';
    $editorial_summary = '';
    $articles   = array();

    if ( $is_edit ) {
        $post = get_post( $post_id );
        if ( ! $post || 'occult_weekly' !== $post->post_type ) {
            wp_die( '指定された号が見つかりません。' );
        }
        $title      = $post->post_title;
        $issue_id   = get_post_meta( $post_id, 'hatakiti_occult_issue_id', true );
        $week_start = get_post_meta( $post_id, 'hatakiti_occult_week_start', true );
        $week_end   = get_post_meta( $post_id, 'hatakiti_occult_week_end', true );
        $issue_date = get_post_meta( $post_id, 'hatakiti_occult_issue_date', true );
        $editorial_summary = get_post_meta( $post_id, 'hatakiti_occult_editorial_summary', true );
        $articles   = json_decode( (string) get_post_meta( $post_id, 'hatakiti_occult_articles_json', true ), true );
        if ( ! is_array( $articles ) ) {
            $articles = array();
        }
    }

    if ( $error && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
        $title      = isset( $_POST['post_title'] ) ? wp_unslash( $_POST['post_title'] ) : $title;
        $issue_id   = isset( $_POST['hatakiti_occult_issue_id'] ) ? wp_unslash( $_POST['hatakiti_occult_issue_id'] ) : $issue_id;
        $week_start = isset( $_POST['hatakiti_occult_week_start'] ) ? wp_unslash( $_POST['hatakiti_occult_week_start'] ) : $week_start;
        $week_end   = isset( $_POST['hatakiti_occult_week_end'] ) ? wp_unslash( $_POST['hatakiti_occult_week_end'] ) : $week_end;
        $issue_date = isset( $_POST['hatakiti_occult_issue_date'] ) ? wp_unslash( $_POST['hatakiti_occult_issue_date'] ) : $issue_date;
        $editorial_summary = isset( $_POST['hatakiti_occult_editorial_summary'] ) ? wp_unslash( $_POST['hatakiti_occult_editorial_summary'] ) : $editorial_summary;
    }

    // Map item_id -> its current group, so the selection table can be
    // pre-filled with each item's existing tier/group on edit.
    $item_to_group = array();
    foreach ( $articles as $article ) {
        foreach ( (array) $article['news_item_ids'] as $item_id ) {
            $item_to_group[ $item_id ] = $article;
        }
    }

    // Candidates: already linked to this issue, OR unlinked and (if a
    // range is set) published within it.
    $meta_query = array( 'relation' => 'OR' );
    if ( $post_id ) {
        $meta_query[] = array( 'key' => 'hatakiti_occult_issue_post_id', 'value' => $post_id );
    }
    $unlinked_clause = array(
        'relation' => 'AND',
        array(
            'relation' => 'OR',
            array( 'key' => 'hatakiti_occult_issue_post_id', 'value' => '', 'compare' => '=' ),
            array( 'key' => 'hatakiti_occult_issue_post_id', 'compare' => 'NOT EXISTS' ),
        ),
    );
    if ( $week_start && $week_end ) {
        $unlinked_clause[] = array(
            'key'     => 'hatakiti_occult_published_at',
            'value'   => array( $week_start . ' 00:00:00', $week_end . ' 23:59:59' ),
            'compare' => 'BETWEEN',
            'type'    => 'DATETIME',
        );
    }
    $meta_query[] = $unlinked_clause;

    $candidates = get_posts( array(
        'post_type'      => 'occult_news_item',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'meta_query'     => $meta_query,
        'orderby'        => 'meta_value',
        'meta_key'       => 'hatakiti_occult_published_at',
        'order'          => 'DESC',
    ) );
    ?>
    <div class="wrap hatakiti-record-form">
        <h1><?php echo $is_edit ? '号を編集' : '号を追加'; ?></h1>
        <p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=occult_weekly' ) ); ?>">&larr; 号一覧に戻る</a></p>

        <?php if ( $error ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
        <?php elseif ( isset( $_GET['updated'] ) ) : ?>
            <div class="notice notice-success"><p>保存しました。</p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'hatakiti_save_occult_weekly', 'hatakiti_record_nonce' ); ?>
            <?php if ( $is_edit ) : ?>
                <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
            <?php endif; ?>

            <table class="form-table" role="presentation"><tbody>
                <?php
                hatakiti_form_text_row( 'タイトル', 'post_title', $title, '例：週刊オカルト新聞 2026年9月第1週', true );
                hatakiti_form_text_row( 'issue_id', 'hatakiti_occult_issue_id', $issue_id, '例：occult-2026-09-01' );
                hatakiti_form_date_row( '対象期間（開始）', 'hatakiti_occult_week_start', $week_start );
                hatakiti_form_date_row( '対象期間（終了）', 'hatakiti_occult_week_end', $week_end );
                hatakiti_form_date_row( '発行日', 'hatakiti_occult_issue_date', $issue_date );
                ?>
                <tr>
                    <th scope="row"><label for="hatakiti_occult_editorial_summary">編集後記／編集サマリー</label></th>
                    <td>
                        <textarea id="hatakiti_occult_editorial_summary" name="hatakiti_occult_editorial_summary" rows="4" class="large-text"><?php echo esc_textarea( $editorial_summary ); ?></textarea>
                        <p class="description">この号全体についての一言（今週の傾向、編集後記など）。任意項目。</p>
                    </td>
                </tr>
            </tbody></table>

            <?php if ( $is_edit ) : ?>
                <p class="description">
                    集計: ソース数 <?php echo (int) get_post_meta( $post_id, 'hatakiti_occult_source_count', true ); ?> /
                    記事数 <?php echo (int) get_post_meta( $post_id, 'hatakiti_occult_article_count', true ); ?> /
                    大見出し数 <?php echo (int) get_post_meta( $post_id, 'hatakiti_occult_main_topic_count', true ); ?>
                </p>
            <?php endif; ?>

            <h2>ニュースを選択</h2>
            <p class="description">対象期間内の未使用ニュース、およびこの号に紐付け済みのニュースが表示されます。同じ「扱い」＋同じ「グループ」を指定した行は1つの記事にまとめられます（複数ソースの統合）。グループを空欄にすると、そのニュース単独の記事になります。</p>
            <?php if ( $candidates ) : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>含める</th>
                            <th>扱い</th>
                            <th>グループ</th>
                            <th>並び順</th>
                            <th>ソース</th>
                            <th>元記事</th>
                            <th>公開日</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $candidates as $candidate ) : ?>
                            <?php
                            $cid = $candidate->ID;
                            $current_group = isset( $item_to_group[ $cid ] ) ? $item_to_group[ $cid ] : null;
                            $checked = ( isset( $item_to_group[ $cid ] ) || get_post_meta( $cid, 'hatakiti_occult_issue_post_id', true ) === (string) $post_id ) && $post_id;
                            $cur_tier  = $current_group ? $current_group['tier'] : 'small';
                            $cur_group_label = $current_group && strpos( $current_group['key'], 'single::' ) !== 0 ? $current_group['headline'] : '';
                            $cur_order = $current_group ? (int) $current_group['order'] : 0;
                            ?>
                            <tr>
                                <td><input type="checkbox" name="include[]" value="<?php echo esc_attr( $cid ); ?>"<?php checked( $checked ); ?>></td>
                                <td>
                                    <select name="tier[<?php echo esc_attr( $cid ); ?>]">
                                        <?php foreach ( HATAKITI_OCCULT_TIERS as $tier_key => $tier_label ) : ?>
                                            <option value="<?php echo esc_attr( $tier_key ); ?>"<?php selected( $cur_tier, $tier_key ); ?>><?php echo esc_html( $tier_label ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" class="regular-text" name="group[<?php echo esc_attr( $cid ); ?>]" value="<?php echo esc_attr( $cur_group_label ); ?>" placeholder="単独記事なら空欄"></td>
                                <td><input type="number" class="small-text" name="order[<?php echo esc_attr( $cid ); ?>]" value="<?php echo esc_attr( $cur_order ); ?>"></td>
                                <td><?php echo esc_html( get_post_meta( $cid, 'hatakiti_occult_source_name', true ) ); ?></td>
                                <td><a href="<?php echo esc_url( get_post_meta( $cid, 'hatakiti_occult_original_url', true ) ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( get_the_title( $cid ) ); ?></a></td>
                                <td><?php echo esc_html( get_post_meta( $cid, 'hatakiti_occult_published_at', true ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="hk-card-excerpt">対象期間内の未使用ニュースがありません。先に「RSS取得」でニュースを取得するか、対象期間を保存してから再度開いてください。</p>
            <?php endif; ?>

            <?php if ( $articles ) : ?>
                <h2>記事本文を編集</h2>
                <p class="description">直前の保存で確定したグループごとに、新聞本文を入力してください（自動生成はまだ行われません — 手動またはChatGPT等で作成した文章を貼り付けてください）。</p>
                <?php foreach ( $articles as $article ) : ?>
                    <div class="hk-record-box" style="margin-bottom:16px;">
                        <p><strong><?php echo esc_html( HATAKITI_OCCULT_TIERS[ $article['tier'] ] ?? $article['tier'] ); ?>：<?php echo esc_html( $article['headline'] ); ?></strong></p>
                        <p class="description">
                            情報源:
                            <?php
                            $names = array_map( function ( $iid ) {
                                return get_post_meta( $iid, 'hatakiti_occult_source_name', true ) . '「' . get_the_title( $iid ) . '」';
                            }, $article['news_item_ids'] );
                            echo esc_html( implode( ' / ', $names ) );
                            ?>
                        </p>
                        <textarea name="group_body[<?php echo esc_attr( $article['key'] ); ?>]" rows="6" class="large-text"><?php echo esc_textarea( $article['body'] ); ?></textarea>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <p class="hatakiti-form-actions">
                <button type="submit" name="hatakiti_action" value="draft" class="button button-secondary">下書き保存</button>
                <button type="submit" name="hatakiti_action" value="publish" class="button button-primary">公開</button>
            </p>
        </form>
    </div>
    <?php
}
