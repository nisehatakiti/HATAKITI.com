<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<main class="hk-container hk-acting">
    <header class="hk-staff-hero">
        <p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜演技をする</p>
        <h1>演技をする</h1>
        <p>まず「体」を作り、そこから「心」「技」へ進みます。実際に身体を動かしながら、演技を学びます。</p>
    </header>

    <nav class="hk-chapter-nav" aria-label="演技をするナビゲーション">
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">← 演劇の教科書</a>
        <span>演技をする</span>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-1/' ) ); ?>">第1章へ →</a>
    </nav>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>第一部　体 ― 演技のできる身体を作る</h2>
            <p>いきなり感情表現や台詞から始めるのではなく、まず身体の土台を整えます。呼吸から始めて、発声、身体、姿勢、歩き方、重心へ進み、最後に感情の解放へつなげます。</p>
        </div>

        <div class="hk-acting-chapter-grid">
            <a class="hk-acting-chapter-card" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-1/' ) ); ?>">
                <span>第1章</span><h3>呼吸法</h3><p>呼吸を観察し、息を止めずに身体と声を使う。</p><strong>読む →</strong>
            </a>
            <a class="hk-acting-chapter-card" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-2/' ) ); ?>">
                <span>第2章</span><h3>発声</h3><p>呼吸と身体を使い、相手に届く声をつくる。</p><strong>読む →</strong>
            </a>
            <a class="hk-acting-chapter-card" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-3/' ) ); ?>">
                <span>第3章</span><h3>身体</h3><p>身体の感覚を知り、必要な力を使える身体をつくる。</p><strong>読む →</strong>
            </a>
            <a class="hk-acting-chapter-card" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-4/' ) ); ?>">
                <span>第4章</span><h3>姿勢</h3><p>立ち方、軸、身体のバランスから人物の土台をつくる。</p><strong>読む →</strong>
            </a>
            <a class="hk-acting-chapter-card" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-5/' ) ); ?>">
                <span>第5章</span><h3>歩き方</h3><p>歩幅、速度、方向、身体の質から人物をつくる。</p><strong>読む →</strong>
            </a>
            <a class="hk-acting-chapter-card" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-6/' ) ); ?>">
                <span>第6章</span><h3>重心</h3><p>前後・左右・上下の重心移動を演技に使う。</p><strong>読む →</strong>
            </a>
            <a class="hk-acting-chapter-card" href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-7/' ) ); ?>">
                <span>第7章</span><h3>感情の解放</h3><p>身体が整ったところから、感情の反応を扱えるようにする。</p><strong>読む →</strong>
            </a>
        </div>
    </section>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>この先の構成</h2>
            <p>体ができたら、次に「心」、そして「技」へ進みます。</p>
        </div>
        <div class="hk-acting-next-grid">
            <div><span>第二部</span><h3>心 ― 役を生きる</h3><p>想像力、感覚、感情、人物、状況、目的、欲求、相手、関係を扱います。</p></div>
            <div><span>第三部</span><h3>技 ― 演技として成立させる</h3><p>台詞、視線、間、リズム、行動、身体表現、空間、相手への働きかけを扱います。</p></div>
        </div>
    </section>

    <nav class="hk-chapter-nav" aria-label="演技をするナビゲーション">
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">← 演劇の教科書</a>
        <span>演技をする</span>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/acting/chapter-1/' ) ); ?>">第1章へ →</a>
    </nav>
</main>

<style>
.hk-acting .hk-staff-hero{max-width:820px;margin:56px auto 48px;text-align:center}
.hk-acting .hk-staff-hero h1{font-family:var(--hk-font-serif);font-size:38px}
.hk-acting .hk-staff-hero>p:last-child{color:var(--hk-fg-dim);line-height:2}
.hk-acting .hk-section{max-width:900px;margin:0 auto 56px}
.hk-acting .hk-section-head{margin-bottom:24px}
.hk-acting .hk-section-head h2{border-bottom:1px solid var(--hk-border);padding-bottom:12px}
.hk-acting .hk-section-head p,.hk-acting .hk-acting-chapter-card p,.hk-acting .hk-acting-next-grid p{color:var(--hk-fg-dim);line-height:1.9}
.hk-acting .hk-acting-chapter-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.hk-acting .hk-acting-chapter-card{display:block;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:24px;color:var(--hk-fg);text-decoration:none;transition:border-color .2s,transform .2s}
.hk-acting .hk-acting-chapter-card:hover{border-color:var(--hk-accent-warm);transform:translateY(-2px)}
.hk-acting .hk-acting-chapter-card span,.hk-acting .hk-acting-next-grid span{font-size:12px;color:var(--hk-accent-warm)}
.hk-acting .hk-acting-chapter-card h3,.hk-acting .hk-acting-next-grid h3{font-family:var(--hk-font-serif);margin:7px 0 10px}
.hk-acting .hk-acting-chapter-card strong{color:var(--hk-accent-warm);font-size:13px}
.hk-acting .hk-acting-next-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.hk-acting .hk-acting-next-grid>div{border:1px solid var(--hk-border);background:var(--hk-bg-card);padding:24px}
.hk-acting .hk-chapter-nav{max-width:900px;margin-left:auto;margin-right:auto}
@media(max-width:700px){.hk-acting .hk-staff-hero h1{font-size:30px}.hk-acting .hk-acting-chapter-grid,.hk-acting .hk-acting-next-grid{grid-template-columns:1fr}}
</style>
<?php get_footer(); ?>