<?php
/**
 * Panel „Parafia → Uprawnienia” (jak w motywie Parafia Jedlanka).
 *
 * Tabela: rola × sekcja (Aktualności, Galerie, Ogłoszenia, Intencje, Strony)
 * z poziomami: brak / pisze do akceptacji / publikuje własne / pełny dostęp,
 * plus prawo do zmiany ustawień strony. Role dodatkowe: Proboszcz,
 * Kancelaria parafialna, Media parafialne.
 *
 * Galerie są wpisami w kategorii „galeria”, dlatego dostają własny zestaw
 * uprawnień (edit_galerie, publish_galerie…) mapowany w map_meta_cap.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PARAFIA_MROZY_ROLE_WERSJA', '1' );

/** sekcja => [ etykieta, przyrostek uprawnień ] */
function parafia_mrozy_sekcje_uprawnien() {
    return [
        'aktualnosci' => [ 'Aktualności (wpisy)', 'posts' ],
        'galerie'     => [ 'Galerie', 'galerie' ],
        'ogloszenia'  => [ 'Ogłoszenia', 'ogloszenia' ],
        'intencje'    => [ 'Intencje', 'intencje_mszalne' ],
        'strony'      => [ 'Strony', 'pages' ],
    ];
}

function parafia_mrozy_poziomy() {
    return [
        'brak'       => 'Brak dostępu',
        'akceptacja' => 'Pisze – publikuje ktoś inny',
        'wlasne'     => 'Publikuje własne',
        'pelny'      => 'Pełny dostęp',
    ];
}

function parafia_mrozy_role_wlasne() {
    return [
        'proboszcz'        => 'Proboszcz',
        'kancelaria'       => 'Kancelaria parafialna',
        'media_parafialne' => 'Media parafialne',
    ];
}

function parafia_mrozy_role_w_tabeli() {
    return [ 'proboszcz', 'editor', 'kancelaria', 'media_parafialne', 'author', 'contributor' ];
}

function parafia_mrozy_uprawnienia_domyslne() {
    $wszystko = array_fill_keys( array_keys( parafia_mrozy_sekcje_uprawnien() ), 'pelny' );
    return [
        'proboszcz'        => $wszystko + [ 'ustawienia' => 1 ],
        'editor'           => $wszystko + [ 'ustawienia' => 0 ],
        'kancelaria'       => [
            'aktualnosci' => 'brak',
            'galerie'     => 'brak',
            'ogloszenia'  => 'pelny',
            'intencje'    => 'pelny',
            'strony'      => 'brak',
            'ustawienia'  => 0,
        ],
        'media_parafialne' => [
            'aktualnosci' => 'wlasne',
            'galerie'     => 'wlasne',
            'ogloszenia'  => 'brak',
            'intencje'    => 'brak',
            'strony'      => 'brak',
            'ustawienia'  => 0,
        ],
        'author'           => [
            'aktualnosci' => 'wlasne',
            'galerie'     => 'wlasne',
            'ogloszenia'  => 'brak',
            'intencje'    => 'brak',
            'strony'      => 'brak',
            'ustawienia'  => 0,
        ],
        'contributor'      => [
            'aktualnosci' => 'akceptacja',
            'galerie'     => 'akceptacja',
            'ogloszenia'  => 'brak',
            'intencje'    => 'brak',
            'strony'      => 'brak',
            'ustawienia'  => 0,
        ],
    ];
}

function parafia_mrozy_uprawnienia() {
    $domyslne = parafia_mrozy_uprawnienia_domyslne();
    $zapisane = get_option( 'parafia_mrozy_uprawnienia', [] );
    $wynik    = [];
    foreach ( $domyslne as $rola => $wiersz ) {
        $wynik[ $rola ] = isset( $zapisane[ $rola ] ) && is_array( $zapisane[ $rola ] )
            ? array_merge( $wiersz, $zapisane[ $rola ] )
            : $wiersz;
    }
    return $wynik;
}

