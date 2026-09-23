<?php
/**
 * Virtual page: 演劇の現場を知る
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
$items = array(
    'theatre-textbook/theatre-world/high-school' => array('高校演劇','学校という環境で、限られた時間・人員・設備の中で一つの舞台を作る。'),
    'theatre-textbook/theatre-world/commercial' => array('商業演劇','興行として公演を成立させるための制作・劇場・宣伝・技術の仕組みを見る。'),
    'theatre-textbook/theatre-world/student' => array('大学・学生演劇','学生主体の創作活動。劇団活動との違いや、企画から公演までを学ぶ。'),
    'theatre-textbook/theatre-world/small-theatre' => array('小劇場演劇','比較的小規模な劇場で、作品・俳優・スタッフがどう舞台を作るかを知る。'),
    'theatre-textbook/theatre-world/production' => array('公演ができるまで','企画、脚本、キャスティング、稽古、仕込み、場当たり、ゲネプロ、本番まで。'),
);
if ( 'theatre-textbook/theatre-world' !== $path ) {
    $item = $items[$path] ?? array('演劇の現場','演劇の現場を知るための基礎教材。');
} else {
    $item = array('演劇の現場を知る','役者だけでは舞台はできない。高校演劇から商業演劇まで、演劇がどのように作られているのかを知る。');
}
get_header();
?>
<main class="hk-container hk-world">
<header class="hk-world-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書</p><h1><?php echo esc_html($item[0]); ?></h1><p><?php echo esc_html($item[1]); ?></p></header>
<?php if ('theatre-textbook/theatre-world' === $path): ?>
<section class="hk-section"><div class="hk-section-head"><h2>演劇の現場は、一つではない</h2></div>
<p class="hk-lead">「高校演劇」と「商業演劇」を単純にプロ・アマで分けるのではなく、それぞれの目的、体制、予算、稽古、劇場、観客、スタッフの違いを具体的に見ていきます。</p>
<div class="hk-world-grid"><?php foreach($items as $url=>$v): ?><a class="hk-world-card" href="<?php echo esc_url(home_url('/'.$url.'/')); ?>"><span><?php echo esc_html($v[0]); ?></span><strong>→</strong><p><?php echo esc_html($v[1]); ?></p></a><?php endforeach; ?></div>
</section>
<section class="hk-section"><div class="hk-section-head"><h2>舞台を作る人たち</h2></div><div class="hk-staff-links">
<a href="<?php echo esc_url(home_url('/theatre-textbook/staff/')); ?>">舞台スタッフの仕事 →</a>
<a href="<?php echo esc_url(home_url('/theatre-textbook/staff/lighting/')); ?>">照明 →</a>
<a href="<?php echo esc_url(home_url('/theatre-textbook/staff/sound/')); ?>">音響 →</a>
<a href="<?php echo esc_url(home_url('/theatre-textbook/staff/stage-management/')); ?>">舞台監督 →</a>
</div></section>
<?php else: ?>
<section class="hk-section"><div class="hk-section-head"><h2>このページで見ること</h2></div>
<div class="hk-world-panel"><p><?php echo esc_html($item[1]); ?></p>
<?php if ( 'theatre-textbook/theatre-world/high-school' === $path ) : ?>
<h3>高校演劇</h3><p>学校ごとに活動条件は大きく異なります。顧問、部員、活動時間、学校施設、地域大会など、教育活動としての側面と創作活動としての側面を両方見ます。</p><h3>商業演劇との違いを考える</h3><p>「高校だからこう」「商業だからこう」と決めつけず、目的、予算、人員、稽古時間、劇場、観客、意思決定の仕組みなどを比較します。</p>
<?php elseif ( 'theatre-textbook/theatre-world/commercial' === $path ) : ?>
<h3>商業演劇</h3><p>興行として公演を成立させるため、作品制作だけでなく劇場、宣伝、チケット、契約、スタッフ体制、収支など多くの要素が関係します。公演形態や会社によって仕組みは異なります。</p>
<?php elseif ( 'theatre-textbook/theatre-world/production' === $path ) : ?>
<h3>公演の流れ</h3><div class="hk-production-flow"><span>企画</span><b>→</b><span>脚本・演出</span><b>→</b><span>キャスト・スタッフ</span><b>→</b><span>稽古</span><b>→</b><span>仕込み</span><b>→</b><span>場当たり</span><b>→</b><span>ゲネプロ</span><b>→</b><span>本番</span></div>
<?php endif; ?>
<ul><li>誰が、どんな役割を担うのか</li><li>公演を成立させるために何が必要なのか</li><li>役者とスタッフはどこで関わるのか</li><li>実際に自分で試すなら何から始めるのか</li></ul></div></section>
<?php endif; ?>
</main>
<style>
.hk-world-hero{max-width:800px;margin:64px auto;padding:0 20px;text-align:center}.hk-world-hero h1{font-family:var(--hk-font-serif);font-size:36px}.hk-world-hero>p:last-child{color:var(--hk-fg-dim);line-height:2}.hk-lead{max-width:820px;margin:0 auto 24px;color:var(--hk-fg-dim);line-height:2}.hk-world-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}.hk-world-card,.hk-world-panel{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:24px;color:var(--hk-fg)}.hk-world-card:hover{border-color:var(--hk-accent-warm);text-decoration:none}.hk-world-card span{color:var(--hk-accent-warm);font-family:var(--hk-font-serif)}.hk-world-card strong{float:right}.hk-world-card p{color:var(--hk-fg-dim);font-size:14px;line-height:1.8}.hk-world-panel{max-width:800px;margin:auto}.hk-world-panel li{margin:12px 0}.hk-staff-links{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.hk-staff-links a{padding:18px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);color:var(--hk-fg)}.hk-staff-links a:hover{border-color:var(--hk-accent-warm);text-decoration:none}@media(max-width:700px){.hk-world-grid,.hk-staff-links{grid-template-columns:1fr}.hk-world-hero h1{font-size:29px}}
 .hk-production-flow{display:flex;gap:8px;flex-wrap:wrap;align-items:center;padding:18px;margin-top:18px;border:1px solid var(--hk-border);background:var(--hk-bg-card)}.hk-production-flow b{color:var(--hk-accent-warm)}\n</style>
<?php get_footer(); ?>