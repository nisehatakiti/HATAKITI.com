<?php
/**
 * 週刊オカルト新聞 — A4縦・日本語縦書きの新聞紙面PDFを自動組版する。
 *
 * Web版（single-occult_weekly.php）とは目的が異なる別レイアウト。既存の
 * articles_json / editorial_summary / news_item_ids などのデータ構造は
 * 一切変更せず、そのまま読み取って紙面に流し込むだけ。
 *
 * この環境（ConoHa共有ホスティング、root権限なし、Node/Chromium/
 * LibreOffice/rsvg-convertいずれも利用不可）では、CSS
 * `writing-mode: vertical-rl` を解釈できるレンダラが存在しないため、
 * HTML/CSSベースのPDF変換では縦書き新聞紙面を作れない。そのため
 * TCPDF（vendor/tcpdf、Composer不要のcomposer-free配布）の低レベル
 * 描画API（Cell/Rotate/Transform）の上に、原稿用紙的な文字グリッドで
 * 一文字ずつ配置する自前の縦組みエンジンをこのファイルに実装する。
 *
 * スコープ上の割り切り（報告書に明記）:
 *   - 禁則処理は行頭禁則・行末禁則の主要文字のみ（JIS完全準拠ではない）
 *   - 数字は縦中横（2桁ペアリング）を使わず、桁数によらず1桁ずつ
 *     通常サイズの縦書き文字として並べる（縮小ペアと通常サイズの
 *     混在が半角文字のように見える不具合を避けるため）
 *   - 長音記号(ー)・波ダッシュ(〜)・三点リーダ(…)のみ90度回転、
 *     小書きかな等はそのまま（多くの実際の縦組みでも同様）
 *   - 出典・編集後記・題字まわりは横書き（実際の新聞でもクレジット行や
 *     ロゴまわりは横書きが一般的で、可読性とURL表示の都合を優先）
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HATAKITI_OCCULT_PDF_DIR', HATAKITI_CORE_DIR . 'includes/' );
define( 'HATAKITI_OCCULT_PDF_FONT_DIR', HATAKITI_CORE_DIR . 'assets/fonts/' );
define( 'HATAKITI_OCCULT_PDF_TCPDF_MAIN', HATAKITI_CORE_DIR . 'vendor/tcpdf/tcpdf.php' );

/**
 * 組版コード自体のバージョン。マストヘッド・段組み・余白などPDFの
 * 見た目に関わるロジックを変更するたびに上げる — hatakiti_occult_pdf_
 * cache_key() がこれを含めるため、記事内容（articles_json）が同じ
 * ままでも既存の全キャッシュ済みPDFが次回アクセス時に再生成される。
 *
 * 41: 記事の視覚的な途切れ問題＋左側空白問題 修正指示書。
 *   (1) 出典（横書き1行）を mode==='headline'（＝ブロック内最初の
 *       セグメントか否か）ではなく overflow_body===null（＝この
 *       セグメントで記事が本当に完結したか否か）で描くよう修正。
 *       旧実装は本文が同一ページ内で継続する場合でも出典を本文途中に
 *       描いてしまい、「出典＝記事終了」の合図の直後に続きの本文が
 *       現れる＝記事が途中で切れて見える主因だった（post_id=662 1面で
 *       実データ確認）。
 *   (2) large tier行から生じた空き矩形（body_used_widthベース）が
 *       tier不一致で構造的に常に埋まらなかった不具合を修正
 *       （hatakiti_occult_pdf_space_fill_fallback_tier('large')が
 *       常にnullを返すため）。
 *   (3) left_side_empty_area指標を追加し、PagePlan比較の優先順位へ
 *       組み込み。
 *   (4) space-fill配置（hatakiti_occult_pdf_stack_articles()内）で
 *       実描画がdry-run見積もりと食い違いoverflowした場合に本文が
 *       無警告で失われる経路を防御的に修正（残本文をキューへ差し戻す）。
 */
define( 'HATAKITI_OCCULT_PDF_GENERATOR_VERSION', '41' );

/**
 * マストヘッド（1ページ目最上部）のロゴ画像。「週刊オカルト新聞」の
 * 正式ロゴとして採用された画像をそのまま配置する — PDF側でタイトル
 * 文字列を再入力・再現することはしない。縦横比は元画像（2172×724px、
 * 比率3:1）のまま、高さ基準でスケーリングする。
 */
define( 'HATAKITI_OCCULT_PDF_LOGO_PATH', HATAKITI_CORE_DIR . 'assets/images/occult-weekly-logo.png' );
define( 'HATAKITI_OCCULT_PDF_LOGO_ASPECT', 3.0 );
define( 'HATAKITI_OCCULT_PDF_LOGO_HEIGHT_MM', 34.0 );

/**
 * 週刊オカルト新聞の正式創刊日（月曜日固定）。この日付を基準に、
 * 号の発行日（hatakiti_occult_issue_date）がどの週に属するかを
 * 逆算し、通算号数を決定論的に算出する（「今日の日付」や「最新投稿」
 * からの推測ではなく、この固定値だけを根拠にする）。同じ号を何度PDF
 * 再生成しても常に同じ号数になることは、この値が変わらない限り保証
 * される。
 *
 * この日付より前の週に属する号（articles_json準備期間のテスト投稿含む）
 * は正式な通算号数の対象外として扱う
 * （hatakiti_occult_pdf_compute_issue_number()がnullを返す）。
 */
define( 'HATAKITI_OCCULT_WEEKLY_LAUNCH_DATE', '2026-09-07' );

/**
 * 本文「1段あたりの最大文字数」。今回の組版方式の中心定数（指示書§1）
 * — tier・記事・文字量によらず、1つの縦書き列（段）はこの文字数を
 * 超えない。hatakiti_occult_pdf_column_capacity() がこれを実際の描画
 * 時の上限としてハード制約するため、「箱が高いほど1列に詰め込める文字
 * 数が増える」という、段の高さが不揃いになる直接の原因を断つ。
 */
define( 'HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN', 20 );

/**
 * 「本文1段」＝HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN文字ぶんの物理高さ
 * （mm）を計算する。本文フォントサイズは全tier共通の
 * HATAKITI_OCCULT_PDF_BODY_FONT_PTを使う（指示書§13）ため、この関数は
 * 実質どのtierを渡しても同じ値を返す — 引数の$tierはlayout_constants()
 * から本文フォントサイズを引くための入口として残しているだけで、
 * 「tierによって1段の高さが変わる」という意味ではない。
 *
 * 記事の箱の本文部分は、続きも含め常にこの1段ぶんの高さちょうどになる
 * — 段を複数個積んで1つの箱にする（＝1箱が2段分3段分の高さを持つ）こと
 * はしない。記事がこの1段に収まりきらなければ、続きは同じ高さの次の箱
 * （罫線で区切られた次の段）へ送る（指示書§2/§5/§6）。+1文字ぶん余裕を
 * 持たせるのは、hatakiti_occult_pdf_column_capacity()側の禁則処理向け
 * 安全マージン（floor(...)−1）と辻褄を合わせ、この高さで実際に描画する
 * と必ずちょうどHATAKITI_OCCULT_PDF_CHARS_PER_COLUMN文字が上限になる
 * ようにするため。
 */
function hatakiti_occult_pdf_unit_h_mm( $tier ) {
    $fonts  = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $char_h = $fonts['body'] * 0.3528;
    return ( HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN + 1 ) * $char_h + HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM;
}

/**
 * 半角数字→全角数字の表示用マッピング。DB上のarticles_json／headline／
 * bodyは一切書き換えない — PDFへ実際に文字を描画する直前にだけ、この
 * 関数を通して見た目だけ全角化する。縦書き本文側では、桁数判定
 * （連続する半角数字の長さの判定）は必ず変換前の半角文字列に対して
 * 行い、変換後の全角文字はグリフを差し替えて描画するだけに使う
 * （全角文字はASCIIではないためctype_digit()が効かなくなり、変換を
 * 先にやってしまうと桁数判定そのものが壊れる）。
 *
 * ASCII数字・上付き数字・下付き数字はまず固定マップで変換する（上付き・
 * 下付きはUnicode上「10進数字」カテゴリに属さないため、後述の汎用判定
 * では検出できない）。そのうえで、intl拡張が使える環境では
 * IntlChar::charDigitValue()により「Unicode上10進数字に分類される
 * 文字」を汎用的に検出し、まだ全角になっていなければ全角数字へ差し
 * 替える — ASCII・上下付き数字のどちらでもない、想定外の数字互換
 * 文字（他言語の10進数字など）が元記事から紛れ込んでいた場合の最終
 * 防衛ラインとして機能する。intl拡張が無い環境では固定マップのみに
 * フォールバックする。
 */
function hatakiti_occult_pdf_fullwidth_digits( $text ) {
    static $map = array(
        '0' => '０', '1' => '１', '2' => '２', '3' => '３', '4' => '４',
        '5' => '５', '6' => '６', '7' => '７', '8' => '８', '9' => '９',
        // 元記事から取り込んだ本文に上付き数字（脚注番号等）が混じって
        // いることがある。これも通常の全角数字に正規化する。
        '⁰' => '０', '¹' => '１', '²' => '２', '³' => '３', '⁴' => '４',
        '⁵' => '５', '⁶' => '６', '⁷' => '７', '⁸' => '８', '⁹' => '９',
        // 下付き数字（脚注・化学式等由来）も同様に正規化する。
        '₀' => '０', '₁' => '１', '₂' => '２', '₃' => '３', '₄' => '４',
        '₅' => '５', '₆' => '６', '₇' => '７', '₈' => '８', '₉' => '９',
    );
    $text = strtr( (string) $text, $map );

    if ( ! class_exists( 'IntlChar' ) ) {
        return $text;
    }

    static $fw_digits = array( '０', '１', '２', '３', '４', '５', '６', '７', '８', '９' );
    $chars   = mb_str_split( $text, 1, 'UTF-8' );
    $changed = false;
    foreach ( $chars as $i => $ch ) {
        if ( in_array( $ch, $fw_digits, true ) ) {
            continue; // すでに全角数字
        }
        $cp = IntlChar::ord( $ch );
        if ( null === $cp || false === $cp ) {
            continue;
        }
        $val = IntlChar::charDigitValue( $cp );
        if ( null !== $val && $val >= 0 && $val <= 9 ) {
            $chars[ $i ] = $fw_digits[ $val ];
            $changed     = true;
        }
    }
    return $changed ? implode( '', $chars ) : $text;
}

/**
 * 本文の文字サイズ（pt）。tier・記事・空きスペースの都合によらず、
 * PDF全体で唯一のこの値を使う（指示書§13「本文文字サイズは全記事・
 * 全ページ・全段組みで完全に統一する」）。記事を箱に収めるための調整は
 * 段数・段高さ・紙面レイアウト・ページ送りで行い、本文フォントサイズ
 * そのものは変更しない。見出しサイズはtierごとに変えてよい
 * （hatakiti_occult_pdf_layout_constants()のtier_fonts参照、指示書§14）。
 */
define( 'HATAKITI_OCCULT_PDF_BODY_FONT_PT', 9.5 );

/**
 * mm単位の版面定数。すべてA4縦（210×297mm）を前提にする。
 */
function hatakiti_occult_pdf_layout_constants() {
    return array(
        'page_w'          => 210.0,
        'page_h'          => 297.0,
        'margin_l'        => 7.0,
        'margin_r'        => 7.0,
        'margin_t'        => 9.0,
        'margin_b'        => 7.0,
        // ロゴ画像の高さ＋発行日・号数の副題行＋二重罫線＋本文までの
        // 余白。旧テキスト題字（25.0mm）より高くなる分、本文領域は
        // その分だけ狭くなるが、必要ならページ数が増えてよいという
        // 指示書の方針に従う（本文を削って合わせない）。
        'masthead_h'      => HATAKITI_OCCULT_PDF_LOGO_HEIGHT_MM + 15.0,
        'page2_header_h'  => 6.0,  // 2ページ目以降の簡易見出し
        // 見出しサイズは記事の重要度（tier）を表現するためのものとして
        // tierごとに変えるが、本文サイズは3tierとも
        // HATAKITI_OCCULT_PDF_BODY_FONT_PTで統一する（指示書§13/§14）。
        'tier_fonts'      => array(
            'large'  => array( 'headline' => 18.5, 'body' => HATAKITI_OCCULT_PDF_BODY_FONT_PT ),
            'medium' => array( 'headline' => 13.0, 'body' => HATAKITI_OCCULT_PDF_BODY_FONT_PT ),
            'small'  => array( 'headline' => 10.0, 'body' => HATAKITI_OCCULT_PDF_BODY_FONT_PT ),
        ),
    );
}

/**
 * TCPDF本体をロードし、獅子文字（Shippori Mincho）を登録したインスタンスを返す。
 * フォント変換結果（.php/.z）は uploads 配下にキャッシュし、TTF本体は
 * プラグイン同梱のものを毎回参照する（フォント変換はTCPDFが必要時にのみ
 * 行い、変換済みキャッシュがあれば再利用する＝不要な再変換をしない）。
 */
function hatakiti_occult_pdf_new_tcpdf() {
    if ( ! class_exists( 'TCPDF' ) ) {
        require_once HATAKITI_OCCULT_PDF_TCPDF_MAIN;
    }

    $font_cache_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'hatakiti-pdf-fonts/';
    if ( ! is_dir( $font_cache_dir ) ) {
        wp_mkdir_p( $font_cache_dir );
    }

    $regular_key = TCPDF_FONTS::addTTFfont(
        HATAKITI_OCCULT_PDF_FONT_DIR . 'ShipporiMincho-Regular.ttf',
        'TrueTypeUnicode',
        '',
        96,
        $font_cache_dir
    );
    $bold_key = TCPDF_FONTS::addTTFfont(
        HATAKITI_OCCULT_PDF_FONT_DIR . 'ShipporiMincho-Bold.ttf',
        'TrueTypeUnicode',
        '',
        96,
        $font_cache_dir
    );

    $pdf = new TCPDF( 'P', 'mm', 'A4', true, 'UTF-8', false );
    // addTTFfont() writes the converted definition into $font_cache_dir, which
    // is outside TCPDF's own search paths (current dir / K_PATH_FONTS) — hand
    // SetFont() the exact file path via AddFont() so it doesn't need to guess.
    $pdf->AddFont( $regular_key, '', $font_cache_dir . $regular_key . '.php' );
    $pdf->AddFont( $bold_key, '', $font_cache_dir . $bold_key . '.php' );
    $pdf->setPrintHeader( false );
    $pdf->setPrintFooter( false );
    $pdf->SetAutoPageBreak( false, 0 );
    $pdf->SetMargins( 0, 0, 0, true );
    $pdf->setCellPaddings( 0, 0, 0, 0 );
    $pdf->setCellMargins( 0, 0, 0, 0 );
    $pdf->SetCreator( 'HATAKITI.com' );
    $pdf->SetAuthor( 'HATAKITI.com' );
    $pdf->SetFontSubsetting( true );

    return array( $pdf, $regular_key, $bold_key );
}

/**
 * 数字は桁数によらず縦中横（2桁ペアリング）を使わず1桁ずつ通常サイズの
 * 縦書き文字として並べる＋回転文字（ー〜…）を考慮しつつ、段落
 * （\n\n区切り）ごとに「1文字ぶんの描画単位」の配列へ分解する。
 *
 * @return array 各要素は段落＝unitの配列。unit = array('type'=>'char'|'rotate'|'punct'|'small_kana', 'ch'=>string)
 */
function hatakiti_occult_pdf_build_units( $text ) {
    $text       = (string) $text;
    $text       = str_replace( "\r\n", "\n", $text );
    $paragraphs = preg_split( '/\n{2,}|\n/u', $text );

    // 縦書きで90度回転させる文字。長音記号・波ダッシュ・三点リーダー
    // （既存）に加え、全角ダッシュ類と主要な括弧類（開き・閉じとも同じ
    // 角度で回転させる — 開き括弧は下向きに開き、閉じ括弧は上向きに
    // 開く形になり、縦書きとして自然な向きになる）。！？は回転させない
    // （縦書きでもそのまま自然に見えるため）。
    $rotate_chars = array(
        'ー', '〜', '～', '…', '‥', '―', '—', '‐',
        '「', '」', '『', '』', '（', '）', '【', '】',
        '［', '］', '〈', '〉', '《', '》', '〔', '〕', '｛', '｝',
    );

    // 読点・句点（全角のカンマ・ピリオド含む）は、通常の文字と同じ
    // 大きさ・中央揃えで描くと縦書きの列の中で浮いて見えるため、
    // 専用の'punct'タイプとして小さめ・セル右上寄りに描く
    // （hatakiti_occult_pdf_layout_and_draw_columns()のdraw_unit参照）。
    $punct_small_chars = array( '、', '。', '，', '．' );

    // 小書き仮名（捨て仮名）。中央揃えのまま通常文字と同じ大きさで描くと
    // 縦書きの列の中で不自然に見えるため、専用の'small_kana'タイプとし、
    // 通常文字よりわずかに小さく・セル右寄りに描く（指示書「小文字が
    // 中央に寄りすぎず、縦書きとして自然な右寄せ補正を行う」）。
    $small_kana_chars = array(
        'ぁ', 'ぃ', 'ぅ', 'ぇ', 'ぉ', 'っ', 'ゃ', 'ゅ', 'ょ',
        'ァ', 'ィ', 'ゥ', 'ェ', 'ォ', 'ッ', 'ャ', 'ュ', 'ョ',
    );

    $result = array();
    foreach ( $paragraphs as $para ) {
        $para = trim( $para );
        if ( '' === $para ) {
            continue;
        }
        $chars = mb_str_split( $para, 1, 'UTF-8' );
        $units = array();
        $i     = 0;
        $n     = count( $chars );
        while ( $i < $n ) {
            $ch = $chars[ $i ];
            if ( ctype_digit( $ch ) ) {
                // 連続する半角数字の終端を求め、桁数に関わらず1桁ずつ
                // 通常サイズの縦書き文字として配置する（縦中横の2桁
                // ペアリングは使わない — 桁数によって「2桁だけ縮小表示・
                // 残りは通常サイズ」という混在が生じ、数字の一部が半角
                // のように見えてしまうため。実際に「518」「8月22日」
                // 「1980〜90」等で報告された見た目の不具合）。
                $j = $i;
                while ( $j < $n && ctype_digit( $chars[ $j ] ) ) {
                    $j++;
                }
                for ( $k = $i; $k < $j; $k++ ) {
                    $units[] = array( 'type' => 'char', 'ch' => $chars[ $k ] );
                }
                $i = $j;
                continue;
            }
            if ( in_array( $ch, $punct_small_chars, true ) ) {
                $units[] = array( 'type' => 'punct', 'ch' => $ch );
            } elseif ( in_array( $ch, $small_kana_chars, true ) ) {
                $units[] = array( 'type' => 'small_kana', 'ch' => $ch );
            } elseif ( in_array( $ch, $rotate_chars, true ) ) {
                $units[] = array( 'type' => 'rotate', 'ch' => $ch );
            } else {
                $units[] = array( 'type' => 'char', 'ch' => $ch );
            }
            $i++;
        }
        $result[] = $units;
    }
    return $result;
}

/**
 * 段落配列（hatakiti_occult_pdf_build_units の結果）を、右→左に並ぶ
 * 縦組みの列へ配置する。禁則処理（行頭・行末の主要文字）を簡易的に適用する。
 *
 * @param array  $paragraphs  hatakiti_occult_pdf_build_units() の戻り値
 * @param float  $x_right     配置開始位置（一番右の列の右端 mm）
 * @param float  $y_top       列の上端 mm
 * @param float  $col_h_mm    1列に使える高さ mm
 * @param int    $max_columns この呼び出しで使ってよい列数の上限
 * @param float  $font_pt     本文フォントサイズ(pt)
 * @param string $font_key    TCPDFフォントキー
 * @return array array('columns_used'=>int, 'overflow'=>bool, 'remainder'=>array)
 *               remainder は収まりきらなかった段落配列（続きをそのまま次の
 *               呼び出しに渡せる形）
 */
function hatakiti_occult_pdf_layout_and_draw_columns( $pdf, $paragraphs, $x_right, $y_top, $col_h_mm, $max_columns, $font_pt, $font_key ) {
    $cannot_start = array( '、', '。', '，', '．', '・', '：', '；', '？', '！', '」', '』', '）', ')', ']', '｝', '〉', '》', '】', '〕', 'ヽ', 'ヾ' );
    $cannot_end   = array( '「', '『', '（', '(', '[', '{', '【', '〈', '《', '〔' );

    $char_h    = $font_pt * 0.3528;
    $col_pitch = $char_h * 1.08;
    $capacity  = hatakiti_occult_pdf_column_capacity( $col_h_mm, $char_h );

    $pdf->SetFont( $font_key, '', $font_pt );

    $col_index    = 0;
    $slot_in_col  = 0;
    $col_left     = $x_right - $col_pitch;
    $col_top      = $y_top;

    $flush_column_started = false;

    $draw_unit = function ( $unit, $col_left, $y ) use ( $pdf, $col_pitch, $char_h, $font_pt, $font_key ) {
        if ( 'rotate' === $unit['type'] ) {
            // ー・〜／～は回転文字の中でも特に、そのまま中央揃えで
            // 回転させると縦書きの列内でやや左寄りに見えるため、回転の
            // 中心自体をわずかに右へずらす（PDF組版最終微調整指示§1）。
            // 三点リーダーや括弧類など他の回転文字はそのまま（対象外）。
            $rotate_right_adjust = 0.0;
            if ( 'ー' === $unit['ch'] ) {
                $rotate_right_adjust = HATAKITI_OCCULT_PDF_LONG_VOWEL_RIGHT_ADJUST;
            } elseif ( '〜' === $unit['ch'] || '～' === $unit['ch'] ) {
                $rotate_right_adjust = HATAKITI_OCCULT_PDF_WAVE_DASH_RIGHT_ADJUST;
            }
            $cx = $col_left + ( $col_pitch / 2 ) + $rotate_right_adjust;
            $cy = $y + ( $char_h / 2 );
            $pdf->StartTransform();
            $pdf->Rotate( -90, $cx, $cy );
            $pdf->SetXY( $cx - ( $char_h / 2 ), $cy - ( $col_pitch / 2 ) );
            $pdf->Cell( $char_h, $col_pitch, $unit['ch'], 0, 0, 'C' );
            $pdf->StopTransform();
        } elseif ( 'punct' === $unit['type'] ) {
            // 縦書きの読点・句点は、そのマスの「右上」（＝直前＝真上の
            // 文字に寄り添う位置）に一回り小さく配置する。列は
            // [col_left, col_left+col_pitch] の範囲で、col_left側が
            // 左（次に読む列側）、col_left+col_pitch側が右（直前の列
            // 側）— 右上に置くには、この右端（col_left+col_pitchに近い
            // 側）に寄せて右揃え（'R'）で描く必要がある。以前は左端
            // （col_left側）に寄せた左揃え（'L'）になっており、結果と
            // して「右上」ではなく「左上」に見える不具合があった。
            // 列の送り幅（col_pitch/char_h）自体は変えない — 段組みの
            // 高さ計算（capacity等）に影響を与えないための制約。
            // 、。は句読点の中でも特に左寄りに見えるため、既存の右上
            // シフトにさらにわずかな右方向オフセットを加える（，．は
            // 対象外、PDF組版最終微調整指示§1）。
            $punct_right_adjust = ( '、' === $unit['ch'] || '。' === $unit['ch'] ) ? HATAKITI_OCCULT_PDF_PUNCT_RIGHT_ADJUST : 0.0;
            $punct_pt = $font_pt * 0.62;
            $pdf->SetFontSize( $punct_pt );
            $pdf->SetXY( $col_left + ( $col_pitch * 0.10 ) + $punct_right_adjust, $y - ( $char_h * 0.32 ) );
            $pdf->Cell( $col_pitch * 0.86, $char_h * 0.62, $unit['ch'], 0, 0, 'R' );
            $pdf->SetFontSize( $font_pt );
        } elseif ( 'small_kana' === $unit['type'] ) {
            // 小書き仮名（捨て仮名）は、通常文字よりわずかに小さく・
            // セルの右寄り（＝col_left+col_pitchに近い側）に描く —
            // 中央揃えのままだと縦書きの列の中で座りが悪く見えるため
            // （指示書§15「小文字が中央に寄りすぎず、縦書きとして自然な
            // 右寄せ補正」）。読点・句点ほど極端な縮小・上下シフトは
            // しない。
            $small_pt = $font_pt * 0.85;
            $pdf->SetFontSize( $small_pt );
            $pdf->SetXY( $col_left + ( $col_pitch * 0.14 ), $y );
            $pdf->Cell( $col_pitch * 0.82, $char_h, $unit['ch'], 0, 0, 'R' );
            $pdf->SetFontSize( $font_pt );
        } else {
            $ch_display = hatakiti_occult_pdf_fullwidth_digits( $unit['ch'] );
            $pdf->SetXY( $col_left, $y );
            $pdf->Cell( $col_pitch, $char_h, $ch_display, 0, 0, 'C' );
        }
    };

    // すべての段落を1本のフラットな「行アイテム」列に変換する。
    // 各段落の先頭には column-break マーカーを入れる（段落先頭は
    // 新しい列から、字下げ1文字ぶんで始まる — 日本語新聞の一般的な流儀）。
    $flat = array();
    foreach ( $paragraphs as $p_idx => $units ) {
        $flat[] = array( 'break' => true );
        foreach ( $units as $u ) {
            $flat[] = array( 'break' => false, 'unit' => $u );
        }
    }

    $columns_drawn  = 0;
    $pos            = 0;
    $total          = count( $flat );
    $remainder_flat = array();
    $max_slots_used = 0;

    while ( $pos < $total ) {
        if ( $columns_drawn >= $max_columns ) {
            $remainder_flat = array_slice( $flat, $pos );
            break;
        }

        $col_left = $x_right - ( ( $columns_drawn + 1 ) * $col_pitch );
        $slots    = array(); // このコラムに置くunit配列

        // 段落区切りマーカーなら、1文字ぶん字下げしてから続ける
        if ( isset( $flat[ $pos ]['break'] ) && true === $flat[ $pos ]['break'] ) {
            $pos++;
            $slots[] = null; // 空白セル(字下げ)
        }

        while ( count( $slots ) < $capacity && $pos < $total ) {
            if ( isset( $flat[ $pos ]['break'] ) && true === $flat[ $pos ]['break'] ) {
                break; // 次は新しい段落 → このコラムはここで終える
            }
            $slots[] = $flat[ $pos ]['unit'];
            $pos++;
        }

        // 行末禁則：最後が「開き括弧」なら次のコラムへ送り戻す。
        // 括弧類は'rotate'、読点・句点は'punct'、小書き仮名は
        // 'small_kana'扱いになっているため、'char'だけでなくこれらも
        // 対象にする（そうしないと回転／縮小表示させた文字だけ禁則処理
        // が効かなくなってしまう）。
        $kinsoku_types = array( 'char', 'rotate', 'punct', 'small_kana' );
        if ( count( $slots ) > 0 ) {
            $last = end( $slots );
            if ( is_array( $last ) && in_array( $last['type'], $kinsoku_types, true ) && in_array( $last['ch'], $cannot_end, true ) && $pos < $total ) {
                array_pop( $slots );
                $pos--;
            }
        }

        // 行頭禁則：次に置かれる予定の文字が「閉じ約物」なら、
        // このコラムに1文字だけ押し込む（capacity+1まで許容）。
        if ( $pos < $total && ! ( isset( $flat[ $pos ]['break'] ) && $flat[ $pos ]['break'] ) ) {
            $next_unit = $flat[ $pos ]['unit'];
            if ( in_array( $next_unit['type'], $kinsoku_types, true ) && in_array( $next_unit['ch'], $cannot_start, true ) ) {
                $slots[] = $next_unit;
                $pos++;
            }
        }

        // 描画
        $y = $col_top;
        foreach ( $slots as $slot ) {
            if ( null !== $slot ) {
                $draw_unit( $slot, $col_left, $y );
            }
            $y += $char_h;
        }
        // 段落区切りで短く終わる列があり得るため、実際に描画された
        // 「見た目の下端」は最も深く到達した列で決まる（段組み全体の
        // 実高さ＝罫線を置くべき位置の算出に使う）。
        $max_slots_used = max( $max_slots_used, count( $slots ) );

        $columns_drawn++;
    }

    if ( $pos < $total && empty( $remainder_flat ) ) {
        $remainder_flat = array_slice( $flat, $pos );
    }

    // remainder_flat をふたたび段落配列(units配列の配列)に組み直す
    $remainder_paragraphs = array();
    $current               = array();
    foreach ( $remainder_flat as $item ) {
        if ( $item['break'] ) {
            if ( ! empty( $current ) ) {
                $remainder_paragraphs[] = $current;
            }
            $current = array();
        } else {
            $current[] = $item['unit'];
        }
    }
    if ( ! empty( $current ) ) {
        $remainder_paragraphs[] = $current;
    }

    return array(
        'columns_used' => $columns_drawn,
        'col_pitch'    => $col_pitch,
        'overflow'     => ! empty( $remainder_paragraphs ),
        'remainder'    => $remainder_paragraphs,
        // 実際に描画された内容の真の高さ（mm）。段落区切りで途中まで
        // しか埋まらない列があるため、割り当てた$col_h_mmより浅い場合
        // がある — 呼び出し側はこれを使って罫線位置・次の記事の開始
        // 位置を決め、使われなかった分を無駄にしない。
        'content_h'    => $max_slots_used * $char_h,
    );
}