function parafia_mrozy_uprawnienia_poziomu( $mnoga, $poziom ) {
    $akceptacja = [ "edit_{$mnoga}", "delete_{$mnoga}" ];
    $wlasne     = array_merge( $akceptacja, [ "publish_{$mnoga}", "edit_published_{$mnoga}", "delete_published_{$mnoga}" ] );
    $pelny      = array_merge( $wlasne, [ "edit_others_{$mnoga}", "delete_others_{$mnoga}", "read_private_{$mnoga}", "edit_private_{$mnoga}", "delete_private_{$mnoga}" ] );
    switch ( $poziom ) {
        case 'akceptacja':
            return $akceptacja;
        case 'wlasne':
            return $wlasne;
        case 'pelny':
            return $pelny;
        default:
            return [];
    }
}

function parafia_mrozy_zastosuj_uprawnienia() {
    $tabela = parafia_mrozy_uprawnienia();
    $sekcje = parafia_mrozy_sekcje_uprawnien();
    $wlasne = parafia_mrozy_role_wlasne();

    foreach ( $tabela as $klucz_roli => $wiersz ) {
        $rola = get_role( $klucz_roli );
        if ( ! $rola ) {
            continue;
        }
        $zdjecia = false;
        foreach ( $sekcje as $sekcja => $dane ) {
            foreach ( parafia_mrozy_uprawnienia_poziomu( $dane[1], 'pelny' ) as $cap ) {
                $rola->remove_cap( $cap );
            }
            $poziom = isset( $wiersz[ $sekcja ] ) ? $wiersz[ $sekcja ] : 'brak';
            foreach ( parafia_mrozy_uprawnienia_poziomu( $dane[1], $poziom ) as $cap ) {
                $rola->add_cap( $cap );
            }
            if ( in_array( $poziom, [ 'wlasne', 'pelny' ], true ) || ( 'galerie' === $sekcja && 'brak' !== $poziom ) ) {
                $zdjecia = true;
            }
        }

        if ( $zdjecia ) {
            $rola->add_cap( 'upload_files' );
        } elseif ( isset( $wlasne[ $klucz_roli ] ) && 'proboszcz' !== $klucz_roli ) {
            $rola->remove_cap( 'upload_files' );
        }

        if ( ! empty( $wiersz['ustawienia'] ) ) {
            $rola->add_cap( 'parafia_ustawienia' );
        } else {
            $rola->remove_cap( 'parafia_ustawienia' );
        }
    }

    $admin = get_role( 'administrator' );
    if ( $admin ) {
        foreach ( $sekcje as $dane ) {
            foreach ( parafia_mrozy_uprawnienia_poziomu( $dane[1], 'pelny' ) as $cap ) {
                $admin->add_cap( $cap );
            }
        }
        $admin->add_cap( 'parafia_ustawienia' );
    }
}

function parafia_mrozy_zainstaluj_role() {
    $redaktor = get_role( 'editor' );
    $bazowe   = $redaktor ? $redaktor->capabilities : [ 'read' => true ];

    if ( ! get_role( 'proboszcz' ) ) {
        add_role( 'proboszcz', 'Proboszcz', $bazowe );
    }
    if ( ! get_role( 'kancelaria' ) ) {
        add_role( 'kancelaria', 'Kancelaria parafialna', [ 'read' => true ] );
    }
    if ( ! get_role( 'media_parafialne' ) ) {
        add_role( 'media_parafialne', 'Media parafialne', [ 'read' => true ] );
    }

    parafia_mrozy_zastosuj_uprawnienia();
    update_option( 'parafia_mrozy_role_wersja', PARAFIA_MROZY_ROLE_WERSJA, false );
}
add_action( 'after_switch_theme', 'parafia_mrozy_zainstaluj_role' );

function parafia_mrozy_sprawdz_role() {
    if ( get_option( 'parafia_mrozy_role_wersja' ) !== PARAFIA_MROZY_ROLE_WERSJA ) {
        parafia_mrozy_zainstaluj_role();
    }
}
add_action( 'init', 'parafia_mrozy_sprawdz_role', 20 );

