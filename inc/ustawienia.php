<?php
/**
 * Panel „Parafia → Ustawienia strony”.
 *
 * Jedno miejsce na najważniejsze elementy strony: dane parafii, konto,
 * porządek Mszy, kancelarię, sekcje strony głównej, licznik rocznicy,
 * cytat i stopkę. Wartości są zapisywane jako theme_mods — te same klucze,
 * których używały dotąd Customizer i szablony (pm_get()), więc nic nie ginie.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PARAFIA_MROZY_USTAWIENIA_STRONA', 'parafia-ustawienia' );
define( 'PARAFIA_MROZY_UPRAWNIENIA_STRONA', 'parafia-uprawnienia' );

/**
 * Pola ustawień: klucz => [ etykieta, typ, domyślnie, sekcja, opis ].
 */
function parafia_mrozy_pola_ustawien() {
    return [
        // Dane parafii
        'parafia_nazwa'          => [ 'Pełna nazwa parafii', 'text', 'Parafia Rzymskokatolicka pw. św. Teresy od Dzieciątka Jezus w Mrozach', 'parafia', 'Na stronie głównej (kancelaria), na stronie Kancelaria i w danych do przelewu.' ],
        'parafia_address'        => [ 'Adres', 'text', 'ul. Św. Teresy 3, 05-320 Mrozy', 'parafia', '' ],
        'parafia_phone'          => [ 'Telefon', 'text', '25 757 41 93', 'parafia', '' ],
        'parafia_email'          => [ 'Adres e-mail', 'email', 'parafiamrozy@gmail.com', 'parafia', '' ],
        'parafia_map_url'        => [ 'Mapa dojazdu – adres osadzenia', 'url', 'https://maps.google.com/maps?q=Parafia+Rzk%C5%82+w%C5%9B%C4%85w+Teresa+Mrozy&output=embed', 'parafia', 'Adres „src” z kodu osadzenia Map Google (Udostępnij → Umieść mapę).' ],

        // Konto
        'parafia_bank'           => [ 'Bank', 'text', 'Bank Spółdzielczy w Mrozach', 'konto', '' ],
        'parafia_konto'          => [ 'Numer konta', 'text', '58 9227 0004 0000 0808 2000 0010', 'konto', '' ],
        'pm_tytul_przelewu'      => [ 'Tytuł przelewu (stopka)', 'text', 'Darowizna na cele kultu religijnego', 'konto', '' ],

        // Msze
        'msze_powszednie'        => [ 'Msze w dni powszednie', 'text', '07:00 i 18:00', 'msze', '' ],
        'msze_niedziela'         => [ 'Msze w niedzielę', 'text', '07:00, 08:30, 10:00, 12:00 i 18:00', 'msze', '' ],
        'spowiedz'               => [ 'Spowiedź', 'text', 'Przed każdą Mszą Świętą oraz w Pierwszy Piątek Miesiąca', 'msze', '' ],
        'rozaniec'               => [ 'Różaniec', 'text', 'Niedziela: 17:30', 'msze', '' ],

        // Kancelaria
        'pm_kancelaria_godziny'  => [ 'Godziny otwarcia (jeden dzień w wierszu)', 'textarea', "Wtorek: 16:30 – 17:30\nŚroda: 16:30 – 17:30\nCzwartek: 8:45 – 9:30\nPiątek: 16:30 – 17:30\nSobota: 8:45 – 9:30", 'kancelaria', 'Widoczne w panelu informacyjnym na stronie głównej.' ],
        'pm_kancelaria_uwaga_t'  => [ 'Ważna informacja – nagłówek', 'text', 'W Pierwszy Piątek Miesiąca', 'kancelaria', 'Wyróżniona na czerwono. Zostaw oba pola puste, aby ukryć.' ],
        'pm_kancelaria_uwaga'    => [ 'Ważna informacja – treść', 'text', 'kancelaria nieczynna!', 'kancelaria', '' ],

        // Strona główna — sekcje
        'pm_sekcja_slider'       => [ 'Slider (zdjęcia na górze)', 'wlacz', '1', 'sekcje', '' ],
        'pm_sekcja_adwent'       => [ 'Adwent – „Droga do Betlejem”', 'adwent', '1', 'sekcje', 'Pokazuje się sama tylko od I niedzieli Adwentu do terminu ustawionego w menu Adwent. Treści, termin i tryb testowy: menu Adwent.' ],
        'pm_sekcja_aktualnosci'  => [ 'Aktualności parafialne', 'wlacz', '1', 'sekcje', '' ],
        'pm_sekcja_ogloszenia'   => [ 'Ogłoszenia parafialne', 'wlacz', '1', 'sekcje', '' ],
        'pm_sekcja_intencje'     => [ 'Intencje Mszy św.', 'wlacz', '1', 'sekcje', '' ],
        'pm_sekcja_rocznica'     => [ 'Licznik do rocznicy', 'wlacz', '1', 'sekcje', '' ],
        'pm_sekcja_galeria'      => [ 'Galeria', 'wlacz', '1', 'sekcje', '' ],
        'pm_sekcja_kalendarz'    => [ 'Kalendarz liturgiczny', 'wlacz', '1', 'sekcje', '' ],
        'pm_sekcja_info'         => [ 'Kancelaria i porządek Mszy', 'wlacz', '1', 'sekcje', '' ],
        'pm_sekcja_cytat'        => [ 'Cytat', 'wlacz', '1', 'sekcje', '' ],
        'pm_sekcja_vatican'      => [ 'Vatican News', 'wlacz', '1', 'sekcje', '' ],

        // Strona główna — liczba elementów
        'pm_liczba_slajdow'      => [ 'Slajdy w sliderze', 'number', '5', 'liczby', 'Slider pokazuje wpisy z zaznaczonym „Ustawienia slajdera hero”, a gdy ich brak – 3 najnowsze aktualności.' ],
        'pm_liczba_aktualnosci'  => [ 'Aktualności', 'number', '6', 'liczby', '' ],
        'pm_liczba_ogloszen'     => [ 'Ogłoszenia', 'number', '3', 'liczby', '' ],
        'pm_liczba_intencji'     => [ 'Intencje', 'number', '3', 'liczby', '' ],
        'pm_liczba_galerii'      => [ 'Galerie', 'number', '3', 'liczby', '' ],

        // Rocznica
        'pm_rocznica_data'       => [ 'Data rocznicy', 'date', '2031-04-01', 'rocznica', 'Licznik odlicza czas do północy tego dnia.' ],
        'pm_rocznica_etykieta'   => [ 'Napis nad datą', 'text', 'Do 100. rocznicy erygowania parafii', 'rocznica', '' ],
        'pm_rocznica_podpis'     => [ 'Podpis pod licznikiem', 'text', 'Parafia pw. Św. Teresy od Dzieciątka Jezus w Mrozach · erygowana 1 kwietnia 1931', 'rocznica', '' ],

        // Cytat
        'pm_cytat_tresc'         => [ 'Treść cytatu', 'tekst_html', '„zrozumiałam, że kościół ma Serce i że Serce to <strong>PŁONIE MIŁOŚCIĄ</strong>. zrozumiałam, że jedynie miłość sprawia, że działają członki Kościoła, że gdyby Miłość wygasła, Apostołowie nie głosiliby Ewangelii, Męczennicy odmówiliby przelewania swojej krwi. Zrozumiałam, że miłość zawiera wszystkie powołania, że miłość jest wszystkim, obejmuje wszystkie czasy i wszystkie miejsca… jednym słowem jest wieczna!"', 'cytat', 'Można wyróżnić fragment znacznikami <strong>…</strong>.' ],
        'pm_cytat_zrodlo'        => [ 'Źródło cytatu', 'text', '(por. św. Teresa od Dzieciątka Jezus, Dzieje Duszy, 2005, s. 264)', 'cytat', '' ],

        // Stopka
        'pm_stopka_nazwa'        => [ 'Nazwa parafii w stopce', 'textarea', "Parafia Rzymskokatolicka\npw. św. Teresy od Dzieciątka Jezus\nw Mrozach", 'stopka', 'Każdy wiersz to osobna linia pod logo.' ],
        'pm_stopka_linki'        => [ 'Przydatne strony (jedna w wierszu)', 'textarea', "Niezbędnik Katolika | https://niezbednik.niedziela.pl/\nKatolicka Agencja Informacyjna | https://ekai.pl/\nVatican News | https://www.vaticannews.va/pl.html\nDiecezja Warszawsko-Praska | https://diecezja.waw.pl/\nIdziemy | https://www.idziemy.pl/\nGość niedzielny | https://gosc.pl/\nMały gość | https://malygosc.pl/", 'stopka', 'Format: Nazwa | adres. Zostaw puste, aby ukryć listę.' ],
        'pm_stopka_podpis'       => [ 'Podpis twórcy', 'text', 'Stworzone z ❤️ przez Kacper Makowski na wyższą Chwałę Bożą', 'stopka', 'Zostaw puste, aby ukryć.' ],
    ];
}

