<?php
/**
 * Galerie zdjęć — wygodny panel w kokpicie (wzorowany na motywie Parafia Jedlanka).
 *
 * Galerie pozostają zwykłymi wpisami w kategorii „galeria” (adresy i strona główna
 * działają jak dotąd), ale zamiast edytora bloków mają własny panel:
 * przeciągnij-i-upuść zdjęć z komputera, wybór z biblioteki, zmiana kolejności,
 * wybór okładki, data wydarzenia i krótki opis.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PARAFIA_MROZY_GALERIA_KATEGORIA', 'galeria' );
define( 'PARAFIA_MROZY_GALERIA_ZDJECIA', '_parafia_galeria_zdjecia' );
define( 'PARAFIA_MROZY_GALERIA_DATA', '_parafia_galeria_data' );
define( 'PARAFIA_MROZY_GALERIA_MENU', 'edit.php?category_name=galeria' );
define( 'PARAFIA_MROZY_GALERIA_NOWA', 'post-new.php?galeria=1' );

/* -------------------------------------------------------------------------
 * Dane galerii
 * ---------------------------------------------------------------------- */

function parafia_mrozy_galeria_kategoria_id( $utworz = false ) {
    $kat = get_category_by_slug( PARAFIA_MROZY_GALERIA_KATEGORIA );
    if ( $kat ) {
        return (int) $kat->term_id;
    }
    if ( ! $utworz ) {
        return 0;
    }
    $nowa = wp_insert_term( 'Galeria', 'category', [ 'slug' => PARAFIA_MROZY_GALERIA_KATEGORIA ] );
    return is_wp_error( $nowa ) ? 0 : (int) $nowa['term_id'];
}

function parafia_mrozy_jest_galeria( $post ) {
    $post = get_post( $post );
    return $post && 'post' === $post->post_type && has_category( PARAFIA_MROZY_GALERIA_KATEGORIA, $post );
}

function parafia_mrozy_galeria_ze_starej_tresci( $post ) {
    $tresc = (string) $post->post_content;
    $ids   = [];

    if ( preg_match_all( '/wp-image-(\d+)|"id":(\d+)|data-id="(\d+)"/', $tresc, $m, PREG_SET_ORDER ) ) {
        foreach ( $m as $dopasowanie ) {
            $id = (int) ( ! empty( $dopasowanie[1] ) ? $dopasowanie[1] : ( ! empty( $dopasowanie[2] ) ? $dopasowanie[2] : ( $dopasowanie[3] ?? 0 ) ) );
            if ( $id && ! in_array( $id, $ids, true ) && wp_attachment_is_image( $id ) ) {
                $ids[] = $id;
            }
        }
    }
    if ( preg_match_all( '/\[gallery[^\]]*\bids="([\d,\s]+)"/', $tresc, $m ) ) {
        foreach ( $m[1] as $lista ) {
            foreach ( array_map( 'absint', explode( ',', $lista ) ) as $id ) {
                if ( $id && ! in_array( $id, $ids, true ) && wp_attachment_is_image( $id ) ) {
                    $ids[] = $id;
                }
            }
        }
    }
    if ( ! $ids ) {
        // Ostatnia deska ratunku: zdjęcia wgrane do tego wpisu.
        $ids = array_map( 'intval', get_children( [
            'post_parent'    => $post->ID,
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'numberposts'    => -1,
            'orderby'        => 'menu_order ID',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ] ) );
    }
    return array_values( $ids );
}

function parafia_mrozy_galeria_opis_ze_starej_tresci( $tresc ) {
    $bez_zdjec = preg_replace( '#<!-- wp:(gallery|image|media-text)\b.*?<!-- /wp:\1 -->#s', '', (string) $tresc );
    $bez_zdjec = strip_shortcodes( $bez_zdjec );
    $tekst     = trim( wp_strip_all_tags( preg_replace( '#</p>#', "</p>\n\n", $bez_zdjec ) ) );
    return preg_replace( "/\n{3,}/", "\n\n", $tekst );
}

