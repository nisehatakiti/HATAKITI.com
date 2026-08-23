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
                    $activity_date     = get_post_meta( get_the_ID(), 'hatakiti_activity_date', true );
                    $activity_date_end = get_post_meta( get_the_ID(), 'hatakiti_activity_date_end', true );
                    $activity_range    = hatakiti_format_date_range( $activity_date, $activity_date_end );
                    if ( $activity_range ) {
                        printf( '<time datetime="%s">%s</time>', esc_attr( $activity_date ), esc_html( $activity_range ) );
                    } else {
                        printf( '<time datetime="%s">%s</time>', esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ) );
                    }
                    $types = get_the_terms( get_the_ID(), 'activity_type' );
                    if ( $types && ! is_wp_error( $types ) ) {
                        echo ' ・ ' . esc_html( implode( ' / ', wp_list_pluck( $types, 'name' ) ) );
                    }
                    $categories = get_the_terms( get_the_ID(), 'activity_category' );
                    if ( $categories && ! is_wp_error( $categories ) ) {
                        echo ' ・ ' . esc_html( implode( ' / ', wp_list_pluck( $categories, 'name' ) ) );
                    }
                    ?>
                </div>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="hk-article-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
            <?php endif; ?>

            <?php
            $direction = get_post_meta( get_the_ID(), 'hatakiti_direction', true );
            $script    = get_post_meta( get_the_ID(), 'hatakiti_script', true );
            if ( $direction || $script ) :
                ?>
                <div class="hk-record-box">
                    <dl>
                        <?php if ( $direction ) : ?>
                            <dt>演出</dt><dd><?php echo esc_html( $direction ); ?></dd>
                        <?php endif; ?>
                        <?php if ( $script ) : ?>
                            <dt>脚本</dt><dd><?php echo esc_html( $script ); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
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
