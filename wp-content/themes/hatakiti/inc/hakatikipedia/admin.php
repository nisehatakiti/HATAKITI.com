<?php
/**
 * HATAKITIpedia admin diagnostics.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hakatikipedia_register_admin_page() {
    add_menu_page(
        'HATAKITIpedia診断',
        'HATAKITIpedia',
        'manage_options',
        'hakatikipedia-diagnostics',
        'hakatikipedia_render_admin_page',
        'dashicons-book-alt',
        30
    );
}
add_action( 'admin_menu', 'hakatikipedia_register_admin_page' );

function hakatikipedia_run_admin_diagnostic() {
    $categories = hakatikipedia_get_registered_categories();
    $validation = hakatikipedia_validate_category_map( $categories );
    hakatikipedia_log_category_validation( $validation );
    return $validation;
}

function hakatikipedia_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'このページを表示する権限がありません。', 'hatakiti' ) );
    }

    $validation = hakatikipedia_run_admin_diagnostic();
    ?>
    <div class="wrap">
        <h1>HATAKITIpedia 分類マッピング診断</h1>
        <?php if ( $validation['ok'] ) : ?>
            <div class="notice notice-success"><p><strong>正常です。</strong> 101カテゴリの分類マッピングに問題はありません。</p></div>
        <?php else : ?>
            <div class="notice notice-error"><p><strong>分類マッピングに問題があります。</strong> 未登録・重複・未使用マッピングを確認してください。</p></div>
        <?php endif; ?>

        <table class="widefat striped" style="max-width:900px;margin:20px 0">
            <tbody>
                <tr><td>実カテゴリ数</td><td><strong><?php echo esc_html( $validation['category_count'] ); ?></strong></td></tr>
                <tr><td>マッピング数</td><td><strong><?php echo esc_html( $validation['mapped_count'] ); ?></strong></td></tr>
                <tr><td>大分類数</td><td><strong><?php echo esc_html( $validation['major_count'] ); ?></strong></td></tr>
                <tr><td>未登録カテゴリ</td><td><?php echo esc_html( count( $validation['missing'] ) ); ?></td></tr>
                <tr><td>重複カテゴリ</td><td><?php echo esc_html( count( $validation['duplicates'] ) ); ?></td></tr>
                <tr><td>未使用マッピング</td><td><?php echo esc_html( count( $validation['orphan'] ) ); ?></td></tr>
                <tr><td>空の大分類</td><td><?php echo esc_html( count( $validation['empty_major'] ) ); ?></td></tr>
                <tr><td>未知の大分類</td><td><?php echo esc_html( count( $validation['unknown_major'] ) ); ?></td></tr>
            </tbody>
        </table>

        <h2>大分類別カテゴリ数</h2>
        <table class="widefat striped" style="max-width:900px">
            <thead><tr><th>大分類</th><th>カテゴリ数</th></tr></thead>
            <tbody>
            <?php foreach ( $validation['major_counts'] as $major => $count ) : ?>
                <tr><td><?php echo esc_html( $major ); ?></td><td><?php echo esc_html( $count ); ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        $sections = array(
            '未登録カテゴリ' => $validation['missing'],
            '未使用マッピング' => $validation['orphan'],
            '空の大分類' => $validation['empty_major'],
            '未知の大分類' => $validation['unknown_major'],
        );
        foreach ( $sections as $title => $items ) :
        ?>
            <?php if ( ! empty( $items ) ) : ?>
                <h2 style="margin-top:28px"><?php echo esc_html( $title ); ?></h2>
                <ul>
                    <?php foreach ( $items as $item ) : ?>
                        <li><code><?php echo esc_html( is_array( $item ) ? wp_json_encode( $item, JSON_UNESCAPED_UNICODE ) : $item ); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ( ! empty( $validation['duplicates'] ) ) : ?>
            <h2 style="margin-top:28px">重複カテゴリ</h2>
            <table class="widefat striped" style="max-width:900px">
                <thead><tr><th>カテゴリ</th><th>登録先</th></tr></thead>
                <tbody>
                <?php foreach ( $validation['duplicates'] as $category => $majors ) : ?>
                    <tr>
                        <td><code><?php echo esc_html( $category ); ?></code></td>
                        <td><?php echo esc_html( implode( ' / ', $majors ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}
