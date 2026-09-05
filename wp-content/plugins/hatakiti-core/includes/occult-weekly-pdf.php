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
 *   - 縦中横は連続する半角数字を2桁ずつペアリングする単純な方式
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
 */
define( 'HATAKITI_OCCULT_PDF_GENERATOR_VERSION', '24' );

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
 * （縦中横ペアリング・4桁西暦判定）は必ず変換前の半角文字列に対して
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
 * 半角数字ちょうど2桁の縦中横ペアリング（それ以外の桁数は1桁ずつ通常の
 * 縦書き文字として並べる）＋回転文字（ー〜…）を考慮しつつ、段落
 * （\n\n区切り）ごとに「1文字ぶんの描画単位」の配列へ分解する。
 *
 * @return array 各要素は段落＝unitの配列。unit = array('type'=>'char'|'tcy'|'rotate', 'ch'=>string|array)
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
                // まず連続する半角数字の全体の長さを求める（2桁で打ち切らない
                // — ちょうど4桁かどうかで西暦判定するため）。
                $j = $i;
                while ( $j < $n && ctype_digit( $chars[ $j ] ) ) {
                    $j++;
                }
                $run_len = $j - $i;

                if ( 2 === $run_len ) {
                    // ちょうど2桁（日付・年代の下2桁など）だけを縦中横で
                    // ペアリングする。
                    $pair    = implode( '', array_slice( $chars, $i, 2 ) );
                    $units[] = array( 'type' => 'tcy', 'ch' => $pair );
                } else {
                    // 2桁ちょうど以外（1桁単独、3桁の数量表記「518」、
                    // 4桁の西暦「2026」、5桁以上など）は縦中横ペアリングに
                    // せず、1桁ずつ通常の縦書き文字として配置する。
                    // 「２桁だけ小さく詰めて残り1桁だけ通常サイズ」という
                    // 混在（例：518→「51」ペア＋「8」単独）は、縦中横部分
                    // だけ不自然に小さく・半角風に見えるため禁止する。
                    for ( $k = $i; $k < $j; $k++ ) {
                        $units[] = array( 'type' => 'char', 'ch' => $chars[ $k ] );
                    }
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
        if ( 'tcy' === $unit['type'] ) {
            // 縦中横の数字も表示上は全角グリフに差し替える（桁数判定は
            // build_units()側で変換前の半角文字列に対してすでに終わって
            // いるため、ここでの差し替えはグリフの見た目だけに影響する）。
            $digits    = mb_str_split( $unit['ch'], 1, 'UTF-8' );
            $digits[0] = hatakiti_occult_pdf_fullwidth_digits( $digits[0] );
            if ( isset( $digits[1] ) ) {
                $digits[1] = hatakiti_occult_pdf_fullwidth_digits( $digits[1] );
            }
            $tcy_pt  = $font_pt * 0.56;
            $half_w  = $col_pitch / 2;
            $pdf->SetFontSize( $tcy_pt );
            $pdf->SetXY( $col_left, $y );
            $pdf->Cell( $half_w, $char_h, $digits[0], 0, 0, 'C' );
            if ( isset( $digits[1] ) ) {
                $pdf->SetXY( $col_left + $half_w, $y );
                $pdf->Cell( $half_w, $char_h, $digits[1], 0, 0, 'C' );
            }
            $pdf->SetFontSize( $font_pt );
        } elseif ( 'rotate' === $unit['type'] ) {
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
    $src_h    = 'headline' === $mode ? HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM : 0.0;
    $body_top = $y + $header_h + $gap;
    $body_h   = hatakiti_occult_pdf_body_segment_h( $article, $tier );

    $overflow_body = null;
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
            $overflow_body = $body_paragraphs;
            $body_content_h = 0.0;
        } else {
            $body_result = hatakiti_occult_pdf_layout_and_draw_columns( $pdf, $body_paragraphs, $x + $w, $body_top, $body_h, $cols_to_use, $fonts['body'], $font_regular );
            if ( $body_result['overflow'] ) {
                $overflow_body = $body_result['remainder'];
            }
        }
    } else {
        $overflow_body = hatakiti_occult_pdf_build_units( $body );
    }

    $bottom_y = $body_top + $body_h;

    // 出典（横書き、本文のすぐ下に1行）。今回そろえるのは見出し上端と
    // 本文開始位置までであり、記事の終了位置（出典・罫線の位置）まで
    // 列間で無理に揃えようとはしない（追加指示§7）— 出典は単純にこの
    // セグメント自身の本文の直後に置く。
    $source_lines = hatakiti_occult_pdf_source_lines( $article['news_item_ids'] ?? array() );
    if ( $source_lines && $w > 12 && $src_h > 0 ) {
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
        $bottom_y += $src_h;
    }

    return array( 'overflow_body' => $overflow_body, 'bottom_y' => $bottom_y );
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
 * この「ブロック」に何列（1〜3）並べるかを、キュー先頭の記事構成から
 * 直接決める（指示書§1「最優先方針」の順序をそのままルールの適用順に
 * する）。
 *
 *   - キュー先頭がlarge tierなら常に1列（横ぶち抜き、指示書§4）。
 *     large記事はどんな場合もペアにしない（続きであっても同じ）。
 *   - キュー先頭から連続して同tierが3件並んでいて（続き記事も同tier
 *     である限りカウントする — 続き記事だからといって単独1列へ強制
 *     しない、指示書追加指示§4/§5「続き記事が先頭なら常に全幅1列、を
 *     撤廃し、元記事と同じ列幅・同じX座標を維持することを優先する」）、
 *     3等分した列幅がHATAKITI_OCCULT_PDF_MIN_COL_W_MM以上なら3列。
 *   - 同様に2件同tierが並んでいれば2列。
 *   - それ以外（tierが混在する、または後続がない）は1列。
 *
 * この判定は「ブロック」の開始時に1回だけ行う。ブロック内の各列は、
 * 一度決まった列幅・X座標のまま、その列の記事が完結する（または
 * ページ末に達する）まで縦方向へ継続描画し続ける
 * （hatakiti_occult_pdf_stack_articles参照）。
 *
 * @return int 1|2|3
 */