/**
 * 与えられた日付（'Y-m-d'）が属する週の月曜日を、真夜中のUNIXタイム
 * スタンプとして返す。日付文字列として不正な場合はnull。
 */
function hatakiti_occult_pdf_monday_of_week_ts( $date_str ) {
    $ts = strtotime( trim( (string) $date_str ) . ' 00:00:00' );
    if ( false === $ts ) {
        return null;
    }
    $day_of_week = (int) date( 'N', $ts ); // 1=月曜〜7=日曜
    return $ts - ( ( $day_of_week - 1 ) * DAY_IN_SECONDS );
}

/**
 * 号の発行日（hatakiti_occult_issue_date、'Y-m-d'）から、
 * HATAKITI_OCCULT_WEEKLY_LAUNCH_DATE を基準にした通算号数を決定論的に
 * 算出する。「今日の日付」や「最新投稿」からの推測ではなく、発行日が
 * 属する週の月曜日と、創刊日（同じく月曜日）との週数差だけで決まる
 * ため、同じ号を何度再生成しても常に同じ値になる。
 *
 * 創刊日より前の週に属する場合（articles_json準備期間のテスト投稿等）
 * は正式な通算号数の対象外としてnullを返す（指示書§5/§11「正式創刊
 * 以前の投稿は正式号数にカウントしない」）。
 *
 * @return int|null 創刊週なら1（表示は「創刊号」）、以降は2,3,4...
 */
function hatakiti_occult_pdf_compute_issue_number( $issue_date_str ) {
    $issue_monday_ts = hatakiti_occult_pdf_monday_of_week_ts( $issue_date_str );
    if ( null === $issue_monday_ts ) {
        return null;
    }
    $launch_monday_ts = hatakiti_occult_pdf_monday_of_week_ts( HATAKITI_OCCULT_WEEKLY_LAUNCH_DATE );
    if ( null === $launch_monday_ts ) {
        return null;
    }
    $diff_days = (int) round( ( $issue_monday_ts - $launch_monday_ts ) / DAY_IN_SECONDS );
    if ( $diff_days < 0 ) {
        return null;
    }
    return intdiv( $diff_days, 7 ) + 1;
}

/**
 * 号数表示文字列（「創刊号」または「第○号」、全角数字）を返す。
 * $issue_number が null（創刊前の号）なら空文字列。
 */
function hatakiti_occult_pdf_issue_number_label( $issue_number ) {
    if ( null === $issue_number ) {
        return '';
    }
    if ( 1 === $issue_number ) {
        return '創刊号';
    }
    return hatakiti_occult_pdf_fullwidth_digits( '第' . $issue_number . '号' );
}

/**
 * 発行日（'Y-m-d'）を「２０２６年９月７日発行」のような表示用文字列に
 * 変換する（全角数字化込み）。不正な日付文字列ならそのまま返す。
 */
function hatakiti_occult_pdf_format_issue_date_for_display( $date_str ) {
    $ts = strtotime( trim( (string) $date_str ) );
    if ( false === $ts ) {
        return (string) $date_str;
    }
    $text = date( 'Y', $ts ) . '年' . (int) date( 'n', $ts ) . '月' . (int) date( 'j', $ts ) . '日発行';
    return hatakiti_occult_pdf_fullwidth_digits( $text );
}

/**
 * 題字（1ページ目のみ）。「週刊オカルト新聞」を主題字として横書きで大きく
 * 掲載し、号数・発行日・号のサブタイトルを添える。二重罫で区切る。
 *
 * 号数表示は「○月○日号」のように発行日を号名として使わず、
 * HATAKITI_OCCULT_WEEKLY_LAUNCH_DATE を基準にした通算号数
 * （創刊号／第○号）を主役として表示し、発行日は補助情報として添える
 * （PDF「○月○日号」表記廃止／正式創刊号・通算号数管理指示）。
 * 正式創刊前の号（$issue_idはあるがhatakiti_occult_pdf_compute_issue_
 * number()がnullを返す場合）は、既存デザインとの後方互換のため従来
 * 通り $issue_id をそのまま「第{issue_id}号」として表示する。
 */
function hatakiti_occult_pdf_draw_masthead( $pdf, $font_regular, $font_bold, $c, $issue_subtitle, $issue_id, $issue_date ) {
    $top = $c['margin_t'];
    $w   = $c['page_w'] - $c['margin_l'] - $c['margin_r'];

    // 正式ロゴ画像をマストヘッドとして配置する（テキストでの題字再現は
    // 行わない）。縦横比は元画像のまま、高さ基準でスケーリングし、
    // 紙面中央に配置する。
    $logo_h = HATAKITI_OCCULT_PDF_LOGO_HEIGHT_MM;
    $logo_w = $logo_h * HATAKITI_OCCULT_PDF_LOGO_ASPECT;
    $logo_x = $c['margin_l'] + ( $w - $logo_w ) / 2;
    if ( file_exists( HATAKITI_OCCULT_PDF_LOGO_PATH ) ) {
        $pdf->Image( HATAKITI_OCCULT_PDF_LOGO_PATH, $logo_x, $top, $logo_w, $logo_h, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false );
    } else {
        // ロゴ画像が万一見つからない場合の保険（本来発生しない想定）。
        // 紙面が完全な空白にならないよう最小限のテキストにとどめる。
        $pdf->SetFont( $font_bold, '', 24 );
        $pdf->SetXY( $c['margin_l'], $top );
        $pdf->Cell( $w, $logo_h, '週刊オカルト新聞', 0, 0, 'C' );
    }

    $pdf->SetFont( $font_regular, '', 9 );
    $sub_y = $top + $logo_h + 3;
    $left_text  = $issue_subtitle ? hatakiti_occult_pdf_fullwidth_digits( mb_substr( $issue_subtitle, 0, 60 ) ) : '';
    $right_bits   = array();
    $issue_number = hatakiti_occult_pdf_compute_issue_number( $issue_date );
    if ( null !== $issue_number ) {
        $right_bits[] = hatakiti_occult_pdf_issue_number_label( $issue_number );
    } elseif ( $issue_id ) {
        // 正式創刊前（テスト・準備データ）は既存表示のままにする
        // （正式創刊以前の投稿へ号数を遡及付与しない、指示書§11）。
        $right_bits[] = '第' . $issue_id . '号';
    }
    if ( $issue_date ) {
        $right_bits[] = null !== $issue_number
            ? hatakiti_occult_pdf_format_issue_date_for_display( $issue_date )
            : $issue_date . '発行';
    }
    $right_text = hatakiti_occult_pdf_fullwidth_digits( implode( '　', $right_bits ) );

    $pdf->SetXY( $c['margin_l'], $sub_y );
    $pdf->Cell( $w * 0.62, 6, $left_text, 0, 0, 'L' );
    $pdf->SetXY( $c['margin_l'] + $w * 0.62, $sub_y );
    $pdf->Cell( $w * 0.38, 6, $right_text, 0, 0, 'R' );

    $rule_y = $sub_y + 8;
    $pdf->SetLineWidth( 0.9 );
    $pdf->Line( $c['margin_l'], $rule_y, $c['page_w'] - $c['margin_r'], $rule_y );
    $pdf->SetLineWidth( 0.2 );
    $pdf->Line( $c['margin_l'], $rule_y + 1.3, $c['page_w'] - $c['margin_r'], $rule_y + 1.3 );
}

function hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c, $page_no = 2 ) {
    $pdf->SetFont( $font_regular, '', 9 );
    $pdf->SetXY( $c['margin_l'], $c['margin_t'] );
    $pdf->Cell( $c['page_w'] - $c['margin_l'] - $c['margin_r'], 5, hatakiti_occult_pdf_fullwidth_digits( '週刊オカルト新聞　（第' . (int) $page_no . '面）' ), 0, 0, 'L' );
    $rule_y = $c['margin_t'] + $c['page2_header_h'] - 2;
    $pdf->SetLineWidth( 0.3 );
    $pdf->Line( $c['margin_l'], $rule_y, $c['page_w'] - $c['margin_r'], $rule_y );
}

/**
 * 情報源（出典）表記。記事1本ぶんの割当幅は狭いことが多いため、
 * ここでは「情報源：媒体名・媒体名」という媒体名だけの短い1行に留める
 * （記事間の重なりを避けるため）。元記事タイトル・URLの詳細は紙面末尾の
 * 出典一覧（hatakiti_occult_pdf_draw_footer）にすべて掲載し、そこで
 * データを失わない。最初のURLだけ、この短い行にもクリックリンクとして
 * 重ねておく。
 */
function hatakiti_occult_pdf_source_lines( $news_item_ids ) {
    $names     = array();
    $first_url = '';
    foreach ( (array) $news_item_ids as $item_id ) {
        $name = get_post_meta( $item_id, 'hatakiti_occult_source_name', true );
        $url  = get_post_meta( $item_id, 'hatakiti_occult_original_url', true );
        if ( $name && ! in_array( $name, $names, true ) ) {
            $names[] = $name;
        }
        if ( $url && ! $first_url ) {
            $first_url = $url;
        }
    }
    if ( ! $names ) {
        return array();
    }
    return array(
        array(
            'text' => '情報源：' . implode( '・', $names ),
            'url'  => $first_url,
        ),
    );
}

/**
 * 段落配列(units)の総ユニット数（段落区切りの字下げぶんも1として含む）。
 */
function hatakiti_occult_pdf_count_units( $body_text ) {
    $paragraphs = hatakiti_occult_pdf_build_units( $body_text );
    $total = 0;
    foreach ( $paragraphs as $p ) {
        $total += count( $p ) + 1;
    }
    return array( $total, $paragraphs );
}


/**
 * 版面マージン定数。
 *
 *   BODY_BOTTOM_MARGIN_MM: 各列の最終文字と「その列に許された高さの
 *     下端」との間に必ず確保する余白。禁則処理で1文字ぶん押し込まれる
 *     最悪ケースでも、この余白ぶんは絶対に消費されない（下記
 *     hatakiti_occult_pdf_column_capacity() が担保する）。
 *   NORMAL_HEAD_GAP_MM: 見出しと本文の間の余白（続きの箱は見出しを
 *     再表示しないため、この余白も使わない）。
 *   ROW_GAP_MM: 縦に積んだ記事ボックス間の区切り線の余白（線の上下
 *     それぞれに ROW_GAP_MM/2 ずつ）。
 *
 * 記事の開始可否は「本文1段ぶんの箱（新規記事なら見出し＋間隔＋出典
 * ストリップも含む）が残り高さに収まるか」で判定する（指示書§4/§18）。
 */
define( 'HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM', 2.2 );
define( 'HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM', 2.2 );
define( 'HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM', 4.2 );
define( 'HATAKITI_OCCULT_PDF_ROW_GAP_MM', 3.2 );

/**
 * 横並び段組み（1行に複数記事を並べる）に関する定数。
 *
 *   ROW_COL_GAP_MM     : 横並びにした記事どうしの間隔（罫線を含む）。
 *   MIN_COL_W_MM        : 1記事あたりの列幅がこれを下回るなら横並びに
 *     しない安全弁（縦書き複数列を組むための最低限の幅を確保する）。
 */
define( 'HATAKITI_OCCULT_PDF_ROW_COL_GAP_MM', 2.0 );
define( 'HATAKITI_OCCULT_PDF_MIN_COL_W_MM', 55.0 );

/**
 * 次段階のレイアウト改善指示書「後続記事の探索ルール」— 先頭記事
 * （群）がこのページに完全に収まらない場合に、代わりに配置できる
 * 記事を探して良い先読み件数の上限。「記事順序を大きくシャッフル
 * することは避ける」ため、無制限にはしない。
 */
define( 'HATAKITI_OCCULT_PDF_LOOKAHEAD_WINDOW', 6 );

/**
 * ページ途中空白削減指示書§1〜§4 — バックフィルで列ごとに独立して
 * 候補を探すと、「片方の列は同tier候補が見つかって大きく延長できる
 * が、もう片方の列は（列幅が狭い等で）候補が1つも見つからない」
 * ケースで、延長された列の下に、候補のない列だけが取り残されて
 * 大きな空白矩形になる（ブロック外枠の列区切り線は block_bottom
 * = max(col_bottom) まで引かれるため、空いた列がそのまま可視の
 * 空白として残る）。
 *
 * これを避けるため、「候補が1つも無い列」が存在する状態で、他の列を
 * その候補の無い列の位置よりこの値を超えて延長することは許可しない
 * （フェーズ分割バックフィル、詳細はhatakiti_occult_pdf_stack_articles()
 * 内のコメント参照）。延長を見送られた記事はキューに残り、次のブロック
 * （＝この列を含む現在ブロックが確定したあとの、新しい段組み判定）で
 * 改めて配置候補になる。
 */
define( 'HATAKITI_OCCULT_PDF_BACKFILL_IMBALANCE_GAP_MM', 20.0 );

/**
 * バックフィルで同tierの供給が尽きた列に、隣接tier（medium⇄small）の
 * 記事を詰めることを許可するか。既定はfalse — 実データ検証で、有効化
 * するとmedium列がsmall記事を先食いしてしまい、あとで小tierのブロック
 * に使える記事が不足してページ数がかえって増える逆効果を確認した
 * ため（hatakiti_occult_pdf_fallback_tier()のコメント参照）。
 */
define( 'HATAKITI_OCCULT_PDF_ALLOW_CROSS_TIER_BACKFILL', false );

/**
 * ページ途中空白削減指示書（第2弾）—
 * hatakiti_occult_pdf_choose_block_config_with_lookahead()による
 * 「直近2ブロック分をdry-runでまとめて比較し、ブロック境界そのものを
 * 組み替える」機能を有効にするか。既定はfalse。
 *
 * 実データ9件で検証した結果、ブロック内部空白（列間不均衡）は
 * 局所的に改善する一方、2ブロック先読みだけではその先のページ構成
 * まで見通せず、上流のブロックで選び直した構成が下流のキュー残量を
 * 変えてしまい、実データ9件中2件（#547: 3→4ページ、#662: 6→7ページ）
 * で総ページ数がかえって悪化することを確認した。これは
 * HATAKITI_OCCULT_PDF_ALLOW_CROSS_TIER_BACKFILLや過去に削除した
 * predictive backfill simulationと同じ「局所最適が大域では逆効果」
 * のパターンであり、同じ方針（実装は残すが既定オフ）を踏襲する。
 * trueにする場合は、必ず実データ全件で総ページ数・ページまたぎ・
 * 内部空白を比較してから判断すること。
 */
define( 'HATAKITI_OCCULT_PDF_ALLOW_BLOCK_BOUNDARY_LOOKAHEAD', false );

/**
 * ページ単位探索（Page-Level Layout Search）指示書 —
 * hatakiti_occult_pdf_search_page_plan()を有効にするか。§12
 * 「Feature Flag」は既定falseとしているが、実データ9件（既存5件＋
 * 追加検証用ドラフト4件）で検証した結果、§15の採用基準（総ページ数
 * 悪化ゼロ・記事途中分割ゼロ・warnings NONE・重点ケースでの明確な
 * 視覚改善）をすべて満たしたため、既定trueとして有効化した。詳細は
 * 実装コミットのメッセージを参照。
 */
define( 'HATAKITI_OCCULT_PDF_ALLOW_PAGE_LEVEL_SEARCH', true );

/** 同指示書§4：候補生成の再帰探索で許すRow数の上限。 */
define( 'HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_ROWS', 4 );

/** 同指示書§4：生成する候補PagePlanの総数上限（探索爆発の防止）。 */
define( 'HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_CANDIDATES', 200 );

/**
 * 同指示書§5：small tier専用の均等4列候補で許す最小列幅（mm）。
 * 既存のHATAKITI_OCCULT_PDF_MIN_COL_W_MM(55mm)は4列には適用しない
 * （§5-2）。現行紙面幅196mm・列間ギャップ2mmでは均等4列がちょうど
 * 47.5mmになる。post_id=662のsmall tier記事4件を使い、47.5mmと
 * 45mmを実際にラスタライズして比較した結果、45mmでは見出しが3行に
 * 折り返される列が複数出て窮屈になるのに対し、47.5mmでは全列2行に
 * 収まり可読性が保たれることを確認したため、47.5mmを採用した。
 * なお実データ9件では均等4列候補が実際に採用されたケースは無く
 * （他の構成がより高評価だったため）、この値は上記の強制テストで
 * 検証したものであり、自然採用による目視確認ではない。
 */
define( 'HATAKITI_OCCULT_PDF_SMALL_4COL_MIN_COL_W_MM', 47.5 );

/**
 * 同指示書§7 Priority 3：最大空白矩形の「明確な改善」とみなす絶対値
 * 側のしきい値（mm²）。相対15%改善に加えて、こちらも満たせば採用対象
 * とする（baseline側の矩形が小さい／ゼロに近い場合、相対%基準だけでは
 * 判定できないため）。
 */
define( 'HATAKITI_OCCULT_PDF_PAGE_SEARCH_MIN_RECT_IMPROVEMENT_MM2', 500.0 );

/**
 * 空き矩形拡張指示書§11の安全弁：最大空白矩形やページ末尾空白が
 * 改善しても、列バランス（column_imbalance_score）がBaselineより
 * この値（mm）を超えて悪化する場合は不採用とする。実データで確認
 * された自然な悪化（post=592 3ページ目、+17.8mm、目視で問題なし）
 * より十分大きく、かつ「明らかにおかしい」悪化は防げる値として40mmを
 * 採用した。
 */
define( 'HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_IMBALANCE_REGRESSION_MM', 40.0 );

/**
 * PagePlan探索仕様（Row/Space Fill並列探索）§14-4：累積order_jump
 * （空き矩形への記事先取りによるキュー飛び越し量の合計）がこの値を
 * 超える候補は枝刈りする。LOOKAHEAD_WINDOW×2を初期値とする。
 */
define( 'HATAKITI_OCCULT_PDF_PAGE_SEARCH_ORDER_JUMP_LIMIT', HATAKITI_OCCULT_PDF_LOOKAHEAD_WINDOW * 2 );

/**
 * 同仕様§14-5：Space Fillが連続して発生してよい最大回数
 * （間にRow追加を挟まずに空き矩形へ配置し続けられる回数の上限）。
 * 紙面が不自然になるのを防ぐ安全弁。
 */
define( 'HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_CONSECUTIVE_SPACE_FILLS', 3 );

/**
 * 同仕様§19-3：Space Fillによって実際に埋まる面積（＝記事の高さ×
 * スペース幅）がこの値未満の場合、候補として生成しない
 * （「巨大空白に記事を置いた結果、実質数mm²しか改善しない」ような
 * 無意味な詰め込みを避ける）。
 */
define( 'HATAKITI_OCCULT_PDF_PAGE_SEARCH_MIN_SPACE_FILL_IMPROVEMENT_MM2', 200.0 );

/**
 * hatakiti_occult_pdf_compare_page_plans()で、面積系指標（largest_
 * empty_rect_area等、mm²）を比較する際の「同等」とみなす許容誤差。
 * 実データpost=662 3ページ目で、262.7mm²というノイズレベルの差だけで
 * 段組み切替回数の少ない候補が不採用になる問題を確認したため導入した
 * （長さ系指標と同じ±0.5mmの許容誤差では狭すぎる）。
 */
define( 'HATAKITI_OCCULT_PDF_PAGE_SEARCH_AREA_TIE_TOLERANCE_MM2', 500.0 );

/**
 * ページ充填アルゴリズム改善指示書（4分割・複数ブロック組合せ対応）
 * — hatakiti_occult_pdf_search_multirow_plan()が探索する段（行）の
 * 最大数。「2列→1列→2列」のような3段構成まで許可する。
 */
define( 'HATAKITI_OCCULT_PDF_MAX_PLAN_ROWS', 3 );

/**
 * 縦書き本文中、特定の記号だけがやや左寄りに見える視覚補正のための
 * 右方向オフセット（mm）。通常文字の描画位置・段の高さ計算・列幅
 * 判定など、レイアウト構造には一切影響しない — draw_unit内でその記号
 * を描く直前のX座標にだけ加算する見た目だけの微調整。1文字ぶんの幅
 * （col_pitch、9.5pt本文でおよそ3.6mm）に対して数%〜1割弱程度の値に
 * とどめる（「はっきり動いた」ではなく「他の文字と並べて自然」を目標に、
 * 前回の値より明確に強めた再調整）。記号ごとに別定数にしておき、実際の
 * PDF画像を見ながら数値だけを調整できるようにする。
 */
define( 'HATAKITI_OCCULT_PDF_LONG_VOWEL_RIGHT_ADJUST', 0.30 ); // ー
define( 'HATAKITI_OCCULT_PDF_WAVE_DASH_RIGHT_ADJUST', 0.30 );  // 〜／～
define( 'HATAKITI_OCCULT_PDF_PUNCT_RIGHT_ADJUST', 0.40 );      // 、。

/**
 * 続き記事の冒頭に付ける「継続表示」の高さ（mm）。指示書§7「記事の続きが
 * 読者に明確に分かること」を満たすため、見出しは再表示しない代わりに、
 * 小さな1行で「「元見出しの短縮形」の続き」と表示する（本文と同じ書体・
 * 横書き、見出しより明確に小さいサイズ — 見出しの再現ではなく、あくまで
 * 継続の目印であることを視覚的に示す）。
 */
define( 'HATAKITI_OCCULT_PDF_CONTINUATION_LABEL_H_MM', 4.5 );
define( 'HATAKITI_OCCULT_PDF_CONTINUATION_LABEL_FONT_PT', 8.0 );

/**
 * 与えられた列の高さ(mm)と1文字の高さ(mm)から、実際に配置してよい
 * 文字数（capacity）を返す。HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM
 * ぶんを必ず差し引いてから計算するため、禁則処理で最大1文字押し込まれる
 * 最悪ケースでも、列の下端との間に必ず余白が残る。本文サイズの見積もり
 * （estimate）と実際の描画（draw）の両方がこの同じ関数を通ることで、
 * 見積もりと実描画のずれ（＝罫線と文字が重なる不具合の原因）を防ぐ。
 *
 * HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN を必ずハード上限として適用する
 * — 箱が（複数の段ぶん）高くなっても、1列がその文字数を超えて伸びる
 * ことは物理的に起こらない（指示書§1/§22「本文20文字を超えた段が
 * 存在しないことを確認できるように」）。
 */
function hatakiti_occult_pdf_column_capacity( $col_h_mm, $char_h ) {
    $usable = max( 0, $col_h_mm - HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM );
    $cap    = max( 1, (int) floor( $usable / $char_h ) - 1 );
    return min( $cap, HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN );
}

/**
 * 記事（または続き）の本文部分に必要な高さ（mm）を返す。まだ本文が
 * HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN文字を超えて残っている「途中
 * セグメント」は常に「本文1段」ぶん（hatakiti_occult_pdf_unit_h_mm）の
 * 固定高さそのもの — 記事の文字量によって変えない（指示書§6「途中
 * セグメントは同じ高さの箱を使用する」）。
 *
 * 一方、この箱で記事が完結する「最終セグメント」（残り本文が
 * CHARS_PER_COLUMN文字以下）は、本文が実際に必要とする高さぶんにまで
 * 縮小する — 標準の1段ぶんを丸ごと確保して残りを空白のまま抱え込む
 * ことはしない（指示書§16「最終セグメントに必要な領域だけ使用し、
 * 残りの空白を再び利用可能領域として登録する」）。1段の高さちょうど
 * まで縮小しても、実際に描画される列は依然としてこの高さから逆算
 * される禁則マージン込みの容量に収まる（＝20文字を超えることはない）。
 */
function hatakiti_occult_pdf_body_segment_h( $article, $tier ) {
    $fonts  = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $char_h = $fonts['body'] * 0.3528;
    $body   = (string) ( $article['body'] ?? '' );
    list( $unit_count, ) = hatakiti_occult_pdf_count_units( $body );

    if ( $unit_count > HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN ) {
        return hatakiti_occult_pdf_unit_h_mm( $tier );
    }
    // 最終セグメント：実際の文字数ぶんだけ（+1文字の安全マージンは
    // unit_h_mm()と同じ考え方）。
    return ( $unit_count + 1 ) * $char_h + HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM;
}

