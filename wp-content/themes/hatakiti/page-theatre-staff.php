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
    <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-5/' ) ); ?>"><span>第5章</span><strong>照明図を読む</strong><small>平面図・立面図・灯体・回路・色・方向</small></a>
    <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-6/' ) ); ?>"><span>第6章</span><strong>電気と操作</strong><small>回路・調光器・フェーダー・チャンネル・DMX</small></a>
    <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-7/' ) ); ?>"><span>第7章</span><strong>色を作る</strong><small>ゼラ・色温度・LED・混色・色の組み合わせ</small></a>
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

<?php elseif ( 'theatre-textbook/staff/lighting/chapter-4' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>第4章　光をどこから当てる？</h1><p>同じ灯体でも、取り付ける場所と向きを変えると舞台の見え方は大きく変わります。ここでは照明の「位置」と「方向」を整理します。</p></header>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-3/' ) ); ?>">← 第3章</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-5/' ) ); ?>">第5章 →</a>
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

    <figure class="hk-illustration">
      <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-13-front-light.png' ) ); ?>" alt="前明かりを上から見た図と客席から見た図。客席側から舞台上の人物へ光を当てる位置関係を示す図" loading="lazy">
      <figcaption>前明かりは客席側から舞台へ向けて入れる光です。上から見ると、客席側にある灯体から舞台上へ光が向かっていることが分かります。</figcaption>
    </figure>
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

    <figure class="hk-illustration">
      <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-14-side-light.png' ) ); ?>" alt="サイドライトを上から見た図と客席から見た図。舞台の左右から人物へ光を当てる位置関係を示す図" loading="lazy">
      <figcaption>サイドライトは舞台の左右方向から入る光です。上から見ると左右から光が入り、正面から見ると身体の側面や輪郭に光が当たることが分かります。</figcaption>
    </figure>
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

    <figure class="hk-illustration">
      <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-15-back-light.png' ) ); ?>" alt="バックライトを上から見た図と客席から見た図。人物の後ろ側から客席方向へ光を当てる位置関係を示す図" loading="lazy">
      <figcaption>バックライトは人物の後ろ側から入る光です。正面から見ると、人物の輪郭が光によって浮かび上がります。</figcaption>
    </figure>
  </section>

  <section class="hk-subsection">
    <h3>4-4　トップライト</h3>
    <p><strong>トップライト</strong>は、人物や舞台空間の上方から下向きに入れる光です。</p>
    <p>頭や肩、床などに特徴的な影を作りやすく、人物を周囲の空間から切り出すように見せることもできます。</p>
    <p>ただし、真上に近いほど顔の目の周りに影ができやすいため、人物をきれいに見せたい場合は他の方向の光と組み合わせます。</p>

    <figure class="hk-illustration">
      <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-16-top-light.png' ) ); ?>" alt="トップライトを上から見た図と客席から見た図。舞台上方から人物へ下向きに光を当てる位置関係を示す図" loading="lazy">
      <figcaption>トップライトは舞台や人物の上方から下向きに入る光です。正面から見ると、頭や肩、床に特徴的な影ができます。</figcaption>
    </figure>
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

<?php elseif ( 'theatre-textbook/staff/lighting/chapter-5' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>第5章　照明図を読む</h1><p>照明を実際に仕込むときは、「どこに、どの灯体を、どの方向へ向けるか」を図面に落とします。ここでは照明図を読むための基本を学びます。</p></header>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-4/' ) ); ?>">← 第4章</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-6/' ) ); ?>">第6章 →</a>
</nav>
<section class="hk-section">
  <div class="hk-section-head">
    <h2>実際の仕込み図を見てみよう</h2>
    <p>実際の現場では、舞台の平面図や立面図に、バトン、灯体の種類、灯体番号、回路番号、照射方向などを書き込んで仕込み図を作ります。劇場や現場によって記号や番号の付け方は異なりますが、まずは「どこに何を仕込んで、どこを照らすのか」を読み取ることが大切です。</p>
  </div>
  <figure class="hk-illustration">
    <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-17-lighting-plot-overview.png' ) ); ?>" alt="前明かり、サイドライト、バックライト、トップライトの仕込み図。平面図と立面図にバトン、灯体番号、回路番号、灯体種類、照射方向を示した例" loading="lazy">
    <figcaption>仕込み図の例。平面図では灯体の位置と照射方向、立面図ではバトンの高さと光の角度を確認します。記号や番号のルールは劇場・現場によって異なります。</figcaption>
  </figure>
</section>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>導入　照明を「図」にする</h2>
    <p>頭の中で「ここから当てたい」と考えるだけでは、実際の仕込みにはつながりません。照明では、その情報を図面にしてスタッフ同士で共有します。</p>
  </div>
  <p>照明図は、単なる「きれいな絵」ではありません。</p>
  <p><strong>どこに灯体があり、何を使い、どの方向を向き、どの回路につながり、どんな色を使うのか。</strong>そうした情報を整理するための道具です。</p>

  <div class="hk-nyakakichi">
    <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-question.png" alt="にゃかきち" loading="lazy"></div>
    <div class="hk-nyakakichi-question"><p>「照明図って、舞台の上にある灯体を上から見た絵なの？」</p></div>
  </div>
  <div class="hk-nyakakichi-followup"><p>その考え方が基本です。ただし、上から見た図だけでは分からない情報もあるので、目的に応じて別の図も使います。</p></div>
