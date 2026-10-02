<?php
/**
 * Virtual page: 演劇用語集
 *
 * Wiki-style index for theatre terminology used throughout the textbook.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();

$terms = array(
    '演劇' => array('slug'=>'theatre','category'=>'基本','summary'=>'人が人物や出来事を表現し、それを他の人が受け取る表現。劇場の有無だけで演劇を決めることはできません。'),
    '俳優' => array('slug'=>'actor','category'=>'基本','summary'=>'舞台などで人物を演じ、身体や声を使って観客に表現を届ける人。'),
    '観客' => array('slug'=>'audience','category'=>'基本','summary'=>'演劇を見て、聞いて、受け取る人。演じる側との関係の中で舞台が成立します。'),
    '劇場' => array('slug'=>'theatre-building','category'=>'劇場・空間','summary'=>'演劇などを上演し、観客が見るために整えられた空間や建物。時代や地域によって形が異なります。'),
    '祭り' => array('slug'=>'festival','category'=>'古代・儀式','summary'=>'地域や社会で行われる行事。古代の演劇は祭りや社会的行事と深く結びついていました。'),
    '儀式' => array('slug'=>'ritual','category'=>'古代・儀式','summary'=>'宗教や社会において、一定の意味や手順をもって行われる行為。演劇と重なる表現を持つ場合があります。'),
    '悲劇' => array('slug'=>'tragedy','category'=>'ジャンル','summary'=>'古代ギリシャで発展し、人間の選択や苦しみ、運命などを扱った演劇のジャンル。'),
    '喜劇' => array('slug'=>'comedy','category'=>'ジャンル','summary'=>'笑いを通して人間や社会を描く演劇のジャンル。古代ギリシャでも発展しました。'),
    '合唱隊' => array('slug'=>'chorus','category'=>'古代・演技','summary'=>'古代ギリシャ演劇などで、歌や言葉、動きによって作品に参加する集団。'),
    '仮面' => array('slug'=>'mask','category'=>'古代・演技','summary'=>'顔を覆う道具。古代演劇などで人物の表現や視覚的な識別に用いられました。'),
    '演出' => array('slug'=>'direction','category'=>'近代・演出','summary'=>'作品を舞台上でどのように成立させるかを考え、俳優、空間、時間、音、光などをまとめる仕事や考え方。'),
    'リアリズム' => array('slug'=>'realism','category'=>'近代・演劇論','summary'=>'現実の人間や生活を舞台上でどう表現するかを重視する近代演劇の重要な考え方。'),
);

$groups = array();
foreach ( $terms as $name => $term ) {
    $groups[ $term['category'] ][ $name ] = $term;
}
ksort( $groups );
?>
<main class="hk-container hk-glossary">
    <header class="hk-textbook-hero">
        <p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜Wiki</p>
        <h1>演劇用語集</h1>
        <p>演劇の歴史、演技、舞台スタッフなどで出てくる言葉を、必要なときに調べられるように整理していきます。</p>
    </header>

    <nav class="hk-chapter-nav" aria-label="演劇用語集ナビゲーション">
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/history/chapter-1/' ) ); ?>">← 演劇の歴史</a>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">演劇の教科書</a>
        <span></span>
    </nav>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>演劇用語を探す</h2>
            <p>用語をクリックすると、その言葉の詳しいページへ移動します。今後、演劇の歴史だけでなく演技・演出・舞台技術の用語も追加していきます。</p>
        </div>
        <label class="hk-glossary-search">
            <span>用語を検索</span>
            <input type="search" id="hk-glossary-search" placeholder="例：劇場、悲劇、リアリズム">
        </label>
    </section>

    <section class="hk-section" id="glossary-list">
        <?php foreach ( $groups as $category => $items ) : ?>
            <div class="hk-glossary-group">
                <h2><?php echo esc_html( $category ); ?></h2>
                <div class="hk-glossary-grid">
                    <?php foreach ( $items as $name => $term ) : ?>
                        <a class="hk-glossary-card" data-term="<?php echo esc_attr( $name . ' ' . $term['summary'] . ' ' . $category ); ?>" href="<?php echo esc_url( home_url( '/theatre-textbook/glossary/' . $term['slug'] . '/' ) ); ?>">
                            <span><?php echo esc_html( $category ); ?></span>
                            <h3><?php echo esc_html( $name ); ?></h3>
                            <p><?php echo esc_html( $term['summary'] ); ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <p id="hk-glossary-empty" hidden>該当する用語はありません。</p>
    </section>
</main>
<style>
.hk-glossary .hk-textbook-hero{max-width:760px;margin:56px auto 48px;text-align:center}
.hk-glossary .hk-textbook-hero h1{font-family:var(--hk-font-serif);font-size:36px}
.hk-glossary .hk-textbook-hero>p:last-child{color:var(--hk-fg-dim);line-height:2}
.hk-glossary .hk-chapter-nav{max-width:900px;margin:0 auto 48px}
.hk-glossary-search{display:block;max-width:700px;margin:0 auto}
.hk-glossary-search span{display:block;font-size:13px;color:var(--hk-fg-dim);margin-bottom:8px}
.hk-glossary-search input{width:100%;box-sizing:border-box;padding:14px 16px;border:1px solid var(--hk-border);border-radius:8px;background:var(--hk-bg-elevated);color:var(--hk-fg);font:inherit}
.hk-glossary-group{margin:0 0 48px}
.hk-glossary-group>h2{font-size:22px;border-bottom:1px solid var(--hk-border);padding-bottom:12px;margin-bottom:18px}
.hk-glossary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.hk-glossary-card{display:block;padding:22px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);color:var(--hk-fg);transition:border-color .2s}
.hk-glossary-card:hover{border-color:var(--hk-accent-warm);text-decoration:none}
.hk-glossary-card>span{font-size:11px;color:var(--hk-accent-warm);letter-spacing:.08em}
.hk-glossary-card h3{font-family:var(--hk-font-serif);font-size:22px;margin:8px 0 10px}
.hk-glossary-card p{margin:0;color:var(--hk-fg-dim);font-size:14px;line-height:1.8}
@media(max-width:900px){.hk-glossary-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.hk-glossary-grid{grid-template-columns:1fr}.hk-glossary .hk-textbook-hero h1{font-size:29px}}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('hk-glossary-search');
    const cards = Array.from(document.querySelectorAll('.hk-glossary-card'));
    const empty = document.getElementById('hk-glossary-empty');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = input.value.trim().toLowerCase();
        let visible = 0;
        cards.forEach(function (card) {
            const match = !q || card.dataset.term.toLowerCase().includes(q);
            card.hidden = !match;
            if (match) visible++;
        });
        empty.hidden = visible !== 0;
    });
});
</script>
<?php get_footer(); ?>