/* -------------------------------------------------------------------------
 * Galerie = wpisy z kategorii „galeria” → własne uprawnienia
 * ---------------------------------------------------------------------- */

function parafia_mrozy_mapa_galerii() {
    $mapa = [];
    foreach ( parafia_mrozy_uprawnienia_poziomu( 'posts', 'pelny' ) as $cap ) {
        $mapa[ $cap ] = str_replace( '_posts', '_galerie', $cap );
    }
    return $mapa;
}

// Uprawnienia do konkretnej galerii (edycja, usuwanie, publikacja, podgląd).
function parafia_mrozy_galeria_map_meta_cap( $caps, $cap, $user_id, $args ) {
    if ( ! in_array( $cap, [ 'edit_post', 'delete_post', 'publish_post', 'read_post' ], true ) || empty( $args[0] ) ) {
        return $caps;
    }
    if ( ! function_exists( 'parafia_mrozy_jest_galeria' ) || ! parafia_mrozy_jest_galeria( $args[0] ) ) {
        return $caps;
    }
    $mapa = parafia_mrozy_mapa_galerii();
    return array_map( function ( $c ) use ( $mapa ) {
        return isset( $mapa[ $c ] ) ? $mapa[ $c ] : $c;
    }, $caps );
}
add_filter( 'map_meta_cap', 'parafia_mrozy_galeria_map_meta_cap', 10, 4 );

/**
 * Czy bieżące żądanie dotyczy galerii (lista galerii, ekran galerii, zapis galerii).
 * Wtedy ogólne uprawnienia do wpisów (edit_posts, publish_posts…) przyjmują
 * wartość uprawnień do galerii.
 */
function parafia_mrozy_kontekst_galerii() {
    static $wynik = null;
    if ( null !== $wynik ) {
        return $wynik;
    }
    if ( ! is_admin() || ! function_exists( 'parafia_mrozy_galeria_tryb_edycji' ) ) {
        $wynik = false;
        return $wynik;
    }
    global $pagenow;
    if ( empty( $pagenow ) || ! did_action( 'init' ) ) {
        return false; // kategorie jeszcze niezarejestrowane – sprawdź ponownie później
    }
    $wynik = false;
    if ( parafia_mrozy_galeria_lista_galerii() || parafia_mrozy_galeria_tryb_edycji() ) {
        $wynik = true;
    } elseif ( 'post.php' === $pagenow && isset( $_POST['parafia_mrozy_galeria_nonce'], $_POST['post_ID'] ) ) {
        $wynik = wp_verify_nonce( sanitize_key( wp_unslash( $_POST['parafia_mrozy_galeria_nonce'] ) ), 'parafia_mrozy_galeria_zapis' )
            && parafia_mrozy_jest_galeria( absint( $_POST['post_ID'] ) );
    }
    return $wynik;
}