/**
 * 見出し（横書き・箱の全幅で折り返し）に必要な高さ(mm)を、実際には
 * 描画せず見積もる。
 */
function hatakiti_occult_pdf_measure_headline_height( $pdf, $font_bold, $font_pt, $text, $box_w ) {
    if ( '' === (string) $text ) {
        return 0.0;
    }
    // 見出しは横書きなので、全角化すると折り返し幅（＝行数）が変わり
    // 得る。実際に描画する文字列（全角化後）で見積もらないと、行数が
    // 増えた場合に見積もりと実描画がずれてしまうため、ここで変換する。
    $text = hatakiti_occult_pdf_fullwidth_digits( (string) $text );
    $pdf->SetFont( $font_bold, '', $font_pt );
    $line_h = $font_pt * 0.3528 * 1.3;
    $h = $pdf->getStringHeight( $box_w, $text );
    return max( $line_h, $h );
}

/**
 * 見出し（または続きラベル）に必要な高さ(mm)を見積もる。ブロック内の
 * 各列の本文開始Y座標を揃えるには、実際に描画する前にブロック内の
 * 全列ぶんの見出し高さが分かっている必要があるため、描画用関数
 * （hatakiti_occult_pdf_draw_article_box）とは別に切り出してある。
 *
 *   - $article が続き記事（_continuation）なら、それがこのブロックの
 *     最初のセグメントであるかどうかに関わらず、続きラベルの高さを返す
 *     — ラベルを表示するかどうか（$is_first_in_block）は呼び出し側が
 *     決めるが、高さの見積もり自体はラベル前提でよい（続き記事が
 *     ブロック内2セグメント目以降になることはなく、続き記事は必ず
 *     ブロックの最初のセグメントとして現れるため — 同一ブロック内の
 *     2セグメント目以降はこの関数を呼ばずheader_h=0を直接使う）。
 *   - 新規記事なら実際の見出し文字列を計測する。
 */
function hatakiti_occult_pdf_measure_first_segment_header_h( $pdf, $font_bold, $article, $tier, $col_w ) {
    if ( ! empty( $article['_continuation'] ) ) {
        return HATAKITI_OCCULT_PDF_CONTINUATION_LABEL_H_MM;
    }
    $fonts    = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $headline = (string) ( $article['headline'] ?? '' );
    return hatakiti_occult_pdf_measure_headline_height( $pdf, $font_bold, $fonts['headline'], $headline, $col_w );
}

/**
 * 1記事（の1セグメントぶん）を描画する。
 *
 * 指示書の核心：Article = 面積であり Article = 1列 ではない。見出しは
 * 横書きで箱の全幅を使って上部に、本文は見出しの下で「箱の全幅を使った
 * 縦書き複数列」として組む。これにより1記事が細い縦ストリップに閉じ
 * こめられることを避ける。
 *
 * 見出し領域の高さ($header_h)と本文開始Y座標は、呼び出し側
 * （hatakiti_occult_pdf_stack_articles）がブロック単位で決めてから渡す
 * — この関数自身は「見出しがこの記事にとって実際に必要な高さ」を
 * 使って本文開始位置を決めたりしない。同一ブロック内の複数列で
 * 見出しの行数が異なっても、本文の開始Y座標（$y + $header_h + gap）が
 * 全列で完全に一致する（追加指示§3〜§6「本文開始位置を揃える」）。
 *
 * @param string $mode 'headline'=新規記事の見出しを描く／
 *                      'label'=ページまたぎ続きの継続ラベルを描く／
 *                      'none'=同一ページ内の同一列直下続き（見出しも
 *                      ラベルも描かず、本文をそのまま$yから続ける）
 * @param float  $header_h このセグメントに割り当てる見出し領域の高さ。
 *                      'headline'/'label'ではブロック内の最大値
 *                      （block_header_h）、'none'では常に0。
 * @return array array('overflow_body'=>array|null, 'bottom_y'=>float)
 */
function hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article, $tier, $x, $y, $w, $header_h, $mode ) {
    $fonts    = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $headline = (string) ( $article['headline'] ?? '' );
    $body     = (string) ( $article['body'] ?? '' );

    if ( 'label' === $mode ) {
        // ページをまたいだ続き — 元の見出しをそのまま再表示するのでは
        // なく、小さな1行の継続表示ラベル「「元見出しの短縮形」の続き」
        // を描く（指示書§2「ページをまたぐ場合の続き表示は維持する」）。
        $label_short = mb_substr( $headline, 0, 16 );
        if ( mb_strlen( $headline ) > 16 ) {
            $label_short .= '…';
        }
        $label_text = hatakiti_occult_pdf_fullwidth_digits( '「' . $label_short . '」の続き' );
        $pdf->SetFont( $font_regular, '', HATAKITI_OCCULT_PDF_CONTINUATION_LABEL_FONT_PT );
        $pdf->SetXY( $x, $y );
        $pdf->Cell( $w, HATAKITI_OCCULT_PDF_CONTINUATION_LABEL_H_MM, $label_text, 0, 0, 'L' );
    } elseif ( 'headline' === $mode && '' !== $headline ) {
        // 表示用に全角化した文字列で折り返し幅を測る・描くの両方を行う
        // — 測定と描画で異なる文字列を使うと折り返し行数がずれ、罫線と
        // 本文が重なる不具合の原因になる（過去に修正した問題と同じ種類）。
        $headline_display = hatakiti_occult_pdf_fullwidth_digits( $headline );
        $head_pt     = $fonts['headline'];
        $head_line_h = $head_pt * 0.3528 * 1.3;
        $pdf->SetFont( $font_bold, '', $head_pt );
        $pdf->SetXY( $x, $y );
        $pdf->MultiCell( $w, $head_line_h, $headline_display, 0, 'L' );
    }
    // 'none'（同一ページ内・同一列の直下続き）は見出しもラベルも描かず、
    // 本文をそのまま連続させる（追加指示§1「不要な続きラベルを廃止」）。

    // 見出し(またはラベル)と本文の間隔は、何か描いた場合のみ設ける。
    // 'none'では見出し領域そのものが無い（$header_h=0）ため、間隔も
    // 置かず本文をこのセグメントの先頭からそのまま続ける（追加指示§1
    // 「不要な続きラベル用の高さや余白も確保しないこと」）。
    $gap      = 'none' === $mode ? 0.0 : HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM;
    $body_top = $y + $header_h + $gap;
    $body_h   = hatakiti_occult_pdf_body_segment_h( $article, $tier );

    $overflow_body    = null;
    $body_used_width  = 0.0; // 実描画占有矩形ベースAvailable Spaces指示書§4：本文が実際に使った幅（右詰め）。
    $body_content_h   = $body_h; // Row内部visual空白指示書§2〜§3：本文の実描画高さ（既定は予約高さと同じ）。
    if ( $body_h > 2.0 ) {
        list( , $body_paragraphs ) = hatakiti_occult_pdf_count_units( $body );

        $body_char_h    = $fonts['body'] * 0.3528;
        $body_col_pitch = $body_char_h * 1.08;
        // 段落の切れ目ごとに新しい列から始める（禁則処理ぶんの余白も
        // 生じる）ため、単純な「総文字数÷1段あたりの文字数」の見積もり
        // では実際に必要な列数を過小評価することがある。過小評価した
        // ぶんで列数を打ち切ると、前の段にまだ描画可能な余地があるのに
        // 本文の末尾が丸ごと次の箱へ送られてしまう（指示書§8「実際の
        // 描画可能領域を計算し、可能なら前段へ収める」）。そこで列数は
        // 幅が物理的に許す上限（$max_cols_by_w）をそのまま使う — 実際の
        // 描画は本文を使い切った時点で自然に停止するため、上限を大きく
        // しても余計な空列が増えることはない。
        $max_cols_by_w  = max( 0, (int) floor( $w / $body_col_pitch ) );
        $cols_to_use    = $max_cols_by_w;

        if ( $cols_to_use < 1 ) {
            $overflow_body  = $body_paragraphs;
            $body_content_h = 0.0;
        } else {
            $body_result = hatakiti_occult_pdf_layout_and_draw_columns( $pdf, $body_paragraphs, $x + $w, $body_top, $body_h, $cols_to_use, $fonts['body'], $font_regular );
            if ( $body_result['overflow'] ) {
                $overflow_body = $body_result['remainder'];
            }
            // 本文は列0（右端）から左へ埋まっていくため、実際に使った
            // 列数ぶんの幅だけが右詰めで占有される。割り当てられた
            // $w全体を占有済みとはみなさない（実描画占有矩形ベース
            // Available Spaces指示書§1・§4・§7）。
            $body_used_width = $body_result['columns_used'] * $body_result['col_pitch'];
            // Row内部visual空白指示書§2〜§3：予約高さ($body_h)ではなく
            // 実際に描画された最深部（content_h）を「本文の実際の高さ」
            // として記録する。ただし$bottom_y（出典・次行の開始位置）
            // の計算には従来どおり予約高さ$body_hを使い続ける — 実描画
            // 位置そのものは変更しない（既存の安定したページ送り・
            // 段組み判定を壊さないため）。空き矩形検出でのみ、この
            // より正確な値を使う。
            $body_content_h = $body_result['content_h'];
        }
    } else {
        $overflow_body = hatakiti_occult_pdf_build_units( $body );
    }

    $bottom_y = $body_top + $body_h;

    // 出典（横書き、本文のすぐ下に1行）。今回そろえるのは見出し上端と
    // 本文開始位置までであり、記事の終了位置（出典・罫線の位置）まで
    // 列間で無理に揃えようとはしない（追加指示§7）— 出典は単純にこの
    // セグメント自身の本文の直後に置く。
    //
    // 「記事が途中で切れて見える」問題修正指示書§3：出典帯ぶんの高さ
    // 予約（$src_h、ひいてはbottom_y・visual_bottom・次セグメントの
    // 開始位置）はmode==='headline'を基準にした既存どおりの計算のまま
    // 変更しない — ここをoverflow_body基準に変えると、visual_bottom
    // （空き矩形検出・PagePlan探索の候補比較の入力）が連鎖的に変わり、
    // 実データ検証（post_id=662）で総ページ数が悪化するケースを確認した
    // ため、意図的にレイアウト計算には手を入れない。変更するのは
    // 「出典を実際に印字するかどうか」だけ — overflow_body===null
    // （＝このセグメントで記事本文が本当に完結した）の場合のみ印字する。
    // 旧実装はmode==='headline'でありさえすれば完結の有無に関係なく
    // 出典を印字していたため、本文がこのセグメントで完結せず同一列内で
    // 継続する場合でも「出典＝記事終了の合図」が本文途中に印字され、
    // 続くcontinuationセグメント（見出しもラベルも無い"none"モード）が
    // 唐突な孤立断片に見える原因になっていた（post_id=662 1面「米政府
    // 「非人間的知性確認時の準備計画は存在する」」で実際に確認）。
    $src_h          = 'headline' === $mode ? HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM : 0.0;
    $print_citation = ( null === $overflow_body );
    $source_lines   = hatakiti_occult_pdf_source_lines( $article['news_item_ids'] ?? array() );
    if ( $source_lines && $w > 12 && $src_h > 0 ) {
        if ( $print_citation ) {
            $src_font  = 6.3;
            $src_y     = $bottom_y + 0.6;
            $max_chars = max( 2, (int) floor( $w / ( $src_font * 0.55 ) ) );
            $sl        = $source_lines[0];
            $text      = mb_strlen( $sl['text'] ) > $max_chars ? mb_substr( $sl['text'], 0, $max_chars - 1 ) . '…' : $sl['text'];

            $pdf->SetFont( $font_regular, '', $src_font );
            $pdf->SetXY( $x, $src_y );
            $pdf->Cell( $w, 3.4, hatakiti_occult_pdf_fullwidth_digits( $text ), 0, 0, 'L' );
            if ( $sl['url'] ) {
                $pdf->Link( $x, $src_y, $w, 3.4, $sl['url'] );
            }
        }
        $bottom_y += $src_h;
    }

    // Row内部visual空白指示書§2・§5：article_visual_bottom。出典が
    // 存在する場合は、出典（横書きの実描画要素）がbottom_yの位置に
    // 実際に描画されるため、そこを空き領域の開始位置にしてはならない
    // （既存記事の出典を上書きする配置を防ぐ）。出典が無いセグメント
    // （'none'モードの列内続き等）のみ、予約高さ($body_h)ではなく
    // 実際に描画された本文の最深部(content_h)を使う — 罫線は呼び出し
    // 元でbottom_yに描かれるため、visual_bottomはそれ以下にはしない。
    $visual_bottom = ( $src_h > 0 ) ? $bottom_y : ( $body_top + $body_content_h );

    return array(
        'overflow_body'    => $overflow_body,
        'bottom_y'         => $bottom_y,
        // 実描画占有矩形ベースAvailable Spaces指示書§4：本文の実際の
        // 占有矩形（右詰め）。見出し・出典は割り当て幅$wをそのまま
        // 占有するとみなす（実際にほぼ全幅を使う横書き要素のため）。
        'body_top'         => round( $body_top, 3 ),
        'body_h'           => round( $body_h, 3 ),
        'body_used_width'  => round( $body_used_width, 3 ),
        // Row内部visual空白指示書§2〜§3：実際に描画された最深部に基づく
        // visual_bottom（出典がある場合はbottom_yと同じ＝出典・罫線を
        // 上書きしない）。
        'visual_bottom'    => round( $visual_bottom, 3 ),
    );
}

/**
 * overflow_bodyがあれば「続きの箱」として同じキュー位置に差し戻し、
 * 無ければキューから取り除く。headlineフィールド自体は変更せず残すが、
 * draw_article_box()側が_continuationフラグを見て元の見出しの再表示を
 * スキップし、代わりに小さな継続表示ラベル「「元見出し」の続き」を
 * 描く（指示書§7「記事の続きが読者に明確に分かること」）。本文そのもの
 * は一切変更せずそのまま続ける。
 */
function hatakiti_occult_pdf_overflow_to_text( $overflow_body ) {
    $rebuilt = '';
    foreach ( $overflow_body as $para ) {
        $ptext = '';
        foreach ( $para as $u ) {
            $ptext .= $u['ch'];
        }
        $rebuilt .= ( $rebuilt ? "\n\n" : '' ) . $ptext;
    }
    return $rebuilt;
}

/**
 * fractions（列0=最も右→列N-1=最も左の幅比率、合計1.0）を、実際の
 * mm幅の配列へ変換する。列間ギャップ(ROW_COL_GAP_MM)ぶんを先に引いた
 * 実質幅を比率で配分する。
 */
function hatakiti_occult_pdf_fractions_to_widths( $fractions, $zone_w, $col_gap ) {
    $cols   = count( $fractions );
    $usable = $zone_w - ( $col_gap * ( $cols - 1 ) );
    $widths = array();
    foreach ( $fractions as $f ) {
        $widths[] = $usable * $f;
    }
    return $widths;
}

/**
 * ある記事（の残り本文）が、指定した列幅$col_wで$block_topから
 * 描画された場合に、実際に完結する（またはページ末$page_bottomに
 * 達して打ち切られる）until、どこまで（mm, ページ上端からの絶対Y）
 * 到達するかを見積もる。実際には描画しない — 候補となる段組み構成
 * ごとに「どれだけの高さを使い、どれだけ空白が残るか」を比較する
 * ためだけの dry-run。
 *
 * hatakiti_occult_pdf_body_segment_h() と同じ「非最終セグメントは
 * 常に固定の1段ぶんの高さ（unit_h_mm）、最終セグメントだけ実際の
 * 残り文字数ぶんへ縮小する」という考え方をそのまま踏襲し、列幅から
 * 導いた「1セグメントで実際に収まる文字数」（$capacity_per_segment）
 * を使って、続きが必要な場合はセグメントを積み増しながら進める。
 *
 * @return array array('bottom'=>mm, 'truncated'=>bool)
 */
function hatakiti_occult_pdf_estimate_article_height( $article, $tier, $col_w, $header_h, $block_top, $page_bottom ) {
    $fonts          = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $char_h         = $fonts['body'] * 0.3528;
    $body_col_pitch = $char_h * 1.08;
    $max_cols_by_w  = max( 1, (int) floor( $col_w / $body_col_pitch ) );
    $capacity       = $max_cols_by_w * HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN;

    list( $remaining, ) = hatakiti_occult_pdf_count_units( (string) ( $article['body'] ?? '' ) );

    $y        = $block_top;
    $is_first = true;
    $truncated = false;
    $safety   = 0;

    while ( $remaining > 0 && $safety < 40 ) {
        $safety++;

        $seg_header_h = $is_first ? $header_h : 0.0;
        $gap          = $is_first ? HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM : 0.0;
        $src_h        = $is_first ? HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM : 0.0;

        if ( $remaining > HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN ) {
            $body_h   = hatakiti_occult_pdf_unit_h_mm( $tier );
            $consumed = min( $remaining, $capacity );
        } else {
            $body_h   = ( $remaining + 1 ) * $char_h + HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM;
            $consumed = $remaining;
        }

        $seg_bottom = $y + $seg_header_h + $gap + $body_h + $src_h;
        if ( $seg_bottom > $page_bottom ) {
            $truncated = true;
            break;
        }

        $y         = $seg_bottom + HATAKITI_OCCULT_PDF_ROW_GAP_MM;
        $remaining -= $consumed;
        $is_first  = false;
    }

    if ( $truncated || $safety >= 40 ) {
        return array( 'bottom' => $page_bottom, 'truncated' => true );
    }
    // 最後に足したROW_GAPぶんを戻す（罫線位置＝実際の記事末端に相当）。
    return array( 'bottom' => $y - HATAKITI_OCCULT_PDF_ROW_GAP_MM, 'truncated' => false );
}

/**
 * 「ブロック」の段組み構成を決める（安全弁専用・旧実装）。
 *
 * hatakiti_occult_pdf_decide_block_config() から、通常の
 * 「記事全体がページに収まる構成が1つも無い」場合にのみ呼ばれる
 * フォールバック。記事の途中打ち切り（続き＝ページまたぎ）を許容
 * したうえで、それでも紙面の空白が最小になる構成を選ぶ — 2026-09-07
 * 全ページ対応・ブロック単位の可変段組みレイアウト実装指示書の
 * ロジックをそのまま維持している（列0（最も右）→列N-1（最も左）の
 * mm幅の配列を返す）。
 *
 * @return float[] 列0（最も右）→列N-1（最も左）の実際のmm幅。
 */
function hatakiti_occult_pdf_decide_block_config_legacy( $pdf, $font_bold, $queue, $zone_w, $page_bottom, $block_top ) {
    if ( empty( $queue ) ) {
        return array( $zone_w );
    }

    // 優先順位2（最優先）：続き記事の列位置・列幅維持。キュー先頭が
    // 前ブロックの続き（_pinned_col_w保持）なら、連続して続く先頭
    // ぶんだけそのままのmm幅を再現する。新規記事を混ぜて構成を組み
    // 替えることはしない — この構成が尽きるまで次のブロックへ持ち
    // 越さず、ここで確定させる。
    if ( isset( $queue[0]['_pinned_col_w'] ) ) {
        $widths = array();
        for ( $k = 0; $k < 3 && $k < count( $queue ); $k++ ) {
            if ( ! isset( $queue[ $k ]['_pinned_col_w'] ) ) {
                break;
            }
            $widths[] = (float) $queue[ $k ]['_pinned_col_w'];
        }
        return $widths;
    }

    // 優先順位1：large tierは常に1列全幅、他記事と横並びにしない。
    $tier = $queue[0]['_tier'];
    if ( 'large' === $tier ) {
        return array( $zone_w );
    }

    // tierごとの許可構成（指示書§6）。mediumは均等3列を候補に含めない
    // — 「1/3+2/3」等の非対称2列までに留める。smallのみ均等3列を追加。
    $configs = array(
        '1col'       => array( 1.0 ),
        '2col_equal' => array( 0.5, 0.5 ),
        '2col_q0w'   => array( 2 / 3, 1 / 3 ), // queue[0]（右列）を広く
        '2col_q1w'   => array( 1 / 3, 2 / 3 ), // queue[1]（左列）を広く
    );
    if ( 'small' === $tier ) {
        $configs['3col_equal'] = array( 1 / 3, 1 / 3, 1 / 3 );
    }

    // 同tierが連続している件数ぶんしか列を割けない（既存の制約を維持）。
    $same_tier_run = 1;
    for ( $i = 1; $i < count( $queue ) && $i < 3; $i++ ) {
        if ( $queue[ $i ]['_tier'] !== $tier ) {
            break;
        }
        $same_tier_run++;
    }

    $col_gap    = HATAKITI_OCCULT_PDF_ROW_COL_GAP_MM;
    $candidates = array();
    foreach ( $configs as $key => $fractions ) {
        $cols = count( $fractions );
        if ( $cols > $same_tier_run ) {
            continue;
        }
        $widths = hatakiti_occult_pdf_fractions_to_widths( $fractions, $zone_w, $col_gap );
        if ( min( $widths ) < HATAKITI_OCCULT_PDF_MIN_COL_W_MM ) {
            continue; // 優先順位4：細すぎる列は候補から除外
        }

        $block_header_h = 0.0;
        for ( $k = 0; $k < $cols; $k++ ) {
            $h = hatakiti_occult_pdf_measure_first_segment_header_h( $pdf, $font_bold, $queue[ $k ], $tier, $widths[ $k ] );
            $block_header_h = max( $block_header_h, $h );
        }

        $bottoms = array();
        for ( $k = 0; $k < $cols; $k++ ) {
            $est       = hatakiti_occult_pdf_estimate_article_height( $queue[ $k ], $tier, $widths[ $k ], $block_header_h, $block_top, $page_bottom );
            $bottoms[] = $est['bottom'];
        }
        $block_bottom = max( $bottoms );
        $height       = max( 0.001, $block_bottom - $block_top );
        $slack        = 0.0;
        foreach ( $bottoms as $b ) {
            $slack += ( $block_bottom - $b );
        }

        $candidates[ $key ] = array(
            'widths'       => $widths,
            'wasted_ratio' => $slack / ( $height * $cols ),
        );
    }

    if ( empty( $candidates ) ) {
        return array( $zone_w );
    }
    if ( ! isset( $candidates['1col'] ) ) {
        // 1colはcols<=same_tier_run・幅制約を必ず満たすため通常は
        // 到達しない安全弁。
        $first = reset( $candidates );
        return $first['widths'];
    }

    // 優先順位5：標準候補は1:1の2列。
    $chosen_key = isset( $candidates['2col_equal'] ) ? '2col_equal' : '1col';

    // 均等3列（smallのみ）は、2列よりも明確に空白が少ない場合のみ。
    if ( isset( $candidates['3col_equal'] )
        && $candidates['3col_equal']['wasted_ratio'] < 0.35
        && $candidates['3col_equal']['wasted_ratio'] <= $candidates[ $chosen_key ]['wasted_ratio'] * 0.7
    ) {
        $chosen_key = '3col_equal';
    }

    // 非対称2列（1:2／2:1）は、記事量の偏りに合わせて明確に空白が
    // 減る場合のみ、均等2列の代わりに採用する。どちら向きにするかは
    // queue[0]/queue[1]のどちらの本文が長いかで選ぶ（長い方に広い列
    // を割り当てる）。
    if ( '3col_equal' !== $chosen_key && isset( $candidates['2col_q0w'] ) && isset( $candidates['2col_q1w'] ) ) {
        list( $len0, ) = hatakiti_occult_pdf_count_units( (string) ( $queue[0]['body'] ?? '' ) );
        list( $len1, ) = hatakiti_occult_pdf_count_units( (string) ( $queue[1]['body'] ?? '' ) );
        $asym_key = $len0 >= $len1 ? '2col_q0w' : '2col_q1w';
        if ( $candidates[ $asym_key ]['wasted_ratio'] < 0.35
            && $candidates[ $asym_key ]['wasted_ratio'] <= $candidates[ $chosen_key ]['wasted_ratio'] * 0.7
        ) {
            $chosen_key = $asym_key;
        }
    }

    // それでも無駄が大きすぎる場合のみ、1列（記事量が少ない場合）へ
    // 後退する。
    if ( '1col' !== $chosen_key
        && $candidates[ $chosen_key ]['wasted_ratio'] > 0.6
        && $candidates['1col']['wasted_ratio'] < $candidates[ $chosen_key ]['wasted_ratio']
    ) {
        $chosen_key = '1col';
    }

    return $candidates[ $chosen_key ]['widths'];
}

/**
 * 後続記事探索（次段階のレイアウト改善指示書「後続記事の探索ルール」）。
 * queue[$start]から最大HATAKITI_OCCULT_PDF_LOOKAHEAD_WINDOW件先までの
 * 範囲で、単独1列でこのページの残り高さに完全に収まる記事を探す。
 * _pinned_col_wを持つ記事（列位置・列幅が既に確定している続き）は
 * 対象にしない — 列位置維持を最優先する既存仕様を壊さないため。
 * 複数の候補が見つかった場合は「配置後の残り空白が最も少ないもの」
 * （優先順位4）を優先し、同点の場合はインデックスが小さい方＝
 * 「飛び越し量が少ないもの」（優先順位5）を優先する。
 *
 * @return int|null 見つかった場合はそのqueueインデックス、無ければnull。
 */
function hatakiti_occult_pdf_search_ahead_filler( $pdf, $font_bold, $queue, $start, $zone_w, $block_top, $page_bottom ) {
    $best_idx   = null;
    $best_waste = null;
    $limit      = min( count( $queue ), $start + HATAKITI_OCCULT_PDF_LOOKAHEAD_WINDOW );

    for ( $j = $start; $j < $limit; $j++ ) {
        if ( isset( $queue[ $j ]['_pinned_col_w'] ) ) {
            continue;
        }
        $tier     = $queue[ $j ]['_tier'];
        $header_h = hatakiti_occult_pdf_measure_first_segment_header_h( $pdf, $font_bold, $queue[ $j ], $tier, $zone_w );
        $est      = hatakiti_occult_pdf_estimate_article_height( $queue[ $j ], $tier, $zone_w, $header_h, $block_top, $page_bottom );
        if ( $est['truncated'] ) {
            continue;
        }
        $waste = $page_bottom - $est['bottom'];
        if ( null === $best_waste || $waste < $best_waste ) {
            $best_waste = $waste;
            $best_idx   = $j;
        }
    }

    return $best_idx;
}

/**
 * ページ充填アルゴリズム改善指示書（4分割・複数ブロック組合せ対応）
 * — このtierで候補となる「1行ぶん」の段組み構成。mediumは均等3列・
 * 4列を候補に含めない（本文幅が狭くなりすぎるため）。smallのみ
 * 均等3列・4列を追加する。
 */
