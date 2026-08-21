<?php
/**
 * Site-wide footer, including the required co-creation credit
 * (docs/03-ContentModel.md §7).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
    <footer class="hk-site-footer">
        <?php if ( has_nav_menu( 'footer' ) ) : ?>
            <nav class="hk-footer-nav">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => '',
                    'depth'          => 1,
                ) );
                ?>
            </nav>
        <?php endif; ?>
        <p class="hk-footer-credit">このページは、友達の少ないHATAKITIが、チャッピー（ChatGPT）とともに作成しています。</p>
    </footer>

<?php wp_footer(); ?>
</body>
</html>
