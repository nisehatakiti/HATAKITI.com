<?php
/**
 * Virtual page: 舞台スタッフの仕事
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
$pages = array(
 'theatre-textbook/staff/lighting'=>array('照明','光で、時間・場所・人物・感情・空間の見え方を設計する。','機材','凸レンズスポット、フレネル、PAR、LED、エリスポットなど','色','カラーフィルター（ゼラ）はメーカーや製品体系とセットで確認する。','実践','同じ人物を方向・明るさ・色だけ変えて見せ、印象の違いを観察する。'),
 'theatre-textbook/staff/sound'=>array('音響','声、SE、BGM、空間の音を設計し、客席へ届ける。','機材','マイク、スピーカー、ミキサー、アンプ、ケーブルなど','基礎','ゲイン、音量、EQ、定位、ハウリングを理解する。','実践','「待つ」という同じ場面に異なるSEやBGMを重ね、意味の変化を比べる。'),
 'theatre-textbook/staff/stage-management'=>array('舞台監督','舞台上と舞台裏の進行を整理し、本番を成立させる。','進行','進行表、キュー、転換、場当たり、ゲネプロ、本番進行。','連携','演出・役者・照明・音響・舞台美術などをつなぐ。','実践','「開演5分前に出演者がいない」などの状況から対応を考える。'),
);
if('theatre-textbook/staff'===$path){$title='舞台スタッフの仕事';$desc='役者以外にも、舞台を成立させるたくさんの仕事があります。';}
else {$p=$pages[$path]??$pages['theatre-textbook/staff/lighting'];$title=$p[0];$desc=$p[1];}
get_header(); ?>
<main class="hk-container hk-staff"><header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜舞台を作る</p><h1><?php echo esc_html($title); ?></h1><p><?php echo esc_html($desc); ?></p></header>
<?php if('theatre-textbook/staff'===$path): ?><section class="hk-section"><div class="hk-staff-grid"><?php foreach($pages as $url=>$p): ?><a href="<?php echo esc_url(home_url('/'.$url.'/')); ?>" class="hk-staff-card"><h2><?php echo esc_html($p[0]); ?></h2><p><?php echo esc_html($p[1]); ?></p></a><?php endforeach; ?><div class="hk-staff-card"><h2>舞台美術</h2><p>舞台空間、装置、素材、転換などを設計する。</p></div><div class="hk-staff-card"><h2>制作</h2><p>企画、予算、広報、チケット、劇場との調整などを担う。</p></div></div></section>
<?php else: ?><section class="hk-section"><div class="hk-staff-detail"><div><h2><?php echo esc_html($p[2]); ?></h2><p><?php echo esc_html($p[3]); ?></p></div><div><h2><?php echo esc_html($p[4]); ?></h2><p><?php echo esc_html($p[5]); ?></p></div><div><h2><?php echo esc_html($p[6]); ?></h2><p><?php echo esc_html($p[7]); ?></p></div></div></section><?php endif; ?></main>
<style>.hk-staff-hero{max-width:800px;margin:64px auto;padding:0 20px;text-align:center}.hk-staff-hero h1{font-family:var(--hk-font-serif);font-size:36px}.hk-staff-hero>p:last-child{color:var(--hk-fg-dim);line-height:2}.hk-staff-grid,.hk-staff-detail{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}.hk-staff-card,.hk-staff-detail>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:24px;color:var(--hk-fg)}.hk-staff-card:hover{border-color:var(--hk-accent-warm);text-decoration:none}.hk-staff-card p,.hk-staff-detail p{color:var(--hk-fg-dim);line-height:1.9}.hk-staff-detail>div:last-child{grid-column:1/-1;border-left:3px solid var(--hk-accent-warm)}@media(max-width:700px){.hk-staff-grid,.hk-staff-detail{grid-template-columns:1fr}.hk-staff-detail>div:last-child{grid-column:auto}.hk-staff-hero h1{font-size:29px}}</style>
<?php get_footer(); ?>