function parafia_mrozy_sekcje_ustawien() {
    return [
        'parafia'    => [ 'Dane parafii', 'Stopka, strona główna, strona Kancelaria i Kontakt.' ],
        'konto'      => [ 'Konto parafii', '' ],
        'msze'       => [ 'Msze Święte i nabożeństwa', 'Panel „Porządek Mszy Św.” na stronie głównej.' ],
        'kancelaria' => [ 'Kancelaria – godziny', '' ],
        'sekcje'     => [ 'Strona główna – widoczne sekcje', 'Odznacz sekcję, aby ukryć ją na stronie głównej.' ],
        'liczby'     => [ 'Strona główna – ile elementów pokazać', '' ],
        'rocznica'   => [ 'Licznik do rocznicy', '' ],
        'cytat'      => [ 'Cytat na stronie głównej', '' ],
        'stopka'     => [ 'Stopka', '' ],
    ];
}

/** Wartość ustawienia (bez escapowania) z domyślną z rejestru pól. */
function pm_opcja( $klucz ) {
    if ( 'pm_sekcja_adwent' === $klucz ) {
        $adwent = function_exists( 'parafia_adwent_ustawienia' ) ? parafia_adwent_ustawienia() : [];
        return empty( $adwent['wlaczony'] ) ? '0' : '1';
    }
    $pola     = parafia_mrozy_pola_ustawien();
    $domyslna = isset( $pola[ $klucz ] ) ? $pola[ $klucz ][2] : '';
    $wartosc  = get_theme_mod( $klucz, null );
    return null === $wartosc ? $domyslna : (string) $wartosc;
}

