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
<?php elseif ( 'theatre-textbook/staff/lighting' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>照明</h1><p>光の方向、広がり、明るさ、色、影を設計し、舞台上の時間・場所・人物・空気を観客に伝える仕事。</p></header>
<section class="hk-section">
  <div class="hk-section-head"><h2>照明を学ぶ</h2><p>照明は「暗い舞台を明るくする」だけではありません。光をどう作り、どこへ届け、何を見せるのかを順番に学んでいきます。</p></div>
  <div class="hk-chapter-list">
    <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-1/' ) ); ?>"><span>第1章</span><strong>光を考える5つの視点</strong><small>明るさ・方向・広がり・色・影</small></a>
    <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-2/' ) ); ?>"><span>第2章</span><strong>灯体を知る</strong><small>凸・フレネル・PAR・エリスポット・ホリゾント・LED</small></a>
    <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-3/' ) ); ?>"><span>第3章</span><strong>灯体の中で光はどうなっている？</strong><small>光源・反射鏡・レンズ・光軸・絞り・カッター・ゼラホルダー</small></a>
    <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-4/' ) ); ?>"><span>第4章</span><strong>光をどこから当てる？</strong><small>前明かり・サイド・バック・トップ・ホリゾント・SS</small></a>
  </div>
</section>
<?php elseif ( 'theatre-textbook/staff/lighting/chapter-1' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>第1章　光を考える5つの視点</h1><p>照明を考えるときに、まず押さえておきたい「明るさ・方向・広がり・色・影」の5つの視点を学びます。</p></header>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <span class="hk-chapter-nav-disabled">← 前の章</span>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-2/' ) ); ?>">次へ →</a>
</nav>
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
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <span class="hk-chapter-nav-disabled">← 前の章</span>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-2/' ) ); ?>">次へ →</a>
</nav>

<?php elseif ( 'theatre-textbook/staff/lighting/chapter-2' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>第2章　灯体を知る</h1><p>舞台照明で使われる代表的な灯体と、それぞれが得意とする光を学びます。</p></header>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-1/' ) ); ?>">← 前へ</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-3/' ) ); ?>">次へ →</a>
</nav>
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
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-1/' ) ); ?>">← 前へ</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-3/' ) ); ?>">次へ →</a>
</nav>