function hatakiti_occult_pdf_row_configs_for_tier( $tier ) {
    $configs = array(
        '1col'       => array( 1.0 ),
        '2col_equal' => array( 0.5, 0.5 ),
        '2col_q0w'   => array( 2 / 3, 1 / 3 ),
        '2col_q1w'   => array( 1 / 3, 2 / 3 ),
    );
    if ( 'small' === $tier ) {
        $configs['3col_equal'] = array( 1 / 3, 1 / 3, 1 / 3 );
        $configs['4col_equal'] = array( 0.25, 0.25, 0.25, 0.25 );
    }
    return $configs;
}

/**
 * バックフィルの優先順位2（次段の空白削減・可変ブロック組版指示書§5）
 * — 同tierで埋まらない場合にだけ試す隣接tier。実装はしたが、既定では
 * HATAKITI_OCCULT_PDF_ALLOW_CROSS_TIER_BACKFILLでオフにしている。
 *
 * 理由（実データでの検証で判明）：medium列の空きを小tier記事で
 * バックフィルすると、その時点では該当ページの空白率は下がるが、
 * small記事という「あとで小tier同士のブロックに必要な在庫」を先食い
 * してしまい、結果的にページ末尾でsmall記事が足りなくなって
 * ページ数がかえって増えるケースを創刊号#662の実データで確認した
 * （5ページ→6ページに悪化）。1ブロック単位の局所最適が、ページ全体
 * では悪化する典型例。真に安全に行うには「後続の同tier必要量を含めた
 * 全体最適」が要るが、今回の実装范囲では見送り、既定オフとした。
 * 有効化したい場合はこの定数をtrueにする（既存の他ロジックは無変更で
 * 動く）。
 */
function hatakiti_occult_pdf_fallback_tier( $tier ) {
    if ( ! HATAKITI_OCCULT_PDF_ALLOW_CROSS_TIER_BACKFILL ) {
        return null;
    }
    if ( 'medium' === $tier ) {
        return 'small';
    }
    if ( 'small' === $tier ) {
        return 'medium';
    }
    return null;
}

/**
 * hatakiti_occult_pdf_fallback_tier()の隣接tier表（medium⇄small）と
 * 同じ組み合わせを、HATAKITI_OCCULT_PDF_ALLOW_CROSS_TIER_BACKFILLの
 * 設定に関係なく返す。Space Fill専用 — 既に「他記事が使わなかった
 * 余り幅」であり埋めなければそのまま無駄になる領域が対象のため、
 * バックフィルで確認された「medium列がsmallを先食いする」リスクは
 * 当てはまらない（採用前に必ず総ページ数悪化チェックを通るため）。
 */
function hatakiti_occult_pdf_space_fill_fallback_tier( $tier ) {
    if ( 'medium' === $tier ) {
        return 'small';
    }
    if ( 'small' === $tier ) {
        return 'medium';
    }
    return null;
}

/**
 * ちょうど$fractions分の記事（$items）を、$row_topから始まる1行として
 * 描いた場合のdry-run評価。列のいずれか1つでもこのページに完全に収まら
 * ない場合はnullを返す（記事途中分割を伴う行は候補にしない）。
 *
 * 「各列の初期記事のあとにバックフィルでどこまで延長できるか」まで
 * この時点でdry-runシミュレーションして候補比較に織り込む案を実装・
 * 実測したが、創刊号#662の実データで比較したところ、素朴な単一記事
 * dry-run（このバージョン）の方が実際のページ数が少なかった
 * （シミュレーション有りは6〜7ページ、無しは5ページ）。バックフィルは
 * 実行時に貪欲に確定していくため、候補比較の時点で「先読みで仮に消費
 * した記事」が実際のバックフィル時の選択と食い違い、かえって不利な
 * 段組みを選んでしまうケースがあったためと考えられる。そのため、この
 * 関数は各列の初期記事1件だけを見る単純なdry-runに留めている —
 * 実際の空き埋めはhatakiti_occult_pdf_stack_articles()内のバックフィル
 * パス（実行時に確定済みの状態を見て判断するため、事前シミュレーション
 * より正確）に委ねる。
 *
 * @return array|null array('cols','widths','row_height')
 */
function hatakiti_occult_pdf_evaluate_row( $pdf, $font_bold, $items, $tier, $fractions, $zone_w, $row_top, $page_bottom ) {
    $cols = count( $fractions );
    if ( count( $items ) < $cols ) {
        return null;
    }
    $col_gap = HATAKITI_OCCULT_PDF_ROW_COL_GAP_MM;
    $widths  = hatakiti_occult_pdf_fractions_to_widths( $fractions, $zone_w, $col_gap );
    if ( min( $widths ) < HATAKITI_OCCULT_PDF_MIN_COL_W_MM ) {
        return null;
    }

    $header_h = 0.0;
    for ( $k = 0; $k < $cols; $k++ ) {
        $h = hatakiti_occult_pdf_measure_first_segment_header_h( $pdf, $font_bold, $items[ $k ], $tier, $widths[ $k ] );
        $header_h = max( $header_h, $h );
    }

    $bottoms = array();
    foreach ( $widths as $k => $w ) {
        $est = hatakiti_occult_pdf_estimate_article_height( $items[ $k ], $tier, $w, $header_h, $row_top, $page_bottom );
        if ( $est['truncated'] ) {
            return null;
        }
        $bottoms[ $k ] = $est['bottom'];
    }

    $row_bottom = max( $bottoms );
    $row_height = $row_bottom - $row_top;

    return array(
        'cols'       => $cols,
        'widths'     => $widths,
        'row_height' => $row_height,
    );
}

/**
 * $a と $b（どちらも hatakiti_occult_pdf_search_rows_recursive()の
 * 戻り値と同じ形）を比べ、より良い方を返す。
 *
 * 比較の基準は「使用高さ ÷ 配置した記事数」（1記事あたりの平均消費
 * 高さ）— 小さいほど良い。1列は「他列との不揃い」が原理的に発生
 * しない（列が1本しかない）ため、単純な「行内の空白面積」で比べると
 * 常に1列が満点になってしまい、複数列が一切選ばれなくなる。1記事
 * あたりの高さで比較すれば、列幅が半分になった記事はおおよそ倍の
 * 高さを要するため、記事の長さがよく揃っている場合は複数列と1列が
 * ほぼ同等に評価され、長さが偏っている場合にのみ1列（または段数の
 * 違う組合せ）が有利になる — 指示書§5の面積比較の意図を、1列を含む
 * 全構成で公平に比較できる形にしたもの。僅差（1記事あたり0.5mm以内）
 * なら段数が少ない方を優先する。$b が null なら常に $a。
 */
function hatakiti_occult_pdf_better_multirow_plan( $a, $b ) {
    if ( null === $b ) {
        return $a;
    }
    $avg_a = $a['height'] / max( 1, $a['article_count'] );
    $avg_b = $b['height'] / max( 1, $b['article_count'] );
    if ( $avg_a < $avg_b - 0.001 ) {
        return $a;
    }
    if ( abs( $avg_a - $avg_b ) <= 0.5 && count( $a['rows'] ) < count( $b['rows'] ) ) {
        return $a;
    }
    return $b;
}

/**
 * 後続の同tier記事群を使い、最大$max_rows段までの組合せを再帰的に
 * 探索する（ページ充填アルゴリズム改善指示書§4「評価単位をブロックから
 * ページ残り領域に拡張する」）。各段は$queueの連続する先頭部分（この
 * 呼び出しでは$consumed_before以降）を、段の列数ぶんだけ消費する —
 * 記事の順序は一切入れ替えない。「この段だけで止める」場合と「この段＋
 * 後続の最良の組合せ」の両方を候補として比較し、
 * hatakiti_occult_pdf_better_multirow_plan()（1記事あたりの平均消費
 * 高さが最も小さいもの）で選ぶ。
 *
 * @return array|null array('rows'=>[evaluate_row()の戻り値,...],
 *   'height','article_count') — 1行も組めない場合はnull。
 */
function hatakiti_occult_pdf_search_rows_recursive( $pdf, $font_bold, $queue, $tier, $configs, $zone_w, $row_top, $page_bottom, $remaining_count, $consumed_before, $max_rows ) {
    $best = null;

    foreach ( $configs as $fractions ) {
        $cols = count( $fractions );
        if ( $cols > $remaining_count ) {
            continue;
        }
        $items = array_slice( $queue, $consumed_before, $cols );

        // 非対称2列は、この行に割り当てる2記事の本文量に合った向き
        // だけを候補にする（長い方を広い列へ）。
        if ( 2 === $cols && 0.5 !== $fractions[0] ) {
            list( $len0, ) = hatakiti_occult_pdf_count_units( (string) ( $items[0]['body'] ?? '' ) );
            list( $len1, ) = hatakiti_occult_pdf_count_units( (string) ( $items[1]['body'] ?? '' ) );
            $wants_q0_wide = $len0 >= $len1;
            $is_q0_wide    = $fractions[0] > $fractions[1];
            if ( $wants_q0_wide !== $is_q0_wide ) {
                continue;
            }
        }

        $row = hatakiti_occult_pdf_evaluate_row( $pdf, $font_bold, $items, $tier, $fractions, $zone_w, $row_top, $page_bottom );
        if ( null === $row ) {
            continue;
        }

        // 候補1：この段だけで止める。
        $alone = array(
            'rows'          => array( $row ),
            'height'        => $row['row_height'],
            'article_count' => $row['cols'],
        );
        $best = hatakiti_occult_pdf_better_multirow_plan( $alone, $best );

        // 候補2：この段＋後続段の最良の組合せ。
        if ( $max_rows > 1 && $remaining_count > $cols ) {
            $next = hatakiti_occult_pdf_search_rows_recursive( $pdf, $font_bold, $queue, $tier, $configs, $zone_w, $row_top + $row['row_height'], $page_bottom, $remaining_count - $cols, $consumed_before + $cols, $max_rows - 1 );
            if ( null !== $next ) {
                $combined = array(
                    'rows'          => array_merge( array( $row ), $next['rows'] ),
                    'height'        => $row['row_height'] + $next['height'],
                    'article_count' => $row['cols'] + $next['article_count'],
                );
                $best = hatakiti_occult_pdf_better_multirow_plan( $combined, $best );
            }
        }
    }

    return $best;
}

/**
 * ページ残り領域を、後続の同tier記事を使って最大
 * HATAKITI_OCCULT_PDF_MAX_PLAN_ROWS段のブロックへ分割する候補を探索し、
 * 1記事あたりの平均消費高さが最も小さい組合せの「最初の1行ぶんの列幅」
 * だけを返す（続きの行は、次回のhatakiti_occult_pdf_decide_block_
 * config()呼び出しで、その時点のqueue/block_topから同じ探索が自然に
 * 再実行され、通常は同じ結論が再発見される — 状態を持ち越さない、
 * 決定論的な探索）。
 *
 * 4列は、4列を含まない最良候補より1記事あたりの平均消費高さが30%以上
 * 改善する場合のみ採用する（指示書§6「4列の採用条件」— 既存の3列・
 * 非対称2列の採用しきい値と同じ考え方）。
 *
 * @return float[]|null 見つかった場合は最初の行の列幅配列（列0＝最も
 *   右→列N-1＝最も左）、1行も完結できる組合せが無ければnull。
 */
function hatakiti_occult_pdf_search_multirow_plan( $pdf, $font_bold, $queue, $tier, $zone_w, $block_top, $page_bottom ) {
    $limit         = min( count( $queue ), HATAKITI_OCCULT_PDF_LOOKAHEAD_WINDOW );
    $same_tier_run = 0;
    for ( $i = 0; $i < $limit; $i++ ) {
        if ( $queue[ $i ]['_tier'] !== $tier ) {
            break;
        }
        $same_tier_run++;
    }
    if ( 0 === $same_tier_run ) {
        return null;
    }

    $configs_all = hatakiti_occult_pdf_row_configs_for_tier( $tier );
    $configs_no4 = $configs_all;
    unset( $configs_no4['4col_equal'] );

    $best_any = hatakiti_occult_pdf_search_rows_recursive( $pdf, $font_bold, $queue, $tier, $configs_all, $zone_w, $block_top, $page_bottom, $same_tier_run, 0, HATAKITI_OCCULT_PDF_MAX_PLAN_ROWS );
    if ( null === $best_any ) {
        return null;
    }

    $uses_4col = false;
    foreach ( $best_any['rows'] as $r ) {
        if ( 4 === $r['cols'] ) {
            $uses_4col = true;
            break;
        }
    }
    if ( ! $uses_4col ) {
        return $best_any['rows'][0]['widths'];
    }

    $best_no4 = hatakiti_occult_pdf_search_rows_recursive( $pdf, $font_bold, $queue, $tier, $configs_no4, $zone_w, $block_top, $page_bottom, $same_tier_run, 0, HATAKITI_OCCULT_PDF_MAX_PLAN_ROWS );
    if ( null === $best_no4 ) {
        return $best_any['rows'][0]['widths'];
    }

    $avg_any = $best_any['height'] / max( 1, $best_any['article_count'] );
    $avg_no4 = $best_no4['height'] / max( 1, $best_no4['article_count'] );
    if ( $avg_any > $avg_no4 * 0.7 ) {
        // 4列を含む方が明確には有利でない（1記事あたりの平均消費高さが
        // 30%以上改善していない）— 4列を使わない方を採用する。
        return $best_no4['rows'][0]['widths'];
    }
    return $best_any['rows'][0]['widths'];
}

/**
 * 「ブロック」の段組み構成を決める（次段階のレイアウト改善指示書）。
 *
 * 従来の「記事をページ末まで詰め込んで続きを次ページへ」という通常
 * 経路をやめ、次の順序で決める（1ページ目から最終ページまで共通）：
 *
 *   1) 続き記事（_pinned_col_w保持）が先頭なら、その列位置・列幅を
 *      そのまま維持する（既存仕様、無条件で最優先）。
 *   2) large tierが先頭なら、1列全幅で「記事全体がこのページに収まる
 *      か」を確認する。収まるならそのまま配置。収まらない場合、
 *      large記事は分割せず、後続キューから「単独1列でこのページに
 *      完全に収まる」記事を探して先に配置する（見つからない場合のみ
 *      安全弁として従来どおりlarge記事を配置＝続きが発生し得る）。
 *   3) medium/smallでは、hatakiti_occult_pdf_search_multirow_plan()が
 *      後続の同tier記事群（最大HATAKITI_OCCULT_PDF_LOOKAHEAD_WINDOW件）
 *      を使い、最大HATAKITI_OCCULT_PDF_MAX_PLAN_ROWS段までの段組み
 *      組合せ（1列/1:1/1:2/2:1、smallのみ1:1:1・1:1:1:1も候補）を
 *      dry-runで探索し、割り当てる記事すべてがこのページに完全に
 *      収まる組合せの中から面積効率（実際に文字が入っている面積 ÷
 *      その段組みが占める版面上の面積）が最も高いものを選ぶ — その
 *      最初の1段ぶんだけを今回のブロックとして採用する（続きの段は
 *      次回の呼び出しで同じ探索から自然に再発見される）。該当構成が
 *      1つも無ければ、後続キューから「単独1列で完全に収まる」記事を
 *      探して先に配置する。それも無ければ安全弁として
 *      hatakiti_occult_pdf_decide_block_config_legacy()（続きの発生を
 *      許容する旧ロジック）にフォールバックする。
 *
 * @return float[] 列0（最も右）→列N-1（最も左）の実際のmm幅。
 */
function hatakiti_occult_pdf_decide_block_config( $pdf, $font_bold, &$queue, $zone_w, $page_bottom, $block_top, $depth = 0 ) {
    if ( empty( $queue ) ) {
        return array( $zone_w );
    }

    // 優先順位2（続き記事の列位置・列幅維持）は無条件で最優先 —
    // 旧ロジックへ委譲する（既存仕様そのまま、探索の対象にしない）。
    if ( isset( $queue[0]['_pinned_col_w'] ) ) {
        return hatakiti_occult_pdf_decide_block_config_legacy( $pdf, $font_bold, $queue, $zone_w, $page_bottom, $block_top );
    }

    $tier = $queue[0]['_tier'];

    if ( 'large' === $tier ) {
        $header_h = hatakiti_occult_pdf_measure_first_segment_header_h( $pdf, $font_bold, $queue[0], 'large', $zone_w );
        $est      = hatakiti_occult_pdf_estimate_article_height( $queue[0], 'large', $zone_w, $header_h, $block_top, $page_bottom );
        if ( ! $est['truncated'] ) {
            return array( $zone_w );
        }
        if ( $depth < 1 ) {
            $filler_idx = hatakiti_occult_pdf_search_ahead_filler( $pdf, $font_bold, $queue, 1, $zone_w, $block_top, $page_bottom );
            if ( null !== $filler_idx ) {
                $moved = array_splice( $queue, $filler_idx, 1 );
                array_unshift( $queue, $moved[0] );
                return hatakiti_occult_pdf_decide_block_config( $pdf, $font_bold, $queue, $zone_w, $page_bottom, $block_top, $depth + 1 );
            }
        }
        // 安全弁：埋められる記事が見つからない場合のみ、large記事自体
        // を配置する（続きが発生し得る＝従来どおりの例外的経路）。
        return array( $zone_w );
    }

    $plan_widths = hatakiti_occult_pdf_search_multirow_plan( $pdf, $font_bold, $queue, $tier, $zone_w, $block_top, $page_bottom );
    if ( null !== $plan_widths ) {
        return $plan_widths;
    }

    // 先頭記事（群）がどの構成でも完結しない — 後続キューから、この
    // ページの残り高さに単独1列で完全に収まる記事を探して先に配置
    // する（「①同一記事はページをまたがない」「③空白を残したまま
    // 改ページしない」）。
    if ( $depth < 1 ) {
        $filler_idx = hatakiti_occult_pdf_search_ahead_filler( $pdf, $font_bold, $queue, 1, $zone_w, $block_top, $page_bottom );
        if ( null !== $filler_idx ) {
            $moved = array_splice( $queue, $filler_idx, 1 );
            array_unshift( $queue, $moved[0] );
            return hatakiti_occult_pdf_decide_block_config( $pdf, $font_bold, $queue, $zone_w, $page_bottom, $block_top, $depth + 1 );
        }
    }

    // 安全弁：埋められる記事が無い場合のみ、従来ロジック（続きの発生
    // を許容）にフォールバックする。
    return hatakiti_occult_pdf_decide_block_config_legacy( $pdf, $font_bold, $queue, $zone_w, $page_bottom, $block_top );
}

/**
 * 「紙面先行型」の列組版。処理は「ブロック」単位で進む —
 * hatakiti_occult_pdf_decide_block_config() がブロック先頭で段組み
 * 構成（列数と各列の幅、1列〜3列・均等／非対称）を1回だけ決め、各列
 * にはキュー先頭からその列数ぶんの記事を割り当てる。そのあとは各列を
 * 完全に独立に、同じX座標・同じ列幅のまま下方向へ積み上げる — ある
 * 列の記事が続く限り、その続きは必ず同じ列にとどまる（同一記事の
 * 連続性・元記事と同じ列位置の維持、ページをまたいでも同じ）。ブロック
 * は全列が完結する（またはページ末に達する）まで終わらず、次のブロック
 * はその時点の最も深い列の位置から、改めて段組み構成を判定して始まる
 * （同一ページ内・ページをまたいでも、ブロックの列配置を勝手に組み替え
 * ない）。1ページ目から最終ページまで、この判定ロジックはまったく同じ
 * — ページ番号による特別扱いは無い。
 *
 * 【バックフィル】1つの列（続き記事ではなく、元の記事が完結した列）が
 * 空いた場合、そこへ別の記事を割り込ませる（記事配置アルゴリズム再調整
 * 指示書§5/§7）。空いた列を「その列のX座標・列幅を持つ、記事完結位置
 * からページ末までの空き矩形」とみなし、後続キュー（最大
 * HATAKITI_OCCULT_PDF_LOOKAHEAD_WINDOW件）から、その矩形に完全に収まる
 * 同tier記事を探して詰める — 「列構成（列数・列幅）を維持すること」を
 * 優先しつつ、既に決まった列構成の中で個々の列の空きは可能な限り埋める。
 * 続き記事が確定している列（次ページへ持ち越す列）はバックフィル対象に
 * しない。
 *

 * 【見出し領域と本文領域の分離】各列の見出し（またはブロック先頭の
 * 続きラベル）の高さはブロック内で列ごとに異なってよいが、本文の
 * 開始Y座標はブロック内の全列で完全に一致させる — 見出しが長い列に
 * 合わせて block_header_h = ブロック内の各列の見出し高さの最大値を
 * 求め、全列とも body_start_y = block_top + block_header_h + 共通余白
 * から本文を描き始める（追加指示§3〜§6）。見出しが短い列は、見出し
 * 下に余白ができてよい。記事の終了位置（出典・罫線）まで列間で揃える
 * 必要はない（追加指示§7）。
 *
 * 【続きラベルの要否】ブロックの最初のセグメント（＝キューに入っていた
 * ときにすでに続き記事だったもの＝ページをまたいだ続き）は継続表示
 * ラベルを表示する。一方、同じ列の中で2セグメント目以降（＝同一ページ
 * 内でこの場で発生した続き）はラベルを一切表示せず、見出し領域も
 * 確保しない（高さ0）— 本文がそのまま連続して見える（追加指示§1/§2）。
 *
 * @return array array('bottom_y'=>mm, 'drew_any'=>bool, 'debug'=>array)
 */
function hatakiti_occult_pdf_stack_articles( &$queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_y, $zone_w, $zone_h_budget, $page_no = 1 ) {
    $page_bottom = $zone_y + $zone_h_budget;
    $block_top   = $zone_y;
    $drew_any    = false;
    $debug       = array();
    $block_index = 0; // 検証・報告用（レイアウトには影響しない）— このページ内でのブロック通し番号。

    // ページ単位探索指示書§11：まずBaseline Planと候補PagePlanを比較し、
    // 明確に優れた候補が採用されればそのRow列をこのページの冒頭で
    // まとめて実描画する。採用されない場合（既定はfalse、または
    // 候補が基準を満たさない場合）は、下の既存の逐次ブロック処理へ
    // そのままフォールバックする。
    if ( HATAKITI_OCCULT_PDF_ALLOW_PAGE_LEVEL_SEARCH && ! $GLOBALS['hatakiti_occult_pdf_suppress_page_search'] && ! empty( $queue ) && ! isset( $queue[0]['_pinned_col_w'] ) ) {
        $plan_result = hatakiti_occult_pdf_search_page_plan( $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $block_top, $page_bottom, $page_no, $block_index );
        if ( null !== $plan_result ) {
            $debug = array_merge( $debug, $plan_result['debug_log'] );
            if ( null !== $plan_result['rows'] ) {
                foreach ( $plan_result['rows'] as $row ) {
                    $block_index++;
                    $result = hatakiti_occult_pdf_draw_one_block( $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $block_top, $page_bottom, $page_no, $block_index, $row['col_w_arr'] );
                    if ( null === $result ) {
                        break; // 実描画時に再現できなかった場合の安全弁（通常到達しない）。
                    }
                    $debug     = array_merge( $debug, $result['debug'] );
                    $drew_any  = true;
                    $block_top = $result['block_bottom'];
                }
            }
            // 空き矩形拡張指示書§7：採用されたPagePlanに空き矩形への
            // 配置（placements）が含まれる場合、探索時に確定したx/y/幅
            // へ実際に描画する。探索はコピーしたキューで行われたため、
            // 実キューから同じ記事を_debug_article_id（安定した識別子）
            // で探して取り出す。
            if ( ! empty( $plan_result['placements'] ) ) {
                foreach ( $plan_result['placements'] as $placement ) {
                    $found_idx = null;
                    foreach ( $queue as $qi => $qa ) {
                        if ( ( $qa['_debug_article_id'] ?? null ) === $placement['article_id'] ) {
                            $found_idx = $qi;
                            break;
                        }
                    }
                    if ( null === $found_idx ) {
                        continue; // 通常到達しない安全弁。
                    }
                    $article = $queue[ $found_idx ];
                    array_splice( $queue, $found_idx, 1 );
                    $draw_result = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article, $placement['tier'], $placement['x'], $placement['y'], $placement['width'], $placement['header_h'], 'headline' );
                    $pdf->SetLineWidth( 0.25 );
                    $pdf->Line( $placement['x'], $draw_result['bottom_y'] + ( HATAKITI_OCCULT_PDF_ROW_GAP_MM / 2 ), $placement['x'] + $placement['width'], $draw_result['bottom_y'] + ( HATAKITI_OCCULT_PDF_ROW_GAP_MM / 2 ) );
                    $debug[] = array(
                        'page' => $page_no, 'block' => 'space_fill', 'cols' => 1, 'col' => 0,
                        'article_id' => $placement['article_id'],
                        'tier' => $placement['tier'],
                        'headline' => mb_substr( (string) ( $article['headline'] ?? '' ), 0, 16 ),
                        'x' => round( $placement['x'], 1 ), 'y' => round( $placement['y'], 1 ), 'w' => round( $placement['width'], 1 ), 'h' => round( $draw_result['bottom_y'] - $placement['y'], 1 ),
                        'continuation' => false, 'mode' => 'headline', 'label_shown' => false, 'is_first_in_block' => false,
                        'body_top' => round( $placement['y'] + $placement['header_h'] + HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM, 1 ),
                        'overflow' => ! empty( $draw_result['overflow_body'] ),
                        'body_h_actual' => $draw_result['body_h'] ?? 0.0,
                        'body_used_width' => $draw_result['body_used_width'] ?? 0.0,
                        'visual_bottom' => $draw_result['visual_bottom'] ?? round( $draw_result['bottom_y'], 1 ),
                        'space_fill' => true,
                        'order_jump' => $placement['order_jump'],
                    );
                    $drew_any = true;

                    // 左空白問題修正指示書§3・§9：space-fill配置は探索時の
                    // hatakiti_occult_pdf_estimate_article_in_space()が
                    // truncated===false（完全に収まる）と判定した候補
                    // しか候補化しないが、これはdry-runの見積もりに過ぎない
                    // — 実描画（hatakiti_occult_pdf_draw_article_box()）が
                    // 万一これと食い違い本文が残った場合、この経路には
                    // 通常のwhile(true)継続ループが無いため、何もしなければ
                    // 残本文がそのまま失われてしまう（「記事の部分配置は
                    // 禁止」に反する、最悪の失敗モード）。見積もりと実描画の
                    // 乖離は通常発生しない想定だが、防御的に、万一発生した
                    // 場合は残本文を通常の続きセグメントとしてキュー先頭へ
                    // 差し戻し、次の通常ブロック処理で必ず描画されるように
                    // する（内容を落とさないことを常に優先する）。
                    if ( ! empty( $draw_result['overflow_body'] ) ) {
                        $continuation                  = $article;
                        $continuation['_continuation'] = true;
                        $continuation['body']          = hatakiti_occult_pdf_overflow_to_text( $draw_result['overflow_body'] );
                        array_unshift( $queue, $continuation );
                    }
                }
            }
        }
    }

    while ( ! empty( $queue ) && ( $page_bottom - $block_top ) > HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM ) {
        $block_index++;

        // ページ途中空白削減指示書（第2弾）§1〜§3 — 「現在のブロック
        // 構成を固定してから次のブロックを始める」を最適解とみなさず、
        // 直近2ブロック分をdry-runでまとめて比較し、ページ全体として
        // 空白の少ない構成を選ぶ（詳細は
        // hatakiti_occult_pdf_choose_block_config_with_lookahead()）。
        $col_w_arr = hatakiti_occult_pdf_choose_block_config_with_lookahead( $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $block_top, $page_bottom, $page_no, $block_index );

        $result = hatakiti_occult_pdf_draw_one_block( $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $block_top, $page_bottom, $page_no, $block_index, $col_w_arr );
        if ( null === $result ) {
            break;
        }
        $debug       = array_merge( $debug, $result['debug'] );
        $drew_any    = $drew_any || $result['drew_any'];
        $block_top   = $result['block_bottom'];
    }

    return array( 'bottom_y' => $block_top, 'drew_any' => $drew_any, 'debug' => $debug );
}

