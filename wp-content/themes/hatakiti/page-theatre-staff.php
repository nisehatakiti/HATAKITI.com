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

<section class="hk-section">
<div class="hk-section-head"><h2>照明を設計するときの5つの視点</h2><p>「何色を入れるか」だけでなく、光が舞台上で何をしているのかを分解して考えます。</p></div>
<div class="hk-four hk-lighting-five">
<div><b>1. 明るさ</b><p>何を見せ、何を見せないかを決めます。明るくすることは単純に「良い」ことではなく、暗がりも演出になります。</p></div>
<div><b>2. 方向</b><p>光がどこから来るかで、顔の陰影、身体の立体感、舞台空間の奥行きが変わります。</p></div>
<div><b>3. 広がり</b><p>狙った範囲だけを照らすのか、周囲までつなげるのか。光の境界そのものが舞台上の線になります。</p></div>
<div><b>4. 色</b><p>時間・場所・季節・温度感・心理などを表現します。色そのものだけでなく、誰にどの方向から当たるかも重要です。</p></div>
<div><b>5. 影</b><p>照明は「明るくする仕事」であると同時に「影を作る仕事」でもあります。影の位置と濃さを観察します。</p></div>
</div>
<div class="hk-panel hk-lighting-note"><strong>覚え方：</strong>「何を見せる？ → どこから？ → どこまで？ → 何色？ → どんな影？」の順に考えると、器具選びと明かり作りを結びつけやすくなります。</div>
</section>

<section class="hk-section hk-lighting-intro-illustration">
<div class="hk-section-head"><h2>にゃかきちと照明を考える</h2><p>ここからは、にゃかきちが「これ、どうなってるの？」と疑問を持ちながら、灯体・配置・操作を順番に見ていきます。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 01</div>
  <div class="hk-illustration-placeholder-file">lighting-01-light-directions.png</div>
  <p>舞台を上から見た模式図。正面光・サイド・バック・トップなど、光の方向を人物に向けて矢印で示す。にゃかきちは舞台袖から「同じ人でも、光が横から来ると違って見えるの？」と疑問を持つ。</p>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>灯体は「光を作って、整えて、届ける」</h2><p>灯体の内部では、光源から出た光をそのまま舞台へ飛ばしているわけではありません。器具によって方法は違いますが、光を集めたり、形を整えたりしてから舞台へ送ります。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 02</div>
  <div class="hk-illustration-placeholder-file">lighting-02-fixture-anatomy.png</div>
  <p>灯体の断面模式図。光源→反射・集光→レンズ→絞り／カッター→フィルターホルダー→舞台上の光、という流れを示す。灯体によって部品構成が異なることも注記。</p>
</div>
<div class="hk-panel"><p><strong>にゃかきちの疑問：</strong>「じゃあ、灯体の種類が違うと、光の出方も違うの？」</p><p>その通りです。レンズ、反射鏡、配光、カッターの有無などが違うため、同じ場所を照らしても光の広がり方や境界が変わります。</p></div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>代表的な灯体を比べてみる</h2><p>器具名を暗記するのではなく、「どんな光が出るか」で比べます。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 03</div>
  <div class="hk-illustration-placeholder-file">lighting-03-fixture-comparison.png</div>
  <p>凸・フレネル・エリスポット・PARの横並び比較。灯体の外観を簡略化し、それぞれの光を舞台上に投射した断面図を同じ条件で表示。光の境界、広がり、形を比較できる構成。</p>
</div>
<div class="hk-term-grid">
<div><h3>凸（平凸）</h3><p>狙った場所へ比較的輪郭のある光を置きたいときに考えます。</p></div>
<div><h3>フレネル</h3><p>比較的柔らかな境界で人物やエリアをつなげたいときに考えます。</p></div>
<div><h3>エリスポット</h3><p>カッターなどで光の形を細かく制御したいときに考えます。</p></div>
<div><h3>PAR</h3><p>配光の種類やレンズとの組み合わせを確認し、広がりや光量を活かします。</p></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>凸：狙った場所に光を置く</h2><p>比較的はっきりした光を作りやすい灯体として、まず凸を見てみます。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 04</div>
  <div class="hk-illustration-placeholder-file">lighting-04_profile_spot.png</div>
  <p>凸の簡略断面図。光源、反射鏡、平凸レンズ、前方へ出る光を示す。フォーカス操作によって光の大きさ・輪郭を調整するイメージを併記。</p>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>フレネル：柔らかくつなぐ</h2><p>フレネルレンズを使った灯体は、比較的柔らかな境界の光を作りやすいのが特徴です。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 05</div>
  <div class="hk-illustration-placeholder-file">lighting-05_fresnel.png</div>
  <p>フレネル灯体の簡略断面図。フレネルレンズと光源の関係、スポット／フラッド方向の変化、舞台上の比較的柔らかな光の境界を示す。</p>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>エリスポット：光の形を作る</h2><p>エリスポットでは、レンズによる光にカッターやゴボなどを組み合わせ、光の形そのものを設計できます。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 06</div>
  <div class="hk-illustration-placeholder-file">lighting-06_ellipsoidal_cutters_gobo.png</div>
  <p>エリスポットの側面模式図。光源→レンズ→カッター4枚→ゴボ→レンズ→舞台の順を示し、窓枠・木漏れ日などの投影例を小さく添える。</p>
</div>
<div class="hk-panel"><p><strong>にゃかきちの疑問：</strong>「カッターって、光を暗くするためのもの？」</p><p>主な役割は、必要な場所だけに光を残すことです。明るさを下げるというより、光の形や境界を切って不要な光を当てないために使います。</p></div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>PAR：レンズと配光を確認する</h2><p>PARは一種類の光だけを出す器具名ではなく、実際には使用するレンズや配光などによって見え方が変わります。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 07</div>
  <div class="hk-illustration-placeholder-file">lighting-07_par_beam.png</div>
  <p>PAR灯体の簡略図と、配光の違う例を横並びで表示。光の広がりと用途を比較し、機種によって仕様が異なることを注記。</p>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>ホリゾントライト：背景を作る</h2><p>ホリゾント系の灯体は、人物を狙うというより、舞台奥の背景面を均一または意図した色・明るさで作るために使います。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 08</div>
  <div class="hk-illustration-placeholder-file">lighting-08_horizont.png</div>
  <p>舞台断面図。ホリゾント幕と、その下部または上部から背景面へ光を送るホリゾント系灯体を示す。背景面が色で染まる様子を表現。</p>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>サイドスポット（SS）は「灯体の種類」ではなく配置の考え方</h2><p>SSは特定の一種類の灯体を意味する言葉ではなく、舞台の横方向から人物や身体へ光を入れる配置・役割を指して使われます。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 09</div>
  <div class="hk-illustration-placeholder-file">lighting-09_side_spot_rolling.png</div>
  <p>舞台平面図＋断面図。左右の袖に配置されたSS、床置きのコロガシ、そこから人物へ入る横光を表示。高い位置・低い位置で身体への当たり方が変わることも示す。</p>
