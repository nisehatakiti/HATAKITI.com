<?php
/**
 * メソッド演技 詳細編
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<main class="hk-container hk-method-deep">
<header class="hk-method-deep-hero">
<a class="hk-method-back" href="<?php echo esc_url( home_url( '/theatre-textbook/acting-theory/' ) ); ?>">← 演技とは何か</a>
<p class="hk-textbook-kicker">演技論・演技システム｜02</p>
<h1>メソッド演技</h1>
<p>「メソッド演技」という言葉は、一つの固定された技法を指すだけではありません。スタニスラフスキーの仕事を背景に、アメリカでさまざまな教師・俳優によって発展した演技訓練の流れを見ていきます。</p>
</header>

<nav class="hk-chapter-nav" aria-label="メソッド演技ナビゲーション"><a href="<?php echo esc_url(home_url('/theatre-textbook/stanislavski/')); ?>">← スタニスラフスキー</a><a href="<?php echo esc_url(home_url('/theatre-textbook/acting-theory/')); ?>">総論</a><a href="<?php echo esc_url(home_url('/theatre-textbook/meisner/')); ?>">次：マイズナー →</a></nav>

<section class="hk-section"><div class="hk-section-head"><h2>1　まず「メソッド＝一つの方法」ではない</h2></div>
<p>日本では「メソッド演技」という言葉が広く使われていますが、実際には複数の教師や流派の実践をまとめて語ることがあります。したがって、すべてのメソッド俳優が同じ練習をするわけではありません。</p>
<div class="hk-method-deep-note"><strong>覚えておこう</strong><p>メソッドを学ぶときは「これが唯一のメソッド」と決めず、誰が、何を目的に、どんな訓練を行っているのかを見る。</p></div>
</section>

<section class="hk-section"><div class="hk-section-head"><h2>2　スタニスラフスキーとのつながり</h2></div>
<p>アメリカの演技訓練は、スタニスラフスキーの考え方を受け取りながら発展しました。その過程で、内面、想像力、感覚、行動、相手との関係など、さまざまな要素が異なる形で重視されるようになります。</p>
<p>だから「スタニスラフスキー」と「メソッド」を完全に別世界のものとして考えるより、<strong>影響を受けながら変化した演技訓練の歴史</strong>として見るほうが分かりやすくなります。</p>
</section>

<?php
$method_diagram = get_stylesheet_directory() . '/../../plugins/hatakiti-core/assets/images/acting/acting-method-basic-flow.png';
if ( file_exists( $method_diagram ) ) :
?>
<section class="hk-section hk-method-visual">
<div class="hk-section-head"><h2>メソッド演技の基本的な流れ</h2></div>
<figure class="hk-method-figure">
<img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/acting/acting-method-basic-flow.png' ) ); ?>" alt="メソッド演技の基本的な流れを示す図説" loading="lazy">
<figcaption>感覚や想像を手がかりに、人物の状況へつなげ、最終的には具体的な行動として表現します。</figcaption>
</figure>
</section>
<?php endif; ?>

<section class="hk-section"><div class="hk-section-head"><h2>3　感覚を具体的にする</h2></div>
<p>役の世界を想像するとき、視覚だけでなく、触った感じ、温度、重さ、音、匂いなどの感覚を使うことがあります。</p>
<div class="hk-method-deep-grid"><article><h3>冷たい</h3><p>金属の手すりに触れたときの冷たさを想像する。</p></article><article><h3>重い</h3><p>箱を持ったとき、腕だけでなく足や呼吸まで変化させる。</p></article><article><h3>狭い</h3><p>身体の周囲に壁があるような空間を想像する。</p></article></div>
<p class="hk-textbook-note">目的は「記憶を正確に再現する」ことではなく、役の状況を俳優の中で具体的にすることです。</p>
</section>

<section class="hk-section"><div class="hk-section-head"><h2>4　想像力を使う</h2></div>
<p>自分が実際に経験したことだけが演技の材料ではありません。経験していない状況でも、脚本の情報や想像力から人物の世界を作ることができます。</p>
<div class="hk-exercise"><h3>想像の練習</h3><ol><li>封筒を一枚用意する。</li><li>中身を見ないまま、誰から届いたのかを想像する。</li><li>今夜届いたのか、十年前の手紙なのかを決める。</li><li>重さ、紙の質感、匂い、開けるまでの時間を具体的にする。</li><li>最後に開け、想像していた世界と実際の紙との違いを観察する。</li></ol></div>
</section>

<section class="hk-section"><div class="hk-section-head"><h2>5　「自分の経験」はどう使う？</h2></div>
<p>自分の経験を演技の材料にする場合でも、経験そのものを舞台上で再現する必要はありません。経験から得た感覚や状況の理解を、役の世界へ変換して使うことができます。</p>
<div class="hk-method-deep-note"><strong>大切なこと</strong><p>つらい経験を掘り起こすことが、メソッド演技の必須条件ではありません。安全に扱える想像、観察、身体、感覚を使った訓練もあります。</p></div>
</section>

<section class="hk-section"><div class="hk-section-head"><h2>6　リラクゼーションと身体</h2></div>
<p>身体が固まっていると、呼吸や声、動きの選択肢も狭くなります。そのため、リラクゼーションなどを使って余計な緊張に気づき、身体を使える状態にする訓練があります。</p>
<p>ここでいうリラックスは「力を抜いて何もしない」という意味ではありません。必要な場所には力を使いながら、不要な緊張を減らすことを目指します。</p>
</section>

<section class="hk-section"><div class="hk-section-head"><h2>7　人物を自分に近づけるのではなく、自分を人物の状況へ置く</h2></div>
<p>「自分ならどうする？」という問いは便利ですが、それだけでは役と自分が同じになってしまいます。</p>
<p>そこで、<strong>自分がこの人物と同じ状況に置かれたら、何を感じ、何を見て、何をしようとするだろうか</strong>と考えます。人物の条件を一つずつ増やすことで、自分の反応を役の世界へ移していきます。</p>
</section>

<section class="hk-section"><div class="hk-section-head"><h2>8　「本当に感じる」と「本当に起きている」は違う</h2></div>
<p>演技では、俳優の中に本当の反応が起きることがあります。しかし、舞台上では照明、相手役、客席、台詞、時間、演出上の約束も同時に存在します。</p>
<p>だから、内面のリアリティと舞台上の技術は対立するものではありません。<strong>感じながら、扱えること</strong>が俳優の技術になります。</p>
</section>

<section class="hk-section"><div class="hk-section-head"><h2>9　5分エチュード「届いた手紙」</h2></div>
<div class="hk-exercise"><h3>一人で行う</h3><ol><li>封筒を手にする。</li><li>誰から届いたのか、開ける前に具体的に想像する。</li><li>身体、呼吸、視線の変化を観察する。</li><li>開封する。</li><li>中身を読んだ直後に何が起きるかを決めず、最初の反応を受け取る。</li></ol><p>無理に泣いたり、強い感情を作ったりしません。想像した状況が身体や行動にどう影響するかを観察します。</p></div>
<div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url(content_url('plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-question.png')); ?>" alt="にゃかきち"></div><div class="hk-nyakakichi-question"><p>自分の経験を無理に思い出さなくても、想像から役の世界を作れるんだニャ。</p></div></div>
</section>

<section class="hk-section"><div class="hk-section-head"><h2>10　メソッドを「感情を出す技術」にしない</h2></div>
<p>メソッド演技については「本当に泣く」「過去の感情を呼び起こす」といったイメージだけが強調されることがあります。しかし、演技訓練には複数の流れがあり、想像力、感覚、身体、脚本分析、即興なども重要な材料になります。</p>
<div class="hk-method-deep-compare"><div><strong>狭い理解</strong><span>つらい記憶を思い出す → 泣く</span></div><div><strong>広い理解</strong><span>状況を具体化する → 感覚・想像・身体を使う → 行動する</span></div></div>
</section>

<section class="hk-section"><div class="hk-panel hk-summary"><h3>メソッド演技編まとめ</h3>
<p><strong>感覚 → 想像 → 状況の具体化 → 身体・声 → 行動</strong></p>
<p>メソッドを「強い感情を作る方法」と考えるのではなく、役の世界を俳優の中で具体的にし、その状況の中で行動するための複数の訓練として捉えてみましょう。</p>
<p>次は、さらに視点を変えて「相手を受け取り、その瞬間に反応する」マイズナー・テクニックを見ていきます。</p>
</div></section>

<nav class="hk-chapter-nav" aria-label="メソッド演技ナビゲーション"><a href="<?php echo esc_url(home_url('/theatre-textbook/stanislavski/')); ?>">← スタニスラフスキー</a><a href="<?php echo esc_url(home_url('/theatre-textbook/acting-theory/')); ?>">総論</a><a href="<?php echo esc_url(home_url('/theatre-textbook/meisner/')); ?>">次：マイズナー →</a></nav>
</main>
<style>

.hk-chapter-nav{display:flex;justify-content:space-between;align-items:center;gap:12px;max-width:850px;margin:28px auto;padding:14px 0;border-top:1px solid var(--hk-border);border-bottom:1px solid var(--hk-border)}
.hk-chapter-nav a,.hk-chapter-nav span{flex:1}.hk-chapter-nav a:last-child{text-align:right}.hk-chapter-nav span{text-align:center;color:var(--hk-fg-dim);font-size:13px}
.hk-method-deep .hk-nyakakichi{max-width:850px;margin:30px auto 46px;display:flex;align-items:center;gap:18px}.hk-method-deep .hk-nyakakichi-image{width:110px;height:110px;flex:none}.hk-method-deep .hk-nyakakichi-image img{width:100%;height:100%;object-fit:contain}.hk-method-deep .hk-nyakakichi-question{position:relative;flex:1;padding:18px 22px;background:var(--hk-bg-card);border:1px solid var(--hk-border);border-radius:16px;line-height:1.8}.hk-method-deep .hk-nyakakichi-question:before{content:"";position:absolute;left:-9px;top:50%;width:16px;height:16px;margin-top:-8px;background:var(--hk-bg-card);border-left:1px solid var(--hk-border);border-bottom:1px solid var(--hk-border);transform:rotate(45deg)}
.hk-method-visual{max-width:850px;margin-left:auto;margin-right:auto}.hk-method-figure{margin:0}.hk-method-figure img{display:block;width:100%;height:auto;border:1px solid var(--hk-border);border-radius:14px}.hk-method-figure figcaption{margin-top:10px;color:var(--hk-fg-dim);font-size:12px;line-height:1.7}
@media(max-width:600px){.hk-chapter-nav{margin-left:16px;margin-right:16px}.hk-chapter-nav a,.hk-chapter-nav span{font-size:11px}.hk-method-deep .hk-nyakakichi{margin-left:16px;margin-right:16px;align-items:flex-start}.hk-method-deep .hk-nyakakichi-image{width:90px;height:90px}.hk-method-figure{margin-left:0;margin-right:0}}
.hk-method-deep-hero{max-width:850px;margin:48px auto 42px;padding:0 20px}.hk-method-deep-hero h1{font-family:var(--hk-font-serif);font-size:40px}.hk-method-deep-hero>p:last-child{color:var(--hk-fg-dim);line-height:2}.hk-method-deep .hk-section{max-width:850px;margin-left:auto;margin-right:auto}.hk-method-deep-note,.hk-method-deep-compare{padding:22px;background:var(--hk-bg-card);border:1px solid var(--hk-border);line-height:1.9}.hk-method-deep-note p{margin:8px 0 0}.hk-method-deep-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:20px 0}.hk-method-deep-grid article{padding:20px;background:var(--hk-bg-elevated);border:1px solid var(--hk-border)}.hk-method-deep-grid h3{font-family:var(--hk-font-serif);margin-top:0}.hk-method-deep-compare{display:grid;grid-template-columns:1fr 1fr;gap:14px}.hk-method-deep-compare div{padding:16px;background:var(--hk-bg-elevated);display:flex;flex-direction:column;gap:6px}.hk-method-deep-compare span{color:var(--hk-fg-dim)}.hk-method-deep .hk-summary{padding:28px}@media(max-width:700px){.hk-method-deep-grid,.hk-method-deep-compare{grid-template-columns:1fr}.hk-method-deep-hero h1{font-size:30px}.hk-method-deep-hero{padding:0 16px}}
</style>
<?php get_footer(); ?>