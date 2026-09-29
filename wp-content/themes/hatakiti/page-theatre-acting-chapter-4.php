<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<main class="hk-container hk-acting">
<header class="hk-staff-hero">
<p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜演技をする｜第一部 体</p>
<h1>第4章　身体</h1>
<p>役者にとって身体は、演技をするための道具です。まず自分の身体を知り、感じ、意識して動かせるようにします。</p>
</header>

<nav class="hk-chapter-nav hk-acting-chapter-nav" aria-label="演技をする章ナビゲーション">
<a class="hk-nav-prev" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-3/' ) ); ?>">← 第3章　滑舌</a>
<a class="hk-nav-center" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/' ) ); ?>">目次</a>
<a class="hk-nav-next" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-5/' ) ); ?>">第5章　姿勢 →</a>
</nav>

<section class="hk-section">
<div class="hk-section-head">
<h2>4-1　自分の身体を知る</h2>
<p>まず、自分の身体がどこにあり、どう動いているのかを知ります。</p>
</div>
<p>普段は、身体のすべてを意識して生活しているわけではありません。歩く、座る、手を伸ばすといった動作も、多くは無意識に行っています。</p>
<p>役者は、その無意識に行っている身体を、必要なときに意識して扱えることが大切です。</p>
<p>まずは頭、首、肩、腕、手、胸、背中、腹、骨盤、脚、足というように、自分の身体を部分として認識してみましょう。</p>


<figure class="hk-acting-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/acting/acting-body-07-body-adjustment.svg' ) ); ?>" alt="図説⑦　今日の身体を確認する" loading="lazy"><figcaption>図説⑦　今日の身体を確認する</figcaption></figure><div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-question.png' ) ); ?>" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>昨日できたことが、今日はやりにくいこともあるんだね。</p></div></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head">
<h2>実践エチュード</h2>
<p>ここまでで感じてきた自分の身体を、もう一度最初から確かめてみます。</p>
</div>
<div class="hk-exercise">
<h3>身体スキャンと分離運動　10分</h3>
<ol>
<li>自然に立ち、足裏から頭まで身体を感じる。</li>
<li>力が入っている場所、動かしにくい場所を探す。</li>
<li>肩、腕、手、胸、骨盤、脚などを一つずつ小さく動かす。</li>
<li>一つの部分を動かしたとき、ほかの部分がどう反応するか感じる。</li>
<li>最後に全身をゆっくり動かし、もう一度身体全体を感じる。</li>
</ol>
<p><strong>ポイント：</strong>大きく動くことや柔らかくなることが目的ではありません。自分の身体が今どうなっているのかを知り、意識して扱えるようになることが目的です。</p>
</div>
</section>

<section class="hk-section">
<div class="hk-panel hk-summary">
<h3>第4章まとめ</h3>
<p><strong>身体を知る → 感じる → 力を知る → 部分を動かす → 全身の連動を感じる → 自分の可動域を知る → 身体を調整する。</strong></p>
<p>役者にとって身体は、演技をするための道具です。まず自分の身体を正確に把握し、自分で感じ、自分で動かせるようになることが、姿勢、歩き方、重心、そしてその先の身体表現につながります。</p>
</div>
</section>

<nav class="hk-chapter-nav hk-acting-chapter-nav" aria-label="演技をする章ナビゲーション">
<a class="hk-nav-prev" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-3/' ) ); ?>">← 第3章　滑舌</a>
<a class="hk-nav-center" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/' ) ); ?>">目次</a>
<a class="hk-nav-next" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-5/' ) ); ?>">第5章　姿勢 →</a>
</nav>
</main>
<style>
.hk-acting .hk-staff-hero{max-width:760px;margin:56px auto 48px}
.hk-acting .hk-section{max-width:760px;margin:0 auto 54px}
.hk-acting .hk-section-head{margin-bottom:24px}
.hk-acting .hk-section-head h2{border-bottom:1px solid var(--hk-border);padding-bottom:12px}
.hk-acting p{line-height:1.95}
.hk-acting .hk-panel,.hk-acting .hk-exercise{padding:24px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}
.hk-acting .hk-exercise{border-left:3px solid var(--hk-accent-warm)}
.hk-acting .hk-exercise h3{margin-top:0}
.hk-acting .hk-exercise li{margin-bottom:8px}