function parafia_mrozy_galeria_zdjecia( $post ) {
    $post = get_post( $post );
    if ( ! $post ) {
        return [];
    }
    $zapisane = get_post_meta( $post->ID, PARAFIA_MROZY_GALERIA_ZDJECIA, true );
    if ( is_array( $zapisane ) && $zapisane ) {
        return array_values( array_filter( array_map( 'absint', $zapisane ), 'wp_attachment_is_image' ) );
    }
    return parafia_mrozy_galeria_ze_starej_tresci( $post );
}

/** Czy galeria ma już zdjęcia zapisane nowym panelem (a nie tylko w treści wpisu). */
function parafia_mrozy_galeria_nowy_format( $post ) {
    $post = get_post( $post );
    return $post && metadata_exists( 'post', $post->ID, PARAFIA_MROZY_GALERIA_ZDJECIA );
}

function parafia_mrozy_galeria_opis( $post ) {
    $post = get_post( $post );
    if ( ! $post ) {
        return '';
    }
    if ( '' !== trim( $post->post_excerpt ) ) {
        return $post->post_excerpt;
    }
    return parafia_mrozy_galeria_opis_ze_starej_tresci( $post->post_content );
}

function parafia_mrozy_galeria_data( $post ) {
    $post = get_post( $post );
    $data = (string) get_post_meta( $post->ID, PARAFIA_MROZY_GALERIA_DATA, true );
    return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data ) ? $data : get_the_date( 'Y-m-d', $post );
}

function parafia_mrozy_galeria_data_tekst( $data ) {
    $miesiace = [ 1 => 'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia' ];
    $nr       = (int) substr( $data, 5, 2 );
    return (int) substr( $data, 8, 2 ) . ' ' . ( $miesiace[ $nr ] ?? '' ) . ' ' . substr( $data, 0, 4 );
}

function parafia_mrozy_galeria_liczba( $n ) {
    $n = (int) $n;
    if ( 1 === $n ) {
        return '1 zdjęcie';
    }
    $reszta10  = $n % 10;
    $reszta100 = $n % 100;
    return $n . ( $reszta10 >= 2 && $reszta10 <= 4 && ( $reszta100 < 12 || $reszta100 > 14 ) ? ' zdjęcia' : ' zdjęć' );
}

function parafia_mrozy_galeria_okladka( $post ) {
    $post = get_post( $post );
    $id   = (int) get_post_thumbnail_id( $post );
    if ( $id && wp_attachment_is_image( $id ) ) {
        return $id;
    }
    $zdjecia = parafia_mrozy_galeria_zdjecia( $post );
    return $zdjecia ? $zdjecia[0] : 0;
}

/**
 * Argumenty WP_Query dla listy galerii — od najnowszego wydarzenia.
 * $rok (opcjonalnie) zawęża listę do jednego roku.
 */
function parafia_mrozy_galeria_args( $args = [], $rok = 0 ) {
    $args = array_merge( [
        'post_type'     => 'post',
        'post_status'   => 'publish',
        'category_name' => PARAFIA_MROZY_GALERIA_KATEGORIA,
    ], $args );

    if ( $rok >= 1900 && $rok <= 2200 ) {
        $args['meta_query'] = [
            'parafia_data_galerii' => [
                'key'     => PARAFIA_MROZY_GALERIA_DATA,
                'value'   => [ $rok . '-01-01', $rok . '-12-31' ],
                'compare' => 'BETWEEN',
                'type'    => 'DATE',
            ],
        ];
    } else {
        $args['meta_query'] = [
            'relation'             => 'OR',
            'parafia_data_galerii' => [
                'key'     => PARAFIA_MROZY_GALERIA_DATA,
                'compare' => 'EXISTS',
            ],
            [
                'key'     => PARAFIA_MROZY_GALERIA_DATA,
                'compare' => 'NOT EXISTS',
            ],
        ];
    }
    $args['orderby'] = [
        'parafia_data_galerii' => 'DESC',
        'date'                 => 'DESC',
    ];
    return $args;
}

