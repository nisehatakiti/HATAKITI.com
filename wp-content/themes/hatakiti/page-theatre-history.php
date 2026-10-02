<?php
/**
 * Virtual page: 演劇の歴史
 * Chapter 1: 演劇はなぜ生まれたのか
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
$base = trim( parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
if ( $base && 0 === strpos( $path, $base . '/' ) ) {
    $path = substr( $path, strlen( $base ) + 1 );
}

if ( 'theatre-textbook/history' !== $path && 'theatre-textbook/history/chapter-1' !== $path ) {
    wp_safe_redirect( home_url( '/theatre-textbook/' ) );
    exit;
}

get_header();
?>
<main class="hk-container hk-history-textbook">
    <header class="hk-textbook-hero">
        <p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜演劇の歴史</p>
        <h1>第1章　演劇はなぜ生まれたのか</h1>
        <p>劇場や台本ができる前から、人間は「まねる」「語る」「踊る」「誰かになる」「人に見せる」という行為をしてきました。まずは、演劇が生まれた背景から考えてみましょう。</p>
    </header>

    <nav class="hk-chapter-nav" aria-label="演劇史ナビゲーション">
        <span class="hk-chapter-nav-disabled">← 前の章</span>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">目次</a>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/history/chapter-2/' ) ); ?>">第2章 →</a>
    </nav>

    <section class="hk-section hk-history-intro">
        <h2>はじめに</h2>
        <p>「演劇の歴史」と聞くと、立派な劇場や舞台を思い浮かべるかもしれません。でも、最初から劇場があったわけではありません。</p>
        <p>照明もありません。客席もありません。台本もありません。もちろん、「俳優」という職業もありません。</p>
        <p>それでも人間は、ずっと昔から、誰かのまねをする、何かを演じる、物語を語る、歌う、踊る、人に見せる、といったことをしてきました。</p>
        <div class="hk-nyakakichi">
            <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-question.png' ) ); ?>" alt="演劇について疑問を持つにゃかきち" loading="lazy"></div>
            <div class="hk-nyakakichi-question"><p>人間は、どうして「演じる」ようになったんだろう？</p></div>
        </div>
        <div class="hk-nyakakichi-followup"><p>この章では、演劇が生まれるまでを考えながら、<strong>演劇とはそもそも何なのか</strong>を見ていきます。</p></div>
    </section>

    <section class="hk-section hk-nyakakichi-break">
        <div class="hk-nyakakichi">
            <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-thinking.png' ) ); ?>" alt="考えながら演劇の歴史を読むにゃかきち" loading="lazy"></div>
            <div class="hk-nyakakichi-question"><p>劇場も台本もないのに、どうやって演劇が始まったんだろう？</p></div>
        </div>
        <div class="hk-nyakakichi-followup"><p>ここからは、演劇ができる前の「人間の行動」に目を向けてみましょう。</p></div>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-1　「演じる」ということ</h2>
            <p>まずは「ごっこ遊び」から考えてみます。</p>
        </div>
        <p>子どもが「お店屋さんごっこ」をしているところを想像してください。</p>
        <p>一人が店員になります。「いらっしゃいませ」。もう一人がお客さんになります。「これください」。</p>
        <p>実際にはお店ではありません。それでも二人は一時的に、<strong>「店員」と「お客さん」</strong>という別の役割を演じています。</p>
        <p>つまり「演じる」とは、<strong>自分ではない人物や、自分とは違う立場になって表現すること</strong>の一つだと考えられます。</p>
        <div class="hk-nyakakichi">
            <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-thinking.png' ) ); ?>" alt="演じることを考えるにゃかきち" loading="lazy"></div>
            <div class="hk-nyakakichi-question"><p>じゃあ、「演じる」って、ただまねをすることなのかニャ？</p></div>
        </div>
        <div class="hk-nyakakichi-followup"><p>ここから先では、まねることと演じることの関係を少しずつ見ていきます。</p></div>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-2　人間は「まね」をする</h2>
            <p>まねから始まる表現があります。</p>
        </div>
        <p>人間は、周りにいる人や動物の動きをまねすることがあります。歩き方をまねる。話し方をまねる。仕事をする姿をまねる。</p>
        <p>まねをすることで、人間は知らないことを学ぶこともできます。でも、「まね」にはもう一つの使い方があります。</p>
        <p><strong>その場にないものを、目の前で再現することです。</strong></p>
        <p>例えば、狩りから帰ってきた人が「今日、こんな動物を見た」と言いながら、その動物の走り方を身体で表現したとします。見ている人は、その動きを見て「動物が走っているところ」を想像できます。</p>
        <figure class="hk-history-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/history/' ) ); ?>history-chapter1-01-mimicry-to-expression.png" alt="人間は「まね」から何を生み出したのかを示す図解" loading="lazy"></figure>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-3　人間は物語を語る</h2>
            <p>「こんなことがあった」と人に伝えるところから、表現は広がります。</p>
        </div>
        <p>人間は、経験した出来事を他の人に伝えます。「今日、こんなことがあった」「昔、こんな人がいた」「この山には、こんな話がある」。</p>
        <p>物語を語るとき、人間は声だけを使うわけではありません。表情を変えたり、身振りを加えたりします。</p>
        <p>怖い話なら声を小さくする。大きな出来事なら身体を大きく動かす。登場人物の言葉を、その人物になったつもりで言ってみる。</p>
        <p>ここで、<strong>「出来事を説明する」</strong>ことから、<strong>「出来事を実際に見せる」</strong>ことへ近づいていきます。</p>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-4　「語る」から「演じる」へ</h2>
            <p>出来事を話すことと、人物として見せることには違いがあります。</p>
        </div>
        <div class="hk-compare">
            <div><span>語る</span><p>「男は『ここから先へは行くな』と言いました。」</p></div>
            <div><span>演じる</span><p>「ここから先へは行くな！」</p></div>
        </div>
        <p>前者は出来事を<strong>語っている</strong>状態です。後者は、その人物の声や身体を使って<strong>演じている</strong>状態に近づきます。</p>
        <p>もちろん、語りと演技を完全に分けることはできません。昔から、語ることと演じることが混ざった表現もたくさんありました。</p>
        <p>それでも、「誰かについて話す」ことと「自分がその人物になって見せる」ことには違いがあります。この違いは、演劇を考えるうえで大切です。</p>
        <figure class="hk-history-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/history/' ) ); ?>history-chapter1-02-tell-and-act.png" alt="「語る」から「演じる」へを比較する図解" loading="lazy"></figure>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-5　「見る人」が生まれる</h2>
            <p>演劇を考えるとき、もう一つ重要なのが「見る人」の存在です。</p>
        </div>
        <p>一人で踊っているだけなら、それは踊りです。そこに別の人がいて、その踊りを見ている。すると、<strong>「見せる人」と「見る人」</strong>という関係が生まれます。</p>
        <p>演劇では、俳優だけでなく観客も重要です。俳優が表現し、観客がそれを受け取る。その両方が同じ時間と空間を共有することで、舞台上の出来事が成立します。</p>
        <figure class="hk-history-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/history/' ) ); ?>history-chapter1-03-performer-and-audience.png" alt="演じる人と見る人の関係を示す図解" loading="lazy"></figure>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-6　祭りや儀式から考える</h2>
            <p>昔の人々は、生活に関わる大切な出来事に合わせて、さまざまな表現をしていました。</p>
        </div>
        <p>季節の変化や収穫、誕生、死などに合わせて、歌う、踊る、仮面をつける、特別な衣装を着る、行列をする、物語を語る、といった行為が行われていました。</p>
        <p>祭りや儀式には、後の演劇につながる要素がたくさんあります。ただし、<strong>「昔の儀式＝演劇」</strong>というわけではありません。</p>
        <p>儀式には宗教的・社会的な目的があります。演劇には、物語を表現することや、人を楽しませることなど、さまざまな目的があります。両者には重なる部分がありますが、同じものではありません。</p>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-7　「誰かになる」という不思議</h2>
            <p>舞台の上の人は、本当にその人物なのでしょうか？</p>
        </div>
        <p>舞台の上に王様が登場したとします。でも、演じている人は本当の王様ではありません。俳優です。</p>
        <p>それでも観客は、「この人は王様なんだ」と考えて舞台を見ることができます。</p>
        <p>俳優は本当に王様になる必要はありません。観客も、本物の王様がいると思っているわけではありません。</p>
        <p>それでも両者が、<strong>「この時間、この場所では、この人を王様として見る」</strong>という約束を共有することができます。</p>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-8　演劇には「約束」がある</h2>
            <p>何もない場所でも、観客の想像によって舞台を作ることができます。</p>
        </div>
        <p>舞台の上に何もないとします。俳優が「ここに大きな箱があります」と言って、箱を持ち上げる動きをします。</p>
        <p>実際には箱はありません。でも、身体の使い方を変えれば「重い箱」を表現できます。観客も、その表現を受け取って想像します。</p>
        <p>舞台に本物の家を作らなくても、「ここは家です」という表現と観客の想像によって、そこを家として扱うことができます。</p>
        <figure class="hk-history-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/history/' ) ); ?>history-chapter1-04-empty-space-becomes-stage.png" alt="劇場がなくても舞台が生まれることを示す図解" loading="lazy"></figure>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-9　劇場がなくても演劇はできる</h2>
            <p>演劇と劇場は、同じものではありません。</p>
        </div>
        <p><strong>演劇に、必ず劇場が必要なわけではありません。</strong></p>
        <p>教室でもできます。体育館でもできます。公園でもできます。路上でもできます。何もない空間でもできます。</p>
        <p>人間が演じる行為そのものは、劇場ができるより前から存在していました。</p>
        <p>だから演劇史を見るときには、<strong>「どんな劇場が作られたか」</strong>だけでなく、<strong>「人々はどこで、誰に向けて、何を演じていたのか」</strong>を見ることも大切です。</p>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-10　演劇は社会を映す</h2>
            <p>演劇は、社会と切り離されて存在しているわけではありません。</p>
        </div>
        <p>その時代の生活や考え方が、演劇にも影響します。</p>
        <ul class="hk-check-list">
            <li>誰が舞台に立てるのか</li>
            <li>誰が観客になれるのか</li>
            <li>どんな物語が人気なのか</li>
            <li>どんな言葉が使われるのか</li>
            <li>女性は舞台に立てるのか</li>
            <li>身分によって観劇できる場所が違うのか</li>
            <li>宗教や政治と演劇はどう関係するのか</li>
        </ul>
        <p>つまり、<strong>演劇の歴史は、人間がどんな社会で暮らしてきたのかを見る歴史でもある</strong>のです。</p>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>1-11　演劇は「人間を知る」方法でもある</h2>
            <p>自分とは違う人物を演じることで、別の人間を想像します。</p>
        </div>
        <p>王様になる。子どもになる。老人になる。犯罪者になる。自分とはまったく違う考え方を持った人物になる。</p>
        <p>そして、その人物について考えます。</p>
        <div class="hk-question-list">
            <p>「なぜ、この人はこんなことをするのだろう？」</p>
            <p>「何を怖がっているのだろう？」</p>
            <p>「何を望んでいるのだろう？」</p>
        </div>
        <p>こう考えることで、自分とは違う人間を想像することになります。観客も、舞台上の人物を見ることで「自分だったらどうするだろう？」と考えることがあります。</p>
        <p>演劇には、<strong>他人を想像する</strong>という役割もあるのです。</p>
    </section>


    <section class="hk-section hk-reading-note">
        <p><strong>この章で出てきた言葉は、あとで「演劇用語集」にまとめます。</strong>本文では言葉の説明を増やしすぎず、まず演劇そのものの流れを読んでみましょう。</p>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>やってみよう　「演じる」の原点</h2>
            <p>演技の一番基本的なところを体験してみましょう。</p>
        </div>
        <div class="hk-exercise">
            <h3>「重い箱」と「軽い箱」</h3>
            <ol>
                <li><strong>箱を想像する。</strong> 目の前に箱があると思ってください。実際には何もありません。</li>
                <li><strong>普通の箱として持つ。</strong> 両手で箱を持ち上げます。</li>
                <li><strong>重い箱にする。</strong> とても重い箱だと思って持ち上げます。足、腰、呼吸など、身体のどこが変わるでしょうか。</li>
                <li><strong>軽い箱にする。</strong> 片手でも簡単に持てる箱にします。さっきとは身体の使い方が変わるはずです。</li>
            </ol>
        </div>
        <div class="hk-nyakakichi">
            <div class="hk-nyakakichi-image"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/nyakakichi/nyakakichi-practice.png' ) ); ?>" alt="重い箱と軽い箱の練習をするにゃかきち" loading="lazy"></div>
            <div class="hk-nyakakichi-question"><p>本物の箱がなくても、身体が変われば『重い』って伝えられるんだニャ！</p></div>
        </div>
        <div class="hk-nyakakichi-followup"><p>そうです。これが、演技で身体を使うときの基本の一つです。</p></div>
    </section>

    <section class="hk-section">
        <div class="hk-panel hk-summary">
            <h2>第1章まとめ</h2>
            <p>演劇は、最初から劇場や台本があって始まったわけではありません。</p>
            <p>人間が、<strong>まねる・語る・踊る・歌う・誰かになる・人に見せる</strong>というさまざまな行為を重ねる中で、演劇につながる表現が生まれていきました。</p>
            <p>演劇には、<strong>演じる人</strong>と<strong>見る人</strong>がいます。演じる人は身体や声を使って人物や出来事を表現し、見る人はそれを受け取って想像します。</p>
            <p>そして両者が同じ時間と空間を共有することで、一つの舞台上の出来事が生まれます。</p>
        <figure class="hk-history-figure"><img src="<?php echo esc_url( content_url( 'plugins/hatakiti-core/assets/images/history/' ) ); ?>history-chapter1-05-birth-of-theatre.png" alt="演劇が生まれるまでのさまざまな行為をまとめた図解" loading="lazy"></figure>
            <p>演劇にはたくさんの形があります。だから、「これだけが演劇だ」と簡単に決めることはできません。</p>
            <p>演劇は、人間がその時代、その社会の中で、<strong>「誰かに何かを見せたい」「何かを伝えたい」「別の人間や世界を表現したい」</strong>と考えた結果として、さまざまな形に変化してきたものだと考えることができます。</p>
        </div>
    </section>

    <section class="hk-section hk-next-chapter">
        <div class="hk-section-head">
            <h2>次は、実際の歴史へ</h2>
            <p>ここまでで「演劇が生まれる土台」を見ました。次は、古代の社会の中で、どんな演劇が実際に作られていったのかを見ていきます。</p>
        </div>
        <a class="hk-history-next-card" href="<?php echo esc_url( home_url( '/theatre-textbook/history/chapter-2/' ) ); ?>">
            <span>第2章</span>
            <strong>古代の演劇</strong>
            <small>古代ギリシャ、古代ローマ、そして世界各地の古い演劇へ</small>
        </a>
    </section>

    <nav class="hk-chapter-nav" aria-label="演劇史ナビゲーション">
        <span class="hk-chapter-nav-disabled">← 前の章</span>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">目次</a>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/history/chapter-2/' ) ); ?>">第2章 →</a>
    </nav>
</main>

<style>
.hk-history-textbook .hk-section{max-width:900px}
.hk-history-textbook .hk-section-head h2{font-size:24px}
.hk-history-textbook .hk-chapter-nav{display:flex;justify-content:space-between;align-items:center;gap:12px;margin:28px 0;padding:14px 0;border-top:1px solid var(--hk-border);border-bottom:1px solid var(--hk-border)}
.hk-history-textbook .hk-chapter-nav a,.hk-history-textbook .hk-chapter-nav span{flex:1}
.hk-history-textbook .hk-chapter-nav a:nth-child(2),.hk-history-textbook .hk-chapter-nav span:nth-child(2){text-align:center}
.hk-history-textbook .hk-chapter-nav a:last-child,.hk-history-textbook .hk-chapter-nav span:last-child{text-align:right}
.hk-history-textbook .hk-nyakakichi{display:flex;align-items:center;gap:18px;margin:28px 0 10px;padding:0;background:transparent;border:0;color:var(--hk-fg);position:relative;overflow:visible}
.hk-history-textbook .hk-nyakakichi-image{flex:0 0 110px;text-align:center}
.hk-history-textbook .hk-nyakakichi-image img{display:block;width:110px;height:auto;max-height:170px;object-fit:contain;margin:0 auto}
.hk-history-textbook .hk-nyakakichi-question{flex:1;position:relative;background:#454545;border-radius:16px;padding:16px 20px;color:#fff;line-height:1.8}
.hk-history-textbook .hk-nyakakichi-question:before{content:"";position:absolute;left:-12px;top:24px;border-top:10px solid transparent;border-bottom:10px solid transparent;border-right:14px solid #454545}
.hk-history-textbook .hk-nyakakichi-question p{margin:0;color:#fff}
.hk-history-textbook .hk-nyakakichi-followup{margin:0 0 24px 128px;line-height:1.9;color:var(--hk-fg)}
.hk-history-textbook .hk-nyakakichi-followup p{margin:0 0 8px}
.hk-history-textbook .hk-nyakakichi-break{margin-top:8px}
.hk-history-textbook .hk-reading-note{margin-top:8px}
.hk-history-textbook .hk-reading-note p{margin:0;padding:18px 20px;border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card);color:var(--hk-fg-dim)}
.hk-history-textbook p,.hk-history-textbook li{line-height:2}
.hk-term-note{margin:28px 0;padding:22px 24px;border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card)}
.hk-term-note h3{margin:0 0 8px;font-size:15px;color:var(--hk-accent-warm)}
.hk-term-note p{margin:0;color:var(--hk-fg-dim)}
.hk-compare{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:28px 0}
.hk-compare>div{padding:24px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}
.hk-compare span{display:block;color:var(--hk-accent-warm);font-size:12px;letter-spacing:.12em;margin-bottom:10px}
.hk-compare p{margin:0}
.hk-check-list{padding-left:1.4em}
.hk-question-list{margin:28px 0;padding:20px 24px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}
.hk-question-list p{margin:4px 0}
.hk-next-chapter{margin-top:64px}
.hk-history-next-card{display:flex;flex-direction:column;gap:7px;max-width:620px;padding:24px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);color:var(--hk-fg)}
.hk-history-next-card:hover{border-color:var(--hk-accent-warm);text-decoration:none}
.hk-history-next-card span{font-size:11px;color:var(--hk-accent-warm);letter-spacing:.12em}
.hk-history-next-card strong{font-family:var(--hk-font-serif);font-size:22px}
.hk-history-next-card small{color:var(--hk-fg-dim);font-size:13px}
@media(max-width:650px){.hk-compare{grid-template-columns:1fr}.hk-history-textbook .hk-nyakakichi{align-items:center;gap:12px;margin-top:24px}.hk-history-textbook .hk-nyakakichi-image{flex-basis:90px}.hk-history-textbook .hk-nyakakichi-image img{width:90px;max-height:145px}.hk-history-textbook .hk-nyakakichi-question{padding:13px 15px}.hk-history-textbook .hk-nyakakichi-question:before{left:-10px;top:20px;border-top-width:8px;border-bottom-width:8px;border-right-width:11px}.hk-history-textbook .hk-nyakakichi-followup{margin-left:102px;margin-bottom:20px}}
</style>

<?php get_footer(); ?>
