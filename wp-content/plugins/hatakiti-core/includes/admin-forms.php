<?php
/**
 * Dedicated data-entry screens for 観劇記録 / 映画記録.
 *
 * HATAKITI should never see WordPress's native title field or block
 * editor for these two record types. Instead:
 *
 *   - "観劇記録を追加" / "映画記録を追加" (and every "Edit" link in the
 *     list table) open one purpose-built form with every field laid out
 *     in a fixed order, top to bottom.
 *   - Any stray navigation to the native post-new.php / post.php screens
 *     for these post types is transparently redirected to that same form.
 *   - Saving still goes through the normal theatre_record / film_record
 *     post + post meta + taxonomies underneath (see includes/meta-boxes.php
 *     for the field definitions) — only the admin UI is custom.
 *
 * The public single-theatre_record.php / single-film_record.php templates
 * are unrelated to this file and are not changed by it.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Belt-and-braces: never let the block editor load for these post types,
 * even if 'editor' support were ever re-added by mistake.
 */
add_filter( 'use_block_editor_for_post_type', function ( $use_block_editor, $post_type ) {
    if ( in_array( $post_type, array( 'theatre_record', 'film_record', 'activity_record', 'occult_weekly' ), true ) ) {
        return false;
    }
    return $use_block_editor;
}, 10, 2 );

/**
 * Hidden admin pages (no parent menu — reached only via redirect or a
 * direct edit link, never a second visible menu entry).
 */
function hatakiti_register_record_form_pages() {
    $theatre_hook = add_submenu_page(
        null,
        '観劇記録フォーム',
        '観劇記録フォーム',
        'edit_posts',
        'hatakiti-theatre-record-form',
        'hatakiti_render_theatre_record_form'
    );
    $film_hook = add_submenu_page(
        null,
        '映画記録フォーム',
        '映画記録フォーム',
        'edit_posts',
        'hatakiti-film-record-form',
        'hatakiti_render_film_record_form'
    );

    // Submissions are processed on load-{hook}, which runs before
    // wp-admin's header is output — a redirect from inside the page
    // *callback* itself would be too late (headers already sent).
    add_action( 'load-' . $theatre_hook, 'hatakiti_handle_theatre_record_page_load' );
    add_action( 'load-' . $film_hook, 'hatakiti_handle_film_record_page_load' );

    // Recorded so the admin-only stylesheet can target exactly these two
    // screens without guessing WordPress's hook-suffix naming convention.
    $GLOBALS['hatakiti_form_hooks'] = array( $theatre_hook, $film_hook );
}
add_action( 'admin_menu', 'hatakiti_register_record_form_pages' );

/**
 * Holds a save error message across the load-{hook} → render handoff, when
 * a submission fails validation and the page needs to redisplay it.
 */
$GLOBALS['hatakiti_form_error'] = null;

function hatakiti_handle_theatre_record_page_load() {
    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['hatakiti_record_nonce'] ) ) {
        return;
    }

    $submitted_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $result       = hatakiti_handle_theatre_record_submit( $submitted_id );

    if ( is_wp_error( $result ) ) {
        $GLOBALS['hatakiti_form_error'] = $result->get_error_message();
        return;
    }

    wp_safe_redirect( admin_url( 'admin.php?page=hatakiti-theatre-record-form&post_id=' . $result . '&updated=1' ) );
    exit;
}

function hatakiti_handle_film_record_page_load() {
    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['hatakiti_record_nonce'] ) ) {
        return;
    }

    $submitted_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $result       = hatakiti_handle_film_record_submit( $submitted_id );

    if ( is_wp_error( $result ) ) {
        $GLOBALS['hatakiti_form_error'] = $result->get_error_message();
        return;
    }

    wp_safe_redirect( admin_url( 'admin.php?page=hatakiti-film-record-form&post_id=' . $result . '&updated=1' ) );
    exit;
}

/**
 * Catch any request for the native "Add New" screen for these post types
 * and bounce it to our own form instead.
 */
function hatakiti_redirect_record_post_new() {
    $screen_page = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';

    $target = hatakiti_record_form_page_for_type( $screen_page );
    if ( ! $target ) {
        return;
    }

    wp_safe_redirect( admin_url( 'admin.php?page=' . $target ) );
    exit;
}
add_action( 'load-post-new.php', 'hatakiti_redirect_record_post_new' );

