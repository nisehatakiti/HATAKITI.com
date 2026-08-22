<?php
/**
 * Dedicated data-entry screen for 活動履歴, same pattern as
 * includes/admin-forms.php (観劇記録/映画記録): no native title field or
 * block editor, one purpose-built form, everything top to bottom.
 *
 * 活動日 is a required field here — independent of WordPress's own
 * post_date — specifically because relying only on the native "Published
 * on" date previously left it effectively invisible/easy to lose track of
 * on this content type.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_register_activity_form_page() {
    $hook = add_submenu_page(
        null,
        '活動履歴フォーム',
        '活動履歴フォーム',
        'edit_posts',
        'hatakiti-activity-record-form',
        'hatakiti_render_activity_record_form'
    );

    add_action( 'load-' . $hook, 'hatakiti_handle_activity_record_page_load' );

    if ( empty( $GLOBALS['hatakiti_form_hooks'] ) ) {
        $GLOBALS['hatakiti_form_hooks'] = array();
    }
    $GLOBALS['hatakiti_form_hooks'][] = $hook;
}
add_action( 'admin_menu', 'hatakiti_register_activity_form_page', 11 );

function hatakiti_handle_activity_record_page_load() {
    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['hatakiti_record_nonce'] ) ) {
        return;
    }

    $submitted_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $result       = hatakiti_handle_activity_record_submit( $submitted_id );

    if ( is_wp_error( $result ) ) {
        $GLOBALS['hatakiti_form_error'] = $result->get_error_message();
        return;
    }

    wp_safe_redirect( admin_url( 'admin.php?page=hatakiti-activity-record-form&post_id=' . $result . '&updated=1' ) );
    exit;
}

function hatakiti_handle_activity_record_submit( $post_id ) {
    check_admin_referer( 'hatakiti_save_activity_record', 'hatakiti_record_nonce' );

    $title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
    if ( '' === trim( $title ) ) {
        return new WP_Error( 'hatakiti_missing_title', 'タイトルを入力してください。' );
    }

    $activity_date = isset( $_POST['hatakiti_activity_date'] ) ? wp_unslash( $_POST['hatakiti_activity_date'] ) : '';
    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $activity_date ) ) {
        return new WP_Error( 'hatakiti_missing_date', '活動日を入力してください。' );
    }

    $body   = isset( $_POST['hatakiti_review'] ) ? wp_kses_post( wp_unslash( $_POST['hatakiti_review'] ) ) : '';
    $status = ( isset( $_POST['hatakiti_action'] ) && 'publish' === $_POST['hatakiti_action'] ) ? 'publish' : 'draft';

    $postarr = array(
        'post_type'    => 'activity_record',
        'post_title'   => $title,
        'post_content' => $body,
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

    update_post_meta( $post_id, 'hatakiti_activity_date', $activity_date );

    $related_link = isset( $_POST['hatakiti_related_link'] ) ? esc_url_raw( wp_unslash( $_POST['hatakiti_related_link'] ) ) : '';
    update_post_meta( $post_id, 'hatakiti_related_link', $related_link );

    // 活動種別 checkboxes, plus an optional inline "add a new type" field.
    $type_ids = isset( $_POST['hatakiti_activity_type'] ) ? array_map( 'absint', (array) $_POST['hatakiti_activity_type'] ) : array();

    $new_type = isset( $_POST['hatakiti_new_activity_type'] ) ? sanitize_text_field( wp_unslash( $_POST['hatakiti_new_activity_type'] ) ) : '';
    if ( '' !== trim( $new_type ) ) {
        $existing = term_exists( $new_type, 'activity_type' );
        if ( $existing ) {
            $type_ids[] = (int) $existing['term_id'];
        } else {
            $inserted = wp_insert_term( $new_type, 'activity_type' );
            if ( ! is_wp_error( $inserted ) ) {
                $type_ids[] = (int) $inserted['term_id'];
            }
        }
    }
    wp_set_object_terms( $post_id, array_unique( $type_ids ), 'activity_type', false );

    $tags_raw = isset( $_POST['hatakiti_tags'] ) ? sanitize_text_field( wp_unslash( $_POST['hatakiti_tags'] ) ) : '';
    $tags     = array_filter( array_map( 'trim', explode( ',', $tags_raw ) ) );
    wp_set_post_tags( $post_id, $tags, false );

    if ( ! empty( $_FILES['hatakiti_featured_image']['name'] ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attach_id = media_handle_upload( 'hatakiti_featured_image', $post_id );
        if ( ! is_wp_error( $attach_id ) ) {
            set_post_thumbnail( $post_id, $attach_id );
        }
        // A bad upload is not fatal to saving the rest of the record —
        // HATAKITI can just try the image again from the edit form.
    }

    return $post_id;
}

function hatakiti_render_activity_record_form() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '権限がありません。' );
    }

    $error   = $GLOBALS['hatakiti_form_error'];
    $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
    $is_edit = $post_id > 0;

    $title         = '';
    $activity_date = '';
    $body          = '';
    $related_link  = '';
    $tags          = array();
    $selected_ids  = array();
    $thumbnail_id  = 0;

    if ( $is_edit ) {
        $post = get_post( $post_id );
        if ( ! $post || 'activity_record' !== $post->post_type ) {
            wp_die( '指定された活動履歴が見つかりません。' );
        }
        $title         = $post->post_title;
        $body          = $post->post_content;
        $activity_date = get_post_meta( $post_id, 'hatakiti_activity_date', true );
        $related_link  = get_post_meta( $post_id, 'hatakiti_related_link', true );
        $tags          = wp_list_pluck( wp_get_post_tags( $post_id ), 'name' );
        $selected_ids  = wp_get_object_terms( $post_id, 'activity_type', array( 'fields' => 'ids' ) );
        $thumbnail_id  = get_post_thumbnail_id( $post_id );
    }

    if ( $error && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
        $title         = isset( $_POST['post_title'] ) ? wp_unslash( $_POST['post_title'] ) : $title;
        $activity_date = isset( $_POST['hatakiti_activity_date'] ) ? wp_unslash( $_POST['hatakiti_activity_date'] ) : $activity_date;
        $body          = isset( $_POST['hatakiti_review'] ) ? wp_unslash( $_POST['hatakiti_review'] ) : $body;
        $related_link  = isset( $_POST['hatakiti_related_link'] ) ? wp_unslash( $_POST['hatakiti_related_link'] ) : $related_link;
        $tags          = isset( $_POST['hatakiti_tags'] ) ? array_filter( array_map( 'trim', explode( ',', wp_unslash( $_POST['hatakiti_tags'] ) ) ) ) : $tags;
        $selected_ids  = isset( $_POST['hatakiti_activity_type'] ) ? array_map( 'absint', (array) $_POST['hatakiti_activity_type'] ) : $selected_ids;
    }

    $types = get_terms( array( 'taxonomy' => 'activity_type', 'hide_empty' => false ) );
    ?>
    <div class="wrap hatakiti-record-form">
        <h1><?php echo $is_edit ? '活動履歴を編集' : '活動履歴を追加'; ?></h1>
        <p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=activity_record' ) ); ?>">&larr; 活動履歴一覧に戻る</a></p>

        <?php if ( $error ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
        <?php elseif ( isset( $_GET['updated'] ) ) : ?>
            <div class="notice notice-success"><p>保存しました。</p></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'hatakiti_save_activity_record', 'hatakiti_record_nonce' ); ?>
            <?php if ( $is_edit ) : ?>
                <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
            <?php endif; ?>

            <table class="form-table" role="presentation"><tbody>
                <?php
                hatakiti_form_date_row( '活動日', 'hatakiti_activity_date', $activity_date );
                hatakiti_form_text_row( 'タイトル', 'post_title', $title, '', true );
                ?>
                <tr>
                    <th scope="row">活動種別</th>
                    <td>
                        <div class="hatakiti-checkbox-group">
                            <?php foreach ( $types as $type ) : ?>
                                <label>
                                    <input type="checkbox" name="hatakiti_activity_type[]" value="<?php echo esc_attr( $type->term_id ); ?>"<?php checked( in_array( (int) $type->term_id, array_map( 'intval', (array) $selected_ids ), true ) ); ?>>
                                    <?php echo esc_html( $type->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="hatakiti-add-genre">
                            <label for="hatakiti_new_activity_type">新しい活動種別を追加：</label>
                            <input type="text" id="hatakiti_new_activity_type" name="hatakiti_new_activity_type" class="regular-text" placeholder="例：企画">
                        </p>
                    </td>
                </tr>
                <?php hatakiti_form_text_row( '関連リンク', 'hatakiti_related_link', $related_link, 'https://...' ); ?>
                <tr>
                    <th scope="row"><label for="hatakiti_featured_image">画像</label></th>
                    <td>
                        <?php if ( $thumbnail_id ) : ?>
                            <div class="hatakiti-current-thumb"><?php echo wp_get_attachment_image( $thumbnail_id, 'thumbnail' ); ?></div>
                        <?php endif; ?>
                        <input type="file" id="hatakiti_featured_image" name="hatakiti_featured_image" accept="image/*">
                        <?php if ( $thumbnail_id ) : ?>
                            <p class="description">画像を選び直すと、現在の画像と差し替わります。</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody></table>

            <h2>本文</h2>
            <textarea name="hatakiti_review" rows="14" class="large-text hatakiti-review-textarea"><?php echo esc_textarea( $body ); ?></textarea>

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
 * List table polish, same idea as 観劇記録/映画記録.
 */
add_filter( 'manage_activity_record_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'title' === $key ) {
            $new['hatakiti_activity_date'] = '活動日';
        }
    }
    return $new;
} );
add_action( 'manage_activity_record_posts_custom_column', function ( $column, $post_id ) {
    if ( 'hatakiti_activity_date' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'hatakiti_activity_date', true ) );
    }
}, 10, 2 );
