<?php
/**
 * "HATAKITIの本棚" — used automatically for the Page with slug `bookshelf`
 * (WordPress template hierarchy: page-{slug}.php). Reads live from Booklog
 * (see inc/booklog.php) rather than storing books in WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$hk_booklog_user = hatakiti_get_booklog_user();
$hk_booklog_url  = $hk_booklog_user ? 'https://booklog.jp/users/' . rawurlencode( $hk_booklog_user ) : '';
$hk_books        = hatakiti_get_booklog_books( 24 );
?>
<main id="main" class="hk-container">
    <div class="hk-archive-header">
        <h1>HATAKITIの本棚</h1>
        <p>本棚はブクログで管理しています。</p>
        <?php if ( $hk_booklog_url ) : ?>
            <p><a class="hk-btn" href="<?php echo esc_url( $hk_booklog_url ); ?>" target="_blank" rel="noopener">ブクログの本棚を見る &rarr;</a></p>
        <?php endif; ?>
    </div>

    <?php if ( $hk_books ) : ?>
        <div class="hk-book-grid">
            <?php foreach ( $hk_books as $hk_book ) : ?>
                <a class="hk-book-card" href="<?php echo esc_url( $hk_book['url'] ); ?>" target="_blank" rel="noopener">
                    <?php if ( $hk_book['image'] ) : ?>
                        <div class="hk-book-cover"><img src="<?php echo esc_url( $hk_book['image'] ); ?>" alt="" loading="lazy"></div>
                    <?php endif; ?>
                    <div class="hk-book-title"><?php echo esc_html( $hk_book['title'] ); ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <?php hatakiti_coming_soon( '本棚の情報を取得できませんでした。上のリンクからブクログの本棚をご覧ください。' ); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