</div>
<div class="hk-panel"><p><strong>にゃかきちの疑問：</strong>「SSという名前の特別な灯体があるの？」</p><p>ここではそう考えない方が分かりやすいです。SSは「横から入れる」という<strong>配置・役割</strong>の言葉として覚え、実際に何の灯体を使うかは現場や目的で決まります。</p></div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>サスバトンとコロガシ：どこに灯体を置くのか</h2><p>灯体そのものだけでなく、どこに取り付けるか・置くかによって光の方向が決まります。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 10</div>
  <div class="hk-illustration-placeholder-file">lighting-10_batten_and_rolling.png</div>
  <p>舞台断面図。上部のサスバトンに吊られた灯体と、舞台袖・舞台床に置かれたコロガシを同時に表示。バトンからのトップ／前方光と、床付近からの低いサイド光を対比。</p>
</div>
<div class="hk-term-grid">
<div><h3>サスバトン</h3><p>劇場上部から吊り下げられた、照明器具などを取り付けるための棒状の設備。バトンの位置によって灯体の高さと照射方向の基準が変わります。</p></div>
<div><h3>コロガシ</h3><p>灯体を床置きして使う配置。低い位置からの光を作れるため、SSなどの横光や足元方向の光に利用されます。実際の器具・固定方法は現場の安全手順に従います。</p></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>フェーダーと調光器：操作と電力制御をつなぐ</h2><p>「フェーダーを上げると灯体が明るくなる」の裏側には、操作する人と電力を制御する機器の関係があります。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 11</div>
  <div class="hk-illustration-placeholder-file">lighting-11_fader_dimmer_power.png</div>
  <p>信号・電力の流れを分けた模式図。操作卓のフェーダー→制御信号→調光器→同一系統の電源→灯体、という流れを示す。「フェーダー＝操作するもの」「調光器＝電力を制御するもの」を明確にする。同一の調光回路に接続された灯体は、その回路の制御に応じて明るさが変わる例も示す。</p>
</div>
<div class="hk-panel"><p><strong>にゃかきちの疑問：</strong>「フェーダーと調光器って同じものじゃないの？」</p><p>現場では会話の中で近い意味に感じることもありますが、教材では分けて覚えます。<strong>フェーダーは人が操作するためのコントロール</strong>、<strong>調光器は電力を制御して灯体の明るさを変える装置</strong>です。現在のシステムでは制御信号の方式もさまざまなので、実際の劇場設備に従います。</p></div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>灯体・配置・操作を一本につなげる</h2><p>「どの灯体を使うか」だけでなく、「どこに置き、どう操作し、どこへ光を当てるか」を一つの流れで考えます。</p></div>
<div class="hk-illustration-placeholder">
  <div class="hk-illustration-placeholder-label">ILLUSTRATION 12</div>
  <div class="hk-illustration-placeholder-file">lighting-12_fixture_position_control.png</div>
  <p>舞台断面＋制御系の総合図。サスバトン上の灯体、SS、コロガシ、ホリゾント、操作卓、調光器を一枚につなぎ、「位置→灯体→回路→フェーダー→舞台上の光」の関係を示す。</p>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>光の方向を詳しく見る</h2><p>同じ人物でも、方向が変わるだけで顔の見え方と身体の立体感は大きく変わります。</p></div>