function parafia_mrozy_galeria_lata() {
    global $wpdb;
    $kat = parafia_mrozy_galeria_kategoria_id();
    if ( ! $kat ) {
        return [];
    }
    $lata = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT LEFT(pm.meta_value, 4)
               FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
         INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
              WHERE pm.meta_key = %s
                AND p.post_type = 'post' AND p.post_status = 'publish'
                AND tt.taxonomy = 'category' AND tt.term_id = %d
           ORDER BY 1 DESC",
            PARAFIA_MROZY_GALERIA_DATA,
            $kat
        )
    );
    return array_values( array_filter( array_map( 'absint', $lata ) ) );
}

/** Sąsiednia galeria: 'nowsze' albo 'starsze' (wg daty wydarzenia). */
function parafia_mrozy_galeria_sasiad( $post, $kierunek ) {
    static $kolejnosc = null;
    $post = get_post( $post );
    if ( null === $kolejnosc ) {
        $kolejnosc = get_posts( parafia_mrozy_galeria_args( [
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ] ) );
    }
    $poz = array_search( $post->ID, $kolejnosc, true );
    if ( false === $poz ) {
        return null;
    }
    $poz += 'starsze' === $kierunek ? 1 : -1;
    return isset( $kolejnosc[ $poz ] ) ? get_post( $kolejnosc[ $poz ] ) : null;
}

/* -------------------------------------------------------------------------
 * Uzupełnienie daty wydarzenia dla istniejących galerii
 * ---------------------------------------------------------------------- */

function parafia_mrozy_galeria_migracja() {
    if ( (int) get_option( 'parafia_mrozy_galeria_wersja', 0 ) >= 1 ) {
        return;
    }
    $kat = parafia_mrozy_galeria_kategoria_id();
    if ( $kat ) {
        $ids = get_posts( [
            'post_type'      => 'post',
            'post_status'    => 'any',
            'cat'            => $kat,
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ] );
        foreach ( $ids as $id ) {
            if ( '' === (string) get_post_meta( $id, PARAFIA_MROZY_GALERIA_DATA, true ) ) {
                update_post_meta( $id, PARAFIA_MROZY_GALERIA_DATA, get_the_date( 'Y-m-d', $id ) );
            }
        }
    }
    update_option( 'parafia_mrozy_galeria_wersja', 1 );
}
add_action( 'init', 'parafia_mrozy_galeria_migracja', 20 );

/* -------------------------------------------------------------------------
 * Kokpit: menu „Galerie”
 * ---------------------------------------------------------------------- */

function parafia_mrozy_galeria_menu() {
    add_menu_page( 'Galerie', 'Galerie', 'edit_posts', PARAFIA_MROZY_GALERIA_MENU, '', 'dashicons-format-gallery', 6 );
    add_submenu_page( PARAFIA_MROZY_GALERIA_MENU, 'Wszystkie galerie', 'Wszystkie galerie', 'edit_posts', PARAFIA_MROZY_GALERIA_MENU );
    add_submenu_page( PARAFIA_MROZY_GALERIA_MENU, 'Dodaj galerię', 'Dodaj galerię', 'edit_posts', PARAFIA_MROZY_GALERIA_NOWA );
}
add_action( 'admin_menu', 'parafia_mrozy_galeria_menu' );

function parafia_mrozy_galeria_lista_galerii() {
    global $pagenow;
    return 'edit.php' === $pagenow && isset( $_GET['category_name'] ) && PARAFIA_MROZY_GALERIA_KATEGORIA === sanitize_key( wp_unslash( $_GET['category_name'] ) );
}

