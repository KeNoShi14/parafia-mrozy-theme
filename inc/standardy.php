<?php
/**
 * Standardy ochrony dzieci.
 *
 * - strona /standardy-ochrony-dzieci/ (szablon page-standardy-ochrony-dzieci.php),
 * - dokument PDF w pliki/standardy-ochrony-dzieci.pdf (nowa wersja = podmiana pliku),
 * - jednorazowe utworzenie strony i dodanie zakładki do „Menu główne”.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PARAFIA_STANDARDY_SLUG', 'standardy-ochrony-dzieci' );
define( 'PARAFIA_STANDARDY_PLIK', 'pliki/standardy-ochrony-dzieci.pdf' );

/** Adres PDF z wersją zależną od daty pliku (po podmianie przeglądarki nie pokażą starej wersji). */
function parafia_standardy_pdf_url() {
    $sciezka = get_theme_file_path( PARAFIA_STANDARDY_PLIK );
    $wersja  = file_exists( $sciezka ) ? filemtime( $sciezka ) : PARAFIA_MROZY_VERSION;
    return add_query_arg( 'v', $wersja, get_theme_file_uri( PARAFIA_STANDARDY_PLIK ) );
}

function parafia_standardy_to_ta_strona() {
    if ( ! is_page() ) return false;
    return parafia_mrozy_strona_ma_szablon( get_queried_object(), 'page-standardy-ochrony-dzieci.php' );
}

function parafia_standardy_zasoby() {
    if ( ! parafia_standardy_to_ta_strona() ) return;

    wp_enqueue_style(
        'parafia-standardy',
        get_template_directory_uri() . '/css/standardy.css',
        [ 'parafia-mrozy-style' ],
        PARAFIA_MROZY_VERSION
    );
    wp_enqueue_script(
        'pdfjs-lib',
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js',
        [],
        '3.4.120',
        true
    );
    wp_enqueue_script(
        'parafia-standardy',
        get_template_directory_uri() . '/js/standardy.js',
        [ 'pdfjs-lib' ],
        PARAFIA_MROZY_VERSION,
        true
    );
    wp_localize_script( 'parafia-standardy', 'pmStandardy', [
        'workerSrc' => 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js',
    ] );
}
add_action( 'wp_enqueue_scripts', 'parafia_standardy_zasoby', 20 );

/**
 * Jednorazowo: tworzy stronę (jeśli jej nie ma) i dodaje ją jako zakładkę
 * na końcu menu przypisanego do lokalizacji „Menu główne”.
 * Później zakładkę można dowolnie przenieść lub usunąć w Wygląd → Menu – nie wróci.
 */
function parafia_standardy_migracja() {
    if ( (int) get_option( 'parafia_mrozy_standardy_wersja', 0 ) >= 1 ) {
        return;
    }

    $strona = get_page_by_path( PARAFIA_STANDARDY_SLUG );
    if ( ! $strona ) {
        $id = wp_insert_post( [
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => 'Standardy ochrony dzieci',
            'post_name'    => PARAFIA_STANDARDY_SLUG,
            'post_content' => '',
        ], true );
        if ( is_wp_error( $id ) || ! $id ) {
            return; // spróbujemy przy następnym żądaniu
        }
        update_post_meta( $id, '_wp_page_template', 'page-standardy-ochrony-dzieci.php' );
    } else {
        $id = $strona->ID;
    }

    $lokalizacje = get_nav_menu_locations();
    $menu_id     = isset( $lokalizacje['primary'] ) ? (int) $lokalizacje['primary'] : 0;

    if ( $menu_id && wp_get_nav_menu_object( $menu_id ) ) {
        $jest = false;
        foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
            if ( 'post_type' === $item->type && (int) $item->object_id === (int) $id ) {
                $jest = true;
                break;
            }
        }
        if ( ! $jest ) {
            wp_update_nav_menu_item( $menu_id, 0, [
                'menu-item-title'     => 'Standardy ochrony dzieci',
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $id,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ] );
        }
    }

    update_option( 'parafia_mrozy_standardy_wersja', 1 );
}
add_action( 'init', 'parafia_standardy_migracja', 30 );