<div class="hk-equipment hk-direction-grid">
<article><h3>正面光</h3><p>顔や身体を観客から見えやすくする基本的な方向。影を減らしやすい一方、立体感が弱くなることもあります。</p><p class="hk-tip">考えること：<strong>表情をどこまで明確に見せるか</strong></p></article>
<article><h3>斜め前・サイド前</h3><p>正面より陰影が生まれ、人物の立体感を作りやすくなります。左右の光量差でも印象が変わります。</p><p class="hk-tip">考えること：<strong>顔のどちら側に影を置くか</strong></p></article>
<article><h3>サイド光</h3><p>身体の輪郭や動きを強調しやすい方向。ダンスや身体表現だけでなく、心理的な緊張感にも使えます。</p><p class="hk-tip">考えること：<strong>身体を「面」ではなく「線」として見せるか</strong></p></article>
<article><h3>逆光・バックライト</h3><p>人物の輪郭を背景から分離しやすく、奥行きやシルエットを作るのに向いています。顔は暗くなりやすいため、前方の光との関係を考えます。</p><p class="hk-tip">考えること：<strong>人物を見せるのか、輪郭を見せるのか</strong></p></article>
<article><h3>トップ・上方光</h3><p>上からの光は目の周囲や顔の下側に影を作りやすく、時間帯や場所の表現、緊張感のある場面などにも使えます。</p><p class="hk-tip">考えること：<strong>顔にできる影を意図して使うか</strong></p></article>
<article><h3>足元・低い位置から</h3><p>日常的な自然光とは異なる影を作りやすく、通常とは違う身体の見え方を作れます。特殊な表現として扱います。</p><p class="hk-tip">考えること：<strong>「普段見ない方向からの光」に意味を持たせるか</strong></p></article>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>光の広がりと境界</h2><p>照明の「広がり」は、舞台上のどこまでを同じ光として扱うかという設計です。</p></div>
<div class="hk-term-grid">
<div><h3>狭く狙う</h3><p>特定の人物や場所に視線を集めやすくなります。周囲との明暗差が大きいほど、舞台上の焦点が明確になります。</p></div>
<div><h3>広く照らす</h3><p>複数の人物や空間を同じ光の中に置きやすくなります。場面全体のつながりを作るのに向いています。</p></div>
<div><h3>境界を硬くする</h3><p>光が当たる場所と当たらない場所の違いをはっきりさせます。エリア分けや形のある光を作るときに意識します。</p></div>
<div><h3>境界を柔らかくする</h3><p>隣り合う明かりを自然につなぎやすくなります。フレネルなど、比較的柔らかい境界を作りやすい器具が役立ちます。</p></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>明るさは「何を見せるか」で決める</h2></div>
<div class="hk-panel">
<p>照明の明るさは、単独の数値だけで決めるものではありません。人物、背景、床、装置などの明るさの<strong>相対的な関係</strong>を見ることが重要です。</p>
<ul>
<li><strong>人物を見せたい：</strong>顔や身体に必要な明るさを確保する。</li>
<li><strong>背景を見せたい：</strong>人物との明暗差を調整し、空間の情報を残す。</li>
<li><strong>視線を集めたい：</strong>見せたい場所と、それ以外の明るさの差を利用する。</li>
<li><strong>時間を感じさせたい：</strong>明るさだけでなく色・方向・影を組み合わせる。</li>
<li><strong>暗さを演出したい：</strong>全部を明るくせず、見せる範囲を意図的に限定する。</li>
</ul>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>照明器具の選び方</h2><p>器具名を覚えることより、「欲しい光の性質」から器具を選べるようになることを目標にします。</p></div>
<div class="hk-term-grid">
<div><h3>凸（平凸）</h3><p>比較的輪郭のある光を作りやすいスポット。狙った場所へ光を置きたいときに考えます。</p></div>
<div><h3>フレネル</h3><p>光の境界を比較的柔らかくしやすいスポット。人物やエリアを自然につなげたいときに考えます。</p></div>
<div><h3>PAR</h3><p>器具・レンズ・配光の組み合わせによって特徴が変わります。広がりや光量、設置位置との関係を確認します。</p></div>
<div><h3>エリスポット</h3><p>光の形をカッターやゴボなどで制御できるタイプ。窓、木漏れ日、特定の場所だけに落ちる光など、形を作りたいときに使います。</p></div>
<div><h3>LED器具</h3><p>色を電子的に変えられる機種など、多様なタイプがあります。器具によって配光、色再現、出力、制御方法が異なるため、機種仕様を確認します。</p></div>
<div><h3>器具を組み合わせる</h3><p>一台ですべてを解決しようとせず、「人物を見せる光」「背景を作る光」「輪郭を作る光」のように役割を分けて考えます。</p></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>フォーカスを学ぶ</h2><p>フォーカスは、器具をどこへ向けるかだけではなく、「どこに光を置くか」を決める作業です。</p></div>
<div class="hk-steps"><span>照らす対象を決める</span><b>→</b><span>器具の位置を確認</span><b>→</b><span>光軸を合わせる</span><b>→</b><span>広がりを調整</span><b>→</b><span>不要な光を確認</span><b>→</b><span>隣の明かりとつなぐ</span></div>
<div class="hk-panel" style="margin-top:14px"><p><strong>チェックするもの：</strong>人物の顔だけでなく、足元・背景・舞台袖・客席側への漏れ光まで確認します。フォーカスは「狙った場所に当たった」で終わらず、「狙っていない場所に余計な光がない」ことも重要です。</p></div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>明かり合わせを理解する</h2><p>仕込みが終わった照明を、実際の舞台上で作品に合わせて整えていく段階です。</p></div>
<div class="hk-steps"><span>明かりを点ける</span><b>→</b><span>人物を見る</span><b>→</b><span>背景を見る</span><b>→</b><span>影を見る</span><b>→</b><span>色を見る</span><b>→</b><span>明るさを調整</span><b>→</b><span>全体を確認</span></div>
<div class="hk-panel" style="margin-top:14px"><p>ここでは「一灯ずつきれいにする」だけでなく、複数の明かりが重なったときに人物の顔、衣裳、背景、舞台装置がどう見えるかを確認します。最終的には場面転換やキューまで含めて、作品全体の流れの中で判断します。</p></div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>ホリゾント系のライトとサイドスポット（SS）</h2><p>舞台照明では、人物を照らす器具だけでなく、背景や身体の輪郭を作るための専用・定番の明かりがあります。</p></div>
<div class="hk-equipment">
<article><h3>ホリゾントライト</h3><p>ホリゾント幕など、舞台奥の背景面を広く照らすための器具です。上向き・下向きなど、機種や設置方法によって配光が異なります。</p><p class="hk-tip">向いている考え方：<strong>「舞台の奥を一枚の面として作りたい」</strong></p></article>
<article><h3>ホリゾント系のLEDライト</h3><p>現在はLEDタイプもあり、複数の色を組み合わせて背景の色やグラデーションを作りやすい機種があります。色を変えられることと、舞台上の人物を直接照らすことは別の役割として考えます。</p><p class="hk-tip">考えること：<strong>「背景の色をどう変化させるか」</strong></p></article>
<article><h3>サイドスポット（SS）</h3><p>舞台袖側など、舞台の横方向から人物や身体を照らすスポットです。「SS」は現場でサイドスポットを指す略称として使われることがあります。身体の側面や輪郭に光を入れやすく、立体感や動きを強調できます。</p><p class="hk-tip">向いている考え方：<strong>「横から身体をどう見せるか」</strong></p></article>
<article><h3>SSを使うときのポイント</h3><p>左右のサイド光を組み合わせる場合は、片側だけが強くならないか、顔の影がどう出るか、足元まで光が届いているかを確認します。高さを変えると、身体のどの部分に光が入るかも変わります。</p><p class="hk-tip">チェックすること：<strong>「顔・胸・腰・足のどこに光があるか」</strong></p></article>
<article><h3>ホリゾントとSSの違い</h3><p>ホリゾント系は主に<strong>背景面</strong>、SSは主に<strong>人物や身体の側面</strong>を扱います。同じ「色のついた明かり」でも、照らす対象と方向が違います。</p></article>
<article><h3>組み合わせて考える</h3><p>たとえば、ホリゾントで夜の青い背景を作り、SSで人物の輪郭や身体の動きを拾う、といった役割分担ができます。さらに正面光やバックライトを加えて、人物の見え方を調整します。</p></article>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>灯体の仕組みを知る</h2><p>照明は「スイッチを入れると光る箱」ではありません。光源から出た光を、反射・レンズ・シャッター類で整えて、舞台上の必要な場所へ届けています。</p></div>
<div class="hk-light-fixture-diagram" role="img" aria-label="舞台照明灯体の基本構造を示す模式図">
  <div class="hk-fixture-part hk-fixture-source"><strong>光源</strong><small>LED・ランプなど</small></div>
  <div class="hk-fixture-arrow">→</div>
  <div class="hk-fixture-part"><strong>反射・集光</strong><small>光を前方へ集める</small></div>
  <div class="hk-fixture-arrow">→</div>
  <div class="hk-fixture-part"><strong>絞り・カッター</strong><small>光の範囲・形を整える</small></div>
  <div class="hk-fixture-arrow">→</div>
  <div class="hk-fixture-part"><strong>レンズ</strong><small>配光・焦点を整える</small></div>
  <div class="hk-fixture-arrow">→</div>
  <div class="hk-fixture-part hk-fixture-output"><strong>舞台上の光</strong><small>人物・背景・床など</small></div>
