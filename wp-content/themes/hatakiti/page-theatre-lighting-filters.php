<?php
/**
 * Virtual page: ゼラ色見本データベース
 *
 * Data source: assets/data/gel-colors.json
 * Manufacturer facts and HATAKITI editorial usage tags are kept distinct.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$gel_data_path = get_template_directory() . '/assets/data/gel-colors.json';
$gel_records = array();

if ( file_exists( $gel_data_path ) ) {
    $gel_json = file_get_contents( $gel_data_path );
    $gel_payload = json_decode( $gel_json, true );
    if ( isset( $gel_payload['records'] ) && is_array( $gel_payload['records'] ) ) {
        $gel_records = $gel_payload['records'];
    }
}

$manufacturers = array();
$series = array();
$uses = array(
    '人物', '顔・肌', '背景', '空間', '夕景', '朝・昼', '夜', '月明かり',
    '日光', '室内', '暖色系の演出', '寒色系の演出', '心理表現', 'ファンタジー', '特殊効果',
);

foreach ( $gel_records as $record ) {
    if ( ! empty( $record['manufacturer'] ) ) {
        $manufacturers[ $record['manufacturer'] ] = true;
    }
    if ( ! empty( $record['series'] ) ) {
        $series[ $record['series'] ] = true;
    }
}

$manufacturer_options = array_keys( $manufacturers );
$series_options = array_keys( $series );
natcasesort( $manufacturer_options );
natcasesort( $series_options );

$request_path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
$detail_id = '';
$filter_prefix = 'theatre-textbook/staff/lighting/filters/';
if ( 0 === strpos( $request_path, $filter_prefix ) ) {
    $detail_id = trim( substr( $request_path, strlen( $filter_prefix ) ), '/' );
}

$selected_record = null;
if ( $detail_id ) {
    foreach ( $gel_records as $record ) {
        if ( isset( $record['id'] ) && $record['id'] === $detail_id ) {
            $selected_record = $record;
            break;
        }
    }
}

get_header();
?>
<main class="hk-container hk-gel-page">
    <header class="hk-gel-hero">
        <p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜照明</p>
        <h1>ゼラ色見本データベース</h1>
        <p>舞台照明用カラーフィルターを、色そのものを見ながら探せるデータベースです。メーカー・シリーズ・色番号・色名・用途・透過率から絞り込み、照明を考える入口として使えます。</p>
        <p class="hk-gel-hero-note">色番号・色名・透過率などのメーカー情報と、HATAKITIによる用途分類は分けて管理しています。</p>
    </header>

<?php if ( $selected_record ) : ?>
<?php
$detail_hex = ! empty( $selected_record['reference_hex'] ) ? $selected_record['reference_hex'] : '';
$detail_rgb = ! empty( $selected_record['reference_rgb'] ) ? implode( ' / ', $selected_record['reference_rgb'] ) : '未登録';
$detail_uses = isset( $selected_record['uses'] ) && is_array( $selected_record['uses'] ) ? $selected_record['uses'] : array();
$detail_source = $selected_record['detail_url'] ?? '';
?>
    <section class="hk-section hk-gel-detail">
        <div class="hk-gel-breadcrumb"><a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/filters/' ) ); ?>">← ゼラ色見本一覧</a></div>
        <div class="hk-gel-detail-top">
            <div class="hk-gel-detail-swatch <?php echo $detail_hex ? '' : 'is-missing'; ?>"<?php echo $detail_hex ? ' style="--hk-gel-color:' . esc_attr( $detail_hex ) . '"' : ''; ?>>
                <?php if ( $detail_hex ) : ?>
                    <span>参考表示色</span>
                    <strong><?php echo esc_html( $detail_hex ); ?></strong>
                <?php else : ?>
                    <span>参考表示色<br>未登録</span>
                <?php endif; ?>
            </div>
            <div class="hk-gel-detail-intro">
                <p class="hk-textbook-kicker">COLOR DETAIL</p>
                <h2><?php echo esc_html( trim( ( $selected_record['color_number'] ?? '' ) . ' ' . ( $selected_record['color_name'] ?? '' ) ) ); ?></h2>
                <p class="hk-gel-detail-brand"><?php echo esc_html( trim( ( $selected_record['manufacturer'] ?? '' ) . ' / ' . ( $selected_record['series'] ?? '' ) ) ); ?></p>
                <p><?php echo esc_html( $selected_record['notes'] ?? '' ); ?></p>
                <div class="hk-gel-detail-warning"><strong>画面上の色について</strong><br>この色見本はRGBによる参考表示です。実際のゼラを通した光の色とは異なります。</div>
            </div>
        </div>

        <div class="hk-gel-detail-values">
            <div><span>色番号</span><strong><?php echo esc_html( $selected_record['color_number'] ?? '—' ); ?></strong></div>
            <div><span>色名</span><strong><?php echo esc_html( $selected_record['color_name'] ?? '—' ); ?></strong></div>
            <div><span>透過率</span><strong><?php echo esc_html( $selected_record['transmission_display'] ?? '未登録' ); ?></strong></div>
            <div><span>RGB</span><strong><?php echo esc_html( $detail_rgb ); ?></strong></div>
            <div><span>HEX</span><strong><?php echo esc_html( $detail_hex ?: '未登録' ); ?></strong></div>
        </div>

        <div class="hk-gel-detail-columns">
            <div>
                <h3>用途</h3>
                <?php if ( $detail_uses ) : ?>
                    <div class="hk-gel-tags"><?php foreach ( $detail_uses as $use ) : ?><span><?php echo esc_html( $use ); ?></span><?php endforeach; ?></div>
                <?php else : ?>
                    <p>HATAKITI用途分類は未登録です。</p>
                <?php endif; ?>
            </div>
            <div>
                <h3>メーカー情報</h3>
                <p>メーカー：<?php echo esc_html( $selected_record['manufacturer'] ?? '—' ); ?></p>
                <p>シリーズ：<?php echo esc_html( $selected_record['series'] ?? '—' ); ?></p>
                <p>透過率：<?php echo esc_html( $selected_record['transmission_display'] ?? '未登録' ); ?></p>
            </div>
            <div>
                <h3>出典</h3>
                <p>参考表示色：<?php echo esc_html( $selected_record['reference_color_source'] ?? '未登録' ); ?></p>
                <?php if ( ! empty( $selected_record['reference_color_method'] ) ) : ?><p>方法：<?php echo esc_html( $selected_record['reference_color_method'] ); ?></p><?php endif; ?>
                <?php if ( $detail_source ) : ?><p><a href="<?php echo esc_url( $detail_source ); ?>" target="_blank" rel="noopener">メーカー資料を見る ↗</a></p><?php endif; ?>
            </div>
        </div>

        <div class="hk-gel-detail-learning">
            <h3>この色から照明を考える</h3>
            <p>色見本を選んだら、次は「どこに・何を・どんな時間や印象で見せるか」を考えてみましょう。照明の器具、方向、広がり、明るさと組み合わせることで、同じ色でも見え方は変わります。</p>
            <a class="hk-gel-cta" href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">照明の基本を読む →</a>
        </div>
    </section>

<?php elseif ( $detail_id ) : ?>
    <section class="hk-section">
        <div class="hk-gel-empty">
            <h2>色見本が見つかりません</h2>
            <p>指定された色番号は現在のデータベースに登録されていません。</p>
            <a class="hk-gel-cta" href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/filters/' ) ); ?>">色見本一覧へ →</a>
        </div>
    </section>
<?php else : ?>

    <section class="hk-section hk-gel-notice">
        <div class="hk-gel-notice-inner">
            <strong>まず知っておきたいこと</strong>
            <p>メーカーが違えば、同じ番号でも同じ色とは限りません。検索ではメーカー・シリーズ・色番号をセットで扱います。</p>
            <p>透過率はメーカー資料の値をそのまま登録することを基本とし、HATAKITI側で測定値を作りません。測定条件が異なる値を単純比較しないよう、詳細ページでは出典も確認できます。</p>
            <p class="hk-gel-caution">画面の色は実物のゼラの代わりにはなりません。最終的な色判断には実物での確認をおすすめします。</p>
        </div>
    </section>

    <section class="hk-section" id="gel-search">
        <div class="hk-section-head">
            <h2>色を探す</h2>
            <p>複数の条件を組み合わせると、条件をすべて満たす色に絞り込まれます。</p>
        </div>

        <div class="hk-gel-filter-panel">
            <div class="hk-gel-filter-group">
                <h3>メーカー</h3>
                <div class="hk-gel-check-grid" id="hk-gel-manufacturers">
                    <?php foreach ( $manufacturer_options as $manufacturer ) : ?>
                        <label><input type="checkbox" value="<?php echo esc_attr( $manufacturer ); ?>"> <?php echo esc_html( $manufacturer ); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hk-gel-filter-group">
                <h3>シリーズ</h3>
                <div class="hk-gel-check-grid" id="hk-gel-series">
                    <?php foreach ( $series_options as $series_name ) : ?>
                        <label><input type="checkbox" value="<?php echo esc_attr( $series_name ); ?>"> <?php echo esc_html( $series_name ); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hk-gel-filter-fields">
                <label>色番号
                    <input id="hk-gel-number" type="search" placeholder="例：26 / 2xx">
                    <small>正式なメーカー表記を保持し、完全一致または前方一致で検索します。</small>
                </label>
                <label>色名
                    <input id="hk-gel-name" type="search" placeholder="例：Red / Blue / Amber">
                    <small>部分一致。日本語名は登録された検索語がある場合に対応します。</small>
                </label>
            </div>

            <div class="hk-gel-filter-group">
                <h3>用途</h3>
                <div class="hk-gel-check-grid hk-gel-use-grid" id="hk-gel-uses">
                    <?php foreach ( $uses as $use ) : ?>
                        <label><input type="checkbox" value="<?php echo esc_attr( $use ); ?>"> <?php echo esc_html( $use ); ?></label>
                    <?php endforeach; ?>
                </div>
                <small class="hk-gel-filter-help">複数選択した用途は、すべて登録されている色を表示します。</small>
            </div>

            <div class="hk-gel-transmission">
                <h3>透過率</h3>
                <div class="hk-gel-range-values">
                    <label>最小 <input id="hk-gel-min" type="number" min="0" max="100" step="0.1" value="0"> %</label>
                    <label>最大 <input id="hk-gel-max" type="number" min="0" max="100" step="0.1" value="100"> %</label>
                </div>
                <div class="hk-gel-range-sliders">
                    <input id="hk-gel-min-range" type="range" min="0" max="100" step="0.1" value="0" aria-label="透過率最小">
                    <input id="hk-gel-max-range" type="range" min="0" max="100" step="0.1" value="100" aria-label="透過率最大">
                </div>
                <div class="hk-gel-range-labels"><span>0%</span><span>100%</span></div>
            </div>

            <div class="hk-gel-actions">
                <button type="button" class="hk-gel-button secondary" id="hk-gel-clear">条件をクリア</button>
                <button type="button" class="hk-gel-button primary" id="hk-gel-search-button">色を探す</button>
            </div>
        </div>
    </section>

    <section class="hk-section hk-gel-purpose">
        <div class="hk-section-head">
            <h2>目的から探す</h2>
            <p>「何色か」ではなく、「どんな光を作りたいか」から入るための入口です。</p>
        </div>
        <div class="hk-gel-purpose-grid">
            <div>
                <h3>何を照らしたい？</h3>
                <div class="hk-gel-purpose-buttons" data-purpose-group="target">
                    <button type="button" data-use="人物">人物</button>
                    <button type="button" data-use="顔・肌">顔・肌</button>
                    <button type="button" data-use="背景">背景</button>
                    <button type="button" data-use="空間">空間</button>
                </div>
            </div>
            <div>
                <h3>どんな時間？</h3>
                <div class="hk-gel-purpose-buttons" data-purpose-group="time">
                    <button type="button" data-use="朝・昼">朝・昼</button>
                    <button type="button" data-use="夕景">夕方</button>
                    <button type="button" data-use="夜">夜</button>
                    <button type="button" data-use="月明かり">深夜・月明かり</button>
                    <button type="button" data-use="日光">日光</button>
                </div>
            </div>
            <div>
                <h3>どんな印象？</h3>
                <div class="hk-gel-purpose-buttons" data-purpose-group="mood">
                    <button type="button" data-use="暖色系の演出">暖かい</button>
                    <button type="button" data-use="寒色系の演出">冷たい</button>
                    <button type="button" data-use="心理表現">不安・心理表現</button>
                    <button type="button" data-use="ファンタジー">幻想的</button>
                    <button type="button" data-use="特殊効果">特殊効果</button>
                </div>
            </div>
        </div>
        <p class="hk-filter-note">目的検索は、登録済みのHATAKITI用途タグを入口として使います。用途タグが未登録の色を、推測で分類することはありません。</p>
    </section>

    <section class="hk-section">
        <div class="hk-gel-result-head">
            <div class="hk-section-head"><h2>検索結果</h2></div>
            <div class="hk-gel-result-controls">
                <span id="hk-gel-count">0件</span>
                <label>並び順
                    <select id="hk-gel-sort">
                        <option value="recommended">おすすめ</option>
                        <option value="number">色番号順</option>
                        <option value="name">色名順</option>
                        <option value="transmission_desc">透過率が高い順</option>
                        <option value="transmission_asc">透過率が低い順</option>
                        <option value="manufacturer">メーカー順</option>
                    </select>
                </label>
            </div>
        </div>
        <div id="hk-gel-results" class="hk-gel-grid"></div>
        <div id="hk-gel-empty" class="hk-gel-empty" hidden>
            <h3>条件に一致する色見本はありません。</h3>
            <p>メーカー・用途・透過率などの条件を少し緩めてみてください。</p>
        </div>
        <div class="hk-gel-result-foot">
            <span id="hk-gel-page-info"></span>
            <div class="hk-gel-pagination" id="hk-gel-pagination"></div>
        </div>
    </section>

    <section class="hk-section hk-gel-learning">
        <div class="hk-section-head">
            <h2>色見本から演劇を学ぶ</h2>
            <p>色を選んで終わりではなく、その色を「何のために使うか」まで考えられるようにします。</p>
        </div>
        <div class="hk-gel-learning-grid">
            <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>">
                <span>01</span><strong>照明の基本</strong><small>光源・方向・広がり・色</small>
            </a>
            <a href="<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/' ) ); ?>#exercise">
                <span>02</span><strong>光で人物を見せる</strong><small>安心・不安・孤独を考える</small>
            </a>
            <div>
                <span>03</span><strong>次の教材へ</strong><small>器具、フォーカス、明かり合わせへ</small>
            </div>
        </div>
    </section>

    <section class="hk-section hk-gel-data-policy">
        <div class="hk-section-head"><h2>データについて</h2></div>
        <div class="hk-panel">
            <p><strong>メーカー情報：</strong>メーカー、シリーズ、色番号、色名、透過率などはメーカー資料を基準に登録します。</p>
            <p><strong>参考表示色：</strong>RGB / HEXは、根拠を確認できたものだけを登録します。実測値ではなく、画面比較のための参考表示です。</p>
            <p><strong>HATAKITI編集情報：</strong>用途や注意点はメーカー記載と区別して扱います。用途が未登録のものを推測で補いません。</p>
        </div>
    </section>

<?php endif; ?>
</main>

<style>
.hk-gel-hero{max-width:860px;margin:64px auto 42px;padding:0 20px;text-align:center}.hk-gel-hero h1{font-family:var(--hk-font-serif);font-size:38px}.hk-gel-hero>p:not(.hk-textbook-kicker){color:var(--hk-fg-dim);line-height:1.9}.hk-gel-hero-note{font-size:12px!important;color:var(--hk-fg-faint)!important}
.hk-gel-notice-inner{border:1px solid var(--hk-border);border-left:4px solid var(--hk-accent-warm);background:var(--hk-bg-card);padding:22px}.hk-gel-notice-inner p{color:var(--hk-fg-dim);line-height:1.9;margin:.7em 0}.hk-gel-caution{font-weight:700;color:var(--hk-fg)!important}
.hk-gel-filter-panel{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:24px}.hk-gel-filter-group{padding-bottom:22px;margin-bottom:22px;border-bottom:1px solid var(--hk-border)}.hk-gel-filter-group h3,.hk-gel-transmission h3{font-family:var(--hk-font-serif);margin:0 0 12px}.hk-gel-check-grid{display:flex;gap:8px;flex-wrap:wrap}.hk-gel-check-grid label{display:inline-flex;align-items:center;gap:5px;padding:8px 11px;border:1px solid var(--hk-border);background:var(--hk-bg-card);font-size:12px;cursor:pointer}.hk-gel-check-grid label:has(input:checked){border-color:var(--hk-accent-warm);color:var(--hk-fg)}.hk-gel-filter-fields{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px}.hk-gel-filter-fields label{font-size:12px;color:var(--hk-fg-dim)}.hk-gel-filter-fields input{display:block;width:100%;box-sizing:border-box;margin-top:7px;padding:12px;border:1px solid var(--hk-border);background:var(--hk-bg-card);color:var(--hk-fg)}.hk-gel-filter-fields small,.hk-gel-filter-help{display:block;margin-top:6px;color:var(--hk-fg-faint);line-height:1.6}.hk-gel-transmission{padding-bottom:22px}.hk-gel-range-values{display:flex;gap:20px;flex-wrap:wrap}.hk-gel-range-values label{font-size:12px}.hk-gel-range-values input{width:90px;margin-left:5px;padding:7px;border:1px solid var(--hk-border);background:var(--hk-bg-card);color:var(--hk-fg)}.hk-gel-range-sliders{position:relative;height:34px;margin-top:12px}.hk-gel-range-sliders input{position:absolute;left:0;top:8px;width:100%;margin:0;pointer-events:none;appearance:none;background:transparent}.hk-gel-range-sliders input::-webkit-slider-thumb{pointer-events:auto;appearance:none;width:18px;height:18px;border-radius:50%;background:var(--hk-accent-warm);border:2px solid var(--hk-bg);cursor:pointer}.hk-gel-range-sliders input::-moz-range-thumb{pointer-events:auto;width:18px;height:18px;border-radius:50%;background:var(--hk-accent-warm);border:2px solid var(--hk-bg);cursor:pointer}.hk-gel-range-sliders:before{content:"";position:absolute;left:0;right:0;top:16px;height:3px;background:var(--hk-border)}.hk-gel-range-labels{display:flex;justify-content:space-between;color:var(--hk-fg-faint);font-size:10px}.hk-gel-actions{display:flex;justify-content:flex-end;gap:10px}.hk-gel-button{padding:12px 22px;border:1px solid var(--hk-border);cursor:pointer}.hk-gel-button.secondary{background:transparent;color:var(--hk-fg)}.hk-gel-button.primary{background:var(--hk-accent-warm);color:#fff;border-color:var(--hk-accent-warm)}
.hk-gel-purpose-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.hk-gel-purpose-grid>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:20px}.hk-gel-purpose-grid h3{font-family:var(--hk-font-serif);margin-top:0}.hk-gel-purpose-buttons{display:flex;flex-wrap:wrap;gap:7px}.hk-gel-purpose-buttons button{padding:8px 10px;border:1px solid var(--hk-border);background:var(--hk-bg-card);color:var(--hk-fg);cursor:pointer;font-size:12px}.hk-gel-purpose-buttons button:hover,.hk-gel-purpose-buttons button.is-active{border-color:var(--hk-accent-warm);color:var(--hk-accent-warm)}
.hk-filter-note{font-size:13px;color:var(--hk-fg-dim);line-height:1.8}.hk-gel-result-head{display:flex;justify-content:space-between;align-items:end;gap:16px}.hk-gel-result-controls{display:flex;align-items:center;gap:14px;color:var(--hk-fg-dim);font-size:13px}.hk-gel-result-controls select{margin-left:6px;padding:8px;border:1px solid var(--hk-border);background:var(--hk-bg-card);color:var(--hk-fg)}.hk-gel-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.hk-gel-card{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);overflow:hidden;transition:border-color .15s,transform .15s}.hk-gel-card:hover{border-color:var(--hk-accent-warm);transform:translateY(-2px)}.hk-gel-card-link{display:block;color:inherit;text-decoration:none}.hk-gel-swatch{height:190px;background:var(--hk-gel-color);display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.55)}.hk-gel-swatch.light{color:#222;text-shadow:none}.hk-gel-swatch span{font-size:11px;letter-spacing:.08em}.hk-gel-swatch strong{font-size:21px;margin-top:6px}.hk-gel-swatch small{margin-top:6px;font-size:10px}.hk-gel-swatch-missing{background:repeating-linear-gradient(135deg,var(--hk-bg-card),var(--hk-bg-card) 10px,var(--hk-bg-elevated) 10px,var(--hk-bg-elevated) 20px);color:var(--hk-fg-dim);text-shadow:none;text-align:center}.hk-gel-meta{padding:18px}.hk-gel-brand{font-size:11px;color:var(--hk-accent-warm)}.hk-gel-meta h3{font-family:var(--hk-font-serif);margin:5px 0 12px}.hk-gel-meta dl{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:0}.hk-gel-meta dl div{border-top:1px solid var(--hk-border);padding-top:8px}.hk-gel-meta dt{font-size:10px;color:var(--hk-fg-faint)}.hk-gel-meta dd{margin:3px 0 0;font-size:12px}.hk-gel-tags{display:flex;flex-wrap:wrap;gap:5px;margin-top:12px}.hk-gel-tags span{font-size:10px;padding:4px 7px;border:1px solid var(--hk-border);color:var(--hk-fg-dim)}.hk-gel-card-note{font-size:10px;color:var(--hk-fg-faint);margin:12px 0 0}.hk-gel-empty{border:1px dashed var(--hk-border);padding:40px;text-align:center;color:var(--hk-fg-dim)}.hk-gel-empty h2,.hk-gel-empty h3{font-family:var(--hk-font-serif);color:var(--hk-fg)}.hk-gel-result-foot{display:flex;justify-content:space-between;align-items:center;margin-top:20px;color:var(--hk-fg-faint);font-size:12px}.hk-gel-pagination{display:flex;gap:5px}.hk-gel-pagination button{min-width:34px;padding:7px;border:1px solid var(--hk-border);background:var(--hk-bg-card);color:var(--hk-fg);cursor:pointer}.hk-gel-pagination button.is-current{border-color:var(--hk-accent-warm);color:var(--hk-accent-warm)}
.hk-gel-learning-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.hk-gel-learning-grid>a,.hk-gel-learning-grid>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:22px;color:var(--hk-fg)}.hk-gel-learning-grid>a:hover{border-color:var(--hk-accent-warm);text-decoration:none}.hk-gel-learning-grid span{display:block;color:var(--hk-accent-warm);font-size:11px}.hk-gel-learning-grid strong{display:block;font-family:var(--hk-font-serif);margin:7px 0}.hk-gel-learning-grid small{color:var(--hk-fg-dim)}.hk-panel{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:22px}.hk-panel p{color:var(--hk-fg-dim);line-height:1.9}
.hk-gel-detail{border-top:1px solid var(--hk-border)}.hk-gel-breadcrumb{margin-bottom:18px}.hk-gel-breadcrumb a{color:var(--hk-accent-warm)}.hk-gel-detail-top{display:grid;grid-template-columns:minmax(280px,430px) 1fr;gap:30px;align-items:center}.hk-gel-detail-swatch{min-height:330px;background:var(--hk-gel-color);display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.55)}.hk-gel-detail-swatch.is-missing{background:repeating-linear-gradient(135deg,var(--hk-bg-card),var(--hk-bg-card) 12px,var(--hk-bg-elevated) 12px,var(--hk-bg-elevated) 24px);color:var(--hk-fg-dim);text-shadow:none;text-align:center}.hk-gel-detail-swatch strong{font-size:30px;margin-top:8px}.hk-gel-detail-intro h2{font-family:var(--hk-font-serif);font-size:34px}.hk-gel-detail-brand{color:var(--hk-accent-warm)}.hk-gel-detail-intro>p{color:var(--hk-fg-dim);line-height:1.9}.hk-gel-detail-warning{margin-top:18px;padding:14px;border-left:3px solid var(--hk-accent-warm);background:var(--hk-bg-card);color:var(--hk-fg-dim);line-height:1.8}.hk-gel-detail-values{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-top:20px}.hk-gel-detail-values>div,.hk-gel-detail-columns>div{border:1px solid var(--hk-border);background:var(--hk-bg-elevated);padding:17px}.hk-gel-detail-values span{display:block;font-size:10px;color:var(--hk-accent-warm)}.hk-gel-detail-values strong{display:block;margin-top:6px}.hk-gel-detail-columns{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:10px}.hk-gel-detail-columns h3,.hk-gel-detail-learning h3{font-family:var(--hk-font-serif);margin-top:0}.hk-gel-detail-columns p{color:var(--hk-fg-dim);line-height:1.8;font-size:13px}.hk-gel-detail-learning{margin-top:16px;border:1px solid var(--hk-border);background:var(--hk-bg-card);padding:22px}.hk-gel-detail-learning p{color:var(--hk-fg-dim);line-height:1.9}.hk-gel-cta{display:inline-block;margin-top:8px;color:var(--hk-accent-warm)}
@media(max-width:900px){.hk-gel-grid{grid-template-columns:repeat(2,1fr)}.hk-gel-purpose-grid,.hk-gel-learning-grid{grid-template-columns:1fr}.hk-gel-detail-values{grid-template-columns:repeat(3,1fr)}}
@media(max-width:700px){.hk-gel-filter-fields{grid-template-columns:1fr}.hk-gel-grid{grid-template-columns:1fr}.hk-gel-result-head{align-items:flex-start;flex-direction:column}.hk-gel-result-controls{width:100%;justify-content:space-between}.hk-gel-detail-top,.hk-gel-detail-columns{grid-template-columns:1fr}.hk-gel-detail-values{grid-template-columns:repeat(2,1fr)}.hk-gel-detail-swatch{min-height:230px}.hk-gel-actions{justify-content:stretch}.hk-gel-button{flex:1}}
@media(max-width:480px){.hk-gel-detail-values{grid-template-columns:1fr}.hk-gel-result-controls{align-items:flex-start;flex-direction:column}}
</style>

<?php if ( ! $selected_record && ! $detail_id ) : ?>
<script>
(function(){
    const records = <?php echo wp_json_encode( $gel_records, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>;
    const pageSize = 24;
    let currentPage = 1;
    let lastFiltered = [];

    const $ = id => document.getElementById(id);
    const text = value => String(value ?? '').trim().toLowerCase();
    const esc = value => String(value ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));
    const checked = id => Array.from(document.querySelectorAll('#' + id + ' input:checked')).map(input => input.value);
    const getNumber = id => {
        const value = parseFloat($(id).value);
        return Number.isFinite(value) ? value : null;
    };

    function updateSeriesVisibility(){
        const selectedManufacturers = checked('hk-gel-manufacturers');
        document.querySelectorAll('#hk-gel-series label').forEach(label => {
            const seriesName = label.querySelector('input').value;
            const exists = !selectedManufacturers.length || records.some(r => selectedManufacturers.includes(r.manufacturer) && r.series === seriesName);
            label.hidden = !exists;
            if (!exists) label.querySelector('input').checked = false;
        });
    }

    function applyUrl(){
        const params = new URLSearchParams(window.location.search);
        const map = {
            number:'hk-gel-number',
            name:'hk-gel-name',
            min:'hk-gel-min',
            max:'hk-gel-max',
            sort:'hk-gel-sort'
        };
        Object.entries(map).forEach(([key,id]) => {
            if(params.has(key)) $(id).value = params.get(key);
        });
        if(params.has('makers')){
            const values=params.get('makers').split(',').filter(Boolean);
            document.querySelectorAll('#hk-gel-manufacturers input').forEach(x=>x.checked=values.includes(x.value));
        }
        updateSeriesVisibility();
        if(params.has('series')){
            const values=params.get('series').split(',').filter(Boolean);
            document.querySelectorAll('#hk-gel-series input').forEach(x=>x.checked=values.includes(x.value));
        }
        if(params.has('uses')){
            const values=params.get('uses').split(',').filter(Boolean);
            document.querySelectorAll('#hk-gel-uses input').forEach(x=>x.checked=values.includes(x.value));
        }
        const min = getNumber('hk-gel-min') ?? 0;
        const max = getNumber('hk-gel-max') ?? 100;
        $('hk-gel-min-range').value=min;
        $('hk-gel-max-range').value=max;
    }

    function syncUrl(){
        const params=new URLSearchParams();
        const makers=checked('hk-gel-manufacturers');
        const series=checked('hk-gel-series');
        const uses=checked('hk-gel-uses');
        if(makers.length) params.set('makers',makers.join(','));
        if(series.length) params.set('series',series.join(','));
        if($('hk-gel-number').value.trim()) params.set('number',$('hk-gel-number').value.trim());
        if($('hk-gel-name').value.trim()) params.set('name',$('hk-gel-name').value.trim());
        if(getNumber('hk-gel-min') !== null && getNumber('hk-gel-min') !== 0) params.set('min',$('hk-gel-min').value);
        if(getNumber('hk-gel-max') !== null && getNumber('hk-gel-max') !== 100) params.set('max',$('hk-gel-max').value);
        if(uses.length) params.set('uses',uses.join(','));
        if($('hk-gel-sort').value !== 'recommended') params.set('sort',$('hk-gel-sort').value);
        history.replaceState(null,'',window.location.pathname+(params.toString()?'?'+params.toString():''));
    }

    function numberMatches(value, query){
        if(!query) return true;
        const source=String(value ?? '').trim().toLowerCase();
        const q=query.toLowerCase();
        return source===q || source.startsWith(q);
    }

    function textMatches(value, query){
        return !query || text(value).includes(query);
    }

    function score(record, filters){
        let score=0;
        if(filters.number){
            const n=text(record.color_number);
            if(n===filters.number) score+=100;
            else if(n.startsWith(filters.number)) score+=50;
        }
        if(filters.name){
            const n=text(record.color_name);
            if(n===filters.name) score+=80;
            else if(n.startsWith(filters.name)) score+=40;
            else if(n.includes(filters.name)) score+=20;
        }
        if(filters.uses.length) score += filters.uses.filter(u=>(record.uses||[]).includes(u)).length*15;
        if(filters.min!==null || filters.max!==null){
            const t=Number(record.transmission);
            if(Number.isFinite(t)){
                const target=((filters.min??0)+(filters.max??100))/2;
                score += Math.max(0,20-Math.abs(t-target)/5);
            }
        }
        return score;
    }

    function filterRecords(){
        const makers=checked('hk-gel-manufacturers');
        const series=checked('hk-gel-series');
        const uses=checked('hk-gel-uses');
        const number=text($('hk-gel-number').value);
        const name=text($('hk-gel-name').value);
        const min=getNumber('hk-gel-min');
        const max=getNumber('hk-gel-max');
        const filters={makers,series,uses,number,name,min,max};

        let list=records.filter(record=>{
            if(makers.length && !makers.includes(record.manufacturer)) return false;
            if(series.length && !series.includes(record.series)) return false;
            if(!numberMatches(record.color_number,number)) return false;
            if(!textMatches(record.color_name,name)) return false;

            const t=Number(record.transmission);
            if(min!==null && (!Number.isFinite(t) || t<min)) return false;
            if(max!==null && (!Number.isFinite(t) || t>max)) return false;

            if(uses.length && !uses.every(use=>(record.uses||[]).includes(use))) return false;
            return true;
        });

        const sort=$('hk-gel-sort').value;
        const collator=new Intl.Collator('ja',{numeric:true,sensitivity:'base'});
        list.sort((a,b)=>{
            if(sort==='recommended') return score(b,filters)-score(a,filters) || collator.compare(String(a.color_number||''),String(b.color_number||''));
            if(sort==='number') return collator.compare(String(a.color_number||''),String(b.color_number||''));
            if(sort==='name') return collator.compare(String(a.color_name||''),String(b.color_name||''));
            if(sort==='manufacturer') return collator.compare(String(a.manufacturer||''),String(b.manufacturer||'')) || collator.compare(String(a.color_number||''),String(b.color_number||''));
            const av=Number(a.transmission), bv=Number(b.transmission);
            if(!Number.isFinite(av)) return 1;
            if(!Number.isFinite(bv)) return -1;
            return sort==='transmission_desc' ? bv-av : av-bv;
        });
        return list;
    }

    function swatch(record){
        if(!record.reference_hex) return '<div class="hk-gel-swatch hk-gel-swatch-missing"><span>参考表示色<br>未登録</span></div>';
        const rgb=record.reference_rgb ? '<small>RGB '+esc(record.reference_rgb.join(' / '))+'</small>' : '';
        const rgbValues=record.reference_rgb||[255,255,255];
        const luminance=(0.299*rgbValues[0]+0.587*rgbValues[1]+0.114*rgbValues[2]);
        const light=luminance>205?' light':'';
        return '<div class="hk-gel-swatch'+light+'" style="--hk-gel-color:'+esc(record.reference_hex)+'"><span>参考表示色</span><strong>'+esc(record.reference_hex)+'</strong>'+rgb+'</div>';
    }

    function card(record){
        const uses=(record.uses||[]).map(use=>'<span>'+esc(use)+'</span>').join('');
        const href='<?php echo esc_url( home_url( '/theatre-textbook/staff/lighting/filters/' ) ); ?>'+encodeURIComponent(record.id||'')+'/';
        return '<article class="hk-gel-card"><a class="hk-gel-card-link" href="'+href+'">'+swatch(record)+'<div class="hk-gel-meta"><div class="hk-gel-brand">'+esc(record.manufacturer||'')+' / '+esc(record.series||'')+'</div><h3>'+esc(record.color_number||'')+' '+esc(record.color_name||'')+'</h3><dl><div><dt>透過率</dt><dd>'+esc(record.transmission_display||'未登録')+'</dd></div><div><dt>参考色</dt><dd>'+esc(record.reference_hex||'未登録')+'</dd></div></dl><div class="hk-gel-tags">'+uses+'</div><p class="hk-gel-card-note">※表示色はRGBによる参考色です。</p></div></a></article>';
    }

    function renderPagination(totalPages){
        const pagination=$('hk-gel-pagination');
        pagination.innerHTML='';
        if(totalPages<=1) return;
        const add=(label,page,current=false)=>{
            const button=document.createElement('button');
            button.type='button'; button.textContent=label; if(current) button.classList.add('is-current');
            button.addEventListener('click',()=>{currentPage=page;render();window.scrollTo({top:$('gel-search').offsetTop-20,behavior:'smooth'});});
            pagination.appendChild(button);
        };
        const start=Math.max(1,currentPage-2), end=Math.min(totalPages,start+4);
        if(start>1) add('1',1);
        for(let page=start;page<=end;page++) add(String(page),page,page===currentPage);
        if(end<totalPages) add('…',totalPages);
    }

    function render(){
        lastFiltered=filterRecords();
        const total=lastFiltered.length;
        const totalPages=Math.max(1,Math.ceil(total/pageSize));
        currentPage=Math.min(currentPage,totalPages);
        const start=(currentPage-1)*pageSize;
        const visible=lastFiltered.slice(start,start+pageSize);
        $('hk-gel-count').textContent=total+'件';
        $('hk-gel-page-info').textContent=total ? (start+1)+'–'+Math.min(start+pageSize,total)+'件を表示' : '';
        $('hk-gel-results').innerHTML=visible.map(card).join('');
        $('hk-gel-empty').hidden=total!==0;
        renderPagination(totalPages);
        syncUrl();
    }

    function syncRange(source){
        const min=Math.min(parseFloat($('hk-gel-min-range').value),parseFloat($('hk-gel-max-range').value));
        const max=Math.max(parseFloat($('hk-gel-min-range').value),parseFloat($('hk-gel-max-range').value));
        $('hk-gel-min').value=min.toFixed(1).replace('.0','');
        $('hk-gel-max').value=max.toFixed(1).replace('.0','');
        if(source==='min') $('hk-gel-min-range').value=min;
        if(source==='max') $('hk-gel-max-range').value=max;
        currentPage=1;render();
    }

    document.querySelectorAll('#hk-gel-manufacturers input').forEach(input=>input.addEventListener('change',()=>{updateSeriesVisibility();currentPage=1;render();}));
    document.querySelectorAll('#hk-gel-series input,#hk-gel-uses input').forEach(input=>input.addEventListener('change',()=>{currentPage=1;render();}));
    $('hk-gel-number').addEventListener('input',()=>{currentPage=1;render();});
    $('hk-gel-name').addEventListener('input',()=>{currentPage=1;render();});
    $('hk-gel-sort').addEventListener('change',()=>{currentPage=1;render();});
    $('hk-gel-min').addEventListener('input',()=>{$('hk-gel-min-range').value=$('hk-gel-min').value||0;currentPage=1;render();});
    $('hk-gel-max').addEventListener('input',()=>{$('hk-gel-max-range').value=$('hk-gel-max').value||100;currentPage=1;render();});
    $('hk-gel-min-range').addEventListener('input',()=>syncRange('min'));
    $('hk-gel-max-range').addEventListener('input',()=>syncRange('max'));
    $('hk-gel-search-button').addEventListener('click',()=>{currentPage=1;render();});
    $('hk-gel-clear').addEventListener('click',()=>{
        document.querySelectorAll('#hk-gel-manufacturers input,#hk-gel-series input,#hk-gel-uses input').forEach(x=>x.checked=false);
        $('hk-gel-number').value='';$('hk-gel-name').value='';
        $('hk-gel-min').value='0';$('hk-gel-max').value='100';
        $('hk-gel-min-range').value='0';$('hk-gel-max-range').value='100';$('hk-gel-sort').value='recommended';
        updateSeriesVisibility();currentPage=1;render();
    });
    document.querySelectorAll('.hk-gel-purpose-buttons button').forEach(button=>button.addEventListener('click',()=>{
        const use=button.dataset.use;
        const input=document.querySelector('#hk-gel-uses input[value="'+CSS.escape(use)+'"]');
        if(!input) return;
        input.checked=!input.checked;
        button.classList.toggle('is-active',input.checked);
        currentPage=1;render();
        $('gel-search').scrollIntoView({behavior:'smooth',block:'start'});
    }));

    applyUrl();
    document.querySelectorAll('.hk-gel-purpose-buttons button').forEach(button=>{
        const use=button.dataset.use;
        const input=document.querySelector('#hk-gel-uses input[value="'+CSS.escape(use)+'"]');
        button.classList.toggle('is-active',!!input&&input.checked);
    });
    render();
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>
