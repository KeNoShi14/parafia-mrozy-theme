<?php
/**
 * Konwersja zdjęć do WebP od razu przy wgrywaniu (jak w motywie Parafia Jedlanka).
 *
 * Oryginał JPG/PNG/HEIC jest zamieniany na WebP jeszcze zanim WordPress utworzy
 * załącznik — w bibliotece mediów ląduje wyłącznie plik .webp, a wszystkie
 * miniatury również powstają jako WebP.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'PARAFIA_IMG_QUALITY' ) ) {
    define( 'PARAFIA_IMG_QUALITY', 82 );
}
if ( ! defined( 'PARAFIA_IMG_MAX_WIDTH' ) ) {
    // Najdłuższy bok zdjęcia po wgraniu (px).
    define( 'PARAFIA_IMG_MAX_WIDTH', 1920 );
}

function parafia_mrozy_webp_wlaczone() {
    return (bool) apply_filters( 'parafia_mrozy_webp_wlaczone', true )
        && wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] );
}

function parafia_mrozy_webp_typy() {
    return [ 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/heic', 'image/heif', 'image/bmp', 'image/tiff' ];
}

function parafia_mrozy_webp_przy_wgraniu( $plik ) {
    if ( ! empty( $plik['error'] ) || empty( $plik['file'] ) || empty( $plik['type'] ) ) {
        return $plik;
    }
    if ( ! in_array( $plik['type'], parafia_mrozy_webp_typy(), true ) || ! parafia_mrozy_webp_wlaczone() ) {
        return $plik;
    }

    $edytor = wp_get_image_editor( $plik['file'] );
    if ( is_wp_error( $edytor ) ) {
        return $plik;
    }

    // Zdjęcia z telefonu: obrót zgodnie z EXIF, bo WebP tej informacji nie zachowa.
    if ( method_exists( $edytor, 'maybe_exif_rotate' ) && in_array( $plik['type'], [ 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/heic', 'image/heif' ], true ) ) {
        $edytor->maybe_exif_rotate();
    }

    $rozmiar = $edytor->get_size();
    if ( ! empty( $rozmiar['width'] ) && ( $rozmiar['width'] > PARAFIA_IMG_MAX_WIDTH || $rozmiar['height'] > PARAFIA_IMG_MAX_WIDTH ) ) {
        $edytor->resize( PARAFIA_IMG_MAX_WIDTH, PARAFIA_IMG_MAX_WIDTH, false );
    }
    $edytor->set_quality( PARAFIA_IMG_QUALITY );

    $katalog = dirname( $plik['file'] );
    $nazwa   = pathinfo( $plik['file'], PATHINFO_FILENAME ) . '.webp';
    if ( file_exists( trailingslashit( $katalog ) . $nazwa ) ) {
        $nazwa = wp_unique_filename( $katalog, $nazwa );
    }

    $zapis = $edytor->save( trailingslashit( $katalog ) . $nazwa, 'image/webp' );
    if ( is_wp_error( $zapis ) || empty( $zapis['path'] ) || ! file_exists( $zapis['path'] ) ) {
        return $plik;
    }

    wp_delete_file( $plik['file'] );
    $plik['file'] = $zapis['path'];
    $plik['url']  = trailingslashit( dirname( $plik['url'] ) ) . wp_basename( $zapis['path'] );
    $plik['type'] = 'image/webp';
    return $plik;
}
add_filter( 'wp_handle_upload', 'parafia_mrozy_webp_przy_wgraniu', 5 );
add_filter( 'wp_handle_sideload', 'parafia_mrozy_webp_przy_wgraniu', 5 );

// Miniatury starszych zdjęć JPG/PNG (np. przy regeneracji) też jako WebP.
function parafia_mrozy_webp_miniatury( $formaty ) {
    if ( parafia_mrozy_webp_wlaczone() ) {
        $formaty['image/jpeg'] = 'image/webp';
        $formaty['image/png']  = 'image/webp';
    }
    return $formaty;
}
add_filter( 'image_editor_output_format', 'parafia_mrozy_webp_miniatury' );

function parafia_mrozy_webp_jakosc_miniatur( $jakosc, $mime ) {
    return 'image/webp' === $mime && parafia_mrozy_webp_wlaczone() ? PARAFIA_IMG_QUALITY : $jakosc;
}
add_filter( 'wp_editor_set_quality', 'parafia_mrozy_webp_jakosc_miniatur', 10, 2 );

/**
 * Zgodność wstecz: starsze zdjęcia wgrane jako JPG/PNG mają obok kopię .webp
 * (meta _webp_url). Dla przeglądarek obsługujących WebP podmieniamy srcset.
 */
function parafia_mrozy_serve_webp( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
    if ( empty( $_SERVER['HTTP_ACCEPT'] ) || false === strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) ) {
        return $sources;
    }
    if ( ! get_post_meta( $attachment_id, '_webp_url', true ) ) {
        return $sources;
    }

    $uploads = wp_upload_dir();
    foreach ( $sources as $width => $source ) {
        $webp_variant = preg_replace( '/\.(jpe?g|png)$/i', '.webp',
            preg_replace( '/-\d+x\d+(\.(jpe?g|png))$/i', '$1', $source['url'] )
        );
        $webp_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $webp_variant );
        if ( file_exists( $webp_path ) ) {
            $sources[ $width ]['url'] = $webp_variant;
        }
    }
    return $sources;
}
add_filter( 'wp_calculate_image_srcset', 'parafia_mrozy_serve_webp', 10, 5 );