function hatakiti_occult_pdf_decide_row_columns( $queue, $zone_w ) {
    if ( empty( $queue ) ) {
        return 1;
    }
    $first = $queue[0];
    if ( 'large' === $first['_tier'] ) {
        return 1;
    }
    $tier = $first['_tier'];

    $same_run = 1;
    for ( $i = 1; $i < count( $queue ) && $i < 3; $i++ ) {
        if ( $queue[ $i ]['_tier'] !== $tier ) {
            break;
        }
        $same_run++;
    }

    if ( $same_run >= 3 ) {
        $triple_w = ( $zone_w - ( HATAKITI_OCCULT_PDF_ROW_COL_GAP_MM * 2 ) ) / 3;
        if ( $triple_w >= HATAKITI_OCCULT_PDF_MIN_COL_W_MM ) {
            return 3;
        }
    }
    if ( $same_run >= 2 ) {
        $pair_w = ( $zone_w - HATAKITI_OCCULT_PDF_ROW_COL_GAP_MM ) / 2;
        if ( $pair_w >= HATAKITI_OCCULT_PDF_MIN_COL_W_MM ) {
            return 2;
        }
    }
    return 1;
}

/**
 * 「紙面先行型」の列組版。処理は「ブロック」単位で進む —
 * hatakiti_occult_pdf_decide_row_columns() がブロック先頭で列数
 * （1〜3）を1回だけ決め、各列にはキュー先頭からその列数ぶんの記事を
 * 割り当てる。そのあとは各列を完全に独立に、同じX座標・同じ列幅の
 * まま下方向へ積み上げる — ある列の記事が続く限り、その続きは必ず
 * 同じ列にとどまる（同一記事の連続性・元記事と同じ列位置の維持）。
 * 1つの列の記事が完結しても、その空いた列へ別の記事を割り込ませる
 * ことはしない — ブロックは全列が完結する（またはページ末に達する）
 * まで終わらず、次のブロックはその時点の最も深い列の位置から、
 * 改めて列数を判定して始まる（同一ページ内でブロックの列配置を勝手に
 * 組み替えない）。
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
 * 紙面利用率（列によっては他の列より早く記事が完結し、そのぶん
 * 空白が残る）よりも、列位置の一貫性を優先する。
 *
 * @return array array('bottom_y'=>mm, 'drew_any'=>bool, 'debug'=>array)
 */
