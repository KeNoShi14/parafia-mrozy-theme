<?php
/**
 * Standardy ochrony dzieci.
 *
 * - strona /standardy-ochrony-dzieci/ (szablon page-standardy-ochrony-dzieci.php),
 * - dokument PDF w pliki/standardy-ochrony-dzieci.pdf (nowa wersja = podmiana pliku),
 * - jednorazowe utworzenie strony i dodanie jej do „Menu główne” pod „Duszpasterstwo”.
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

/** ID pozycji „Duszpasterstwo” (najwyższy poziom) w danym menu albo 0. */
function parafia_standardy_rodzic_menu( $items ) {
    foreach ( (array) $items as $item ) {
        if ( 0 === (int) $item->menu_item_parent
            && 'duszpasterstwo' === mb_strtolower( trim( wp_strip_all_tags( $item->title ) ) ) ) {
            return (int) $item->ID;
        }
    }
    return 0;
}

/**
 * Jednorazowo: tworzy stronę (jeśli jej nie ma) i umieszcza ją w „Menu główne”
 * jako podstronę „Duszpasterstwo” (gdy takiej pozycji brak – na końcu menu).
 * Wersja 2 przenosi pod „Duszpasterstwo” zakładkę dodaną wcześniej na najwyższym poziomie.
 * Później pozycję można dowolnie przenieść lub usunąć w Wygląd → Menu – nie wróci.
 */
function parafia_standardy_migracja() {
    $wersja = (int) get_option( 'parafia_mrozy_standardy_wersja', 0 );
    if ( $wersja >= 2 ) {
        return;
    }

    $strona = get_page_by_path( PARAFIA_STANDARDY_SLUG );
    if ( ! $strona ) {
        if ( $wersja >= 1 ) {
            update_option( 'parafia_mrozy_standardy_wersja', 2 );
            return; // strona celowo usunięta – nie odtwarzamy
        }
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
        $items  = wp_get_nav_menu_items( $menu_id );
        $rodzic = parafia_standardy_rodzic_menu( $items );
        $nasza  = null;
        foreach ( (array) $items as $item ) {
            if ( 'post_type' === $item->type && (int) $item->object_id === (int) $id ) {
                $nasza = $item;
                break;
            }
        }

        if ( $nasza ) {
            // Przenieś pod „Duszpasterstwo” tylko zakładkę z najwyższego poziomu.
            if ( $rodzic && 0 === (int) $nasza->menu_item_parent ) {
                update_post_meta( $nasza->ID, '_menu_item_menu_item_parent', (string) $rodzic );
            }
        } elseif ( $wersja < 1 ) {
            wp_update_nav_menu_item( $menu_id, 0, [
                'menu-item-title'     => 'Standardy ochrony dzieci',
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $id,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
                'menu-item-parent-id' => $rodzic,
            ] );
        }
    }

    update_option( 'parafia_mrozy_standardy_wersja', 2 );
}
add_action( 'init', 'parafia_standardy_migracja', 30 );
