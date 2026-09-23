<?php
/**
 * Virtual page: 演劇の教科書
 *
 * This page is intentionally self-contained so it does not require a
 * WordPress Page record. It can later be split into child pages/CPTs.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="main" class="hk-container hk-textbook">
    <header class="hk-textbook-hero">
        <p class="hk-textbook-kicker">HATAKITI 演劇の教科書</p>
        <h1>演劇を、知る。考える。やってみる。</h1>
        <p>
            演劇の歴史、演技論、エチュード、練習台本を通して、
            「演じるとは何か」を少しずつ学ぶためのページです。
        </p>
    </header>

    <section class="hk-section">
        <div class="hk-section-head"><h2>4つの入口</h2></div>
        <div class="hk-textbook-grid">
            <a class="hk-textbook-card" href="#history">
                <span class="hk-textbook-number">01</span>
                <h3>演劇の歴史</h3>
                <p>古代から現代まで。演劇が何を求め、どう変わってきたのか。</p>
            </a>
            <a class="hk-textbook-card" href="#methods">
                <span class="hk-textbook-number">02</span>
                <h3>演技論・演技システム</h3>
                <p>スタニスラフスキー、メソッド、マイズナー、ルコックなど。</p>
            </a>
            <a class="hk-textbook-card" href="#etudеs">
                <span class="hk-textbook-number">03</span>
                <h3>エチュード</h3>
                <p>一人、二人、グループ。実際に身体を動かして試す練習。</p>
            </a>
            <a class="hk-textbook-card" href="#scripts">
                <span class="hk-textbook-number">04</span>
                <h3>練習台本</h3>
                <p>短時間で演じられるオリジナルの短編台本と演習課題。</p>
            </a>
        </div>
    </section>

    <section class="hk-section" id="history">
        <div class="hk-section-head"><h2>01　演劇の歴史</h2></div>
        <div class="hk-textbook-panel">
            <div class="hk-history-flow">
                <span>古代ギリシャ</span><b>→</b>
                <span>中世・ルネサンス</span><b>→</b>
                <span>近代演劇</span><b>→</b>
                <span>20世紀</span><b>→</b>
                <span>現代演劇</span>
            </div>
            <p>まずは「いつ、誰が、何を変えたのか」を追いながら、演劇の考え方の流れを整理していきます。日本の演劇史も、能・狂言、歌舞伎、人形浄瑠璃、新劇、小劇場など別の流れで扱います。</p>
            <span class="hk-badge-soon">順次追加</span>
        </div>
    </section>

    <section class="hk-section" id="methods">
        <div class="hk-section-head"><h2>02　演技論・演技システム</h2></div>
        <div class="hk-method-table-wrap">
            <table class="hk-method-table">
                <thead>
                    <tr><th>方法</th><th>入口になる問い</th><th>主な関心</th></tr>
                </thead>
                <tbody>
                    <tr><td>スタニスラフスキー・システム</td><td>この人物は、この状況で何をしたい？</td><td>目的・状況・行動・真実性</td></tr>
                    <tr><td>メソッド演技</td><td>自分の内側から、この人物をどう感じる？</td><td>感情・記憶・感覚・心理</td></tr>
                    <tr><td>マイズナー・テクニック</td><td>相手から何を受け、今どう反応した？</td><td>相手・反応・瞬間</td></tr>
                    <tr><td>ルコック・システム</td><td>身体と空間で、どう表現できる？</td><td>身体・動き・空間・遊び</td></tr>
                </tbody>
            </table>
        </div>
        <p class="hk-textbook-note">※ ここでは各方法を単純な「違い」だけでなく、歴史的背景、基本用語、代表的な練習へと掘り下げていきます。</p>
    </section>

    <section class="hk-section" id="etudеs">
        <div class="hk-section-head"><h2>03　エチュード</h2></div>
        <div class="hk-etuде-grid">
            <article class="hk-etuде-card">
                <span>身体表現</span>
                <h3>重い箱</h3>
                <p>実際には何もない箱を、重さの違う箱として扱う。身体の変化だけで重量を伝える。</p>
            </article>
            <article class="hk-etuде-card">
                <span>二人</span>
                <h3>待ち合わせ</h3>
                <p>同じ場所にいる二人。それぞれが相手に言えない目的を一つ持って始める。</p>
            </article>
            <article class="hk-etuде-card">
                <span>即興</span>
                <h3>一人だけ知っている</h3>
                <p>二人のうち一人だけが重要な事実を知っている。説明せず、行動で伝える。</p>
            </article>
        </div>
    </section>

    <section class="hk-section" id="scripts">
        <div class="hk-section-head"><h2>04　練習台本</h2></div>
        <article class="hk-script-card">
            <p class="hk-textbook-kicker">短編台本 001</p>
            <h3>待ち合わせ</h3>
            <div class="hk-script-meta">登場人物：A・B　／　目安：3〜5分</div>
            <div class="hk-script">
                <p><strong>A</strong>　……遅いな。</p>
                <p><strong>B</strong>　ごめん。</p>
                <p><strong>A</strong>　何分待ったと思ってる？</p>
                <p><strong>B</strong>　十五分。</p>
                <p><strong>A</strong>　違う。三十二分。</p>
                <p><strong>B</strong>　そんなに？</p>
                <p><strong>A</strong>　時計見てたから。</p>
                <p><strong>B</strong>　……怒ってる？</p>
                <p><strong>A</strong>　怒ってない。</p>
                <p><strong>B</strong>　怒ってるじゃん。</p>
                <p><strong>A</strong>　怒ってないって。</p>
                <p><strong>B</strong>　じゃあ、なんで来たの？</p>
                <p><strong>A</strong>　……。</p>
                <p><strong>B</strong>　何？</p>
                <p><strong>A</strong>　いや。</p>
                <p><strong>B</strong>　言って。</p>
                <p><strong>A</strong>　今日は、来ないと思ってた。</p>
            </div>
            <div class="hk-exercise">
                <h4>やってみよう</h4>
                <ol>
                    <li>まず普通に演じる。</li>
                    <li>Aは「怒っていることを絶対に認めない」という条件で演じる。</li>
                    <li>Bは「本当は帰りたい」という目的を隠して演じる。</li>
                    <li>台詞を変えず、二人の距離だけを変えて演じる。</li>
                </ol>
            </div>
        </article>
    </section>
</main>

<style>
.hk-textbook-hero{max-width:760px;margin:64px auto 72px;padding:0 20px;text-align:center}
.hk-textbook-kicker{margin:0 0 10px;color:var(--hk-accent-warm);font-size:12px;letter-spacing:.16em}
.hk-textbook-hero h1{font-family:var(--hk-font-serif);font-size:34px}
.hk-textbook-hero>p:last-child{color:var(--hk-fg-dim);line-height:2}
.hk-textbook-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.hk-textbook-card,.hk-textbook-panel,.hk-etuде-card,.hk-script-card{border:1px solid var(--hk-border);background:var(--hk-bg-elevated)}
.hk-textbook-card{padding:24px 20px;color:var(--hk-fg)}
.hk-textbook-card:hover{border-color:var(--hk-accent-warm);text-decoration:none}
.hk-textbook-number{display:block;color:var(--hk-accent-warm);font-size:12px;letter-spacing:.12em}
.hk-textbook-card h3{font-size:17px;margin:10px 0}
.hk-textbook-card p,.hk-textbook-panel p,.hk-etuде-card p{margin:0;color:var(--hk-fg-dim);font-size:14px}
.hk-textbook-panel{padding:28px}
.hk-history-flow{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;margin-bottom:24px;color:var(--hk-fg)}
.hk-history-flow b{color:var(--hk-accent-warm)}
.hk-method-table-wrap{overflow-x:auto}
.hk-method-table{width:100%;border-collapse:collapse;background:var(--hk-bg-elevated)}
.hk-method-table th,.hk-method-table td{border:1px solid var(--hk-border);padding:14px 16px;text-align:left;vertical-align:top}
.hk-method-table th{color:var(--hk-accent-warm);font-size:13px}
.hk-method-table td{font-size:14px}
.hk-textbook-note{margin-top:16px;color:var(--hk-fg-faint);font-size:13px}
.hk-etuде-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.hk-etuде-card{padding:24px}
.hk-etuде-card>span{font-size:11px;color:var(--hk-accent-cool)}
.hk-etuде-card h3{margin:8px 0}
.hk-script-card{padding:32px;max-width:760px;margin:auto}
.hk-script-card h3{font-family:var(--hk-font-serif);font-size:26px}
.hk-script-meta{font-size:12px;color:var(--hk-fg-faint);border-bottom:1px solid var(--hk-border);padding-bottom:16px;margin-bottom:24px}
.hk-script p{margin:0 0 9px}
.hk-exercise{margin-top:30px;padding:22px;border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card)}
.hk-exercise h4{margin-bottom:10px}
@media(max-width:900px){.hk-textbook-grid{grid-template-columns:repeat(2,1fr)}.hk-etuде-grid{grid-template-columns:1fr}}
@media(max-width:600px){.hk-textbook-hero h1{font-size:27px}.hk-textbook-grid{grid-template-columns:1fr}.hk-method-table{min-width:680px}.hk-script-card{padding:24px 18px}}
</style>

<?php get_footer(); ?>