/** Czy bieżący ekran kokpitu to edycja / dodawanie galerii. */
function parafia_mrozy_galeria_tryb_edycji() {
    global $pagenow;
    if ( 'post-new.php' === $pagenow ) {
        $typ = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post';
        return 'post' === $typ && ! empty( $_GET['galeria'] );
    }
    if ( 'post.php' === $pagenow && isset( $_GET['post'] ) ) {
        return parafia_mrozy_jest_galeria( absint( $_GET['post'] ) );
    }
    return false;
}

function parafia_mrozy_galeria_podswietl_menu( $parent_file ) {
    if ( parafia_mrozy_galeria_tryb_edycji() || parafia_mrozy_galeria_lista_galerii() ) {
        return PARAFIA_MROZY_GALERIA_MENU;
    }
    return $parent_file;
}
add_filter( 'parent_file', 'parafia_mrozy_galeria_podswietl_menu' );

function parafia_mrozy_galeria_podswietl_podmenu( $submenu_file ) {
    global $pagenow;
    if ( 'post-new.php' === $pagenow && parafia_mrozy_galeria_tryb_edycji() ) {
        return PARAFIA_MROZY_GALERIA_NOWA;
    }
    if ( parafia_mrozy_galeria_tryb_edycji() || parafia_mrozy_galeria_lista_galerii() ) {
        return PARAFIA_MROZY_GALERIA_MENU;
    }
    return $submenu_file;
}
add_filter( 'submenu_file', 'parafia_mrozy_galeria_podswietl_podmenu' );

// Na liście galerii przycisk „Dodaj nową” prowadzi do nowej galerii.
function parafia_mrozy_galeria_przycisk_dodaj() {
    if ( ! parafia_mrozy_galeria_lista_galerii() ) {
        return;
    }
    ?>
    <script>
    document.addEventListener( 'DOMContentLoaded', function () {
        document.querySelectorAll( '.wrap .page-title-action' ).forEach( function ( a ) {
            a.href = <?php echo wp_json_encode( admin_url( PARAFIA_MROZY_GALERIA_NOWA ) ); ?>;
            a.textContent = 'Dodaj galerię';
        } );
        var h = document.querySelector( '.wrap h1.wp-heading-inline' );
        if ( h ) { h.textContent = 'Galerie'; }
    } );
    </script>
    <?php
}
add_action( 'admin_head-edit.php', 'parafia_mrozy_galeria_przycisk_dodaj' );

/* -------------------------------------------------------------------------
 * Kokpit: ekran edycji galerii
 * ---------------------------------------------------------------------- */

function parafia_mrozy_galeria_bez_edytora_blokow( $uzyj, $post ) {
    if ( parafia_mrozy_jest_galeria( $post ) || parafia_mrozy_galeria_tryb_edycji() ) {
        return false;
    }
    return $uzyj;
}
add_filter( 'use_block_editor_for_post', 'parafia_mrozy_galeria_bez_edytora_blokow', 20, 2 );

function parafia_mrozy_galeria_przygotuj_ekran() {
    if ( ! parafia_mrozy_galeria_tryb_edycji() ) {
        return;
    }
    remove_post_type_support( 'post', 'editor' );
    remove_post_type_support( 'post', 'excerpt' );
}
add_action( 'load-post.php', 'parafia_mrozy_galeria_przygotuj_ekran' );
add_action( 'load-post-new.php', 'parafia_mrozy_galeria_przygotuj_ekran' );

function parafia_mrozy_galeria_tytul_edycji( $tekst, $post ) {
    return parafia_mrozy_galeria_tryb_edycji() ? 'Nazwa wydarzenia, np. Odpust parafialny 2026' : $tekst;
}
add_filter( 'enter_title_here', 'parafia_mrozy_galeria_tytul_edycji', 10, 2 );

