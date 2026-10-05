<?php
/**
 * HATAKITIpedia category map and validation.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the canonical HATAKITIpedia major-category map.
 *
 * Each existing fine-grained category must appear exactly once.
 */
function hakatikipedia_get_category_map() {
    return {'基本' =>array('基本','ジャンル'),'演技' =>array('集団・演技','演技・音','身体表現','音楽・演技','身体・音','演技・創作','演技技法','役柄・演技','演技・表現','演技・理論','俳優・訓練','演技・集団表現','演技・演出論','演技・人物造形','演技・脚本','演技・舞台美術','演技・想像','演技・身体','演技・空間','演技・音響','演技・関係','演技・感情','演技・人物','演技・欲求','演技・状況','人物・状況','人物・関係','人物・環境','人物・社会','人物・時間','構成・時間','構成・状況','人物・作品','構成・演技','演技・目的','演技・実践','演技・時間','役柄・演劇表現'),'演出・構成・演劇論' =>array('宗教・演劇論','演劇表現','演劇論・表現','舞台・演劇表現','演出・理論'),'演劇史' =>array('古代演劇','古代演劇・音楽・演技','古代演劇・身体表現','古代演劇・祭礼・身体表現','古代・演技','中世・演劇史','宗教・演劇史','中世・宗教文化','宗教・音楽','言語・演劇史','中世・上演空間','中世・芸能史','芸能史','日本演劇史','西洋演劇史','舞台技術・演劇史','日本演劇史・組織','演劇組織・芸能史','人物造形・演劇史','演劇史・人物類型','近代演劇史','近代演劇史・演劇表現','近代演劇','現代演劇'),'日本伝統芸能' =>array('能・舞台','能・演技','能・声楽','能・音楽','能・役柄','歌舞伎・舞台空間','日本伝統芸能','歌舞伎・観客文化','歌舞伎・演技','歌舞伎・役柄','歌舞伎・舞台技術','歌舞伎・舞台音楽','歌舞伎・舞台機構'),'劇場・空間' =>array('劇場・空間','社会・上演空間','祭礼・上演空間'),'舞台美術・舞台機構' =>array('舞台美術','舞台機構','舞台美術・衣裳','舞台美術・人物表現','舞台基礎'),'舞台技術・音響' =>array('舞台技術','舞台技術・演出','舞台音響'),'制作・運営・産業' =>array('上演・制作','演劇産業','演劇組織','俳優・職業','演劇制作','舞台制作・運営'),'観客・上演' =>array('観客・上演')};
}

/**
 * Validate the glossary category map against the categories actually used by terms.
 */
function hakatikipedia_validate_category_map( $terms ) {
    $major_map = hakatikipedia_get_category_map();
    $allowed_majors = array_keys( $major_map );
    $actual_categories = array();
    foreach ( $terms as $term ) {
        if ( ! empty( $term['category'] ) ) {
            $actual_categories[ $term['category'] ] = true;
        }
    }
    $actual_categories = array_keys( $actual_categories );

    $missing = array_values( array_diff( $actual_categories, array_merge( ...array_values( $major_map ) ) ) );
    $mapped = array();
    $duplicates = array();
    foreach ( $major_map as $major => $categories ) {
        foreach ( $categories as $category ) {
            if ( isset( $mapped[ $category ] ) ) {
                $duplicates[ $category ] = array( $mapped[ $category ], $major );
            } else {
                $mapped[ $category ] = $major;
            }
        }
    }

    $orphan = array_values( array_diff( array_keys( $mapped ), $actual_categories ) );
    $unknown_majors = array_values( array_diff( $allowed_majors, array(
        '基本・ジャンル','演技','演出・構成・演劇論','演劇史','日本伝統芸能',
        '劇場・空間','舞台美術・舞台機構','舞台技術・音響','制作・運営・産業','観客・上演'
    ) ) );

    $empty_majors = array();
    $major_counts = array();
    foreach ( $major_map as $major => $categories ) {
        $count = 0;
        foreach ( $categories as $category ) {
            if ( in_array( $category, $actual_categories, true ) ) {
                $count++;
            }
        }
        $major_counts[ $major ] = $count;
        if ( 0 === $count ) {
            $empty_majors[] = $major;
        }
    }

    $mapped_unique = array_unique( $mapped );
    $errors = ! empty( $missing ) || ! empty( $duplicates ) || ! empty( $unknown_majors ) || ! empty( $empty_majors );
    $warnings = ! empty( $orphan );

    return array(
        'ok' => ! $errors && ! $warnings,
        'category_count' => count( $actual_categories ),
        'mapped_count' => count( $mapped ),
        'major_count' => count( $major_map ),
        'missing' => $missing,
        'duplicates' => $duplicates,
        'orphan' => $orphan,
        'empty_major' => $empty_majors,
        'unknown_major' => $unknown_majors,
        'major_counts' => $major_counts,
        'errors' => $errors,
        'warnings' => $warnings,
    );
}

/**
 * Log only when the validation state changes.
 */
function hakatikipedia_log_category_validation( $validation ) {
    $state = array(
        'missing' => $validation['missing'],
        'duplicates' => $validation['duplicates'],
        'orphan' => $validation['orphan'],
        'empty_major' => $validation['empty_major'],
        'unknown_major' => $validation['unknown_major'],
    );
    $fingerprint = md5( wp_json_encode( $state ) );
    $option = 'hakatikipedia_category_validation_fingerprint';
    $previous = get_option( $option, '' );

    if ( $fingerprint === $previous ) {
        return;
    }

    update_option( $option, $fingerprint, false );

    if ( $validation['errors'] || $validation['warnings'] ) {
        error_log( '[HATAKITIpedia] Category mapping problem: ' . wp_json_encode( $state, JSON_UNESCAPED_UNICODE ) );
    } else {
        error_log( '[HATAKITIpedia] Category mapping restored. ' . wp_json_encode( $validation['major_counts'], JSON_UNESCAPED_UNICODE ) );
    }
}
