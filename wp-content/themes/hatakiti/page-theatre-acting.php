<?php
/**
 * Virtual pages: 演技編
 * Beginner-friendly acting textbook chapters.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
$base = trim( parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
if ( $base && 0 === strpos( $path, $base . '/' ) ) {
    $path = substr( $path, strlen( $base ) + 1 );
}

if ( 'theatre-textbook/acting' === $path ) {
    get_header();
    ?>
    <main class="hk-container hk-acting">
        <header class="hk-staff-hero">
            <p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜演技</p>
            <h1>演技編</h1>
            <p>「感情をどう出すか」から始めて、身体、呼吸、声、視線、相手との関係、行動へと、実際に演じるための考え方を学びます。</p>
        </header>

        <nav class="hk-chapter-nav" aria-label="演技編ナビゲーション">
            <span class="hk-chapter-nav-disabled">← 前の章</span>
            <a href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">演劇の教科書</a>
            <a href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-1/' ) ); ?>">第1章 →</a>
        </nav>

        <section class="hk-section">
            <div class="hk-section-head">
                <h2>演技編で最初に考えること</h2>
                <p>演技は、感情を「作って見せる」ことだけではありません。自分の中にある反応や衝動を邪魔しているものに気づき、身体と声を自由に使える状態を作ることも、演技の大切な土台です。</p>
            </div>
            <div class="hk-panel">
                <p><strong>第1章では「感情の解放」から始めます。</strong></p>
                <p>ただし、これは無理に泣いたり、過去のつらい経験を思い出したりすることではありません。安全な範囲で、呼吸・身体・声・想像力を使い、自分の反応を抑え込まずに出してみる練習です。</p>
            </div>
        </section>

        <section class="hk-section">
            <div class="hk-section-head">
                <h2>第1章　感情の解放</h2>
                <p>「感情を出せ」と言われても出てこない。そのとき、感情がないのではなく、身体や頭が先に止めていることがあります。</p>
            </div>
            <div class="hk-term-grid">
                <div><h3>感じる</h3><p>今、自分の中に何が起きているのかを観察する。</p></div>
                <div><h3>止めない</h3><p>出てきた反応を「こんなのは変だ」とすぐに修正しない。</p></div>
                <div><h3>動く</h3><p>感情を身体の中だけに閉じ込めず、呼吸や動きにつなげる。</p></div>
                <div><h3>声にする</h3><p>ためらいながらでも、息・声・音として外へ出してみる。</p></div>
            </div>
        </section>

        <section class="hk-section">
            <div class="hk-section-head"><h2>やってみよう</h2></div>
            <div class="hk-exercise">
                <h3>5分「止めない」エチュード</h3>
                <ol>
                    <li>立ったまま、まず普通に呼吸する。</li>
                    <li>肩、首、手、顔など、力が入っている場所を探す。</li>
                    <li>息を吐きながら、身体を少しだけ動かす。</li>
                    <li>出てきた動きを「上手い・下手」と評価せず、そのまま続ける。</li>
                    <li>最後に、小さな声でもよいので、息を音にしてみる。</li>
                </ol>
                <p>目的は「大きな感情を出す」ことではありません。自分の反応を先回りして止めない感覚を覚えることです。</p>
            </div>
        </section>

        <section class="hk-section">
            <div class="hk-panel hk-summary">
                <h3>第1章の入口</h3>
                <p><strong>感情を作る前に、感情が動ける身体を作る。</strong></p>
                <p>次のページから、感情の解放を「呼吸」「身体」「声」「想像」「相手との関係」へと分けて、具体的な練習に進みます。</p>
            </div>
        </section>

        <nav class="hk-chapter-nav" aria-label="演技編ナビゲーション">
            <span class="hk-chapter-nav-disabled">← 前の章</span>
            <a href="<?php echo esc_url( home_url( '/theatre-textbook/acting/' ) ); ?>">目次</a>
            <a href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-1/' ) ); ?>">第1章 →</a>
        </nav>
    </main>
    <?php
    get_footer();
    return;
}

if ( 'theatre-textbook/acting/chapter-1' !== $path ) {
    wp_safe_redirect( home_url( '/theatre-textbook/acting/' ) );
    exit;
}

get_header();
?>
<main class="hk-container hk-acting">
<header class="hk-staff-hero">
    <p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜演技</p>
    <h1>第1章　感情の解放</h1>
    <p>感情を無理に作るのではなく、呼吸・身体・声・想像力を使って、自分の反応を止めないところから演技を始めます。</p>
</header>

<nav class="hk-chapter-nav" aria-label="演技編の章ナビゲーション">
    <span class="hk-chapter-nav-disabled">← 前の章</span>
    <a href="<?php echo esc_url( home_url( '/theatre-textbook/acting/' ) ); ?>">目次</a>
    <span class="hk-chapter-nav-disabled">第2章 →</span>
</nav>

<section class="hk-section">
    <div class="hk-section-head">
        <h2>導入　「感情を出して」と言われたら</h2>
        <p>演技の稽古で「もっと感情を出して」と言われることがあります。でも、感情は蛇口のように、意志だけで出したり止めたりできるものではありません。</p>
    </div>
    <p>「悲しい顔をしよう」「怒った声を出そう」と外側から形を作ることはできます。それでも、本人の中で何も起きていなければ、どこか説明しているような芝居になってしまうことがあります。</p>
    <p>そこで最初に考えたいのが、<strong>感情を大きくすることではなく、感情が動くのを邪魔しないこと</strong>です。</p>

    <div class="hk-nyakakichi">
        <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-question.png' ) ); ?>" alt="にゃかきち" loading="lazy"></div>
        <div class="hk-nyakakichi-question"><p>「感情を出そうと頑張るほど、逆に出てこないことがあるの？」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>あります。出そうとすることより、身体や呼吸を自由にして、起きている反応をそのまま受け取るほうが入口になることがあります。</p></div>
</section>

<section class="hk-section">
    <div class="hk-section-head">
        <h2>1-1　感情は「顔」だけにあるわけではない</h2>
        <p>感情が動くと、身体にも変化が起きます。</p>
    </div>
    <p>呼吸が浅くなる。胸が詰まる。肩が上がる。手に力が入る。足が動きたくなる。逆に、身体が固まって動かなくなることもあります。</p>
    <p>つまり、感情は表情だけで表現するものではありません。<strong>呼吸、姿勢、重心、筋肉、視線、声</strong>にも現れます。</p>
    <div class="hk-term-grid">
        <div><h3>呼吸</h3><p>速くなる、止まる、深くなる、浅くなる。</p></div>
        <div><h3>身体</h3><p>近づく、離れる、固まる、崩れる。</p></div>
        <div><h3>視線</h3><p>見る、避ける、追う、焦点が合わない。</p></div>
        <div><h3>声</h3><p>息が混じる、詰まる、震える、強くなる。</p></div>
    </div>
</section>

<section class="hk-section">
    <div class="hk-section-head">
        <h2>1-2　まず「感じていること」に気づく</h2>
        <p>感情を解放する第一歩は、何か特別な感情を作ることではありません。</p>
    </div>
    <p>「今、肩に力が入っている」「息を止めている」「笑いそうになっている」「声を出すのをためらっている」など、現在の自分の反応に気づきます。</p>
    <p>ここでは、良い悪いを判断しません。<strong>気づいたことを、そのまま観察する</strong>だけです。</p>

    <div class="hk-nyakakichi">
        <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-thinking.png' ) ); ?>" alt="にゃかきち" loading="lazy"></div>
        <div class="hk-nyakakichi-question"><p>「じゃあ、無理に悲しくなったり、怒ったりしなくてもいいんだね？」</p></div>
    </div>
    <div class="hk-nyakakichi-followup"><p>その通り。まずは「今、自分の中で何が起きているか」を知ることから始めます。</p></div>
</section>

<section class="hk-section">
    <div class="hk-section-head">
        <h2>1-3　呼吸を止めない</h2>
        <p>感情が動くとき、呼吸も変わります。そして、呼吸を止めると身体全体が固まりやすくなります。</p>
    </div>
    <p>まず、ゆっくり息を吐きます。大きく吸おうとする必要はありません。吐く息を邪魔しないだけでも、身体の緊張に気づきやすくなります。</p>
    <div class="hk-exercise">
        <h3>呼吸の練習</h3>
        <ol>
            <li>立って、普段どおりに呼吸する。</li>
            <li>息を吐くときに、肩や顎の力を少し抜く。</li>
            <li>息を吐きながら、身体の小さな動きを一つ許す。</li>
            <li>無理に深呼吸せず、自分の自然な呼吸に戻る。</li>
        </ol>
    </div>
</section>

<section class="hk-section">
    <div class="hk-section-head">
        <h2>1-4　身体のブレーキを外す</h2>
        <p>感情が出ないとき、「感情がない」のではなく、身体が先にブレーキをかけていることがあります。</p>
    </div>
    <p>例えば、笑いそうになった瞬間に口を閉じる。泣きそうになった瞬間に息を止める。怒りを感じた瞬間に肩や顎を固める。</p>
    <p>こうした反応は日常生活では役に立つこともあります。でも舞台では、そこから先の変化を観客に見せる必要があります。</p>
    <p>だから稽古では、<strong>「出そうとする」より「止めているものに気づく」</strong>ことから始めます。</p>
</section>

<section class="hk-section">
    <div class="hk-section-head">
        <h2>1-5　声を「感情の出口」にする</h2>
        <p>声は台詞だけのものではありません。</p>
    </div>
    <p>ため息、笑い、うめき、息、短い声、叫びなど、言葉になる前の音にも身体の反応が表れます。</p>
    <div class="hk-exercise">
        <h3>声を出す練習</h3>
        <ol>
            <li>息を吐く。</li>
            <li>「あ」「う」「ん」など短い音を出す。</li>
            <li>音を大きくすることより、息と声がつながっていることを感じる。</li>
            <li>笑い、ため息、驚きなど、自然に出てきた音を一度そのまま許す。</li>
        </ol>
        <p>大声を出すことが目的ではありません。声を出すことへの「ためらい」を少しずつ減らします。</p>
    </div>
</section>

<section class="hk-section">
    <div class="hk-section-head">
        <h2>1-6　感情を「作らない」</h2>
        <p>ここが、この章で一番大切なところです。</p>
    </div>
    <p>「悲しい役だから悲しくならなければ」「怒っている役だから怒らなければ」と考えると、感情を結果として作ろうとしてしまいます。</p>
    <p>そうではなく、<strong>その人物が何を見て、何を求め、何をしようとしているのか</strong>に意識を向けます。</p>
    <p>感情は、その状況の中で起きる反応として扱うことができます。</p>
    <div class="hk-panel">
        <p><strong>「悲しく見せる」</strong> → 顔を悲しくする</p>
        <p><strong>「悲しい状況にいる人物として、相手に何を求めるか」</strong> → 行動の中で感情が動く</p>
    </div>
</section>

<section class="hk-section">
    <div class="hk-section-head">
        <h2>1-7　5分エチュード「言いたいのに言えない」</h2>
        <p>感情を直接作らず、身体と行動から反応を起こしてみます。</p>
    </div>
    <div class="hk-exercise">
        <h3>設定</h3>
        <p>AはBにどうしても伝えたいことがある。でも、なかなか言い出せない。</p>
        <ol>
            <li>AはBの前に立つ。</li>
            <li>台詞は「ねえ」だけにする。</li>
            <li>「ねえ」と言ったあと、言いたいことを身体の中に持ったまま待つ。</li>
            <li>呼吸、視線、距離、手の動きがどう変化するか観察する。</li>
            <li>最後に一度だけ、言いたかった言葉を出してみる。</li>
        </ol>
        <p>ここで大切なのは「泣く」「怒る」などの結果を決めないことです。何かが起きるのを待ち、その反応を受け取ります。</p>
    </div>
</section>

<section class="hk-section">
    <div class="hk-section-head">
        <h2>1-8　解放と暴走は違う</h2>
        <p>感情を自由にすることは、何をしてもいいという意味ではありません。</p>
    </div>
    <p>舞台には相手役、観客、台詞、動線、演出意図があります。感情が動いていても、相手に危険を与えたり、作品の約束を壊したりしてよいわけではありません。</p>
    <p>目指すのは、<strong>感情を抑え込まないことと、演技をコントロールできることを両立する状態</strong>です。</p>
    <div class="hk-panel">
        <h3>稽古での注意</h3>
        <ul>
            <li>個人的なつらい記憶を無理に掘り起こす必要はありません。</li>
            <li>苦しくなったら練習を止めて構いません。</li>
            <li>相手に触れる、叫ぶ、物を投げるなどの行為は、必ず事前にルールを決めます。</li>
            <li>「本気になること」と「自分を追い込むこと」は同じではありません。</li>
        </ul>
    </div>
</section>

<section class="hk-section">
    <div class="hk-panel hk-summary">
        <h3>第1章まとめ</h3>
        <p>感情の解放は、感情を無理やり大きくすることではありません。</p>
        <p><strong>感じる → 気づく → 止めない → 呼吸する → 身体を動かす → 声にする → 行動につなげる</strong></p>
        <p>この流れを繰り返しながら、感情が動いても、それを舞台上で扱える身体を作っていきます。</p>
    </div>
    <div class="hk-nyakakichi">
        <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-practice.png' ) ); ?>" alt="にゃかきち" loading="lazy"></div>
        <div class="hk-nyakakichi-question"><p>「感情を出すんじゃなくて、感情が動けるようにするんだニャ！」</p></div>
    </div>
</section>

<nav class="hk-chapter-nav" aria-label="演技編の章ナビゲーション">
    <span class="hk-chapter-nav-disabled">← 前の章</span>
    <a href="<?php echo esc_url( home_url( '/theatre-textbook/acting/' ) ); ?>">目次</a>
    <span class="hk-chapter-nav-disabled">第2章 →</span>
</nav>
</main>
<?php get_footer(); ?>
