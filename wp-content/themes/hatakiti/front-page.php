<?php
/**
 * Front page: logo, nav (from header.php), intro, latest 3, content
 * entrances, weekly occult newspaper, StageArt teaser, footer.
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

    <section class="hk-section hk-occult-front" id="occult-weekly">
        <?php
        $hk_occult_archive = get_post_type_archive_link( 'occult_weekly' );
        $hk_occult_latest  = new WP_Query( array(
            'post_type'           => 'occult_weekly',
            'post_status'         => 'publish',
            'posts_per_page'      => 1,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
            'meta_key'            => 'hatakiti_occult_issue_date',
            'orderby'             => 'meta_value',
            'order'               => 'DESC',
            // 臨時発行のmanual_testは常にdraftのためpublish状態のこの
            // クエリには出てこないが、多重防御として明示的にも除外する。
            'meta_query'          => hatakiti_occult_weekly_public_meta_query(),
        ) );
        ?>
        <div class="hk-section-head">
            <h2>週刊オカルト新聞</h2>
            <?php if ( $hk_occult_archive ) : ?>
                <a class="hk-more" href="<?php echo esc_url( $hk_occult_archive ); ?>">バックナンバーを見る →</a>
            <?php endif; ?>
        </div>
        <?php if ( $hk_occult_latest->have_posts() ) : $hk_occult_latest->the_post(); ?>
            <div class="hk-occult-front-card">
                <div>
                    <div class="hk-card-type">HATAKITI OCCULT WEEKLY</div>
                    <h3><?php the_title(); ?></h3>
                    <p>UFO、怪異、奇妙な事件、超常現象など、1週間のオカルト関連ニュースをAI編集部が整理し、新聞記事としてお届けします。リンク先を開かなくてもニュースの概要が分かることを重視し、さらに詳しく知りたい方のために出典を掲載します。</p>
                </div>
                <a class="hk-btn" href="<?php the_permalink(); ?>">最新号を読む →</a>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="hk-occult-front-card">
                <div>
                    <div class="hk-card-type">HATAKITI OCCULT WEEKLY</div>
                    <h3>週刊オカルト新聞</h3>
                    <p>1週間のオカルト関連ニュースを、新聞形式でまとめてお届けします。</p>
                </div>
                <?php if ( $hk_occult_archive ) : ?>
                    <a class="hk-btn" href="<?php echo esc_url( $hk_occult_archive ); ?>">オカルト新聞を読む →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
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
            <a class="hk-tile" href="<?php echo esc_url( get_post_type_archive_link( 'film_record' ) ); ?>">
                <span class="hk-tile-label">映画記録</span>
            </a>
            <?php if ( $hk_occult_archive ) : ?>
                <a class="hk-tile hk-tile--occult" href="<?php echo esc_url( $hk_occult_archive ); ?>">
                    <span class="hk-tile-label">週刊オカルト新聞</span>
                </a>
            <?php endif; ?>
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
