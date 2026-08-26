<?php
/**
 * JSON import for 日本民話 — the core deliverable of this feature
 * (docs/12-JapaneseFolktale-DataContract.md §19,
 * docs/13-JapaneseFolktale-CollectionOperations.md).
 *
 * Accepts either a single FolktaleRecord object, a bare array of them
 * (a collection batch), or {"records": [...]}. Each record is matched
 * against existing posts by record_id (docs/12 §5): no match creates a
 * new draft, a match updates the existing post in place — never a new
 * one — and on update this deliberately never touches post_status, so a
 * record HATAKITI has already published can't be silently reverted to
 * draft by a later re-import.
 *
 * New records are always saved as draft regardless of a "status":
 * "publish" in the JSON, per docs/12 §5's explicit "勝手に公開しないこと"
 * — the hatakiti_folktale_allow_auto_publish filter is the seam for
 * lifting that later without touching this file again.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_register_folktale_import_page() {
    add_submenu_page(
        'edit.php?post_type=folktale',
        'JSONインポート',
        'JSONインポート',
        'edit_posts',
        'hatakiti-folktale-import',
        'hatakiti_render_folktale_import_page'
    );
}
add_action( 'admin_menu', 'hatakiti_register_folktale_import_page' );

function hatakiti_render_folktale_import_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '権限がありません。' );
    }

    $results = null;

    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['hatakiti_folktale_import_nonce'] ) ) {
        check_admin_referer( 'hatakiti_folktale_import', 'hatakiti_folktale_import_nonce' );
        $results = hatakiti_handle_folktale_import_submit();
    }
    ?>
    <div class="wrap hatakiti-record-form">
        <h1>日本民話 JSONインポート</h1>
        <p>docs/12-JapaneseFolktale-DataContract.md 形式のJSONを読み込みます。単一レコード・レコードの配列（バッチ）・<code>{"records":[...]}</code> のいずれも受け付けます。</p>
        <p class="description">record_id が既存の民話と一致する場合は新規投稿を作らず既存データを更新します（公開状態は変更しません）。一致しない場合は新規に<strong>下書き</strong>として作成します。</p>

        <?php if ( is_wp_error( $results ) ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $results->get_error_message() ); ?></p></div>
        <?php elseif ( is_array( $results ) ) : ?>
            <div class="notice <?php echo $results['error_count'] ? 'notice-warning' : 'notice-success'; ?>">
                <p>
                    処理件数: <?php echo (int) count( $results['rows'] ); ?> /
                    新規: <?php echo (int) $results['created_count']; ?> /
                    更新: <?php echo (int) $results['updated_count']; ?> /
                    エラー: <?php echo (int) $results['error_count']; ?>
                </p>
            </div>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>結果</th>
                        <th>record_id</th>
                        <th>タイトル</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $results['rows'] as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( $row['label'] ); ?></td>
                            <td><code><?php echo esc_html( $row['record_id'] ); ?></code></td>
                            <td>
                                <?php if ( ! empty( $row['post_id'] ) ) : ?>
                                    <a href="<?php echo esc_url( get_edit_post_link( $row['post_id'] ) ); ?>"><?php echo esc_html( $row['title'] ); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html( $row['title'] ); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( $row['message'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>JSONを読み込む</h2>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'hatakiti_folktale_import', 'hatakiti_folktale_import_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="hatakiti_folktale_json_file">JSONファイル</label></th>
                    <td><input type="file" id="hatakiti_folktale_json_file" name="hatakiti_folktale_json_file" accept="application/json,.json"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hatakiti_folktale_json_text">またはJSONを直接貼り付け</label></th>
                    <td><textarea id="hatakiti_folktale_json_text" name="hatakiti_folktale_json_text" rows="12" class="large-text code" placeholder='{"schema_version":"1.0","record_id":"JP-FOLK-AOM-0001", ...}'></textarea></td>
                </tr>
            </table>
            <p class="hatakiti-form-actions">
                <button type="submit" class="button button-primary">インポート実行</button>
            </p>
        </form>
    </div>
    <?php
}

function hatakiti_handle_folktale_import_submit() {
    $json_text = '';

    if ( ! empty( $_FILES['hatakiti_folktale_json_file']['tmp_name'] ) && is_uploaded_file( $_FILES['hatakiti_folktale_json_file']['tmp_name'] ) ) {
        $json_text = file_get_contents( $_FILES['hatakiti_folktale_json_file']['tmp_name'] );
    } elseif ( ! empty( $_POST['hatakiti_folktale_json_text'] ) ) {
        $json_text = wp_unslash( $_POST['hatakiti_folktale_json_text'] );
    }

    $json_text = trim( (string) $json_text );
    if ( '' === $json_text ) {
        return new WP_Error( 'hatakiti_folktale_empty', 'JSONファイルまたはテキストを指定してください。' );
    }

    $decoded = json_decode( $json_text, true );
    if ( null === $decoded && 'null' !== strtolower( trim( $json_text ) ) ) {
        return new WP_Error( 'hatakiti_folktale_invalid_json', 'JSONの解析に失敗しました。構文を確認してください。' );
    }

    // Normalize into a flat list of record objects.
    if ( isset( $decoded['record_id'] ) ) {
        $records = array( $decoded );
    } elseif ( isset( $decoded['records'] ) && is_array( $decoded['records'] ) ) {
        $records = $decoded['records'];
    } elseif ( is_array( $decoded ) && ( empty( $decoded ) || array_keys( $decoded ) === range( 0, count( $decoded ) - 1 ) ) ) {
        $records = $decoded;
    } else {
        return new WP_Error( 'hatakiti_folktale_unrecognized', 'JSONの形式を認識できませんでした（単一レコード／レコードの配列／{"records":[...]} のいずれかにしてください）。' );
    }

    if ( empty( $records ) ) {
        return new WP_Error( 'hatakiti_folktale_no_records', 'インポート対象のレコードが見つかりませんでした。' );
    }

    $rows           = array();
    $created_count  = 0;
    $updated_count  = 0;
    $error_count    = 0;

    foreach ( $records as $record ) {
        $result = hatakiti_import_folktale_record( $record );
        $rows[] = $result;
        if ( 'error' === $result['status'] ) {
            $error_count++;
        } elseif ( 'created' === $result['status'] ) {
            $created_count++;
        } elseif ( 'updated' === $result['status'] ) {
            $updated_count++;
        }
    }

    return array(
        'rows'          => $rows,
        'created_count' => $created_count,
        'updated_count' => $updated_count,
        'error_count'   => $error_count,
    );
}

/**
 * Imports one FolktaleRecord. Never throws — always returns a result row
 * so one bad record in a batch doesn't abort the rest.
 */
