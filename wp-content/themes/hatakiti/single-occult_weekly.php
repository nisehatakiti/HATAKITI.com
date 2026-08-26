<?php
/**
 * Single 週刊オカルト新聞 issue. Layout per docs/07-OccultWeekly.md
 * "新聞レイアウト" §, using 指示書's tier order: 大見出し -> 主要ニュース ->
 * 小記事 -> 出典一覧 -> 文責／注意書き. Every article shows its own
 * sources inline (docs/07: a source list at the very bottom only is
 * explicitly called insufficient) — the bottom 出典一覧 is an additional
 * consolidated view, not a replacement for that.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
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

        $all_sources = array(); // dedup by "name|url"
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
            </header>

            <?php if ( $tiers['large'] ) : ?>
                <h2 class="hk-review-heading">今週の大見出し</h2>
                <?php foreach ( $tiers['large'] as $article ) : ?>
                    <?php hatakiti_render_occult_article( $article, 'large' ); ?>
                <?php endforeach; ?>
                <?php hatakiti_render_divider(); ?>
            <?php endif; ?>

            <?php if ( $tiers['medium'] ) : ?>
                <h2 class="hk-review-heading">今週の注目情報</h2>
                <div class="hk-occult-grid">
                    <?php foreach ( $tiers['medium'] as $article ) : ?>
                        <?php hatakiti_render_occult_article( $article, 'medium' ); ?>
                    <?php endforeach; ?>
                </div>
                <?php hatakiti_render_divider(); ?>
            <?php endif; ?>

            <?php if ( $tiers['small'] ) : ?>
                <h2 class="hk-review-heading">その他の奇妙な話</h2>
                <ul class="hk-record-list hk-record-list--grid">
                    <?php foreach ( $tiers['small'] as $article ) : ?>
                        <li>
                            <div class="hk-record-info">
                                <div class="hk-record-title"><?php echo esc_html( $article['headline'] ); ?></div>
                                <?php if ( ! empty( $article['body'] ) ) : ?>
                                    <div class="hk-card-excerpt"><?php echo esc_html( wp_trim_words( $article['body'], 40 ) ); ?></div>
                                <?php endif; ?>
                                <div class="hk-record-sub"><?php hatakiti_render_occult_sources( $article ); ?></div>
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
                本ページは複数の公開情報をAIおよびHATAKITIが整理・要約したものです。掲載内容の真偽を保証するものではありません。文責：チャッピー
            </p>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