.hk-acting .hk-chapter-nav.hk-acting-chapter-nav{width:100%;max-width:900px;margin:28px auto;padding:14px 0;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:16px}
.hk-acting-chapter-nav .hk-nav-prev{justify-self:start}.hk-acting-chapter-nav .hk-nav-center{justify-self:center}.hk-acting-chapter-nav .hk-nav-next{justify-self:end}
@media(max-width:700px){.hk-acting .hk-chapter-nav.hk-acting-chapter-nav{grid-template-columns:1fr;text-align:center}.hk-acting-chapter-nav .hk-nav-prev,.hk-acting-chapter-nav .hk-nav-center,.hk-acting-chapter-nav .hk-nav-next{justify-self:center}}
.hk-acting .hk-acting-figure{max-width:760px;margin:30px auto}.hk-acting .hk-acting-figure img{display:block;width:100%;height:auto;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}.hk-acting .hk-acting-figure figcaption{margin-top:10px;text-align:center;color:var(--hk-fg-dim);font-size:13px}.hk-acting .hk-nyakakichi{display:grid;grid-template-columns:100px minmax(0,1fr);align-items:center;gap:16px;max-width:760px;margin:24px auto 0}.hk-acting .hk-nyakakichi-image img{display:block;width:100px;height:auto}.hk-acting .hk-nyakakichi-question{position:relative;background:#242424;border:1px solid var(--hk-border);border-radius:14px;padding:16px 20px;color:var(--hk-fg)}.hk-acting .hk-nyakakichi-question:before{content:"";position:absolute;left:-12px;top:50%;transform:translateY(-50%);border-top:11px solid transparent;border-bottom:11px solid transparent;border-right:12px solid var(--hk-border)}.hk-acting .hk-nyakakichi-question:after{content:"";position:absolute;left:-10px;top:50%;transform:translateY(-50%);border-top:10px solid transparent;border-bottom:10px solid transparent;border-right:11px solid #242424}.hk-acting .hk-nyakakichi-question p{margin:0;line-height:1.8}@media(max-width:700px){.hk-acting .hk-nyakakichi{grid-template-columns:82px minmax(0,1fr)}.hk-acting .hk-nyakakichi-image img{width:82px}}
</style>
<?php get_footer(); ?
<figure class="hk-acting-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/acting/acting-body-01-body-awareness.svg' ) ); ?>" alt="図説①　まず、自分の身体を知る" loading="lazy"><figcaption>図説①　まず、自分の身体を知る</figcaption></figure><div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-question.png' ) ); ?>" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>普段って、身体のことを全部意識してないよね？</p></div></div>
</section>

<section class="hk-section">
<div class="hk-section-head">
<h2>4-2　身体の感覚を感じる</h2>
<p>見るだけでなく、自分の身体がどう感じているのかを知ります。</p>
</div>
<p>身体を動かす前に、立っているときの足裏、手のひら、肩、背中などにどんな感覚があるのかを感じてみます。</p>
<p>温かい、冷たい、重い、軽い、張っている、ゆるんでいる、触れている、離れている。身体にはさまざまな感覚があります。</p>
<p>演技では、こうした自分自身の身体感覚を知っていることが、身体を意識して扱うための土台になります。</p>
<div class="hk-exercise">
<h3>身体スキャン</h3>
<ol>
<li>目を閉じても安全な場所で、自然に立つ。</li>
<li>足裏から頭まで、身体を順番に意識する。</li>
<li>それぞれの場所にどんな感覚があるかを感じる。</li>
<li>左右で違うところがないか確かめる。</li>
<li>最後に全身の感覚をまとめて感じる。</li>
</ol>

<figure class="hk-acting-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/acting/acting-body-02-body-sensation.svg' ) ); ?>" alt="図説②　身体を頭から足先まで感じてみる" loading="lazy"><figcaption>図説②　身体を頭から足先まで感じてみる</figcaption></figure><div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-question.png' ) ); ?>" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>身体って、見るだけじゃなくて感じるものなんだね。</p></div></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head">
<h2>4-3　身体の力を知る</h2>
<p>どこに力が入っているのか、どこが抜けているのかを把握します。</p>
</div>
<p>身体は、動かしている部分だけに力が入るとは限りません。手を握っただけなのに肩が固くなっていたり、声を出すときに首に力が入っていたりすることがあります。</p>
<p>大切なのは、力が入っていること自体を悪いことにするのではなく、<strong>自分が今どこに力を使っているのかを知ること</strong>です。</p>
<div class="hk-exercise">
<h3>力の場所を探す</h3>
<ol>
<li>手を強く握って、どこに力が入るか感じる。</li>
<li>肩を上げて、首や背中の変化を感じる。</li>
<li>顔を少し緊張させて、顎や首の変化を感じる。</li>
<li>一度力を抜き、変化を比べる。</li>
</ol>
<figure class="hk-acting-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/acting/acting-body-03-body-tension.svg' ) ); ?>" alt="図説③　自分では気づかない力を探す" loading="lazy"><figcaption>図説③　自分では気づかない力を探す</figcaption></figure><div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-question.png' ) ); ?>" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>手を握っただけなのに、肩まで固くなるの？</p></div></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head">
<h2>4-4　身体を部分として動かす</h2>
<p>身体の各部分を意識して、自分で動きを選べるようにします。</p>
</div>
<p>身体を一つの塊として動かすのではなく、頭、肩、腕、手、胸、骨盤、脚など、それぞれの部分を意識して動かしてみます。</p>
<p>最初は大きく動かす必要はありません。小さな動きでも、「今どこを動かしているのか」が分かることが重要です。</p>
<div class="hk-exercise">
<h3>部分を順番に動かす</h3>
<ol>
<li>右肩だけを動かす。</li>
<li>左肩だけを動かす。</li>
<li>右腕、左腕をそれぞれ動かす。</li>
<li>頭、胸、骨盤をそれぞれ動かす。</li>
<li>最後に、複数の部分を組み合わせて動かす。</li>
</ol>
<figure class="hk-acting-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/acting/acting-body-04-body-isolation.svg' ) ); ?>" alt="図説④　今、どこを動かしている？" loading="lazy"><figcaption>図説④　今、どこを動かしている？</figcaption></figure><div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-question.png' ) ); ?>" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>身体って、一緒に動かすものじゃないの？</p></div></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head">
<h2>4-5　身体を連動させる</h2>
<p>部分を知ったら、今度は身体全体がどうつながっているのかを感じます。</p>
</div>
<p>身体の各部分は、それぞれ独立しているわけではありません。腕を動かせば肩や背中も動き、脚を動かせば骨盤や上半身にも変化が起こります。</p>
<p>一つの部分を動かしたとき、身体のほかの部分に何が起きているのかを観察してみましょう。</p>
<div class="hk-exercise">
<h3>連動を感じる</h3>
<ol>
<li>腕をゆっくり上げる。</li>
<li>肩、背中、胸がどう動くか感じる。</li>
<li>脚を一歩前へ出す。</li>
<li>骨盤、背中、腕がどう変化するか感じる。</li>
<li>動き全体を一つの身体として感じる。</li>
</ol>
<figure class="hk-acting-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/acting/acting-body-05-body-connection.svg' ) ); ?>" alt="図説⑤　身体は全部つながっている" loading="lazy"><figcaption>図説⑤　身体は全部つながっている</figcaption></figure>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head">
<h2>4-6　身体の可動域を知る</h2>
<p>自分の身体が、どこまで無理なく動けるのかを知ります。</p>
</div>
<p>腕はどこまで上がるのか。首はどの方向へどこまで動くのか。股関節はどの程度開くのか。</p>
<p>自分の身体の動く範囲を知っておくことは、演技をするときにも大切です。動かせる範囲を知れば、無理な動きを避けながら、必要な動きを選べます。</p>
<p><strong>無理に広げることが目的ではありません。</strong> 痛みが出るところまで動かさず、自分の現在の範囲を知ることから始めます。</p>
</section>

<section class="hk-section">
<div class="hk-section-head">
<h2>4-7　身体を自分で調整する</h2>
<p>自分の身体の状態を知り、必要に応じて整えられるようにします。</p>
</div>
<p>役者の身体は、毎日同じ状態ではありません。疲れている日もあれば、緊張している日もあります。</p>
<p>だからこそ、「今日は肩が固い」「右脚が動かしにくい」「呼吸が浅い」といった身体の変化に自分で気づき、必要なところを動かしたり、力を抜いたりできることが大切です。</p>
<div class="hk-exercise">
<h3>今日の身体を確認する</h3>
<ol>
<li>身体全体を一度感じる。</li>
<li>いつもと違う場所を探す。</li>
<li>その場所を小さく動かしてみる。</li>
<li>必要なら休み、無理をしない。</li>
<li>最後にもう一度、全身の状態を確認する。</li>
</ol>
<figure class="hk-acting-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/acting/acting-body-06-range-of-motion.svg' ) ); ?>" alt="図説⑥　自分の身体は、どこまで動く？" loading="lazy"><figcaption>図説⑥　自分の身体は、どこまで動く？</figcaption></figure><div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-question.png' ) ); ?>" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>いっぱい動かせるほうが、いい身体なの？</p></div></div>
>