function hatakiti_occult_pdf_stack_articles( &$queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_y, $zone_w, $zone_h_budget, $page_no = 1 ) {
    $row_gap     = HATAKITI_OCCULT_PDF_ROW_GAP_MM;
    $col_gap     = HATAKITI_OCCULT_PDF_ROW_COL_GAP_MM;
    $page_bottom = $zone_y + $zone_h_budget;
    $block_top   = $zone_y;
    $drew_any    = false;
    $debug       = array();

    while ( ! empty( $queue ) && ( $page_bottom - $block_top ) > HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM ) {
        $cols  = hatakiti_occult_pdf_decide_row_columns( $queue, $zone_w );
        $col_w = 1 === $cols ? $zone_w : ( $zone_w - ( $col_gap * ( $cols - 1 ) ) ) / $cols;

        // 各列のX座標（縦書きの読み順に合わせ、列0をいちばん右に置く）
        // はブロック開始時に一度だけ決め、この列の記事が続く限り最後
        // まで変えない。
        $col_x = array();
        for ( $k = 0; $k < $cols; $k++ ) {
            $col_x[ $k ] = $zone_x + $zone_w - ( ( $k + 1 ) * $col_w ) - ( $k * $col_gap );
        }

        // ブロック内全列の「最初のセグメント」の見出し（またはラベル）
        // 高さを先に見積もり、その最大値を全列共通の見出し領域高さと
        // する — これにより本文開始Y座標を列間で完全に揃えられる
        // （追加指示§5「block_header_h = 最大値」）。
        $block_header_h = 0.0;
        for ( $k = 0; $k < $cols; $k++ ) {
            $h = hatakiti_occult_pdf_measure_first_segment_header_h( $pdf, $font_bold, $queue[ $k ], $queue[ $k ]['_tier'], $col_w );
            $block_header_h = max( $block_header_h, $h );
        }
        $body_start_y = $block_top + $block_header_h + HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM;

        $col_final   = array(); // 各列で最後に残った記事（nullなら完結）
        $col_bottom  = array(); // 各列が到達した実際のY
        $col_drew    = array();

        for ( $k = 0; $k < $cols; $k++ ) {
            $article     = $queue[ $k ];
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

                $result = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article, $tier, $col_x[ $k ], $seg_top, $col_w, $header_h, $mode );
                $drew_this = true;

                $debug[] = array(
                    'page' => $page_no, 'tier' => $tier . ( $cols > 1 ? '(col' . $cols . '-' . ( $k + 1 ) . ')' : '' ),
                    'headline' => mb_substr( (string) ( $article['headline'] ?? '' ), 0, 16 ),
                    'x' => round( $col_x[ $k ], 1 ), 'y' => round( $seg_top, 1 ), 'w' => round( $col_w, 1 ), 'h' => round( $result['bottom_y'] - $seg_top, 1 ),
                    'continuation' => ! empty( $article['_continuation'] ),
                    'mode' => $mode,
                    'label_shown' => 'label' === $mode,
                    'is_first_in_block' => $is_first,
                    'body_top' => round( $seg_top + $header_h + $gap, 1 ),
                    'overflow' => ! empty( $result['overflow_body'] ),
                );

                $pdf->SetLineWidth( 'large' === $tier && 1 === $cols ? 0.5 : 0.25 );
                $pdf->Line( $col_x[ $k ], $result['bottom_y'] + ( $row_gap / 2 ), $col_x[ $k ] + $col_w, $result['bottom_y'] + ( $row_gap / 2 ) );

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

        $block_bottom = max( $col_bottom );
        if ( $block_bottom <= $block_top ) {
            // どの列も1件も描画できなかった（先頭記事の最初のセグメント
            // すら残り高さに収まらない）— これ以上このページには載らない。
            break;
        }

        // ブロックの外枠：列の境界線（ブロック全体の高さぶん）と、
        // ブロック終端の全幅罫線。
        if ( $cols > 1 ) {
            for ( $k = 1; $k < $cols; $k++ ) {
                $div_x = $col_x[ $k ] + $col_w + ( $col_gap / 2 );
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

        $block_top = $block_bottom;
    }

    return array( 'bottom_y' => $block_top, 'drew_any' => $drew_any, 'debug' => $debug );
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
    foreach ( $articles as $a ) {
        $t = isset( $a['tier'] ) && isset( $tiers[ $a['tier'] ] ) ? $a['tier'] : 'small';
        $a['_tier']  = $t;
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

    $page1_stack = hatakiti_occult_pdf_stack_articles( $queue, $pdf, $font_regular, $font_bold, $c['margin_l'], $page1_col_top, $full_w, $page1_col_h_full, 1 );
    $debug_log   = $page1_stack['debug'];

    // footer（出典一覧＋編集後記）ぶんの余白を毎ページ機械的に確保
    // すると、記事がまだ続くページにも常に「footerのための空白」が
    // 残ってしまい、「前ページに十分な空きがあるのに続きを次ページへ
    // 送る」不具合の直接の原因になる。そこで、記事ページは常にフル
    // 高さを使って積み、footerは「記事がすべて尽きた最後のページ」の
    // 残り高さに収まる場合だけそこへ同居させ、収まらない場合のみ
    // footer専用の1ページを追加する。
    $footer_margin    = 3.0;
    $footer_y         = null;
    $max_pages_safety = 12;
    $page_no          = 1;

    if ( empty( $queue ) ) {
        // 全記事が1面だけで収まった。footerが1面の残り高さに収まるか
        // を確認し、収まらなければfooter専用ページを足す。
        $bottom_y        = max( $page1_stack['bottom_y'], $page1_col_top + 40 );
        $remaining_on_p1 = ( $page1_col_top + $page1_col_h_full ) - $bottom_y;
        if ( $remaining_on_p1 >= $footer_h + $footer_margin ) {
            $footer_y = $bottom_y;
        }
    } else {
        $page_no = 2;
        while ( ! empty( $queue ) && $page_no <= $max_pages_safety ) {
            $pdf->AddPage();
            hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c, $page_no );
            $page_n_col_top    = $c['margin_t'] + $c['page2_header_h'];
            $page_n_col_h_full = ( $c['page_h'] - $c['margin_b'] ) - $page_n_col_top;

            $page_n_stack = hatakiti_occult_pdf_stack_articles( $queue, $pdf, $font_regular, $font_bold, $c['margin_l'], $page_n_col_top, $full_w, $page_n_col_h_full, $page_no );
            $debug_log    = array_merge( $debug_log, $page_n_stack['debug'] );

            if ( empty( $queue ) ) {
                // このページで記事が尽きた。footerがこのページの
                // 残り高さに収まるかを確認する。
                $bottom_y        = max( $page_n_stack['bottom_y'], $page_n_col_top + 30 );
                $remaining_on_pg = ( $page_n_col_top + $page_n_col_h_full ) - $bottom_y;
                if ( $remaining_on_pg >= $footer_h + $footer_margin ) {
                    $footer_y = $bottom_y;
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
        // フォールバック：footer専用の1ページを追加する。
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
