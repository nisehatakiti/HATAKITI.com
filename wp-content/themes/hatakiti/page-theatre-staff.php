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
<header class="hk-staff-hero">
  <p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜舞台を作る</p>
  <h1>照明</h1>
  <p>光の方向、広がり、明るさ、色、影を設計し、舞台上の時間・場所・人物・空気を観客に伝える仕事。</p>
</header>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>導入　照明って何をしている？</h2>
    <p>照明は、ただ暗い舞台を明るくするだけではありません。観客に何を見せるのかを、光によって作る仕事です。</p>
  </div>
  <p>舞台に立っている俳優を、客席から見る。当たり前のように見えるこの光も、偶然そこにあるわけではありません。</p>
  <p>どこを明るくするのか。どこを暗くするのか。どの方向から光を当てるのか。どんな色にするのか。光をどこまで広げるのか。</p>
  <p>照明は、それらを一つひとつ考えて作られています。</p>

  <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-question.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「照明って、暗い舞台を明るくするためのものじゃないの？」</p></div></div><div class="hk-nyakakichi-followup"><p>もちろん、それも大切な仕事です。でも、照明にはそれ以上の役割があります。</p></div>

  
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-01-same-stage.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-01-same-stage.png' ) ); ?>" alt="同じ舞台でも照明によって見え方が変わることを示す図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-01-same-stage.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>同じ舞台でも、照明を変えると「見え方」や観客の視線が変わります。</figcaption>
</figure>

  <div class="hk-term-grid">
    <div><h3>人物を見せる</h3><p>俳優の顔や表情、身体の動きを観客に見せます。</p></div>
    <div><h3>場所を見せる</h3><p>屋外、部屋、森、夜の街など、空間の印象を作ります。</p></div>
    <div><h3>時間を見せる</h3><p>朝、昼、夕方、夜など、時間の変化を光で表現できます。</p></div>
    <div><h3>視線を誘導する</h3><p>見てほしい場所に光を集め、観客の視線を導きます。</p></div>
    <div><h3>空間を分ける</h3><p>一つの舞台の中に複数の場所やエリアを作ることができます。</p></div>
    <div><h3>見せない</h3><p>暗くすることで、見せたくない場所を隠したり、観客に想像させたりできます。</p></div>
  </div>

  <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-interested.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「じゃあ、照明を考えるときって、何を考えればいいの？」</p></div></div><div class="hk-nyakakichi-followup"><p>まずは、光を5つの視点に分けて考えてみましょう。</p></div>