function parafia_mrozy_uprawnienia_dynamiczne( $wszystkie ) {
    // Administrator zawsze może zmieniać ustawienia strony.
    if ( ! empty( $wszystkie['manage_options'] ) ) {
        $wszystkie['parafia_ustawienia'] = true;
    }

    if ( parafia_mrozy_kontekst_galerii() ) {
        foreach ( parafia_mrozy_mapa_galerii() as $wpis => $galeria ) {
            $wszystkie[ $wpis ] = ! empty( $wszystkie[ $galeria ] );
        }
        return $wszystkie;
    }

    // Okno mediów przy galerii: osoba z dostępem tylko do galerii może opisać i wgrać zdjęcie.
    if ( empty( $wszystkie['edit_posts'] ) && ! empty( $wszystkie['edit_galerie'] ) ) {
        $ajax = wp_doing_ajax() && isset( $_REQUEST['action'] )
            && in_array( sanitize_key( wp_unslash( $_REQUEST['action'] ) ), [ 'upload-attachment', 'query-attachments', 'save-attachment', 'save-attachment-compat', 'get-attachment' ], true );
        global $pagenow;
        if ( $ajax || 'async-upload.php' === $pagenow ) {
            $wszystkie['edit_posts'] = true;
        }
    }

    // Edytor bloków (ogłoszenia, intencje) czyta przez REST m.in. taksonomie i bloki
    // wielokrotnego użytku — potrzebuje do tego edit_posts.
    if ( empty( $wszystkie['edit_posts'] ) && defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        $metoda = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
        if ( isset( $_GET['_method'] ) ) {
            $metoda = sanitize_key( wp_unslash( $_GET['_method'] ) );
        } elseif ( isset( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ) {
            $metoda = sanitize_key( wp_unslash( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) );
        }
        $trasa = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? (string) $GLOBALS['wp']->query_vars['rest_route'] : '';
        if ( 'get' === $metoda && preg_match( '#^/wp/v2/(taxonomies|global-styles|blocks|types)(/|$)#', $trasa ) ) {
            foreach ( [ 'edit_ogloszenia', 'edit_intencje_mszalne', 'edit_galerie' ] as $cap ) {
                if ( ! empty( $wszystkie[ $cap ] ) ) {
                    $wszystkie['edit_posts'] = true;
                    break;
                }
            }
        }
    }
    return $wszystkie;
}
add_filter( 'user_has_cap', 'parafia_mrozy_uprawnienia_dynamiczne' );

/* -------------------------------------------------------------------------
 * Zakładka „Uprawnienia”
 * ---------------------------------------------------------------------- */

function parafia_mrozy_strona_uprawnien() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $tabela  = parafia_mrozy_uprawnienia();
    $sekcje  = parafia_mrozy_sekcje_uprawnien();
    $poziomy = parafia_mrozy_poziomy();
    $liczby  = count_users();
    $liczby  = isset( $liczby['avail_roles'] ) ? $liczby['avail_roles'] : [];
    $role    = wp_roles();
    ?>
    <div class="wrap">
        <h1>Parafia</h1>
        <?php parafia_mrozy_zakladki( PARAFIA_MROZY_UPRAWNIENIA_STRONA ); ?>
        <?php if ( isset( $_GET['zapisano'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>Uprawnienia zapisane.</p></div>
        <?php endif; ?>
        <p>Wybierz, co może robić każda rola. Sekcja, do której rola nie ma dostępu, w ogóle nie pojawia się w jej kokpicie. Rolę przypisuje się osobie w menu <a href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>">Użytkownicy</a>.</p>
        <ul style="list-style:disc;padding-left:1.5em">
            <li><strong><?php echo esc_html( $poziomy['akceptacja'] ); ?></strong> – może przygotować wpis i wysłać go do akceptacji.</li>
            <li><strong><?php echo esc_html( $poziomy['wlasne'] ); ?></strong> – publikuje sam, ale poprawia i usuwa tylko swoje wpisy.</li>
            <li><strong><?php echo esc_html( $poziomy['pelny'] ); ?></strong> – publikuje, poprawia i usuwa wszystkie wpisy w sekcji, także akceptuje cudze.</li>
            <li><strong>Ustawienia strony</strong> – dostęp do zakładki „Ustawienia strony” (dane parafii, Msze, sekcje strony głównej).</li>
        </ul>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="parafia_mrozy_zapisz_uprawnienia">
            <?php wp_nonce_field( 'parafia_mrozy_uprawnienia' ); ?>
            <div style="overflow-x:auto">
            <table class="widefat striped" style="max-width:1300px">
                <thead>
                    <tr>
                        <th scope="col">Rola</th>
                        <?php foreach ( $sekcje as $dane ) : ?>
                            <th scope="col"><?php echo esc_html( $dane[0] ); ?></th>
                        <?php endforeach; ?>
                        <th scope="col">Ustawienia strony</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row"><?php echo esc_html( translate_user_role( 'Administrator' ) ); ?><br><span class="description"><?php echo esc_html( sprintf( '%d os.', isset( $liczby['administrator'] ) ? $liczby['administrator'] : 0 ) ); ?></span></th>
                        <?php foreach ( $sekcje as $dane ) : ?>
                            <td><?php echo esc_html( $poziomy['pelny'] ); ?></td>
                        <?php endforeach; ?>
                        <td>Tak</td>
                    </tr>
                    <?php
                    foreach ( parafia_mrozy_role_w_tabeli() as $klucz_roli ) :
                        if ( ! isset( $role->role_names[ $klucz_roli ] ) ) {
                            continue;
                        }
                        $wiersz = $tabela[ $klucz_roli ];
                        ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( translate_user_role( $role->role_names[ $klucz_roli ] ) ); ?><br><span class="description"><?php echo esc_html( sprintf( '%d os.', isset( $liczby[ $klucz_roli ] ) ? $liczby[ $klucz_roli ] : 0 ) ); ?></span></th>
                            <?php foreach ( $sekcje as $sekcja => $dane ) : ?>
                                <td>
                                    <label class="screen-reader-text" for="<?php echo esc_attr( "pu-{$klucz_roli}-{$sekcja}" ); ?>"><?php echo esc_html( $role->role_names[ $klucz_roli ] . ' – ' . $dane[0] ); ?></label>
                                    <select id="<?php echo esc_attr( "pu-{$klucz_roli}-{$sekcja}" ); ?>" name="<?php echo esc_attr( "uprawnienia[{$klucz_roli}][{$sekcja}]" ); ?>">
                                        <?php foreach ( $poziomy as $poziom => $opis ) : ?>
                                            <option value="<?php echo esc_attr( $poziom ); ?>" <?php selected( $wiersz[ $sekcja ], $poziom ); ?>><?php echo esc_html( $opis ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            <?php endforeach; ?>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr( "uprawnienia[{$klucz_roli}][ustawienia]" ); ?>" value="1" <?php checked( ! empty( $wiersz['ustawienia'] ) ); ?>> Tak</label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <p class="submit">
                <?php submit_button( 'Zapisz uprawnienia', 'primary', 'submit', false ); ?>
                &nbsp;
                <?php submit_button( 'Przywróć domyślne', 'secondary', 'przywroc', false ); ?>
            </p>
        </form>
    </div>
    <?php
}

function parafia_mrozy_zapisz_uprawnienia() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Brak uprawnień.', 403 );
    }
    check_admin_referer( 'parafia_mrozy_uprawnienia' );

    if ( isset( $_POST['przywroc'] ) ) {
        delete_option( 'parafia_mrozy_uprawnienia' );
    } else {
        $wejscie = isset( $_POST['uprawnienia'] ) && is_array( $_POST['uprawnienia'] ) ? wp_unslash( $_POST['uprawnienia'] ) : [];
        $poziomy = array_keys( parafia_mrozy_poziomy() );
        $czyste  = [];
        foreach ( parafia_mrozy_role_w_tabeli() as $klucz_roli ) {
            $wiersz = isset( $wejscie[ $klucz_roli ] ) && is_array( $wejscie[ $klucz_roli ] ) ? $wejscie[ $klucz_roli ] : [];
            foreach ( array_keys( parafia_mrozy_sekcje_uprawnien() ) as $sekcja ) {
                $poziom                           = isset( $wiersz[ $sekcja ] ) ? sanitize_key( $wiersz[ $sekcja ] ) : 'brak';
                $czyste[ $klucz_roli ][ $sekcja ] = in_array( $poziom, $poziomy, true ) ? $poziom : 'brak';
            }
            $czyste[ $klucz_roli ]['ustawienia'] = empty( $wiersz['ustawienia'] ) ? 0 : 1;
        }
        update_option( 'parafia_mrozy_uprawnienia', $czyste, false );
    }

    parafia_mrozy_zastosuj_uprawnienia();
    wp_safe_redirect( admin_url( 'admin.php?page=' . PARAFIA_MROZY_UPRAWNIENIA_STRONA . '&zapisano=1' ) );
    exit;
}
add_action( 'admin_post_parafia_mrozy_zapisz_uprawnienia', 'parafia_mrozy_zapisz_uprawnienia' );

/* -------------------------------------------------------------------------
 * Kokpit: „Parafia – szybkie dodawanie”
 * ---------------------------------------------------------------------- */

function parafia_mrozy_widget_kokpitu() {
    wp_add_dashboard_widget( 'parafia_szybkie', 'Parafia – szybkie dodawanie', 'parafia_mrozy_widget_kokpitu_tresc' );

    global $wp_meta_boxes;
    if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['parafia_szybkie'] ) ) {
        $ramka = $wp_meta_boxes['dashboard']['normal']['core']['parafia_szybkie'];
        unset( $wp_meta_boxes['dashboard']['normal']['core']['parafia_szybkie'] );
        $wp_meta_boxes['dashboard']['normal']['core'] = [ 'parafia_szybkie' => $ramka ] + $wp_meta_boxes['dashboard']['normal']['core'];
    }
}
add_action( 'wp_dashboard_setup', 'parafia_mrozy_widget_kokpitu' );