</div>
<p class="hk-diagram-caption">※これは灯体の種類を横断して考えるための概念図です。実際の構造・配置は器具によって異なります。</p>
<div class="hk-term-grid">
<div><h3>光源</h3><p>LEDやランプなど、実際に光を発生させる部分。光源の種類によって色、出力、発熱、制御方法などが変わります。</p></div>
<div><h3>反射・集光</h3><p>発生した光を前方へ効率よく送り、レンズなどと組み合わせて必要な配光を作ります。</p></div>
<div><h3>レンズ</h3><p>光の広がり方や焦点を整えます。凸、フレネル、エリスポットなど、器具によって光の性質が変わります。</p></div>
<div><h3>光を切る・絞る部分</h3><p>必要な場所だけを照らすために、絞りやカッターなどで光の範囲・形を調整します。</p></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>「絞り」と「カッター」は何が違う？</h2><p>どちらも光を制御しますが、考え方が違います。</p></div>
<div class="hk-control-diagram">
  <div class="hk-control-card"><div class="hk-beam hk-beam-wide"></div><h3>絞り（アイリス）</h3><p>光の開口を全体として狭くしたり広くしたりします。円形に近い光の大きさを調整するイメージです。</p><strong>「光の大きさを変える」</strong></div>
  <div class="hk-control-card"><div class="hk-beam hk-beam-cut"></div><h3>カッター（シャッター）</h3><p>器具内部のカッターを差し込んで、光の一部を直線的に切ります。窓や舞台袖など、不要な場所への光漏れを止めるのにも使います。</p><strong>「光の形・境界を切る」</strong></div>
</div>
<div class="hk-panel hk-lighting-note"><strong>実際のフォーカスでは：</strong>まず照らしたい範囲を決め、必要なら絞りで大きさを整え、カッターで舞台装置や袖などに当たる不要な光を切ります。その後、周囲の明かりとのつながりを確認します。</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>カッターの使い方</h2><p>「光をきれいにする」のではなく、「光を当てたくない場所を決める」と考えると分かりやすくなります。</p></div>
<div class="hk-steps"><span>照らしたい範囲を決める</span><b>→</b><span>余計な光を探す</span><b>→</b><span>カッターを少し入れる</span><b>→</b><span>境界を見る</span><b>→</b><span>舞台上で再確認</span></div>
<div class="hk-term-grid">
<div><h3>舞台袖を切る</h3><p>袖幕や舞台袖に光が漏れると、暗転時などに意図しない場所が見えることがあります。カッターで光を止めます。</p></div>
<div><h3>装置を切る</h3><p>壁やセットの一部だけを照らしたくない場合、カッターで境界を作ります。</p></div>
<div><h3>床を切る</h3><p>人物の上半身を中心に見せたいなど、床への余分な光を抑える場合にも使います。</p></div>
<div><h3>境界を観察する</h3><p>カッターを入れすぎると人物の一部まで欠けます。舞台全体を見ながら少しずつ調整します。</p></div>
</div>
</section>

<section class="hk-section">
<div class="hk-section-head"><h2>ゼラは「光の前に色を置く」</h2><p>カラーフィルターは、灯体から出た光に色を与えるために使います。</p></div>
<div class="hk-gel-use-diagram">
  <div class="hk-gel-diagram-item"><span class="hk-gel-light">白色光</span><small>灯体から出る光</small></div>
  <b>→</b>
  <div class="hk-gel-frame">ゼラ<br><small>フィルター</small></div>
  <b>→</b>
  <div class="hk-gel-stage">色のついた光<br><small>舞台上へ</small></div>
</div>
<div class="hk-term-grid">
<div><h3>ゼラを入れる場所</h3><p>一般的な灯体では、専用のフィルターホルダーやフレームにカラーフィルターをセットします。具体的な位置や固定方法は灯体の機種に従います。</p></div>
<div><h3>ゼラを選ぶ</h3><p>「青だから夜」と単純に決めず、人物、背景、時間、空間、心理など、何を表現するための色なのかを考えます。</p></div>
<div><h3>ゼラを重ねる</h3><p>複数枚を重ねると光量低下や色の変化が起こります。必要な場合以外は、目的を明確にして使います。</p></div>
<div><h3>熱に注意する</h3><p>従来型ランプ器具ではフィルターが高温になるため、フィルターの種類・耐熱性・器具の指定方法を確認します。LED器具でも機種ごとの指定を守ります。</p></div>
</div>
<div class="hk-warning"><strong>重要：</strong>灯体ごとにフィルターホルダーの位置、カッターや絞りの有無、操作方法が違います。ここでは共通する考え方を学び、実際の仕込みでは使用する器具の取扱説明書と劇場・現場の手順を優先します。</div>
</section>

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
</tbody></table>
<p style="margin-top:18px"><a class="hk-gel-cta" href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/filters/' ) ); ?>">ゼラ色見本データベースで色を探す →</a></p>
</div></section>

<section class="hk-section"><div class="hk-section-head"><h2>仕込みから本番まで</h2></div><div class="hk-steps"><span>器具・回路を確認</span><b>→</b><span>吊り込み</span><b>→</b><span>ケーブル・回路</span><b>→</b><span>フォーカス</span><b>→</b><span>明かり合わせ</span><b>→</b><span>場当たり</span><b>→</b><span>本番</span></div></section>