/**
 * Catch any request for the native "Edit" screen for one of these records
 * (list-table title link, "Edit" row action, admin bar, bookmarks, …) and
 * bounce it to our own form. Trash/restore/other post.php actions are left
 * alone so those keep working normally.
 */
function hatakiti_redirect_record_post_edit() {
    $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'edit';
    if ( 'edit' !== $action ) {
        return;
    }

    $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
    if ( ! $post_id ) {
        return;
    }

    $target = hatakiti_record_form_page_for_type( get_post_type( $post_id ) );
    if ( ! $target ) {
        return;
    }

    wp_safe_redirect( admin_url( 'admin.php?page=' . $target . '&post_id=' . $post_id ) );
    exit;
}
add_action( 'load-post.php', 'hatakiti_redirect_record_post_edit' );

function hatakiti_record_form_page_for_type( $post_type ) {
    if ( 'theatre_record' === $post_type ) {
        return 'hatakiti-theatre-record-form';
    }
    if ( 'film_record' === $post_type ) {
        return 'hatakiti-film-record-form';
    }
    if ( 'activity_record' === $post_type ) {
        return 'hatakiti-activity-record-form';
    }
    if ( 'occult_weekly' === $post_type ) {
        return 'hatakiti-occult-weekly-form';
    }
    return '';
}

/**
 * Point every "edit" link WordPress generates for these post types (list
 * table title/row actions, admin bar, etc.) straight at our form, so users
 * never even bounce through the native screen first.
 */
add_filter( 'get_edit_post_link', function ( $link, $post_id ) {
    $target = hatakiti_record_form_page_for_type( get_post_type( $post_id ) );
    if ( ! $target ) {
        return $link;
    }
    return admin_url( 'admin.php?page=' . $target . '&post_id=' . $post_id );
}, 10, 2 );

/**
 * Quick Edit only understands core fields, not this form's custom data —
 * remove it for these two post types so it can't be used by mistake.
 *
 * For occult_weekly specifically, also remove the native "ゴミ箱へ移動"
 * row action. articles_json / editorial_summary only ever change through
 * hatakiti_finalize_occult_weekly_groups() (the custom form / AI-generate
 * path); the native list table's Trash link and Bulk Edit's status
 * dropdown bypass that entirely and were the likely cause of occult_weekly
 * drafts being trashed/published without anyone deliberately using the
 * custom form (found while investigating repeated unexplained status
 * changes on draft issues 539/547/558/565 — see also the bulk_actions
 * filter below).
 */
add_filter( 'post_row_actions', function ( $actions, $post ) {
    if ( in_array( $post->post_type, array( 'theatre_record', 'film_record', 'activity_record', 'occult_weekly' ), true ) ) {
        unset( $actions['inline hide-if-no-js'] );
    }
    if ( 'occult_weekly' === $post->post_type ) {
        unset( $actions['trash'] );
    }
    return $actions;
}, 10, 2 );

/**
 * Bulk Edit's status dropdown ("公開済み" / "ゴミ箱" etc.) can change
 * occult_weekly status for many posts at once without ever touching the
 * custom form. Remove Edit (bulk) and Trash from the bulk actions menu on
 * the occult_weekly list table; "表示" of individual issues is unaffected.
 */
add_filter( 'bulk_actions-edit-occult_weekly', function ( $actions ) {
    unset( $actions['edit'] );
    unset( $actions['trash'] );
    return $actions;
} );

/**
 * Admin-only stylesheet for the two form screens.
 */
function hatakiti_admin_form_assets( $hook ) {
    if ( empty( $GLOBALS['hatakiti_form_hooks'] ) || ! in_array( $hook, $GLOBALS['hatakiti_form_hooks'], true ) ) {
        return;
    }
    wp_enqueue_style(
        'hatakiti-admin-forms',
        plugins_url( 'assets/admin-forms.css', HATAKITI_CORE_DIR . 'hatakiti-core.php' ),
        array(),
        '1.0.0'
    );
}
add_action( 'admin_enqueue_scripts', 'hatakiti_admin_form_assets' );

/**
 * Shared field-row rendering.
 */
