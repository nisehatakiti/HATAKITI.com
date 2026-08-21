<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<form role="search" method="get" class="hk-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label class="hk-screen-reader-text" for="hk-search-field"><?php esc_html_e( '検索', 'hatakiti' ); ?></label>
    <input type="search" id="hk-search-field" name="s" placeholder="<?php esc_attr_e( '観劇記録・記事を検索', 'hatakiti' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
    <button type="submit"><?php esc_html_e( '検索', 'hatakiti' ); ?></button>
</form>
