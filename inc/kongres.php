<?php
/**
 * Kongres Eucharystyczny Diecezji Warszawsko-Praskiej (29.11.2026 – 21.11.2027).
 *
 * Sekcja na stronie głównej (pod ogłoszeniami): krótko o Kongresie
 * oraz patron bieżącego miesiąca ze zdjęciem i odnośnikami do kongresdwp.pl.
 * Pokazuje się sama od pierwszego do ostatniego miesiąca z listy patronów;
 * można ją ukryć w panelu Parafia → Ustawienia strony → sekcje.
 *
 * Dane patronów, opisy i zdjęcia pochodzą z oficjalnej strony Kongresu:
 * https://www.kongresdwp.pl/index.php?display=patroni
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PARAFIA_KONGRES_URL', 'https://www.kongresdwp.pl/' );
define( 'PARAFIA_KONGRES_PATRONI_URL', 'https://www.kongresdwp.pl/index.php?display=patroni' );

/**
 * Patroni miesięcy: 'RRRR-MM' => [ imię, hasło, slug na kongresdwp.pl ].
 * Zdjęcie: https://www.kongresdwp.pl/assets/kongres/patroni/<slug>.webp
 */
function parafia_kongres_patroni() {
    return [
        '2026-11' => [ 'Bł. ks. Jerzy Popiełuszko',              'Świadek dobra silniejszego niż zło',         'jerzy' ],
        '2026-12' => [ 'Św. Joanna Beretta Molla',               'Miłość, która staje się darem',              'joanna' ],
        '2027-01' => [ 'Św. Tomasz z Akwinu',                    'Mistrz teologii i adoracji',                 'tomasz' ],
        '2027-02' => [ 'Św. John Henry Newman',                  'Serce mówi do serca',                        'newman' ],
        '2027-03' => [ 'Św. Faustyna Kowalska',                  'Życie z Jezusem w Eucharystii',              'faustyna' ],
        '2027-04' => [ 'Św. Jan Paweł II',                       'Papież eucharystycznego zachwytu',           'jan-pawel' ],
        '2027-05' => [ 'Bł. Stefan kard. Wyszyński',             'Niezłomny Pasterz Kościoła i Narodu',        'wyszynski' ],
        '2027-06' => [ 'Bł. Rodzina Ulmów',                      'Dom otwarty na bliźniego',                   'ulmowie' ],
        '2027-07' => [ 'Bł. Maria i Luigi Beltrame Quattrocchi', 'Miłość karmiona Eucharystią',                'quattrocchi' ],
        '2027-08' => [ 'Św. Pius X, papież',                     'Papież reformator',                          'pius' ],
        '2027-09' => [ 'Bł. ks. Ignacy Kłopotowski',             'Od Eucharystii do służby człowiekowi',       'ignacy' ],
        '2027-10' => [ 'Św. Carlo Acutis',                       '„Eucharystia jest moją autostradą do nieba”', 'carlo' ],
        '2027-11' => [ 'Bł. Fulton Sheen',                       'Głos Ewangelii zrodzony z adoracji',         'fulton' ],
    ];
}

/** Nazwa miesiąca w mianowniku. */
function parafia_kongres_miesiac( $nr ) {
    $m = [ 1 => 'Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień' ];
    return $m[ (int) $nr ] ?? '';
}

/**
 * Patron na dany miesiąc lub null. Podgląd innego miesiąca dla redaktorów:
 * /?kongres_podglad=RRRR-MM
 */
function parafia_kongres_klucz_miesiaca() {
    if ( isset( $_GET['kongres_podglad'] ) && current_user_can( 'edit_posts' ) ) {
        $p = sanitize_text_field( wp_unslash( $_GET['kongres_podglad'] ) );
        if ( preg_match( '/^\d{4}-\d{2}$/', $p ) ) return $p;
    }
    return current_datetime()->format( 'Y-m' );
}

function parafia_kongres_dane( $klucz ) {
    $patroni = parafia_kongres_patroni();
    if ( ! isset( $patroni[ $klucz ] ) ) return null;
    list( $imie, $haslo, $slug ) = $patroni[ $klucz ];
    list( $rok, $mies ) = explode( '-', $klucz );
    return [
        'imie'    => $imie,
        'haslo'   => $haslo,
        'url'     => PARAFIA_KONGRES_PATRONI_URL . '&patron=' . rawurlencode( $slug ),
        'foto'    => 'https://www.kongresdwp.pl/assets/kongres/patroni/' . rawurlencode( $slug ) . '.webp',
        'miesiac' => parafia_kongres_miesiac( $mies ) . ' ' . $rok,
    ];
}