<section class="hk-section"><div class="hk-section-head"><h2>5分照明エチュード</h2></div><div class="hk-exercise"><h3>同じ人物を、3つの光で見せる</h3><ol><li>まず正面から普通に照らした人物を見る。</li><li>横からの光に変えて、顔や身体の影を観察する。</li><li>後ろからの光に変えて、輪郭がどう変わるかを見る。</li><li>「安心」「不安」「孤独」のどれかを、光の方向・強さ・色だけで表現する。</li></ol><p>ポイントは「きれいな照明を作る」ことではなく、<strong>光を変えると観客の読み方がどう変わるか</strong>を考えること。</p></div></section>

<?php elseif ( 'theatre-textbook/staff/lighting/filters' === $path ) : ?>
<?php
$gel_data_path = get_template_directory() . '/assets/data/gel-colors.json';
$gel_records = array();
if ( file_exists( $gel_data_path ) ) {
  $gel_json = file_get_contents( $gel_data_path );
  $gel_payload = json_decode( $gel_json, true );
  $gel_records = isset( $gel_payload['records'] ) && is_array( $gel_payload['records'] ) ? $gel_payload['records'] : array();
}
?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>ゼラ色見本データベース</h1><p>舞台照明用カラーフィルターを、メーカー・シリーズ・色番号・色名・透過率・用途から探し、画面上で参考表示色を比較できます。</p></header><?php
$selected_gel_id = isset( $_GET['gel'] ) ? sanitize_key( wp_unslash( $_GET['gel'] ) ) : '';
$selected_gel = null;
if ( $selected_gel_id ) {
  foreach ( $gel_records as $gel ) {
    if ( isset( $gel['id'] ) && $gel['id'] === $selected_gel_id ) { $selected_gel = $gel; break; }
  }
}
if ( $selected_gel ) :
  $detail_hex = ! empty( $selected_gel['reference_hex'] ) ? $selected_gel['reference_hex'] : '';
?>
<section class="hk-section hk-gel-detail">
  <div class="hk-gel-detail-top">
    <div class="hk-gel-detail-swatch <?php echo $detail_hex ? '' : 'is-missing'; ?>" style="<?php echo $detail_hex ? 'background:' . esc_attr( $detail_hex ) . ';' : ''; ?>">
      <?php if ( $detail_hex ) : ?><span>参考表示色</span><strong><?php echo esc_html( $detail_hex ); ?></strong><?php else : ?><span>参考色<br>未登録</span><?php endif; ?>
    </div>
    <div><p class="hk-textbook-kicker">ゼラ詳細</p><h2><?php echo esc_html( trim( ( $selected_gel['color_number'] ?? '' ) . ' ' . ( $selected_gel['color_name'] ?? '' ) ) ); ?></h2><p class="hk-gel-detail-brand"><?php echo esc_html( trim( ( $selected_gel['manufacturer'] ?? '' ) . ' / ' . ( $selected_gel['series'] ?? '' ) ) ); ?></p><p class="hk-gel-detail-warning">この色見本はRGBによる参考表示です。実際のゼラを通した光の色とは異なる場合があります。</p></div>
  </div>
  <div class="hk-gel-detail-values">
    <div><span>RGB</span><strong><?php echo ! empty( $selected_gel['reference_rgb'] ) ? esc_html( implode( ' / ', $selected_gel['reference_rgb'] ) ) : '未登録'; ?></strong></div>
    <div><span>HEX</span><strong><?php echo esc_html( $detail_hex ?: '未登録' ); ?></strong></div>
    <div><span>透過率</span><strong><?php echo esc_html( $selected_gel['transmission_display'] ?? '未登録' ); ?></strong></div>
    <div><span>色番号</span><strong><?php echo esc_html( $selected_gel['color_number'] ?? '—' ); ?></strong></div>
  </div>
  <div class="hk-gel-detail-columns">
    <div><h3>用途</h3><div class="hk-gel-tags"><?php foreach ( (array) ( $selected_gel['uses'] ?? array() ) as $use ) : ?><span><?php echo esc_html( $use ); ?></span><?php endforeach; ?></div></div>
    <div><h3>参考色の出典・方法</h3><p><?php echo esc_html( $selected_gel['reference_color_source'] ?? '未登録' ); ?><?php if ( ! empty( $selected_gel['reference_color_method'] ) ) : ?> / <?php echo esc_html( $selected_gel['reference_color_method'] ); ?><?php endif; ?></p></div>
    <div><h3>注意点</h3><p><?php echo esc_html( $selected_gel['notes'] ?? '—' ); ?></p></div>
  </div>
  <a class="hk-gel-back" href="<?php echo esc_url( remove_query_arg( 'gel' ) ); ?>">← 色見本一覧に戻る</a>
</section>
<?php endif; ?>

<section class="hk-section hk-gel-notice">
  <div class="hk-gel-notice-inner">
    <strong>RGB参考色について</strong>
    <p>このページの色見本は、ゼラの色を画面上で比較するための<strong>参考表示色</strong>です。実際のゼラを通した光の色を保証するものではありません。モニター、光源、照明器具、投射対象、周囲の明るさ、カメラなどによって見え方は変わります。</p>
    <p class="hk-gel-caution">舞台での最終的な色判断には、実物のゼラによる確認をおすすめします。</p>
  </div>
</section>