/** Czy sekcja strony głównej jest włączona. */
function pm_sekcja( $sekcja ) {
    return '1' === pm_opcja( 'pm_sekcja_' . $sekcja );
}

/** Liczba elementów sekcji (1–24). */
function pm_liczba( $klucz ) {
    return max( 1, min( 24, (int) pm_opcja( 'pm_liczba_' . $klucz ) ) );
}

/** Wiersze pola wielowierszowego, bez pustych. */
function pm_wiersze( $klucz ) {
    return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', pm_opcja( $klucz ) ) ), 'strlen' ) );
}

/** Lista „Nazwa | adres” → [ [nazwa, url], … ]. */
function pm_linki( $klucz ) {
    $linki = [];
    foreach ( pm_wiersze( $klucz ) as $wiersz ) {
        $czesci = array_map( 'trim', explode( '|', $wiersz, 2 ) );
        if ( 2 === count( $czesci ) && '' !== $czesci[0] && '' !== $czesci[1] ) {
            $linki[] = $czesci;
        }
    }
    return $linki;
}

function pm_data_slownie( $data ) {
    if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $data, $m ) ) {
        return '';
    }
    $miesiace = [ 1 => 'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia' ];
    return (int) $m[3] . ' ' . ( $miesiace[ (int) $m[2] ] ?? '' ) . ' ' . $m[1];
}

function parafia_mrozy_oczysc_pole( $pole, $wartosc ) {
    $wartosc = is_string( $wartosc ) ? $wartosc : '';
    switch ( $pole[1] ) {
        case 'textarea':
            return sanitize_textarea_field( $wartosc );
        case 'tekst_html':
            return trim( wp_kses( $wartosc, [ 'strong' => [], 'em' => [], 'b' => [], 'i' => [], 'br' => [] ] ) );
        case 'email':
            return sanitize_email( $wartosc );
        case 'url':
            return esc_url_raw( trim( $wartosc ) );
        case 'number':
            return (string) max( 1, min( 24, absint( $wartosc ) ) );
        case 'date':
            return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $wartosc ) ? $wartosc : $pole[2];
        case 'wlacz':
        case 'adwent':
            return '1' === $wartosc ? '1' : '0';
        default:
            return sanitize_text_field( $wartosc );
    }
}