/**
 * hatakiti_occult_pdf_stack_articles()から1ブロック分の決定・描画・
 * バックフィル・キュー更新を切り出したもの。$col_w_arrを渡せば
 * その列構成を強制し（hatakiti_occult_pdf_decide_block_config()は
 * 呼ばない）、nullなら従来どおり自動決定する。実PDFへの本描画にも、
 * 候補比較用の使い捨てTCPDFへのdry-runにも、同じ関数を使う（実描画と
 * 見積もりの乖離を防ぐ、既存の設計方針を踏襲）。
 *
 * @return array|null array('block_bottom'=>mm,'drew_any'=>bool,'debug'=>array)。
 *   どの列も1件も描画できなければnull（＝これ以上このページには載らない）。
 */
function hatakiti_occult_pdf_draw_one_block( &$queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $block_top, $page_bottom, $page_no, $block_index, $col_w_arr = null ) {
    $row_gap = HATAKITI_OCCULT_PDF_ROW_GAP_MM;
    $col_gap = HATAKITI_OCCULT_PDF_ROW_COL_GAP_MM;
    if ( null === $col_w_arr ) {
        $col_w_arr = hatakiti_occult_pdf_decide_block_config( $pdf, $font_bold, $queue, $zone_w, $page_bottom, $block_top );
    }
    $cols     = count( $col_w_arr );
    $drew_any = false;
    $debug    = array();

        // 各列のX座標（縦書きの読み順に合わせ、列0をいちばん右に置く）
        // はブロック開始時に一度だけ決め、この列の記事が続く限り最後
        // まで変えない。列幅は列ごとに異なってよい（非対称2列）ため、
        // 右端からの累積オフセットで各列の左端を求める。
        $col_x = array();
        $cursor_from_right = 0.0;
        for ( $k = 0; $k < $cols; $k++ ) {
            $col_x[ $k ] = $zone_x + $zone_w - $cursor_from_right - $col_w_arr[ $k ];
            $cursor_from_right += $col_w_arr[ $k ] + $col_gap;
        }

        // ブロック内全列の「最初のセグメント」の見出し（またはラベル）
        // 高さを先に見積もり、その最大値を全列共通の見出し領域高さと
        // する — これにより本文開始Y座標を列間で完全に揃えられる
        // （追加指示§5「block_header_h = 最大値」）。
        $block_header_h = 0.0;
        for ( $k = 0; $k < $cols; $k++ ) {
            $h = hatakiti_occult_pdf_measure_first_segment_header_h( $pdf, $font_bold, $queue[ $k ], $queue[ $k ]['_tier'], $col_w_arr[ $k ] );
            $block_header_h = max( $block_header_h, $h );
        }
        $body_start_y = $block_top + $block_header_h + HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM;

        $col_final   = array(); // 各列で最後に残った記事（nullなら完結）
        $col_bottom  = array(); // 各列が到達した実際のY
        $col_drew    = array();

        for ( $k = 0; $k < $cols; $k++ ) {
            $article     = $queue[ $k ];
            // このブロックで決まった列幅を記事自身に記録しておく —
            // ページをまたいで続きが発生した場合、次ページの
            // hatakiti_occult_pdf_decide_block_config() がこの値を
            // そのまま読み、同じ列幅・同じX座標を再現する（新規記事の
            // 混在や段組みの組み替えをしない、追加指示§10）。
            $article['_pinned_col_w'] = $col_w_arr[ $k ];
            $is_first    = true; // このブロック内でこの列の最初のセグメントか
            $y           = $block_top;
            $drew_this   = false;
            $stall_key   = null;
            $stall_count = 0;

            while ( true ) {
                $body_len_now = mb_strlen( (string) ( $article['body'] ?? '' ) );
                if ( $stall_key === $body_len_now ) {
                    $stall_count++;
                } else {
                    $stall_key   = $body_len_now;
                    $stall_count = 0;
                }
                if ( $stall_count >= 3 ) {
                    // 安全弁：同じ記事が縮まないまま連続で続き扱いに
                    // なった場合、無限/準無限ループを防ぐ。この列は
                    // ここで打ち切り、現状の$articleをそのまま次の
                    // 機会（次ページ等）へ差し戻す。
                    break;
                }

                $tier = $article['_tier'];
                // このセグメントの見出し領域：ブロック最初のセグメント
                // だけblock_header_h（ラベル or 見出し）を使い、2回目
                // 以降は0（見出しもラベルも無し・本文がそのまま続く）。
                if ( $is_first ) {
                    $mode     = ! empty( $article['_continuation'] ) ? 'label' : 'headline';
                    $header_h = $block_header_h;
                    $seg_top  = $block_top; // 全列共通の見出し上端
                } else {
                    $mode     = 'none';
                    $header_h = 0.0;
                    $seg_top  = $y;
                }

                $body_h    = hatakiti_occult_pdf_body_segment_h( $article, $tier );
                $gap       = 'none' === $mode ? 0.0 : HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM;
                $src_h     = 'headline' === $mode ? HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM : 0.0;
                $seg_bottom = $seg_top + $header_h + $gap + $body_h + $src_h;
                if ( $seg_bottom > $page_bottom ) {
                    break;
                }

                $result = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article, $tier, $col_x[ $k ], $seg_top, $col_w_arr[ $k ], $header_h, $mode );
                $drew_this = true;

                $debug[] = array(
                    'page' => $page_no, 'block' => $block_index, 'cols' => $cols, 'col' => $k,
                    'article_id' => $article['_debug_article_id'] ?? null,
                    'tier' => $tier . ( $cols > 1 ? '(col' . $cols . '-' . ( $k + 1 ) . ')' : '' ),
                    'headline' => mb_substr( (string) ( $article['headline'] ?? '' ), 0, 16 ),
                    'x' => round( $col_x[ $k ], 1 ), 'y' => round( $seg_top, 1 ), 'w' => round( $col_w_arr[ $k ], 1 ), 'h' => round( $result['bottom_y'] - $seg_top, 1 ),
                    'continuation' => ! empty( $article['_continuation'] ),
                    'mode' => $mode,
                    'label_shown' => 'label' === $mode,
                    'is_first_in_block' => $is_first,
                    'body_top' => round( $seg_top + $header_h + $gap, 1 ),
                    'overflow' => ! empty( $result['overflow_body'] ),
                    'body_h_actual' => $result['body_h'] ?? round( $body_h, 1 ),
                    'body_used_width' => $result['body_used_width'] ?? 0.0,
                    'visual_bottom' => $result['visual_bottom'] ?? round( $result['bottom_y'], 1 ),
                );

                $pdf->SetLineWidth( 'large' === $tier && 1 === $cols ? 0.5 : 0.25 );
                $pdf->Line( $col_x[ $k ], $result['bottom_y'] + ( $row_gap / 2 ), $col_x[ $k ] + $col_w_arr[ $k ], $result['bottom_y'] + ( $row_gap / 2 ) );

                $y        = $result['bottom_y'] + $row_gap;
                $is_first = false;

                if ( ! empty( $result['overflow_body'] ) ) {
                    // この列の記事はまだ続く — 続きも必ず同じ列
                    // （同じX座標・同じ列幅）に留める。同一ページ内の
                    // この続きは、次のループでmode='none'（ラベル無し）
                    // として描かれる。
                    $continuation                  = $article;
                    $continuation['_continuation'] = true;
                    $continuation['body']          = hatakiti_occult_pdf_overflow_to_text( $result['overflow_body'] );
                    $article                       = $continuation;
                    continue;
                }
                $article = null; // この列の記事は完結した
                break;
            }

            $col_final[ $k ]  = $article;
            $col_bottom[ $k ] = $y;
            $col_drew[ $k ]   = $drew_this;
            if ( $drew_this ) {
                $drew_any = true;
            }
        }

        // ページ単位探索指示書§1-1：この時点（バックフィル前）で1列でも
        // 続きが必要（$col_final[$k]が非null）なら、この行内のどれかの
        // 記事がこのページ内で完結しない＝ページをまたぐ「途中分割」に
        // なる。呼び出し側（ページ単位探索）がこの行候補を除外できる
        // よう、フラグとして記録しておく（バックフィルは完結済みの列
        // だけを対象にするため、ここでの判定に影響しない）。
        $has_continuation = false;
        foreach ( $col_final as $cf ) {
            if ( null !== $cf ) {
                $has_continuation = true;
                break;
            }
        }

        // バックフィル（次段の空白削減・可変ブロック組版指示書§5/§7、
        // ページ途中空白削減指示書§1〜§4）：
        // ブロック内で記事が完結し空きが生じた列（＝その列の記事完結
        // 位置からページ末までの「空き矩形」）に、後続記事を、その矩形に
        // 完全に収まる範囲で詰め込めるだけ詰め込む。優先順位1（同tier）
        // →優先順位2（隣接tier、hatakiti_occult_pdf_fallback_tier()）の
        // 順で探す — 同tierの供給が尽きた場合のみ、隣接tierの記事で
        // 列を埋める。続きが必要な列（$col_final[$k]が非null）は対象に
        // しない — 列位置維持の既存仕様を壊さないため。
        //
        // 1ラウンドを「探索（コミットしない）→不均衡チェック→コミット」
        // の3フェーズに分ける。列ごとに見つかった候補を即座に確定させて
        // しまうと、「片方の列だけ大きく延長できて、もう片方の列には
        // 候補が1つも無い」場合に、候補の無い列がそのまま大きな空白
        // 矩形として取り残される（block_bottomは列の最大値になるため）。
        // これを防ぐため、候補の無い開いた列が存在する場合、その列の
        // 現在位置からHATAKITI_OCCULT_PDF_BACKFILL_IMBALANCE_GAP_MMを
        // 超えて他の列を延長する候補は、このラウンドでは見送る（キュー
        // に残したままにする）。見送られた記事は、このブロックが確定
        // した後、次のブロックの段組み判定で改めて配置候補になる —
        // 「現在ブロックに無理に詰め込む」のではなく「空白の原因になる
        // 延長を止め、次のブロックで適切な段組みを選び直させる」ことで
        // 空白そのものを縮める（ページ途中空白削減指示書§3）。
        $block_tier = $queue[0]['_tier'];
        $backfill_changed = true;
        while ( $backfill_changed ) {
            $backfill_changed = false;

            // フェーズ1：各開いている列について、コミットせずに最良候補
            // を探す。同一ラウンド内で複数の列が同じ記事を奪い合わない
            // よう、$claimedで確保済みのキュー添字を記録する。
            $plans   = array();
            $claimed = array();
            for ( $k = 0; $k < $cols; $k++ ) {
                $plans[ $k ] = null;
                if ( null !== $col_final[ $k ] ) {
                    continue;
                }
                if ( ( $page_bottom - $col_bottom[ $k ] ) <= HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM ) {
                    continue;
                }

                // 優先順位1（同tier）→優先順位2（隣接tier、指示書§5）の順で
                // 探す。同tierで1件でも見つかれば隣接tierは試さない。
                $best_j        = null;
                $best_est      = null;
                $best_header_h = 0.0;
                $best_tier     = null;
                $limit         = min( count( $queue ), $cols + HATAKITI_OCCULT_PDF_LOOKAHEAD_WINDOW );
                foreach ( array( $block_tier, hatakiti_occult_pdf_fallback_tier( $block_tier ) ) as $try_tier ) {
                    if ( null === $try_tier ) {
                        continue;
                    }
                    for ( $j = $cols; $j < $limit; $j++ ) {
                        if ( isset( $claimed[ $j ] ) || ! isset( $queue[ $j ] ) || $queue[ $j ]['_tier'] !== $try_tier || isset( $queue[ $j ]['_pinned_col_w'] ) ) {
                            continue;
                        }
                        $cand_header_h = hatakiti_occult_pdf_measure_first_segment_header_h( $pdf, $font_bold, $queue[ $j ], $try_tier, $col_w_arr[ $k ] );
                        $est           = hatakiti_occult_pdf_estimate_article_height( $queue[ $j ], $try_tier, $col_w_arr[ $k ], $cand_header_h, $col_bottom[ $k ], $page_bottom );
                        if ( $est['truncated'] ) {
                            continue;
                        }
                        if ( null === $best_est || $est['bottom'] < $best_est['bottom'] ) {
                            $best_j        = $j;
                            $best_est      = $est;
                            $best_header_h = $cand_header_h;
                            $best_tier     = $try_tier;
                        }
                    }
                    if ( null !== $best_j ) {
                        break;
                    }
                }

                if ( null === $best_j ) {
                    continue;
                }
                $claimed[ $best_j ] = true;
                $plans[ $k ]        = array(
                    'j'         => $best_j,
                    'est'       => $best_est,
                    'header_h'  => $best_header_h,
                    'tier'      => $best_tier,
                );
            }

            // フェーズ2：不均衡チェック。候補が1つも無い「取り残された」
            // 開いた列があれば、その最も浅い位置を基準に、他の列の延長を
            // 基準+閾値までに制限する。
            $stuck_floor = null;
            for ( $k = 0; $k < $cols; $k++ ) {
                if ( null !== $col_final[ $k ] ) {
                    continue;
                }
                if ( ( $page_bottom - $col_bottom[ $k ] ) <= HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM ) {
                    continue;
                }
                if ( null === $plans[ $k ] ) {
                    $stuck_floor = ( null === $stuck_floor ) ? $col_bottom[ $k ] : min( $stuck_floor, $col_bottom[ $k ] );
                }
            }
            if ( null !== $stuck_floor ) {
                for ( $k = 0; $k < $cols; $k++ ) {
                    if ( null === $plans[ $k ] ) {
                        continue;
                    }
                    if ( $plans[ $k ]['est']['bottom'] > $stuck_floor + HATAKITI_OCCULT_PDF_BACKFILL_IMBALANCE_GAP_MM ) {
                        $plans[ $k ] = null; // 見送り：候補の無い列を大きく置き去りにするため。
                    }
                }
            }

            // フェーズ3：残った有効プランをコミットする。キュー添字が
            // 大きいものから順にspliceし、小さい添字の位置がずれない
            // ようにする。
            $commit_ks = array();
            foreach ( $plans as $k => $plan ) {
                if ( null !== $plan ) {
                    $commit_ks[] = $k;
                }
            }
            usort(
                $commit_ks,
                function ( $a, $b ) use ( $plans ) {
                    return $plans[ $b ]['j'] <=> $plans[ $a ]['j'];
                }
            );

            foreach ( $commit_ks as $k ) {
                $plan      = $plans[ $k ];
                $candidate = $queue[ $plan['j'] ];
                array_splice( $queue, $plan['j'], 1 );

                $seg_top = $col_bottom[ $k ];
                $result  = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $candidate, $plan['tier'], $col_x[ $k ], $seg_top, $col_w_arr[ $k ], $plan['header_h'], 'headline' );

                $debug[] = array(
                    'page' => $page_no, 'block' => $block_index, 'cols' => $cols, 'col' => $k,
                    'article_id' => $candidate['_debug_article_id'] ?? null,
                    'tier' => $plan['tier'] . ( $cols > 1 ? '(col' . $cols . '-' . ( $k + 1 ) . ')' : '' ),
                    'headline' => mb_substr( (string) ( $candidate['headline'] ?? '' ), 0, 16 ),
                    'x' => round( $col_x[ $k ], 1 ), 'y' => round( $seg_top, 1 ), 'w' => round( $col_w_arr[ $k ], 1 ), 'h' => round( $result['bottom_y'] - $seg_top, 1 ),
                    'continuation' => false,
                    'mode' => 'headline',
                    'label_shown' => false,
                    'is_first_in_block' => false,
                    'body_top' => round( $seg_top + $plan['header_h'] + HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM, 1 ),
                    'overflow' => ! empty( $result['overflow_body'] ),
                    'body_h_actual' => $result['body_h'] ?? 0.0,
                    'body_used_width' => $result['body_used_width'] ?? 0.0,
                    'visual_bottom' => $result['visual_bottom'] ?? round( $result['bottom_y'], 1 ),
                    'backfill' => true,
                    'cross_tier' => ( $plan['tier'] !== $block_tier ),
                );

                $pdf->SetLineWidth( 0.25 );
                $pdf->Line( $col_x[ $k ], $result['bottom_y'] + ( $row_gap / 2 ), $col_x[ $k ] + $col_w_arr[ $k ], $result['bottom_y'] + ( $row_gap / 2 ) );

                $col_bottom[ $k ] = $result['bottom_y'] + $row_gap;
                $drew_any         = true;
                $backfill_changed = true;
            }
        }

        $block_bottom = max( $col_bottom );
        if ( $block_bottom <= $block_top ) {
            // どの列も1件も描画できなかった（先頭記事の最初のセグメント
            // すら残り高さに収まらない）— これ以上このページには載らない。
            return null;
        }

        // ブロックの外枠：列の境界線（ブロック全体の高さぶん）と、
        // ブロック終端の全幅罫線。
        if ( $cols > 1 ) {
            for ( $k = 1; $k < $cols; $k++ ) {
                $div_x = $col_x[ $k ] + $col_w_arr[ $k ] + ( $col_gap / 2 );
                $pdf->SetLineWidth( 0.25 );
                $pdf->Line( $div_x, $block_top, $div_x, $block_bottom );
            }
        }
        $pdf->SetLineWidth( 0.4 );
        $pdf->Line( $zone_x, $block_bottom, $zone_x + $zone_w, $block_bottom );

        // キュー更新：前から順に処理し、完結して取り除かれた列のぶんだけ
        // 後続のインデックスがずれることを考慮する。
        $removed_before = 0;
        for ( $k = 0; $k < $cols; $k++ ) {
            $idx = $k - $removed_before;
            if ( null === $col_final[ $k ] ) {
                array_splice( $queue, $idx, 1 );
                $removed_before++;
            } else {
                $queue[ $idx ] = $col_final[ $k ];
            }
        }

        return array( 'block_bottom' => $block_bottom, 'drew_any' => $drew_any, 'debug' => $debug, 'has_continuation' => $has_continuation );
}

/**
 * ページ途中空白削減指示書（第2弾）§候補例 — 現在のブロック（キュー
 * 先頭記事群）について、tier・同tier連続数・最小列幅の制約を満たす
 * 「許可された列構成」の候補一覧を返す（1列／均等2列／非対称2列
 * 2種／smallのみ均等3列）。1件も選ばず、勝者を決めるのは呼び出し側
 * （hatakiti_occult_pdf_choose_block_config_with_lookahead()）。
 * hatakiti_occult_pdf_decide_block_config_legacy()と同じ候補集合・
 * 同じ制約チェックを使うが、こちらは「全候補を残す」点だけが異なる。
 *
 * @return array 各要素が1つの$col_w_arr候補（mm単位の幅配列）。
 */
function hatakiti_occult_pdf_block_alt_candidates( $queue, $zone_w, $include_4col_small = false ) {
    if ( empty( $queue ) || isset( $queue[0]['_pinned_col_w'] ) ) {
        return array();
    }
    $tier = $queue[0]['_tier'];
    if ( 'large' === $tier ) {
        return array();
    }
    $configs = array(
        array( 1.0 ),
        array( 0.5, 0.5 ),
        array( 2 / 3, 1 / 3 ),
        array( 1 / 3, 2 / 3 ),
    );
    if ( 'small' === $tier ) {
        $configs[] = array( 1 / 3, 1 / 3, 1 / 3 );
        if ( $include_4col_small ) {
            // ページ単位探索指示書§5：small tier限定・試験的な均等4列候補。
            $configs[] = array( 0.25, 0.25, 0.25, 0.25 );
        }
    }
    $max_cols_needed = 1;
    foreach ( $configs as $fractions ) {
        $max_cols_needed = max( $max_cols_needed, count( $fractions ) );
    }
    $same_tier_run = 1;
    for ( $i = 1; $i < count( $queue ) && $i < $max_cols_needed; $i++ ) {
        if ( $queue[ $i ]['_tier'] !== $tier ) {
            break;
        }
        $same_tier_run++;
    }
    $col_gap = HATAKITI_OCCULT_PDF_ROW_COL_GAP_MM;
    $out     = array();
    foreach ( $configs as $fractions ) {
        $cols = count( $fractions );
        if ( $cols > $same_tier_run ) {
            continue;
        }
        $widths   = hatakiti_occult_pdf_fractions_to_widths( $fractions, $zone_w, $col_gap );
        $min_w_ok = ( 4 === $cols && 'small' === $tier )
            ? HATAKITI_OCCULT_PDF_SMALL_4COL_MIN_COL_W_MM
            : HATAKITI_OCCULT_PDF_MIN_COL_W_MM;
        if ( min( $widths ) < $min_w_ok ) {
            continue;
        }
        $out[] = $widths;
    }
    return $out;
}

/**
 * debug配列（hatakiti_occult_pdf_draw_one_block()の'debug'）から、
 * ブロック内で各列が実際に到達したY（col_bottom）を求め、
 * block_bottom（列の最大値）との差が通常の行間ギャップを明確に超える
 * 分だけを「内部空白」として面積合計を返す。バックフィルの列間不均衡
 * 修正（PDFページ途中空白削減指示書）で導入した空白検出と同じ考え方。
 */
function hatakiti_occult_pdf_debug_rows_internal_blank_area( $debug_rows, $block_bottom ) {
    $col_bottom = array();
    $col_w      = array();
    foreach ( $debug_rows as $r ) {
        if ( ! isset( $r['col'], $r['y'], $r['h'], $r['w'] ) ) {
            continue;
        }
        $bottom = $r['y'] + $r['h'];
        if ( ! isset( $col_bottom[ $r['col'] ] ) || $bottom > $col_bottom[ $r['col'] ] ) {
            $col_bottom[ $r['col'] ] = $bottom;
            $col_w[ $r['col'] ]      = $r['w'];
        }
    }
    $area = 0.0;
    foreach ( $col_bottom as $col_no => $bottom ) {
        $gap = $block_bottom - $bottom;
        if ( $gap > 1.0 ) {
            $area += $gap * $col_w[ $col_no ];
        }
    }
    return $area;
}

/* ============================================================
 * ページ内「空き矩形（Available Spaces）」方式への拡張 指示書
 * ============================================================
 * 既存のRow方式（ページを上から下へ複数の横長ブロックとして積む）は
 * 維持したまま、Row内で列ごとの記事完結位置がblock_bottomより浅い
 * 場合に生じる「空き矩形」を明示的に矩形として管理し、ページ単位
 * 探索の候補として後続記事をそこへ配置できるようにする（§2〜§5）。
 * 完全自由な2次元ビンパッキングにはせず、「既存Rowの列がそのまま
 * 使っていた矩形の、記事完結位置から下」という1パターンのみを対象と
 * する（§5-3/5-4）。列自体の左右分割（§5-1/5-2）は
 * hatakiti_occult_pdf_split_available_space()が対応するが、実際の
 * 候補生成（§4-5相当、hatakiti_occult_pdf_search_page_plan_recursive()
 * 内）では常に空きスペースの幅をそのまま使う（列幅を変えない）ため
 * 発生しない。
 */

/**
 * §4-1：ページ開始時点の初期空き矩形（ページコンテンツ領域全体）を
 * 1件返す。
 */
function hatakiti_occult_pdf_create_initial_available_space( $zone_x, $zone_y, $zone_w, $page_bottom ) {
    return array(
        array( 'x' => $zone_x, 'y' => $zone_y, 'width' => $zone_w, 'height' => $page_bottom - $zone_y ),
    );
}

/**
 * Row（hatakiti_occult_pdf_draw_one_block()のdebug配列）から、実際に
 * 描画された占有矩形（occupied_rects）を差し引いた残りを空き矩形と
 * して抽出する（実描画占有矩形ベースAvailable Spaces指示書§1・§6〜
 * §8）。2種類の空き矩形を検出する：
 *
 * ①列の高さ差（既存）：各列が実際に完結した位置（col_bottom）と
 *   Row全体のblock_bottomの差が通常の行間ギャップを明確に超える分。
 *
 * ②セグメント内の幅の余り（新規）：本文は縦書きの列0（右端）から
 *   左へ埋まるため、割り当てられた列幅よりも実際に使った本文幅
 *   （body_used_width、hatakiti_occult_pdf_draw_article_box()が返す
 *   実測値）が狭い場合、見出し帯の直下から本文終端までの高さぶん、
 *   左側に「記事ブロックの外接矩形だけを見ていては検出できない」
 *   空き矩形が残る（post_id=662 3ページ目「水浴びする謎の人影」の
 *   ような、見出しは全幅・本文は右側の狭い列しか使わないケース）。
 *   これが今回の指示書が重点的に検出対象とするケース。
 *
 * tierは列の最初のセグメントのtier文字列から "(colN-M)" 表記を除いた
 * ものを記録し、後続の同tier限定マッチングに使う（バックフィルと
 * 同じ方針、既存tierルールを破らないため）。
 */