function parafia_mrozy_galeria_ramki( $post ) {
    if ( ! parafia_mrozy_galeria_tryb_edycji() ) {
        return;
    }
    // Nowa galeria od razu trafia do kategorii „galeria”.
    if ( 'auto-draft' === $post->post_status ) {
        $kat = parafia_mrozy_galeria_kategoria_id( true );
        if ( $kat ) {
            wp_set_post_categories( $post->ID, [ $kat ] );
        }
    }
    remove_meta_box( 'postimagediv', 'post', 'side' );
    remove_meta_box( 'postexcerpt', 'post', 'normal' );
    add_meta_box( 'parafia-galeria-zdjecia', 'Zdjęcia', 'parafia_mrozy_galeria_ramka_zdjec', 'post', 'normal', 'high' );
    add_meta_box( 'parafia-galeria-szczegoly', 'Data i opis', 'parafia_mrozy_galeria_ramka_szczegolow', 'post', 'normal', 'high' );
}
add_action( 'add_meta_boxes_post', 'parafia_mrozy_galeria_ramki' );

function parafia_mrozy_galeria_zasoby_kokpitu( $strona ) {
    if ( ! in_array( $strona, [ 'post.php', 'post-new.php' ], true ) || ! parafia_mrozy_galeria_tryb_edycji() ) {
        return;
    }
    $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : get_the_ID();
    wp_enqueue_media( [ 'post' => $post_id ] );
    wp_enqueue_script(
        'parafia-mrozy-galeria-kokpit',
        get_template_directory_uri() . '/js/galeria-kokpit.js',
        [ 'jquery', 'jquery-ui-sortable', 'wp-plupload', 'media-views' ],
        PARAFIA_MROZY_VERSION,
        true
    );
    wp_enqueue_style(
        'parafia-mrozy-galeria-kokpit',
        get_template_directory_uri() . '/css/galeria-kokpit.css',
        [],
        PARAFIA_MROZY_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'parafia_mrozy_galeria_zasoby_kokpitu' );

function parafia_mrozy_galeria_ramka_zdjec( $post ) {
    wp_nonce_field( 'parafia_mrozy_galeria_zapis', 'parafia_mrozy_galeria_nonce' );
    $zdjecia = parafia_mrozy_galeria_zdjecia( $post );
    $okladka = (int) get_post_thumbnail_id( $post );
    if ( ! in_array( $okladka, $zdjecia, true ) ) {
        $okladka = $zdjecia ? $zdjecia[0] : 0;
    }
    ?>
    <input type="hidden" name="parafia_galeria_zdjecia" id="parafia-galeria-pole" value="<?php echo esc_attr( implode( ',', $zdjecia ) ); ?>">
    <input type="hidden" name="parafia_galeria_okladka" id="parafia-galeria-okladka" value="<?php echo esc_attr( $okladka ); ?>">
    <div class="parafia-gk" data-post="<?php echo esc_attr( $post->ID ); ?>">
        <div class="parafia-gk__strefa" id="parafia-gk-strefa">
            <p class="parafia-gk__strefa-tekst"><strong>Przeciągnij tutaj zdjęcia z komputera</strong><br>albo</p>
            <p class="parafia-gk__przyciski">
                <button type="button" class="button button-primary button-hero" id="parafia-gk-wybierz">Wybierz zdjęcia z komputera</button>
                <button type="button" class="button button-hero" id="parafia-gk-biblioteka">Dodaj z biblioteki mediów</button>
            </p>
            <p class="description">Można zaznaczyć wiele zdjęć naraz. Zdjęcia zapisują się jako WebP i są zmniejszane automatycznie.</p>
            <div class="parafia-gk__postep" id="parafia-gk-postep" hidden></div>
        </div>
        <div class="parafia-gk__pasek">
            <span class="parafia-gk__licznik" id="parafia-gk-licznik"></span>
            <span class="description">Kolejność zmienisz, przeciągając miniatury. Gwiazdka wybiera zdjęcie na okładkę.</span>
        </div>
        <ul class="parafia-gk__lista" id="parafia-gk-lista">
            <?php foreach ( $zdjecia as $id ) : ?>
                <?php echo parafia_mrozy_galeria_miniatura_kokpitu( $id, $id === $okladka ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php endforeach; ?>
        </ul>
        <template id="parafia-gk-wzor"><?php echo parafia_mrozy_galeria_miniatura_kokpitu( 0, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?></template>
    </div>
    <?php
}

function parafia_mrozy_galeria_miniatura_kokpitu( $id, $okladka ) {
    $url = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
    return '<li class="parafia-gk__zdjecie' . ( $okladka ? ' is-okladka' : '' ) . '" data-id="' . esc_attr( $id ) . '">'
        . '<img src="' . esc_url( $url ) . '" alt="">'
        . '<button type="button" class="parafia-gk__okladka" aria-label="Ustaw jako okładkę" title="Okładka galerii">★</button>'
        . '<button type="button" class="parafia-gk__usun" aria-label="Usuń z galerii" title="Usuń z galerii">×</button>'
        . '<span class="parafia-gk__znacznik">Okładka</span>'
        . '</li>';
}

function parafia_mrozy_galeria_ramka_szczegolow( $post ) {
    $data = 'auto-draft' === $post->post_status && '' === (string) get_post_meta( $post->ID, PARAFIA_MROZY_GALERIA_DATA, true )
        ? wp_date( 'Y-m-d' )
        : parafia_mrozy_galeria_data( $post );
    $opis = parafia_mrozy_galeria_opis( $post );
    ?>
    <p>
        <label for="parafia-galeria-data" style="display:block;font-weight:600;margin-bottom:4px">Data wydarzenia</label>
        <input type="date" id="parafia-galeria-data" name="parafia_galeria_data" value="<?php echo esc_attr( $data ); ?>">
    </p>
    <p>
        <label for="parafia-galeria-opis" style="display:block;font-weight:600;margin-bottom:4px">Krótki opis (opcjonalnie)</label>
        <textarea id="parafia-galeria-opis" name="excerpt" rows="4" class="large-text"><?php echo esc_textarea( $opis ); ?></textarea>
    </p>
    <?php
}

function parafia_mrozy_galeria_zapisz( $post_id ) {
    if ( ! isset( $_POST['parafia_mrozy_galeria_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['parafia_mrozy_galeria_nonce'] ) ), 'parafia_mrozy_galeria_zapis' ) ) {
        return;
    }
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $surowe  = isset( $_POST['parafia_galeria_zdjecia'] ) ? sanitize_text_field( wp_unslash( $_POST['parafia_galeria_zdjecia'] ) ) : '';
    $zdjecia = [];
    foreach ( explode( ',', $surowe ) as $id ) {
        $id = absint( $id );
        if ( $id && ! in_array( $id, $zdjecia, true ) && wp_attachment_is_image( $id ) ) {
            $zdjecia[] = $id;
        }
    }
    update_post_meta( $post_id, PARAFIA_MROZY_GALERIA_ZDJECIA, $zdjecia );

    $okladka = isset( $_POST['parafia_galeria_okladka'] ) ? absint( $_POST['parafia_galeria_okladka'] ) : 0;
    if ( ! in_array( $okladka, $zdjecia, true ) ) {
        $okladka = $zdjecia ? $zdjecia[0] : 0;
    }
    if ( $okladka ) {
        set_post_thumbnail( $post_id, $okladka );
    } else {
        delete_post_thumbnail( $post_id );
    }

    $data = isset( $_POST['parafia_galeria_data'] ) ? sanitize_text_field( wp_unslash( $_POST['parafia_galeria_data'] ) ) : '';
    if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data ) ) {
        update_post_meta( $post_id, PARAFIA_MROZY_GALERIA_DATA, $data );
    }

    // Galeria zawsze zostaje w kategorii „galeria”.
    $kat = parafia_mrozy_galeria_kategoria_id( true );
    if ( $kat && ! has_category( $kat, $post_id ) ) {
        wp_set_post_categories( $post_id, [ $kat ], true );
    }

    foreach ( $zdjecia as $id ) {
        $zalacznik = get_post( $id );
        if ( $zalacznik && ! $zalacznik->post_parent ) {
            wp_update_post( [ 'ID' => $id, 'post_parent' => $post_id ] );
        }
    }
}
add_action( 'save_post_post', 'parafia_mrozy_galeria_zapisz' );

// Galeria dodana „po staremu” (zwykły wpis w kategorii) też dostaje datę wydarzenia.
function parafia_mrozy_galeria_domyslna_data( $post_id, $post ) {
    if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status || ! parafia_mrozy_jest_galeria( $post ) ) {
        return;
    }
    if ( '' === (string) get_post_meta( $post_id, PARAFIA_MROZY_GALERIA_DATA, true ) ) {
        update_post_meta( $post_id, PARAFIA_MROZY_GALERIA_DATA, get_the_date( 'Y-m-d', $post ) );
    }
}
add_action( 'save_post_post', 'parafia_mrozy_galeria_domyslna_data', 30, 2 );

/* -------------------------------------------------------------------------
 * Kokpit: kolumny na liście galerii
 * ---------------------------------------------------------------------- */

function parafia_mrozy_galeria_kolumny( $kolumny ) {
    if ( ! parafia_mrozy_galeria_lista_galerii() ) {
        return $kolumny;
    }
    $nowe = [ 'cb' => $kolumny['cb'], 'parafia_okladka' => '' ];
    foreach ( $kolumny as $klucz => $nazwa ) {
        if ( in_array( $klucz, [ 'cb', 'categories', 'tags', 'comments' ], true ) ) {
            continue;
        }
        $nowe[ $klucz ] = $nazwa;
        if ( 'title' === $klucz ) {
            $nowe['parafia_zdjec'] = 'Zdjęcia';
            $nowe['parafia_data']  = 'Data wydarzenia';
        }
    }
    unset( $nowe['date'] );
    return $nowe;
}
add_filter( 'manage_post_posts_columns', 'parafia_mrozy_galeria_kolumny' );

function parafia_mrozy_galeria_kolumna( $kolumna, $post_id ) {
    if ( 'parafia_okladka' === $kolumna ) {
        $okladka = parafia_mrozy_galeria_okladka( $post_id );
        echo $okladka ? wp_get_attachment_image( $okladka, [ 60, 60 ], false, [ 'style' => 'width:60px;height:60px;object-fit:cover;display:block;border-radius:3px' ] ) : '';
    } elseif ( 'parafia_zdjec' === $kolumna ) {
        echo esc_html( count( parafia_mrozy_galeria_zdjecia( $post_id ) ) );
    } elseif ( 'parafia_data' === $kolumna ) {
        echo esc_html( parafia_mrozy_galeria_data_tekst( parafia_mrozy_galeria_data( $post_id ) ) );
    }
}
add_action( 'manage_post_posts_custom_column', 'parafia_mrozy_galeria_kolumna', 10, 2 );

function parafia_mrozy_galeria_styl_kolumn() {
    if ( parafia_mrozy_galeria_lista_galerii() ) {
        echo '<style>.column-parafia_okladka{width:72px}.column-parafia_zdjec{width:80px}.column-parafia_data{width:160px}</style>';
    }
}
add_action( 'admin_head', 'parafia_mrozy_galeria_styl_kolumn' );

// Lista galerii w kokpicie: od najnowszego wydarzenia.
function parafia_mrozy_galeria_kolejnosc_kokpit( $zapytanie ) {
    if ( ! is_admin() || ! $zapytanie->is_main_query() || ! parafia_mrozy_galeria_lista_galerii() || isset( $_GET['orderby'] ) ) {
        return;
    }
    $args = parafia_mrozy_galeria_args();
    $zapytanie->set( 'meta_query', $args['meta_query'] );
    $zapytanie->set( 'orderby', $args['orderby'] );
}
add_action( 'pre_get_posts', 'parafia_mrozy_galeria_kolejnosc_kokpit' );