<section class="hk-section">
  <div class="hk-section-head"><h2>色を調べる</h2></div>
  <form class="hk-gel-search" id="hk-gel-search" onsubmit="return false;">
    <label>メーカー<input id="hk-gel-manufacturer" type="search" placeholder="例：Rosco / LEE"></label>
    <label>シリーズ<input id="hk-gel-series" type="search" placeholder="例：Roscolux"></label>
    <label>色番号<input id="hk-gel-number" type="search" placeholder="例：R26 / 26"></label>
    <label>色名<input id="hk-gel-name" type="search" placeholder="例：Light Red"></label>
    <label>透過率 下限<span class="hk-gel-unit">%</span><input id="hk-gel-min-transmission" type="number" min="0" max="100" placeholder="0"></label>
    <label>透過率 上限<span class="hk-gel-unit">%</span><input id="hk-gel-max-transmission" type="number" min="0" max="100" placeholder="100"></label>
    <div class="hk-gel-sort-wrap"><label>並び順<select id="hk-gel-sort"><option value="number">色番号順</option><option value="name">色名順</option><option value="transmission">透過率順</option><option value="manufacturer">メーカー順</option></select></label></div>
    <div class="hk-gel-actions"><button type="button" id="hk-gel-reset">条件をリセット</button></div>
  </form>
  <div class="hk-gel-usage"><span>用途から絞り込む</span>
    <label><input type="checkbox" value="人物">人物</label><label><input type="checkbox" value="顔・肌">顔・肌</label><label><input type="checkbox" value="背景">背景</label><label><input type="checkbox" value="空間">空間</label><label><input type="checkbox" value="夕景">夕景</label><label><input type="checkbox" value="朝・昼">朝・昼</label><label><input type="checkbox" value="夜">夜</label><label><input type="checkbox" value="月明かり">月明かり</label><label><input type="checkbox" value="日光">日光</label><label><input type="checkbox" value="室内">室内</label><label><input type="checkbox" value="心理表現">心理表現</label><label><input type="checkbox" value="幻想">幻想</label><label><input type="checkbox" value="特殊効果">特殊効果</label>
  </div>
  <p class="hk-filter-note">色番号はメーカー・シリーズごとに意味が異なります。「メーカー＋シリーズ＋番号」を1セットとして管理します。複数条件を指定した場合は、すべての条件に一致するレコードを表示します。</p>
</section>

<section class="hk-section">
  <div class="hk-gel-result-head"><div class="hk-section-head"><h2>色見本</h2></div><span id="hk-gel-count"></span></div>
  <div id="hk-gel-results" class="hk-gel-grid"></div>
  <div id="hk-gel-empty" class="hk-gel-empty" hidden>条件に一致する色見本はありません。</div>
  <p class="hk-filter-note">色見本の色はすべて「参考表示色」です。実物のゼラの色とは異なる場合があります。</p>
</section>

<section class="hk-section">
  <div class="hk-section-head"><h2>詳細ページの表示ルール</h2></div>
  <div class="hk-gel-detail-rule">
    <div><b>① 参考表示色</b><p>大きな色面とHEXを最上部に表示。色そのものを見比べられるようにします。</p></div>
    <div><b>② RGB / HEX</b><p>RGB値とHEX値を明記し、参考表示色であることを併記します。</p></div>
    <div><b>③ 製品情報</b><p>メーカー、シリーズ、色番号、色名、透過率をメーカー情報として整理します。</p></div>
    <div><b>④ HATAKITI情報</b><p>用途や現場での注意点は、メーカー公称情報と区別して表示します。</p></div>
  </div>
</section>

<section class="hk-section">
  <div class="hk-section-head"><h2>登録データの扱い</h2></div>
  <div class="hk-panel">
    <p>RGB値は、<strong>reference_rgb / reference_hex</strong> として独立管理します。出典と算出方法も記録し、根拠が確認できない色は推測で登録しません。</p>
    <p>メーカー名、シリーズ、色番号、色名、透過率はメーカー資料を優先し、HATAKITIの用途タグ・注意点は編集情報として分離します。</p>
  </div>
</section>