function hatakiti_occult_pdf_row_leftover_spaces( $debug_rows, $row_bottom ) {
    $col_bottom = array();
    $col_x      = array();
    $col_w      = array();
    $col_tier   = array();
    $spaces     = array();
    foreach ( $debug_rows as $r ) {
        if ( ! isset( $r['col'], $r['y'], $r['h'], $r['w'], $r['x'] ) ) {
            continue;
        }
        // Row内部visual空白指示書§2〜§3：列の実際の到達位置は
        // visual_bottom（実描画の最深部、出典があればそこまで含む）を
        // 優先する。無ければ従来どおりbox_bottom（$r['y']+$r['h']）に
        // フォールバックする。
        $bottom = isset( $r['visual_bottom'] ) ? $r['visual_bottom'] : ( $r['y'] + $r['h'] );
        $tier   = preg_replace( '/\(col\d+-\d+\)$/', '', (string) ( $r['tier'] ?? '' ) );
        if ( ! isset( $col_bottom[ $r['col'] ] ) || $bottom > $col_bottom[ $r['col'] ] ) {
            $col_bottom[ $r['col'] ] = $bottom;
            $col_x[ $r['col'] ]      = $r['x'];
            $col_w[ $r['col'] ]      = $r['w'];
            $col_tier[ $r['col'] ]   = $tier;
        }

        // ②セグメント内の幅の余り。本文が右詰めで使った幅
        // （body_used_width）が割り当て幅（w）より明確に狭い場合のみ。
        if ( isset( $r['body_used_width'], $r['body_top'], $r['body_h_actual'] )
            && $r['body_h_actual'] > 1.0
            && ( $r['w'] - $r['body_used_width'] ) > 1.0
        ) {
            $spaces[] = array(
                'x'      => $r['x'], // 本文は右詰め＝空きは左側。
                'y'      => $r['body_top'],
                'width'  => $r['w'] - $r['body_used_width'],
                'height' => $r['body_h_actual'],
                'tier'   => $tier,
                // 左空白問題修正指示書§10：この種別（本文が右詰めで
                // 使った幅の余り）は構造的に必ず「その列の左側」に
                // 生じる空白のため、left_side_empty_area集計の対象と
                // して明示的にマークする。
                'kind'   => 'width_gap',
            );
        }
    }
    foreach ( $col_bottom as $col_no => $bottom ) {
        $h = $row_bottom - $bottom;
        if ( $h > 1.0 ) {
            $spaces[] = array(
                'x'      => $col_x[ $col_no ],
                'y'      => $bottom,
                'width'  => $col_w[ $col_no ],
                'height' => $h,
                'tier'   => $col_tier[ $col_no ],
            );
        }
    }
    return $spaces;
}

/**
 * §4-2：指定した記事を指定した空きスペースへ配置した場合のdry-run。
 * 既存のhatakiti_occult_pdf_estimate_article_height()をそのまま使い、
 * space.height（＝space.y + space.heightをページ末とみなす）を
 * 超える場合はtruncated扱いとする — 記事途中分割・スペースの高さ
 * 超過を絶対に許可しない（§1-1・§4-2）。
 *
 * @return array array('fits'=>bool,'article_height'=>mm,'bottom'=>mm,
 *   'truncated'=>bool,'header_h'=>mm)
 */
function hatakiti_occult_pdf_estimate_article_in_space( $pdf, $font_bold, $article, $tier, $space ) {
    $header_h        = hatakiti_occult_pdf_measure_first_segment_header_h( $pdf, $font_bold, $article, $tier, $space['width'] );
    $space_bottom     = $space['y'] + $space['height'];
    $est              = hatakiti_occult_pdf_estimate_article_height( $article, $tier, $space['width'], $header_h, $space['y'], $space_bottom );
    return array(
        'fits'           => ! $est['truncated'],
        'article_height' => $est['bottom'] - $space['y'],
        'bottom'         => $est['bottom'],
        'truncated'      => $est['truncated'],
        'header_h'       => $header_h,
    );
}

/**
 * §4-3：空きスペースに記事を配置した後、残った領域を新しい空き矩形群
 * として返す。今回許可する配置（§5-3/5-4）は「スペースの幅をそのまま
 * 使い、上端から記事の必要高さぶんを使う」パターンのみのため、
 * 残りは常に「同じx・同じ幅で、配置した記事の下」の1矩形（あれば）
 * だけになる。左右分割（§5-1/5-2）が必要になる配置（スペースの幅の
 * 一部だけを使う配置）は今回の候補生成では発生しないため、
 * $used_widthがspace.widthより狭い場合のみ左右の余り矩形も返す
 * （将来の配置パターン拡張に備えた一般化、§5-1/5-2）。
 */
function hatakiti_occult_pdf_split_available_space( $space, $used_height, $used_width = null, $align = 'left' ) {
    $tier = $space['tier'] ?? '';
    $kind = $space['kind'] ?? null; // 左空白問題修正指示書§10：由来種別を残りの矩形にも引き継ぐ。
    $out  = array();
    if ( null === $used_width || $used_width >= $space['width'] - 0.05 ) {
        $used_width = $space['width'];
    } else {
        $remaining_width = $space['width'] - $used_width;
        if ( $remaining_width > 0.5 ) {
            $side_x = ( 'right' === $align ) ? $space['x'] : $space['x'] + $used_width;
            $out[]  = array( 'x' => $side_x, 'y' => $space['y'], 'width' => $remaining_width, 'height' => $space['height'], 'tier' => $tier, 'kind' => $kind );
        }
    }
    $leftover_h = $space['height'] - $used_height;
    if ( $leftover_h > 1.0 ) {
        $used_x = ( 'right' === $align && $used_width < $space['width'] ) ? $space['x'] + ( $space['width'] - $used_width ) : $space['x'];
        $out[]  = array( 'x' => $used_x, 'y' => $space['y'] + $used_height, 'width' => $used_width, 'height' => $leftover_h, 'tier' => $tier, 'kind' => $kind );
    }
    return $out;
}

/**
 * §4-4：空き矩形リストを正規化する。width/heightが許容誤差以下の
 * ものを削除し、同じx・同じ幅で縦に隣接する矩形を結合する。
 */
function hatakiti_occult_pdf_normalize_available_spaces( $spaces ) {
    $filtered = array();
    foreach ( $spaces as $s ) {
        if ( $s['width'] <= 0.5 || $s['height'] <= 1.0 ) {
            continue;
        }
        $filtered[] = $s;
    }
    usort(
        $filtered,
        function ( $a, $b ) {
            return $a['y'] <=> $b['y'];
        }
    );
    $merged = array();
    foreach ( $filtered as $s ) {
        $last = count( $merged ) - 1;
        if ( $last >= 0
            && abs( $merged[ $last ]['x'] - $s['x'] ) < 0.1
            && abs( $merged[ $last ]['width'] - $s['width'] ) < 0.1
            && abs( ( $merged[ $last ]['y'] + $merged[ $last ]['height'] ) - $s['y'] ) < 0.5
            && ( $merged[ $last ]['kind'] ?? null ) === ( $s['kind'] ?? null )
        ) {
            $merged[ $last ]['height'] += $s['height'];
        } else {
            $merged[] = $s;
        }
    }
    return $merged;
}

/**
 * ページ途中空白削減指示書（第2弾）§1〜§3 —「現在のブロックを
 * block_bottomまで確定してから次のブロックを始める」処理を最適解と
 * みなさず、直近2ブロック分（現在ブロック＋次ブロック）をdry-runで
 * まとめて比較し、ページ全体として空白の少ない列構成を選ぶ。
 *
 * 対象は現在ブロックの列構成のみ（次ブロックは常に既存の自動決定＝
 * hatakiti_occult_pdf_decide_block_config()に委ねる）。候補は
 * 「自然候補（既存ロジックがそのまま選ぶ構成）」と
 * hatakiti_occult_pdf_block_alt_candidates()が返す許可された代替構成
 * （1列／均等2列／非対称2列2種／smallのみ均等3列）。各候補について
 * 「現在ブロック＋次ブロック」を使い捨てTCPDFでdry-run実描画し、
 * ブロック内部空白（列間不均衡）の合計面積で比較する。自然候補より
 * 明確に（0.5mm²超）優れる代替が無ければ、自然候補のまま変更しない
 * （記事順序・列構成の不要な変更を避ける、指示書§重要）。
 *
 * 続き記事（_pinned_col_w）・large tierは列構成が既存仕様で固定される
 * ため対象外とし、常にnullを返す（呼び出し側は
 * hatakiti_occult_pdf_decide_block_config()の自動決定をそのまま使う）。
 *
 * @return array|null 採用する$col_w_arr。自然候補のままでよい場合は
 *   null（＝呼び出し側がdecide_block_config()を自前で呼ぶ）。
 */
function hatakiti_occult_pdf_choose_block_config_with_lookahead( $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $block_top, $page_bottom, $page_no, $block_index ) {
    if ( ! HATAKITI_OCCULT_PDF_ALLOW_BLOCK_BOUNDARY_LOOKAHEAD ) {
        return null;
    }
    if ( empty( $queue ) || isset( $queue[0]['_pinned_col_w'] ) || 'large' === $queue[0]['_tier'] ) {
        return null;
    }

    $queue_for_natural = $queue; // 値渡しのコピー — decide_block_config()は&$queueを取るため。
    $natural           = hatakiti_occult_pdf_decide_block_config( $pdf, $font_bold, $queue_for_natural, $zone_w, $page_bottom, $block_top );

    $alt_candidates = hatakiti_occult_pdf_block_alt_candidates( $queue, $zone_w );
    $candidates      = array( $natural );
    foreach ( $alt_candidates as $alt ) {
        $dup = false;
        foreach ( $candidates as $existing ) {
            if ( count( $existing ) !== count( $alt ) ) {
                continue;
            }
            $same = true;
            foreach ( $existing as $i => $w ) {
                if ( abs( $w - $alt[ $i ] ) > 0.05 ) {
                    $same = false;
                    break;
                }
            }
            if ( $same ) {
                $dup = true;
                break;
            }
        }
        if ( ! $dup ) {
            $candidates[] = $alt;
        }
    }

    if ( count( $candidates ) <= 1 ) {
        return null; // 代替候補が無ければ、無駄なdry-runをせず自然候補のまま。
    }

    $scores = array();
    foreach ( $candidates as $idx => $cand ) {
        $queue_copy = $queue;
        list( $scratch_pdf, $scratch_font_regular, $scratch_font_bold ) = hatakiti_occult_pdf_new_tcpdf();
        $scratch_pdf->AddPage();
        $block1 = hatakiti_occult_pdf_draw_one_block( $queue_copy, $scratch_pdf, $scratch_font_regular, $scratch_font_bold, $zone_x, $zone_w, $block_top, $page_bottom, $page_no, $block_index, $cand );
        if ( null === $block1 ) {
            continue; // この構成では何も描画できない＝無効。
        }
        $score = hatakiti_occult_pdf_debug_rows_internal_blank_area( $block1['debug'], $block1['block_bottom'] );

        if ( ! empty( $queue_copy ) && ( $page_bottom - $block1['block_bottom'] ) > HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM ) {
            $block2 = hatakiti_occult_pdf_draw_one_block( $queue_copy, $scratch_pdf, $scratch_font_regular, $scratch_font_bold, $zone_x, $zone_w, $block1['block_bottom'], $page_bottom, $page_no, $block_index + 1, null );
            if ( null !== $block2 ) {
                $score += hatakiti_occult_pdf_debug_rows_internal_blank_area( $block2['debug'], $block2['block_bottom'] );
                if ( empty( $queue_copy ) ) {
                    // このブロックの並びでページの記事が尽きる＝末尾の
                    // トレーリング空白も比較対象に含める。
                    $score += ( $page_bottom - $block2['block_bottom'] ) * $zone_w;
                }
            }
        } elseif ( empty( $queue_copy ) ) {
            $score += ( $page_bottom - $block1['block_bottom'] ) * $zone_w;
        }

        $scores[ $idx ] = $score;
    }

    if ( ! isset( $scores[0] ) ) {
        return null; // 自然候補自体が無効になることは通常無いはずだが、念のため。
    }

    $best_idx   = 0;
    $best_score = $scores[0];
    foreach ( $scores as $idx => $score ) {
        if ( 0 === $idx ) {
            continue;
        }
        if ( $score < $best_score - 0.5 ) {
            $best_score = $score;
            $best_idx   = $idx;
        }
    }

    return ( 0 === $best_idx ) ? null : $candidates[ $best_idx ];
}

/* ============================================================
 * ページ単位探索（Page-Level Layout Search）指示書
 * ============================================================
 * hatakiti_occult_pdf_choose_block_config_with_lookahead()
 * （直近2ブロックのみのdry-run比較）が実データで大域的なページ数
 * 悪化を起こしたことを踏まえ、今回はページ全体（現在ページの残り
 * 高さに収まる複数行＝PagePlan）を1つの単位として、既存アルゴリズム
 * （Baseline Plan）と比較する。採用は「明確な改善がある場合のみ」
 * とし、採用前に「このページ以降、キューが尽きるまでに必要な
 * ページ数」を軽量にシミュレートしてBaselineと比較する
 * （Priority 1、hatakiti_occult_pdf_count_pages_to_exhaust_queue()）。
 * これによりhatakiti_occult_pdf_choose_block_config_with_lookahead()
 * で起きたような大域的なページ数悪化を採用前に検出できる。
 */

/**
 * ページ単位探索の再帰候補生成中、$hatakiti_occult_pdf_suppress_page_search
 * がtrueの間は、hatakiti_occult_pdf_stack_articles()内で
 * ページ単位探索・2ブロック先読みのいずれも呼び出さない
 * （hatakiti_occult_pdf_count_pages_to_exhaust_queue()が使う軽量
 * シミュレーションの中で、探索が探索を呼ぶ多重再帰・計算量爆発を
 * 防ぐための安全弁）。
 */
$GLOBALS['hatakiti_occult_pdf_suppress_page_search'] = false;

/**
 * ページ単位探索指示書§4／空き矩形拡張指示書§8：現在のキュー先頭
 * 記事群について、PagePlanの「Row」候補をすべて成立させたうえで、
 * 再帰的に「ここでプランを終了する」候補、「もう1行足す」候補、
 * 「既存の空き矩形へ後続記事を配置する」候補の3種類を列挙する。
 *
 * 各Row候補はhatakiti_occult_pdf_draw_one_block()で実際にdry-run
 * 描画し、①ページ内に完全に収まる（truncatedでない）②途中分割が
 * 発生しない（has_continuation===false）の両方を満たすものだけを
 * 採用する（§1-1）。large tierは既存仕様どおり自然決定（全幅）の
 * まま1行として受け入れ、そこから先だけを探索する（§1-4）。続き
 * 記事（_pinned_col_w）に達したら、それ以上は探索しない（§1-3、
 * 呼び出し側で先に固定描画済みの前提）。
 *
 * 空き矩形候補（空き矩形拡張指示書§4-5・§5-3/5-4）：Rowを追加する
 * たびに、そのRow内で発生した列ごとの空き矩形
 * （hatakiti_occult_pdf_row_leftover_spaces()）をavailable_spacesへ
 * 積み上げる。各再帰ノードでは、その時点で最大面積の空き矩形1件を
 * 対象に、キュー先頭からLOOKAHEAD_WINDOW件以内・同tier・large tier
 * 以外の記事を試し、収まる候補が見つかるたびに1つの新しい再帰枝を
 * 作る（探索爆発防止のため、対象空き矩形は常に最大の1件のみ、成立
 * 候補も先頭から数件までに制限）。空き矩形へ配置してもyは進めない
 * （Rowの積み上げとは独立した操作のため）。
 *
 * @param array    &$candidates      結果を追加する配列（参照）。各要素は
 *   array('rows'=>[...], 'placements'=>[...], 'available_spaces'=>[...],
 *         'final_y'=>mm, 'remaining_queue'=>array, 'order_jump'=>int)。
 * @param int      &$candidate_count 生成済み候補数（参照、上限管理用）。
 */
function hatakiti_occult_pdf_search_page_plan_recursive( &$candidates, &$candidate_count, $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $y, $page_bottom, $page_no, $block_index_base, $rows_so_far, $available_spaces, $placements_so_far, $order_jump_so_far, $consecutive_space_fills, $depth ) {
    if ( ! empty( $rows_so_far ) || ! empty( $placements_so_far ) ) {
        $candidates[] = array(
            'rows'             => $rows_so_far,
            'placements'       => $placements_so_far,
            'available_spaces' => $available_spaces,
            'final_y'          => $y,
            'remaining_queue'  => $queue,
            'order_jump'       => $order_jump_so_far,
        );
        $candidate_count++;
    }
    if ( $candidate_count >= HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_CANDIDATES ) {
        return;
    }
    if ( $depth >= HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_ROWS ) {
        return;
    }

    // 分岐A（PagePlan探索仕様§6〜§8）：既存の空き矩形すべてを対象に、
    // 後続記事（LOOKAHEAD_WINDOW以内・同tier・large tier以外）を試す。
    // §14-4（order_jump上限）・§14-5（連続Space Fill回数上限）・
    // §19-3（最低改善面積）の安全弁を先に確認してから候補化する。
    // Rowの積み上げとは独立した操作のため、$yは変更しない。
    if ( ! empty( $available_spaces ) && ! empty( $queue )
        && $order_jump_so_far < HATAKITI_OCCULT_PDF_PAGE_SEARCH_ORDER_JUMP_LIMIT
        && $consecutive_space_fills < HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_CONSECUTIVE_SPACE_FILLS
    ) {
        $limit = min( count( $queue ), HATAKITI_OCCULT_PDF_LOOKAHEAD_WINDOW );
        foreach ( $available_spaces as $space_idx => $space ) {
            if ( $candidate_count >= HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_CANDIDATES ) {
                return;
            }
            $tried = 0;
            for ( $j = 0; $j < $limit && $tried < 3; $j++ ) {
                if ( ! isset( $queue[ $j ] ) || isset( $queue[ $j ]['_pinned_col_w'] ) ) {
                    continue;
                }
                if ( $order_jump_so_far + $j > HATAKITI_OCCULT_PDF_PAGE_SEARCH_ORDER_JUMP_LIMIT ) {
                    continue; // §14-4：累積order_jump上限。
                }
                $cand_tier = $queue[ $j ]['_tier'];
                if ( 'large' === $cand_tier ) {
                    continue; // §1-4相当：large tierは空き矩形へ配置しない。
                }
                // Space Fillはバックフィルとは別の仕組み — 既に「他の
                // 記事の本文が使わなかった余り幅」であり、埋めなければ
                // そのまま無駄になる領域を対象にするため、バックフィル
                // で確認された「medium列がsmallを先食いして後のページが
                // 悪化する」リスク（HATAKITI_OCCULT_PDF_ALLOW_CROSS_TIER_
                // BACKFILLのコメント参照）は当てはまらない。仮に先食い
                // が起きても、採用前に必ずhatakiti_occult_pdf_should_
                // adopt_page_plan()のPriority1（総ページ数悪化チェック）
                // を通るため、同tier優先→隣接tier
                // （hatakiti_occult_pdf_fallback_tier()、medium⇄small）
                // の順で候補化してよい。
                // 左空白問題修正指示書§5〜§6：spaceの由来がlarge tier行
                // （1列＝全幅の行で、他に同じ行内の列が無い）の場合、
                // 同tier／隣接tier限定ルールは「同じ行内の他列との視覚的
                // 整合性」を守るためのものであり、そもそも比較対象となる
                // 他列が存在しないlarge由来の空きには当てはまらない。
                // ここで弾くと、body_used_widthベースで正しく検出された
                // 空き矩形（post_id=662 1面のようなケース）が構造的に
                // 常に埋まらなくなる（hatakiti_occult_pdf_space_fill_
                // fallback_tier('large')が常にnullを返すため）。幅・高さ
                // に実際に収まるかはこの後のest['fits']が判定するので、
                // ここではtierだけを理由に除外しない。
                $space_tier = $space['tier'] ?? '';
                if ( 'large' !== $space_tier
                    && '' !== $space_tier
                    && $cand_tier !== $space_tier
                    && $cand_tier !== hatakiti_occult_pdf_space_fill_fallback_tier( $space_tier )
                ) {
                    continue;
                }
                $est = hatakiti_occult_pdf_estimate_article_in_space( $pdf, $font_bold, $queue[ $j ], $cand_tier, $space );
                if ( ! $est['fits'] ) {
                    continue;
                }
                if ( ( $est['article_height'] * $space['width'] ) < HATAKITI_OCCULT_PDF_PAGE_SEARCH_MIN_SPACE_FILL_IMPROVEMENT_MM2 ) {
                    continue; // §19-3：改善面積が小さすぎる詰め込みは候補化しない。
                }
                $tried++;

                $new_queue      = $queue;
                $placed_article = $queue[ $j ];
                array_splice( $new_queue, $j, 1 );

                $new_spaces = $available_spaces;
                array_splice( $new_spaces, $space_idx, 1 );
                $leftover   = hatakiti_occult_pdf_split_available_space( $space, $est['article_height'] );
                $new_spaces = hatakiti_occult_pdf_normalize_available_spaces( array_merge( $new_spaces, $leftover ) );

                $new_placements   = $placements_so_far;
                $new_placements[] = array(
                    'article_id' => $placed_article['_debug_article_id'] ?? null,
                    'x'          => $space['x'],
                    'y'          => $space['y'],
                    'width'      => $space['width'],
                    'tier'       => $cand_tier,
                    'header_h'   => $est['header_h'],
                    'order_jump' => $j,
                );

                hatakiti_occult_pdf_search_page_plan_recursive( $candidates, $candidate_count, $new_queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $y, $page_bottom, $page_no, $block_index_base, $rows_so_far, $new_spaces, $new_placements, $order_jump_so_far + $j, $consecutive_space_fills + 1, $depth + 1 );
                if ( $candidate_count >= HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_CANDIDATES ) {
                    return;
                }
            }
        }
    }

    if ( $candidate_count >= HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_CANDIDATES ) {
        return;
    }
    if ( empty( $queue ) || ( $page_bottom - $y ) <= HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM ) {
        return;
    }
    if ( isset( $queue[0]['_pinned_col_w'] ) ) {
        return; // §1-3：続き記事はページ単位探索の対象外。
    }

    // 分岐B：従来のRow追加。Rowを追加したら連続Space Fill回数は
    // リセットする（間にRowを挟むため、§14-5の「連続」に該当しない）。
    $tier = $queue[0]['_tier'];
    if ( 'large' === $tier ) {
        // §1-4：large記事の列構成は変更しない。自然決定（常に全幅）の
        // 結果を1行として受け入れ、その先だけを探索対象にする。
        $queue_copy = $queue;
        list( $scratch_pdf, $scratch_font_regular, $scratch_font_bold ) = hatakiti_occult_pdf_new_tcpdf();
        $scratch_pdf->AddPage();
        $result = hatakiti_occult_pdf_draw_one_block( $queue_copy, $scratch_pdf, $scratch_font_regular, $scratch_font_bold, $zone_x, $zone_w, $y, $page_bottom, $page_no, $block_index_base + $depth + 1, array( $zone_w ) );
        if ( null === $result || $result['has_continuation'] ) {
            return;
        }
        $new_rows      = $rows_so_far;
        $new_rows[]    = array( 'top' => $y, 'bottom' => $result['block_bottom'], 'col_w_arr' => array( $zone_w ), 'debug' => $result['debug'] );
        $row_leftovers = hatakiti_occult_pdf_row_leftover_spaces( $result['debug'], $result['block_bottom'] );
        $new_spaces    = hatakiti_occult_pdf_normalize_available_spaces( array_merge( $available_spaces, $row_leftovers ) );
        hatakiti_occult_pdf_search_page_plan_recursive( $candidates, $candidate_count, $queue_copy, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $result['block_bottom'], $page_bottom, $page_no, $block_index_base, $new_rows, $new_spaces, $placements_so_far, $order_jump_so_far, 0, $depth + 1 );
        return;
    }

    $row_candidates = hatakiti_occult_pdf_block_alt_candidates( $queue, $zone_w, ( 'small' === $tier ) );
    foreach ( $row_candidates as $col_w_arr ) {
        if ( $candidate_count >= HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_CANDIDATES ) {
            return;
        }
        $queue_copy = $queue;
        list( $scratch_pdf, $scratch_font_regular, $scratch_font_bold ) = hatakiti_occult_pdf_new_tcpdf();
        $scratch_pdf->AddPage();
        $result = hatakiti_occult_pdf_draw_one_block( $queue_copy, $scratch_pdf, $scratch_font_regular, $scratch_font_bold, $zone_x, $zone_w, $y, $page_bottom, $page_no, $block_index_base + $depth + 1, $col_w_arr );
        if ( null === $result || $result['has_continuation'] ) {
            continue; // §1-1：収まらない、または途中分割が発生する候補は除外。
        }
        $new_rows      = $rows_so_far;
        $new_rows[]    = array( 'top' => $y, 'bottom' => $result['block_bottom'], 'col_w_arr' => $col_w_arr, 'debug' => $result['debug'] );
        $row_leftovers = hatakiti_occult_pdf_row_leftover_spaces( $result['debug'], $result['block_bottom'] );
        $new_spaces    = hatakiti_occult_pdf_normalize_available_spaces( array_merge( $available_spaces, $row_leftovers ) );
        hatakiti_occult_pdf_search_page_plan_recursive( $candidates, $candidate_count, $queue_copy, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $result['block_bottom'], $page_bottom, $page_no, $block_index_base, $new_rows, $new_spaces, $placements_so_far, $order_jump_so_far, 0, $depth + 1 );
    }
}

/**
 * 既存アルゴリズム（hatakiti_occult_pdf_draw_one_block()の自然決定
 * ＝decide_block_config()）だけを使って、現在ページの残りを最後まで
 * dry-run実行し、PagePlanと同じ形（'rows'/'final_y'/'remaining_queue'）
 * で返す。ページ単位探索の比較基準（Baseline Plan）として使う
 * （§8「Baselineとの比較」）。
 */
function hatakiti_occult_pdf_dry_run_baseline_plan( $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $start_y, $page_bottom, $page_no, $block_index_base ) {
    $queue_copy = $queue;
    list( $scratch_pdf, $scratch_font_regular, $scratch_font_bold ) = hatakiti_occult_pdf_new_tcpdf();
    $scratch_pdf->AddPage();
    $rows             = array();
    $available_spaces = array();
    $y                = $start_y;
    $block_index      = $block_index_base;
    $safety           = 0;
    while ( ! empty( $queue_copy ) && ( $page_bottom - $y ) > HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM && $safety < 20 ) {
        $safety++;
        $block_index++;
        $result = hatakiti_occult_pdf_draw_one_block( $queue_copy, $scratch_pdf, $scratch_font_regular, $scratch_font_bold, $zone_x, $zone_w, $y, $page_bottom, $page_no, $block_index, null );
        if ( null === $result ) {
            break;
        }
        $rows[]           = array( 'top' => $y, 'bottom' => $result['block_bottom'], 'debug' => $result['debug'] );
        $row_leftovers    = hatakiti_occult_pdf_row_leftover_spaces( $result['debug'], $result['block_bottom'] );
        $available_spaces = hatakiti_occult_pdf_normalize_available_spaces( array_merge( $available_spaces, $row_leftovers ) );
        $y                = $result['block_bottom'];
    }
    return array(
        'rows'             => $rows,
        'placements'       => array(),
        'available_spaces' => $available_spaces,
        'final_y'          => $y,
        'remaining_queue'  => $queue_copy,
        'order_jump'       => 0,
    );
}

