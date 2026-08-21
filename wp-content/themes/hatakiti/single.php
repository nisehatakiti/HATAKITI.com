<?php
/**
 * Single post template — covers both 日々の所感 and 演劇について,
 * which are ordinary WordPress posts distinguished by category
 * (see inc/template-tags.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <?php while ( have_posts() ) : the_post(); ?>
        <article class="hk-article">
            <header class="hk-article-header">
                <div class="hk-card-type"><?php echo esc_html( hatakiti_content_type_label() ); ?></div>
                <h1><?php the_title(); ?></h1>
                <div class="hk-article-meta">
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
                    <?php
                    $cats = get_the_category();
                    if ( $cats ) {
                        echo ' ・ ';
                        $names = array();
                        foreach ( $cats as $cat ) {
                            $names[] = sprintf( '<a href="%s">%s</a>', esc_url( get_category_link( $cat ) ), esc_html( $cat->name ) );
                        }
                        echo wp_kses_post( implode( ' / ', $names ) );
                    }
                    ?>
                </div>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="hk-article-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
            <?php endif; ?>

            <div class="hk-article-body">
                <?php the_content(); ?>
            </div>

            <?php hatakiti_render_credit_badge_if_missing(); ?>
            <?php hatakiti_render_tags(); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
