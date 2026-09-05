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
define( 'HATAKITI_OCCULT_PDF_GENERATOR_VERSION', '15' );

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
 * 本文「1段あたりの最大文字数」。今回の組版方式の中心定数（指示書§1）
 * — tier・記事・文字量によらず、1つの縦書き列（段）はこの文字数を
 * 超えない。hatakiti_occult_pdf_column_capacity() がこれを実際の描画
 * 時の上限としてハード制約するため、「箱が高いほど1列に詰め込める文字
 * 数が増える」という、段の高さが不揃いになる直接の原因を断つ。
 */
define( 'HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN', 20 );

/**
 * 重要度（tier）の本文フォントサイズから、「本文1段」＝
 * HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN文字ぶんの物理高さ（mm）を
 * 計算する。記事の箱の本文部分は、続きも含め常にこの1段ぶんの高さ
 * ちょうどになる — 段を複数個積んで1つの箱にする（＝1箱が2段分3段分の
 * 高さを持つ）ことはしない。記事がこの1段に収まりきらなければ、続きは
 * 同じ高さの次の箱（罫線で区切られた次の段）へ送る（指示書§2/§5/§6）。
 * +1文字ぶん余裕を持たせるのは、hatakiti_occult_pdf_column_capacity()
 * 側の禁則処理向け安全マージン（floor(...)−1）と辻褄を合わせ、この
 * 高さで実際に描画すると必ずちょうど
 * HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN文字が上限になるようにするため。
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
 */