</section>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>5-1　まずは平面図を読む</h2>
    <p>平面図は、舞台を上から見た図です。灯体の位置や向きを把握するのに向いています。</p>
  </div>

  <figure class="hk-illustration">
    <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-18-floor-plan.png' ) ); ?>" alt="照明仕込み図の平面図。客席、1サス・2サス・3サス、サイド、フロアコンセント、灯体番号、回路番号、灯体種類、照射方向を示した例" loading="lazy">
    <figcaption>平面図の例。バトンやサイドなどの仕込み位置と、各灯体の番号・回路・種類・照射方向を読み取ります。</figcaption>
  </figure>

  <p>平面図を見るときは、まず<strong>「舞台はどこか」「客席はどこか」</strong>を確認します。</p>
  <p>そのうえで、灯体がどこに取り付けられ、どちらを向いているのかを読みます。</p>

  <div class="hk-four">
    <div><b>位置</b><p>どのバトン、サイド、床置きなどに灯体があるか。</p></div>
    <div><b>向き</b><p>光軸がどの方向を向いているか。</p></div>
    <div><b>種類</b><p>凸、フレネル、PAR、エリスポットなど何の灯体か。</p></div>
    <div><b>番号</b><p>その灯体を仕込み・操作するときに識別するための情報。</p></div>
  </div>

  <div class="hk-nyakakichi">
    <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-thinking.png" alt="にゃかきち" loading="lazy"></div>
    <div class="hk-nyakakichi-question"><p>「同じ場所に灯体が2台あったら、どうやって見分けるの？」</p></div>
  </div>
  <div class="hk-nyakakichi-followup"><p>そこで、灯体番号や回路番号などを使って区別します。劇場や現場によって表記方法は異なりますが、「一台ずつ識別できるようにする」という考え方は共通しています。</p></div>
</section>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>5-2　灯体の記号を見る</h2>
    <p>照明図では、灯体を実物そっくりに描くのではなく、記号で表すことが多くあります。</p>
  </div>
  <p>記号そのものは劇場、学校、劇団、照明会社などによって違う場合があります。そのため、<strong>「この形なら必ずこの灯体」と決めつけない</strong>ことが大切です。</p>

  <figure class="hk-illustration">
    <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-19-fixture-symbols.png' ) ); ?>" alt="照明仕込み図で使う灯体記号の例。凸レンズ、フレネル、PAR、エリスポット、ホリゾントライト、LED灯体と図面上の表記例を示した図" loading="lazy">
    <figcaption>灯体記号の例。実際の記号・表記方法・番号体系は劇場や現場によって異なるため、図面の凡例を確認します。</figcaption>
  </figure>

  <p>大切なのは記号を暗記することではありません。</p>
  <p><strong>「この記号は何を表しているのか」を図面の凡例や現場のルールから確認する。</strong>これが基本です。</p>
</section>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>5-3　立面図・断面図で高さを見る</h2>
    <p>平面図は上から見た位置関係には強い一方で、「どの高さにあるか」は分かりにくいことがあります。</p>
  </div>
  <p>そこで、横から見た立面図や断面図を使います。</p>

  <figure class="hk-illustration">
    <img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/lighting-20-elevation-section.png' ) ); ?>" alt="照明仕込み図の立面図・断面図。フロントバトン、トップバトン、バックバトンの高さと照射角度、舞台・客席・演者位置の関係を示した例" loading="lazy">
    <figcaption>立面図・断面図の例。バトンの高さ、舞台面との位置関係、照射角度や照射範囲を確認します。</figcaption>
  </figure>

  <div class="hk-term-grid">
    <div><h3>平面図</h3><p>上から見て、舞台上の位置と方向を確認する図。</p></div>
    <div><h3>立面図</h3><p>横から見て、高さや上下方向の関係を確認する図。</p></div>
    <div><h3>断面図</h3><p>舞台と客席などを切った断面として、空間の高さや奥行きを確認する図。</p></div>
    <div><h3>凡例</h3><p>図面で使っている記号や略号が何を意味するのかを示す説明。</p></div>
  </div>
</section>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>5-4　「灯体・回路・色」を分けて考える</h2>
    <p>照明図を読むときに混乱しやすいのが、灯体そのものと電気的な情報を同じものとして考えてしまうことです。</p>
  </div>

  <div class="hk-lighting-flow">
    <div><strong>灯体</strong><small>何の器具か</small></div>
    <b>＋</b>
    <div><strong>位置・方向</strong><small>どこからどこへ当てるか</small></div>
    <b>＋</b>
    <div><strong>回路</strong><small>どの系統で操作するか</small></div>
    <b>＋</b>
    <div><strong>色</strong><small>どんな光にするか</small></div>
  </div>

  <p>たとえば、「フレネルを1台使う」という情報だけでは、照明プランは完成しません。</p>
  <p>どこに吊るのか。どこへ向けるのか。どの回路につなぐのか。ゼラを入れるのか。必要ならどれくらいの明るさにするのか。</p>
  <p>このように、<strong>一台の灯体にも複数の情報が組み合わさっています。</strong></p>

  <div class="hk-nyakakichi">
    <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-interested.png" alt="にゃかきち" loading="lazy"></div>
    <div class="hk-nyakakichi-question"><p>「じゃあ、照明図は灯体の場所だけ書けばいいわけじゃないんだね？」</p></div>
  </div>
  <div class="hk-nyakakichi-followup"><p>その通りです。現場で必要な情報を、誰が見ても分かる形に整理するのが照明図の役割です。</p></div>