</section>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>第1章　光を考える5つの視点</h2>
    <p>最初から「この灯体を使おう」と考えるのではなく、まず「どんな光が必要なのか」を考えます。</p>
  </div>

  
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-02-five-viewpoints.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-02-five-viewpoints.png' ) ); ?>" alt="照明を考える5つの視点を示す図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-02-five-viewpoints.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>照明を考える基本は、明るさ・方向・広がり・色・影の5つです。</figcaption>
</figure>

  <p>舞台照明では、特に次の5つの視点が基本になります。</p>
  <div class="hk-four hk-lighting-five">
    <div><b>1. 明るさ</b><p>どこを、どれくらい明るくするのか。</p></div>
    <div><b>2. 方向</b><p>どこから光を当てるのか。</p></div>
    <div><b>3. 広がり</b><p>どこまで光を広げるのか。</p></div>
    <div><b>4. 色</b><p>どんな色の光にするのか。</p></div>
    <div><b>5. 影</b><p>どんな影を作るのか、あるいは消すのか。</p></div>
  </div>

  <section class="hk-subsection">
    <h3>1-1　明るさ</h3>
    <p>まず考えるのは、どれくらい明るくするのかです。舞台全体を明るくする必要がある場面もあれば、人物だけを明るくしたい場面もあります。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-thinking.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「じゃあ、舞台全部を一番明るくすれば見やすいんじゃない？」</p></div></div>
    <p>明るければ明るいほど良い、というわけではありません。全部が同じ明るさだと、どこを見ればよいのか分かりにくくなることがあります。</p>
    
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-03-brightness.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-03-brightness.png' ) ); ?>" alt="舞台全体を同じ明るさにした場合と見せたい場所を明るくした場合の比較図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-03-brightness.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>明るさは「見えるかどうか」だけでなく、「どこを見るか」にも関係します。</figcaption>
</figure>
    <p>照明では、<strong>「どこを明るくするか」</strong>が重要です。</p>
  </section>

  <section class="hk-subsection">
    <h3>1-2　方向</h3>
    <p>同じ人物でも、正面、斜め前、横、後ろ、上、下など、どこから光を当てるかによって見え方が変わります。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-question.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「同じ明るさでも、向きが違うだけでそんなに変わるの？」</p></div></div>
    <p>変わります。光がどこから来ているように見えるかによって、顔の陰影、身体の立体感、空間の奥行きが変わります。</p>
    <p>方向については第4章で詳しく扱います。ここでは、<strong>「光の向きも照明の設計要素」</strong>だと覚えておきましょう。</p>
  </section>

  <section class="hk-subsection">
    <h3>1-3　広がり</h3>
    <p>次に考えるのが、光をどこまで広げるかです。一人だけを照らす狭い光もあれば、舞台全体を覆うような広い光もあります。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-surprised.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「じゃあ、光って広げたり狭くしたりできるの？」</p></div></div>
    <p>できます。灯体の種類やレンズ、絞りなどを使って、光の広がり方を調整します。</p>
    
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-04-beam-spread.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-04-beam-spread.png' ) ); ?>" alt="広い光・中くらいの光・狭い光を比較する図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-04-beam-spread.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>光の広がりを変えると、舞台上で同じ明かりとして扱う範囲も変わります。</figcaption>
</figure>
    <p>なお、<strong>「狭い光＝暗い光」ではありません。</strong>光の明るさと広がりは別の考え方です。</p>
  </section>

  <section class="hk-subsection">
    <h3>1-4　色</h3>
    <p>白い光だけでなく、青、赤、オレンジ、緑、紫など、さまざまな色の光を使うことができます。</p>
    <p>ただし、「夜だから青」「夕方だから赤」というように、色だけで照明を決める必要はありません。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-confused.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「赤い光を当てたら、全部『怖い場面』になるの？」</p></div></div>
    <p>そうとは限りません。同じ赤でも、明るさ、方向、周囲の色、影との組み合わせによって印象は変わります。</p>
    <p><strong>色は、照明を作るための一つの手段です。</strong></p>
  </section>

  <section class="hk-subsection">
    <h3>1-5　影</h3>
    <p>最後が影です。照明を考えるとき、つい「どこを明るくするか」ばかり考えてしまいます。でも、実は影も重要です。</p>
    <p>光があれば、どこかに影ができます。光の方向を変えれば影の方向も変わります。複数方向から光を当てれば、影が薄くなることもあります。</p>
    <p>つまり、<strong>照明を考えることは、影をどう作るかを考えることでもあります。</strong></p>
    
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-05-light-and-shadow.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-05-light-and-shadow.png' ) ); ?>" alt="光の方向によって人物の影の出方が変わることを示す図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-05-light-and-shadow.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>光の方向が変わると、影の位置や濃さが変わり、人物の見え方も変わります。</figcaption>
</figure>
  </section>

  <div class="hk-panel hk-summary">
    <h3>第1章まとめ</h3>
    <p>照明を考えるときは、<strong>明るさ・方向・広がり・色・影</strong>の5つを意識します。</p>
    <p>この5つを組み合わせることで、舞台の「見え方」を作っていきます。</p>
  </div>

  <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-pointing.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「なるほど……。でも、実際にその光を出すには、どんな機械を使うの？」</p></div></div><div class="hk-nyakakichi-followup"><p>ここから、いよいよ灯体を見ていきます。</p></div>