function hatakiti_form_text_row( $label, $name, $value, $placeholder = '', $required = false ) {
    printf(
        '<tr><th scope="row"><label for="%1$s">%2$s%3$s</label></th><td><input type="text" class="regular-text" id="%1$s" name="%1$s" value="%4$s" placeholder="%5$s"%6$s></td></tr>',
        esc_attr( $name ),
        esc_html( $label ),
        $required ? ' <span class="hatakiti-required">*</span>' : '',
        esc_attr( $value ),
        esc_attr( $placeholder ),
        $required ? ' required' : ''
    );
}

function hatakiti_form_date_row( $label, $name, $value ) {
    printf(
        '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="date" id="%1$s" name="%1$s" value="%3$s"></td></tr>',
        esc_attr( $name ),
        esc_html( $label ),
        esc_attr( $value )
    );
}

function hatakiti_form_radio_row( $label, $name, $options, $value ) {
    printf( '<tr><th scope="row">%s</th><td><div class="hatakiti-radio-group">', esc_html( $label ) );
    foreach ( $options as $option ) {
        printf(
            '<label><input type="radio" name="%1$s" value="%2$s"%3$s> %2$s</label>',
            esc_attr( $name ),
            esc_attr( $option ),
            checked( $value, $option, false )
        );
    }
    echo '</div></td></tr>';
}

/**
 * ==========================================================================
 * 観劇記録
 * ==========================================================================
 */

function hatakiti_handle_theatre_record_submit( $post_id ) {
    check_admin_referer( 'hatakiti_save_theatre_record', 'hatakiti_record_nonce' );

    $title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
    if ( '' === trim( $title ) ) {
        return new WP_Error( 'hatakiti_missing_title', '公演タイトルを入力してください。' );
    }

    $review = isset( $_POST['hatakiti_review'] ) ? wp_kses_post( wp_unslash( $_POST['hatakiti_review'] ) ) : '';
    $status = ( isset( $_POST['hatakiti_action'] ) && 'publish' === $_POST['hatakiti_action'] ) ? 'publish' : 'draft';

    $postarr = array(
        'post_type'    => 'theatre_record',
        'post_title'   => $title,
        'post_content' => $review,
        'post_status'  => $status,
    );

    if ( $post_id ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return new WP_Error( 'hatakiti_forbidden', 'この記録を編集する権限がありません。' );
        }
        $postarr['ID'] = $post_id;
        $result = wp_update_post( $postarr, true );
    } else {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return new WP_Error( 'hatakiti_forbidden', '記録を作成する権限がありません。' );
        }
        $result = wp_insert_post( $postarr, true );
    }

    if ( is_wp_error( $result ) ) {
        return $result;
    }
    $post_id = $result;

    foreach ( hatakiti_theatre_record_fields() as $key => $field ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            continue;
        }
        $raw = wp_unslash( $_POST[ $key ] );
        if ( 'date' === $field['type'] ) {
            $value = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
        } elseif ( 'select' === $field['type'] ) {
            $value = in_array( $raw, $field['options'], true ) ? $raw : '';
        } else {
            $value = sanitize_text_field( $raw );
        }
        update_post_meta( $post_id, $key, $value );
    }

    $tags_raw = isset( $_POST['hatakiti_tags'] ) ? sanitize_text_field( wp_unslash( $_POST['hatakiti_tags'] ) ) : '';
    $tags     = array_filter( array_map( 'trim', explode( ',', $tags_raw ) ) );
    wp_set_post_tags( $post_id, $tags, false );

    return $post_id;
}