</section>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>5-5　照明図を読む順番</h2>
    <p>情報が多い図面は、最初から全部を読もうとすると混乱します。順番を決めて見ると読みやすくなります。</p>
  </div>

  <div class="hk-steps">
    <span>① 舞台・客席を確認</span><b>→</b>
    <span>② 灯体の位置を見る</span><b>→</b>
    <span>③ 向きを見る</span><b>→</b>
    <span>④ 灯体の種類を見る</span><b>→</b>
    <span>⑤ 番号・回路を見る</span><b>→</b>
    <span>⑥ 色・備考を見る</span>
  </div>

  <p>この順番で見ると、「どこにあるのか」から「何をする灯体なのか」へ、少しずつ情報を増やしていけます。</p>
</section>

<section class="hk-section">
  <div class="hk-section-head">
    <h2>5-6　照明図から実際の仕込みを想像する</h2>
    <p>図面を読めるようになると、まだ劇場に入っていなくても仕込みの準備を考えられるようになります。</p>
  </div>

  <div class="hk-exercise">
    <h3>5分照明エチュード「図面から光を想像する」</h3>
    <ol>
      <li>舞台中央に俳優が一人立っているとします。</li>
      <li>前明かり、サイド、バックの3方向から光がある平面図を想像します。</li>
      <li>それぞれの光だけを一つずつ点灯したら、人物のどこが明るくなり、どこに影ができるか考えます。</li>
      <li>3つを同時に点灯したとき、影や立体感がどう変わるか考えます。</li>
      <li>最後に「この場面で一番見せたいものは何か」を一文で書きます。</li>
    </ol>
    <p>答え合わせは「正しい配置を当てる」ことではありません。<strong>図面上の情報から、舞台上の光を頭の中で想像する</strong>練習です。</p>
  </div>

  <div class="hk-panel hk-summary">
    <h3>第5章まとめ</h3>
    <p>照明図は、舞台上の光をスタッフ同士で共有するための設計図です。</p>
    <p><strong>平面図で位置と方向、立面図・断面図で高さや空間の関係</strong>を読みます。</p>
    <p>さらに、灯体の種類、番号、回路、色などの情報を組み合わせて、一台ずつの役割を整理します。</p>
    <p>図面を読むときは、まず「どこにあるか」、次に「どこへ向いているか」、そして「何の灯体で、どう操作するか」という順番で考えると分かりやすくなります。</p>
  </div>

  <div class="hk-nyakakichi">
    <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-pointing.png" alt="にゃかきち" loading="lazy"></div>
    <div class="hk-nyakakichi-question"><p>「図面が読めると、実際の舞台でどんな光になるか想像できるんだね！」</p></div>
  </div>
  <div class="hk-nyakakichi-followup"><p>そうです。次は、その灯体を実際に電気につなぎ、操作するための仕組みを見ていきます。</p></div>
</section>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-4/' ) ); ?>">← 第4章</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <span class="hk-chapter-nav-disabled">第6章 →</span>
</nav>