/* -------------------------------------------------------------------------
 * Menu „Parafia” z dwiema zakładkami
 * ---------------------------------------------------------------------- */

function parafia_mrozy_menu_parafii() {
    add_menu_page( 'Ustawienia strony', 'Parafia', 'parafia_ustawienia', PARAFIA_MROZY_USTAWIENIA_STRONA, 'parafia_mrozy_strona_ustawien', 'dashicons-building', 3 );
    add_submenu_page( PARAFIA_MROZY_USTAWIENIA_STRONA, 'Ustawienia strony', 'Ustawienia strony', 'parafia_ustawienia', PARAFIA_MROZY_USTAWIENIA_STRONA, 'parafia_mrozy_strona_ustawien' );
    add_submenu_page( PARAFIA_MROZY_USTAWIENIA_STRONA, 'Uprawnienia', 'Uprawnienia', 'manage_options', PARAFIA_MROZY_UPRAWNIENIA_STRONA, 'parafia_mrozy_strona_uprawnien' );
}
add_action( 'admin_menu', 'parafia_mrozy_menu_parafii' );

function parafia_mrozy_zakladki( $aktywna ) {
    $zakladki = [ PARAFIA_MROZY_USTAWIENIA_STRONA => 'Ustawienia strony' ];
    if ( current_user_can( 'manage_options' ) ) {
        $zakladki[ PARAFIA_MROZY_UPRAWNIENIA_STRONA ] = 'Uprawnienia';
    }
    echo '<nav class="nav-tab-wrapper wp-clearfix" aria-label="Parafia">';
    foreach ( $zakladki as $strona => $tytul ) {
        printf(
            '<a href="%1$s" class="nav-tab%2$s"%3$s>%4$s</a>',
            esc_url( admin_url( 'admin.php?page=' . $strona ) ),
            $strona === $aktywna ? ' nav-tab-active' : '',
            $strona === $aktywna ? ' aria-current="page"' : '',
            esc_html( $tytul )
        );
    }
    echo '</nav>';
}

function parafia_mrozy_strona_ustawien() {
    if ( ! current_user_can( 'parafia_ustawienia' ) ) {
        return;
    }
    $pola = parafia_mrozy_pola_ustawien();
    ?>
    <div class="wrap">
        <h1>Parafia</h1>
        <?php parafia_mrozy_zakladki( PARAFIA_MROZY_USTAWIENIA_STRONA ); ?>
        <?php if ( isset( $_GET['zapisano'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>Ustawienia zapisane.</p></div>
        <?php endif; ?>
        <p>Dane wpisane tutaj pojawiają się automatycznie na stronie głównej, w stopce oraz na stronach Kancelaria i Kontakt.</p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="parafia_mrozy_zapisz_ustawienia">
            <?php wp_nonce_field( 'parafia_mrozy_ustawienia' ); ?>
            <?php foreach ( parafia_mrozy_sekcje_ustawien() as $sekcja => $opis_sekcji ) : ?>
                <h2 class="title"><?php echo esc_html( $opis_sekcji[0] ); ?></h2>
                <?php if ( $opis_sekcji[1] ) : ?>
                    <p class="description"><?php echo esc_html( $opis_sekcji[1] ); ?></p>
                <?php endif; ?>
                <table class="form-table" role="presentation">
                    <?php
                    foreach ( $pola as $klucz => $pole ) :
                        if ( $pole[3] !== $sekcja ) {
                            continue;
                        }
                        $id      = 'pm-' . $klucz;
                        $nazwa   = 'ustawienia[' . $klucz . ']';
                        $wartosc = pm_opcja( $klucz );
                        ?>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $pole[0] ); ?></label></th>
                            <td>
                                <?php if ( 'wlacz' === $pole[1] || 'adwent' === $pole[1] ) : ?>
                                    <input type="hidden" name="<?php echo esc_attr( $nazwa ); ?>" value="0">
                                    <label><input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $nazwa ); ?>" value="1" <?php checked( '1', $wartosc ); ?>> Pokazuj</label>
                                    <?php if ( 'adwent' === $pole[1] && defined( 'PARAFIA_ADWENT_MENU' ) ) : ?>
                                        &nbsp;<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . PARAFIA_ADWENT_MENU ) ); ?>">Ustawienia Adwentu →</a>
                                    <?php endif; ?>
                                <?php elseif ( 'textarea' === $pole[1] || 'tekst_html' === $pole[1] ) : ?>
                                    <textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $nazwa ); ?>" rows="<?php echo 'tekst_html' === $pole[1] ? 6 : 5; ?>" class="large-text"><?php echo esc_textarea( $wartosc ); ?></textarea>
                                <?php elseif ( 'number' === $pole[1] ) : ?>
                                    <input type="number" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $nazwa ); ?>" value="<?php echo esc_attr( $wartosc ); ?>" min="1" max="24" class="small-text">
                                <?php else : ?>
                                    <input type="<?php echo esc_attr( in_array( $pole[1], [ 'email', 'url', 'date' ], true ) ? $pole[1] : 'text' ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $nazwa ); ?>" value="<?php echo esc_attr( $wartosc ); ?>" class="<?php echo 'date' === $pole[1] ? '' : ( 'url' === $pole[1] ? 'large-text' : 'regular-text' ); ?>">
                                <?php endif; ?>
                                <?php if ( $pole[4] ) : ?>
                                    <p class="description"><?php echo esc_html( $pole[4] ); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
            <?php submit_button( 'Zapisz ustawienia' ); ?>
        </form>
    </div>
    <?php
}