<script>
(function(){
  const records = <?php echo wp_json_encode( $gel_records, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>;
  const $ = id => document.getElementById(id);
  const results = $('hk-gel-results'), count = $('hk-gel-count'), empty = $('hk-gel-empty');
  const params = new URLSearchParams(window.location.search);

  function val(id){ return ($(id).value || '').trim().toLowerCase(); }
  function esc(v){ return String(v ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s])); }
  function selectedUses(){ return Array.from(document.querySelectorAll('.hk-gel-usage input:checked')).map(x=>x.value); }
  function textMatch(value, query){ return !query || String(value || '').toLowerCase().includes(query); }
  function applyUrl(){
    const map={manufacturer:'hk-gel-manufacturer',series:'hk-gel-series',number:'hk-gel-number',name:'hk-gel-name',min_transmission:'hk-gel-min-transmission',max_transmission:'hk-gel-max-transmission',sort:'hk-gel-sort'};
    Object.keys(map).forEach(k=>{if(params.has(k)) $(map[k]).value=params.get(k);});
    if(params.has('uses')) {
      const uses=params.get('uses').split(',').filter(Boolean);
      document.querySelectorAll('.hk-gel-usage input').forEach(x=>x.checked=uses.includes(x.value));
    }
  }
  function syncUrl(){
    const q=new URLSearchParams();
    [['manufacturer','hk-gel-manufacturer'],['series','hk-gel-series'],['number','hk-gel-number'],['name','hk-gel-name'],['min_transmission','hk-gel-min-transmission'],['max_transmission','hk-gel-max-transmission'],['sort','hk-gel-sort']].forEach(([k,id])=>{if($(id).value)q.set(k,$(id).value);});
    const uses=selectedUses(); if(uses.length)q.set('uses',uses.join(','));
    const url=window.location.pathname+(q.toString()?'?'+q.toString():'');
    history.replaceState(null,'',url);
  }
  function swatch(record){
    if(!record.reference_hex) return '<div class="hk-gel-swatch hk-gel-swatch-missing"><span>参考色<br>未登録</span></div>';
    const rgb=record.reference_rgb ? '<small>RGB '+esc(record.reference_rgb.join(' / '))+'</small>' : '';
    return '<div class="hk-gel-swatch" style="--hk-gel-color:'+esc(record.reference_hex)+'"><span>参考表示色</span><strong>'+esc(record.reference_hex)+'</strong>'+rgb+'</div>';
  }
  function card(r){
    const uses=(r.uses||[]).map(x=>'<span>'+esc(x)+'</span>').join('');
    return '<article class="hk-gel-card"><a class="hk-gel-card-link" href="?gel='+encodeURIComponent(r.id||'')+'">'+swatch(r)+'<div class="hk-gel-meta"><div class="hk-gel-brand">'+esc(r.manufacturer||'')+' / '+esc(r.series||'')+'</div><h3>'+esc(r.color_number||'')+' '+esc(r.color_name||'')+'</h3><dl><div><dt>透過率</dt><dd>'+esc(r.transmission_display||'—')+'</dd></div><div><dt>参考色</dt><dd>'+esc(r.reference_hex||'未登録')+'</dd></div></dl><div class="hk-gel-tags">'+uses+'</div><p class="hk-gel-card-note">※表示色はRGBによる参考色です。</p></div></a></article>';
  }
  function render(){
    const mq=val('hk-gel-manufacturer'), sq=val('hk-gel-series'), nq=val('hk-gel-number'), cq=val('hk-gel-name');
    const min=parseFloat($('hk-gel-min-transmission').value), max=parseFloat($('hk-gel-max-transmission').value), uses=selectedUses();
    let list=records.filter(r=>textMatch(r.manufacturer,mq)&&textMatch(r.series,sq)&&textMatch(r.color_number,nq)&&textMatch(r.color_name,cq));
    list=list.filter(r=>{const t=Number(r.transmission);return !Number.isFinite(t)||(Number.isNaN(min)||t>=min)&&(Number.isNaN(max)||t<=max);});
    if(uses.length) list=list.filter(r=>(r.uses||[]).some(u=>uses.includes(u)));
    const sort=$('hk-gel-sort').value;
    list.sort((a,b)=>String(a[sort==='number'?'color_number':sort==='name'?'color_name':sort==='manufacturer'?'manufacturer':'transmission']||'').localeCompare(String(b[sort==='number'?'color_number':sort==='name'?'color_name':sort==='manufacturer'?'manufacturer':'transmission']||''),'ja',{numeric:true}));
    count.textContent=list.length+'件';
    results.innerHTML=list.map(card).join('');
    empty.hidden=list.length!==0;
    syncUrl();
  }
  document.querySelectorAll('#hk-gel-search input, #hk-gel-search select, .hk-gel-usage input').forEach(x=>x.addEventListener('input',render));
  $('hk-gel-reset').addEventListener('click',()=>{document.getElementById('hk-gel-search').reset();document.querySelectorAll('.hk-gel-usage input').forEach(x=>x.checked=false);render();});
  applyUrl(); render();
})();
</script>

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
.hk-four{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.hk-four b{font-family:var(--hk-font-serif);font-size:18px}.hk-equipment{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.hk-equipment h3,.hk-term-grid h3{font-family:var(--hk-font-serif);margin-top:0}.hk-warning{margin:20px 0;padding:18px;border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card);line-height:1.8}.hk-filter-table{width:100%;border-collapse:collapse}.hk-filter-table th,.hk-filter-table td{border:1px solid var(--hk-border);padding:12px;text-align:left}.hk-filter-table th{color:var(--hk-accent-warm)}.hk-light-fixture-diagram{display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;padding:26px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}.hk-fixture-part{min-width:115px;padding:18px 12px;text-align:center;border:1px solid var(--hk-border);background:var(--hk-bg-card)}.hk-fixture-part strong{display:block;font-family:var(--hk-font-serif)}.hk-fixture-part small{display:block;margin-top:6px;color:var(--hk-fg-dim);font-size:11px;line-height:1.5}.hk-fixture-output{border-color:var(--hk-accent-warm)}.hk-fixture-arrow{font-size:20px;color:var(--hk-accent-warm)}.hk-diagram-caption{font-size:11px;color:var(--hk-fg-faint);margin-top:9px}.hk-control-diagram{display:grid;grid-template-columns:1fr 1fr;gap:16px}.hk-control-card{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:20px}.hk-control-card h3{font-family:var(--hk-font-serif)}.hk-control-card>strong{color:var(--hk-accent-warm)}.hk-beam{height:105px;position:relative;margin-bottom:18px;overflow:hidden;background:var(--hk-bg-card)}.hk-beam:after{content:"";position:absolute;left:50%;top:8px;transform:translateX(-50%);width:0;height:0;border-left:70px solid transparent;border-right:70px solid transparent;border-top:88px solid var(--hk-accent-warm);opacity:.7}.hk-beam-wide:after{border-left-width:105px;border-right-width:105px}.hk-beam-cut:after{clip-path:polygon(0 0,100% 0,78% 100%,0 100%)}.hk-gel-use-diagram{display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap;padding:28px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}.hk-gel-diagram-item,.hk-gel-frame,.hk-gel-stage{min-width:125px;min-height:75px;display:flex;align-items:center;justify-content:center;text-align:center;padding:12px;border:1px solid var(--hk-border)}.hk-gel-light{font-weight:700}.hk-gel-frame{border-color:var(--hk-accent-warm)}.hk-gel-stage{background:var(--hk-bg-card)}.hk-gel-diagram-item small,.hk-gel-frame small,.hk-gel-stage small{display:block;font-size:10px;color:var(--hk-fg-dim)}@media(max-width:700px){.hk-control-diagram{grid-template-columns:1fr}.hk-light-fixture-diagram,.hk-gel-use-diagram{justify-content:flex-start}.hk-fixture-arrow{transform:rotate(90deg)}}.hk-steps{display:flex;justify-content:center;align-items:center;gap:10px;flex-wrap:wrap;border:1px solid var(--hk-border);padding:24px;background:var(--hk-bg-elevated)}.hk-steps b{color:var(--hk-accent-warm)}.hk-term-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.hk-exercise{border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card);padding:24px;line-height:1.9}.hk-exercise li{margin:8px 0}
@media(max-width:850px){.hk-four{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.hk-staff-hero h1{font-size:30px}.hk-staff-grid,.hk-equipment,.hk-term-grid,.hk-four{grid-template-columns:1fr}.hk-steps{justify-content:flex-start}}
.hk-gel-detail{border-top:1px solid var(--hk-border)}.hk-gel-detail-top{display:grid;grid-template-columns:minmax(260px,420px) 1fr;gap:28px;align-items:center}.hk-gel-detail-swatch{min-height:300px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.55)}.hk-gel-detail-swatch span{font-size:12px}.hk-gel-detail-swatch strong{font-size:28px;margin-top:8px}.hk-gel-detail-swatch.is-missing{background:repeating-linear-gradient(135deg,var(--hk-bg-card),var(--hk-bg-card) 12px,var(--hk-bg-elevated) 12px,var(--hk-bg-elevated) 24px);color:var(--hk-fg-dim);text-shadow:none;text-align:center}.hk-gel-detail-top h2{font-family:var(--hk-font-serif);font-size:30px}.hk-gel-detail-brand{color:var(--hk-accent-warm)}.hk-gel-detail-warning{border-left:3px solid var(--hk-accent-warm);padding:12px 15px;background:var(--hk-bg-card);color:var(--hk-fg-dim);line-height:1.8}.hk-gel-detail-values{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:20px}.hk-gel-detail-values>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:18px}.hk-gel-detail-values span{display:block;font-size:10px;color:var(--hk-accent-warm)}.hk-gel-detail-values strong{display:block;margin-top:6px}.hk-gel-detail-columns{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:12px}.hk-gel-detail-columns>div{border:1px solid var(--hk-border);padding:18px;background:var(--hk-bg-elevated)}.hk-gel-detail-columns h3{font-family:var(--hk-font-serif);margin-top:0}.hk-gel-detail-columns p{color:var(--hk-fg-dim);line-height:1.8;font-size:13px}.hk-gel-back{display:inline-block;margin-top:20px;color:var(--hk-accent-warm)}@media(max-width:700px){.hk-gel-detail-top,.hk-gel-detail-values,.hk-gel-detail-columns{grid-template-columns:1fr}.hk-gel-detail-swatch{min-height:220px}}.hk-gel-notice{margin-top:0}.hk-gel-notice-inner{border:1px solid var(--hk-border);border-left:4px solid var(--hk-accent-warm);background:var(--hk-bg-card);padding:22px}.hk-gel-notice-inner p{color:var(--hk-fg-dim);line-height:1.9;margin:.7em 0}.hk-gel-caution{font-weight:700;color:var(--hk-fg)!important}.hk-gel-search{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.hk-gel-search label{font-size:12px;color:var(--hk-fg-dim);position:relative}.hk-gel-search input,.hk-gel-search select{display:block;width:100%;box-sizing:border-box;margin-top:7px;padding:11px;border:1px solid var(--hk-border);background:var(--hk-bg-card);color:var(--hk-fg)}.hk-gel-unit{float:right;font-size:11px}.hk-gel-sort-wrap,.hk-gel-actions{display:flex;align-items:end}.hk-gel-actions button{width:100%;padding:11px;border:1px solid var(--hk-border);background:transparent;color:var(--hk-fg);cursor:pointer}.hk-gel-actions button:hover{border-color:var(--hk-accent-warm);color:var(--hk-accent-warm)}.hk-gel-usage{display:flex;gap:9px;flex-wrap:wrap;margin-top:16px;padding:15px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}.hk-gel-usage>span{width:100%;font-size:12px;color:var(--hk-accent-warm);margin-bottom:2px}.hk-gel-usage label{font-size:12px}.hk-gel-usage input{margin-right:4px}.hk-gel-result-head{display:flex;justify-content:space-between;align-items:end}.hk-gel-result-head #hk-gel-count{color:var(--hk-fg-dim);font-size:13px}.hk-gel-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.hk-gel-card{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);overflow:hidden}.hk-gel-card:hover{border-color:var(--hk-accent-warm)}.hk-gel-card-link{display:block;color:inherit;text-decoration:none}.hk-gel-swatch{height:180px;background:var(--hk-gel-color);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;text-shadow:0 1px 2px rgba(0,0,0,.5);color:#fff}.hk-gel-swatch span{font-size:11px;letter-spacing:.08em}.hk-gel-swatch strong{font-size:20px}.hk-gel-swatch small{font-size:11px}.hk-gel-swatch-missing{background:repeating-linear-gradient(135deg,var(--hk-bg-card),var(--hk-bg-card) 10px,var(--hk-bg-elevated) 10px,var(--hk-bg-elevated) 20px);text-align:center;text-shadow:none;color:var(--hk-fg-dim)}.hk-gel-meta{padding:18px}.hk-gel-brand{font-size:11px;color:var(--hk-accent-warm)}.hk-gel-meta h3{margin:5px 0 14px;font-family:var(--hk-font-serif)}.hk-gel-meta dl{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:0}.hk-gel-meta dl div{border-top:1px solid var(--hk-border);padding-top:8px}.hk-gel-meta dt{font-size:10px;color:var(--hk-fg-dim)}.hk-gel-meta dd{margin:3px 0 0;font-size:13px}.hk-gel-tags{display:flex;gap:5px;flex-wrap:wrap;margin-top:13px}.hk-gel-tags span{font-size:10px;border:1px solid var(--hk-border);padding:4px 7px;color:var(--hk-fg-dim)}.hk-gel-card-note{font-size:10px;color:var(--hk-fg-faint);margin-bottom:0}.hk-gel-empty{border:1px dashed var(--hk-border);padding:35px;text-align:center;color:var(--hk-fg-dim)}.hk-gel-detail-rule{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.hk-gel-detail-rule>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:18px}.hk-gel-detail-rule b{color:var(--hk-accent-warm)}.hk-gel-detail-rule p{color:var(--hk-fg-dim);line-height:1.8;font-size:13px}.hk-filter-note strong{color:var(--hk-fg)}
@media(max-width:900px){.hk-gel-search{grid-template-columns:repeat(2,1fr)}.hk-gel-grid{grid-template-columns:repeat(2,1fr)}.hk-gel-detail-rule{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.hk-gel-search,.hk-gel-grid,.hk-gel-detail-rule{grid-template-columns:1fr}}
.hk-filter-search{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.hk-filter-search label{font-size:12px;color:var(--hk-fg-dim)}.hk-filter-search input{display:block;width:100%;box-sizing:border-box;margin-top:7px;padding:11px;border:1px solid var(--hk-border);background:var(--hk-bg-card);color:var(--hk-fg)}.hk-filter-note{font-size:13px;color:var(--hk-fg-dim);line-height:1.8}.hk-filter-record{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.hk-filter-record>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:18px}.hk-filter-record span{display:block;font-size:11px;color:var(--hk-accent-warm);margin-bottom:7px}.hk-filter-record strong{display:block}.hk-filter-record small{display:block;color:var(--hk-fg-faint);margin-top:8px}.hk-filter-table-wrap{overflow-x:auto}.hk-filter-database{width:100%;min-width:1050px;border-collapse:collapse}.hk-filter-database th,.hk-filter-database td{border:1px solid var(--hk-border);padding:11px;text-align:left;vertical-align:top}.hk-filter-database th{color:var(--hk-accent-warm);font-size:12px}.hk-filter-database td{font-size:13px}.hk-swatch-placeholder{width:58px;height:42px;display:flex;align-items:center;justify-content:center;border:1px dashed var(--hk-border);font-size:10px;color:var(--hk-fg-faint)}
@media(max-width:700px){.hk-filter-search,.hk-filter-record{grid-template-columns:1fr}}
</style>
<?php get_footer(); ?>