<?php elseif ( 'theatre-textbook/staff/lighting/chapter-6' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>第6章　電気と操作</h1><p>灯体を舞台に仕込んだだけでは、照明は動きません。ここでは、灯体から回路、調光器、操作卓まで、光を実際に動かすための仕組みを学びます。</p></header>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-5/' ) ); ?>">← 第5章</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <span class="hk-chapter-nav-disabled">第7章 →</span>
</nav>
<section class="hk-section">
  <div class="hk-section-head"><h2>導入　光は「電気」だけでは動かない</h2><p>灯体を舞台に仕込んだあと、電源と操作の仕組みをつないで、はじめて「点ける・消す・明るさを変える」ができるようになります。</p></div>
  <p>ここで覚えたいのは、すべての灯体が同じ仕組みで動くわけではない、ということです。</p>
  <p>白熱・ハロゲン系の灯体では、電力を調整して明るさを変える<strong>調光</strong>が基本です。一方、LED灯体では、専用の電源や制御信号を使って明るさや色などを操作するものがあります。</p>
  <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-question.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「スイッチを入れれば、そのまま明るくなるんじゃないの？」</p></div></div>
  <div class="hk-nyakakichi-followup"><p>舞台照明では、どの回路につながっているか、どのように制御するか、という仕組みを通って灯体が動きます。</p></div>
</section>
<section class="hk-section">
  <div class="hk-section-head"><h2>6-1　まず「回路」を知る</h2><p>回路は、照明設備と電源をつなぐための基本単位です。</p></div>
  <p>仕込み図に「回路番号」が書かれているのは、灯体がどの電源系統につながるのかを識別するためです。</p>
  <p>仕込み図の「回路番号」や記号は、劇場によって表記方法が違う場合があります。<strong>番号だけで判断せず、図面の凡例や劇場の設備表を見る</strong>のが基本です。</p>
  <figure class="hk-illustration hk-lighting-chapter-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/' ) ); ?>lighting-21-lighting-system-overview.png" alt="照明システム全体のつながりを示す図。電源、分電盤・回路、調光器、灯体と、照明卓、チャンネル、制御の関係を示す" loading="lazy"><figcaption>照明システム全体のつながり。電源側の流れと、操作側の流れを分けて見ると理解しやすくなります。</figcaption></figure>
</section>
<section class="hk-section">
  <div class="hk-section-head"><h2>6-2　調光器（Dimmer）は何をしている？</h2><p>従来型の舞台照明では、調光器が灯体への電力を調整して明るさを変えます。</p></div>
  <p>第3章で出てきた「絞り」と「調光器」は別物です。</p>
  <div class="hk-term-grid"><div><h3>絞り</h3><p>灯体の光学系で、光の広がりや出方を調整するもの。</p></div><div><h3>調光器</h3><p>電気側で、対応する灯体への出力を調整するもの。</p></div><div><h3>フェーダー</h3><p>操作卓などで、明るさや制御値を操作するための操作子。</p></div><div><h3>チャンネル</h3><p>操作卓上で、照明を一つの操作単位として扱うための番号・制御単位。</p></div></div>
  <p><strong>フェーダーを上げる＝灯体の絞りが開く、ではありません。</strong>操作卓から制御情報が送られ、その先の機器が設定に応じて灯体を動かします。</p>
  <figure class="hk-illustration hk-lighting-chapter-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/' ) ); ?>lighting-22-dimmer.png" alt="調光器の役割を示す図。電源から調光器、回路、灯体へ電力が流れ、照明卓から制御する関係を示す" loading="lazy"><figcaption>調光器は電気側で灯体への出力を調整します。絞りとは役割が違います。</figcaption></figure>
</section>
<section class="hk-section">
  <div class="hk-section-head"><h2>6-3　フェーダーとチャンネルを分けて考える</h2><p>「回路番号」と「チャンネル番号」は、同じものを指しているとは限りません。</p></div>
  <p>操作卓では、複数の回路を一つのチャンネルにまとめて操作することがあります。逆に、一つの灯体を細かく分けて制御する場合もあります。</p>
  <p>つまり、<strong>「どこにつながっているか」と「どう操作するか」は別の情報</strong>です。パッチ（割り当て）の方法や呼び方は劇場によって異なるので、実際の現場ではその劇場の表を確認します。</p>
  <div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-thinking.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「じゃあ、回路番号とフェーダー番号が同じとは限らないんだね？」</p></div></div>
  <div class="hk-nyakakichi-followup"><p>そうです。この二つを分けて考えられると、照明図と操作卓の関係が分かりやすくなります。</p></div>
  <figure class="hk-illustration hk-lighting-chapter-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/' ) ); ?>lighting-23-channel-patch.png" alt="照明卓のフェーダー、チャンネル、パッチ、回路、灯体の関係を示す図" loading="lazy"><figcaption>フェーダーで操作するチャンネルと、実際の回路・灯体をパッチで対応づけます。</figcaption></figure>
  <figure class="hk-illustration hk-lighting-chapter-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/' ) ); ?>lighting-23-channel-patch-detail.png" alt="設備側の実際の回路と灯体、操作側のチャンネル、そしてパッチによる対応関係を詳しく示す図" loading="lazy"><figcaption>設備側にある実際の回路・灯体と、照明卓で操作するチャンネルは、パッチによって対応づけられます。</figcaption></figure>

</section>
<section class="hk-section">
  <div class="hk-section-head"><h2>6-4　LED灯体では何が変わる？</h2><p>LED灯体では、明るさだけでなく色なども電気的に制御できるものがあります。</p></div>
  <p>白熱灯では、ゼラを入れて色を作る方法が基本でした。一方、カラーLED灯体では、灯体内部の複数のLEDを組み合わせて色を作れる機種があります。</p>
  <p>そのため、LEDでは<strong>「電源」と「制御信号」</strong>を分けて考えることが重要です。</p>
  <div class="hk-panel"><h3>DMXという言葉</h3><p><strong>DMX</strong>は、舞台照明などで機器を制御するために広く使われている通信方式です。</p><p>操作卓からDMX信号を送り、対応する灯体や機器がその情報を受け取って、明るさ・色・動きなどを制御します。</p><p>DMXでは「アドレス」という考え方が出てきます。どの制御値をどの機器が受け取るかを区別するための番号です。</p></div>
  <figure class="hk-illustration hk-lighting-chapter-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/' ) ); ?>lighting-24-dmx-led.png" alt="LED灯体とDMX制御の関係を示す図。照明卓からDMX信号を送り、LED灯体の明るさや色などを制御する" loading="lazy"><figcaption>LED灯体では、DMXなどの制御信号によって明るさや色などを操作する機種があります。</figcaption></figure>
</section>
<section class="hk-section">
  <div class="hk-section-head"><h2>6-5　照明卓では何を操作している？</h2><p>照明卓は、舞台上の灯体を直接手で動かしているわけではありません。設定された制御情報を、必要な機器へ送っています。</p></div>
  <p>初心者のうちは、まず「フェーダーを上げると明るくなる」という体験から始めて構いません。</p>
  <p>その裏側には、<strong>回路・調光器・チャンネル・パッチ・DMXアドレス</strong>など、複数の仕組みがあります。</p>
  <figure class="hk-illustration hk-lighting-chapter-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/lighting/' ) ); ?>lighting-25-console-operation.png" alt="照明卓の基本操作の流れを示す図。チャンネル選択、フェーダー操作、色や動きの調整、シーンの記憶と再生を説明する" loading="lazy"><figcaption>照明卓では、チャンネルを選び、必要な値を調整し、その状態をシーンとして記憶・再生できます。</figcaption></figure>
  <div class="hk-steps"><span>① 灯体を仕込む</span><b>→</b><span>② 回路・電源を確認</span><b>→</b><span>③ 制御先を割り当てる</span><b>→</b><span>④ 操作卓から操作</span></div>
</section>
<section class="hk-section">
  <div class="hk-section-head"><h2>6-6　実際に確かめてみる</h2><p>電気と操作は、一つずつ動かしてみると理解しやすくなります。</p></div>
  <div class="hk-exercise"><h3>5分照明エチュード「一台だけ点けてみる」</h3><ol><li>仕込み図から、舞台中央を照らす灯体を一台選びます。</li><li>その灯体の番号、種類、回路番号を確認します。</li><li>操作卓側で、その灯体に対応するチャンネルを確認します。</li><li>一台だけを点灯し、平面図で見た照射方向と実際の舞台上の光を比べます。</li><li>次に別の灯体を一台だけ点け、二つを組み合わせたときの違いを観察します。</li></ol><p>実際の機材を扱うときは、劇場ごとの安全手順と担当者の指示に従います。</p></div>
</section>
<section class="hk-section"><div class="hk-panel hk-summary"><h3>第6章まとめ</h3><p>照明を動かすには、灯体だけでなく、電源・回路・調光・操作の仕組みを理解する必要があります。</p><p><strong>回路は設備側、チャンネルは操作側の単位</strong>として考えると整理しやすくなります。</p><p>LED灯体ではDMXなどの制御信号が登場し、明るさだけでなく色や動きまで操作できる機種があります。</p><p>次の章では、照明の「色」をさらに深く見ていきます。</p></div>
<div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-understood.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「灯体を仕込んで、電気をつないで、操作卓から動かす。だんだん仕組みが見えてきた！」</p></div></div></section>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション"><a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-5/' ) ); ?>">← 第5章</a><a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a><a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-7/' ) ); ?>">第7章 →</a></nav>

<?php elseif ( 'theatre-textbook/staff/lighting/chapter-7' === $path ) : ?>
<header class="hk-staff-hero"><p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p><h1>第7章　色を作る</h1><p>ゼラ、色温度、LEDの混色。照明の「色」を、感覚だけでなく仕組みから考えます。</p></header>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-6/' ) ); ?>">← 第6章</a>
  <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
  <span class="hk-chapter-nav-disabled">第8章 →</span>
</nav>
<section class="hk-section">
<div class="hk-section-head"><h2>導入　「赤い光」だけではない</h2><p>照明の色は、単純に「赤・青・黄色」を選ぶだけではありません。光源、フィルター、混色、周囲の色との関係によって、同じ舞台でも見え方が変わります。</p></div>
<p>たとえば、白い衣裳に青い光を当てれば青く見えます。しかし、赤いゼラを通した光と、RGBのLEDで作った赤い光は、同じ「赤」と呼んでも光の作り方が違います。</p>
<div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-thinking.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「じゃあ、色を選ぶだけじゃなくて、どうやってその色を作ったかも考えるの？」</p></div></div>
<div class="hk-nyakakichi-followup"><p>その通りです。第7章では、色を「材料」と「作り方」の両方から見ていきます。</p></div>
</section>
<section class="hk-section">
<div class="hk-section-head"><h2>7-1　ゼラで色を作る</h2><p>従来型の灯体では、光源の前にカラーフィルター（通称：ゼラ）を入れて、通す光の成分を変えます。</p></div>
<p>ゼラは光の一部を通し、一部を吸収します。そのため、白い光にゼラを重ねると、舞台に届く光の色が変わります。</p>
<p>第3章で登場したゼラホルダーは、このゼラを灯体の前に取り付けるためのものです。</p>
<p>実際の番号や色名を調べるときは、<a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/filters/' ) ); ?>">ゼラ・カラーフィルター一覧</a>も利用できます。</p>
<div class="hk-panel"><h3>ゼラを見るときの情報</h3><ul><li>メーカー</li><li>シリーズ</li><li>番号</li><li>色名</li><li>透過率（Transmission）</li><li>どの灯体・場面で使うか</li></ul></div>
</section>
<section class="hk-section">
<div class="hk-section-head"><h2>7-2　色温度を知る</h2><p>白い光にも「暖かい白」「冷たい白」があります。これを考えるときに出てくるのが色温度です。</p></div>
<p>色温度はK（ケルビン）で表します。数字が低いほど暖色側、高いほど寒色側へ傾く、という理解から始めると分かりやすいでしょう。</p>
<div class="hk-term-grid"><div><h3>低い色温度</h3><p>暖色寄り。ろうそくや白熱電球のような、赤み・黄みを感じる光。</p></div><div><h3>高い色温度</h3><p>寒色寄り。青みを感じる、比較的クールな白。</p></div></div>
<p>ただし、舞台照明では「色温度が高い＝必ず青い」「低い＝必ず赤い」と単純化しすぎないことも大切です。</p>
</section>
<section class="hk-section">
<div class="hk-section-head"><h2>7-3　LEDでは色をどう作る？</h2><p>カラーLED灯体では、複数の色のLEDを組み合わせて一つの色を作る機種があります。</p></div>
<p>代表的なのがRGBです。赤（Red）、緑（Green）、青（Blue）の光を組み合わせて、さまざまな色を作ります。</p>
<div class="hk-lighting-flow"><div><strong>R</strong><small>赤</small></div><b>＋</b><div><strong>G</strong><small>緑</small></div><b>＋</b><div><strong>B</strong><small>青</small></div><b>→</b><div><strong>混色</strong><small>作りたい色へ</small></div></div>
<p>最近のLED灯体では、RGBだけでなく、Amber、Lime、Whiteなど別の色のLEDを加えて、より細かく色を作れる機種もあります。</p>
</section>
<section class="hk-section">
<div class="hk-section-head"><h2>7-4　「同じ色」でも光の作り方が違う</h2><p>ゼラとLEDは、同じ色を目指していても仕組みが違います。</p></div>
<div class="hk-term-grid"><div><h3>ゼラ</h3><p>白色光から特定の成分を選び、通過する光を変える。</p><p><strong>灯体 → ゼラ → 舞台</strong></p></div><div><h3>カラーLED</h3><p>複数のLEDの出力を組み合わせて、灯体から出す光そのものを作る。</p><p><strong>LED素子 → 混色 → 舞台</strong></p></div></div>
<p>そのため、同じ色名でも、灯体や光源の違いによって衣裳や舞台美術の見え方が変わることがあります。</p>
</section>
<section class="hk-section">
<div class="hk-section-head"><h2>7-5　色は「単色」ではなく組み合わせで考える</h2><p>舞台では、一つの色だけで全部を照らすとは限りません。</p></div>
<p>たとえば、前明かりを少し暖色、サイドを寒色にすると、人物の立体感を保ちながら舞台全体の空気を作れます。</p>
<p>バックライトだけ色を変える、背景だけ別の色にする、場面転換で色を少しずつ変える、といった方法もあります。</p>
<div class="hk-panel"><h3>色を組み合わせるときの3つの視点</h3><ol><li><strong>何を見せたいか</strong> — 顔、衣裳、背景、空間など。</li><li><strong>どんな空気にしたいか</strong> — 暖かい、冷たい、静か、緊張感がある、など。</li><li><strong>どの光同士を混ぜるか</strong> — 前・サイド・バック・背景の役割を考える。</li></ol></div>
</section>
<section class="hk-section">
<div class="hk-section-head"><h2>7-6　実際に色を比べてみる</h2><p>色は実際に当てて比べると、一気に理解しやすくなります。</p></div>
<div class="hk-exercise"><h3>5分照明エチュード「同じ場所を3色で見る」</h3><ol><li>同じ灯体、同じ位置、同じ明るさで、色だけを変えます。</li><li>暖色系、寒色系、彩度の高い色など、3種類を順番に当てます。</li><li>顔、衣裳、舞台美術、背景の見え方がどう変わったかを書き出します。</li><li>次に、前明かりとサイドライトで色を変え、人物の立体感の変化を比べます。</li></ol><p>色の見え方は、劇場の設備、灯体、舞台美術、衣裳、客席環境などによって変わります。実際の現場では必ず実機で確認します。</p></div>
</section>
<section class="hk-section"><div class="hk-panel hk-summary"><h3>第7章まとめ</h3><p>照明の色は、ゼラやLEDなど「色を作る方法」から考えると理解しやすくなります。</p><p><strong>ゼラは光を通す・吸収することで色を変え、LEDは複数の光を組み合わせて色を作る</strong>、という違いがあります。</p><p>色温度は白色光の暖かさ・冷たさを考えるための基本的な指標です。</p><p>そして舞台では、一つの色を選ぶだけでなく、前・サイド・バック・背景など複数の光を組み合わせて色の設計を行います。</p><p>次の章では、仕込んだ灯体を実際にどこへ向けるか、「フォーカスと明かり合わせ」を学びます。</p></div>
<div class="hk-nyakakichi"><div class="hk-nyakakichi-image"><img src="<?php echo esc_url( home_url( '/wp-content/plugins/hatakiti-core/assets/images/nyakakichi/' ) ); ?>nyakakichi-understood.png" alt="にゃかきち" loading="lazy"></div><div class="hk-nyakakichi-question"><p>「色も、選ぶだけじゃなくて、どう作るかと組み合わせ方が大事なんだね！」</p></div></div></section>
<nav class="hk-chapter-nav" aria-label="照明の章ナビゲーション">
<a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-6/' ) ); ?>">← 第6章</a>
<a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">目次</a>
<a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/chapter-8/' ) ); ?>">第8章 →</a>
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

.hk-nyakakichi{display:flex;align-items:center;gap:18px;margin:28px 0 10px;padding:0;background:transparent;border:0;color:var(--hk-fg);position:relative;overflow:visible}.hk-nyakakichi-image{flex:0 0 110px;text-align:center}.hk-nyakakichi-image img{display:block;width:110px;height:auto;max-height:170px;object-fit:contain;margin:0 auto}.hk-nyakakichi-question{flex:1;position:relative;background:#454545;border-radius:16px;padding:16px 20px;color:#fff;line-height:1.8}.hk-nyakakichi-question:before{content:"";position:absolute;left:-12px;top:24px;border-top:10px solid transparent;border-bottom:10px solid transparent;border-right:14px solid #454545}.hk-nyakakichi-question p{margin:0;color:#fff}.hk-nyakakichi-question strong{color:#fff}.hk-nyakakichi-followup{margin:0 0 24px 128px;line-height:1.9;color:var(--hk-fg)}.hk-nyakakichi-followup p{margin:0 0 8px}.hk-nyakakichi-followup p:last-child{margin-bottom:0}@media(max-width:600px){.hk-nyakakichi{align-items:center;gap:12px;margin-top:24px}.hk-nyakakichi-image{flex-basis:90px}.hk-nyakakichi-image img{width:90px;max-height:145px}.hk-nyakakichi-question{padding:13px 15px}.hk-nyakakichi-question:before{left:-10px;top:20px;border-top-width:8px;border-bottom-width:8px;border-right-width:11px}.hk-nyakakichi-followup{margin-left:102px;margin-bottom:20px}}
.hk-chapter-nav{display:flex;justify-content:space-between;align-items:center;gap:12px;margin:28px 0;padding:14px 0;border-top:1px solid var(--hk-border);border-bottom:1px solid var(--hk-border)}
.hk-lighting-plot{margin:24px 0 8px;padding:18px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}
.hk-lighting-plot-audience{padding:10px;text-align:center;background:var(--hk-bg-card);color:var(--hk-fg-dim);font-size:12px}
.hk-lighting-plot-stage{position:relative;height:250px;margin:14px auto;max-width:620px;border:2px solid var(--hk-fg-dim);background:var(--hk-bg-card);overflow:hidden}
.hk-lighting-plot-beam{position:absolute;bottom:18px;width:150px;height:210px;background:var(--hk-accent-warm);opacity:.16;transform-origin:bottom center;clip-path:polygon(42% 0,58% 0,100% 100%,0 100%)}
.hk-plot-beam-left{left:8%;transform:rotate(16deg)}
.hk-plot-beam-center{left:38%;transform:rotate(0)}
.hk-plot-beam-right{right:8%;transform:rotate(-16deg)}
.hk-lighting-plot-person{position:absolute;left:50%;bottom:22px;transform:translateX(-50%);padding:8px 12px;border:1px solid var(--hk-accent-warm);background:var(--hk-bg-elevated);font-size:12px}
.hk-lighting-plot-fixtures{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;color:var(--hk-fg-dim);font-size:11px}
.hk-lighting-plot-fixtures span{padding:5px 9px;border:1px solid var(--hk-border)}
.hk-lighting-symbols{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:24px 0}
.hk-lighting-symbols>div{display:grid;grid-template-columns:62px 1fr;grid-template-rows:auto auto;column-gap:12px;align-items:center;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:16px}
.hk-lighting-symbol{grid-row:1 / span 2;display:flex;align-items:center;justify-content:center;height:54px;border:1px solid var(--hk-border);font-size:25px;color:var(--hk-accent-warm)}
.hk-lighting-symbols strong{font-family:var(--hk-font-serif)}
.hk-lighting-symbols small{color:var(--hk-fg-dim);line-height:1.5}
.hk-lighting-elevation{margin:24px 0 8px;padding:18px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}
.hk-elevation-stage{position:relative;height:260px;max-width:720px;margin:0 auto;border-bottom:3px solid var(--hk-fg-dim)}
.hk-elevation-floor{position:absolute;left:0;bottom:-1px;width:100%;padding-top:8px;text-align:center;font-size:11px;color:var(--hk-fg-dim)}
.hk-elevation-person{position:absolute;left:48%;bottom:25px;padding:18px 10px 8px;border:1px solid var(--hk-accent-warm);background:var(--hk-bg-card);font-size:11px}
.hk-elevation-light{position:absolute;padding:8px 10px;border:1px solid var(--hk-border);background:var(--hk-bg-card);font-size:11px;color:var(--hk-fg)}
.hk-elevation-front{left:12%;bottom:95px}
.hk-elevation-top{left:44%;top:15px}
.hk-elevation-back{right:10%;bottom:125px}
.hk-lighting-flow{display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;margin:24px 0}
.hk-lighting-flow>div{min-width:125px;padding:16px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);text-align:center}
.hk-lighting-flow strong{display:block;font-family:var(--hk-font-serif)}
.hk-lighting-flow small{display:block;margin-top:6px;color:var(--hk-fg-dim);line-height:1.5}
.hk-lighting-flow>b{color:var(--hk-accent-warm)}
@media(max-width:700px){.hk-lighting-symbols{grid-template-columns:1fr}.hk-lighting-plot-stage{height:210px}.hk-elevation-stage{height:220px}.hk-lighting-flow{justify-content:flex-start}.hk-lighting-flow>b{display:none}}

.hk-lighting-chapter-image{margin:24px 0}.hk-lighting-chapter-image img{display:block;width:100%;height:auto;border:1px solid var(--hk-border)}.hk-lighting-chapter-image figcaption{margin-top:8px;color:var(--hk-fg-faint);font-size:12px;line-height:1.7}.hk-lighting-chapter-image + .hk-control-diagram,.hk-lighting-chapter-image + .hk-dimmer-diagram,.hk-lighting-chapter-image + .hk-patch-diagram,.hk-lighting-chapter-image + .hk-dmx-diagram{margin-top:14px}.hk-signal-box{display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;margin:16px 0;padding:16px;background:var(--hk-bg-card);border:1px solid var(--hk-border)}.hk-signal-box span{padding:10px 12px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);font-weight:700}.hk-signal-box b{color:var(--hk-accent-warm)}.hk-figure-caption{margin:8px 0 24px;color:var(--hk-fg-faint);font-size:12px;line-height:1.7}.hk-dimmer-diagram,.hk-dmx-diagram,.hk-console-diagram{display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;margin:22px 0 4px;padding:20px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}.hk-dimmer-diagram>div,.hk-dmx-diagram>div,.hk-console-diagram>div{min-width:130px;padding:15px;text-align:center;border:1px solid var(--hk-border);background:var(--hk-bg-card)}.hk-dimmer-diagram span,.hk-dmx-diagram small,.hk-console-diagram span{display:block;font-size:11px;color:var(--hk-accent-warm);margin-bottom:6px}.hk-dimmer-diagram strong,.hk-dmx-diagram strong,.hk-console-diagram strong{font-family:var(--hk-font-serif)}.hk-dimmer-diagram>b,.hk-dmx-diagram>b,.hk-console-diagram>b{color:var(--hk-accent-warm);font-size:20px}.hk-patch-diagram{display:flex;align-items:center;justify-content:center;gap:20px;margin:22px 0 4px;padding:20px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}.hk-patch-column{display:flex;flex-direction:column;gap:8px;min-width:180px}.hk-patch-column h3{margin:0 0 4px;font-family:var(--hk-font-serif);color:var(--hk-accent-warm)}.hk-patch-column span{padding:10px 14px;border:1px solid var(--hk-border);background:var(--hk-bg-card)}.hk-patch-channel span{text-align:center}.hk-patch-column small{color:var(--hk-fg-dim)}.hk-patch-arrow{font-size:24px;color:var(--hk-accent-warm);line-height:1.1;text-align:center}@media(max-width:700px){.hk-control-diagram{grid-template-columns:1fr}.hk-dimmer-diagram,.hk-dmx-diagram,.hk-console-diagram{justify-content:flex-start}.hk-dimmer-diagram>b,.hk-dmx-diagram>b,.hk-console-diagram>b{display:none}.hk-patch-diagram{gap:8px}.hk-patch-column{min-width:130px}.hk-patch-arrow{font-size:18px}}
.hk-chapter-nav a,.hk-chapter-nav-disabled{padding:10px 14px;color:var(--hk-accent-warm);text-decoration:none}
.hk-chapter-nav a:hover{text-decoration:underline}
.hk-chapter-nav-disabled{color:var(--hk-fg-faint)}
.hk-chapter-list{display:grid;grid-template-columns:1fr;gap:14px}
.hk-chapter-list a{display:grid;grid-template-columns:90px 1fr;column-gap:18px;row-gap:5px;padding:22px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);color:var(--hk-fg);text-decoration:none}
.hk-chapter-list a:hover{border-color:var(--hk-accent-warm);text-decoration:none}
.hk-chapter-list span{grid-row:1 / span 2;color:var(--hk-accent-warm);font-family:var(--hk-font-serif);font-size:18px}
.hk-chapter-list strong{font-family:var(--hk-font-serif);font-size:19px}
.hk-chapter-list small{color:var(--hk-fg-dim);line-height:1.7}
@media(max-width:600px){.hk-chapter-nav{gap:4px}.hk-chapter-nav a,.hk-chapter-nav-disabled{padding:8px 6px;font-size:12px}.hk-chapter-list a{grid-template-columns:1fr}.hk-chapter-list span{grid-row:auto}.hk-chapter-list strong{font-size:17px}}

</style>
<?php get_footer(); ?>