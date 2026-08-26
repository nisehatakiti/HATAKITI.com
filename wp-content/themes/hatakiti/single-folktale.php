<?php
/**
 * Single 日本民話 (Japanese folktale). Display order per
 * docs/12-JapaneseFolktale-DataContract.md §20:
 *   title -> region/location/story_type/characters・beings -> divider ->
 *   summary -> divider -> related folktales -> divider -> sources.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main id="main" class="hk-container">
    <?php while ( have_posts() ) : the_post(); ?>
        <?php
        $post_id      = get_the_ID();
        $prefecture   = get_post_meta( $post_id, 'hatakiti_folktale_region_prefecture', true );
        $province     = get_post_meta( $post_id, 'hatakiti_folktale_region_historical_province', true );
        $municipality = get_post_meta( $post_id, 'hatakiti_folktale_region_municipality', true );
        $area_name    = get_post_meta( $post_id, 'hatakiti_folktale_region_area_name', true );
        $region_source = get_post_meta( $post_id, 'hatakiti_folktale_region_source_description', true );

        $region_parts = array_filter( array( $prefecture, $municipality, $area_name ) );
        $region_label = $region_parts ? implode( ' ', $region_parts ) : $province;
        if ( ! $region_label ) {
            $region_label = $region_source;
        }

        $locations = hatakiti_json_meta( $post_id, 'hatakiti_folktale_locations_json' );
        $characters = hatakiti_json_meta( $post_id, 'hatakiti_folktale_characters_json' );
        $beings     = hatakiti_json_meta( $post_id, 'hatakiti_folktale_beings_json' );
        $related    = hatakiti_json_meta( $post_id, 'hatakiti_folktale_related_records_json' );
        $sources    = hatakiti_json_meta( $post_id, 'hatakiti_folktale_sources_json' );
        $ai         = hatakiti_json_meta( $post_id, 'hatakiti_folktale_ai_processing_json' );

        $story_types = get_the_terms( $post_id, 'folktale_story_type' );
        $themes      = get_the_terms( $post_id, 'folktale_theme' );

        $location_names  = wp_list_pluck( array_filter( $locations, function ( $l ) { return ! empty( $l['name'] ); } ), 'name' );
        $character_names = wp_list_pluck( array_filter( $characters, function ( $c ) { return ! empty( $c['name'] ); } ), 'name' );
        $being_names     = array_map( function ( $b ) {
            return ! empty( $b['name'] ) ? $b['name'] : $b['normalized_name'];
        }, array_filter( $beings, function ( $b ) { return ! empty( $b['name'] ) || ! empty( $b['normalized_name'] ); } ) );
        $who_names       = array_merge( $character_names, array_values( $being_names ) );
        ?>
        <article class="hk-article">
            <header class="hk-article-header">
                <div class="hk-card-type">日本民話</div>
                <h1><?php the_title(); ?></h1>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="hk-article-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
            <?php endif; ?>

            <div class="hk-record-box">
                <dl>
                    <?php if ( $region_label ) : ?>
                        <dt>地域</dt><dd><?php echo esc_html( $region_label ); ?></dd>
                    <?php endif; ?>
                    <?php if ( $location_names ) : ?>
                        <dt>場所</dt><dd><?php echo esc_html( implode( ' / ', $location_names ) ); ?></dd>
                    <?php endif; ?>
                    <?php if ( $story_types && ! is_wp_error( $story_types ) ) : ?>
                        <dt>分類</dt><dd><?php echo esc_html( implode( ' / ', wp_list_pluck( $story_types, 'name' ) ) ); ?></dd>
                    <?php endif; ?>
                    <?php if ( $who_names ) : ?>
                        <dt>登場人物・存在</dt><dd><?php echo esc_html( implode( ' / ', $who_names ) ); ?></dd>
                    <?php endif; ?>
                </dl>
            </div>

            <?php if ( $themes && ! is_wp_error( $themes ) ) : ?>
                <ul class="hk-tag-list">
                    <?php foreach ( $themes as $theme ) : ?>
                        <li><a href="<?php echo esc_url( get_term_link( $theme ) ); ?>">#<?php echo esc_html( $theme->name ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php hatakiti_render_divider(); ?>
            <h2 class="hk-review-heading">民話の内容</h2>
            <div class="hk-article-body">
                <?php
                // Deliberately NOT the_content(): post_content may hold the
                // raw research summary/notes, which is internal working
                // text (research status, dedup reasoning, etc.), not a
                // visitor-facing description.
                //
                // story_status決めるのは「内容として何を出せるか」であり、
                // 地域・分類・出典から機械的に作った薄い紹介文はもう使わ
                // ない — 未確認のものは正直に「調査中」と表示する。
                $story_status  = get_post_meta( $post_id, 'hatakiti_folktale_story_status', true );
                $story_summary = get_post_meta( $post_id, 'hatakiti_folktale_public_summary', true );
                $story_content = get_post_meta( $post_id, 'hatakiti_folktale_story_content', true );

                if ( 'content_confirmed' === $story_status && $story_content ) {
                    if ( $story_summary ) {
                        echo '<p><strong>【あらすじ】</strong></p>';
                        echo wpautop( esc_html( $story_summary ) );
                    }
                    echo '<p><strong>【民話の内容】</strong></p>';
                    echo wpautop( esc_html( $story_content ) );
                } elseif ( 'summary_confirmed' === $story_status && $story_summary ) {
                    echo wpautop( esc_html( $story_summary ) );
                } else {
                    echo wpautop( esc_html( $story_summary ? $story_summary : 'この民話は現在、詳しい内容を調査中です。' ) );
                }
                ?>
            </div>
            <?php if ( ! empty( $ai['summary_generated'] ) && in_array( $story_status, array( 'summary_confirmed', 'content_confirmed' ), true ) ) : ?>
                <p class="hk-credit-badge">本ページの内容は、出典資料を参考にAIが整理・要約したものです。文責：チャッピー</p>
            <?php endif; ?>

            <?php hatakiti_render_divider(); ?>
            <h2 class="hk-review-heading">関連する民話</h2>
            <?php if ( $related ) : ?>
                <ul class="hk-record-list">
                    <?php foreach ( $related as $rel ) : ?>
                        <?php
                        $rel_id = isset( $rel['record_id'] ) ? $rel['record_id'] : '';
                        if ( ! $rel_id ) {
                            continue;
                        }
                        $rel_post = get_posts( array(
                            'post_type'      => 'folktale',
                            'post_status'    => 'any',
                            'meta_key'       => 'hatakiti_folktale_record_id',
                            'meta_value'     => $rel_id,
                            'posts_per_page' => 1,
                            'fields'         => 'ids',
                        ) );
                        ?>
                        <li>
                            <div class="hk-record-info">
                                <div class="hk-record-title">
                                    <?php if ( $rel_post ) : ?>
                                        <a href="<?php echo esc_url( get_permalink( $rel_post[0] ) ); ?>"><?php echo esc_html( get_the_title( $rel_post[0] ) ); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html( $rel_id ); ?> <span class="hk-badge-soon">未登録</span>
                                    <?php endif; ?>
                                </div>
                                <?php $rel_label = hatakiti_folktale_relationship_label( isset( $rel['relationship'] ) ? $rel['relationship'] : '' ); ?>
                                <?php if ( $rel_label ) : ?>
                                    <div class="hk-record-sub"><?php echo esc_html( $rel_label ); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="hk-card-excerpt">関連する民話はまだ登録されていません。</p>
            <?php endif; ?>

            <?php hatakiti_render_divider(); ?>
            <h2 class="hk-review-heading">出典・参考資料</h2>
            <?php if ( $sources ) : ?>
                <ul class="hk-record-list">
                    <?php foreach ( $sources as $source ) : ?>
                        <li>
                            <div class="hk-record-info">
                                <div class="hk-record-title">
                                    <?php if ( ! empty( $source['url'] ) ) : ?>
                                        <a href="<?php echo esc_url( $source['url'] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( isset( $source['title'] ) ? $source['title'] : $source['url'] ); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html( isset( $source['title'] ) ? $source['title'] : '(タイトル不明)' ); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="hk-record-sub">
                                    <?php
                                    $meta_bits = array_filter( array(
                                        isset( $source['publisher'] ) ? $source['publisher'] : '',
                                        isset( $source['author'] ) ? $source['author'] : '',
                                        isset( $source['accessed_date'] ) ? '参照日: ' . $source['accessed_date'] : '',
                                    ) );
                                    echo esc_html( implode( ' ・ ', $meta_bits ) );
                                    ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="hk-card-excerpt">出典情報がありません。</p>
            <?php endif; ?>

            <?php hatakiti_render_tags( $post_id, true ); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