function hatakiti_render_theatre_record_form() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '権限がありません。' );
    }

    $error   = $GLOBALS['hatakiti_form_error'];
    $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

    $is_edit = $post_id > 0;
    $title   = '';
    $review  = '';
    $meta    = array();
    $tags    = array();

    foreach ( hatakiti_theatre_record_fields() as $key => $field ) {
        $meta[ $key ] = '';
    }

    if ( $is_edit ) {
        $post = get_post( $post_id );
        if ( ! $post || 'theatre_record' !== $post->post_type ) {
            wp_die( '指定された観劇記録が見つかりません。' );
        }
        $title  = $post->post_title;
        $review = $post->post_content;
        foreach ( array_keys( $meta ) as $key ) {
            $meta[ $key ] = get_post_meta( $post_id, $key, true );
        }
        $tags = wp_list_pluck( wp_get_post_tags( $post_id ), 'name' );
    }

    // A failed submit re-displays what the user typed instead of losing it.
    if ( $error && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
        $title  = isset( $_POST['post_title'] ) ? wp_unslash( $_POST['post_title'] ) : $title;
        $review = isset( $_POST['hatakiti_review'] ) ? wp_unslash( $_POST['hatakiti_review'] ) : $review;
        foreach ( array_keys( $meta ) as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                $meta[ $key ] = wp_unslash( $_POST[ $key ] );
            }
        }
        $tags = isset( $_POST['hatakiti_tags'] ) ? array_filter( array_map( 'trim', explode( ',', wp_unslash( $_POST['hatakiti_tags'] ) ) ) ) : $tags;
    }
    ?>
    <div class="wrap hatakiti-record-form">
        <h1><?php echo $is_edit ? '観劇記録を編集' : '観劇記録を追加'; ?></h1>
        <p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=theatre_record' ) ); ?>">&larr; 観劇記録一覧に戻る</a></p>

        <?php if ( $error ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
        <?php elseif ( isset( $_GET['updated'] ) ) : ?>
            <div class="notice notice-success"><p>保存しました。</p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'hatakiti_save_theatre_record', 'hatakiti_record_nonce' ); ?>
            <?php if ( $is_edit ) : ?>
                <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
            <?php endif; ?>

            <h2>公演情報</h2>
            <table class="form-table" role="presentation"><tbody>
                <?php
                hatakiti_form_text_row( '劇団名', 'hatakiti_troupe', $meta['hatakiti_troupe'] );
                hatakiti_form_text_row( '公演タイトル', 'post_title', $title, '', true );
                hatakiti_form_text_row( '演出', 'hatakiti_direction', $meta['hatakiti_direction'] );
                hatakiti_form_text_row( '脚本', 'hatakiti_script', $meta['hatakiti_script'] );
                hatakiti_form_date_row( '観劇日', 'hatakiti_viewing_date', $meta['hatakiti_viewing_date'] );
                hatakiti_form_date_row( '公演開始日', 'hatakiti_run_start', $meta['hatakiti_run_start'] );
                hatakiti_form_date_row( '公演終了日', 'hatakiti_run_end', $meta['hatakiti_run_end'] );
                hatakiti_form_text_row( '劇場', 'hatakiti_venue', $meta['hatakiti_venue'] );
                hatakiti_form_radio_row( '観劇方法', 'hatakiti_method', HATAKITI_THEATRE_METHOD_OPTIONS, $meta['hatakiti_method'] );
                ?>
            </tbody></table>

            <h2>感想</h2>
            <textarea name="hatakiti_review" rows="14" class="large-text hatakiti-review-textarea"><?php echo esc_textarea( $review ); ?></textarea>

            <h2>タグ</h2>
            <input type="text" name="hatakiti_tags" class="large-text" value="<?php echo esc_attr( implode( ', ', $tags ) ); ?>" placeholder="カンマ区切りで入力（例：演技, 熱量）">

            <p class="hatakiti-form-actions">
                <button type="submit" name="hatakiti_action" value="draft" class="button button-secondary">下書き保存</button>
                <button type="submit" name="hatakiti_action" value="publish" class="button button-primary">公開</button>
            </p>
        </form>
    </div>
    <?php
}

/**
 * ==========================================================================
 * 映画記録
 * ==========================================================================
 */