function hatakiti_import_folktale_record( $record ) {
    $row_base = array(
        'record_id' => isset( $record['record_id'] ) ? (string) $record['record_id'] : '',
        'title'     => isset( $record['title'] ) ? (string) $record['title'] : '',
        'post_id'   => 0,
    );

    if ( empty( $record['schema_version'] ) ) {
        return $row_base + array( 'status' => 'error', 'label' => 'エラー', 'message' => 'schema_version がありません。' );
    }
    if ( '1.0' !== (string) $record['schema_version'] ) {
        return $row_base + array( 'status' => 'error', 'label' => 'エラー', 'message' => '未対応の schema_version: ' . esc_html( $record['schema_version'] ) );
    }
    if ( empty( $record['record_id'] ) ) {
        return $row_base + array( 'status' => 'error', 'label' => 'エラー', 'message' => 'record_id がありません。' );
    }
    if ( empty( $record['title'] ) ) {
        return $row_base + array( 'status' => 'error', 'label' => 'エラー', 'message' => 'title がありません。' );
    }
    if ( empty( $record['sources'] ) || ! is_array( $record['sources'] ) ) {
        return $row_base + array( 'status' => 'error', 'label' => 'エラー', 'message' => '出典 (sources) は必須です。' );
    }

    $record_id = sanitize_text_field( $record['record_id'] );
    $title     = sanitize_text_field( $record['title'] );

    // summary: plain string or {text, based_on_source_ids}.
    $summary_text    = '';
    $summary_sources = array();
    if ( isset( $record['summary'] ) ) {
        if ( is_array( $record['summary'] ) ) {
            $summary_text    = isset( $record['summary']['text'] ) ? (string) $record['summary']['text'] : '';
            $summary_sources = isset( $record['summary']['based_on_source_ids'] ) ? (array) $record['summary']['based_on_source_ids'] : array();
        } else {
            $summary_text = (string) $record['summary'];
        }
    }

    $existing = get_posts( array(
        'post_type'      => 'folktale',
        'post_status'    => 'any',
        'meta_key'       => 'hatakiti_folktale_record_id',
        'meta_value'     => $record_id,
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    $is_update = ! empty( $existing );
    $post_id   = $is_update ? (int) $existing[0] : 0;

    $status_field       = isset( $record['status'] ) ? $record['status'] : 'draft';
    $allow_auto_publish = apply_filters( 'hatakiti_folktale_allow_auto_publish', false );
    $new_post_status    = ( $allow_auto_publish && 'publish' === $status_field ) ? 'publish' : 'draft';

    $postarr = array(
        'post_type'    => 'folktale',
        'post_title'   => $title,
        'post_content' => wp_kses_post( $summary_text ),
    );

    if ( $is_update ) {
        $postarr['ID'] = $post_id;
        // post_status intentionally omitted — see file docblock.
        $result = wp_update_post( $postarr, true );
    } else {
        $postarr['post_status'] = $new_post_status;
        $result = wp_insert_post( $postarr, true );
    }

    if ( is_wp_error( $result ) ) {
        return $row_base + array( 'status' => 'error', 'label' => 'エラー', 'message' => $result->get_error_message() );
    }
    $post_id = $result;

    update_post_meta( $post_id, 'hatakiti_folktale_record_id', $record_id );
    update_post_meta( $post_id, 'hatakiti_folktale_title_normalized', isset( $record['title_normalized'] ) ? sanitize_text_field( $record['title_normalized'] ) : '' );
    update_post_meta( $post_id, 'hatakiti_folktale_title_origin', isset( $record['title_origin'] ) ? sanitize_text_field( $record['title_origin'] ) : '' );
    update_post_meta( $post_id, 'hatakiti_folktale_schema_version', sanitize_text_field( $record['schema_version'] ) );
    update_post_meta( $post_id, 'hatakiti_folktale_confidence', isset( $record['confidence'] ) ? sanitize_text_field( $record['confidence'] ) : '' );

    $region = isset( $record['region'] ) && is_array( $record['region'] ) ? $record['region'] : array();
    update_post_meta( $post_id, 'hatakiti_folktale_region_prefecture', isset( $region['prefecture'] ) ? sanitize_text_field( $region['prefecture'] ) : '' );
    update_post_meta( $post_id, 'hatakiti_folktale_region_historical_province', isset( $region['historical_province'] ) ? sanitize_text_field( $region['historical_province'] ) : '' );
    update_post_meta( $post_id, 'hatakiti_folktale_region_municipality', isset( $region['municipality'] ) ? sanitize_text_field( $region['municipality'] ) : '' );
    update_post_meta( $post_id, 'hatakiti_folktale_region_area_name', isset( $region['area_name'] ) ? sanitize_text_field( $region['area_name'] ) : '' );
    update_post_meta( $post_id, 'hatakiti_folktale_region_source_description', isset( $region['source_description'] ) ? sanitize_text_field( $region['source_description'] ) : '' );

    update_post_meta( $post_id, 'hatakiti_folktale_locations_json', wp_json_encode( isset( $record['locations'] ) ? $record['locations'] : array(), JSON_UNESCAPED_UNICODE ) );
    update_post_meta( $post_id, 'hatakiti_folktale_characters_json', wp_json_encode( isset( $record['characters'] ) ? $record['characters'] : array(), JSON_UNESCAPED_UNICODE ) );
    update_post_meta( $post_id, 'hatakiti_folktale_beings_json', wp_json_encode( isset( $record['beings'] ) ? $record['beings'] : array(), JSON_UNESCAPED_UNICODE ) );
    update_post_meta( $post_id, 'hatakiti_folktale_sources_json', wp_json_encode( $record['sources'], JSON_UNESCAPED_UNICODE ) );
    update_post_meta( $post_id, 'hatakiti_folktale_related_records_json', wp_json_encode( isset( $record['related_records'] ) ? $record['related_records'] : array(), JSON_UNESCAPED_UNICODE ) );
    update_post_meta( $post_id, 'hatakiti_folktale_ai_processing_json', wp_json_encode( isset( $record['ai_processing'] ) ? $record['ai_processing'] : array(), JSON_UNESCAPED_UNICODE ) );
    update_post_meta( $post_id, 'hatakiti_folktale_summary_based_on_json', wp_json_encode( $summary_sources, JSON_UNESCAPED_UNICODE ) );

    if ( ! empty( $record['themes'] ) && is_array( $record['themes'] ) ) {
        wp_set_object_terms( $post_id, array_map( 'sanitize_text_field', $record['themes'] ), 'folktale_theme', false );
    } else {
        wp_set_object_terms( $post_id, array(), 'folktale_theme', false );
    }

    if ( ! empty( $record['story_type'] ) && is_array( $record['story_type'] ) ) {
        wp_set_object_terms( $post_id, array_map( 'sanitize_text_field', $record['story_type'] ), 'folktale_story_type', false );
    } else {
        wp_set_object_terms( $post_id, array(), 'folktale_story_type', false );
    }

    hatakiti_sync_folktale_being_terms( $post_id );

    // Public-facing content, kept separate from post_content (which may
    // hold AI's raw research summary/notes — internal, not for public
    // display). story_content (本文・あらすじの詳しい内容) takes priority
    // — a record with it is content_confirmed. story_summary (or the
    // older public_summary key, kept for back-compat) without
    // story_content is summary_confirmed. Neither present means no
    // confirmed story content exists yet: researching, with an honest
    // notice rather than a manufactured description built from region/
    // locations/sources.
    if ( ! empty( $record['story_content'] ) && is_string( $record['story_content'] ) ) {
        update_post_meta( $post_id, 'hatakiti_folktale_story_status', 'content_confirmed' );
        update_post_meta( $post_id, 'hatakiti_folktale_story_content', wp_kses_post( $record['story_content'] ) );
        $summary_text = ! empty( $record['story_summary'] ) ? $record['story_summary'] : ( ! empty( $record['public_summary'] ) ? $record['public_summary'] : '' );
        update_post_meta( $post_id, 'hatakiti_folktale_public_summary', sanitize_textarea_field( $summary_text ) );
    } elseif ( ( ! empty( $record['story_summary'] ) && is_string( $record['story_summary'] ) ) || ( ! empty( $record['public_summary'] ) && is_string( $record['public_summary'] ) ) ) {
        update_post_meta( $post_id, 'hatakiti_folktale_story_status', 'summary_confirmed' );
        update_post_meta( $post_id, 'hatakiti_folktale_story_content', '' );
        $summary_text = ! empty( $record['story_summary'] ) ? $record['story_summary'] : $record['public_summary'];
        update_post_meta( $post_id, 'hatakiti_folktale_public_summary', sanitize_textarea_field( $summary_text ) );
    } else {
        update_post_meta( $post_id, 'hatakiti_folktale_story_status', 'researching' );
        update_post_meta( $post_id, 'hatakiti_folktale_story_content', '' );
        update_post_meta( $post_id, 'hatakiti_folktale_public_summary', hatakiti_generate_folktale_researching_notice( $post_id ) );
    }

    return array(
        'record_id' => $record_id,
        'title'     => $title,
        'post_id'   => $post_id,
        'status'    => $is_update ? 'updated' : 'created',
        'label'     => $is_update ? '更新' : '新規作成',
        'message'   => $is_update ? '既存の民話を更新しました（公開状態は変更していません）。' : ( 'publish' === $new_post_status ? '新規に公開しました。' : '新規に下書き作成しました。' ),
    );
}
