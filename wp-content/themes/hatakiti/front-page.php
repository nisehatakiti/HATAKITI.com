<?php
/**
 * Front page: logo, nav (from header.php), intro, latest 3, content
 * entrances, StageArt teaser, footer.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="main" class="hk-container">
    <style>
        .hk-occult-front-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;
            padding: 30px 32px;
            border: 1px solid var(--hk-border);
            border-left: 4px solid var(--hk-accent-warm);
            background: var(--hk-bg-elevated);
        }
        .hk-occult-front-card h3 {
            font-family: var(--hk-font-serif);
            font-size: 24px;
            margin-bottom: 10px;
        }
        .hk-occult-front-card p {
            margin: 0;
            color: var(--hk-fg-dim);
            line-height: 1.9;
        }
        .hk-occult-front-card .hk-btn {
            flex-shrink: 0;
        }
        .hk-tile--occult {
            border-color: rgba(232,163,61,0.55);
        }
        .hk-tile--occult .hk-tile-label {
            color: var(--hk-accent-warm);
        }
        @media (max-width: 700px) {
            .hk-occult-front-card {
                align-items: stretch;
                flex-direction: column;
                padding: 24px 20px;
            }
        }
    </style>

    <section class="hk-intro">
        <?php foreach ( hatakiti_get_intro_lines() as $line ) : ?>
            <p><?php echo esc_html( $line ); ?></p>
        <?php endforeach; ?>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>最新記事</h2>
        </div>
        <?php
        $hk_latest = hatakiti_latest_content_query( 3 );
        if ( $hk_latest->have_posts() ) :
            ?>
            <div class="hk-card-grid">
                <?php
                while ( $hk_latest->have_posts() ) :
                    $hk_latest->the_post();
                    hatakiti_render_card( get_the_ID() );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        <?php else : ?>
            <?php hatakiti_coming_soon( 'まだ記事がありません。' ); ?>
        <?php endif; ?>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>コンテンツ</h2>
        </div>
        <div class="hk-tile-grid">
            <a class="hk-tile" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">
                <span class="hk-tile-label">HATAKITIとは</span>
            </a>
            <a class="hk-tile" href="<?php echo esc_url( home_url( '/category/' . HATAKITI_CAT_NIKKI . '/' ) ); ?>">
                <span class="hk-tile-label">日々の所感</span>
            </a>
            <a class="hk-tile" href="<?php echo esc_url( home_url( '/category/' . HATAKITI_CAT_ENGEKI . '/' ) ); ?>">
                <span class="hk-tile-label">演劇について</span>
            </a>
            <a class="hk-tile" href="<?php echo esc_url( get_post_type_archive_link( 'theatre_record' ) ); ?>">
                <span class="hk-tile-label">観劇記録</span>
            </a>
            <a class="hk-tile" href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">
                <span class="hk-tile-label">演劇の教科書</span>
            </a>
            <a class="hk-tile" href="<?php echo esc_url( get_post_type_archive_link( 'film_record' ) ); ?>">
                <span class="hk-tile-label">映画記録</span>
            </a>

        </div>
    </section>

    <section class="hk-section" id="stageart">
        <?php
        $hk_stageart_url = hatakiti_get_stageart_url();
        ?>
        <div class="hk-stageart">
            <div>
                <h3>StageArt</h3>
                <p>HATAKITIが制作している、演劇に関するもう一つの活動です。</p>
            </div>
            <?php if ( $hk_stageart_url ) : ?>
                <a class="hk-btn" href="<?php echo esc_url( $hk_stageart_url ); ?>" target="_blank" rel="noopener">StageArtを見る</a>
            <?php else : ?>
                <span class="hk-badge-soon">Coming Soon</span>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php
get_footer();