function hatakiti_handle_film_record_submit( $post_id ) {
    check_admin_referer( 'hatakiti_save_film_record', 'hatakiti_record_nonce' );

    $title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
    if ( '' === trim( $title ) ) {
        return new WP_Error( 'hatakiti_missing_title', '作品タイトルを入力してください。' );
    }

    $review = isset( $_POST['hatakiti_review'] ) ? wp_kses_post( wp_unslash( $_POST['hatakiti_review'] ) ) : '';
    $status = ( isset( $_POST['hatakiti_action'] ) && 'publish' === $_POST['hatakiti_action'] ) ? 'publish' : 'draft';

    $postarr = array(
        'post_type'    => 'film_record',
        'post_title'   => $title,
        'post_content' => $review,
        'post_status'  => $status,
    );

    if ( $post_id ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return new WP_Error( 'hatakiti_forbidden', 'この記録を編集する権限がありません。' );
        }
        $postarr['ID'] = $post_id;
        $result = wp_update_post( $postarr, true );
    } else {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return new WP_Error( 'hatakiti_forbidden', '記録を作成する権限がありません。' );
        }
        $result = wp_insert_post( $postarr, true );
    }

    if ( is_wp_error( $result ) ) {
        return $result;
    }
    $post_id = $result;

    foreach ( hatakiti_film_record_fields() as $key => $field ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            continue;
        }
        $raw = wp_unslash( $_POST[ $key ] );
        if ( 'date' === $field['type'] ) {
            $value = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
        } elseif ( 'select' === $field['type'] ) {
            $value = in_array( $raw, $field['options'], true ) ? $raw : '';
        } else {
            $value = sanitize_text_field( $raw );
        }
        update_post_meta( $post_id, $key, $value );
    }

    // Genre checkboxes, plus an optional inline "add a new genre" field so
    // the list can grow without leaving this form.
    $genre_ids = isset( $_POST['hatakiti_genre'] ) ? array_map( 'absint', (array) $_POST['hatakiti_genre'] ) : array();

    $new_genre = isset( $_POST['hatakiti_new_genre'] ) ? sanitize_text_field( wp_unslash( $_POST['hatakiti_new_genre'] ) ) : '';
    if ( '' !== trim( $new_genre ) ) {
        $existing = term_exists( $new_genre, 'film_genre' );
        if ( $existing ) {
            $genre_ids[] = (int) $existing['term_id'];
        } else {
            $inserted = wp_insert_term( $new_genre, 'film_genre' );
            if ( ! is_wp_error( $inserted ) ) {
                $genre_ids[] = (int) $inserted['term_id'];
            }
        }
    }

    wp_set_object_terms( $post_id, array_unique( $genre_ids ), 'film_genre', false );

    $tags_raw = isset( $_POST['hatakiti_tags'] ) ? sanitize_text_field( wp_unslash( $_POST['hatakiti_tags'] ) ) : '';
    $tags     = array_filter( array_map( 'trim', explode( ',', $tags_raw ) ) );
    wp_set_post_tags( $post_id, $tags, false );

    return $post_id;
}

