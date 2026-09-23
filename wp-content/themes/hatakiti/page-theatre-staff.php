<?php
/**
 * Virtual page: 舞台スタッフの仕事
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );

$pages = array(
 'theatre-textbook/staff/lighting' => array(
   'title'=>'照明',
   'lead'=>'光の方向、広がり、明るさ、色、影を設計し、舞台上の時間・場所・人物・空気を観客に伝える仕事。',
 ),
 'theatre-textbook/staff/sound' => array(
   'title'=>'音響',
   'lead'=>'声、SE、BGM、空間の音を設計し、俳優の芝居と観客の体験を音で支える仕事。',
 ),
 'theatre-textbook/staff/stage-management' => array(
   'title'=>'舞台監督',
   'lead'=>'舞台上と舞台裏の進行を整理し、さまざまなスタッフと出演者をつないで本番を成立させる仕事。',
 ),
);

get_header();
?>
<main class="hk-container hk-staff">
<?php if ( 'theatre-textbook/staff' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜舞台を作る</p><h1>舞台スタッフの仕事</h1><p>役者だけでは舞台はできません。照明、音響、舞台監督、美術、制作など、それぞれの専門性が一つの公演を作ります。</p></header>
<section class="hk-section"><div class="hk-staff-grid">
<?php foreach ( $pages as $url => $page ) : ?>
<a href="<?php echo esc_url( home_url( '/'.$url.'/' ) ); ?>" class="hk-staff-card"><h2><?php echo esc_html( $page['title'] ); ?></h2><p><?php echo esc_html( $page['lead'] ); ?></p><span>詳しく見る →</span></a>
<?php endforeach; ?>
<div class="hk-staff-card"><h2>舞台美術</h2><p>舞台空間、装置、素材、転換などを設計し、物理的な「世界」を作ります。</p></div>
<div class="hk-staff-card"><h2>制作</h2><p>企画、予算、広報、チケット、劇場との調整など、公演を成立させる仕組みを担当します。</p></div>
<div class="hk-staff-card"><h2>衣裳・ヘアメイク</h2><p>人物の時代、職業、関係性、変化を視覚的に支えます。</p></div>
<div class="hk-staff-card"><h2>小道具</h2><p>俳優が使う物を用意し、場面のリアリティや演出上の意味を支えます。</p></div>
</div></section>
<?php elseif ( isset( $pages[$path] ) && 'theatre-textbook/staff/lighting' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜舞台を作る</p><h1>照明</h1><p><?php echo esc_html($pages[$path]['lead']); ?></p></header>

<section class="hk-section"><div class="hk-section-head"><h2>まず覚える4つ</h2></div><div class="hk-four">
<div><b>光源</b><p>何から光を出すか。従来のハロゲン器具からLEDまで、器具によって光の性質や扱いが変わります。</p></div>
<div><b>方向</b><p>前、横、後ろ、上など、光の方向で顔・身体・空間の見え方が変わります。</p></div>
<div><b>広がり</b><p>一点を狙うのか、広いエリアを柔らかく照らすのか。レンズや器具を使い分けます。</p></div>
<div><b>色</b><p>フィルターやLEDの色を使って、時間・場所・温度・心理的な印象を作ります。</p></div>
</div></section>

<section class="hk-section"><div class="hk-section-head"><h2>代表的な器具</h2></div>
<div class="hk-equipment">
<article><h3>凸（平凸レンズスポット）</h3><p>比較的輪郭のはっきりした光を作りやすいスポット。舞台ではC8など、レンズ口径をインチで表す呼び方もあります。</p><p class="hk-tip">向いている考え方：<strong>「ここを狙って照らしたい」</strong></p></article>
<article><h3>フレネル</h3><p>フレネルレンズを使ったスポット。凸に比べて光の境界を柔らかくしやすく、エリアをふんわり照らす用途にも使いやすい器具です。</p><p class="hk-tip">向いている考え方：<strong>「この辺りを自然につなげたい」</strong></p></article>
<article><h3>PAR</h3><p>レンズと反射鏡を一体化したPAR型器具など。配光の種類や用途を確認して使います。</p></article>
<article><h3>エリスポット</h3><p>レンズによる比較的明確な光を作り、カッターやゴボなどで光を成形できるタイプ。機種によって機能が異なります。</p></article>
<article><h3>LED照明</h3><p>色を電子的に変えられる器具など、現在の舞台では多様なLED器具が使われています。機種ごとの仕様確認が重要です。</p></article>
</div></section>

<section class="hk-section"><div class="hk-section-head"><h2>ゼラ・カラーフィルター</h2></div>
<div class="hk-panel"><p>「ゼラ」は舞台照明で使われるカラーフィルターを指す通称として広く使われます。現在は製品素材も多様で、メーカーごとに名称・番号体系があります。</p><div class="hk-warning"><strong>番号はメーカーと製品体系をセットで覚える。</strong><br>たとえばRoscoにはRoscolux、Supergel、e-colour+など複数の体系があります。同じ数字を「舞台照明共通の色番号」と考えないことが大切です。</div>
<table class="hk-filter-table"><thead><tr><th>見る項目</th><th>教材で覚えること</th></tr></thead><tbody>
<tr><td>メーカー</td><td>Rosco、LEEなど</td></tr>
<tr><td>シリーズ</td><td>どの製品体系の番号なのか</td></tr>
<tr><td>番号</td><td>製品カタログ上の番号</td></tr>
<tr><td>色名</td><td>色名だけでなく実際の透過光を見る</td></tr>
<tr><td>用途</td><td>人物、背景、時間、季節、心理など何を表現するか</td></tr>
</tbody></table></div></section>

<section class="hk-section"><div class="hk-section-head"><h2>仕込みから本番まで</h2></div><div class="hk-steps"><span>器具・回路を確認</span><b>→</b><span>吊り込み</span><b>→</b><span>ケーブル・回路</span><b>→</b><span>フォーカス</span><b>→</b><span>明かり合わせ</span><b>→</b><span>場当たり</span><b>→</b><span>本番</span></div></section>

<section class="hk-section"><div class="hk-section-head"><h2>5分照明エチュード</h2></div><div class="hk-exercise"><h3>同じ人物を、3つの光で見せる</h3><ol><li>まず正面から普通に照らした人物を見る。</li><li>横からの光に変えて、顔や身体の影を観察する。</li><li>後ろからの光に変えて、輪郭がどう変わるかを見る。</li><li>「安心」「不安」「孤独」のどれかを、光の方向・強さ・色だけで表現する。</li></ol><p>ポイントは「きれいな照明を作る」ことではなく、<strong>光を変えると観客の読み方がどう変わるか</strong>を考えること。</p></div></section>

<?php elseif ( isset( $pages[$path] ) && 'theatre-textbook/staff/sound' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜舞台を作る</p><h1>音響</h1><p><?php echo esc_html($pages[$path]['lead']); ?></p></header>
<section class="hk-section"><div class="hk-section-head"><h2>音響スタッフが扱うもの</h2></div><div class="hk-four">
<div><b>マイク</b><p>声や音を電気信号に変える入口。種類や設置方法で拾い方が変わります。</p></div>
<div><b>ミキサー</b><p>複数の音をまとめ、レベルや音質などを調整します。</p></div>
<div><b>スピーカー</b><p>客席へ音を届ける出口。配置や向きも重要です。</p></div>
<div><b>SE・BGM</b><p>音楽だけでなく、足音、ドア、環境音なども演出材料になります。</p></div>
</div></section>
<section class="hk-section"><div class="hk-section-head"><h2>まず覚える音響用語</h2></div><div class="hk-term-grid">
<div><h3>ゲイン</h3><p>入力段で信号をどの程度扱うかを調整する考え方。単純な「客席の音量」と同じではありません。</p></div>
<div><h3>フェーダー</h3><p>各チャンネルなどのレベルを操作するためのコントロール。</p></div>
<div><h3>EQ</h3><p>周波数帯域ごとのバランスを調整し、音色や聞こえ方を整えます。</p></div>
<div><h3>ハウリング</h3><p>マイクがスピーカーから出た音を再び拾うなどして、特定周波数が増幅される現象。配置やゲインなど複数の要因を確認します。</p></div>
</div></section>
<section class="hk-section"><div class="hk-section-head"><h2>音響プランを考える</h2></div><div class="hk-panel"><p>「音を入れる」こと自体が目的ではありません。観客に何を感じ、何を想像してほしいのかから逆算します。</p><ul><li>現実音として聞かせる</li><li>時間・場所を示す</li><li>心理を補助する</li><li>場面転換をつなぐ</li><li>あえて無音にする</li></ul></div></section>
<section class="hk-section"><div class="hk-section-head"><h2>5分音響エチュード</h2></div><div class="hk-exercise"><h3>「待つ」に音をつける</h3><ol><li>俳優が何もせず30秒待つ。</li><li>時計の音だけを加える。</li><li>遠くの環境音を加える。</li><li>BGMを加える。</li><li>最後に全部なくして無音にする。</li></ol><p>同じ演技でも、音によって観客が受け取る時間感覚や心理が変わることを確認します。</p></div></section>

<?php elseif ( isset( $pages[$path] ) ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜舞台を作る</p><h1>舞台監督</h1><p><?php echo esc_html($pages[$path]['lead']); ?></p></header>
<section class="hk-section"><div class="hk-section-head"><h2>公演を動かす</h2></div><div class="hk-panel"><p>舞台監督の仕事は劇場や公演形態によって異なりますが、稽古から仕込み、場当たり、ゲネプロ、本番まで、舞台進行に関わる多くの情報を整理し、関係者をつなぎます。</p></div></section>
<section class="hk-section"><div class="hk-section-head"><h2>キューを理解する</h2></div><div class="hk-steps"><span>芝居</span><b>→</b><span>照明キュー</span><b>→</b><span>音響キュー</span><b>→</b><span>転換</span><b>→</b><span>次の場面</span></div></section>
<section class="hk-section"><div class="hk-section-head"><h2>5分舞台監督エチュード</h2></div><div class="hk-exercise"><h3>開演5分前</h3><p>「開演5分前。しかし出演者1人がまだ舞台袖に来ていない」という状況を設定します。</p><ol><li>まず何を確認するか。</li><li>誰に連絡するか。</li><li>代替案が必要か。</li><li>照明・音響・受付など、誰に何を伝えるか。</li></ol><p>正解を一つに決めるのではなく、<strong>状況を整理して優先順位をつける</strong>練習です。</p></div></section>
<?php endif; ?>
</main>
<style>
.hk-staff-hero{max-width:820px;margin:64px auto;padding:0 20px;text-align:center}.hk-staff-hero h1{font-family:var(--hk-font-serif);font-size:38px}.hk-staff-hero>p:last-child{color:var(--hk-fg-dim);line-height:2}
.hk-staff-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}.hk-staff-card,.hk-panel,.hk-four>div,.hk-equipment article,.hk-term-grid>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:24px;color:var(--hk-fg)}.hk-staff-card:hover{border-color:var(--hk-accent-warm);text-decoration:none}.hk-staff-card p,.hk-panel p,.hk-four p,.hk-equipment p,.hk-term-grid p{color:var(--hk-fg-dim);line-height:1.9}.hk-staff-card span,.hk-tip{color:var(--hk-accent-warm)}
.hk-four{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.hk-four b{font-family:var(--hk-font-serif);font-size:18px}.hk-equipment{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.hk-equipment h3,.hk-term-grid h3{font-family:var(--hk-font-serif);margin-top:0}.hk-warning{margin:20px 0;padding:18px;border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card);line-height:1.8}.hk-filter-table{width:100%;border-collapse:collapse}.hk-filter-table th,.hk-filter-table td{border:1px solid var(--hk-border);padding:12px;text-align:left}.hk-filter-table th{color:var(--hk-accent-warm)}.hk-steps{display:flex;justify-content:center;align-items:center;gap:10px;flex-wrap:wrap;border:1px solid var(--hk-border);padding:24px;background:var(--hk-bg-elevated)}.hk-steps b{color:var(--hk-accent-warm)}.hk-term-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.hk-exercise{border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card);padding:24px;line-height:1.9}.hk-exercise li{margin:8px 0}
@media(max-width:850px){.hk-four{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.hk-staff-hero h1{font-size:30px}.hk-staff-grid,.hk-equipment,.hk-term-grid,.hk-four{grid-template-columns:1fr}.hk-steps{justify-content:flex-start}}
</style>
<?php get_footer(); ?>