function parafia_mrozy_widget_kokpitu_tresc() {
    $pozycje = [
        [ 'edit_posts', 'publish_posts', 'Dodaj wpis w Aktualnościach', admin_url( 'post-new.php' ), 'Wszystkie wpisy', admin_url( 'edit.php' ) ],
        [ 'edit_galerie', 'publish_galerie', 'Dodaj galerię', admin_url( 'post-new.php?galeria=1' ), 'Wszystkie galerie', admin_url( 'edit.php?category_name=galeria' ) ],
        [ 'edit_ogloszenia', 'publish_ogloszenia', 'Dodaj ogłoszenia', admin_url( 'post-new.php?post_type=ogloszenia' ), 'Wszystkie ogłoszenia', admin_url( 'edit.php?post_type=ogloszenia' ) ],
        [ 'edit_intencje_mszalne', 'publish_intencje_mszalne', 'Dodaj intencje', admin_url( 'post-new.php?post_type=intencje' ), 'Wszystkie intencje', admin_url( 'edit.php?post_type=intencje' ) ],
    ];
    $cokolwiek = false;
    echo '<ul style="margin:0">';
    foreach ( $pozycje as $p ) {
        if ( ! current_user_can( $p[0] ) ) {
            continue;
        }
        $cokolwiek = true;
        $uwaga     = current_user_can( $p[1] ) ? '' : ' <span class="description">(opublikuje osoba zatwierdzająca)</span>';
        printf(
            '<li style="display:flex;flex-wrap:wrap;gap:.5em 1em;align-items:center;padding:.5em 0;border-bottom:1px solid #f0f0f1"><a class="button button-primary" href="%1$s">%2$s</a><a href="%3$s">%4$s</a>%5$s</li>',
            esc_url( $p[3] ),
            esc_html( $p[2] ),
            esc_url( $p[5] ),
            esc_html( $p[4] ),
            $uwaga // phpcs:ignore WordPress.Security.EscapeOutput
        );
    }
    if ( current_user_can( 'parafia_ustawienia' ) ) {
        $cokolwiek = true;
        printf(
            '<li style="padding:.5em 0"><a href="%1$s">%2$s</a></li>',
            esc_url( admin_url( 'admin.php?page=' . PARAFIA_MROZY_USTAWIENIA_STRONA ) ),
            'Godziny Mszy, kancelaria, kontakt, konto i sekcje strony głównej – ustawienia strony'
        );
    }
    echo '</ul>';
    if ( ! $cokolwiek ) {
        echo '<p>Twoje konto nie ma jeszcze dostępu do żadnej sekcji. Poproś administratora strony o nadanie roli.</p>';
    }
}