/**
 * ページ単位探索指示書§6：PagePlan（Baseline／Candidate共通の形式）を
 * 評価し、評価指標一式を返す。個別Rowではなくページ全体（全Row合算）
 * を評価する（§3-3）。
 */
function hatakiti_occult_pdf_evaluate_page_plan( $plan, $zone_w, $page_bottom, $pdf = null, $font_bold = null ) {
    $rows       = $plan['rows'];
    $placements = $plan['placements'] ?? array();
    if ( empty( $rows ) && empty( $placements ) ) {
        return array(
            'valid'                   => false,
            'article_count'           => 0,
            'remaining_height'        => max( 0, $page_bottom - $plan['final_y'] ),
            'largest_empty_rect_area' => 0.0,
            'total_blank_area'        => 0.0,
            'usable_empty_area'       => 0.0,
            'left_side_empty_area'    => 0.0,
            'layout_switch_count'     => 0,
            'row_count'               => 0,
            'column_imbalance_score'  => 0.0,
            'order_jump'              => $plan['order_jump'] ?? 0,
            'clipped_article_count'   => 0,
            'isolated_fragment_count' => 0,
        );
    }

    $article_ids  = array();
    $prev_cols    = null;
    $switch_count = 0;
    $imbalance_total = 0.0;
    // Rowの内部占有領域再利用指示書§13：clipped_article_count。Row候補は
    // has_continuation===trueの時点でhatakiti_occult_pdf_search_page_
    // plan_recursive()が候補化前に除外している（§1-1）ため、
    // rows_so_farに含まれるRowは常にhas_continuation===false（列内の
    // 記事がすべて完結済み）である。ただし個々のdebug行の'overflow'は
    // 「その列内の“次のセグメント”へ続くか」を示すだけで、同じ列の
    // 最後のセグメントで完結していれば正常（1記事が同一ページ内で
    // 複数セグメントに分かれるのは通常の挙動であり、途中で消える
    // クリッピングではない）。そのため、各(行, 列)ごとに最も深い
    // （＝最後の）セグメントのoverflowのみを判定対象にする — 途中の
    // セグメントのoverflow=trueを誤ってクリッピングとして数えない。
    $clipped_article_count = 0;

    foreach ( $rows as $row ) {
        $col_bottom  = array();
        $col_last_of = array(); // col => 最も深いセグメントのoverflowフラグ
        foreach ( $row['debug'] as $r ) {
            if ( ! isset( $r['col'], $r['y'], $r['h'] ) ) {
                continue;
            }
            $b = $r['y'] + $r['h'];
            if ( ! isset( $col_bottom[ $r['col'] ] ) || $b > $col_bottom[ $r['col'] ] ) {
                $col_bottom[ $r['col'] ]  = $b;
                $col_last_of[ $r['col'] ] = ! empty( $r['overflow'] );
            }
            if ( isset( $r['article_id'] ) && null !== $r['article_id'] ) {
                $article_ids[ $r['article_id'] ] = true;
            }
        }
        foreach ( $col_last_of as $has_overflow_at_end ) {
            if ( $has_overflow_at_end ) {
                $clipped_article_count++;
            }
        }
        if ( count( $col_bottom ) > 1 ) {
            $imbalance_total += ( max( $col_bottom ) - min( $col_bottom ) );
        }

        $cols = count( $col_bottom );
        if ( null !== $prev_cols && $cols !== $prev_cols ) {
            $switch_count++;
        }
        $prev_cols = $cols;
    }
    // Rowの内部占有領域再利用指示書§13：isolated_fragment_count。
    // Space Fillはhatakiti_occult_pdf_estimate_article_in_space()が
    // truncated===falseの記事しか候補化しないため（§7-1「完全配置」
    // のみを実装し、§7-2「部分配置」は実装していない）、placementsに
    // 記事の一部だけが置かれることは構造的に発生しない。防御的に
    // 明示カウントする（常に0であるべき）。
    $isolated_fragment_count = 0;
    foreach ( $placements as $p ) {
        if ( isset( $p['article_id'] ) && null !== $p['article_id'] ) {
            $article_ids[ $p['article_id'] ] = true;
        }
        if ( ! empty( $p['fragment'] ) ) {
            $isolated_fragment_count++;
        }
    }

    // 空き矩形拡張指示書§9-1：最大空白矩形／総空白面積は、Rowの内部
    // 空白ではなく、その時点で残っているavailable_spaces（Row内部の
    // 空白のうち、後続配置でまだ埋まっていない分）を正とする。空き
    // 矩形へ配置した分はavailable_spacesから既に除かれているため、
    // 二重計上しない（旧来のhatakiti_occult_pdf_debug_rows_internal_
    // blank_area()による再計算はしない）。
    $available_spaces = $plan['available_spaces'] ?? array();
    $largest_rect_area = 0.0;
    $total_blank_area  = 0.0;
    // 左空白問題修正指示書§10：left_side_empty_area — 本文が縦書きの
    // 右詰めで使った幅の余り（'kind'==='width_gap'、必ずその列の左側に
    // 生じる、hatakiti_occult_pdf_row_leftover_spaces()参照）だけを
    // 合計する。①の列高さ差（行の下端の余り）は左右どちらの偏りでも
    // ないため対象外。
    $left_side_empty_area = 0.0;
    foreach ( $available_spaces as $sp ) {
        $area = $sp['width'] * $sp['height'];
        $total_blank_area += $area;
        $largest_rect_area = max( $largest_rect_area, $area );
        if ( 'width_gap' === ( $sp['kind'] ?? null ) ) {
            $left_side_empty_area += $area;
        }
    }

    $final_y          = $plan['final_y'];
    $remaining_height = max( 0, $page_bottom - $final_y );
    $trailing_area    = $remaining_height * $zone_w;
    $total_blank_area += $trailing_area;
    $largest_rect_area = max( $largest_rect_area, $trailing_area );

    // §15-6：usable_empty_area — remaining_queueのLOOKAHEAD_WINDOW内に
    // 少なくとも1記事を完全収容できる空白矩形の合計面積。診断専用の
    // 補助指標（採用可否の直接条件にはしない、§16）。コストを抑える
    // ため、各矩形につき先頭3件までのみ確認する。
    $usable_empty_area = 0.0;
    if ( null !== $pdf && null !== $font_bold && ! empty( $available_spaces ) && ! empty( $plan['remaining_queue'] ) ) {
        $remaining_queue = $plan['remaining_queue'];
        $check_limit     = min( count( $remaining_queue ), 3 );
        foreach ( $available_spaces as $sp ) {
            for ( $k = 0; $k < $check_limit; $k++ ) {
                if ( isset( $remaining_queue[ $k ]['_pinned_col_w'] ) ) {
                    continue;
                }
                $cand_tier = $remaining_queue[ $k ]['_tier'];
                if ( 'large' === $cand_tier ) {
                    continue;
                }
                // 左空白問題修正指示書§5〜§6：実際のspace-fill探索
                // （hatakiti_occult_pdf_search_page_plan_recursive()）と
                // 同じ基準に揃える — large由来のspaceは同tier限定にしない。
                $sp_tier = $sp['tier'] ?? '';
                if ( 'large' !== $sp_tier && '' !== $sp_tier && $cand_tier !== $sp_tier ) {
                    continue;
                }
                $est = hatakiti_occult_pdf_estimate_article_in_space( $pdf, $font_bold, $remaining_queue[ $k ], $cand_tier, $sp );
                if ( $est['fits'] ) {
                    $usable_empty_area += $sp['width'] * $sp['height'];
                    break;
                }
            }
        }
    }

    return array(
        'valid'                   => true,
        'article_count'           => count( $article_ids ),
        'remaining_height'        => round( $remaining_height, 1 ),
        'largest_empty_rect_area' => round( $largest_rect_area, 1 ),
        'total_blank_area'        => round( $total_blank_area, 1 ),
        'usable_empty_area'       => round( $usable_empty_area, 1 ),
        'left_side_empty_area'    => round( $left_side_empty_area, 1 ),
        'layout_switch_count'     => $switch_count,
        'row_count'               => count( $rows ),
        'column_imbalance_score'  => round( $imbalance_total, 1 ),
        'order_jump'              => $plan['order_jump'] ?? 0,
        'clipped_article_count'   => $clipped_article_count,
        'isolated_fragment_count' => $isolated_fragment_count,
    );
}

/**
 * ページ単位探索指示書§3-4：2つのPagePlanの評価指標を比較し、$aが$bより
 * 良ければtrueを返す（候補集合の中から最良の1件を選ぶための内部比較。
 * BaselineとCandidateの採用判定はhatakiti_occult_pdf_should_adopt_page_plan()
 * が別途、より厳格な基準で行う）。
 */
function hatakiti_occult_pdf_compare_page_plans( $a, $b ) {
    if ( null === $b ) {
        return true;
    }
    // Rowの内部占有領域再利用指示書§14 優先順位1・2：clipping・孤立
    // 断片は他の全指標より絶対優先で少ない方を選ぶ（本設計では常に
    // 0のはずだが、防御的に最優先で評価する）。
    if ( $a['clipped_article_count'] !== $b['clipped_article_count'] ) {
        return $a['clipped_article_count'] < $b['clipped_article_count'];
    }
    if ( $a['isolated_fragment_count'] !== $b['isolated_fragment_count'] ) {
        return $a['isolated_fragment_count'] < $b['isolated_fragment_count'];
    }
    // PagePlan探索仕様§16の優先順位（辞書式比較）。記事数について、
    // 記事サイズ差による不公平を避けるため「配置済み記事数が同じ場合
    // のみ以降を比較する」（§16後段）—つまり記事数が異なる場合は
    // 記事数の多寡のみで決める。
    if ( $a['article_count'] !== $b['article_count'] ) {
        return $a['article_count'] > $b['article_count'];
    }
    if ( $a['remaining_height'] < $b['remaining_height'] - 0.5 ) {
        return true;
    }
    if ( $a['remaining_height'] > $b['remaining_height'] + 0.5 ) {
        return false;
    }
    // 面積系の指標（mm²）は長さ系（mm）よりケタが大きいため、同じ
    // ±0.5の許容誤差では「数百mm²程度のノイズレベルの差」だけで決着
    // してしまい、より優先度の低い指標（段組み切替回数など）が一切
    // 考慮されなくなる（実データpost=662 3ページ目で、262.7mm²の差
    // だけで段組み切替が少ない候補が不採用になる問題を実際に確認した
    // ため修正）。HATAKITI_OCCULT_PDF_PAGE_SEARCH_AREA_TIE_TOLERANCE_MM2
    // 未満の差は「同等」とみなし、次の優先順位へ進める。
    $area_tol = HATAKITI_OCCULT_PDF_PAGE_SEARCH_AREA_TIE_TOLERANCE_MM2;
    if ( $a['largest_empty_rect_area'] < $b['largest_empty_rect_area'] - $area_tol ) {
        return true;
    }
    if ( $a['largest_empty_rect_area'] > $b['largest_empty_rect_area'] + $area_tol ) {
        return false;
    }
    if ( $a['usable_empty_area'] < $b['usable_empty_area'] - $area_tol ) {
        return true;
    }
    if ( $a['usable_empty_area'] > $b['usable_empty_area'] + $area_tol ) {
        return false;
    }
    if ( $a['total_blank_area'] < $b['total_blank_area'] - $area_tol ) {
        return true;
    }
    if ( $a['total_blank_area'] > $b['total_blank_area'] + $area_tol ) {
        return false;
    }
    // 左空白問題修正指示書§10・§11：left_side_empty_areaはevaluate_
    // page_plan()で計測・報告するが、候補比較の優先順位には組み込まない
    // （意図的）。実データ検証（post_id=662）で、この指標をtotal_blank_
    // area直後の優先順位に加えると、ページ内では左空白が少ない候補が
    // 選ばれる一方、そのページで消費する記事の組み合わせが変わり、
    // 数ページ先で総ページ数が悪化する（6→7ページ）ケースを実際に確認
    // した。総ページ数を悪化させないことは他の全ての空白削減より優先
    // する絶対条件（指示書§20）のため、ページ単位の貪欲な比較指標を
    // 増やすほど、should_adopt_page_plan()のPriority1（総ページ数
    // シミュレーション）では捉えきれない複数ページにまたがる悪影響の
    // リスクが増す。left_side_empty_areaは診断・回帰確認用の指標として
    // debug_logに残し、9文書回帰テストで実際に悪化していないかを
    // 目視・数値の両方で確認する運用とする。
    if ( $a['layout_switch_count'] < $b['layout_switch_count'] ) {
        return true;
    }
    if ( $a['layout_switch_count'] > $b['layout_switch_count'] ) {
        return false;
    }
    if ( $a['order_jump'] < $b['order_jump'] ) {
        return true;
    }
    if ( $a['order_jump'] > $b['order_jump'] ) {
        return false;
    }
    return $a['column_imbalance_score'] < $b['column_imbalance_score'];
}

/**
 * ページ単位探索指示書§7 Priority 1：与えられたキュー（コピー）が、
 * 既存アルゴリズムのみ（Baseline相当、ページ単位探索・2ブロック
 * 先読みのいずれも使わない）で尽きるまでに、あと何ページ必要かを
 * 軽量にシミュレートする。ページ2以降と同じ版面（page2_header_h）を
 * 使う——このチェックが呼ばれるのは常に「現在ページの残りキュー」を
 * 渡す場面であり、それが載るのは常に次ページ以降のため。
 *
 * $hatakiti_occult_pdf_suppress_page_searchをtrueにしてから
 * hatakiti_occult_pdf_stack_articles()を呼ぶことで、探索が探索を
 * 呼ぶ多重再帰・計算量爆発を防ぐ（既存アルゴリズムの自然決定のみで
 * シミュレートする）。
 */
function hatakiti_occult_pdf_count_pages_to_exhaust_queue( $queue ) {
    if ( empty( $queue ) ) {
        return 0;
    }
    $c      = hatakiti_occult_pdf_layout_constants();
    $zone_x = $c['margin_l'];
    $zone_w = $c['page_w'] - $c['margin_l'] - $c['margin_r'];
    $zone_y = $c['margin_t'] + $c['page2_header_h'];
    $zone_h = ( $c['page_h'] - $c['margin_b'] ) - $zone_y;

    $prev_suppress = $GLOBALS['hatakiti_occult_pdf_suppress_page_search'];
    $GLOBALS['hatakiti_occult_pdf_suppress_page_search'] = true;

    $queue_copy = $queue;
    $pages      = 0;
    $safety     = 0;
    while ( ! empty( $queue_copy ) && $safety < 15 ) {
        $safety++;
        list( $scratch_pdf, $scratch_font_regular, $scratch_font_bold ) = hatakiti_occult_pdf_new_tcpdf();
        $scratch_pdf->AddPage();
        hatakiti_occult_pdf_stack_articles( $queue_copy, $scratch_pdf, $scratch_font_regular, $scratch_font_bold, $zone_x, $zone_y, $zone_w, $zone_h, 99 );
        $pages++;
    }

    $GLOBALS['hatakiti_occult_pdf_suppress_page_search'] = $prev_suppress;
    return $pages;
}

/**
 * ページ単位探索指示書§3-5／§7／§8：Baseline PlanとCandidate Plan
 * （そのメトリクス）を比較し、Candidateを実際に採用してよいかを
 * 優先順位つきで判定する。ページ単位探索の最大の安全弁。
 *
 * @return array array('adopt'=>bool, 'reason'=>string)
 */
function hatakiti_occult_pdf_should_adopt_page_plan( $baseline, $baseline_metrics, $candidate, $candidate_metrics ) {
    if ( ! $candidate_metrics['valid'] ) {
        return array( 'adopt' => false, 'reason' => 'CANDIDATE_INVALID' );
    }

    // Rowの内部占有領域再利用指示書§15：clipping・孤立断片は絶対条件
    // として最優先で却下する（本設計では常に0のはずだが、防御的に
    // 最初にチェックする）。
    if ( $candidate_metrics['clipped_article_count'] > 0 ) {
        return array( 'adopt' => false, 'reason' => 'CLIPPED_ARTICLE_DETECTED' );
    }
    if ( $candidate_metrics['isolated_fragment_count'] > 0 ) {
        return array( 'adopt' => false, 'reason' => 'ISOLATED_FRAGMENT_DETECTED' );
    }

    // Priority 1：総ページ数を悪化させない。
    $baseline_more  = hatakiti_occult_pdf_count_pages_to_exhaust_queue( $baseline['remaining_queue'] );
    $candidate_more = hatakiti_occult_pdf_count_pages_to_exhaust_queue( $candidate['remaining_queue'] );
    if ( $candidate_more > $baseline_more ) {
        return array( 'adopt' => false, 'reason' => 'TOTAL_PAGE_COUNT_REGRESSION', 'baseline_more_pages' => $baseline_more, 'candidate_more_pages' => $candidate_more );
    }

    // Priority 3：最大空白矩形を明確に改善（相対15%以上、または絶対しきい値以上の解消）。
    $rect_before = $baseline_metrics['largest_empty_rect_area'];
    $rect_after  = $candidate_metrics['largest_empty_rect_area'];
    $rect_delta  = $rect_before - $rect_after;
    $rect_improved = ( $rect_before > 0 && ( $rect_delta / $rect_before ) >= 0.15 )
        || ( $rect_delta >= HATAKITI_OCCULT_PDF_PAGE_SEARCH_MIN_RECT_IMPROVEMENT_MM2 );

    // Priority 4：ページ末尾空白改善。
    $trailing_improved = $candidate_metrics['remaining_height'] < $baseline_metrics['remaining_height'] - 0.5;

    if ( ! $rect_improved && ! $trailing_improved ) {
        return array( 'adopt' => false, 'reason' => 'INSUFFICIENT_VISUAL_IMPROVEMENT' );
    }

    // Priority 5：段組みの自然さ（切替が明確に増える場合は見送る）。
    if ( $candidate_metrics['layout_switch_count'] > $baseline_metrics['layout_switch_count'] + 1 ) {
        return array( 'adopt' => false, 'reason' => 'TOO_MANY_LAYOUT_SWITCHES' );
    }

    // 空き矩形拡張指示書§11 重要な安全弁：最大空白矩形は改善しても、
    // 列バランスが極端に悪化する場合は不採用とする。
    if ( $candidate_metrics['column_imbalance_score'] > $baseline_metrics['column_imbalance_score'] + HATAKITI_OCCULT_PDF_PAGE_SEARCH_MAX_IMBALANCE_REGRESSION_MM ) {
        return array( 'adopt' => false, 'reason' => 'COLUMN_BALANCE_SEVERELY_WORSE' );
    }

    return array( 'adopt' => true, 'reason' => 'IMPROVED', 'baseline_more_pages' => $baseline_more, 'candidate_more_pages' => $candidate_more );
}

/**
 * ページ単位探索指示書§3-1：現在ページの残り高さとキューから、
 * Baseline Planと候補PagePlan群を生成・評価し、採用してよい最良候補
 * があれば返す。呼び出し側（hatakiti_occult_pdf_stack_articles()）は
 * 採用された場合、返されたrowsを順にhatakiti_occult_pdf_draw_one_block()
 * で実描画する。
 *
 * @return array|null 採用する場合は array('rows'=>[...], 'debug_log'=>array)。
 *   採用しない場合はnull（呼び出し側は既存の逐次ブロック処理にフォール
 *   バックする）。debug_log（診断専用、$warningsには一切影響しない）
 *   にはbaseline/candidateの指標と採否理由を記録する（§13）。
 */
function hatakiti_occult_pdf_search_page_plan( $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $start_y, $page_bottom, $page_no, $block_index_base ) {
    if ( ! HATAKITI_OCCULT_PDF_ALLOW_PAGE_LEVEL_SEARCH || $GLOBALS['hatakiti_occult_pdf_suppress_page_search'] ) {
        return null;
    }
    if ( empty( $queue ) || isset( $queue[0]['_pinned_col_w'] ) ) {
        return null;
    }

    $baseline         = hatakiti_occult_pdf_dry_run_baseline_plan( $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $start_y, $page_bottom, $page_no, $block_index_base );
    $baseline_metrics = hatakiti_occult_pdf_evaluate_page_plan( $baseline, $zone_w, $page_bottom, $pdf, $font_bold );

    $candidates       = array();
    $candidate_count  = 0;
    hatakiti_occult_pdf_search_page_plan_recursive( $candidates, $candidate_count, $queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_w, $start_y, $page_bottom, $page_no, $block_index_base, array(), array(), array(), 0, 0, 0 );

    $best         = null;
    $best_metrics = null;
    foreach ( $candidates as $cand ) {
        $metrics = hatakiti_occult_pdf_evaluate_page_plan( $cand, $zone_w, $page_bottom, $pdf, $font_bold );
        if ( hatakiti_occult_pdf_compare_page_plans( $metrics, $best_metrics ) ) {
            $best         = $cand;
            $best_metrics = $metrics;
        }
    }

    $debug_entry = array(
        'page_plan_search'    => true,
        'page'                => $page_no,
        'baseline_metrics'    => $baseline_metrics,
        'candidate_count'     => $candidate_count,
    );

    if ( null === $best ) {
        $debug_entry['adopted']      = false;
        $debug_entry['reject_reason'] = 'NO_CANDIDATE';
        return array( 'rows' => null, 'placements' => null, 'debug_log' => array( $debug_entry ) );
    }

    $decision = hatakiti_occult_pdf_should_adopt_page_plan( $baseline, $baseline_metrics, $best, $best_metrics );
    $debug_entry['best_candidate_metrics'] = $best_metrics;
    $debug_entry['adopted']                = $decision['adopt'];
    $debug_entry['reject_reason']          = $decision['adopt'] ? null : $decision['reason'];
    $debug_entry['placement_count']        = count( $best['placements'] ?? array() );
    $debug_entry['space_fill_article_ids'] = array();
    foreach ( $best['placements'] ?? array() as $p ) {
        $debug_entry['space_fill_article_ids'][] = $p['article_id'];
    }
    if ( isset( $decision['baseline_more_pages'] ) ) {
        $debug_entry['baseline_more_pages']  = $decision['baseline_more_pages'];
        $debug_entry['candidate_more_pages'] = $decision['candidate_more_pages'];
    }

    if ( ! $decision['adopt'] ) {
        return array( 'rows' => null, 'placements' => null, 'debug_log' => array( $debug_entry ) );
    }

    // 空き矩形拡張指示書§16：採用時、空き矩形への配置（あれば）を
    // 個別にログへ残す。
    foreach ( $best['placements'] as $p ) {
        $debug_entry['space_fill_placements'][] = array(
            'article_id' => $p['article_id'],
            'x'          => round( $p['x'], 1 ),
            'y'          => round( $p['y'], 1 ),
            'width'      => round( $p['width'], 1 ),
            'order_jump' => $p['order_jump'],
        );
    }

    return array( 'rows' => $best['rows'], 'placements' => $best['placements'], 'debug_log' => array( $debug_entry ) );
}

/**
 * PDFページ数最小化指示書§1/§3：このページで、記事領域の高さを
 * footer分（$footer_h + $footer_margin）だけ手前で切り上げても、
 * 残りキューがちょうど尽きるかを、使い捨てのTCPDFインスタンス上で
 * 試す（実際の$pdf・$queueは一切変更しない）。尽きるなら、このページ
 * を「記事＋出典＋編集後記が同居する最終ページ」として使ってよい —
 * 出典・編集後記専用ページを追加せずに済む。
 *
 * 「記事をページ末まで目一杯詰める」既存のバックフィルは、footer用の
 * 余白を考慮せずページを埋め切ってしまうため、この判定を経ずに
 * そのまま描画すると、記事がぎりぎり尽きた直後の残り高さがfooterに
 * 足りず、専用ページが生じる（今回の指示書が問題視したケース）。
 * この事前トライアルにより、そもそも記事の割り付け段階で必要な
 * 余白を空けておけるようにする。
 *
 * @return bool 尽きればtrue（＝footer分の余白を確保してこのページを
 *   使ってよい）。
 */
function hatakiti_occult_pdf_trial_fits_with_footer_reserved( $queue, $zone_x, $zone_y, $zone_w, $reduced_budget, $page_no ) {
    if ( $reduced_budget <= 0 ) {
        return false;
    }
    list( $scratch_pdf, $scratch_font_regular, $scratch_font_bold ) = hatakiti_occult_pdf_new_tcpdf();
    $scratch_pdf->AddPage();
    $queue_copy = $queue; // PHPの配列は値渡し（複合値も再帰的に複製される）— 実際の$queueには影響しない。
    hatakiti_occult_pdf_stack_articles( $queue_copy, $scratch_pdf, $scratch_font_regular, $scratch_font_bold, $zone_x, $zone_y, $zone_w, $reduced_budget, $page_no );
    return empty( $queue_copy );
}

/**
 * PDFページ数最小化指示書§3案B／§4①②③ — 「このページを縮めて
 * 残り記事を次ページへ送り、次ページで残り記事＋footerをまとめて
 * 収容できないか」を試す（終盤ページの再配置シミュレーション）。
 *
 * hatakiti_occult_pdf_trial_fits_with_footer_reserved()による
 * 「footer分を手前で切り上げてもこのページだけでキューが尽きるか」
 * の判定が失敗した場合（＝footerを置く余白がこのページには全く
 * 足りない場合）に呼ばれる。full_budgetを段階的に縮小しながら、
 * 「このページでは尽きないが、あふれた分＋footerが次ページの
 * フル高さに収まる」縮小予算を探す。見つかった時点で（＝あふれる
 * 記事数が最小になる時点で）即座に返す。
 *
 * 見つかった予算を現在のページの実描画に使えば、キューは自然に
 * 少しだけ次ページへ残り、次ページ側の
 * trial_fits_with_footer_reserved()判定が今度は成功して、
 * footer専用ページなしで「残り記事＋footer」を1ページに収容できる。
 *
 * 縮小幅は単純な割合ではなく、まずフル予算で試し積みして実際の
 * ブロック境界（Y座標）を取得し、「末尾のブロックから順に1つずつ
 * 次ページへ送る」形で候補を作る。ブロックは1〜4列の行単位で
 * まとまって配置されるため、境界を無視した中途半端な割合縮小では
 * 「送られる量が多すぎる／全く送られない」の両極端になりがちで、
 * 案Bの成立条件（あふれた分＋footerが次ページに収まる）を捉え
 * 損ねやすい。ブロック境界での縮小なら、末尾1ブロックだけを送る
 * 最小限の調整から順に試せる。
 *
 * 見つからなかった場合も、$debug_logで「なぜ既存ページへ収容できな
 * かったのか」を報告できるよう、試した候補のうち最も惜しかった
 * （次ページの残り高さとfooter必要高さの差＝不足量が最小だった）
 * ものを best_attempt として返す（PDFページ数最小化指示書§4の
 * ログ要件）。
 *
 * @return array array(
 *   'budget'       => float|null  見つかった縮小予算（mm）。null＝失敗。
 *   'best_attempt' => array|null  失敗時の最善候補の診断情報
 *     array('spilled_count'=>int, 'remaining_on_next'=>float,
 *           'footer_needed'=>float, 'shortfall'=>float)。
 * )
 */
