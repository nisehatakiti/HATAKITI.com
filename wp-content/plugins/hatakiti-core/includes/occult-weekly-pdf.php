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
        'margin_b'        => 9.0,
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
    $capacity  = max( 1, (int) floor( $col_h_mm / $char_h ) - 1 ); // 禁則の押し出し用に1文字ぶん余裕を残す

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

function hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c ) {
    $pdf->SetFont( $font_regular, '', 9 );
    $pdf->SetXY( $c['margin_l'], $c['margin_t'] );
    $pdf->Cell( $c['page_w'] - $c['margin_l'] - $c['margin_r'], 5, '週刊オカルト新聞　（第2面）', 0, 0, 'L' );
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
 * 1記事ぶん（見出し列＋本文列＋出典）を描画する。
 *
 * @return array array('used_width'=>mm, 'overflow_body'=>array|null, 'bottom_y'=>mm)
 */
function hatakiti_occult_pdf_draw_article( $pdf, $font_regular, $font_bold, $article, $tier, $c, $x_right, $y_top, $col_h_mm, $available_width_mm ) {
    $fonts    = hatakiti_occult_pdf_layout_constants()['tier_fonts'][ $tier ];
    $headline = (string) ( $article['headline'] ?? '' );
    $body     = (string) ( $article['body'] ?? '' );

    // 見出し列（最大2列まで）
    $headline_units = array( mb_str_split( $headline, 1, 'UTF-8' ) );
    $headline_units[0] = array_map( function ( $ch ) {
        return array( 'type' => 'char', 'ch' => $ch );
    }, $headline_units[0] );

    $head_char_h    = $fonts['headline'] * 0.3528;
    $head_col_pitch = $head_char_h * 1.08;
    $head_max_cols  = max( 1, (int) floor( $available_width_mm / $head_col_pitch ) );
    $head_max_cols  = min( 2, $head_max_cols );

    if ( $head_max_cols < 1 ) {
        return array( 'used_width' => 0, 'overflow_body' => hatakiti_occult_pdf_build_units( $body ), 'bottom_y' => $y_top );
    }

    $head_result = hatakiti_occult_pdf_layout_and_draw_columns( $pdf, $headline_units, $x_right, $y_top, $col_h_mm, $head_max_cols, $fonts['headline'], $font_bold );
    $used_width  = $head_result['columns_used'] * $head_col_pitch;

    // 見出しが収まりきらなかった分は本文の先頭に差し戻す（情報を失わない）
    $headline_leftover_text = '';
    if ( $head_result['overflow'] ) {
        foreach ( $head_result['remainder'] as $para ) {
            foreach ( $para as $u ) {
                $headline_leftover_text .= $u['ch'];
            }
        }
    }

    $gap = 1.3;
    $used_width += $gap;
    $x_after_head = $x_right - $used_width;

    $remaining_for_body = $available_width_mm - $used_width;

    $body_full_text = ( $headline_leftover_text ? '【続き】' . $headline_leftover_text . "\n\n" : '' ) . $body;
    $body_paragraphs = hatakiti_occult_pdf_build_units( $body_full_text );

    $body_char_h    = $fonts['body'] * 0.3528;
    $body_col_pitch = $body_char_h * 1.08;
    $body_capacity  = max( 1, (int) floor( $col_h_mm / $body_char_h ) - 1 );

    // 本文が必要とする列数を先に見積もる（記事量に応じて紙面を組み替える）
    $unit_count = 0;
    foreach ( $body_paragraphs as $para ) {
        $unit_count += count( $para ) + 1; // 段落区切り(字下げ)ぶんも1列消費見込みに含める
    }
    $needed_cols = max( 1, (int) ceil( $unit_count / $body_capacity ) );
    $max_cols_by_width = max( 0, (int) floor( $remaining_for_body / $body_col_pitch ) );
    $cols_to_use = min( $needed_cols, $max_cols_by_width );

    $overflow_body = null;
    if ( $cols_to_use < 1 ) {
        $overflow_body = $body_paragraphs;
        $body_used_width = 0;
    } else {
        $body_result = hatakiti_occult_pdf_layout_and_draw_columns( $pdf, $body_paragraphs, $x_after_head, $y_top, $col_h_mm, $cols_to_use, $fonts['body'], $font_regular );
        $body_used_width = $body_result['columns_used'] * $body_col_pitch;
        if ( $body_result['overflow'] ) {
            $overflow_body = $body_result['remainder'];
        }
    }

    $total_used_width = $used_width + $body_used_width;

    // 出典（横書き、記事ブロック下端の専用ストリップ内に1行だけ）。
    // 狭い記事幅でも隣の記事とぶつからないよう、割当幅に収まる文字数まで
    // 短縮する（詳細な元記事タイトル・URLは紙面末尾の出典一覧に必ず載る
    // ので、ここで削っても情報は失われない）。
    $source_lines = hatakiti_occult_pdf_source_lines( $article['news_item_ids'] ?? array() );
    if ( $source_lines && $total_used_width > 6 ) {
        $src_font   = 6.0;
        $block_left = $x_right - $total_used_width;
        $src_y      = $y_top + $col_h_mm + 0.8;
        $max_chars  = max( 2, (int) floor( $total_used_width / ( $src_font * 0.62 ) ) );
        $sl         = $source_lines[0];
        $text       = mb_strlen( $sl['text'] ) > $max_chars ? mb_substr( $sl['text'], 0, $max_chars - 1 ) . '…' : $sl['text'];

        $pdf->SetFont( $font_regular, '', $src_font );
        $pdf->SetXY( $block_left, $src_y );
        $pdf->Cell( $total_used_width, 3.4, $text, 0, 0, 'C' );
        if ( $sl['url'] ) {
            $pdf->Link( $block_left, $src_y, $total_used_width, 3.4, $sl['url'] );
        }
    }

    // 記事間の縦罫線
    $rule_x = $x_right - $total_used_width - 0.9;
    $pdf->SetLineWidth( 'large' === $tier ? 0.5 : 0.2 );
    $pdf->Line( $rule_x, $y_top, $rule_x, $y_top + $col_h_mm + 4.2 );

    return array(
        'used_width'    => $total_used_width + 1.7, // 罫線ぶんの余白込み
        'overflow_body' => $overflow_body,
        'headline_overflowed' => (bool) $headline_leftover_text,
    );
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
        $tiers[ $t ][] = $a;
    }

    $ordered_articles = array_merge( $tiers['large'], $tiers['medium'], $tiers['small'] );

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

    $warnings = array();

    // フッター（出典＋編集後記）に必要な高さを先に見積もる
    $pdf->AddPage();
    $footer_h = hatakiti_occult_pdf_footer_height( $pdf, $font_regular, $all_sources, $editorial_summary, $full_w );

    // --- 試み1: 1ページに収まるか ---
    $source_strip_h = 4.2; // 出典1行ぶんの専用スペース（記事ごとの列の下）

    $page1_col_top    = $c['margin_t'] + $c['masthead_h'];
    $page1_col_bottom = $c['page_h'] - $c['margin_b'] - $footer_h - $source_strip_h;
    $page1_col_h      = $page1_col_bottom - $page1_col_top;

    $attempt_single_page = ( $page1_col_h > 60 ); // 最低限の高さがなければ最初から2ページ想定

    $fits_one_page = false;
    if ( $attempt_single_page ) {
        // ドライラン用に別インスタンスで試し、実際の描画はしない…と
        // したいところだが、TCPDFは描画とレイアウト計算が一体のため、
        // 実際に描画しながら overflow を判定し、だめなら作り直す。
        list( $pdf, $font_regular, $font_bold ) = hatakiti_occult_pdf_new_tcpdf();
        $pdf->AddPage();
        hatakiti_occult_pdf_draw_masthead( $pdf, $font_regular, $font_bold, $c, $issue_subtitle, $issue_id, $issue_date );

        $x_cursor   = $c['page_w'] - $c['margin_r'];
        $left_limit = $c['margin_l'];
        $any_overflow = false;
        $prev_tier = null;

        foreach ( $ordered_articles as $article ) {
            $tier = isset( $article['tier'] ) && isset( $tiers[ $article['tier'] ] ) ? $article['tier'] : 'small';
            $available = $x_cursor - $left_limit;
            if ( $available < 15 ) {
                $any_overflow = true;
                break;
            }
            $result = hatakiti_occult_pdf_draw_article( $pdf, $font_regular, $font_bold, $article, $tier, $c, $x_cursor, $page1_col_top, $page1_col_h, $available );
            $x_cursor -= $result['used_width'];
            if ( $result['overflow_body'] || $result['headline_overflowed'] ) {
                $any_overflow = true;
                break;
            }
            $prev_tier = $tier;
        }

        if ( ! $any_overflow ) {
            $fits_one_page = true;
            hatakiti_occult_pdf_draw_footer( $pdf, $font_regular, $font_bold, $c, $all_sources, $editorial_summary, $page1_col_bottom + 2 );
        }
    }

    // --- 試み2: 2ページ構成 ---
    if ( ! $fits_one_page ) {
        list( $pdf, $font_regular, $font_bold ) = hatakiti_occult_pdf_new_tcpdf();
        $pdf->AddPage();
        hatakiti_occult_pdf_draw_masthead( $pdf, $font_regular, $font_bold, $c, $issue_subtitle, $issue_id, $issue_date );

        $page1_col_top_full    = $c['margin_t'] + $c['masthead_h'];
        $page1_col_bottom_full = $c['page_h'] - $c['margin_b'] - $source_strip_h;
        $page1_col_h_full      = $page1_col_bottom_full - $page1_col_top_full;

        $x_cursor    = $c['page_w'] - $c['margin_r'];
        $left_limit  = $c['margin_l'];
        $page        = 1;
        $page2_started = false;
        $page2_col_top = 0;
        $page2_col_h   = 0;
        $unrecoverable = false;

        $queue = $ordered_articles;
        $i     = 0;
        while ( $i < count( $queue ) ) {
            $article = $queue[ $i ];
            $tier    = isset( $article['tier'] ) && isset( $tiers[ $article['tier'] ] ) ? $article['tier'] : 'small';

            if ( 1 === $page ) {
                $col_top = $page1_col_top_full;
                $col_h   = $page1_col_h_full;
            } else {
                $col_top = $page2_col_top;
                $col_h   = $page2_col_h;
            }

            $available = $x_cursor - $left_limit;
            if ( $available < 15 ) {
                if ( 1 === $page ) {
                    $pdf->AddPage();
                    hatakiti_occult_pdf_draw_page2_header( $pdf, $font_regular, $c );
                    $page2_col_top = $c['margin_t'] + $c['page2_header_h'];
                    $page2_col_bottom = $c['page_h'] - $c['margin_b'] - $footer_h - $source_strip_h;
                    $page2_col_h   = $page2_col_bottom - $page2_col_top;
                    $x_cursor      = $c['page_w'] - $c['margin_r'];
                    $page          = 2;
                    continue;
                } else {
                    $unrecoverable = true;
                    break;
                }
            }

            $result = hatakiti_occult_pdf_draw_article( $pdf, $font_regular, $font_bold, $article, $tier, $c, $x_cursor, $col_top, $col_h, $available );
            $x_cursor -= $result['used_width'];

            if ( $result['overflow_body'] ) {
                // 残りを次の記事として再キューに積む（本文を勝手に切り捨てない）
                $rebuilt_text = '';
                foreach ( $result['overflow_body'] as $para ) {
                    $ptext = '';
                    foreach ( $para as $u ) {
                        $ptext .= $u['ch'];
                    }
                    $rebuilt_text .= ( $rebuilt_text ? "\n\n" : '' ) . $ptext;
                }
                $continuation = $article;
                $continuation['headline'] = '（続き）';
                $continuation['body']     = $rebuilt_text;
                array_splice( $queue, $i + 1, 0, array( $continuation ) );
            }

            $i++;
        }

        if ( $unrecoverable ) {
            $remaining_headlines = array();
            for ( $r = $i; $r < count( $queue ); $r++ ) {
                $remaining_headlines[] = ( isset( $queue[ $r ]['headline'] ) ? mb_substr( $queue[ $r ]['headline'], 0, 20 ) : '' ) . '（' . mb_strlen( $queue[ $r ]['body'] ?? '' ) . '字）';
            }
            $warnings[] = '記事量が多く、2ページ以内に紙面へ収まりませんでした。記事本文は自動で削除していません（articles_jsonは無変更）。未掲載: ' . implode( ' / ', $remaining_headlines );
        }

        hatakiti_occult_pdf_draw_footer( $pdf, $font_regular, $font_bold, $c, $all_sources, $editorial_summary, ( 2 === $page ? $page2_col_top + $page2_col_h : $page1_col_bottom_full ) + 2 );
    }

    $pages = $pdf->getNumPages();

    $tmp_path = trailingslashit( sys_get_temp_dir() ) . 'hatakiti-occult-pdf-' . $post_id . '-' . wp_generate_password( 8, false ) . '.pdf';
    $pdf->Output( $tmp_path, 'F' );

    return array(
        'path'     => $tmp_path,
        'pages'    => $pages,
        'warnings' => $warnings,
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
