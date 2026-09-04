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
        'masthead_h'      => 25.0, // 1ページ目のみ
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

    $rotate_chars = array( 'ー', '〜', '…', '‥' );

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
                $run = $ch;
                $j   = $i + 1;
                while ( $j < $n && ctype_digit( $chars[ $j ] ) && mb_strlen( $run ) < 2 ) {
                    $run .= $chars[ $j ];
                    $j++;
                }
                if ( mb_strlen( $run ) === 2 ) {
                    $units[] = array( 'type' => 'tcy', 'ch' => $run );
                } else {
                    $units[] = array( 'type' => 'char', 'ch' => $run );
                }
                $i = $j;
                continue;
            }
            if ( in_array( $ch, $rotate_chars, true ) ) {
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
            $digits  = mb_str_split( $unit['ch'], 1, 'UTF-8' );
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
        } else {
            $pdf->SetXY( $col_left, $y );
            $pdf->Cell( $col_pitch, $char_h, $unit['ch'], 0, 0, 'C' );
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

    $columns_drawn = 0;
    $pos           = 0;
    $total         = count( $flat );
    $remainder_flat = array();

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

        // 行末禁則：最後が「開き括弧」なら次のコラムへ送り戻す
        if ( count( $slots ) > 0 ) {
            $last = end( $slots );
            if ( is_array( $last ) && 'char' === $last['type'] && in_array( $last['ch'], $cannot_end, true ) && $pos < $total ) {
                array_pop( $slots );
                $pos--;
            }
        }

        // 行頭禁則：次に置かれる予定の文字が「閉じ約物」なら、
        // このコラムに1文字だけ押し込む（capacity+1まで許容）。
        if ( $pos < $total && ! ( isset( $flat[ $pos ]['break'] ) && $flat[ $pos ]['break'] ) ) {
            $next_unit = $flat[ $pos ]['unit'];
            if ( 'char' === $next_unit['type'] && in_array( $next_unit['ch'], $cannot_start, true ) ) {
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
    );
}

/**
 * 題字（1ページ目のみ）。「週刊オカルト新聞」を主題字として横書きで大きく
 * 掲載し、号数・発行日・号のサブタイトルを添える。二重罫で区切る。
 */
function hatakiti_occult_pdf_draw_masthead( $pdf, $font_regular, $font_bold, $c, $issue_subtitle, $issue_id, $issue_date ) {
    $top = $c['margin_t'];
    $w   = $c['page_w'] - $c['margin_l'] - $c['margin_r'];

    $pdf->SetFont( $font_bold, '', 30 );
    $pdf->SetXY( $c['margin_l'], $top );
    $pdf->Cell( $w, 16, '週刊オカルト新聞', 0, 0, 'C' );

    $pdf->SetFont( $font_regular, '', 9 );
    $sub_y = $top + 17;
    $left_text  = $issue_subtitle ? mb_substr( $issue_subtitle, 0, 60 ) : '';
    $right_bits = array();
    if ( $issue_id ) {
        $right_bits[] = '第' . $issue_id . '号';
    }
    if ( $issue_date ) {
        $right_bits[] = $issue_date . '発行';
    }
    $right_text = implode( '　', $right_bits );

    $pdf->SetXY( $c['margin_l'], $sub_y );
    $pdf->Cell( $w * 0.62, 6, $left_text, 0, 0, 'L' );
    $pdf->SetXY( $c['margin_l'] + $w * 0.62, $sub_y );
    $pdf->Cell( $w * 0.38, 6, $right_text, 0, 0, 'R' );

    $rule_y = $top + $c['masthead_h'] - 4;
    $pdf->SetLineWidth( 0.9 );
    $pdf->Line( $c['margin_l'], $rule_y, $c['page_w'] - $c['margin_r'], $rule_y );
    $pdf->SetLineWidth( 0.2 );
    $pdf->Line( $c['margin_l'], $rule_y + 1.3, $c['page_w'] - $c['margin_r'], $rule_y + 1.3 );
}

function hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c, $page_no = 2 ) {
    $pdf->SetFont( $font_regular, '', 9 );
    $pdf->SetXY( $c['margin_l'], $c['margin_t'] );
    $pdf->Cell( $c['page_w'] - $c['margin_l'] - $c['margin_r'], 5, '週刊オカルト新聞　（第' . (int) $page_no . '面）', 0, 0, 'L' );
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
 * 続きの箱であることを示す小さなラベル。見出し帯を完全に省略すると、
 * ページをまたいだ・ゾーンをまたいだ続きの本文が「何の記事か分からない
 * 孤立したテキストの塊」に見えてしまう（実際の紙面画像で確認された
 * 問題）。フルサイズの見出しにはせず、常に一定の小さいサイズで表示する
 * ことで、続きだと分かるようにしつつ縦方向の消費は最小限に抑える。
 */
define( 'HATAKITI_OCCULT_PDF_CONTINUATION_LABEL', '（続き）' );
define( 'HATAKITI_OCCULT_PDF_CONTINUATION_FONT_PT', 6.5 );

/**
 * レイアウト再修正（罫線と本文の干渉・段組みの極端な不揃い対策）で
 * 導入した版面マージン定数。
 *
 *   BODY_BOTTOM_MARGIN_MM: 各列の最終文字と「その列に許された高さの
 *     下端」との間に必ず確保する余白。禁則処理で1文字ぶん押し込まれる
 *     最悪ケースでも、この余白ぶんは絶対に消費されない（下記
 *     hatakiti_occult_pdf_column_capacity() が担保する）。
 *   NORMAL_HEAD_GAP_MM / CONTINUATION_HEAD_GAP_MM: 見出し（または
 *     「続き」ラベル）と本文の間の余白。旧実装の続き用0.4mmは文字が
 *     ラベルに接触して見える主因だったため引き上げる。
 *   ROW_GAP_MM: 縦に積んだ記事ボックス間の区切り線の余白（線の上下
 *     それぞれに ROW_GAP_MM/2 ずつ）。
 *   MIN_BODY_ROWS: 1つの記事ボックスを「その場で」描画してよいと判断
 *     するために最低限必要な、本文1列あたりの文字数。これを満たせない
 *     ほど残り領域が少ない場合は、その記事（続きを含む）を丸ごと次の
 *     ゾーン／ページへ送る — 罫線ぎりぎりに1行だけ押し込むような
 *     極端に窮屈な箱を二度と作らないための仕組み。
 */
define( 'HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM', 2.2 );
define( 'HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM', 2.2 );
define( 'HATAKITI_OCCULT_PDF_CONTINUATION_HEAD_GAP_MM', 1.8 );
define( 'HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM', 4.2 );
define( 'HATAKITI_OCCULT_PDF_ROW_GAP_MM', 3.2 );
define( 'HATAKITI_OCCULT_PDF_MIN_BODY_ROWS', 4 );

/**
 * 与えられた列の高さ(mm)と1文字の高さ(mm)から、実際に配置してよい
 * 文字数（capacity）を返す。HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM
 * ぶんを必ず差し引いてから計算するため、禁則処理で最大1文字押し込まれる
 * 最悪ケースでも、列の下端との間に必ず余白が残る。本文サイズの見積もり
 * （estimate）と実際の描画（draw）の両方がこの同じ関数を通ることで、
 * 見積もりと実描画のずれ（＝罫線と文字が重なる不具合の原因）を防ぐ。
 */
function hatakiti_occult_pdf_column_capacity( $col_h_mm, $char_h ) {
    $usable = max( 0, $col_h_mm - HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM );
    return max( 1, (int) floor( $usable / $char_h ) - 1 );
}

/**
 * 記事（続きを含む）を「今このゾーンの残り高さに描画してよい」と判断
 * するための最低限の高さ(mm)。見出し1行分＋見出し-本文間の余白＋本文
 * 最低4文字分＋出典ストリップ＋下端マージン。これを満たさない残り高さ
 * しかない場合は描画せず、記事を丸ごと次のゾーン／ページへ送る
 * （指示書の最重要方針：文字を詰め込んでページ数を減らさない）。
 */
/**
 * ゾーン全幅をそのまま使うと、短い本文（特に続きの残り）が「1行だけの
 * 横長の帯」になってしまう問題への対策。ゾーン幅をそのまま使った場合の
 * 列数で本文unit数を割ると行数が最低本数(MIN_BODY_ROWS)を満たさない
 * 場合、行数を確保できる分だけ幅を狭める（右端はゾーンの右端に揃えた
 * まま、左側の余りを空ける）。長い本文（行数が自然に足りるもの）では
 * $zone_wをそのまま返すため、通常記事の組み方は変わらない。
 */
function hatakiti_occult_pdf_effective_box_width( $unit_count, $zone_w, $font_pt ) {
    if ( $unit_count <= 0 ) {
        return $zone_w;
    }
    $char_h    = $font_pt * 0.3528;
    $col_pitch = $char_h * 1.08;

    // +1列ぶんの余裕: 見積もり(rows計算)と実描画(capacity計算)は同じ値に
    // 収束するとは限らず、ぎりぎりの幅だと丸め差で1文字だけ収まりきらず
    // 極端に小さい「続き」が新たに生まれることがあるため、常に1列分の
    // 余裕を持たせておく。
    $cols_for_min_rows  = max( 1, (int) ceil( $unit_count / HATAKITI_OCCULT_PDF_MIN_BODY_ROWS ) ) + 1;
    $width_for_min_rows = $cols_for_min_rows * $col_pitch;

    return max( $col_pitch * 2, min( $zone_w, $width_for_min_rows ) );
}

function hatakiti_occult_pdf_min_viable_box_height( $pdf, $font_bold, $article, $tier ) {
    $fonts           = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $is_continuation = ! empty( $article['_continuation'] );

    $head_pt     = hatakiti_occult_pdf_headline_font_pt( $article, $fonts['headline'] );
    $head_line_h = $head_pt * 0.3528 * 1.3;
    $gap         = $is_continuation ? HATAKITI_OCCULT_PDF_CONTINUATION_HEAD_GAP_MM : HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM;
    $src_h       = $is_continuation ? 0.0 : HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM;
    $body_char_h = $fonts['body'] * 0.3528;

    return $head_line_h + $gap + ( HATAKITI_OCCULT_PDF_MIN_BODY_ROWS * $body_char_h ) + $src_h + HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM;
}

/**
 * 見出し（横書き・箱の全幅で折り返し）に必要な高さ(mm)を、実際には
 * 描画せず見積もる。
 */
function hatakiti_occult_pdf_measure_headline_height( $pdf, $font_bold, $font_pt, $text, $box_w ) {
    if ( '' === (string) $text ) {
        return 0.0;
    }
    $pdf->SetFont( $font_bold, '', $font_pt );
    $line_h = $font_pt * 0.3528 * 1.3;
    $h = $pdf->getStringHeight( $box_w, (string) $text );
    return max( $line_h, $h );
}

/**
 * 記事が「続きの箱」かどうかに応じて、見出しに使う実際のフォントサイズ
 * を返す（続きは常に小さい固定サイズ、通常はtierの見出しサイズ）。
 */
function hatakiti_occult_pdf_headline_font_pt( $article, $tier_headline_pt ) {
    return ! empty( $article['_continuation'] ) ? HATAKITI_OCCULT_PDF_CONTINUATION_FONT_PT : $tier_headline_pt;
}

/**
 * 本文unit数と枠幅から、縦組みに必要な高さ(mm)を見積もる。
 */
function hatakiti_occult_pdf_estimate_body_height( $unit_count, $box_w_mm, $font_pt ) {
    $char_h     = $font_pt * 0.3528;
    $col_pitch  = $char_h * 1.08;
    $n_cols_fit = max( 1, (int) floor( $box_w_mm / $col_pitch ) );
    $rows       = max( 1, (int) ceil( $unit_count / $n_cols_fit ) );
    // 実描画側は hatakiti_occult_pdf_column_capacity()（禁則の押し出し
    // 余裕1行＋下端の安全マージン）を使うため、見積もりもそれと整合する
    // ように多めに余裕を持たせる（+2行）。見積もりと実描画の丸め差で
    // 数文字だけ収まりきらず、不自然に小さい「続き」が新たに生まれる
    // ことを防ぐための保守的なマージン。
    return ( $rows + 2 ) * $char_h + HATAKITI_OCCULT_PDF_BODY_BOTTOM_MARGIN_MM;
}

/**
 * 記事1本を、幅$box_wの箱に収めた場合に必要な全体の高さ(mm)を見積もる
 * （見出し＋余白＋本文＋出典ストリップ）。ゾーン幅・高さを決める前の
 * 事前見積もりに使う。
 */
function hatakiti_occult_pdf_estimate_box_height( $pdf, $font_bold, $article, $tier, $box_w ) {
    $fonts    = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $headline = (string) ( $article['headline'] ?? '' );
    $body     = (string) ( $article['body'] ?? '' );

    $head_pt = hatakiti_occult_pdf_headline_font_pt( $article, $fonts['headline'] );
    $head_h = hatakiti_occult_pdf_measure_headline_height( $pdf, $font_bold, $head_pt, $headline, $box_w );
    list( $unit_count, ) = hatakiti_occult_pdf_count_units( $body );
    $body_h = hatakiti_occult_pdf_estimate_body_height( $unit_count, $box_w, $fonts['body'] );

    $is_continuation = ! empty( $article['_continuation'] );
    $gap   = $is_continuation ? HATAKITI_OCCULT_PDF_CONTINUATION_HEAD_GAP_MM : HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM;
    $src_h = $is_continuation ? 0.0 : HATAKITI_OCCULT_PDF_SOURCE_STRIP_H_MM;
    return $head_h + $gap + $body_h + $src_h;
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

    // 見出し：横書き、箱の全幅で折り返し（新聞の見出し帯）。
    // 続きの箱は「（続き）」という小さな固定サイズのラベルにする —
    // 見出し帯を完全に省略すると、ページ／ゾーンをまたいだ本文が
    // 「どの記事の続きか分からない孤立したテキスト」に見えてしまう
    // ことが実際の紙面画像で確認されたため。フルサイズの見出しにはせず
    // 縦方向の消費は最小限に抑える。
    if ( '' === $headline ) {
        $head_h = 0.0;
    } else {
        $head_pt     = hatakiti_occult_pdf_headline_font_pt( $article, $fonts['headline'] );
        $head_line_h = $head_pt * 0.3528 * 1.3;
        $pdf->SetFont( $font_bold, '', $head_pt );
        $head_h = max( $head_line_h, $pdf->getStringHeight( $w, $headline ) );
        $pdf->SetXY( $x, $y );
        $pdf->MultiCell( $w, $head_line_h, $headline, 0, 'L' );
    }

    // 続きの箱は出典ストリップを省略する（出典は記事の最初の箱に
    // すでに表示済みのため、情報は失われない）。
    $gap   = $is_continuation ? HATAKITI_OCCULT_PDF_CONTINUATION_HEAD_GAP_MM : HATAKITI_OCCULT_PDF_NORMAL_HEAD_GAP_MM;
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

    // 出典（横書き、箱の下端の専用ストリップ内に1行）。狭い箱でも隣と
    // ぶつからないよう割当幅に収まる文字数まで短縮する（詳細な元記事
    // タイトル・URLは紙面末尾の出典一覧に必ず載るので情報は失われない）。
    $source_lines = hatakiti_occult_pdf_source_lines( $article['news_item_ids'] ?? array() );
    if ( $source_lines && $w > 12 && $src_h > 0 ) {
        $src_font  = 6.3;
        $src_y     = $y + $h - $src_h + 0.6;
        $max_chars = max( 2, (int) floor( $w / ( $src_font * 0.55 ) ) );
        $sl        = $source_lines[0];
        $text      = mb_strlen( $sl['text'] ) > $max_chars ? mb_substr( $sl['text'], 0, $max_chars - 1 ) . '…' : $sl['text'];

        $pdf->SetFont( $font_regular, '', $src_font );
        $pdf->SetXY( $x, $src_y );
        $pdf->Cell( $w, 3.4, $text, 0, 0, 'L' );
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
 * 無ければキューから取り除く。見出しは固定の小さな「（続き）」ラベル
 * のみ（本文側に埋め込むテキストマーカー方式は、列容量が1〜2文字しか
 * ないほど詰まった状況でマーカー自体が一部しか収まらず、次周回で
 * 再度マーカーを継ぎ足して無限に伸び続けるバグを起こしたため採用しない
 * — 本文そのものは一切変更せずそのまま続ける）。
 */
function hatakiti_occult_pdf_requeue_or_shift( &$queue, $idx, $article, $overflow_body ) {
    if ( ! empty( $overflow_body ) ) {
        $continuation                  = $article;
        $continuation['headline']      = HATAKITI_OCCULT_PDF_CONTINUATION_LABEL;
        $continuation['_continuation'] = true;
        $continuation['body']          = hatakiti_occult_pdf_overflow_to_text( $overflow_body );
        $queue[ $idx ]                 = $continuation;
        return true;
    }
    array_splice( $queue, $idx, 1 );
    return false;
}

function hatakiti_occult_pdf_stack_articles( &$queue, $pdf, $font_regular, $font_bold, $zone_x, $zone_y, $zone_w, $zone_h_budget, $page_no = 1 ) {
    $y         = $zone_y;
    $remaining = $zone_h_budget;
    $row_gap   = HATAKITI_OCCULT_PDF_ROW_GAP_MM;
    $drew_any  = false;
    $debug     = array();
    $stall_key = null;
    $stall_count = 0;

    while ( ! empty( $queue ) && $remaining > 3.0 ) {
        $article = $queue[0];
        $tier    = $article['_tier'];

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

        // small記事は、2本並ぶ場合は横に並べて「小記事」らしくコンパクトに
        // 組む（新聞の小記事の並びを模す）。1本だけ残っている場合や、次が
        // small以外の場合は通常どおりゾーン全幅で処理する。横並びの方が
        // 縦方向の消費が少ないため、紙面の利用効率も上がる。
        if ( 'small' === $tier && isset( $queue[1] ) && 'small' === $queue[1]['_tier'] ) {
            $pair_gap = 2.0;
            $pair_w   = ( $zone_w - $pair_gap ) / 2;
            $article2 = $queue[1];

            // 残り高さがどちらか一方でも最低限の可読サイズに満たない
            // 場合は、この場では描画せずペアごと次のゾーン／ページへ
            // 送る — 罫線ぎりぎりの1行だけの箱を作らないため。
            $min_viable_pair = max(
                hatakiti_occult_pdf_min_viable_box_height( $pdf, $font_bold, $article, 'small' ),
                hatakiti_occult_pdf_min_viable_box_height( $pdf, $font_bold, $article2, 'small' )
            );
            if ( $remaining < $min_viable_pair ) {
                break;
            }

            $needed_h = max(
                hatakiti_occult_pdf_estimate_box_height( $pdf, $font_bold, $article, 'small', $pair_w ),
                hatakiti_occult_pdf_estimate_box_height( $pdf, $font_bold, $article2, 'small', $pair_w )
            );
            $actual_h = min( $needed_h, $remaining );

            // 右側（先に読む）＝queue[0]、左側＝queue[1]
            $box_r = array( 'x' => $zone_x + $pair_w + $pair_gap, 'y' => $y, 'w' => $pair_w, 'h' => $actual_h );
            $box_l = array( 'x' => $zone_x, 'y' => $y, 'w' => $pair_w, 'h' => $actual_h );
            $r1 = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article, 'small', $box_r );
            $r2 = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article2, 'small', $box_l );
            $drew_any = true;

            $debug[] = array( 'page' => $page_no, 'tier' => 'small(pair-R)', 'headline' => mb_substr( (string) ( $article['headline'] ?? '' ), 0, 16 ), 'x' => round( $box_r['x'], 1 ), 'y' => round( $y, 1 ), 'w' => round( $pair_w, 1 ), 'h' => round( $actual_h, 1 ), 'overflow' => ! empty( $r1['overflow_body'] ) );
            $debug[] = array( 'page' => $page_no, 'tier' => 'small(pair-L)', 'headline' => mb_substr( (string) ( $article2['headline'] ?? '' ), 0, 16 ), 'x' => round( $box_l['x'], 1 ), 'y' => round( $y, 1 ), 'w' => round( $pair_w, 1 ), 'h' => round( $actual_h, 1 ), 'overflow' => ! empty( $r2['overflow_body'] ) );

            $pdf->SetLineWidth( 0.25 );
            $pdf->Line( $zone_x + $pair_w + ( $pair_gap / 2 ), $y, $zone_x + $pair_w + ( $pair_gap / 2 ), $y + $actual_h );
            $pdf->Line( $zone_x, $y + $actual_h + ( $row_gap / 2 ), $zone_x + $zone_w, $y + $actual_h + ( $row_gap / 2 ) );

            $still1 = hatakiti_occult_pdf_requeue_or_shift( $queue, 0, $article, $r1['overflow_body'] );
            // queue[0]が続きとして残っている場合、2本目は元々queue[1]に
            // いたので、続きが挿入された分インデックスがずれていない
            // ことを確認して処理する。
            $idx2 = $still1 ? 1 : 0;
            hatakiti_occult_pdf_requeue_or_shift( $queue, $idx2, $article2, $r2['overflow_body'] );

            $y         += $actual_h + $row_gap;
            $remaining -= ( $actual_h + $row_gap );
            continue;
        }

        // 残り高さが最低限の可読サイズに満たない場合は、この場では
        // 描画せず記事（続きを含む）を丸ごと次のゾーン／ページへ送る。
        $min_viable = hatakiti_occult_pdf_min_viable_box_height( $pdf, $font_bold, $article, $tier );
        if ( $remaining < $min_viable ) {
            break;
        }

        // ゾーン全幅をそのまま使うと、本文が短い場合（特に続きの残り）に
        // 「1行だけの横長の帯」になり罫線ぎりぎりまで文字が詰まって見える
        // ため、行数を確保できる幅まで狭める（右端はゾーン右端のまま）。
        // 本文が十分長い通常記事ではeffective_w=$zone_wのまま変わらない。
        $body_font_pt = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ]['body'];
        list( $article_unit_count, ) = hatakiti_occult_pdf_count_units( (string) ( $article['body'] ?? '' ) );
        $effective_w = hatakiti_occult_pdf_effective_box_width( $article_unit_count, $zone_w, $body_font_pt );

        $needed_h = hatakiti_occult_pdf_estimate_box_height( $pdf, $font_bold, $article, $tier, $effective_w );
        $actual_h = min( $needed_h, $remaining );

        $box    = array( 'x' => $zone_x + ( $zone_w - $effective_w ), 'y' => $y, 'w' => $effective_w, 'h' => $actual_h );
        $result = hatakiti_occult_pdf_draw_article_box( $pdf, $font_regular, $font_bold, $article, $tier, $box );
        $drew_any = true;

        $debug[] = array(
            'page' => $page_no, 'tier' => $tier,
            'headline' => mb_substr( (string) ( $article['headline'] ?? '' ), 0, 16 ),
            'x' => round( $zone_x, 1 ), 'y' => round( $y, 1 ), 'w' => round( $zone_w, 1 ), 'h' => round( $actual_h, 1 ),
            'overflow' => ! empty( $result['overflow_body'] ),
        );

        $pdf->SetLineWidth( 'large' === $tier ? 0.5 : 0.25 );
        $pdf->Line( $zone_x, $y + $actual_h + ( $row_gap / 2 ), $zone_x + $zone_w, $y + $actual_h + ( $row_gap / 2 ) );

        hatakiti_occult_pdf_requeue_or_shift( $queue, 0, $article, $result['overflow_body'] );

        $y         += $actual_h + $row_gap;
        $remaining -= ( $actual_h + $row_gap );
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
            $text = $s['name'] . '「' . mb_substr( $s['title'], 0, 50 ) . '」';
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

    $editorial_summary = get_post_meta( $post_id, 'hatakiti_occult_editorial_summary', true );
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
        $unrecoverable = false;

        if ( ! empty( $page2_queue ) ) {
            $pdf->AddPage();
            hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c );
            $page2_col_top    = $c['margin_t'] + $c['page2_header_h'];
            $page2_col_h_full = ( $c['page_h'] - $c['margin_b'] ) - $page2_col_top;
            $reserved_h_2page = $footer_h + 1.5;
            $h_page2          = max( 40, $page2_col_h_full - $reserved_h_2page );

            // footerぶんの余白を確保した高さで積む。ここで収まりきらな
            // かった分（通常は数十字程度）は、footerと同じ3ページ目に
            // 一緒に描く — footerだけが単独でほぼ白紙のページに孤立
            // するより、実際の記事の続きと同居させたほうが紙面として
            // 自然になる。
            $page2_stack = hatakiti_occult_pdf_stack_articles( $page2_queue, $pdf, $font_regular, $font_bold, $c['margin_l'], $page2_col_top, $full_w, $h_page2, 2 );
            $debug_log   = array_merge( $debug_log, $page2_stack['debug'] );
            $footer_y    = max( $page2_stack['bottom_y'], $page2_col_top + 30 );
        } else {
            $footer_y = max( $large_stack['bottom_y'], $left_stack['bottom_y'], $page1_col_top + 40 );
        }

        // 3ページ目：2ページ目にfooterぶんの余白を確保してもなお記事が
        // 収まらなかった場合のみ。「2ページに収めるため本文を削る」こと
        // は禁止されているため、4000字級の分量でどうしても数文字〜
        // 数十字だけ残るケースの正しい落とし所は3ページ目であり、余白や
        // フォントの過度な圧縮ではない。
        if ( ! empty( $page2_queue ) ) {
            $pdf->AddPage();
            hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c, 3 );
            $page3_col_top    = $c['margin_t'] + $c['page2_header_h'];
            $page3_col_h_full = ( $c['page_h'] - $c['margin_b'] ) - $page3_col_top;
            $reserved_h_3page = $footer_h + 1.5;
            $h_page3          = max( 40, $page3_col_h_full - $reserved_h_3page );

            $page3_stack = hatakiti_occult_pdf_stack_articles( $page2_queue, $pdf, $font_regular, $font_bold, $c['margin_l'], $page3_col_top, $full_w, $h_page3, 3 );
            $debug_log   = array_merge( $debug_log, $page3_stack['debug'] );

            if ( ! empty( $page2_queue ) ) {
                $unrecoverable = true;
            }

            $footer_y = max( $page3_stack['bottom_y'], $page3_col_top + 30 );
        }

        if ( $unrecoverable ) {
            $remaining_headlines = array();
            foreach ( $page2_queue as $r_article ) {
                $remaining_headlines[] = mb_substr( (string) ( $r_article['headline'] ?? '' ), 0, 20 ) . '（' . mb_strlen( (string) ( $r_article['body'] ?? '' ) ) . '字）';
            }
            $warnings[] = '記事量が多く、3ページ以内に紙面へ収まりませんでした。記事本文は自動で削除していません（articles_jsonは無変更）。未掲載: ' . implode( ' / ', $remaining_headlines );
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
    return md5( (string) $articles . '|' . (string) $summary );
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
