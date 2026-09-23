<?php
/**
 * Virtual child pages for HATAKITI 演劇の教科書.
 * Four methods share one beginner-friendly layout.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
$base = trim( parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
if ( $base && 0 === strpos( $path, $base . '/' ) ) {
    $path = substr( $path, strlen( $base ) + 1 );
}

$methods = array(
    'theatre-textbook/stanislavski' => array(
        'number' => '01',
        'eyebrow' => 'STANISLAVSKI',
        'title' => 'スタニスラフスキー・システム',
        'lead' => '役の置かれている状況を理解し、その人物が何を求め、目的のために何をするのかを具体的にしていく演技の考え方。',
        'character_open' => 'これって、悲しい役なら本当に悲しくならなきゃダメなのかニャ？',
        'points' => array(
            array( 'title' => '状況', 'text' => 'いつ、どこで、誰と、何が起きているのかを具体的にする。' ),
            array( 'title' => '目的', 'text' => 'この場面で、この人物は何を手に入れたいのかを考える。' ),
            array( 'title' => '行動', 'text' => '目的のために相手へ何をするのかを選ぶ。感情を直接作ろうとしすぎない。' ),
        ),
        'misconception' => '「スタニスラフスキー＝本物の感情を出す方法」とだけ考えると、かえって狭くなります。状況、目的、行動などを具体化し、人物の行動を組み立てることが大切です。',
        'etude_title' => '財布を返して！',
        'etude_meta' => '2人 ／ 5分 ／ 小道具なしでも可',
        'etude_steps' => array(
            'AはBに財布を返してほしい。',
            'Aは「怒らせずに返してもらう」という目的を持つ。',
            'Bは簡単には返さない。',
            '「悲しい」「怒っている」などの感情を先に決めず、目的のための行動を試す。',
        ),
        'common' => 'AはBに帰ってほしくない。Aの目的を一つ決め、その目的のためにBへ何をするのかを具体的にして演じる。',
        'final' => '「感情を作る」前に、その人が何をしたいのか考えるんだニャ。',
    ),
    'theatre-textbook/method' => array(
        'number' => '02',
        'eyebrow' => 'METHOD ACTING',
        'title' => 'メソッド演技',
        'lead' => '役の内面を、役者自身の感覚・想像力・経験などを使って具体的にしていく演技訓練。アメリカで発展した複数の実践を含む呼び方です。',
        'character_open' => 'メソッドって、自分の一番つらかった思い出を思い出せばいいのかニャ？',
        'points' => array(
            array( 'title' => '感覚', 'text' => '感覚や記憶を手がかりに、人物の内側を具体的にする練習がある。' ),
            array( 'title' => '想像力', 'text' => '自分の経験そのものだけでなく、想像によって状況や人物を豊かにする。' ),
            array( 'title' => '練習', 'text' => 'リラクゼーション、感覚・感情に関する訓練、脚本分析、即興など、教師や系統によって内容は異なる。' ),
        ),
        'misconception' => '「メソッド＝トラウマを思い出すこと」ではありません。メソッド演技は一つの固定された方法ではなく、スタニスラフスキーの仕事を背景に、リー・ストラスバーグらによって発展したアメリカの演技訓練の流れを含む言葉です。',
        'etude_title' => '手紙を開ける',
        'etude_meta' => '1人 ／ 5分 ／ 封筒を1枚',
        'etude_steps' => array(
            '封筒を手にする。ただし、すぐには開けない。',
            '誰から届いたのか、何が書かれているのかを想像する。',
            '紙の手触り、重さ、匂い、部屋の空気など、感覚を具体的にする。',
            '感情を無理に作らず、想像した状況の中で封筒を開けてみる。',
        ),
        'common' => 'AはBに帰ってほしくない。AにとってBがどんな存在なのかを、感覚や想像力を使って具体的にしてから演じる。',
        'final' => '自分の経験だけじゃなくて、想像することも大事なんだニャ。',
    ),
    'theatre-textbook/meisner' => array(
        'number' => '03',
        'eyebrow' => 'MEISNER',
        'title' => 'マイズナー・テクニック',
        'lead' => '自分がどう見えるかより、相手を本当に受け取り、相手から影響を受けて、その瞬間に反応することを重視する訓練の考え方。',
        'character_open' => 'リピートって、同じ言葉をずっと繰り返すだけなのかニャ？',
        'points' => array(
            array( 'title' => '相手を見る', 'text' => '自分の頭の中だけで演技を組み立てず、目の前の相手に注意を向ける。' ),
            array( 'title' => '受け取る', 'text' => '相手の言葉や態度に実際に影響される。次の台詞を先回りして準備しすぎない。' ),
            array( 'title' => '反応する', 'text' => '決めておいた感情を再現するより、その瞬間に起きたことへの反応を大切にする。' ),
        ),
        'misconception' => 'リピティションは目的そのものではありません。相手の行動に注意を向け、自分が本当に影響を受けて反応するための代表的な訓練です。',
        'etude_title' => 'リピティション',
        'etude_meta' => '2人 ／ 5分 ／ 向かい合って行う',
        'etude_steps' => array(
            'AがBの目の前にある、実際に感じた特徴を短く言う。',
            'Bはその言葉を繰り返す。',
            '二人は相手の変化を受け取りながら繰り返す。',
            '「面白いことを言おう」とせず、相手の変化が自分にどう影響したかをそのまま返す。',
        ),
        'common' => 'AはBに帰ってほしくない。Bの一言や表情をよく受け取り、それによってAの反応がどう変わるかを追いながら演じる。',
        'final' => '相手をちゃんと見てたら、次の台詞を考える暇がなくなったニャ。',
    ),
    'theatre-textbook/lecoq' => array(
        'number' => '04',
        'eyebrow' => 'LECOQ',
        'title' => 'ルコック・システム',
        'lead' => '身体と空間、動き、即興から演劇をつくっていく教育・創作の考え方。身体表現だけでなく、動きの分析やマスクなど幅広い実践を含みます。',
        'character_open' => '身体を使うって、つまりパントマイムをすることなのかニャ？',
        'points' => array(
            array( 'title' => '身体', 'text' => '感情を顔だけで表すのではなく、重心、呼吸、姿勢、歩き方など身体全体を探る。' ),
            array( 'title' => '空間', 'text' => '距離、方向、広さ、時間などとの関係から、動きや場面を考える。' ),
            array( 'title' => '遊びと即興', 'text' => '決まった答えを再現するだけでなく、動きや制約を試しながら演劇の素材を発見する。' ),
        ),
        'misconception' => 'ルコックは単なるパントマイムの技法ではありません。身体、動き、空間、即興、マスクなどを通して演劇を創作する幅広い教育・創作の体系です。',
        'etude_title' => '重い箱',
        'etude_meta' => '1人 ／ 5分 ／ 想像上の箱',
        'etude_steps' => array(
            '何もない場所に箱があると想像する。',
            'とても軽い箱として持ち上げる。',
            '10kg、50kgの箱へと重さを変えていく。',
            '重さによって、足、膝、背中、呼吸、速度がどう変わるか観察する。',
        ),
        'common' => 'AはBに帰ってほしくない。台詞だけでなく、二人の距離、Aの身体の向き、近づく・離れる動きを使って場面をつくる。',
        'final' => '「帰ってほしくない」って、身体で表すとこんなに変わるんだニャ。',
    ),
);

if ( ! isset( $methods[ $path ] ) ) {
    wp_safe_redirect( home_url( '/theatre-textbook/' ) );
    exit;
}

$m = $methods[ $path ];

// にゃかきちは HATAKITI Core の共通アセットとして管理する。
$nyakakichi_base = content_url( 'plugins/hatakiti-core/assets/images/nyakakichi' );
$method_images = array(
    'theatre-textbook/stanislavski' => array(
        'hero' => 'nyakakichi-stanislavski.png',
        'question' => 'nyakakichi-question.png',
        'practice' => 'nyakakichi-try.png',
    ),
    'theatre-textbook/method' => array(
        'hero' => 'nyakakichi-method.png',
        'question' => 'nyakakichi-question.png',
        'practice' => 'nyakakichi-try.png',
    ),
    'theatre-textbook/meisner' => array(
        'hero' => 'nyakakichi-meisner.png',
        'question' => 'nyakakichi-question.png',
        'practice' => 'nyakakichi-meisner-pair.png',
    ),
    'theatre-textbook/lecoq' => array(
        'hero' => 'nyakakichi-lecoq-heavy.png',
        'question' => 'nyakakichi-question.png',
        'practice' => 'nyakakichi-lecoq-light.png',
    ),
);
$images = $method_images[ $path ];
get_header();
?>

<main id="main" class="hk-container hk-method-page">
    <header class="hk-method-hero">
        <a class="hk-method-back" href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">← 演劇の教科書に戻る</a>
        <p class="hk-textbook-kicker"><?php echo esc_html( $m['eyebrow'] ); ?></p>
        <div class="hk-method-title-row">
            <div>
                <span class="hk-method-number"><?php echo esc_html( $m['number'] ); ?></span>
                <h1><?php echo esc_html( $m['title'] ); ?></h1>
            </div>
            <div class="hk-nyakakichi-image hero" aria-label="にゃかきち">
                <img src="<?php echo esc_url( $nyakakichi_base . '/' . $images['hero'] ); ?>" alt="にゃかきち" loading="eager">
            </div>
        </div>
        <div class="hk-nyakakichi-bubble hero-bubble">
            <?php echo esc_html( $m['character_open'] ); ?>
        </div>
    </header>

    <section class="hk-method-section">
        <div class="hk-method-section-head">
            <span>01</span><h2>まず、これだけ覚えよう</h2>
        </div>
        <div class="hk-method-lead"><?php echo esc_html( $m['lead'] ); ?></div>
    </section>

    <section class="hk-method-section">
        <div class="hk-method-section-head">
            <span>02</span><h2>この演技法で大切なこと</h2>
        </div>
        <div class="hk-method-points">
            <?php foreach ( $m['points'] as $i => $point ) : ?>
                <article class="hk-method-point">
                    <span class="hk-method-point-no"><?php echo esc_html( 'POINT ' . ( $i + 1 ) ); ?></span>
                    <h3><?php echo esc_html( $point['title'] ); ?></h3>
                    <p><?php echo esc_html( $point['text'] ); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="hk-method-section hk-method-misconception">
        <div class="hk-method-section-head">
            <span>03</span><h2>🐱 にゃかきちの「ちょっと待って！」</h2>
        </div>
        <div class="hk-method-question">
            <div class="hk-nyakakichi-image small" aria-label="にゃかきち">
                <img src="<?php echo esc_url( $nyakakichi_base . '/' . $images['question'] ); ?>" alt="にゃかきち" loading="lazy">
            </div>
            <div class="hk-nyakakichi-bubble question-bubble"><?php echo esc_html( $m['character_open'] ); ?></div>
        </div>
        <p class="hk-method-explanation"><?php echo esc_html( $m['misconception'] ); ?></p>
    </section>

    <section class="hk-method-section hk-method-practice">
        <div class="hk-method-section-head">
            <span>04</span><h2>🎭 にゃかきちと5分エチュード</h2>
        </div>
        <article class="hk-practice-card">
            <div class="hk-practice-meta"><?php echo esc_html( $m['etude_meta'] ); ?></div>
            <h3><?php echo esc_html( $m['etude_title'] ); ?></h3>
            <ol>
                <?php foreach ( $m['etude_steps'] as $step ) : ?>
                    <li><?php echo esc_html( $step ); ?></li>
                <?php endforeach; ?>
            </ol>
            <div class="hk-practice-character">
                <div class="hk-nyakakichi-image small" aria-label="にゃかきち">
                    <img src="<?php echo esc_url( $nyakakichi_base . '/' . $images['practice'] ); ?>" alt="にゃかきち" loading="lazy">
                </div>
                <strong>「やってみるニャ！」</strong>
            </div>
        </article>
    </section>

    <section class="hk-method-section hk-common-scene">
        <div class="hk-method-section-head">
            <span>05</span><h2>同じ場面で試してみよう</h2>
        </div>
        <div class="hk-common-scene-card">
            <p class="hk-common-scene-title">AはBに帰ってほしくない。</p>
            <p><?php echo esc_html( $m['common'] ); ?></p>
        </div>
    </section>

    <section class="hk-method-ending">
        <div class="hk-nyakakichi-image ending" aria-label="にゃかきち">
            <img src="<?php echo esc_url( $nyakakichi_base . '/nyakakichi-satisfied.png' ); ?>" alt="にゃかきち" loading="lazy">
        </div>
        <div>
            <p class="hk-textbook-kicker">にゃかきちのひとこと</p>
            <p class="hk-ending-quote">「<?php echo esc_html( $m['final'] ); ?>」</p>
        </div>
    </section>

    <nav class="hk-method-nav" aria-label="演技法ページ">
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/stanislavski/' ) ); ?>">スタニスラフスキー</a>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/method/' ) ); ?>">メソッド演技</a>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/meisner/' ) ); ?>">マイズナー</a>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/lecoq/' ) ); ?>">ルコック</a>
    </nav>
</main>

<style>
.hk-method-hero{max-width:900px;margin:42px auto 56px;padding:0 20px}
.hk-method-back{display:inline-block;margin-bottom:30px;color:var(--hk-fg-dim);font-size:13px}
.hk-method-back:hover{color:var(--hk-accent-warm)}
.hk-method-title-row{display:flex;align-items:center;justify-content:space-between;gap:40px}
.hk-method-number{color:var(--hk-accent-warm);font-size:13px;letter-spacing:.15em}
.hk-method-title-row h1{font-family:var(--hk-font-serif);font-size:40px;margin:8px 0 0}
.hk-nyakakichi-image{width:180px;height:180px;display:flex;align-items:center;justify-content:center;flex:none}
.hk-nyakakichi-image img{display:block;width:100%;height:100%;object-fit:contain}
.hk-nyakakichi-image.hero{width:220px;height:220px}
.hk-nyakakichi-image.small{width:100px;height:100px}
.hk-nyakakichi-image.ending{width:170px;height:170px}
.hk-nyakakichi-bubble{position:relative;margin:28px 0 0;padding:20px 24px;background:var(--hk-bg-card);border:1px solid var(--hk-border);border-radius:18px;color:var(--hk-fg);font-size:18px;line-height:1.8}
.hk-nyakakichi-bubble:before{content:"";position:absolute;width:18px;height:18px;background:var(--hk-bg-card)}
.hk-nyakakichi-bubble.hero-bubble:before{right:58px;top:-10px;border-left:1px solid var(--hk-border);border-top:1px solid var(--hk-border);transform:rotate(45deg)}
.hk-nyakakichi-bubble.question-bubble:before{left:-10px;top:50%;margin-top:-9px;border-left:1px solid var(--hk-border);border-bottom:1px solid var(--hk-border);transform:rotate(45deg)}
.hk-method-section{max-width:900px;margin:0 auto 52px;padding:0 20px}
.hk-method-section-head{display:flex;align-items:center;gap:14px;margin-bottom:18px}
.hk-method-section-head>span{color:var(--hk-accent-warm);font-size:12px;letter-spacing:.12em}
.hk-method-section-head h2{margin:0;font-size:22px;font-family:var(--hk-font-serif)}
.hk-method-lead{padding:26px;border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-elevated);font-size:17px;line-height:2}
.hk-method-points{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.hk-method-point{padding:24px;background:var(--hk-bg-elevated);border:1px solid var(--hk-border)}
.hk-method-point-no{font-size:10px;color:var(--hk-accent-cool);letter-spacing:.1em}
.hk-method-point h3{margin:12px 0 8px;font-family:var(--hk-font-serif);font-size:20px}
.hk-method-point p,.hk-method-explanation,.hk-common-scene-card p{margin:0;color:var(--hk-fg-dim);line-height:1.9;font-size:14px}
.hk-method-misconception{padding-top:4px}
.hk-method-question{display:flex;align-items:center;gap:18px;margin-bottom:18px}
.hk-method-question .hk-nyakakichi-bubble{margin:0;flex:1}
.hk-method-explanation{padding:22px;background:var(--hk-bg-card);border:1px solid var(--hk-border)}
.hk-practice-card{padding:30px;background:var(--hk-bg-elevated);border:1px solid var(--hk-border)}
.hk-practice-meta{font-size:11px;color:var(--hk-accent-cool);letter-spacing:.08em}
.hk-practice-card h3{font-family:var(--hk-font-serif);font-size:27px;margin:8px 0 20px}
.hk-practice-card ol{margin:0;padding-left:22px;color:var(--hk-fg);line-height:2}
.hk-practice-character{display:flex;align-items:center;gap:18px;margin-top:24px;padding-top:20px;border-top:1px solid var(--hk-border)}
.hk-common-scene-card{padding:30px;background:var(--hk-bg-card);border:1px solid var(--hk-border)}
.hk-common-scene-title{font-family:var(--hk-font-serif);font-size:23px!important;color:var(--hk-fg)!important;margin-bottom:12px!important}
.hk-method-ending{max-width:900px;margin:0 auto 50px;padding:28px 20px;display:flex;align-items:center;gap:24px;border-top:1px solid var(--hk-border);border-bottom:1px solid var(--hk-border)}
.hk-ending-quote{font-family:var(--hk-font-serif);font-size:22px;line-height:1.8;margin:0}
.hk-method-nav{max-width:900px;margin:0 auto 70px;padding:0 20px;display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.hk-method-nav a{padding:13px 10px;text-align:center;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);color:var(--hk-fg);font-size:12px}
.hk-method-nav a:hover{border-color:var(--hk-accent-warm);text-decoration:none}
@media(max-width:760px){.hk-method-title-row{align-items:flex-start}.hk-method-title-row h1{font-size:31px}.hk-method-points{grid-template-columns:1fr}.hk-method-nav{grid-template-columns:1fr 1fr}.hk-nyakakichi-image.hero{width:150px;height:150px}.hk-nyakakichi-image{width:130px;height:130px}.hk-method-question{align-items:flex-start}}
@media(max-width:520px){.hk-method-title-row{display:block}.hk-method-title-row .hk-nyakakichi-image{margin:24px auto 0}.hk-method-title-row h1{font-size:27px}.hk-method-section{padding:0 16px}.hk-method-hero{padding:0 16px}.hk-method-nav{grid-template-columns:1fr}.hk-method-ending{padding:24px 16px}.hk-ending-quote{font-size:18px}}
</style>

<?php get_footer(); ?>
