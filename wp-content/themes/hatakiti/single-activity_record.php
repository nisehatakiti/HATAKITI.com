<?php
/**
 * Single 活動履歴 (HATAKITI's own activity/performance history).
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
                <div class="hk-card-type">活動履歴</div>
                <h1><?php the_title(); ?></h1>
                <div class="hk-article-meta">
                    <?php
                    $activity_date = get_post_meta( get_the_ID(), 'hatakiti_activity_date', true );
                    if ( $activity_date ) {
                        printf( '<time datetime="%s">%s</time>', esc_attr( $activity_date ), esc_html( $activity_date ) );
                    } else {
                        printf( '<time datetime="%s">%s</time>', esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ) );
                    }
                    $types = get_the_terms( get_the_ID(), 'activity_type' );
                    if ( $types && ! is_wp_error( $types ) ) {
                        echo ' ・ ' . esc_html( implode( ' / ', wp_list_pluck( $types, 'name' ) ) );
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

            <?php
            $related_link = get_post_meta( get_the_ID(), 'hatakiti_related_link', true );
            if ( $related_link ) :
                ?>
                <p class="hk-related-link"><a href="<?php echo esc_url( $related_link ); ?>" target="_blank" rel="noopener">関連リンク &rarr;</a></p>
            <?php endif; ?>

            <?php hatakiti_render_tags( get_the_ID(), true ); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