<?php elseif ( 'theatre-textbook/staff/lighting/chapter-3' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>第3章　灯体の中で光はどうなっている？</h1><p>光源から出た光が、灯体の中でどのように反射・集光・整形され、舞台へ届くのかを学びます。</p></header>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-2/' ) ); ?>">← 前へ</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <span class="hk-chapter-nav-disabled">次の章 →</span>
</nav>
<section class="hk-section">
  <div class="hk-section-head">
    <h2>第3章　灯体の中で光はどうなっている？</h2>
    <p>灯体は、ただ光を出す箱ではありません。光源から出た光を、反射・集光・整形し、舞台に必要な形へ変えていきます。</p>
  </div>

  <p>第2章では、凸、フレネル、PAR、エリスポット、ホリゾントライト、LED灯体など、いろいろな灯体を見ました。</p>
  <p>ここでは一歩中に入って、<strong>「灯体の中で、光がどう加工されているのか」</strong>を見てみます。</p>

  <div class="hk-light-fixture-diagram" aria-label="灯体の中の光の流れ">
    <div class="hk-fixture-part"><strong>光源</strong><small>ランプ・LEDなど<br>光を出す</small></div>
    <span class="hk-fixture-arrow">→</span>
    <div class="hk-fixture-part"><strong>反射鏡</strong><small>光を反射して<br>利用しやすくする</small></div>
    <span class="hk-fixture-arrow">→</span>
    <div class="hk-fixture-part"><strong>レンズ</strong><small>光を集めたり<br>広げたりする</small></div>
    <span class="hk-fixture-arrow">→</span>
    <div class="hk-fixture-part"><strong>絞り・カッター</strong><small>光の範囲や形を<br>調整する</small></div>
    <span class="hk-fixture-arrow">→</span>
    <div class="hk-fixture-part hk-fixture-output"><strong>舞台の光</strong><small>必要な場所へ<br>必要な形で届ける</small></div>
  </div>
  <p class="hk-diagram-caption">※すべての灯体が同じ構造ではありません。ここでは、照明器具を理解するための基本的な考え方として整理しています。</p>

  <figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-12-fixture-parts.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-12-fixture-parts.png' ) ); ?>" alt="灯体内部の光源、反射鏡、レンズ、絞り、カッター、ゼラホルダーの位置を示す図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-12-fixture-parts.png</div>
    <p>この位置に灯体内部の部品配置図を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>灯体の中では、光源から出た光を反射・集光・整形し、必要な光に加工します。絞り・カッター・ゼラホルダーがどこにあるかも、この図で確認できます。</figcaption>
</figure>

  <section class="hk-subsection">
    <h3>3-1　光源</h3>
    <p>まず、光そのものを出す部分が<strong>光源</strong>です。</p>
    <p>昔から使われてきた舞台照明ではハロゲンランプなどの電球が代表的でした。現在はLEDを光源にした灯体も増えています。</p>
    <p>光源が変わると、消費電力、発熱、色の作り方、調光方法などにも違いが出ます。</p>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-question.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「光源って、要するに電球みたいなもの？」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>そうです。まず「光を生み出すところ」が光源です。ただし、舞台照明では光源そのものを見るのではなく、その光を灯体の中でどう使うかが重要になります。</p></div>
  </section>

  <section class="hk-subsection">
    <h3>3-2　反射鏡</h3>
    <p>光源から出た光は、すべてが舞台に向かって進むわけではありません。そこで使われるのが<strong>反射鏡</strong>です。</p>
    <p>反射鏡で光を反射させることで、光源から出た光を前方へ効率よく導きます。</p>
    <p>灯体によって反射鏡の形や配置は異なります。つまり、反射鏡も光の「広がり方」や「集まり方」に関係する重要な部分です。</p>
  </section>

  <section class="hk-subsection">
    <h3>3-3　レンズ</h3>
    <p>次にレンズです。レンズは光の進む方向を変え、光を集めたり、広げたりするために使われます。</p>
    <p>第2章で見た<strong>凸レンズ</strong>や<strong>フレネルレンズ</strong>も、この役割を担っています。</p>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-thinking.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「レンズを動かすと、光の広がりも変わるの？」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>変わります。灯体によって仕組みは違いますが、レンズや光源との位置関係を変えることで、光の広がり方を調整できるものがあります。</p></div>

    <p>この「光をどこまで広げるか」は、第1章で出てきた<strong>5つの視点の「広がり」</strong>につながっています。</p>
  </section>

  <section class="hk-subsection">
    <h3>3-4　光軸とビーム</h3>
    <p>灯体から出ていく光を考えるとき、中心となる方向を<strong>光軸</strong>として考えると分かりやすくなります。</p>
    <p>そして、灯体から舞台へ向かって進む光のまとまりを<strong>ビーム</strong>として捉えます。</p>
    <p>照明を仕込むときは、「どこに灯体があるか」だけでなく、<strong>その灯体からどの方向へ、どれくらいの範囲の光が出るのか</strong>を考えます。</p>

    <div class="hk-panel">
      <h3>光を考えるときの基本</h3>
      <p><strong>灯体の位置 ＋ 光軸の方向 ＋ ビームの広がり</strong></p>
      <p>この3つを合わせて考えると、「この灯体をどこへ向ければ、どこが照らされるか」をイメージしやすくなります。</p>
    </div>
  </section>

  <section class="hk-subsection">
    <h3>3-5　絞り</h3>
    <p>灯体によっては、光の広がりを調整するための<strong>絞り</strong>があります。</p>
    <p>ここでいう絞りは、単純に「暗くする」という意味ではありません。<strong>光の広がる範囲を狭くする</strong>ための機構として考えると分かりやすいでしょう。</p>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-confused.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「絞るって、暗くするってことじゃないの？」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>照明では、そこを分けて考えます。明るさを変えるのは調光の仕事。絞りは、光をどこまで広げるかを調整するためのものです。</p></div>

    <figure class="hk-illustration">
<?php $lighting_image_path = WP_PLUGIN_DIR . '/hatakiti-core/assets/images/lighting/lighting-11-beam-width.png'; ?>
<?php if ( file_exists( $lighting_image_path ) ) : ?>
  <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-11-beam-width.png' ) ); ?>" alt="広いビームと狭いビームの違いを、灯体から出る光の広がりと照射範囲で比較した図" loading="lazy">
<?php else : ?>
  <div class="hk-illustration-placeholder" aria-label="図解準備中">
    <div class="hk-illustration-placeholder-label">ILLUSTRATION</div>
    <div class="hk-illustration-placeholder-file">lighting-11-beam-width.png</div>
    <p>この位置に広いビームと狭いビームの比較図を配置します。</p>
  </div>
<?php endif; ?>
<figcaption>ビーム角が広いと照射範囲が広がり、狭いと光が集中します。同じ高さ・同じ出力でも、光の広がり方によって照射範囲が変わります。</figcaption>
</figure>
  </section>

  <section class="hk-subsection">
    <h3>3-6　カッター</h3>
    <p>エリスポットなどに備わっている<strong>カッター</strong>は、光の一部を遮って、光の形や境界を作るための機構です。</p>
    <p>たとえば、舞台の床には当てたいけれど、背景の幕には光を当てたくない。あるいは、窓のような四角い範囲だけを照らしたい。</p>
    <p>そんなときに、カッターを使って<strong>「光をどこまで当てるか」</strong>を細かく決めます。</p>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-interested.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「光を切って、形まで作れるんだ！」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>そうです。カッターは「光を減らす部品」ではなく、<strong>光の境界を作るための道具</strong>として考えると理解しやすくなります。</p></div>

    <div class="hk-panel hk-warning">
      <strong>絞りとカッターの違い</strong>
      <p><strong>絞り：</strong>光の広がりを調整する。</p>
      <p><strong>カッター：</strong>光の一部を遮って、境界や形を作る。</p>
    </div>
  </section>

  <section class="hk-subsection">
    <h3>3-7　ゼラホルダー</h3>
    <p>従来型の灯体では、光の色を変えるために<strong>カラーフィルター（通称：ゼラ）</strong>を使うことがあります。</p>
    <p>そのゼラを灯体の前に固定するための部分が<strong>ゼラホルダー</strong>です。</p>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-idea.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「じゃあ、色を変えたいときは、このゼラホルダーにゼラを入れるんだね？」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>その通りです。灯体によってホルダーの位置やサイズは異なりますが、基本的には灯体の光がゼラを通るように取り付けます。</p></div>

    <p>ゼラそのものについては、<a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/filters/' ) ); ?>">ゼラ色見本データベース</a>で色番号や透過率などを確認できます。</p>
  </section>

  <div class="hk-panel hk-summary">
    <h3>第3章まとめ</h3>
    <p>灯体の中では、光源から出た光をそのまま舞台へ送っているわけではありません。</p>
    <p><strong>光源 → 反射鏡 → レンズ → 絞り・カッター → 舞台</strong>というように、光を整えながら必要な形に近づけています。</p>
    <p>灯体によって構造は違いますが、<strong>「光を作る」ではなく「必要な光に加工する」</strong>と考えると、灯体の役割が分かりやすくなります。</p>
  </div>

  <div class="hk-nyakakichi">
    <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-pointing.png" alt="にゃかきち" loading="lazy"></div>
    <div class="hk-nyakakichi-question"><p>「中の仕組みが分かると、灯体の違いも少し分かってきた！」</p></div>
  </div>
  <div class="hk-nyakakichi-followup">
    <p>次は、灯体を「どこから当てるか」です。</p>
    <p>同じ灯体でも、取り付ける場所や方向が変われば、舞台に届く光は大きく変わります。</p>
  </div>
</section>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-2/' ) ); ?>">← 前へ</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <span class="hk-chapter-nav-disabled">次の章 →</span>
</nav>

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

<?php elseif ( 'theatre-textbook/staff/lighting/chapter-4' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>第4章　光をどこから当てる？</h1><p>同じ灯体でも、取り付ける場所と向きを変えると舞台の見え方は大きく変わります。ここでは照明の「位置」と「方向」を整理します。</p></header>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-3/' ) ); ?>">← 第3章</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <span class="hk-chapter-nav-disabled">次の章 →</span>
</nav>
<section class="hk-section">
  <div class="hk-section-head">
    <h2>導入　「どこから当てるか」で光は変わる</h2>
    <p>第1章で、照明には「方向」という視点があることを学びました。第4章では、その方向を舞台上の具体的な位置として見ていきます。</p>
  </div>
  <p>同じ明るさの灯体でも、客席側から当てるのか、横から当てるのか、後ろから当てるのかで、人物の立体感や影の出方は大きく変わります。</p>
  <p>照明では「灯体を何台使うか」だけでなく、<strong>「どこに置き、どこへ向けるか」</strong>がとても重要です。</p>

  <div class="hk-nyakakichi">
    <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-question.png" alt="にゃかきち" loading="lazy"></div>
    <div class="hk-nyakakichi-question"><p>「同じ灯体でも、置く場所が違うだけでそんなに変わるの？」</p></div>
  </div>
  <div class="hk-nyakakichi-followup"><p>変わります。まずは、舞台のどの方向から光が来るのかを言葉で整理してみましょう。</p></div>

  <div class="hk-panel">
    <h3>照明位置を考える基本</h3>
    <p><strong>前・横・後ろ・上・下／ホリゾント</strong>など、光の入口を分けて考えます。</p>
    <p>実際の劇場では、これらを組み合わせて一つの明かりを作ります。</p>
  </div>
</section>

<section class="hk-section">
  <div class="hk-section-head"><h2>第4章　舞台のどこから光を入れる？</h2><p>まずは代表的な照明位置を、一つずつ見ていきます。</p></div>

  <section class="hk-subsection">
    <h3>4-1　前明かり</h3>
    <p><strong>前明かり</strong>は、客席側から舞台上の人物や空間へ向ける光です。</p>
    <p>人物の顔や表情を観客に見せるための基本的な光として使われます。舞台全体を見せるためのベースとして考えることもできます。</p>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-interested.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「客席から舞台に向かって当てるのが前明かりなんだね！」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>そうです。正面だけでなく、少し斜め上から当てるなど、劇場やプランによって角度を調整します。</p></div>

    <div class="hk-light-position-diagram">
      <div class="hk-stage-diagram">
        <span class="hk-stage-label">舞台</span><span class="hk-actor">●</span>
        <span class="hk-light-ray hk-ray-front">↗</span><span class="hk-position-label hk-position-front">前明かり</span><span class="hk-audience">客席</span>
      </div>
    </div>
  </section>

  <section class="hk-subsection">
    <h3>4-2　サイドライト</h3>
    <p><strong>サイドライト</strong>は、舞台の左右方向から入れる光です。</p>
    <p>横から光を当てると、身体の輪郭や立体感が出やすくなります。ダンスや身体表現では、身体のラインを見せるために使われることもあります。</p>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-thinking.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「横から当てると、顔より身体の形が目立つの？」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>そういう傾向があります。もちろん角度や高さによって変わりますが、正面からの光とは違う立体感を作れます。</p></div>

    <div class="hk-light-position-diagram">
      <div class="hk-stage-diagram">
        <span class="hk-stage-label">舞台</span><span class="hk-actor">●</span>
        <span class="hk-light-ray hk-ray-side-left">→</span><span class="hk-light-ray hk-ray-side-right">←</span>
        <span class="hk-position-label hk-position-left">サイド</span><span class="hk-position-label hk-position-right">サイド</span>
      </div>
    </div>
  </section>

  <section class="hk-subsection">
    <h3>4-3　バックライト</h3>
    <p><strong>バックライト</strong>は、人物の後ろ側から客席方向へ向ける光です。</p>
    <p>人物の輪郭を浮かび上がらせたり、背景との分離を作ったりするのに役立ちます。人物の正面を直接照らす光とは役割が異なります。</p>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-surprised.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「後ろから光を当てたら、顔が見えなくならない？」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>正面からの光が弱ければ、そうなることがあります。でも、それが目的ならシルエットとして使えます。前明かりなどと組み合わせれば、輪郭を出しながら人物も見せられます。</p></div>

    <div class="hk-light-position-diagram">
      <div class="hk-stage-diagram">
        <span class="hk-stage-label">舞台</span><span class="hk-actor">●</span>
        <span class="hk-light-ray hk-ray-back">↙</span><span class="hk-position-label hk-position-back">バック</span><span class="hk-audience">客席</span>
      </div>
    </div>
  </section>

  <section class="hk-subsection">
    <h3>4-4　トップライト</h3>
    <p><strong>トップライト</strong>は、人物や舞台空間の上方から下向きに入れる光です。</p>
    <p>頭や肩、床などに特徴的な影を作りやすく、人物を周囲の空間から切り出すように見せることもできます。</p>
    <p>ただし、真上に近いほど顔の目の周りに影ができやすいため、人物をきれいに見せたい場合は他の方向の光と組み合わせます。</p>

    <div class="hk-light-position-diagram">
      <div class="hk-stage-diagram hk-stage-top">
        <span class="hk-stage-label">舞台</span><span class="hk-actor">●</span>
        <span class="hk-light-ray hk-ray-top">↓</span><span class="hk-position-label hk-position-top">トップ</span>
      </div>
    </div>
  </section>

  <section class="hk-subsection">
    <h3>4-5　ホリゾントからの光</h3>
    <p>舞台奥のホリゾント幕や背景を照らすための光には、<strong>ローホリゾント</strong>と<strong>アッパーホリゾント</strong>があります。</p>
    <p>ローホリは下側から、アッパーホリは上側からホリゾントへ光を入れます。どちらも背景の明るさや色を作るために使われます。</p>

    <div class="hk-panel">
      <h3>ローホリとアッパーホリ</h3>
      <p><strong>ローホリ：</strong>舞台床付近から上向きにホリゾントを照らす。</p>
      <p><strong>アッパーホリ：</strong>舞台上方から下向きにホリゾントを照らす。</p>
      <p>上下から組み合わせることで、背景を均一に近づけたり、グラデーションを作ったりすることもできます。</p>
    </div>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-idea.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「ホリゾントライトって、舞台上の人を照らすライトじゃなくて背景用なんだ！」</p></div>
    </div>
  </section>

  <section class="hk-subsection">
    <h3>4-6　SSって何？</h3>
    <p>舞台照明でよく出てくる<strong>SS</strong>は、灯体の種類ではありません。</p>
    <p>SSは一般に<strong>サイド・サイド</strong>の位置、つまり舞台の左右側方から入れるサイド光を指す言葉として使われます。劇場や現場によって呼び方や細かな区分は異なることがあります。</p>

    <div class="hk-nyakakichi">
      <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-confused.png" alt="にゃかきち" loading="lazy"></div>
      <div class="hk-nyakakichi-question"><p>「SSっていう名前のライトがあるのかと思ってた！」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>ここは初心者が混乱しやすいところです。「SSを入れる」は、基本的にはサイド方向から光を入れる、という意味で使われます。</p></div>
  </section>
</section>

<section class="hk-section">
  <div class="hk-section-head"><h2>4-7　組み合わせて考える</h2><p>実際の舞台では、前明かりだけ、サイドだけというように一種類だけで終わることは多くありません。</p></div>
  <p>たとえば人物を見せたいなら、前明かりで顔を見せながら、サイドで身体の立体感を作り、バックで輪郭を出す、といった組み合わせが考えられます。</p>
  <p>重要なのは「この位置の光が正解」と覚えることではありません。<strong>どんな見え方が欲しいから、どの方向の光を足すのか</strong>を考えることです。</p>

  <div class="hk-nyakakichi">
    <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-pointing.png" alt="にゃかきち" loading="lazy"></div>
    <div class="hk-nyakakichi-question"><p>「なるほど。灯体の名前より先に、どこから光が欲しいかを考えるんだね！」</p></div>
  </div>
  <div class="hk-nyakakichi-followup"><p>その通りです。次の章では、その光を実際に図面の上でどう配置するのかを見ていきます。</p></div>

  <div class="hk-panel hk-summary">
    <h3>第4章まとめ</h3>
    <p>代表的な照明位置には、<strong>前明かり・サイド・バック・トップ・ホリゾント</strong>があります。</p>
    <p>ローホリとアッパーホリは、ホリゾントを照らすための上下方向の光です。</p>
    <p>そして<strong>SSは灯体の種類ではなく、サイド方向の照明位置を表す言葉</strong>です。</p>
    <p>照明を考えるときは、「何の灯体を使うか」だけでなく、「どこから光が来てほしいか」を先に考えると整理しやすくなります。</p>
  </div>
</section>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-3/' ) ); ?>">← 第3章</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <span class="hk-chapter-nav-disabled">次の章 →</span>
</nav>

<?php elseif ( isset( $pages[$path] ) && 'theatre-textbook/staff/sound' === $path ) : ?>