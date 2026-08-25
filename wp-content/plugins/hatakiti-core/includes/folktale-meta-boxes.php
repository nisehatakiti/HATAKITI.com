<?php
/**
 * Native meta boxes for 日本民話 — used both by the standard wp-admin
 * edit screen and, indirectly, by the JSON importer (which writes the
 * same meta keys these boxes read/write).
 *
 * テーマ (folktale_theme) and 分類 (folktale_story_type) are plain flat
 * string lists per docs/12 §12/§13 — WordPress's own default tag-style
 * taxonomy box is already the right editor for those, so no custom UI is
 * added for them here.
 *
 * 登場する存在 (folktale_being) carries extra structured data per entry
 * (normalized_name/type/attributes/source_ids — docs/12 §11), so its
 * taxonomy terms are kept in sync FROM the 存在(JSON) field below rather
 * than edited directly — one source of truth, whether a record arrives
 * via JSON import or a manual edit here.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hatakiti_add_folktale_meta_boxes() {
    add_meta_box(
        'hatakiti_folktale_identity',
        '民話ID・基本情報',
        'hatakiti_render_folktale_identity_box',
        'folktale',
        'normal',
        'high'
    );
    add_meta_box(
        'hatakiti_folktale_region',
        '地域',
        'hatakiti_render_folktale_region_box',
        'folktale',
        'normal',
        'high'
    );
    add_meta_box(
        'hatakiti_folktale_structured',
        '場所・登場人物・登場する存在・出典・関連民話（JSON）',
        'hatakiti_render_folktale_structured_box',
        'folktale',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'hatakiti_add_folktale_meta_boxes' );

function hatakiti_render_folktale_identity_box( $post ) {
    wp_nonce_field( 'hatakiti_save_folktale', 'hatakiti_folktale_nonce' );

    $record_id       = get_post_meta( $post->ID, 'hatakiti_folktale_record_id', true );
    $title_norm      = get_post_meta( $post->ID, 'hatakiti_folktale_title_normalized', true );
    $schema_version  = get_post_meta( $post->ID, 'hatakiti_folktale_schema_version', true );
    $confidence      = get_post_meta( $post->ID, 'hatakiti_folktale_confidence', true );
    ?>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="hatakiti_folktale_record_id">record_id</label></th>
            <td>
                <input type="text" class="regular-text code" id="hatakiti_folktale_record_id" name="hatakiti_folktale_record_id" value="<?php echo esc_attr( $record_id ); ?>" placeholder="JP-FOLK-AOM-0001">
                <p class="description">JSONインポートの重複判定に使う一意キーです。手動で変更すると再インポート時に別レコード扱いになるので注意してください。</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="hatakiti_folktale_title_normalized">title_normalized</label></th>
            <td><input type="text" class="regular-text" id="hatakiti_folktale_title_normalized" name="hatakiti_folktale_title_normalized" value="<?php echo esc_attr( $title_norm ); ?>"></td>
        </tr>
        <tr>
            <th scope="row"><label for="hatakiti_folktale_confidence">confidence</label></th>
            <td>
                <select id="hatakiti_folktale_confidence" name="hatakiti_folktale_confidence">
                    <option value="">—</option>
                    <?php foreach ( array( 'high', 'medium', 'low', 'uncertain' ) as $level ) : ?>
                        <option value="<?php echo esc_attr( $level ); ?>"<?php selected( $confidence, $level ); ?>><?php echo esc_html( $level ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">schema_version</th>
            <td><?php echo esc_html( $schema_version ? $schema_version : '—' ); ?></td>
        </tr>
    </table>
    <?php
}

function hatakiti_render_folktale_region_box( $post ) {
    $prefecture   = get_post_meta( $post->ID, 'hatakiti_folktale_region_prefecture', true );
    $province     = get_post_meta( $post->ID, 'hatakiti_folktale_region_historical_province', true );
    $municipality = get_post_meta( $post->ID, 'hatakiti_folktale_region_municipality', true );
    $area_name    = get_post_meta( $post->ID, 'hatakiti_folktale_region_area_name', true );
    $source_desc  = get_post_meta( $post->ID, 'hatakiti_folktale_region_source_description', true );
    ?>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="hatakiti_folktale_region_prefecture">都道府県</label></th>
            <td>
                <select id="hatakiti_folktale_region_prefecture" name="hatakiti_folktale_region_prefecture">
                    <option value="">—</option>
                    <?php foreach ( hatakiti_folktale_prefectures() as $pref ) : ?>
                        <option value="<?php echo esc_attr( $pref ); ?>"<?php selected( $prefecture, $pref ); ?>><?php echo esc_html( $pref ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description">旧国名のみの場合は空欄のままで構いません（下の「旧国名」に記録してください）。</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="hatakiti_folktale_region_historical_province">旧国名</label></th>
            <td><input type="text" class="regular-text" id="hatakiti_folktale_region_historical_province" name="hatakiti_folktale_region_historical_province" value="<?php echo esc_attr( $province ); ?>"></td>
        </tr>
        <tr>
            <th scope="row"><label for="hatakiti_folktale_region_municipality">市町村</label></th>
            <td><input type="text" class="regular-text" id="hatakiti_folktale_region_municipality" name="hatakiti_folktale_region_municipality" value="<?php echo esc_attr( $municipality ); ?>"></td>
        </tr>
        <tr>
            <th scope="row"><label for="hatakiti_folktale_region_area_name">地区</label></th>
            <td><input type="text" class="regular-text" id="hatakiti_folktale_region_area_name" name="hatakiti_folktale_region_area_name" value="<?php echo esc_attr( $area_name ); ?>"></td>
        </tr>
        <tr>
            <th scope="row"><label for="hatakiti_folktale_region_source_description">資料上の地域表記</label></th>
            <td><input type="text" class="regular-text" id="hatakiti_folktale_region_source_description" name="hatakiti_folktale_region_source_description" value="<?php echo esc_attr( $source_desc ); ?>"></td>
        </tr>
    </table>
    <?php
}

function hatakiti_folktale_json_textarea_field( $key, $label, $example ) {
    $value = get_post_meta( get_the_ID(), $key, true );
    if ( '' === $value ) {
        $value = $example;
    }
    ?>
    <p>
        <label for="<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br>
        <textarea id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="6" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
    </p>
    <?php
}

function hatakiti_render_folktale_structured_box( $post ) {
    ?>
    <p class="description">
        docs/12-JapaneseFolktale-DataContract.md の各配列をそのままJSONとして保持しています。
        通常はJSONインポートで登録し、ここでは誤りの修正など必要な箇所だけ直接編集してください。空欄のまま保存すると空配列 <code>[]</code> として扱われます。
    </p>
    <?php
    hatakiti_folktale_json_textarea_field(
        'hatakiti_folktale_locations_json',
        '場所 (locations)',
        '[]'
    );
    hatakiti_folktale_json_textarea_field(
        'hatakiti_folktale_characters_json',
        '登場人物 (characters)',
        '[]'
    );
    hatakiti_folktale_json_textarea_field(
        'hatakiti_folktale_beings_json',
        '妖怪・怪異・神仏など (beings)',
        '[]'
    );
    hatakiti_folktale_json_textarea_field(
        'hatakiti_folktale_sources_json',
        '出典 (sources) — 必須',
        '[]'
    );
    hatakiti_folktale_json_textarea_field(
        'hatakiti_folktale_related_records_json',
        '関連民話 (related_records)',
        '[]'
    );
}

/**
 * Saves everything the boxes above render, plus re-syncs folktale_being
 * taxonomy terms from the just-saved beings JSON (see file docblock).
 */
