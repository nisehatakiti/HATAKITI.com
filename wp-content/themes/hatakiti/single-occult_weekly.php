<?php
/**
 * Single 週刊オカルト新聞 issue — PDF-first web entry point.
 *
 * The newspaper's content lives entirely in the generated PDF
 * (occult-weekly-pdf.php, unchanged by this template). This page is
 * deliberately just a landing page pointing to it — issue title / date /
 * article count, then a prominent PDF link. It never re-renders article
 * headlines or bodies as HTML.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <style>
        .hk-occult-pdf-entry {
            padding: 40px 32px;
            border: 1px solid var(--hk-border);
            border-left: 4px solid var(--hk-accent-warm);
            background: var(--hk-bg-elevated);
            text-align: center;
        }
        .hk-occult-pdf-entry .hk-card-type {
            margin-bottom: 8px;
        }
        .hk-occult-pdf-entry h1 {
            font-family: var(--hk-font-serif);
            font-size: 30px;
            margin: 0 0 16px;
        }
        .hk-occult-pdf-entry .hk-article-meta {
            margin-bottom: 26px;
            color: var(--hk-fg-dim);
            line-height: 1.8;
        }
        .hk-occult-pdf-btn {
            display: inline-block;
            padding: 16px 44px;
            font-size: 18px;
        }
        .hk-occult-pdf-pending {
            color: var(--hk-fg-faint);
            font-style: italic;
        }
        .hk-occult-archive-link {
            margin-top: 24px;
        }
        @media (max-width: 700px) {
            .hk-occult-pdf-entry {
                padding: 28px 20px;
            }
            .hk-occult-pdf-entry h1 {
                font-size: 24px;
            }
            .hk-occult-pdf-btn {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
    <?php while ( have_posts() ) : the_post(); ?>
        <?php
        $post_id       = get_the_ID();
        $issue_id      = get_post_meta( $post_id, 'hatakiti_occult_issue_id', true );
        $issue_date    = get_post_meta( $post_id, 'hatakiti_occult_issue_date', true );
        $articles      = hatakiti_json_meta( $post_id, 'hatakiti_occult_articles_json' );
        $article_count = is_array( $articles ) ? count( $articles ) : 0;

        // ローカルにPDFファイルが既に存在するかだけを見る軽い判定 —
        // PDF生成自体はここでは一切行わない（実際の配信は既存の
        // template_redirect/hatakiti_get_occult_weekly_pdf_path()に
        // 委ねる、無変更）。通常運用ではPDF検証を通過した号のみが
        // publishされるため、この「準備中」表示が出ることは想定して
        // いない。
        $pdf_cache_key = get_post_meta( $post_id, 'hatakiti_occult_pdf_cache_key', true );
        $pdf_ready     = $pdf_cache_key && file_exists( hatakiti_occult_pdf_cache_path( $post_id ) );
        $pdf_url       = add_query_arg( 'hatakiti_pdf', '1', get_permalink() );

        $occult_archive_link = get_post_type_archive_link( 'occult_weekly' );
        ?>
        <article class="hk-article">
            <div class="hk-occult-pdf-entry">
                <div class="hk-card-type">週刊オカルト新聞</div>
                <h1><?php the_title(); ?></h1>
                <div class="hk-article-meta">
                    <?php if ( $issue_id ) : ?><code><?php echo esc_html( $issue_id ); ?></code><br><?php endif; ?>
                    <?php if ( $issue_date ) : ?>発行日: <?php echo esc_html( $issue_date ); ?><?php endif; ?>
                    <?php if ( $article_count > 0 ) : ?>
                        <br>この号には<?php echo (int) $article_count; ?>本の記事が掲載されています。
                    <?php endif; ?>
                </div>

                <?php if ( $pdf_ready ) : ?>
                    <a class="hk-btn hk-occult-pdf-btn" href="<?php echo esc_url( $pdf_url ); ?>">PDFで読む</a>
                <?php else : ?>
                    <p class="hk-occult-pdf-pending">PDF準備中</p>
                <?php endif; ?>

                <?php if ( $occult_archive_link ) : ?>
                    <p class="hk-occult-archive-link">
                        <a href="<?php echo esc_url( $occult_archive_link ); ?>">過去号一覧を見る →</a>
                    </p>
                <?php endif; ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
