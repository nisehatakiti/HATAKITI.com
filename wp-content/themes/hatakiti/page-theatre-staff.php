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
.hk-four{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.hk-four b{font-family:var(--hk-font-serif);font-size:18px}.hk-equipment{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.hk-equipment h3,.hk-term-grid h3{font-family:var(--hk-font-serif);margin-top:0}.hk-warning{margin:20px 0;padding:18px;border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card);line-height:1.8}.hk-filter-table{width:100%;border-collapse:collapse}.hk-filter-table th,.hk-filter-table td{border:1px solid var(--hk-border);padding:12px;text-align:left}.hk-filter-table th{color:var(--hk-accent-warm)}.hk-steps{display:flex;justify-content:center;align-items:center;gap:10px;flex-wrap:wrap;border:1px solid var(--hk-border);padding:24px;background:var(--hk-bg-elevated)}.hk-steps b{color:var(--hk-accent-warm)}.hk-term-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.hk-exercise{border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card);padding:24px;line-height:1.9}.hk-exercise li{margin:8px 0}
@media(max-width:850px){.hk-four{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.hk-staff-hero h1{font-size:30px}.hk-staff-grid,.hk-equipment,.hk-term-grid,.hk-four{grid-template-columns:1fr}.hk-steps{justify-content:flex-start}}
.hk-gel-detail{border-top:1px solid var(--hk-border)}.hk-gel-detail-top{display:grid;grid-template-columns:minmax(260px,420px) 1fr;gap:28px;align-items:center}.hk-gel-detail-swatch{min-height:300px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.55)}.hk-gel-detail-swatch span{font-size:12px}.hk-gel-detail-swatch strong{font-size:28px;margin-top:8px}.hk-gel-detail-swatch.is-missing{background:repeating-linear-gradient(135deg,var(--hk-bg-card),var(--hk-bg-card) 12px,var(--hk-bg-elevated) 12px,var(--hk-bg-elevated) 24px);color:var(--hk-fg-dim);text-shadow:none;text-align:center}.hk-gel-detail-top h2{font-family:var(--hk-font-serif);font-size:30px}.hk-gel-detail-brand{color:var(--hk-accent-warm)}.hk-gel-detail-warning{border-left:3px solid var(--hk-accent-warm);padding:12px 15px;background:var(--hk-bg-card);color:var(--hk-fg-dim);line-height:1.8}.hk-gel-detail-values{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:20px}.hk-gel-detail-values>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:18px}.hk-gel-detail-values span{display:block;font-size:10px;color:var(--hk-accent-warm)}.hk-gel-detail-values strong{display:block;margin-top:6px}.hk-gel-detail-columns{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:12px}.hk-gel-detail-columns>div{border:1px solid var(--hk-border);padding:18px;background:var(--hk-bg-elevated)}.hk-gel-detail-columns h3{font-family:var(--hk-font-serif);margin-top:0}.hk-gel-detail-columns p{color:var(--hk-fg-dim);line-height:1.8;font-size:13px}.hk-gel-back{display:inline-block;margin-top:20px;color:var(--hk-accent-warm)}@media(max-width:700px){.hk-gel-detail-top,.hk-gel-detail-values,.hk-gel-detail-columns{grid-template-columns:1fr}.hk-gel-detail-swatch{min-height:220px}}.hk-gel-notice{margin-top:0}.hk-gel-notice-inner{border:1px solid var(--hk-border);border-left:4px solid var(--hk-accent-warm);background:var(--hk-bg-card);padding:22px}.hk-gel-notice-inner p{color:var(--hk-fg-dim);line-height:1.9;margin:.7em 0}.hk-gel-caution{font-weight:700;color:var(--hk-fg)!important}.hk-gel-search{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.hk-gel-search label{font-size:12px;color:var(--hk-fg-dim);position:relative}.hk-gel-search input,.hk-gel-search select{display:block;width:100%;box-sizing:border-box;margin-top:7px;padding:11px;border:1px solid var(--hk-border);background:var(--hk-bg-card);color:var(--hk-fg)}.hk-gel-unit{float:right;font-size:11px}.hk-gel-sort-wrap,.hk-gel-actions{display:flex;align-items:end}.hk-gel-actions button{width:100%;padding:11px;border:1px solid var(--hk-border);background:transparent;color:var(--hk-fg);cursor:pointer}.hk-gel-actions button:hover{border-color:var(--hk-accent-warm);color:var(--hk-accent-warm)}.hk-gel-usage{display:flex;gap:9px;flex-wrap:wrap;margin-top:16px;padding:15px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}.hk-gel-usage>span{width:100%;font-size:12px;color:var(--hk-accent-warm);margin-bottom:2px}.hk-gel-usage label{font-size:12px}.hk-gel-usage input{margin-right:4px}.hk-gel-result-head{display:flex;justify-content:space-between;align-items:end}.hk-gel-result-head #hk-gel-count{color:var(--hk-fg-dim);font-size:13px}.hk-gel-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.hk-gel-card{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);overflow:hidden}.hk-gel-card:hover{border-color:var(--hk-accent-warm)}.hk-gel-card-link{display:block;color:inherit;text-decoration:none}.hk-gel-swatch{height:180px;background:var(--hk-gel-color);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;text-shadow:0 1px 2px rgba(0,0,0,.5);color:#fff}.hk-gel-swatch span{font-size:11px;letter-spacing:.08em}.hk-gel-swatch strong{font-size:20px}.hk-gel-swatch small{font-size:11px}.hk-gel-swatch-missing{background:repeating-linear-gradient(135deg,var(--hk-bg-card),var(--hk-bg-card) 10px,var(--hk-bg-elevated) 10px,var(--hk-bg-elevated) 20px);text-align:center;text-shadow:none;color:var(--hk-fg-dim)}.hk-gel-meta{padding:18px}.hk-gel-brand{font-size:11px;color:var(--hk-accent-warm)}.hk-gel-meta h3{margin:5px 0 14px;font-family:var(--hk-font-serif)}.hk-gel-meta dl{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:0}.hk-gel-meta dl div{border-top:1px solid var(--hk-border);padding-top:8px}.hk-gel-meta dt{font-size:10px;color:var(--hk-fg-dim)}.hk-gel-meta dd{margin:3px 0 0;font-size:13px}.hk-gel-tags{display:flex;gap:5px;flex-wrap:wrap;margin-top:13px}.hk-gel-tags span{font-size:10px;border:1px solid var(--hk-border);padding:4px 7px;color:var(--hk-fg-dim)}.hk-gel-card-note{font-size:10px;color:var(--hk-fg-faint);margin-bottom:0}.hk-gel-empty{border:1px dashed var(--hk-border);padding:35px;text-align:center;color:var(--hk-fg-dim)}.hk-gel-detail-rule{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.hk-gel-detail-rule>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:18px}.hk-gel-detail-rule b{color:var(--hk-accent-warm)}.hk-gel-detail-rule p{color:var(--hk-fg-dim);line-height:1.8;font-size:13px}.hk-filter-note strong{color:var(--hk-fg)}
@media(max-width:900px){.hk-gel-search{grid-template-columns:repeat(2,1fr)}.hk-gel-grid{grid-template-columns:repeat(2,1fr)}.hk-gel-detail-rule{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.hk-gel-search,.hk-gel-grid,.hk-gel-detail-rule{grid-template-columns:1fr}}
.hk-filter-search{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.hk-filter-search label{font-size:12px;color:var(--hk-fg-dim)}.hk-filter-search input{display:block;width:100%;box-sizing:border-box;margin-top:7px;padding:11px;border:1px solid var(--hk-border);background:var(--hk-bg-card);color:var(--hk-fg)}.hk-filter-note{font-size:13px;color:var(--hk-fg-dim);line-height:1.8}.hk-filter-record{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.hk-filter-record>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:18px}.hk-filter-record span{display:block;font-size:11px;color:var(--hk-accent-warm);margin-bottom:7px}.hk-filter-record strong{display:block}.hk-filter-record small{display:block;color:var(--hk-fg-faint);margin-top:8px}.hk-filter-table-wrap{overflow-x:auto}.hk-filter-database{width:100%;min-width:1050px;border-collapse:collapse}.hk-filter-database th,.hk-filter-database td{border:1px solid var(--hk-border);padding:11px;text-align:left;vertical-align:top}.hk-filter-database th{color:var(--hk-accent-warm);font-size:12px}.hk-filter-database td{font-size:13px}.hk-swatch-placeholder{width:58px;height:42px;display:flex;align-items:center;justify-content:center;border:1px dashed var(--hk-border);font-size:10px;color:var(--hk-fg-faint)}
@media(max-width:700px){.hk-filter-search,.hk-filter-record{grid-template-columns:1fr}}
</style>
<?php get_footer(); ?>