/** Czy sekcja ma się dziś pokazać. */
function parafia_kongres_aktywny() {
    return function_exists( 'pm_sekcja' ) && pm_sekcja( 'kongres' )
        && null !== parafia_kongres_dane( parafia_kongres_klucz_miesiaca() );
}

function parafia_kongres_zasoby() {
    if ( ! is_front_page() || ! parafia_kongres_aktywny() ) return;
    wp_enqueue_style(
        'parafia-kongres',
        get_template_directory_uri() . '/css/kongres.css',
        [ 'parafia-mrozy-style' ],
        PARAFIA_MROZY_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'parafia_kongres_zasoby', 20 );

/** Wyświetla sekcję Kongresu (wywoływane w front-page.php). */
function parafia_kongres_sekcja() {
    if ( ! parafia_kongres_aktywny() ) return;

    $klucz  = parafia_kongres_klucz_miesiaca();
    $patron = parafia_kongres_dane( $klucz );
    $nast   = parafia_kongres_dane( ( new DateTimeImmutable( $klucz . '-01' ) )->modify( '+1 month' )->format( 'Y-m' ) );
    ?>
    <section class="section-wrapper kongres-section" aria-labelledby="kongres-tytul">
        <div class="container">
            <h2 class="section-heading" id="kongres-tytul">Kongres Eucharystyczny</h2>

            <div class="kongres-grid">

                <div class="kongres-o">
                    <p class="kongres-diecezja">Diecezja Warszawsko-Praska · 29 listopada 2026 – 21 listopada 2027</p>
                    <p class="kongres-haslo">„Dał się poznać przy łamaniu chleba”<span>(Łk 24,35)</span></p>

                    <h3>Czym jest Kongres Eucharystyczny?</h3>
                    <p>To rok, w którym cała nasza diecezja – parafie, wspólnoty, rodziny i szkoły – wspólnie wraca do źródła: do Jezusa obecnego w Najświętszym Sakramencie. Kongres ma pomóc nam odnowić wiarę w realną obecność Chrystusa w Eucharystii i przeżywać Mszę Świętą bardziej świadomie.</p>
                    <p>Na drogę Kongresu składają się modlitwa i adoracja (z kaplicą adoracji w każdym dekanacie), katechezy eucharystyczne, świadectwa wiary, procesje oraz sztafeta adoracyjna. Każdemu miesiącowi towarzyszy patron – święty lub błogosławiony, którego życie wyrastało z Eucharystii.</p>

                    <div class="kongres-przyciski">
                        <a class="kongres-btn" href="<?php echo esc_url( PARAFIA_KONGRES_URL ); ?>" target="_blank" rel="noopener">Strona Kongresu »</a>
                        <a class="kongres-link" href="<?php echo esc_url( PARAFIA_KONGRES_PATRONI_URL ); ?>" target="_blank" rel="noopener">Wszyscy patroni Kongresu</a>
                    </div>
                </div>

                <article class="kongres-patron">
                    <a class="kongres-patron-foto" href="<?php echo esc_url( $patron['url'] ); ?>" target="_blank" rel="noopener" tabindex="-1" aria-hidden="true">
                        <img src="<?php echo esc_url( $patron['foto'] ); ?>" alt="" width="360" height="450" loading="lazy" decoding="async">
                    </a>
                    <div class="kongres-patron-tekst">
                        <span class="kongres-patron-etykieta">Patron miesiąca · <?php echo esc_html( $patron['miesiac'] ); ?></span>
                        <h3 class="kongres-patron-imie">
                            <a href="<?php echo esc_url( $patron['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $patron['imie'] ); ?></a>
                        </h3>
                        <p class="kongres-patron-haslo"><?php echo esc_html( $patron['haslo'] ); ?></p>
                        <a class="kongres-link" href="<?php echo esc_url( $patron['url'] ); ?>" target="_blank" rel="noopener">Poznaj patrona »</a>
                        <?php if ( $nast ) : ?>
                        <p class="kongres-patron-nastepny">W kolejnym miesiącu: <strong><?php echo esc_html( $nast['imie'] ); ?></strong></p>
                        <?php endif; ?>
                    </div>
                </article>

            </div>
        </div>
    </section>
    <?php
}
