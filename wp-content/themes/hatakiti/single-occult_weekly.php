<?php
/**
 * Single 週刊オカルト新聞 issue.
 *
 * Newspaper hierarchy:
 *   headline -> full article body -> compact source line.
 * Article length is determined by the AI editorial tier; this template
 * deliberately does not truncate article bodies on the published page.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <style>
        /* 週刊オカルト新聞: article / source hierarchy */
        .hk-occult-article .hk-record-box {
            padding: 28px 30px 24px;
        }
        .hk-occult-article--large .hk-record-box {
            padding: 34px 36px 28px;
            border-color: var(--hk-accent-warm);
        }
        .hk-occult-article--large .hk-record-box > h3 {
            font-family: var(--hk-font-serif);
            font-size: 30px;
            line-height: 1.45;
            margin: 0 0 22px;
        }
        .hk-occult-article--medium .hk-record-box > h4 {
            font-family: var(--hk-font-serif);
            font-size: 22px;
            line-height: 1.55;
            margin: 0 0 18px;
        }
        .hk-occult-article .hk-article-body {
            font-size: 17px;
            line-height: 2.05;
            color: var(--hk-fg);
        }
        .hk-occult-article--large .hk-article-body {
            font-size: 18px;
            line-height: 2.1;
        }
        .hk-occult-article .hk-article-body p {
            margin: 0 0 1.25em;
        }
        .hk-occult-source {
            margin-top: 22px;
            padding-top: 12px;
            border-top: 1px dotted var(--hk-border);
            font-size: 11px;
            line-height: 1.7;
            color: var(--hk-fg-faint);
        }
        .hk-occult-source a {
            color: var(--hk-fg-faint);
            text-decoration: underline;
            text-underline-offset: 2px;
        }
        .hk-occult-source a:hover {
            color: var(--hk-accent-warm);
        }
        .hk-occult-small-item .hk-record-title {
            font-family: var(--hk-font-serif);
            font-size: 18px;
            line-height: 1.55;
            margin-bottom: 10px;
        }
        .hk-occult-small-item .hk-card-excerpt {
            font-size: 15px;
            line-height: 1.95;
            color: var(--hk-fg);
            margin-bottom: 10px;
        }
        .hk-occult-small-item .hk-occult-source {
            margin-top: 10px;
        }
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
        .hk-tile--occult {
            border-color: rgba(232,163,61,0.55);
        }
        .hk-tile--occult .hk-tile-label {
            color: var(--hk-accent-warm);
        }
        @media (max-width: 700px) {
            .hk-occult-article--large .hk-record-box {
                padding: 24px 20px 22px;
            }
            .hk-occult-article .hk-record-box {
                padding: 22px 20px 20px;
            }
            .hk-occult-article--large .hk-record-box > h3 {
                font-size: 25px;
            }
            .hk-occult-article--medium .hk-record-box > h4 {
                font-size: 20px;
            }
            .hk-occult-article .hk-article-body,
            .hk-occult-article--large .hk-article-body {
                font-size: 16px;
            }
            .hk-occult-front-card {
                align-items: stretch;
                flex-direction: column;
                padding: 24px 20px;
            }
        }
    </style>
    <?php while ( have_posts() ) : the_post(); ?>
        <?php
        $post_id    = get_the_ID();
        $week_start = get_post_meta( $post_id, 'hatakiti_occult_week_start', true );
        $week_end   = get_post_meta( $post_id, 'hatakiti_occult_week_end', true );
        $issue_date = get_post_meta( $post_id, 'hatakiti_occult_issue_date', true );
        $issue_id   = get_post_meta( $post_id, 'hatakiti_occult_issue_id', true );
        $editorial_summary = get_post_meta( $post_id, 'hatakiti_occult_editorial_summary', true );
        $articles   = hatakiti_json_meta( $post_id, 'hatakiti_occult_articles_json' );

        $tiers = array( 'large' => array(), 'medium' => array(), 'small' => array() );
        foreach ( $articles as $article ) {
            $tier = isset( $article['tier'] ) && isset( $tiers[ $article['tier'] ] ) ? $article['tier'] : 'small';
            $tiers[ $tier ][] = $article;
        }

        $all_sources = array(); // dedup by URL
        foreach ( $articles as $article ) {
            foreach ( (array) $article['news_item_ids'] as $item_id ) {
                $url = get_post_meta( $item_id, 'hatakiti_occult_original_url', true );
                if ( ! $url ) {
                    continue;
                }
                $all_sources[ $url ] = array(
                    'name'  => get_post_meta( $item_id, 'hatakiti_occult_source_name', true ),
                    'title' => get_the_title( $item_id ),
                    'url'   => $url,
                );
            }
        }
        ?>
        <article class="hk-article">
            <header class="hk-article-header">
                <div class="hk-card-type">週刊オカルト新聞</div>
                <h1><?php the_title(); ?></h1>
                <div class="hk-article-meta">
                    <?php if ( $issue_id ) : ?><code><?php echo esc_html( $issue_id ); ?></code> ・ <?php endif; ?>
                    <?php if ( $week_start && $week_end ) : ?>
                        対象期間: <?php echo esc_html( $week_start ); ?> ～ <?php echo esc_html( $week_end ); ?>
                    <?php endif; ?>
                    <?php if ( $issue_date ) : ?> ・ 発行日: <?php echo esc_html( $issue_date ); ?><?php endif; ?>
                </div>
                <p class="hk-occult-subtitle">HATAKITI OCCULT WEEKLY</p>
                <?php if ( $articles ) : ?>
                    <p class="hk-occult-pdf-link">
                        <a href="<?php echo esc_url( add_query_arg( 'hatakiti_pdf', '1', get_permalink() ) ); ?>">紙面PDF版を見る</a>
                    </p>
                <?php endif; ?>
            </header>

            <?php if ( $tiers['large'] ) : ?>
                <h2 class="hk-review-heading">今週の大見出し</h2>
                <?php foreach ( $tiers['large'] as $article ) : ?>
                    <section class="hk-occult-article hk-occult-article--large">
                        <?php hatakiti_render_occult_article( $article, 'large' ); ?>
                    </section>
                <?php endforeach; ?>
                <?php hatakiti_render_divider(); ?>
            <?php endif; ?>

            <?php if ( $tiers['medium'] ) : ?>
                <h2 class="hk-review-heading">今週の注目情報</h2>
                <div class="hk-occult-grid">
                    <?php foreach ( $tiers['medium'] as $article ) : ?>
                        <section class="hk-occult-article hk-occult-article--medium">
                            <?php hatakiti_render_occult_article( $article, 'medium' ); ?>
                        </section>
                    <?php endforeach; ?>
                </div>
                <?php hatakiti_render_divider(); ?>
            <?php endif; ?>

            <?php if ( $tiers['small'] ) : ?>
                <h2 class="hk-review-heading">その他の奇妙な話</h2>
                <ul class="hk-record-list hk-record-list--grid">
                    <?php foreach ( $tiers['small'] as $article ) : ?>
                        <li class="hk-occult-small-item">
                            <div class="hk-record-info">
                                <div class="hk-record-title"><?php echo esc_html( $article['headline'] ); ?></div>
                                <?php if ( ! empty( $article['body'] ) ) : ?>
                                    <div class="hk-card-excerpt"><?php echo wpautop( esc_html( $article['body'] ) ); ?></div>
                                <?php endif; ?>
                                <div class="hk-occult-source"><?php hatakiti_render_occult_sources( $article ); ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php hatakiti_render_divider(); ?>
            <?php endif; ?>

            <?php if ( ! $articles ) : ?>
                <?php hatakiti_coming_soon( 'この号はまだ記事が編集されていません。' ); ?>
            <?php endif; ?>

            <?php if ( $all_sources ) : ?>
                <h2 class="hk-review-heading">出典一覧</h2>
                <ul class="hk-record-list">
                    <?php foreach ( $all_sources as $source ) : ?>
                        <li>
                            <div class="hk-record-info">
                                <div class="hk-record-title"><a href="<?php echo esc_url( $source['url'] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $source['name'] . '：' . $source['title'] ); ?></a></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php hatakiti_render_divider(); ?>
            <?php endif; ?>

            <?php if ( $editorial_summary ) : ?>
                <h2 class="hk-review-heading">編集後記</h2>
                <div class="hk-article-body"><?php echo wpautop( wp_kses_post( $editorial_summary ) ); ?></div>
                <?php hatakiti_render_divider(); ?>
            <?php endif; ?>

            <p class="hk-credit-badge">
                本ページは複数の公開情報をAIおよびHATAKITIが整理・編集したものです。元記事本文の転載を目的とせず、掲載内容の真偽を保証するものではありません。文責：チャッピー
            </p>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