function hatakiti_save_folktale_meta( $post_id, $post ) {
    if ( ! isset( $_POST['hatakiti_folktale_nonce'] ) ||
        ! wp_verify_nonce( $_POST['hatakiti_folktale_nonce'], 'hatakiti_save_folktale' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $text_fields = array(
        'hatakiti_folktale_record_id',
        'hatakiti_folktale_title_normalized',
        'hatakiti_folktale_confidence',
        'hatakiti_folktale_region_prefecture',
        'hatakiti_folktale_region_historical_province',
        'hatakiti_folktale_region_municipality',
        'hatakiti_folktale_region_area_name',
        'hatakiti_folktale_region_source_description',
    );
    foreach ( $text_fields as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
        }
    }

    $json_fields = array(
        'hatakiti_folktale_locations_json',
        'hatakiti_folktale_characters_json',
        'hatakiti_folktale_beings_json',
        'hatakiti_folktale_sources_json',
        'hatakiti_folktale_related_records_json',
    );
    foreach ( $json_fields as $key ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            continue;
        }
        $raw = trim( wp_unslash( $_POST[ $key ] ) );
        if ( '' === $raw ) {
            $raw = '[]';
        }
        $decoded = json_decode( $raw, true );
        if ( null === $decoded && 'null' !== strtolower( $raw ) ) {
            // Invalid JSON — don't overwrite good data with garbage;
            // leave the previously stored value untouched.
            continue;
        }
        update_post_meta( $post_id, $key, wp_json_encode( $decoded, JSON_UNESCAPED_UNICODE ) );
    }

    hatakiti_sync_folktale_being_terms( $post_id );
}
add_action( 'save_post_folktale', 'hatakiti_save_folktale_meta', 10, 2 );

/**
 * Rebuilds this post's folktale_being taxonomy assignments from its
 * beings(JSON) meta — the taxonomy is a queryable projection of that
 * JSON, not independently edited.
 */
function hatakiti_sync_folktale_being_terms( $post_id ) {
    $json = get_post_meta( $post_id, 'hatakiti_folktale_beings_json', true );
    $beings = json_decode( (string) $json, true );
    if ( ! is_array( $beings ) ) {
        wp_set_object_terms( $post_id, array(), 'folktale_being', false );
        return;
    }

    $term_ids = array();
    foreach ( $beings as $being ) {
        if ( empty( $being['normalized_name'] ) && empty( $being['name'] ) ) {
            continue;
        }
        $normalized = ! empty( $being['normalized_name'] ) ? $being['normalized_name'] : $being['name'];
        $type       = isset( $being['type'] ) ? $being['type'] : '';
        $term_id    = hatakiti_get_or_create_being_term( $normalized, $type );
        if ( $term_id ) {
            $term_ids[] = $term_id;
        }
    }

    wp_set_object_terms( $post_id, array_unique( $term_ids ), 'folktale_being', false );
}
