<?php
/**
 * Virtual page: 演劇用語集 term.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$terms = array(
'theatre'=>array('name'=>'演劇','category'=>'基本','definition'=>'人が人物や出来事を表現し、それを他の人が受け取る表現です。劇場の有無だけで演劇を決めることはできません。','points'=>array('演じる人と見る人の関係がある','身体・声・空間など複数の手段を使う','時代や社会によってさまざまな形がある')),
'actor'=>array('name'=>'俳優','category'=>'基本','definition'=>'舞台などで人物を演じ、身体や声を使って観客に表現を届ける人です。','points'=>array('役の人物を身体と声で表現する','相手や観客との関係の中で演じる','時代や演劇の形式によって役割は変化する')),
'audience'=>array('name'=>'観客','category'=>'基本','definition'=>'演劇を見て、聞いて、受け取る人です。観客は舞台の外にいるだけでなく、演じる側との関係の中で舞台を成立させます。','points'=>array('演じる人の表現を受け取る','想像によって舞台上の出来事を補う','観客との関係によって演劇の形も変わる')),
'theatre-building'=>array('name'=>'劇場','category'=>'劇場・空間','definition'=>'演劇などを上演し、観客が見るために整えられた空間や建物です。形や構造は時代と地域によって異なります。','points'=>array('観客席と上演空間の関係をつくる','舞台の見え方や聞こえ方に影響する','劇場そのものが社会や文化を反映する')),
'festival'=>array('name'=>'祭り','category'=>'古代・儀式','definition'=>'地域や社会で行われる行事です。古代の演劇は祭りや社会的な行事と深く結びついて発展しました。','points'=>array('人々が集まる機会になる','歌・踊り・行列などの表現を伴うことがある','演劇の発展と結びついた地域や時代がある')),
'ritual'=>array('name'=>'儀式','category'=>'古代・儀式','definition'=>'宗教や社会において、一定の意味や手順をもって行われる行為です。演劇と似た表現を持つ場合がありますが、同じものではありません。','points'=>array('宗教的・社会的な意味を持つ','決められた行為や形式を伴う','演劇と重なる表現があっても目的は同一とは限らない')),
'tragedy'=>array('name'=>'悲劇','category'=>'ジャンル','definition'=>'古代ギリシャで発展し、人間の選択や苦しみ、運命などを扱った演劇のジャンルです。','points'=>array('神話や英雄などを題材にする作品がある','人間の選択や葛藤を描く','後世にもさまざまな形で発展した')),
'comedy'=>array('name'=>'喜劇','category'=>'ジャンル','definition'=>'笑いを通して人間や社会を描く演劇のジャンルです。古代ギリシャでも発展しました。','points'=>array('社会や人物を笑いの対象にすることがある','単なる娯楽だけでなく社会への視線を含む作品もある','時代によって形式や笑いの方法が変わる')),
'chorus'=>array('name'=>'合唱隊','category'=>'古代・演技','definition'=>'古代ギリシャ演劇などで、歌や言葉、動きによって作品に参加する集団です。','points'=>array('歌や踊り、言葉で舞台に参加する','物語や人物への反応を示す役割を持つことがある','古代演劇を理解する重要な要素の一つ')),
'mask'=>array('name'=>'仮面','category'=>'古代・演技','definition'=>'顔を覆う道具です。古代演劇などで人物の表現や視覚的な識別に用いられました。','points'=>array('人物の見た目を大きく変える','表情以外の身体表現を強調する場合がある','地域や演劇形式によって用途が異なる')),
'direction'=>array('name'=>'演出','category'=>'近代・演出','definition'=>'作品を舞台上でどのように成立させるかを考え、俳優、空間、時間、音、光などをまとめる仕事や考え方です。','points'=>array('作品全体の方向性を考える','俳優の演技や舞台要素を関係づける','時代によって演出家の役割は変化してきた')),
'realism'=>array('name'=>'リアリズム','category'=>'近代・演劇論','definition'=>'現実の人間や生活を舞台上でどう表現するかを重視する近代演劇の重要な考え方です。','points'=>array('日常生活や人間関係を題材にする','人物の心理や社会環境を重視する考え方がある','19世紀後半以降の演劇を考える重要な概念')),
);
$path=trim(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH),'/');
$base=trim(parse_url(home_url('/'),PHP_URL_PATH),'/');
if($base&&0===strpos($path,$base.'/')){$path=substr($path,strlen($base)+1);}
$slug=trim(substr($path,strlen('theatre-textbook/glossary/')),'/');
if(!isset($terms[$slug])){$slug='theatre';}
$term=$terms[$slug];
get_header();
?>
<main class="hk-container hk-glossary-term">
<header class="hk-textbook-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜演劇用語集</p><h1><?php echo esc_html($term['name']); ?></h1><p><?php echo esc_html($term['category']); ?></p></header>
<nav class="hk-chapter-nav" aria-label="演劇用語集ナビゲーション"><a href="<?php echo esc_url(home_url('/theatre-textbook/glossary/')); ?>">← 用語集</a><a href="<?php echo esc_url(home_url('/theatre-textbook/')); ?>">演劇の教科書</a><span></span></nav>
<section class="hk-section"><div class="hk-term-definition"><span>意味</span><p><?php echo esc_html($term['definition']); ?></p></div><div class="hk-term-points"><h2>ポイント</h2><ul><?php foreach($term['points'] as $point): ?><li><?php echo esc_html($point); ?></li><?php endforeach; ?></ul></div></section>
<section class="hk-section hk-term-related"><div class="hk-section-head"><h2>関連する言葉</h2><p>用語は単独で覚えるのではなく、関係する言葉と一緒に見ると理解しやすくなります。</p></div><p><a href="<?php echo esc_url(home_url('/theatre-textbook/glossary/')); ?>">演劇用語集の一覧へ戻る →</a></p></section>
</main>
<style>
.hk-glossary-term .hk-textbook-hero{max-width:760px;margin:56px auto 48px;text-align:center}.hk-glossary-term .hk-textbook-hero h1{font-family:var(--hk-font-serif);font-size:40px;margin-bottom:10px}.hk-glossary-term .hk-textbook-hero>p:last-child{color:var(--hk-accent-warm)}
.hk-term-definition{padding:30px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);border-left:4px solid var(--hk-accent-warm)}.hk-term-definition>span{font-size:12px;color:var(--hk-accent-warm);letter-spacing:.1em}.hk-term-definition p{font-size:18px;line-height:2;margin:10px 0 0}.hk-term-points{margin-top:28px;padding:26px;border:1px solid var(--hk-border);background:var(--hk-bg-card)}.hk-term-points h2{margin-top:0}.hk-term-points li{margin-bottom:10px;line-height:1.8}.hk-term-related{padding-bottom:60px}
@media(max-width:600px){.hk-glossary-term .hk-textbook-hero h1{font-size:31px}.hk-term-definition p{font-size:16px}}
</style>
<?php get_footer(); ?>