function hatakiti_occult_pdf_fullwidth_digits( $text ) {
    static $map = array(
        '0' => '０', '1' => '１', '2' => '２', '3' => '３', '4' => '４',
        '5' => '５', '6' => '６', '7' => '７', '8' => '８', '9' => '９',
    );
    return strtr( (string) $text, $map );
}

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
        'tier_fonts'      => array(
            'large'  => array( 'headline' => 18.5, 'body' => 10.8 ),
            'medium' => array( 'headline' => 13.0, 'body' => 9.4 ),
            'small'  => array( 'headline' => 10.0, 'body' => 8.5 ),
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
 * 半角数字2桁ペアリング（縦中横）＋回転文字（ー〜…）を考慮しつつ、
 * 段落（\n\n区切り）ごとに「1文字ぶんの描画単位」の配列へ分解する。
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
        'ー', '〜', '…', '‥', '―', '—', '‐',
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

                if ( 4 === $run_len ) {
                    // 4桁の西暦（例: 2026）は縦中横にせず、1桁ずつ通常の
                    // 縦書き文字として配置する（指示書§10-13）。
                    for ( $k = $i; $k < $j; $k++ ) {
                        $units[] = array( 'type' => 'char', 'ch' => $chars[ $k ] );
                    }
                } else {
                    // 4桁以外は既存どおり、2桁ずつの縦中横ペアリング
                    // （余りは1桁の通常文字）。
                    $k = $i;
                    while ( $k < $j ) {
                        $pair_end = min( $k + 2, $j );
                        $pair     = implode( '', array_slice( $chars, $k, $pair_end - $k ) );
                        if ( 2 === mb_strlen( $pair ) ) {
                            $units[] = array( 'type' => 'tcy', 'ch' => $pair );
                        } else {
                            $units[] = array( 'type' => 'char', 'ch' => $pair );
                        }
                        $k = $pair_end;
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
            $cx = $col_left + ( $col_pitch / 2 );
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
            $punct_pt = $font_pt * 0.62;
            $pdf->SetFontSize( $punct_pt );
            $pdf->SetXY( $col_left + ( $col_pitch * 0.10 ), $y - ( $char_h * 0.32 ) );
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
 * 題字（1ページ目のみ）。「週刊オカルト新聞」を主題字として横書きで大きく
 * 掲載し、号数・発行日・号のサブタイトルを添える。二重罫で区切る。
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
    $right_bits = array();
    if ( $issue_id ) {
        $right_bits[] = '第' . $issue_id . '号';
    }
    if ( $issue_date ) {
        $right_bits[] = $issue_date . '発行';
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
 * 記事（または続き）をこの場に置く場合に必要な箱の高さ（mm）を返す。
 * 本文部分は hatakiti_occult_pdf_body_segment_h() が決める（途中
 * セグメントは1段固定、最終セグメントは縮小）。
 *
 * 新規記事（続きでない）はこれに加えて見出し・見出し-本文間隔・出典
 * ストリップぶんを載せる。大見出しのサイズはtierで変わってよいが、
 * それは見出し部分だけの話であり本文の段の高さには影響しない
 * （指示書§21「大見出しと本文段は分離して考える」「LARGEだから本文
 * 1段の高さまで大きくすることは禁止」）。続きは見出しを再表示しない
 * ため、箱の高さは本文部分そのものになる。
 */
function hatakiti_occult_pdf_segment_box_h( $pdf, $font_bold, $article, $tier, $box_w ) {
    $body_h = hatakiti_occult_pdf_body_segment_h( $article, $tier );
    if ( ! empty( $article['_continuation'] ) ) {
        return $body_h;
    }
    $fonts    = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $headline = (string) ( $article['headline'] ?? '' );
    $head_h   = hatakiti_occult_pdf_measure_headline_height( $pdf, $font_bold, $fonts['headline'], $headline, $box_w );
    return $head_h + HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM + HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM + $body_h;
}

/**
 * キュー中で、現在の残り高さ$remainingに箱が収まる最初の記事の
 * インデックスを返す（見つからなければnull）。$start_idxより前は
 * 探索しない。
 *
 * 続き記事は必ずキューの先頭（index 0）にしか現れない —
 * hatakiti_occult_pdf_requeue_or_shift()が続きを常に元の位置（先頭）
 * へ差し戻すため。したがって$start_idx>=1から探すこの関数が対象にする
 * のは常に「まだ開始していない新規記事」のみであり、これを手前へ
 * 繰り上げても、どの記事の連続するセグメントの間にも割り込まない
 * （指示書§7「同一記事の連続性」を壊さない）。空いた箱の容量を有効
 * 利用するための先読み（指示書§9/§10「入る記事を探す」）。
 */
function hatakiti_occult_pdf_find_fitting_index( $queue, $start_idx, $pdf, $font_bold, $box_w, $remaining ) {
    for ( $i = $start_idx; $i < count( $queue ); $i++ ) {
        $h = hatakiti_occult_pdf_segment_box_h( $pdf, $font_bold, $queue[ $i ], $queue[ $i ]['_tier'], $box_w );
        if ( $h <= $remaining ) {
            return $i;
        }
    }
    return null;
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
 * 指定した高さに収まる「自然な幅」を逆算する（大見出しゾーンの幅決定用）。
 * 見出し高さは幅にあまり依存しないため、ラフな仮幅で一度だけ見積もる
 * 近似で十分（最終的な描画幅は実際に割り当てた幅で改めて折り返される）。
 */
function hatakiti_occult_pdf_natural_width_for_height( $pdf, $font_bold, $article, $tier, $fixed_h ) {
    $fonts    = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $headline = (string) ( $article['headline'] ?? '' );
    $body     = (string) ( $article['body'] ?? '' );

    $probe_w = 95.0;
    $head_h  = hatakiti_occult_pdf_measure_headline_height( $pdf, $font_bold, $fonts['headline'], $headline, $probe_w );

    list( $unit_count, ) = hatakiti_occult_pdf_count_units( $body );
    $body_char_h    = $fonts['body'] * 0.3528;
    $body_col_pitch = $body_char_h * 1.08;
    $body_h_budget  = max( 10, $fixed_h - $head_h - HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM - HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM );
    $capacity       = hatakiti_occult_pdf_column_capacity( $body_h_budget, $body_char_h );
    $cols_needed    = max( 1, (int) ceil( $unit_count / $capacity ) );

    return $cols_needed * $body_col_pitch;
}

/**
 * 1記事を「箱」(x=左端, y=上端, w=幅, h=高さ)の中に描画する。
 *
 * 指示書の核心：Article = 面積であり Article = 1列 ではない。見出しは
 * 横書きで箱の全幅を使って上部に、本文は見出しの下で「箱の全幅を使った
 * 縦書き複数列」として組む。これにより1記事が細い縦ストリップに閉じ
 * こめられることを避ける。
 *
 * @return array array('overflow_body'=>array|null)
 */
function hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article, $tier, $box ) {
    $fonts    = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $headline = (string) ( $article['headline'] ?? '' );
    $body     = (string) ( $article['body'] ?? '' );

    $x = $box['x'];
    $y = $box['y'];
    $w = $box['w'];
    $h = $box['h'];

    $is_continuation = ! empty( $article['_continuation'] );

    // 見出し：横書き、箱の全幅で折り返し（新聞の見出し帯）。続きの箱は
    // 見出しを一切再表示しない — 本文が最初の見出しから自然に連続して
    // 読めることを優先する（指示書「続きに小見出しを再表示しない」
    // 「「続き」と表示して新聞らしくすることより、本文が自然につながっ
    // て読めることを優先」）。
    if ( $is_continuation || '' === $headline ) {
        $head_h = 0.0;
    } else {
        // 表示用に全角化した文字列で折り返し幅を測る・描くの両方を行う
        // — 測定と描画で異なる文字列を使うと折り返し行数がずれ、罫線と
        // 本文が重なる不具合の原因になる（過去に修正した問題と同じ種類）。
        $headline_display = hatakiti_occult_pdf_fullwidth_digits( $headline );
        $head_pt     = $fonts['headline'];
        $head_line_h = $head_pt * 0.3528 * 1.3;
        $pdf->SetFont( $font_bold, '', $head_pt );
        $head_h = max( $head_line_h, $pdf->getStringHeight( $w, $headline_display ) );
        $pdf->SetXY( $x, $y );
        $pdf->MultiCell( $w, $head_line_h, $headline_display, 0, 'L' );
    }

    // 続きの箱は見出し間隔・出典ストリップの両方を省略する（出典は記事
    // の最初の箱にすでに表示済みのため、情報は失われない）。
    $gap   = $is_continuation ? 0.0 : HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM;
    $src_h = $is_continuation ? 0.0 : HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM;
    $body_top = $y + $head_h + $gap;
    $body_h   = $h - ( $head_h + $gap ) - $src_h;

    $overflow_body = null;
    if ( $body_h > 2.0 ) {
        list( $unit_count, $body_paragraphs ) = hatakiti_occult_pdf_count_units( $body );

        $body_char_h    = $fonts['body'] * 0.3528;
        $body_col_pitch = $body_char_h * 1.08;
        $body_capacity  = hatakiti_occult_pdf_column_capacity( $body_h, $body_char_h );
        $needed_cols    = max( 1, (int) ceil( $unit_count / $body_capacity ) );
        $max_cols_by_w  = max( 0, (int) floor( $w / $body_col_pitch ) );
        $cols_to_use    = min( $needed_cols, $max_cols_by_w );

        if ( $cols_to_use < 1 ) {
            $overflow_body = $body_paragraphs;
        } else {
            $body_result = hatakiti_occult_pdf_layout_and_draw_columns( $pdf, $body_paragraphs, $x + $w, $body_top, $body_h, $cols_to_use, $fonts['body'], $font_regular );
            if ( $body_result['overflow'] ) {
                $overflow_body = $body_result['remainder'];
            }
        }
    } else {
        $overflow_body = hatakiti_occult_pdf_build_units( $body );
    }

    // 出典（横書き、箱の下端の専用ストリップ内に1行）。紙面先行型では
    // 箱の高さ($h)はグリッド単位の整数倍で決まっており、本文が短く
    // 終わった場合の余白は意図した余白として扱う（詰めない）ため、出典
    // 行も本文の実際の終端ではなく箱の下端に固定する — 罫線（箱の境界）
    // と揃った位置関係を保つ。狭い箱でも隣とぶつからないよう割当幅に
    // 収まる文字数まで短縮する（詳細な元記事タイトル・URLは紙面末尾の
    // 出典一覧に必ず載るので情報は失われない）。
    $source_lines = hatakiti_occult_pdf_source_lines( $article['news_item_ids'] ?? array() );
    if ( $source_lines && $w > 12 && $src_h > 0 ) {
        $src_font  = 6.3;
        $src_y     = $y + $h - $src_h + 0.6;
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

    return array( 'overflow_body' => $overflow_body );
}

/**
 * キュー先頭から記事を取り出しては、指定ゾーン(zone_x, zone_y, zone_w)に
 * 上から下へ積み上げる。1記事は常にゾーンの全幅を使う（Article=面積の
 * 原則）。高さが記事本文量に収まりきらない場合は続きを次のゾーン/ページ
 * のために先頭に差し戻す（articles_jsonそのものは一切変更しない）。
 *
 * @param array &$queue 先頭から処理。各要素に '_tier' が必須。
 * @return array array('bottom_y'=>mm, 'drew_any'=>bool, 'debug'=>array)
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
 * overflow_bodyがあれば「続きの箱」として同じキュー位置に差し戻し、
 * 無ければキューから取り除く。headlineフィールド自体は変更せず残すが、
 * draw_article_box()側が_continuationフラグを見て見出しの再表示を
 * 一切スキップする（指示書「続きに小見出しを再表示しない」）ため、
 * 実際の紙面には見出しも「（続き）」等の定型文字列も出ない。本文の
 * 続きが最初の見出しから自然につながって読める。本文そのものは一切
 * 変更せずそのまま続ける。
 */
function hatakiti_occult_pdf_requeue_or_shift( &$queue, $idx, $article, $overflow_body ) {
    if ( ! empty( $overflow_body ) ) {
        $continuation                  = $article;
        $continuation['_continuation'] = true;
        $continuation['body']          = hatakiti_occult_pdf_overflow_to_text( $overflow_body );
        $queue[ $idx ]                 = $continuation;
        return true;
    }
    array_splice( $queue, $idx, 1 );
    return false;
}

/**
 * 「紙面先行型」のゾーン組版。本文「1段」＝
 * HATAKITI_OCCULT_PDF_CHARS_PER_COLUMN文字ぶんの高さ
 * （hatakiti_occult_pdf_unit_h_mm）を基準にする。まだ本文が20文字を
 * 超えて残っている「途中セグメント」は常にこの1段ぶんの高さちょうど
 * になる（指示書§6「途中セグメントは同じ高さの箱を使用する」）。この
 * 箱で記事が完結する「最終セグメント」は、実際に必要な高さぶんまで
 * 縮小し、浮いた分は次の記事のために解放する
 * （hatakiti_occult_pdf_body_segment_h、指示書§16）。
 *
 *   - キュー先頭の記事（続きも含む）の箱高さ
 *     （hatakiti_occult_pdf_segment_box_h）が残り高さに収まる場合のみ
 *     描画する。まだ続きが出る場合は次の同じ高さの箱（次の段）へ。
 *   - 箱高さが残りに収まらない場合：先頭が続き記事なら、他の記事を
 *     割り込ませたり繰り上げたりは一切せず、そのままこのゾーンを終える
 *     （指示書§7「同一記事の連続性」）。先頭がまだ開始していない新規
 *     記事なら、後方のキューに「この残りに収まる記事」がないか探して
 *     繰り上げる（hatakiti_occult_pdf_find_fitting_index、指示書§9/§10
 *     「入る記事を探す」）— 続きは常にキュー先頭にしか現れないため、
 *     この繰り上げが他の記事の連続性を壊すことはない。
 *   - tierが同じ記事が2本連続するときは横並びペアとして組む
 *     （small/medium/largeいずれのtierでも可、指示書§12「2面以降も
 *     横並び記事を確保する」）。
 *   - 罫線は必ず段の境界に置く。本文の実際の終了位置がその境界より
 *     手前にあっても、そこに罫線は置かない（指示書§17相当）。
 *   - 続きの箱は見出しを再表示しない（draw_article_box側で処理）ため、
 *     本文が最初の見出しから連続して読める（指示書§8）。
 *
 * 段幅（$zone_w、pairの場合はその半分）は記事によらず常に固定。
 *
 * 【スコープ上の注記】今回の指示書が示す「複数のページレイアウト候補
 * （A|B、C|D、Eなど複数パターン）を作り、スコア関数で最良のものを選ぶ」
 * という汎用的な探索エンジンまでは実装していない。上記の、続きの連続性
 * を壊さない範囲での先読み・tier一致ペアの一般化・最終セグメント縮小
 * という具体的な改善に絞って対応した（詳細は最終報告に明記）。
 */
function hatakiti_occult_pdf_stack_articles( &$queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_y, $zone_w, $zone_h_budget, $page_no = 1 ) {
    $y           = $zone_y;
    $remaining   = $zone_h_budget;
    $row_gap     = HATAKITI_OCCULT_PDF_ROW_GAP_MM;
    $drew_any    = false;
    $debug       = array();
    $stall_key   = null;
    $stall_count = 0;

    while ( ! empty( $queue ) && $remaining > HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM ) {
        $article         = $queue[0];
        $tier            = $article['_tier'];
        $is_continuation = ! empty( $article['_continuation'] );

        // 安全弁：同じ記事が縮まないまま(=文字数が減らないまま)連続で
        // 続き扱いになった場合、無限/準無限ループで紙面全体を消費して
        // しまう不具合を将来にわたって防ぐ。3回連続で進捗が無ければ、
        // その時点までの内容を確定させて次の記事へ進む（削除ではなく
        // 「未掲載」として警告に回す — articles_jsonは変更しない）。
        $body_len_now = mb_strlen( (string) ( $article['body'] ?? '' ) );
        if ( $stall_key === $body_len_now ) {
            $stall_count++;
        } else {
            $stall_key   = $body_len_now;
            $stall_count = 0;
        }
        if ( $stall_count >= 3 ) {
            break;
        }

        // tierが同じ記事が2本並ぶ場合は横に並べてコンパクトに組む
        // （新聞の小記事の並びを模す — 指示書§12「2面以降も横並び記事
        // を確保する」に対応するため、small限定だったペア機構を
        // medium/largeにも一般化した）。1本だけ残っている場合や、次が
        // 同じtierでない場合は通常どおりゾーン全幅で処理する。
        if ( isset( $queue[1] ) && $queue[1]['_tier'] === $tier
            && in_array( $tier, array( 'small', 'medium', 'large' ), true ) ) {
            $pair_gap = 2.0;
            $pair_w   = ( $zone_w - $pair_gap ) / 2;
            $article2 = $queue[1];

            // 両記事の箱高さ（新規なら見出しぶん込み、続きなら本文
            // ぶんそのもの）のうち大きいほうを共有の高さとして使う —
            // 横並びのペアの下端を視覚的に揃えるため。どちらかが残り
            // 高さに収まらなければ、他の記事を割り込ませたりせず
            // このゾーンを終える（指示書§4/§13）。
            $h1    = hatakiti_occult_pdf_segment_box_h( $pdf, $font_bold, $article, $tier, $pair_w );
            $h2    = hatakiti_occult_pdf_segment_box_h( $pdf, $font_bold, $article2, $tier, $pair_w );
            $box_h = max( $h1, $h2 );
            if ( $remaining < $box_h ) {
                break;
            }

            $box_r = array( 'x' => $zone_x + $pair_w + $pair_gap, 'y' => $y, 'w' => $pair_w, 'h' => $box_h );
            $box_l = array( 'x' => $zone_x, 'y' => $y, 'w' => $pair_w, 'h' => $box_h );
            $r1 = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article, $tier, $box_r );
            $r2 = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article2, $tier, $box_l );
            $drew_any = true;

            $debug[] = array( 'page' => $page_no, 'tier' => $tier . '(pair-R)', 'headline' => mb_substr( (string) ( $article['headline'] ?? '' ), 0, 16 ), 'x' => round( $box_r['x'], 1 ), 'y' => round( $y, 1 ), 'w' => round( $pair_w, 1 ), 'h' => round( $box_h, 1 ), 'remaining_before' => round( $remaining, 1 ), 'overflow' => ! empty( $r1['overflow_body'] ) );
            $debug[] = array( 'page' => $page_no, 'tier' => $tier . '(pair-L)', 'headline' => mb_substr( (string) ( $article2['headline'] ?? '' ), 0, 16 ), 'x' => round( $box_l['x'], 1 ), 'y' => round( $y, 1 ), 'w' => round( $pair_w, 1 ), 'h' => round( $box_h, 1 ), 'remaining_before' => round( $remaining, 1 ), 'overflow' => ! empty( $r2['overflow_body'] ) );

            $pdf->SetLineWidth( 0.25 );
            $pdf->Line( $zone_x + $pair_w + ( $pair_gap / 2 ), $y, $zone_x + $pair_w + ( $pair_gap / 2 ), $y + $box_h );
            $pdf->Line( $zone_x, $y + $box_h + ( $row_gap / 2 ), $zone_x + $zone_w, $y + $box_h + ( $row_gap / 2 ) );

            $still1 = hatakiti_occult_pdf_requeue_or_shift( $queue, 0, $article, $r1['overflow_body'] );
            // queue[0]が続きとして残っている場合、2本目は元々queue[1]に
            // いたので、続きが挿入された分インデックスがずれていない
            // ことを確認して処理する。
            $idx2 = $still1 ? 1 : 0;
            hatakiti_occult_pdf_requeue_or_shift( $queue, $idx2, $article2, $r2['overflow_body'] );

            $y         += $box_h + $row_gap;
            $remaining -= ( $box_h + $row_gap );
            continue;
        }

        // 単独記事：この記事（続きなら本文1段そのもの、新規なら見出し
        // ぶんも込み）の箱高さが残りに収まらない場合。
        $box_h = hatakiti_occult_pdf_segment_box_h( $pdf, $font_bold, $article, $tier, $zone_w );
        if ( $remaining < $box_h ) {
            if ( $is_continuation ) {
                // 続き記事はキュー内で必ず先頭にいる（＝この記事の連続
                // する続き）ため、これを飛ばして他の記事を先に描くと
                // 同一記事の間に別記事を割り込ませることになる。それは
                // 絶対に禁止（指示書§7）なので、繰り上げず、そのまま
                // このゾーンを終える。
                break;
            }
            // 新規記事はまだ何も開始していないので、後方のキューに
            // 「この残りに収まる記事」がないか探して繰り上げてよい —
            // 探索対象は常に別の新規記事であり（続きは先頭にしか
            // 現れないため）、どの記事の連続性も壊さない（指示書§9/§10）。
            $fit_idx = hatakiti_occult_pdf_find_fitting_index( $queue, 1, $pdf, $font_bold, $zone_w, $remaining );
            if ( null === $fit_idx ) {
                break;
            }
            $promoted = $queue[ $fit_idx ];
            array_splice( $queue, $fit_idx, 1 );
            array_unshift( $queue, $promoted );
            $article         = $queue[0];
            $tier            = $article['_tier'];
            $is_continuation = ! empty( $article['_continuation'] );
            $box_h           = hatakiti_occult_pdf_segment_box_h( $pdf, $font_bold, $article, $tier, $zone_w );
        }

        // 段幅は常に$zone_wのまま固定（記事の長さによって変えない —
        // 指示書§19）。本文部分は常に「本文1段」ぶんの固定高さ —
        // 記事の文字量では変えない。まだ続く場合は続きを次の同じ高さの
        // 箱（次の段）へ送り、この箱で完結する場合は最後の段の余りを
        // 許容する（指示書§6/§7/§8）。罫線は必ずこの段の境界に置く。
        $box    = array( 'x' => $zone_x, 'y' => $y, 'w' => $zone_w, 'h' => $box_h );
        $result = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article, $tier, $box );
        $drew_any = true;

        $debug[] = array(
            'page' => $page_no, 'tier' => $tier,
            'headline' => mb_substr( (string) ( $article['headline'] ?? '' ), 0, 16 ),
            'x' => round( $zone_x, 1 ), 'y' => round( $y, 1 ), 'w' => round( $zone_w, 1 ), 'h' => round( $box_h, 1 ),
            'continuation' => $is_continuation,
            'remaining_before' => round( $remaining, 1 ),
            'overflow' => ! empty( $result['overflow_body'] ),
        );

        $pdf->SetLineWidth( 'large' === $tier ? 0.5 : 0.25 );
        $pdf->Line( $zone_x, $y + $box_h + ( $row_gap / 2 ), $zone_x + $zone_w, $y + $box_h + ( $row_gap / 2 ) );

        hatakiti_occult_pdf_requeue_or_shift( $queue, 0, $article, $result['overflow_body'] );

        $y         += $box_h + $row_gap;
        $remaining -= ( $box_h + $row_gap );
    }

    return array( 'bottom_y' => $y, 'drew_any' => $drew_any, 'debug' => $debug );
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

    $page1_col_top  = $c['margin_t'] + $c['masthead_h'];
    $page1_col_h_full = ( $c['page_h'] - $c['margin_b'] ) - $page1_col_top;

    // 紙面レイアウトの核心：まず紙面領域を「large記事ゾーン」（右側、
    // 内容量から算出した幅、ページ全高）と「left記事ゾーン」（medium/
    // small、残り幅すべて）に分割する。Article = 面積であり、
    // Article = 1列 ではない — 各記事はゾーンの全幅を使って複数列の
    // 縦書き本文を組む（指示書§7/§18）。
    $large_zone_w = 0;
    if ( $tiers['large'] ) {
        $natural_w = 0;
        foreach ( $tiers['large'] as $a ) {
            $natural_w += hatakiti_occult_pdf_natural_width_for_height( $pdf, $font_bold, $a, 'large', $page1_col_h_full );
        }
        $large_zone_w = $natural_w * 1.6; // 見出し帯・余白ぶんの余裕
        $large_zone_w = max( $full_w * 0.34, min( $full_w * 0.62, $large_zone_w ) );
    }
    $zone_gap    = $large_zone_w > 0 ? 3.5 : 0;
    $left_zone_w = $full_w - $large_zone_w - $zone_gap;

    // --- 試み1: footerぶんを引いた高さで、両ゾーンとも1ページに収まるか ---
    $reserved_h_1page = $footer_h + 3.0;
    $h_try1           = $page1_col_h_full - $reserved_h_1page;

    $fits_one_page = false;
    if ( $h_try1 > 60 ) {
        list( $pdf, $font_regular, $font_bold ) = hatakiti_occult_pdf_new_tcpdf();
        $pdf->AddPage();
        hatakiti_occult_pdf_draw_masthead( $pdf, $font_regular, $font_bold, $c, $issue_subtitle, $issue_id, $issue_date );

        $large_queue = $tiers['large'];
        $other_queue = array_merge( $tiers['medium'], $tiers['small'] );

        $large_x = $c['margin_l'] + $left_zone_w + $zone_gap;
        $large_stack = $large_zone_w > 0
            ? hatakiti_occult_pdf_stack_articles( $large_queue, $pdf, $font_regular, $font_bold, $large_x, $page1_col_top, $large_zone_w, $h_try1, 1 )
            : array( 'bottom_y' => $page1_col_top, 'debug' => array() );

        $left_stack = hatakiti_occult_pdf_stack_articles( $other_queue, $pdf, $font_regular, $font_bold, $c['margin_l'], $page1_col_top, $left_zone_w, $h_try1, 1 );

        $debug_log = array_merge( $large_stack['debug'], $left_stack['debug'] );

        if ( empty( $large_queue ) && empty( $other_queue ) ) {
            $fits_one_page = true;
            if ( $large_zone_w > 0 ) {
                $pdf->SetLineWidth( 0.6 );
                $pdf->Line( $large_x - ( $zone_gap / 2 ), $page1_col_top, $large_x - ( $zone_gap / 2 ), $page1_col_top + $h_try1 );
            }
            $footer_y = max( $large_stack['bottom_y'], $left_stack['bottom_y'], $page1_col_top + 40 );
            hatakiti_occult_pdf_draw_footer( $pdf, $font_regular, $font_bold, $c, $all_sources, $editorial_summary, $footer_y + 2 );
        }
    }

    // --- 試み2: 2ページ構成（large記事はページ1でフル高さを使い、
    //     medium/smallはページ1左ゾーン→ページ2の順に流れる） ---
    if ( ! $fits_one_page ) {
        list( $pdf, $font_regular, $font_bold ) = hatakiti_occult_pdf_new_tcpdf();
        $pdf->AddPage();
        hatakiti_occult_pdf_draw_masthead( $pdf, $font_regular, $font_bold, $c, $issue_subtitle, $issue_id, $issue_date );

        $large_queue = $tiers['large'];
        $other_queue = array_merge( $tiers['medium'], $tiers['small'] );

        $large_x = $c['margin_l'] + $left_zone_w + $zone_gap;
        $large_stack = $large_zone_w > 0
            ? hatakiti_occult_pdf_stack_articles( $large_queue, $pdf, $font_regular, $font_bold, $large_x, $page1_col_top, $large_zone_w, $page1_col_h_full, 1 )
            : array( 'bottom_y' => $page1_col_top, 'debug' => array() );
        if ( $large_zone_w > 0 ) {
            $pdf->SetLineWidth( 0.6 );
            $pdf->Line( $large_x - ( $zone_gap / 2 ), $page1_col_top, $large_x - ( $zone_gap / 2 ), $page1_col_top + $page1_col_h_full );
        }

        $left_stack = hatakiti_occult_pdf_stack_articles( $other_queue, $pdf, $font_regular, $font_bold, $c['margin_l'], $page1_col_top, $left_zone_w, $page1_col_h_full, 1 );

        $debug_log = array_merge( $large_stack['debug'], $left_stack['debug'] );

        // ページ2：large残り（稀）＋medium/small残りを、紙面全幅の
        // 単一ゾーンとして続きから積み上げる。
        $page2_queue = array_merge( $large_queue, $other_queue );

        // footer（出典一覧＋編集後記）ぶんの余白を毎ページ機械的に確保
        // すると、記事がまだ続くページにも常に「footerのための空白」が
        // 残ってしまい、「前ページに十分な空きがあるのに続きを次ページへ
        // 送る」不具合の直接の原因になる（指示書「グリッドは空白を作る
        // ための制約ではない」「紙面利用効率＞機械的な余白確保」）。
        // そこで、記事ページは常にフル高さを使って積み、footerは
        // 「記事がすべて尽きた最後のページ」の残り高さに収まる場合だけ
        // そこへ同居させ、収まらない場合のみfooter専用の1ページを追加
        // する。
        $footer_margin    = 3.0;
        $footer_y         = null;
        $max_pages_safety = 12;

        if ( empty( $page2_queue ) ) {
            // 全記事が1ページ目だけで収まった（試み2のフル高さでも
            // ページ2が不要だったケース）。footerが1ページ目の残り高さ
            // に収まるかを確認し、収まらなければfooter専用ページを足す。
            $bottom_y        = max( $large_stack['bottom_y'], $left_stack['bottom_y'], $page1_col_top + 40 );
            $remaining_on_p1 = ( $page1_col_top + $page1_col_h_full ) - $bottom_y;
            if ( $remaining_on_p1 >= $footer_h + $footer_margin ) {
                $footer_y = $bottom_y;
            }
        } else {
            $page_no = 2;
            while ( ! empty( $page2_queue ) && $page_no <= $max_pages_safety ) {
                $pdf->AddPage();
                hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c, $page_no );
                $page_n_col_top    = $c['margin_t'] + $c['page2_header_h'];
                $page_n_col_h_full = ( $c['page_h'] - $c['margin_b'] ) - $page_n_col_top;

                // footerぶんを引かず、ページのフル高さをそのまま記事に使う。
                $page_n_stack = hatakiti_occult_pdf_stack_articles( $page2_queue, $pdf, $font_regular, $font_bold, $c['margin_l'], $page_n_col_top, $full_w, $page_n_col_h_full, $page_no );
                $debug_log    = array_merge( $debug_log, $page_n_stack['debug'] );

                if ( empty( $page2_queue ) ) {
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

            if ( ! empty( $page2_queue ) ) {
                // 安全弁: $max_pages_safety ページでも収まらない極端な分量の
                // 場合のみ到達する想定（通常運用では発生しない）。
                // articles_jsonは変更しない。
                $remaining_headlines = array();
                foreach ( $page2_queue as $r_article ) {
                    $remaining_headlines[] = mb_substr( (string) ( $r_article['headline'] ?? '' ), 0, 20 ) . '（' . mb_strlen( (string) ( $r_article['body'] ?? '' ) ) . '字）';
                }
                $warnings[] = "記事量が非常に多く、{$max_pages_safety}ページ以内に紙面へ収まりませんでした。記事本文は自動で削除していません（articles_jsonは無変更）。未掲載: " . implode( ' / ', $remaining_headlines );
            }
        }

        if ( null === $footer_y ) {
            // 最後の記事ページの残り高さにfooterが収まらなかった場合の
            // フォールバック：footer専用の1ページを追加する。
            $pdf->AddPage();
            hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c, isset( $page_no ) ? $page_no : 2 );
            $footer_y = $c['margin_t'] + $c['page2_header_h'];
        }

        hatakiti_occult_pdf_draw_footer( $pdf, $font_regular, $font_bold, $c, $all_sources, $editorial_summary, $footer_y + 2 );
    }

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