function hatakiti_render_film_record_form() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '権限がありません。' );
    }

    $error   = $GLOBALS['hatakiti_form_error'];
    $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

    $is_edit      = $post_id > 0;
    $title        = '';
    $review       = '';
    $meta         = array();
    $tags         = array();
    $selected_ids = array();

    foreach ( hatakiti_film_record_fields() as $key => $field ) {
        $meta[ $key ] = '';
    }

    if ( $is_edit ) {
        $post = get_post( $post_id );
        if ( ! $post || 'film_record' !== $post->post_type ) {
            wp_die( '指定された映画記録が見つかりません。' );
        }
        $title  = $post->post_title;
        $review = $post->post_content;
        foreach ( array_keys( $meta ) as $key ) {
            $meta[ $key ] = get_post_meta( $post_id, $key, true );
        }
        $tags         = wp_list_pluck( wp_get_post_tags( $post_id ), 'name' );
        $selected_ids = wp_get_object_terms( $post_id, 'film_genre', array( 'fields' => 'ids' ) );
    }

    if ( $error && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
        $title  = isset( $_POST['post_title'] ) ? wp_unslash( $_POST['post_title'] ) : $title;
        $review = isset( $_POST['hatakiti_review'] ) ? wp_unslash( $_POST['hatakiti_review'] ) : $review;
        foreach ( array_keys( $meta ) as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                $meta[ $key ] = wp_unslash( $_POST[ $key ] );
            }
        }
        $tags         = isset( $_POST['hatakiti_tags'] ) ? array_filter( array_map( 'trim', explode( ',', wp_unslash( $_POST['hatakiti_tags'] ) ) ) ) : $tags;
        $selected_ids = isset( $_POST['hatakiti_genre'] ) ? array_map( 'absint', (array) $_POST['hatakiti_genre'] ) : $selected_ids;
    }

    $genres = get_terms( array( 'taxonomy' => 'film_genre', 'hide_empty' => false ) );
    ?>
    <div class="wrap hatakiti-record-form">
        <h1><?php echo $is_edit ? '映画記録を編集' : '映画記録を追加'; ?></h1>
        <p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=film_record' ) ); ?>">&larr; 映画記録一覧に戻る</a></p>

        <?php if ( $error ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
        <?php elseif ( isset( $_GET['updated'] ) ) : ?>
            <div class="notice notice-success"><p>保存しました。</p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'hatakiti_save_film_record', 'hatakiti_record_nonce' ); ?>
            <?php if ( $is_edit ) : ?>
                <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
            <?php endif; ?>

            <h2>作品情報</h2>
            <table class="form-table" role="presentation"><tbody>
                <?php
                hatakiti_form_text_row( '作品タイトル', 'post_title', $title, '', true );
                hatakiti_form_text_row( '監督', 'hatakiti_director', $meta['hatakiti_director'] );
                hatakiti_form_text_row( '脚本', 'hatakiti_screenwriter', $meta['hatakiti_screenwriter'] );
                hatakiti_form_text_row( '公開年', 'hatakiti_release_year', $meta['hatakiti_release_year'] );
                hatakiti_form_text_row( '出演者', 'hatakiti_cast', $meta['hatakiti_cast'] );
                hatakiti_form_date_row( '鑑賞日', 'hatakiti_viewing_date', $meta['hatakiti_viewing_date'] );
                hatakiti_form_radio_row( '鑑賞方法', 'hatakiti_method', HATAKITI_FILM_METHOD_OPTIONS, $meta['hatakiti_method'] );
                ?>
                <tr>
                    <th scope="row">ジャンル</th>
                    <td>
                        <div class="hatakiti-checkbox-group">
                            <?php foreach ( $genres as $genre ) : ?>
                                <label>
                                    <input type="checkbox" name="hatakiti_genre[]" value="<?php echo esc_attr( $genre->term_id ); ?>"<?php checked( in_array( (int) $genre->term_id, array_map( 'intval', (array) $selected_ids ), true ) ); ?>>
                                    <?php echo esc_html( $genre->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="hatakiti-add-genre">
                            <label for="hatakiti_new_genre">新しいジャンルを追加：</label>
                            <input type="text" id="hatakiti_new_genre" name="hatakiti_new_genre" class="regular-text" placeholder="例：ミュージカル">
                        </p>
                    </td>
                </tr>
            </tbody></table>

            <h2>感想</h2>
            <textarea name="hatakiti_review" rows="14" class="large-text hatakiti-review-textarea"><?php echo esc_textarea( $review ); ?></textarea>

            <h2>タグ</h2>
            <input type="text" name="hatakiti_tags" class="large-text" value="<?php echo esc_attr( implode( ', ', $tags ) ); ?>" placeholder="カンマ区切りで入力">

            <p class="hatakiti-form-actions">
                <button type="submit" name="hatakiti_action" value="draft" class="button button-secondary">下書き保存</button>
                <button type="submit" name="hatakiti_action" value="publish" class="button button-primary">公開</button>
            </p>
        </form>
    </div>
    <?php
}

/**
 * ==========================================================================
 * List table polish — surface the key fields as columns so the list itself
 * is useful at a glance, without needing to open each record.
 * ==========================================================================
 */

add_filter( 'manage_theatre_record_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'title' === $key ) {
            $new['hatakiti_troupe']       = '劇団名';
            $new['hatakiti_direction']    = '演出';
            $new['hatakiti_script']       = '脚本';
            $new['hatakiti_viewing_date'] = '観劇日';
        }
    }
    return $new;
} );
add_action( 'manage_theatre_record_posts_custom_column', function ( $column, $post_id ) {
    if ( in_array( $column, array( 'hatakiti_troupe', 'hatakiti_direction', 'hatakiti_script', 'hatakiti_viewing_date' ), true ) ) {
        echo esc_html( get_post_meta( $post_id, $column, true ) );
    }
}, 10, 2 );

add_filter( 'manage_film_record_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'title' === $key ) {
            $new['hatakiti_director']     = '監督';
            $new['hatakiti_viewing_date'] = '鑑賞日';
        }
    }
    return $new;
} );
add_action( 'manage_film_record_posts_custom_column', function ( $column, $post_id ) {
    if ( in_array( $column, array( 'hatakiti_director', 'hatakiti_viewing_date' ), true ) ) {
        echo esc_html( get_post_meta( $post_id, $column, true ) );
    }
}, 10, 2 );
