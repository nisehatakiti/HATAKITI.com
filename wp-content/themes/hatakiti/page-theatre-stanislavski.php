<?php
/**
 * スタニスラフスキー・システム 詳細編
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<main class="hk-container hk-stan">
<header class="hk-stan-hero">
<a class="hk-method-back" href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">← 演劇の教科書に戻る</a>
<p class="hk-textbook-kicker">演技論・演技システム｜01</p>
<h1>スタニスラフスキー・システム</h1>
<p>人物を「それらしく見せる」ことではなく、与えられた状況の中で、その人物が何を求め、何をしようとしているのかを具体的にしていくための演技の考え方を学びます。</p>
</header>
<nav class="hk-chapter-nav" aria-label="スタニスラフスキー編ナビゲーション"><a href="<?php echo esc_url( home_url( '/theatre-textbook/acting-theory/' ) ); ?>">← 演技とは何か</a><span>スタニスラフスキー編</span><a href="<?php echo esc_url( home_url( '/theatre-textbook/method/' ) ); ?>">次：メソッド演技 →</a></nav>

<?php
$sections=array(
array('1　スタニスラフスキーは何を考えたのか','俳優が舞台上で「本当に生きているように見える」ためには、外見だけを真似するのではなく、人物が置かれた状況と行動を具体的にする必要があると考えました。ここから、演技を感情の再現だけではなく、行動の連続として考える入口が生まれます。'),
array('2　与えられた状況','台本には、時代、場所、人物関係、出来事、環境など、人物を取り巻く条件があります。これらを具体的に想像すると、「今ここで何が起きているのか」が変わります。まずは台詞より前に、人物が立っている世界を作ります。'),
array('3　目的','人物は場面の中で何かを求めています。相手に分かってほしい、帰ってきてほしい、隠したい、許してほしいなど、目的は行動を生む力になります。「悲しい」ではなく「相手に残ってほしい」のように、働きかけとして考えると演技が動き始めます。'),
array('4　行動','目的を達成するために人物がすることを考えます。説得する、避ける、探る、脅す、慰める、隠す。行動には相手への働きかけがあります。同じ目的でも、行動を変えれば場面の形は変わります。'),
array('5　障害','目的は簡単には達成できません。相手が拒む、時間がない、秘密がある、自分自身が迷っているなど、目的を邪魔するものがあります。障害があるからこそ、俳優の行動に変化が生まれます。'),
array('6　もしも','「もし自分がこの人物だったら」ではなく、「もし自分がこの状況に置かれていたら」と想像してみます。現実の自分と人物を完全に同一視するのではなく、想像によって状況を具体化するための入口として使えます。'),
array('7　相手への働きかけ','台詞を言うことを目的にしないで、相手に何かを起こそうとします。相手を安心させる、納得させる、怒らせる、黙らせるなど、台詞の裏側にある行動を探します。'),
array('8　感情は結果として現れる','「悲しくなる」「怒る」という結果を先に作ろうとすると、演技が固定されることがあります。状況、目的、障害、相手への行動を具体的にすると、その過程で感情が動くことがあります。感情を否定するのではなく、感情だけを直接操作しようとしない考え方です。'),
array('9　台本を分析してみる','短い場面を選び、①どこにいるか、②何が起きたか、③何が欲しいか、④何が邪魔するか、⑤相手に何をするか、を順番に書き出します。その後、同じ台詞を目的だけ変えて演じてみます。'),
array('10　5分エチュード','AはBに帰ってほしくない。Aの目的を「引き止める」と決める。Bは帰ろうとする。Aは説得、質問、冗談、沈黙など別々の行動を試す。最後に、最も自然に相手へ働きかけられた行動を一つ選び、もう一度演じます。')
);
foreach($sections as $i=>$s): ?>
<section class="hk-section hk-stan-section"><div class="hk-section-head"><h2><?php echo esc_html($s[0]); ?></h2></div><p><?php echo esc_html($s[1]); ?></p>
<?php if($i===2): ?><div class="hk-stan-example"><strong>目的の例</strong><span>悲しむ ×　→　相手に残ってほしい ○</span><span>怒る ×　→　相手に謝らせたい ○</span></div><?php endif; ?>
<?php if($i===3): ?><div class="hk-stan-example"><strong>行動の例</strong><span>説得する・探る・避ける・ごまかす・責める・慰める</span></div><?php endif; ?>
<?php if($i===8): ?><div class="hk-exercise"><h3>台本分析シート</h3><ol><li>この場面は、いつ・どこ？</li><li>直前に何が起きた？</li><li>自分は何が欲しい？</li><li>何が邪魔している？</li><li>相手に何をしている？</li></ol></div><?php endif; ?>
<?php if($i===9): ?><div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url(content_url('plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-practice.png')); ?>" alt="にゃかきち"></div><div class="hk-nyakakichi-question"><p>感情を先に作るんじゃなくて、相手に何をしたいかを試すんだニャ！</p></div></div><?php endif; ?>
</section>
<?php endforeach; ?>

<section class="hk-section"><div class="hk-panel hk-summary"><h3>スタニスラフスキー編まとめ</h3><p><strong>状況 → 目的 → 障害 → 行動 → 相手への働きかけ</strong></p><p>この流れで考えると、感情を無理に作らなくても、人物の行動から演技を組み立てられます。次は、同じ「内面」を別の角度から扱うメソッド演技を見ていきます。</p></div></section>
<nav class="hk-chapter-nav" aria-label="スタニスラフスキー編ナビゲーション"><a href="<?php echo esc_url( home_url( '/theatre-textbook/acting-theory/' ) ); ?>">← 演技とは何か</a><span>スタニスラフスキー編</span><a href="<?php echo esc_url( home_url( '/theatre-textbook/method/' ) ); ?>">次：メソッド演技 →</a></nav>
</main>
<style>
.hk-stan-hero{max-width:850px;margin:48px auto 42px;padding:0 20px}.hk-stan-hero h1{font-family:var(--hk-font-serif);font-size:40px}.hk-stan-hero>p:last-child{color:var(--hk-fg-dim);line-height:2}.hk-stan-section{max-width:850px;margin-left:auto;margin-right:auto}.hk-stan-example{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px;padding:20px;background:var(--hk-bg-elevated);border:1px solid var(--hk-border)}.hk-stan-example strong{width:100%;font-family:var(--hk-font-serif)}.hk-stan-example span{padding:7px 10px;border:1px solid var(--hk-border);font-size:13px}.hk-stan .hk-exercise{margin-top:20px}.hk-stan .hk-summary{padding:28px}@media(max-width:600px){.hk-stan-hero h1{font-size:29px}.hk-stan-hero{padding:0 16px}.hk-stan-section{padding-left:16px;padding-right:16px}}
</style>
<?php get_footer(); ?>