function parafia_mrozy_zapisz_ustawienia() {
    if ( ! current_user_can( 'parafia_ustawienia' ) ) {
        wp_die( 'Brak uprawnień.', 403 );
    }
    check_admin_referer( 'parafia_mrozy_ustawienia' );

    $wejscie = isset( $_POST['ustawienia'] ) && is_array( $_POST['ustawienia'] ) ? wp_unslash( $_POST['ustawienia'] ) : [];
    foreach ( parafia_mrozy_pola_ustawien() as $klucz => $pole ) {
        if ( ! array_key_exists( $klucz, $wejscie ) ) {
            continue;
        }
        $wartosc = parafia_mrozy_oczysc_pole( $pole, $wejscie[ $klucz ] );
        if ( 'adwent' === $pole[1] ) {
            // Ten sam przełącznik co w menu Adwent („Pokazuj widżet na stronie głównej”).
            if ( defined( 'PARAFIA_ADWENT_OPCJA' ) ) {
                $adwent = get_option( PARAFIA_ADWENT_OPCJA );
                $adwent = is_array( $adwent ) ? $adwent : ( function_exists( 'parafia_adwent_ustawienia' ) ? parafia_adwent_ustawienia() : [] );
                $adwent['wlaczony'] = (int) $wartosc;
                update_option( PARAFIA_ADWENT_OPCJA, $adwent, false );
            }
            continue;
        }
        set_theme_mod( $klucz, $wartosc );
    }

    wp_safe_redirect( admin_url( 'admin.php?page=' . PARAFIA_MROZY_USTAWIENIA_STRONA . '&zapisano=1' ) );
    exit;
}
add_action( 'admin_post_parafia_mrozy_zapisz_ustawienia', 'parafia_mrozy_zapisz_ustawienia' );

// Link do panelu w Customizerze (dawne sekcje „Dane parafii” i „Porządek Mszy” są teraz tutaj).
function parafia_mrozy_customizer_odsylacz( $wp_customize ) {
    $wp_customize->add_section( 'parafia_panel_info', [
        'title'       => 'Dane parafii i Msze',
        'priority'    => 30,
        'description' => 'Dane parafii, porządek Mszy, kancelaria i sekcje strony głównej są teraz w kokpicie: Parafia → Ustawienia strony.',
    ] );
    $wp_customize->add_setting( 'parafia_panel_info_link', [ 'sanitize_callback' => '__return_empty_string' ] );
    $wp_customize->add_control( 'parafia_panel_info_link', [
        'section'     => 'parafia_panel_info',
        'type'        => 'hidden',
        'description' => '<a class="button" href="' . esc_url( admin_url( 'admin.php?page=' . PARAFIA_MROZY_USTAWIENIA_STRONA ) ) . '">Otwórz ustawienia strony</a>',
    ] );
}
add_action( 'customize_register', 'parafia_mrozy_customizer_odsylacz' );