/**
 * hatakiti_occult_pdf_find_endgame_reflow_budget()の結果を、
 * footer専用ページの理由ログ（PDFページ数最小化指示書§4）向けの
 * 短い文字列に整形する。
 */
function hatakiti_occult_pdf_format_endgame_reason( $would_finish_full, $endgame_budget, $endgame_best ) {
    if ( ! $would_finish_full ) {
        return 'no(このページは最終記事ページではないため未試行)';
    }
    if ( null !== $endgame_budget ) {
        return 'yes(成功)';
    }
    if ( null === $endgame_best ) {
        return 'yes(失敗、候補構成なし)';
    }
    return sprintf(
        'yes(失敗、最善候補=末尾%d記事を次ページへ送っても次ページ残り%.1fmm<footer必要%.1fmm、不足%.1fmm)',
        $endgame_best['spilled_count'],
        $endgame_best['remaining_on_next'],
        $endgame_best['footer_needed'],
        $endgame_best['shortfall']
    );
}

function hatakiti_occult_pdf_find_endgame_reflow_budget( $queue, $zone_x, $zone_y, $zone_w, $full_budget, $footer_h, $footer_margin, $page_no, $next_zone_y, $next_full_budget ) {
    $fail = array( 'budget' => null, 'best_attempt' => null );

    $queue_copy_full = $queue; // PHPの配列は値渡し — 実際の$queueには影響しない。
    list( $scratch_pdf, $scratch_font_regular, $scratch_font_bold ) = hatakiti_occult_pdf_new_tcpdf();
    $scratch_pdf->AddPage();
    $full_stack = hatakiti_occult_pdf_stack_articles( $queue_copy_full, $scratch_pdf, $scratch_font_regular, $scratch_font_bold, $zone_x, $zone_y, $zone_w, $full_budget, $page_no );
    if ( ! empty( $queue_copy_full ) ) {
        // フル予算でもこのページだけでは尽きない＝呼び出し側の事前
        // 判定と矛盾するはずだが、念のため安全に諦める。
        return $fail;
    }

    $block_tops = array();
    foreach ( $full_stack['debug'] as $row ) {
        if ( ! isset( $row['block'], $row['y'] ) ) {
            continue;
        }
        $b = $row['block'];
        if ( ! isset( $block_tops[ $b ] ) || $row['y'] < $block_tops[ $b ] ) {
            $block_tops[ $b ] = $row['y'];
        }
    }
    if ( empty( $block_tops ) ) {
        return $fail;
    }
    krsort( $block_tops ); // ブロック番号の大きい順＝末尾のブロックから試す。

    $footer_needed = $footer_h + $footer_margin;
    $next_reduced  = $next_full_budget - $footer_h - $footer_margin;
    $best_attempt  = null;
    foreach ( $block_tops as $top_y ) {
        $candidate_budget = ( $top_y - $zone_y ) - 1.0; // このブロックの直前で切り上げる
        if ( $candidate_budget <= 0 ) {
            continue;
        }
        $queue_copy = $queue;
        list( $scratch_pdf2, $scratch_font_regular2, $scratch_font_bold2 ) = hatakiti_occult_pdf_new_tcpdf();
        $scratch_pdf2->AddPage();
        hatakiti_occult_pdf_stack_articles( $queue_copy, $scratch_pdf2, $scratch_font_regular2, $scratch_font_bold2, $zone_x, $zone_y, $zone_w, $candidate_budget, $page_no );
        if ( empty( $queue_copy ) ) {
            // この境界で切ってもまだ全部収まってしまう（backfillが
            // 別のブロック構成を選び直した等）。もう1つ手前を試す。
            continue;
        }
        if ( hatakiti_occult_pdf_trial_fits_with_footer_reserved( $queue_copy, $zone_x, $next_zone_y, $zone_w, $next_reduced, $page_no + 1 ) ) {
            return array( 'budget' => $candidate_budget, 'best_attempt' => null );
        }

        // 惜しさを測るため、あふれた分だけを次ページのフル高さで
        // 試し積みし、実際に残る高さ（footer用に使える高さ）を測る。
        $spill_copy = $queue_copy;
        list( $scratch_pdf3, $scratch_font_regular3, $scratch_font_bold3 ) = hatakiti_occult_pdf_new_tcpdf();
        $scratch_pdf3->AddPage();
        $spill_stack     = hatakiti_occult_pdf_stack_articles( $spill_copy, $scratch_pdf3, $scratch_font_regular3, $scratch_font_bold3, $zone_x, $next_zone_y, $zone_w, $next_full_budget, $page_no + 1 );
        $remaining_next  = ( $next_zone_y + $next_full_budget ) - max( $spill_stack['bottom_y'], $next_zone_y );
        $shortfall       = $footer_needed - $remaining_next;
        if ( null === $best_attempt || $shortfall < $best_attempt['shortfall'] ) {
            $best_attempt = array(
                'spilled_count'     => count( $queue_copy ),
                'remaining_on_next' => round( $remaining_next, 1 ),
                'footer_needed'     => round( $footer_needed, 1 ),
                'shortfall'         => round( $shortfall, 1 ),
            );
        }
    }
    return array( 'budget' => null, 'best_attempt' => $best_attempt );
}

/**
 * 出典一覧＋編集後記＋文責クレジットの横書きフッターブロック。
 */
function hatakiti_occult_pdf_footer_height( $pdf, $font_regular, $all_sources, $editorial_summary, $w ) {
    $pdf->SetFont( $font_regular, '', 8 );
    $h = 0;
    if ( $all_sources ) {
        $h += 5; // 見出し
        $h += count( $all_sources ) * 4.2;
    }
    if ( $editorial_summary ) {
        $h += 5; // 見出し
        $h += $pdf->getStringHeight( $w, $editorial_summary );
    }
    $h += 6; // credit line
    return $h + 4;
}

function hatakiti_occult_pdf_draw_footer( $pdf, $font_regular, $font_bold, $c, $all_sources, $editorial_summary, $y_start ) {
    $w = $c['page_w'] - $c['margin_l'] - $c['margin_r'];
    $y = $y_start;

    $pdf->SetLineWidth( 0.3 );
    $pdf->Line( $c['margin_l'], $y, $c['page_w'] - $c['margin_r'], $y );
    $y += 3;

    if ( $all_sources ) {
        $pdf->SetFont( $font_bold, '', 9 );
        $pdf->SetXY( $c['margin_l'], $y );
        $pdf->Cell( $w, 4.5, '出典', 0, 0, 'L' );
        $y += 5;
        $pdf->SetFont( $font_regular, '', 7.5 );
        foreach ( $all_sources as $s ) {
            $text = hatakiti_occult_pdf_fullwidth_digits( $s['name'] . '「' . mb_substr( $s['title'], 0, 50 ) . '」' );
            $pdf->SetXY( $c['margin_l'], $y );
            $pdf->Cell( $w, 4, $text, 0, 0, 'L' );
            if ( $s['url'] ) {
                $pdf->Link( $c['margin_l'], $y, $w, 4, $s['url'] );
            }
            $y += 4.2;
        }
        $y += 1;
    }

    if ( $editorial_summary ) {
        $pdf->SetFont( $font_bold, '', 9 );
        $pdf->SetXY( $c['margin_l'], $y );
        $pdf->Cell( $w, 4.5, '編集後記', 0, 0, 'L' );
        $y += 5;
        $pdf->SetFont( $font_regular, '', 8.5 );
        $pdf->SetXY( $c['margin_l'], $y );
        $pdf->MultiCell( $w, 4.2, $editorial_summary, 0, 'L' );
        $y = $pdf->GetY() + 1;
    }

    $pdf->SetFont( $font_regular, '', 6.5 );
    $pdf->SetXY( $c['margin_l'], $y );
    $pdf->MultiCell( $w, 3.2, '本紙は複数の公開情報をAIおよびHATAKITIが整理・編集したものです。元記事本文の転載を目的とせず、掲載内容の真偽を保証するものではありません。文責：チャッピー', 0, 'L' );
}

/**
 * メインエントリ：occult_weekly の post_id から紙面PDFを組版し、
 * 一時ファイルパスを返す。呼び出し側で保存/配信/削除を行う。
 *
 * @return array|WP_Error array('path'=>string,'pages'=>int,'warnings'=>array())
 */
function hatakiti_generate_occult_weekly_pdf( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post || 'occult_weekly' !== $post->post_type ) {
        return new WP_Error( 'hatakiti_pdf_bad_post', '対象の号が見つかりません。' );
    }

    $articles = hatakiti_json_meta( $post_id, 'hatakiti_occult_articles_json' );
    if ( ! $articles ) {
        return new WP_Error( 'hatakiti_pdf_no_articles', 'この号にはまだ記事がありません。' );
    }

    $tiers = array( 'large' => array(), 'medium' => array(), 'small' => array() );
    $debug_article_id = 0;
    foreach ( $articles as $a ) {
        $t = isset( $a['tier'] ) && isset( $tiers[ $a['tier'] ] ) ? $a['tier'] : 'small';
        $a['_tier']  = $t;
        // 検証・報告専用（レイアウトには影響しない）— 記事ごとの
        // 開始/終了ページをdebugログから追跡できるようにする安定キー。
        $a['_debug_article_id'] = $debug_article_id++;
        $tiers[ $t ][] = $a;
    }

    $all_sources = array();
    foreach ( $articles as $article ) {
        foreach ( (array) ( $article['news_item_ids'] ?? array() ) as $item_id ) {
            $url = get_post_meta( $item_id, 'hatakiti_occult_original_url', true );
            if ( ! $url ) {
                continue;
            }
            $all_sources[ $url ] = array(
                'name'  => get_post_meta( $item_id, 'hatakiti_occult_source_name', true ),
                'title' => get_the_title( $item_id ),
                'url'   => $url,
            );
        }
    }

    // 表示用に全角化する（DBのpostmetaそのものは変更しない）。
    // editorial_summaryはhatakiti_occult_pdf_footer_height()（見積もり）
    // とhatakiti_occult_pdf_draw_footer()（実描画）の両方で使われるため、
    // ここで一度だけ変換し、以降は同じ変換済み文字列を両方に渡すことで
    // 見積もりと実描画のずれを防ぐ。
    $editorial_summary = hatakiti_occult_pdf_fullwidth_digits( get_post_meta( $post_id, 'hatakiti_occult_editorial_summary', true ) );
    $issue_id          = get_post_meta( $post_id, 'hatakiti_occult_issue_id', true );
    $issue_date        = get_post_meta( $post_id, 'hatakiti_occult_issue_date', true );
    $issue_subtitle    = get_the_title( $post_id );

    list( $pdf, $font_regular, $font_bold ) = hatakiti_occult_pdf_new_tcpdf();
    $c = hatakiti_occult_pdf_layout_constants();
    $full_w = $c['page_w'] - $c['margin_l'] - $c['margin_r'];

    $warnings   = array();
    $debug_log  = array();

    // フッター（出典＋編集後記）に必要な高さを先に見積もる
    $pdf->AddPage();
    $footer_h = hatakiti_occult_pdf_footer_height( $pdf, $font_regular, $all_sources, $editorial_summary, $full_w );

    $page1_col_top    = $c['margin_t'] + $c['masthead_h'];
    $page1_col_h_full = ( $c['page_h'] - $c['margin_b'] ) - $page1_col_top;

    // 2ページ目以降の版面はどのページでも同じ（見出し高さが一定のため）
    // なので、終盤ページの再配置シミュレーション（「次ページ」の版面を
    // 覗き見る）でも使えるよう、ここで一度だけ計算しておく。
    $page_n_col_top    = $c['margin_t'] + $c['page2_header_h'];
    $page_n_col_h_full = ( $c['page_h'] - $c['margin_b'] ) - $page_n_col_top;

    // 紙面全体を通じて1本のキューとして扱う（large_zone/left_zoneの
    // ような並行ゾーンへの事前分割はしない）。large→medium→smallの
    // 順で並べることで、1面冒頭にlarge記事の横ぶち抜きが来て、その後
    // medium/smallの横並び・単独記事が続く、という自然な新聞面の構成
    // になる（指示書§4「large記事は横ぶち抜きを積極的に使用する」・
    // §6「ページ全体をどういう新聞面にするかを先に決める」）。ページ1
    // も2面以降もまったく同じ hatakiti_occult_pdf_stack_articles() を
    // 使うため、「1面だけ特別扱い」がなく、列数の決め方も完全に共通。
    $queue = array_merge( $tiers['large'], $tiers['medium'], $tiers['small'] );

    list( $pdf, $font_regular, $font_bold ) = hatakiti_occult_pdf_new_tcpdf();
    $pdf->AddPage();
    hatakiti_occult_pdf_draw_masthead( $pdf, $font_regular, $font_bold, $c, $issue_subtitle, $issue_id, $issue_date );

    // footer（出典一覧＋編集後記）ぶんの余白を毎ページ機械的に確保
    // すると、記事がまだ続くページにも常に「footerのための空白」が
    // 残ってしまい、「前ページに十分な空きがあるのに続きを次ページへ
    // 送る」不具合の直接の原因になる。そこで通常は記事ページを常に
    // フル高さで積むが、残りキューが少ない（footerで最後になり得る）
    // 段階では、事前トライアル
    // （hatakiti_occult_pdf_trial_fits_with_footer_reserved()）で
    // 「footer分を手前で切り上げても残りキューが尽きるか」を確認し、
    // 尽きるならそのページだけfooter分の余白を残して積む — これにより
    // 出典・編集後記専用ページの発生そのものを避ける（PDFページ数
    // 最小化指示書§1〜§4）。専用ページになった場合のみ、その理由を
    // $debug_logへ記録する。
    $footer_margin    = 3.0;
    $footer_y         = null;
    $footer_dedicated_reason = null;
    $max_pages_safety = 12;
    $page_no          = 1;

    // まず「フル高さで積んだ場合、このページだけでキューが尽きるか
    // （＝このページが最後の記事ページになり得るか）」を安く確認する。
    // 尽きない（まだ何ページも続く）とわかっている序盤のページでは、
    // footer関連のトライアルをそもそも試す意味がないため省略する
    // （生成時間の無駄を避ける）。
    $would_finish_full_p1 = hatakiti_occult_pdf_trial_fits_with_footer_reserved( $queue, $c['margin_l'], $page1_col_top, $full_w, $page1_col_h_full, 1 );
    $reserve_footer_p1    = false;
    $endgame_budget_p1    = null;
    $endgame_best_p1      = null;
    $page1_budget_to_use  = $page1_col_h_full;
    if ( $would_finish_full_p1 ) {
        $reduced_budget_p1 = $page1_col_h_full - $footer_h - $footer_margin;
        $reserve_footer_p1 = hatakiti_occult_pdf_trial_fits_with_footer_reserved( $queue, $c['margin_l'], $page1_col_top, $full_w, $reduced_budget_p1, 1 );
        if ( $reserve_footer_p1 ) {
            $page1_budget_to_use = $reduced_budget_p1;
        } else {
            // footerを置く余白がこのページには全く足りない。案B：
            // このページを少し縮めて残り記事を次ページへ送り、次ページで
            // 残り記事＋footerをまとめて収容できないか試す。
            $endgame_result_p1 = hatakiti_occult_pdf_find_endgame_reflow_budget( $queue, $c['margin_l'], $page1_col_top, $full_w, $page1_col_h_full, $footer_h, $footer_margin, 1, $page_n_col_top, $page_n_col_h_full );
            $endgame_budget_p1 = $endgame_result_p1['budget'];
            $endgame_best_p1   = $endgame_result_p1['best_attempt'];
            if ( null !== $endgame_budget_p1 ) {
                $page1_budget_to_use = $endgame_budget_p1;
            }
        }
    }

    $page1_stack = hatakiti_occult_pdf_stack_articles( $queue, $pdf, $font_regular, $font_bold, $c['margin_l'], $page1_col_top, $full_w, $page1_budget_to_use, 1 );
    $debug_log   = $page1_stack['debug'];

    if ( empty( $queue ) ) {
        // 全記事が1面だけで収まった。footerが1面の残り高さに収まるか
        // を確認し、収まらなければfooter専用ページを足す。
        $bottom_y        = max( $page1_stack['bottom_y'], $page1_col_top + 40 );
        $remaining_on_p1 = ( $page1_col_top + $page1_col_h_full ) - $bottom_y;
        if ( $remaining_on_p1 >= $footer_h + $footer_margin ) {
            $footer_y = $bottom_y;
        } else {
            $footer_dedicated_reason = sprintf( '1ページ目残り高さ%.1fmm（footer必要%.1fmm、reduced_budget試行=%s、endgame再配置試行=%s）', $remaining_on_p1, $footer_h + $footer_margin, $reserve_footer_p1 ? 'yes' : 'no', hatakiti_occult_pdf_format_endgame_reason( $would_finish_full_p1, $endgame_budget_p1, $endgame_best_p1 ) );
        }
    } else {
        $page_no = 2;
        while ( ! empty( $queue ) && $page_no <= $max_pages_safety ) {
            $pdf->AddPage();
            hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c, $page_no );

            $would_finish_full_n  = hatakiti_occult_pdf_trial_fits_with_footer_reserved( $queue, $c['margin_l'], $page_n_col_top, $full_w, $page_n_col_h_full, $page_no );
            $reserve_footer_n     = false;
            $endgame_budget_n     = null;
            $endgame_best_n       = null;
            $page_n_budget_to_use = $page_n_col_h_full;
            if ( $would_finish_full_n ) {
                $reduced_budget_n = $page_n_col_h_full - $footer_h - $footer_margin;
                $reserve_footer_n = hatakiti_occult_pdf_trial_fits_with_footer_reserved( $queue, $c['margin_l'], $page_n_col_top, $full_w, $reduced_budget_n, $page_no );
                if ( $reserve_footer_n ) {
                    $page_n_budget_to_use = $reduced_budget_n;
                } else {
                    // footerを置く余白がこのページには全く足りない。案B：
                    // このページを少し縮めて残り記事を次ページへ送り、次
                    // ページで残り記事＋footerをまとめて収容できないか試す。
                    $endgame_result_n = hatakiti_occult_pdf_find_endgame_reflow_budget( $queue, $c['margin_l'], $page_n_col_top, $full_w, $page_n_col_h_full, $footer_h, $footer_margin, $page_no, $page_n_col_top, $page_n_col_h_full );
                    $endgame_budget_n = $endgame_result_n['budget'];
                    $endgame_best_n   = $endgame_result_n['best_attempt'];
                    if ( null !== $endgame_budget_n ) {
                        $page_n_budget_to_use = $endgame_budget_n;
                    }
                }
            }

            $page_n_stack = hatakiti_occult_pdf_stack_articles( $queue, $pdf, $font_regular, $font_bold, $c['margin_l'], $page_n_col_top, $full_w, $page_n_budget_to_use, $page_no );
            $debug_log    = array_merge( $debug_log, $page_n_stack['debug'] );

            if ( empty( $queue ) ) {
                // このページで記事が尽きた。footerがこのページの
                // 残り高さに収まるかを確認する。
                $bottom_y        = max( $page_n_stack['bottom_y'], $page_n_col_top + 30 );
                $remaining_on_pg = ( $page_n_col_top + $page_n_col_h_full ) - $bottom_y;
                if ( $remaining_on_pg >= $footer_h + $footer_margin ) {
                    $footer_y = $bottom_y;
                } else {
                    $footer_dedicated_reason = sprintf( '%dページ目残り高さ%.1fmm（footer必要%.1fmm、reduced_budget試行=%s、endgame再配置試行=%s）', $page_no, $remaining_on_pg, $footer_h + $footer_margin, $reserve_footer_n ? 'yes' : 'no', hatakiti_occult_pdf_format_endgame_reason( $would_finish_full_n, $endgame_budget_n, $endgame_best_n ) );
                }
                // 収まらない場合は $footer_y を null のままにし、
                // ループの外でfooter専用ページを追加する。
            }
            $page_no++;
        }

        if ( ! empty( $queue ) ) {
            // 安全弁: $max_pages_safety ページでも収まらない極端な分量の
            // 場合のみ到達する想定（通常運用では発生しない）。
            // articles_jsonは変更しない。
            $remaining_headlines = array();
            foreach ( $queue as $r_article ) {
                $remaining_headlines[] = mb_substr( (string) ( $r_article['headline'] ?? '' ), 0, 20 ) . '（' . mb_strlen( (string) ( $r_article['body'] ?? '' ) ) . '字）';
            }
            $warnings[] = "記事量が非常に多く、{$max_pages_safety}ページ以内に紙面へ収まりませんでした。記事本文は自動で削除していません（articles_jsonは無変更）。未掲載: " . implode( ' / ', $remaining_headlines );
        }
    }

    if ( null === $footer_y ) {
        // 最後の記事ページの残り高さにfooterが収まらなかった場合の
        // フォールバック：footer専用の1ページを追加する。理由をdebug_log
        // へ記録する（PDFページ数最小化指示書§4、articles_json等の
        // DBデータには一切影響しない診断情報）。
        $debug_log[] = array(
            'page'                   => $page_no + 1,
            'dedicated_footer_page'  => true,
            'reason'                 => $footer_dedicated_reason,
        );
        $pdf->AddPage();
        hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c, $page_no );
        $footer_y = $c['margin_t'] + $c['page2_header_h'];
    }

    hatakiti_occult_pdf_draw_footer( $pdf, $font_regular, $font_bold, $c, $all_sources, $editorial_summary, $footer_y + 2 );

    $pages = $pdf->getNumPages();

    $tmp_path = trailingslashit( sys_get_temp_dir() ) . 'hatakiti-occult-pdf-' . $post_id . '-' . wp_generate_password( 8, false ) . '.pdf';
    $pdf->Output( $tmp_path, 'F' );

    return array(
        'path'     => $tmp_path,
        'pages'    => $pages,
        'warnings' => $warnings,
        'debug'    => $debug_log,
    );
}

/**
 * post_id → 生成済みPDFのキャッシュパス。articles_json / editorial_summary
 * が変わらない限り再生成しない（不要な再生成を避ける、§32）。
 */
function hatakiti_occult_pdf_cache_path( $post_id ) {
    $dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'hatakiti-occult-pdf/';
    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
    }
    return $dir . 'issue-' . (int) $post_id . '.pdf';
}

function hatakiti_occult_pdf_cache_key( $post_id ) {
    $articles = get_post_meta( $post_id, 'hatakiti_occult_articles_json', true );
    $summary  = get_post_meta( $post_id, 'hatakiti_occult_editorial_summary', true );
    // HATAKITI_OCCULT_PDF_GENERATOR_VERSION をキーに含めることで、記事
    // 内容が変わっていなくても、組版コード自体（マストヘッド・レイアウト
    // 等）が変わった時点で既存の全キャッシュ済みPDFが自動的に無効化
    // される。ここに含めないと、コードだけを変更した回で「まだ articles_
    // json を書き換えていない号」のPDFが古いレイアウトのまま配信され
    // 続けてしまう（実際に発生した不具合 — 本文データは無関係、PDF
    // キャッシュのみの問題）。
    return md5( HATAKITI_OCCULT_PDF_GENERATOR_VERSION . '|' . (string) $articles . '|' . (string) $summary );
}

/**
 * キャッシュを見て、無ければ生成してキャッシュに保存し、パスを返す。
 */
function hatakiti_get_occult_weekly_pdf_path( $post_id ) {
    $cache_path = hatakiti_occult_pdf_cache_path( $post_id );
    $key        = hatakiti_occult_pdf_cache_key( $post_id );
    $meta_key   = get_post_meta( $post_id, 'hatakiti_occult_pdf_cache_key', true );

    if ( $meta_key === $key && file_exists( $cache_path ) ) {
        return array( 'path' => $cache_path, 'warnings' => array() );
    }

    $result = hatakiti_generate_occult_weekly_pdf( $post_id );
    if ( is_wp_error( $result ) ) {
        return $result;
    }

    copy( $result['path'], $cache_path );
    @unlink( $result['path'] );
    update_post_meta( $post_id, 'hatakiti_occult_pdf_cache_key', $key );

    if ( ! empty( $result['warnings'] ) ) {
        update_post_meta( $post_id, 'hatakiti_occult_pdf_warnings', wp_slash( wp_json_encode( $result['warnings'], JSON_UNESCAPED_UNICODE ) ) );
        foreach ( $result['warnings'] as $w ) {
            error_log( sprintf( '[hatakiti occult pdf] issue #%d: %s', $post_id, $w ) );
        }
    } else {
        delete_post_meta( $post_id, 'hatakiti_occult_pdf_warnings' );
    }

    return array( 'path' => $cache_path, 'pages' => $result['pages'], 'warnings' => $result['warnings'] );
}

/**
 * 公開エントリポイント： occult_weekly の個別ページに ?hatakiti_pdf=1 が
 * 付いていたらPDFを生成・配信して終了する。draft等は編集権限が必要
 * （既存のpreview権限チェックと同じ考え方）。
 */
function hatakiti_occult_pdf_maybe_serve() {
    if ( ! isset( $_GET['hatakiti_pdf'] ) || ! is_singular( 'occult_weekly' ) ) {
        return;
    }

    $post_id = get_queried_object_id();
    $post    = get_post( $post_id );
    if ( ! $post ) {
        return;
    }

    if ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $post_id ) ) {
        wp_die( 'この号のPDFを閲覧する権限がありません。', '403', array( 'response' => 403 ) );
    }

    $result = hatakiti_get_occult_weekly_pdf_path( $post_id );
    if ( is_wp_error( $result ) ) {
        wp_die( esc_html( $result->get_error_message() ), 'PDF生成エラー', array( 'response' => 500 ) );
    }

    $path = $result['path'];
    if ( ! file_exists( $path ) ) {
        wp_die( 'PDFの生成に失敗しました。', 'PDF生成エラー', array( 'response' => 500 ) );
    }

    nocache_headers();
    header( 'Content-Type: application/pdf' );
    header( 'Content-Disposition: inline; filename="occult-weekly-' . (int) $post_id . '.pdf"' );
    header( 'Content-Length: ' . filesize( $path ) );
    readfile( $path );
    exit;
}
add_action( 'template_redirect', 'hatakiti_occult_pdf_maybe_serve' );