</section>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>第2章　灯体を知る</h2>
    <p>舞台照明では、光を作るための器具を「灯体」と呼びます。灯体には、それぞれ得意な光があります。</p>
  </div>

  <p>広い範囲を照らすもの。狭い範囲を照らすもの。輪郭を作るもの。背景を照らすもの。形を切り取るもの。</p>
  <p>だから、「一番明るい灯体を使えばいい」というわけではありません。</p>
  <p><strong>どんな光が必要なのかに合わせて、灯体を選ぶ。</strong>これが基本です。</p>

  <section class="hk-subsection">
    <h3>2-1　凸（平凸）</h3>
    <p>舞台照明でよく使われる灯体の一つが、凸レンズを使ったスポットです。「凸」や「平凸」と呼ばれることがあります。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-question.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「なんで『凸』っていうの？」</p></div></div>
    
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-06-convex-lens.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-06-convex-lens.png' ) ); ?>" alt="凸レンズの断面と形の由来を示す図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-06-convex-lens.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>凸レンズは中央が厚く、外側に向かって薄くなる形をしています。</figcaption>
</figure>
    <p>凸レンズは、光を集めたり、光の広がり方を調整したりするために使われます。凸系の灯体では、比較的しっかりした光を作ることができます。</p>
    <p>そのため、人物や特定の場所を照らしたり、光の範囲を調整したりする用途で使われます。</p>
  </section>

  <section class="hk-subsection">
    <h3>2-2　フレネル</h3>
    <p>次にフレネルです。フレネルもレンズを使った灯体ですが、凸とは少し違った構造になっています。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-thinking.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「凸とフレネルって、どっちもレンズなのに何が違うの？」</p></div></div>
    
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-07-convex-vs-fresnel.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-07-convex-vs-fresnel.png' ) ); ?>" alt="凸レンズとフレネルレンズの構造を比較する図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-07-convex-vs-fresnel.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>フレネルレンズは、レンズの働きを段階的な形状で実現した構造です。</figcaption>
</figure>
    <p>舞台照明では、比較的柔らかな境界の光を作りながら、光の広がりを調整する用途などで使われます。</p>
    <p>凸とフレネルは、どちらが「上」というものではありません。<strong>必要な光に応じて使い分けるもの</strong>です。</p>
  </section>

  <section class="hk-subsection">
    <h3>2-3　PAR</h3>
    <p>PARは、レンズや反射鏡などを利用して、特徴のある方向性を持った光を作るタイプの灯体です。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-interested.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「PARって、普通のスポットと違うの？」</p></div></div>
    
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-08-par-beam.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-08-par-beam.png' ) ); ?>" alt="PAR灯体と方向性のある光を示す図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-08-par-beam.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>PARでは、機種や配光によって光の広がり方が変わります。実際の仕様を確認して使います。</figcaption>
</figure>
    <p>PARは、空間に方向性のある光を作ったり、複数台を組み合わせたりする用途があります。</p>
    <p>ここでも大切なのは、<strong>「PARだから何でもできる」ではなく、「PARが得意な光を利用する」</strong>という考え方です。</p>
  </section>

  <section class="hk-subsection">
    <h3>2-4　エリスポット</h3>
    <p>エリスポットは、光の形や範囲を比較的細かくコントロールできるタイプの灯体です。</p>
    <p>特定の人物、特定の場所、特定の範囲を狙って照らしたいときに活躍します。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-surprised.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「この灯体だけ、光の形まで作れるの？」</p></div></div>
    <p>レンズによる光に、カッターやゴボなどを組み合わせることで、光の形そのものを設計できます。カッターについては後の章で詳しく扱います。</p>
  </section>

  <section class="hk-subsection">
    <h3>2-5　ホリゾントライト</h3>
    <p>舞台の奥にある壁や幕など、背景を広く照らすために使われるのがホリゾントライトです。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-understood.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「人物を照らすライトとは違って、背景を照らすライトもあるんだ？」</p></div></div>
    
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-09-horizont.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-09-horizont.png' ) ); ?>" alt="ホリゾントライトで舞台奥の背景を照らす断面図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-09-horizont.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>ホリゾント系の灯体は、舞台奥の背景面を広く照らすために使われます。</figcaption>
</figure>
    <p>人物だけを照らしても、背景が真っ暗なら舞台全体の印象は大きく変わります。背景を一つの面として作ることで、舞台空間の印象を整えることができます。</p>
  </section>

  <section class="hk-subsection">
    <h3>2-6　LED灯体</h3>
    <p>最近の舞台照明では、LEDを光源として使う灯体も多くなっています。</p>
    <p>LED灯体には、白色光を出すものもあれば、赤・緑・青などを組み合わせて色を作るものもあります。さらに、明るさや色などを電子的に制御できるものもあります。</p>
    <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-idea.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「じゃあ、LEDならゼラを入れなくても色を変えられるの？」</p></div></div>
    <p>灯体によります。LEDの光源そのものの組み合わせで色を作れる器具もあります。一方で、従来型の灯体ではカラーフィルターを使って光の色を変えることがあります。</p>
    <p><strong>「LED＝ゼラが絶対にいらない」</strong>という単純な話ではありません。灯体の種類や使い方によって、色の作り方は変わります。</p>
  </section>

  
<figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-10-fixture-overview.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-10-fixture-overview.png' ) ); ?>" alt="舞台照明の代表的な灯体と、それぞれの得意な光をまとめた図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-10-fixture-overview.png</div>
    <p>この位置に図解を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>灯体にはそれぞれ得意な光があります。「何を使うか」より先に「どんな光が必要か」を考えます。</figcaption>
</figure>

  <div class="hk-panel hk-summary">
    <h3>第2章まとめ</h3>
    <p>ここで覚えてほしいのは、器具の細かな仕様ではありません。</p>
    <p><strong>灯体には、それぞれ得意な光がある。</strong></p>
    <p>そして、<strong>「何を使うか」より先に、「どんな光が必要なのか」を考える。</strong>これが照明を考えるときの基本です。</p>
  </div>

  <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-happy.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「灯体って、ただ光る箱じゃないんだね。」</p></div></div><div class="hk-nyakakichi-followup"><p>そうです。灯体の中では、光源から出た光を反射させたり、レンズで整えたり、広がりを調整したりして、舞台に必要な光へ変えています。</p><p>次は、その「灯体の中」を実際に見てみましょう。</p></div>
</section>

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

.hk-illustration-placeholder{margin:18px 0 0;border:1px dashed var(--hk-border);background:var(--hk-bg-card);min-height:260px;padding:28px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;box-sizing:border-box}.hk-illustration-placeholder-label{font-size:11px;letter-spacing:.14em;color:var(--hk-accent-warm);margin-bottom:8px}.hk-illustration-placeholder-file{font-family:monospace;font-size:16px;color:var(--hk-fg);margin-bottom:14px}.hk-illustration-placeholder p{max-width:760px;margin:0;color:var(--hk-fg-dim);line-height:1.8;font-size:13px}.hk-lighting-intro-illustration .hk-illustration-placeholder{min-height:220px}

.hk-nyakakichi{display:flex;align-items:center;gap:18px;margin:28px 0 10px;padding:0;background:transparent;border:0;color:var(--hk-fg);position:relative;overflow:visible}.hk-nyakakichi-image{flex:0 0 110px;text-align:center}.hk-nyakakichi-image img{display:block;width:110px;height:auto;max-height:170px;object-fit:contain;margin:0 auto}.hk-nyakakichi-question{flex:1;position:relative;background:#454545;border-radius:16px;padding:16px 20px;color:#fff;line-height:1.8}.hk-nyakakichi-question:before{content:"";position:absolute;left:-12px;top:24px;border-top:10px solid transparent;border-bottom:10px solid transparent;border-right:14px solid #454545}.hk-nyakakichi-question p{margin:0;color:#fff}.hk-nyakakichi-question strong{color:#fff}.hk-nyakakichi-followup{margin:0 0 24px 128px;line-height:1.9;color:var(--hk-fg)}.hk-nyakakichi-followup p{margin:0 0 8px}.hk-nyakakichi-followup p:last-child{margin-bottom:0}@media(max-width:600px){.hk-nyakakichi{align-items:center;gap:12px;margin-top:24px}.hk-nyakakichi-image{flex-basis:90px}.hk-nyakakichi-image img{width:90px;max-height:145px}.hk-nyakakichi-question{padding:13px 15px}.hk-nyakakichi-question:before{left:-10px;top:20px;border-top-width:8px;border-bottom-width:8px;border-right-width:11px}.hk-nyakakichi-followup{margin-left:102px;margin-bottom:20px}}</style>
<?php get_footer(); ?>