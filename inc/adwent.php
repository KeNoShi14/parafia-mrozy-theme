<?php
/**
 * Widżet adwentowy „Droga do Betlejem” na stronie głównej
 * oraz strona edycji treści w panelu (menu „Adwent”).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/adwent-dane.php';

define( 'PARAFIA_ADWENT_OPCJA', 'parafia_mrozy_adwent' );
define( 'PARAFIA_ADWENT_MENU', 'parafia-adwent' );
define( 'PARAFIA_ADWENT_UPRAWNIENIE', 'edit_posts' );

/* ═══════════════════════════════════════════
   USTAWIENIA
   ═══════════════════════════════════════════ */

function parafia_adwent_ustawienia() {
    $domyslne = parafia_adwent_domyslne();
    $zapisane = get_option( PARAFIA_ADWENT_OPCJA );
    if ( ! is_array( $zapisane ) ) {
        return $domyslne;
    }
    $u = array_merge( $domyslne, $zapisane );
    foreach ( [ 'swieta' ] as $klucz ) {
        $u[ $klucz ] = array_merge( $domyslne[ $klucz ], is_array( $zapisane[ $klucz ] ?? null ) ? $zapisane[ $klucz ] : [] );
    }
    return $u;
}

function parafia_adwent_pola_dnia() {
    return [ 'tytul' => '', 'ewangelia' => '', 'cytat' => '', 'opis' => '', 'slad' => '', 'km' => 0 ];
}

function parafia_adwent_terminy_konca() {
    return [
        '12-25' => 'Boże Narodzenie (25 grudnia)',
        '01-01' => 'Oktawa Bożego Narodzenia (1 stycznia)',
        '01-06' => 'Objawienie Pańskie (6 stycznia)',
        '02-02' => 'Ofiarowanie Pańskie (2 lutego)',
    ];
}

/* ═══════════════════════════════════════════
   DATY
   ═══════════════════════════════════════════ */

/** Pierwsza niedziela Adwentu = 4 niedziele przed Bożym Narodzeniem. */
function parafia_adwent_pierwsza_niedziela( $rok ) {
    $bn  = new DateTimeImmutable( sprintf( '%04d-12-25', $rok ), wp_timezone() );
    $dow = (int) $bn->format( 'w' );
    return $bn->modify( '-' . ( $dow === 0 ? 7 : $dow ) . ' days' )->modify( '-21 days' );
}

/** Rok „sezonu” — w styczniu i lutym to jeszcze Adwent/Boże Narodzenie z poprzedniego roku. */
function parafia_adwent_rok_sezonu( DateTimeImmutable $data ) {
    $rok = (int) $data->format( 'Y' );
    return ( (int) $data->format( 'n' ) <= 2 ) ? $rok - 1 : $rok;
}

function parafia_adwent_start( $rok, array $u ) {
    if ( ! empty( $u['start'] ) && preg_match( '/^(\d{4})-\d{2}-\d{2}$/', $u['start'], $m ) && (int) $m[1] === (int) $rok ) {
        return new DateTimeImmutable( $u['start'], wp_timezone() );
    }
    return parafia_adwent_pierwsza_niedziela( $rok );
}

/**
 * Stan widżetu dla podanego dnia.
 * Zwraca null, gdy widżet nie powinien być widoczny (poza okresem).
 */
function parafia_adwent_stan( DateTimeImmutable $dzis, array $u, $wymus = false ) {
    $dzis  = $dzis->setTime( 0, 0 );
    $rok   = parafia_adwent_rok_sezonu( $dzis );
    $start = parafia_adwent_start( $rok, $u );
    $bn    = new DateTimeImmutable( sprintf( '%04d-12-25', $rok ), wp_timezone() );

    $koniec_kod = isset( parafia_adwent_terminy_konca()[ $u['koniec'] ] ) ? $u['koniec'] : '01-06';
    $rok_konca  = ( $koniec_kod === '12-25' ) ? $rok : $rok + 1;
    $koniec     = new DateTimeImmutable( $rok_konca . '-' . $koniec_kod, wp_timezone() );

    $n = (int) $start->diff( $bn )->format( '%a' );

    if ( $dzis < $start ) {
        if ( ! $wymus ) return null;
        $dzis = $start;
    }
    if ( $dzis > $koniec && ! $wymus ) return null;

    $swieta = $dzis >= $bn;
    $i      = $swieta ? $n : (int) $start->diff( $dzis )->format( '%a' );

    return [
        'rok'    => $rok,
        'start'  => $start,
        'bn'     => $bn,
        'n'      => $n,
        'i'      => $i,
        'swieta' => $swieta,
        'data'   => $dzis,
    ];
}

function parafia_adwent_data_slownie( DateTimeImmutable $d, $z_dniem_tygodnia = true ) {
    $dni      = [ 'Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota' ];
    $miesiace = [ 1 => 'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia' ];
    $tekst    = $d->format( 'j' ) . ' ' . $miesiace[ (int) $d->format( 'n' ) ];
    return $z_dniem_tygodnia ? $dni[ (int) $d->format( 'w' ) ] . ', ' . $tekst : $tekst;
}

/* ═══════════════════════════════════════════
   STRONA GŁÓWNA — WIDOCZNOŚĆ I ZASOBY
   ═══════════════════════════════════════════ */

function parafia_adwent_podglad_z_adresu() {
    if ( ! isset( $_GET['adwent_podglad'] ) || ! current_user_can( PARAFIA_ADWENT_UPRAWNIENIE ) ) return null;
    $wartosc = sanitize_text_field( wp_unslash( $_GET['adwent_podglad'] ) );
    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $wartosc ) ) return null;
    try {
        return new DateTimeImmutable( $wartosc, wp_timezone() );
    } catch ( Exception $e ) {
        return null;
    }
}

/** Stan do wyświetlenia na stronie głównej (null = nic nie pokazujemy). */
function parafia_adwent_stan_strony() {
    static $wynik = false;
    if ( $wynik !== false ) return $wynik;

    $u       = parafia_adwent_ustawienia();
    $podglad = parafia_adwent_podglad_z_adresu();

    if ( $podglad ) {
        $wynik = [ parafia_adwent_stan( $podglad, $u, true ), $u, true ];
        return $wynik;
    }
    if ( empty( $u['wlaczony'] ) || ( ! empty( $u['tryb_testowy'] ) && ! current_user_can( PARAFIA_ADWENT_UPRAWNIENIE ) ) ) {
        $wynik = null;
        return $wynik;
    }
    $stan  = parafia_adwent_stan( current_datetime(), $u );
    $wynik = $stan ? [ $stan, $u, false ] : null;
    return $wynik;
}

function parafia_adwent_zasoby() {
    if ( ! is_front_page() || ! parafia_adwent_stan_strony() ) return;
    wp_enqueue_style(
        'parafia-adwent-fonty',
        'https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=EB+Garamond:ital,wght@0,500;1,500&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'parafia-adwent',
        get_template_directory_uri() . '/css/adwent.css',
        [ 'parafia-mrozy-style' ],
        PARAFIA_MROZY_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'parafia_adwent_zasoby' );

/* ═══════════════════════════════════════════
   SCENA (SVG)
   ═══════════════════════════════════════════ */

function parafia_adwent_n( $v ) {
    return rtrim( rtrim( number_format( (float) $v, 1, '.', '' ), '0' ), '.' );
}

function parafia_adwent_tr( $x, $y, $s = 1 ) {
    $t = 'translate(' . parafia_adwent_n( $x ) . ' ' . parafia_adwent_n( $y ) . ')';
    return ( $s != 1 ) ? $t . ' scale(' . parafia_adwent_n( $s ) . ')' : $t;
}

/**
 * @param string $wariant 'szeroka' | 'waska'
 * @param array  $d       dane sceny: postep, km_przebyte, km_przystankow, nazwy, swieta, opis
 */
function parafia_adwent_svg( $wariant, array $d ) {
    $L = ( $wariant === 'waska' )
        ? [ 'W' => 560, 'H' => 400, 'P' => [ [70,300], [190,322], [300,292], [400,272], [490,292] ], 'fs' => 15, 'lbl' => 30, 'ts' => 0.85, 'town' => 0.7, 'stable' => 0.9, 'stOff' => -10, 'gwiazdy' => 45 ]
        : [ 'W' => 1120, 'H' => 340, 'P' => [ [110,262], [380,282], [620,248], [830,228], [990,250] ], 'fs' => 14, 'lbl' => 26, 'ts' => 1, 'town' => 1, 'stable' => 1.1, 'stOff' => -20, 'gwiazdy' => 80 ];
    $W  = $L['W'];
    $H  = $L['H'];
    $P  = $L['P'];
    $id = 'adw-' . $wariant;
    $n  = 'parafia_adwent_n';

    // Gwiazdy (stałe ziarno — zawsze ten sam układ).
    $seed  = 11;
    $stars = '';
    for ( $k = 0; $k < $L['gwiazdy']; $k++ ) {
        $seed = ( $seed * 16807 ) % 2147483647; $x = $seed / 2147483647 * $W;
        $seed = ( $seed * 16807 ) % 2147483647; $y = $seed / 2147483647 * $H * 0.55;
        $seed = ( $seed * 16807 ) % 2147483647; $r = 0.6 + $seed / 2147483647 * 1.2;
        $stars .= 'M' . $n( $x - $r ) . ' ' . $n( $y ) . 'a' . $n( $r ) . ' ' . $n( $r ) . ' 0 1 0 ' . $n( 2 * $r ) . ' 0a' . $n( $r ) . ' ' . $n( $r ) . ' 0 1 0 ' . $n( -2 * $r ) . ' 0';
    }
    $f    = function ( $a, $b ) use ( $W, $H, $n ) { return $n( $W * $a ) . ' ' . $n( $H * $b ); };
    $far  = 'M' . $f( 0, .6 ) . ' C' . $f( .12, .46 ) . ' ' . $f( .28, .58 ) . ' ' . $f( .42, .52 ) . ' S' . $f( .72, .44 ) . ' ' . $f( 1, .56 ) . ' L' . $f( 1, 1 ) . ' L' . $f( 0, 1 ) . 'Z';
    $near = 'M' . $f( 0, .8 ) . ' C' . $f( .22, .88 ) . ' ' . $f( .48, .74 ) . ' ' . $f( .72, .78 ) . ' S' . $f( .92, .8 ) . ' ' . $f( 1, .79 ) . ' L' . $f( 1, 1 ) . ' L' . $f( 0, 1 ) . 'Z';

    // Pozycja na trasie według kilometrów.
    $kmSt = $d['km_przystankow'];
    $na_trasie = function ( $km ) use ( $kmSt, $P ) {
        $ost = count( $P ) - 1;
        for ( $i = 0; $i < $ost; $i++ ) {
            if ( $km <= $kmSt[ $i + 1 ] ) {
                $dl = max( 0.0001, $kmSt[ $i + 1 ] - $kmSt[ $i ] );
                $t  = max( 0, min( 1, ( $km - $kmSt[ $i ] ) / $dl ) );
                return [ $i, [ $P[ $i ][0] + ( $P[ $i + 1 ][0] - $P[ $i ][0] ) * $t, $P[ $i ][1] + ( $P[ $i + 1 ][1] - $P[ $i ][1] ) * $t ] ];
            }
        }
        return [ $ost - 1, $P[ $ost ] ];
    };
    list( $seg, $koniec ) = $na_trasie( $d['km_przebyte'] );
    $przebyta = array_slice( $P, 0, $seg + 1 );
    $przebyta[] = $koniec;
    $ostatni_odcinek = $kmSt[4] - $kmSt[3];
    list( , $pozycja ) = $na_trasie( min( $d['km_przebyte'], $kmSt[4] - 0.4 * $ostatni_odcinek ) );
    $punkty = function ( $tab ) use ( $n ) {
        return implode( ' ', array_map( function ( $p ) use ( $n ) { return $n( $p[0] ) . ',' . $n( $p[1] ); }, $tab ) );
    };

    $swieta   = $d['swieta'];
    $postep   = $d['postep'];
    $skalaGw  = $swieta ? 1.5 : 0.75 + 0.5 * $postep;
    $gwY      = $H * 0.17;
    $P0 = $P[0]; $P3 = $P[3]; $P4 = $P[4];
    $promien  = ( $P4[1] - 30 - 60 * $L['town'] - $gwY - 4 ) / $skalaGw;

    ob_start();
    ?>
<svg class="adw-scena adw-scena--<?php echo esc_attr( $wariant ); ?>" viewBox="0 0 <?php echo (int) $W; ?> <?php echo (int) $H; ?>" role="img" aria-label="<?php echo esc_attr( $d['opis'] ); ?>">
<defs>
<linearGradient id="<?php echo $id; ?>-niebo" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0c1124"/><stop offset="1" stop-color="#2b2f58"/></linearGradient>
<radialGradient id="<?php echo $id; ?>-blask"><stop offset="0" stop-color="#f6e3a1" stop-opacity="0.6"/><stop offset="1" stop-color="#f6e3a1" stop-opacity="0"/></radialGradient>
<radialGradient id="<?php echo $id; ?>-cieplo"><stop offset="0" stop-color="#f4c55a" stop-opacity="0.45"/><stop offset="1" stop-color="#f4c55a" stop-opacity="0"/></radialGradient>
</defs>
<rect width="<?php echo (int) $W; ?>" height="<?php echo (int) $H; ?>" fill="url(#<?php echo $id; ?>-niebo)"/>
<path class="adw-migotanie" d="<?php echo esc_attr( $stars ); ?>" fill="#f3ead2" opacity="0.75"/>
<g transform="<?php echo esc_attr( parafia_adwent_tr( $W * 0.07, $H * 0.16 ) ); ?>"><circle r="15" fill="#efe3c2"/><circle cx="7" cy="-5" r="13.5" fill="#0f1429"/></g>

<g transform="<?php echo esc_attr( parafia_adwent_tr( $P4[0] + 30, $gwY, $skalaGw ) ); ?>" opacity="<?php echo esc_attr( $n( $swieta ? 1 : 0.45 + 0.55 * $postep ) ); ?>">
<line x1="0" y1="12" x2="0" y2="<?php echo esc_attr( $n( $promien ) ); ?>" stroke="#f6e3a1" stroke-width="1.2" opacity="<?php echo $swieta ? '0.55' : '0.15'; ?>"/>
<circle r="30" fill="url(#<?php echo $id; ?>-blask)"/>
<path d="M0 -16 L3 -3 L16 0 L3 3 L0 16 L-3 3 L-16 0 L-3 -3 Z" fill="#fbecb8"/>
<path d="M0 -8 L1.5 -1.5 L8 0 L1.5 1.5 L0 8 L-1.5 1.5 L-8 0 L-1.5 -1.5 Z" fill="#ffffff" transform="rotate(45)"/>
</g>

<path d="<?php echo esc_attr( $far ); ?>" fill="#1e2542"/>

<g transform="<?php echo esc_attr( parafia_adwent_tr( $P3[0], $P3[1] - 16, $L['town'] ) ); ?>" fill="#2c3358">
<rect x="-50" y="-30" width="100" height="30"/><rect x="-58" y="-46" width="16" height="46"/><rect x="42" y="-46" width="16" height="46"/><rect x="-16" y="-62" width="32" height="32"/><path d="M-16 -62 L0 -74 L16 -62 Z"/>
<rect x="-46" y="-36" width="6" height="6"/><rect x="-32" y="-36" width="6" height="6"/><rect x="26" y="-36" width="6" height="6"/><rect x="12" y="-36" width="6" height="6"/>
<g fill="#e8c96d"><rect x="-3" y="-52" width="6" height="9"/><rect x="-53" y="-38" width="4" height="6"/><rect x="48" y="-38" width="4" height="6"/></g>
</g>
<g transform="<?php echo esc_attr( parafia_adwent_tr( $P0[0] - 10, $P0[1] - 6, $L['town'] ) ); ?>" fill="#323a62">
<rect x="-70" y="-30" width="26" height="30"/><rect x="-48" y="-44" width="24" height="44"/><rect x="-28" y="-26" width="24" height="26"/>
<g fill="#e8c96d"><rect x="-62" y="-20" width="5" height="6"/><rect x="-39" y="-34" width="5" height="7"/><rect x="-19" y="-16" width="4" height="6"/></g>
</g>
<g transform="<?php echo esc_attr( parafia_adwent_tr( $P4[0] + 10, $P4[1] - 30, $L['town'] ) ); ?>" fill="#323a62">
<rect x="-10" y="-34" width="28" height="34"/><rect x="14" y="-52" width="26" height="52"/><path d="M14 -52 Q27 -68 40 -52 Z"/><rect x="38" y="-28" width="30" height="28"/><rect x="64" y="-42" width="22" height="42"/>
<g fill="#e8c96d"><rect x="-2" y="-24" width="5" height="6"/><rect x="24" y="-40" width="5" height="7"/><rect x="48" y="-18" width="5" height="6"/><rect x="71" y="-32" width="4" height="6"/></g>
</g>

<path d="<?php echo esc_attr( $near ); ?>" fill="#141a2c"/>

<polyline points="<?php echo esc_attr( $punkty( $P ) ); ?>" fill="none" stroke="#e8c96d" stroke-opacity="0.4" stroke-width="2" stroke-dasharray="5 8" stroke-linecap="round"/>
<polyline points="<?php echo esc_attr( $punkty( $przebyta ) ); ?>" fill="none" stroke="#e8c96d" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>

<?php foreach ( $P as $i => $p ) :
    $minieta = $kmSt[ $i ] <= $d['km_przebyte']; ?>
<g transform="<?php echo esc_attr( parafia_adwent_tr( $p[0], $p[1] ) ); ?>"><circle r="5" fill="<?php echo $minieta ? '#e8c96d' : '#3a4166'; ?>" stroke="#e8c96d" stroke-width="1.5"/><text y="<?php echo (int) $L['lbl']; ?>" text-anchor="middle" font-size="<?php echo (int) $L['fs']; ?>" font-family="Montserrat, sans-serif" font-weight="600" fill="<?php echo $minieta ? '#efe9dc' : '#9599b0'; ?>"><?php echo esc_html( $d['nazwy'][ $i ] ); ?></text></g>
<?php endforeach; ?>

<?php if ( $swieta ) : ?>
<g transform="<?php echo esc_attr( parafia_adwent_tr( $P4[0] + $L['stOff'], $P4[1], $L['stable'] ) ); ?>">
<ellipse cx="0" cy="-34" rx="92" ry="64" fill="url(#<?php echo $id; ?>-cieplo)"/>
<path d="M-84 0 C-88 -38 -70 -86 -26 -102 C 14 -114 64 -92 80 -50 C 88 -28 88 -10 84 0 Z" fill="#3b3652"/>
<path d="M-62 -60 C-50 -84 -24 -96 4 -98 M30 -92 C50 -84 64 -68 70 -50 M-74 -24 C-72 -40 -66 -50 -60 -56" fill="none" stroke="#56506f" stroke-width="2" stroke-linecap="round"/>
<path d="M-58 0 C-60 -44 -36 -74 0 -76 C 36 -74 60 -44 58 0 Z" fill="#1e1929" stroke="#e8c96d" stroke-opacity="0.35" stroke-width="1"/>
<ellipse cx="0" cy="-24" rx="46" ry="30" fill="url(#<?php echo $id; ?>-cieplo)"/>
<ellipse cx="-70" cy="-3" rx="12" ry="5" fill="#46405e"/><ellipse cx="68" cy="-4" rx="14" ry="6" fill="#46405e"/>
<path d="M-34 -31 Q-26 -40 -18 -31 L-14 0 L-38 0 Z" fill="#8fa7cf"/>
<circle cx="-26" cy="-36" r="9" fill="none" stroke="#e8c96d" stroke-width="1"/><circle cx="-26" cy="-36" r="5.5" fill="#f1dfc0"/>
<path d="M-12 -10 L12 -10 L8 0 L-8 0 Z" fill="#8b6a3e"/>
<path d="M-11 -10 L-7 -14 L-2 -11 L3 -14 L8 -11 L11 -10 Z" fill="#d9b765"/>
<ellipse cx="1" cy="-14" rx="8" ry="4" fill="#f6ead0"/>
<circle cx="-5" cy="-15" r="6.5" fill="none" stroke="#f6e3a1" stroke-width="1.2"/><circle cx="-5" cy="-15" r="3.2" fill="#f1dfc0"/>
<path d="M22 -44 L34 -44 L39 0 L18 0 Z" fill="#c9a26a"/><path d="M22 -50 Q28 -60 34 -50 L34 -44 L22 -44 Z" fill="#9c7a4e"/>
<circle cx="28" cy="-52" r="9" fill="none" stroke="#e8c96d" stroke-width="1"/><circle cx="28" cy="-50" r="5.8" fill="#f1dfc0"/>
<line x1="42" y1="-58" x2="41" y2="0" stroke="#d8c3a0" stroke-width="2.2" stroke-linecap="round"/>
</g>
<?php else : ?>
<g transform="<?php echo esc_attr( parafia_adwent_tr( $pozycja[0] - 11 * $L['ts'], $pozycja[1], $L['ts'] ) ); ?>">
<g class="adw-krok">
<ellipse cx="10" cy="2" rx="42" ry="3" fill="#000" opacity="0.3"/>
<g fill="#a69c8d">
<rect x="-20" y="-16" width="3.5" height="16"/><rect x="-12" y="-16" width="3.5" height="16"/><rect x="2" y="-16" width="3.5" height="16"/><rect x="9" y="-16" width="3.5" height="16"/>
<ellipse cx="-6" cy="-22" rx="19" ry="9"/><path d="M8 -27 L17 -41 L24 -38 L15 -21 Z"/>
<ellipse cx="22" cy="-40" rx="7" ry="4.5" transform="rotate(25 22 -40)"/><path d="M17 -45 L16 -54 L21 -46 Z"/>
</g>
<path d="M-25 -24 Q-31 -18 -28 -10" stroke="#a69c8d" stroke-width="2" fill="none" stroke-linecap="round"/>
<path d="M-15 -26 Q-13 -46 -5 -47 Q3 -46 5 -26 Z" fill="#8fa7cf"/>
<path d="M-11.5 -47 Q-5 -59 1.5 -47 L2 -41 L-12 -41 Z" fill="#7891bd"/>
<circle cx="-5" cy="-51" r="8.5" fill="none" stroke="#e8c96d" stroke-width="1" opacity="0.85"/><circle cx="-5" cy="-50" r="5" fill="#f1dfc0"/>
<path d="M45 -33 Q34 -32 24 -37" stroke="#d8c3a0" stroke-width="0.9" fill="none"/>
<path d="M33 -40 L45 -40 L49 0 L30 0 Z" fill="#c9a26a"/><path d="M33 -46 Q39 -56 45 -46 L45 -40 L33 -40 Z" fill="#9c7a4e"/>
<circle cx="39" cy="-48" r="8.5" fill="none" stroke="#e8c96d" stroke-width="1" opacity="0.85"/><circle cx="39" cy="-46" r="5.6" fill="#f1dfc0"/>
<line x1="53" y1="-58" x2="51" y2="0" stroke="#d8c3a0" stroke-width="2.2" stroke-linecap="round"/>
<line x1="44" y1="-34" x2="51" y2="-30" stroke="#c9a26a" stroke-width="4" stroke-linecap="round"/>
</g>
</g>
<?php endif; ?>
</svg>
    <?php
    return ob_get_clean();
}

/* ═══════════════════════════════════════════
   SEKCJA NA STRONIE GŁÓWNEJ
   ═══════════════════════════════════════════ */

function parafia_mrozy_adwent_sekcja() {
    $dane = parafia_adwent_stan_strony();
    if ( ! $dane || ! $dane[0] ) return;
    list( $s, $u, $podglad ) = $dane;

    $n      = $s['n'];
    $i      = $s['i'];
    $swieta = $s['swieta'];
    $dni    = is_array( $u['dni'] ) ? $u['dni'] : [];

    // Kilometry.
    $km_razem = 0;
    $km_dzis_przebyte = 0;
    for ( $k = 0; $k < $n; $k++ ) {
        $km = isset( $dni[ $k ]['km'] ) ? (float) $dni[ $k ]['km'] : 0;
        $km_razem += $km;
        if ( $swieta || $k <= $i ) $km_dzis_przebyte += $km;
    }
    $km_razem   = max( 1, $km_razem );
    $pozostalo  = max( 0, $km_razem - $km_dzis_przebyte );

    // Przystanki: ostatni zawsze = cała długość drogi, kolejne rosnąco.
    $przystanki = array_values( (array) $u['przystanki'] );
    $nazwy = [];
    $kmSt  = [];
    $poprz = 0;
    for ( $k = 0; $k < 5; $k++ ) {
        $nazwy[] = $przystanki[ $k ]['nazwa'] ?? '';
        $km      = ( $k === 0 ) ? 0 : ( ( $k === 4 ) ? $km_razem : (float) ( $przystanki[ $k ]['km'] ?? 0 ) );
        $km      = min( $km_razem, max( $poprz, $km ) );
        $kmSt[]  = $km;
        $poprz   = $km;
    }

    $dzien = $swieta ? $u['swieta'] : array_merge( parafia_adwent_pola_dnia(), (array) ( $dni[ $i ] ?? [] ) );

    $tydzien  = $swieta ? $u['tytul_swieta'] : ( $u['tygodnie'][ min( 3, intdiv( $i, 7 ) ) ] ?? '' );
    $data_txt = parafia_adwent_data_slownie( $s['data'] ) . ( $swieta ? '' : ' • Dzień ' . ( $i + 1 ) );
    $zostalo  = $n - $i;
    $swiece   = $swieta ? 4 : min( 4, intdiv( $i, 7 ) + 1 );
    $gaudete  = ! $swieta && $i === 14;

    $opis_sceny = $swieta
        ? 'Święta Rodzina — Maryja, Józef i Dzieciątko Jezus w grocie betlejemskiej'
        : 'Maryja na osiołku i Józef w drodze do Betlejem — ' . $dzien['tytul'];
    $scena = [
        'postep'         => $swieta ? 1 : ( $n ? $i / $n : 0 ),
        'km_przebyte'    => $km_dzis_przebyte,
        'km_przystankow' => $kmSt,
        'nazwy'          => $nazwy,
        'swieta'         => $swieta,
        'opis'           => $opis_sceny,
    ];

    $fmt_km = function ( $v ) { return str_replace( '.', ',', parafia_adwent_n( $v ) ) . ' km'; };
    $link   = $u['przycisk_link'];
    if ( $link && strpos( $link, '/' ) === 0 ) $link = home_url( $link );
    ?>
<section class="adwent-sekcja" aria-labelledby="adwent-naglowek">
    <div class="container">
        <?php if ( $podglad ) : ?>
        <p class="adw-podglad">Podgląd dla administratora: <?php echo esc_html( parafia_adwent_data_slownie( $s['data'] ) . ' ' . $s['data']->format( 'Y' ) ); ?>. Odwiedzający zobaczą dzisiejszy dzień.</p>
        <?php elseif ( ! empty( $u['tryb_testowy'] ) ) : ?>
        <p class="adw-podglad">Tryb testowy — tę sekcję widzą tylko zalogowani redaktorzy.</p>
        <?php endif; ?>

        <header class="adw-naglowek">
            <?php if ( $u['naglowek_male'] ) : ?><p class="adw-naglowek-male"><?php echo esc_html( str_replace( '{rok}', $s['rok'], $u['naglowek_male'] ) ); ?></p><?php endif; ?>
            <h2 id="adwent-naglowek" class="adw-tytul"><?php echo esc_html( $u['naglowek'] ); ?></h2>
            <span class="adw-kreska" aria-hidden="true"></span>
            <?php if ( $u['wstep'] ) : ?><p class="adw-wstep"><?php echo esc_html( $u['wstep'] ); ?></p><?php endif; ?>
        </header>

        <div class="adw-karta">
            <?php echo parafia_adwent_svg( 'szeroka', $scena ); // phpcs:ignore ?>
            <?php echo parafia_adwent_svg( 'waska', $scena ); // phpcs:ignore ?>

            <div class="adw-tresc">
                <article class="adw-dzien">
                    <?php if ( $tydzien ) : ?><span class="adw-tydzien"><?php echo esc_html( $tydzien ); ?></span><?php endif; ?>
                    <span class="adw-data"><?php echo esc_html( $data_txt ); ?></span>
                    <?php if ( $dzien['tytul'] ) : ?><h3 class="adw-dzien-tytul"><?php echo esc_html( $dzien['tytul'] ); ?></h3><?php endif; ?>
                    <?php if ( $dzien['ewangelia'] ) : ?><span class="adw-ewangelia"><?php echo esc_html( $dzien['ewangelia'] ); ?></span><?php endif; ?>
                    <?php if ( $dzien['cytat'] ) : ?><p class="adw-cytat"><?php echo esc_html( $dzien['cytat'] ); ?></p><?php endif; ?>
                    <?php if ( $dzien['opis'] ) : ?><p class="adw-opis"><?php echo nl2br( esc_html( $dzien['opis'] ) ); ?></p><?php endif; ?>

                    <?php if ( $dzien['slad'] ) : ?>
                    <div class="adw-ramka">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5V5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2"/><path d="M4 19.5A2 2 0 0 0 6 21.5h13"/><path d="M9 7h6"/></svg>
                        <div>
                            <strong class="adw-ramka-tytul">Ślad historii</strong>
                            <p><?php echo nl2br( esc_html( $dzien['slad'] ) ); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ( $gaudete && $u['gaudete_tekst'] ) : ?>
                    <div class="adw-ramka adw-ramka--gaudete">
                        <svg width="14" height="22" viewBox="0 0 14 22" aria-hidden="true"><path d="M7 0 C9.5 4 11 6 11 8.5 A4 4 0 0 1 3 8.5 C3 6 4.5 4 7 0 Z" fill="#f4c55a"/><rect x="4" y="12" width="6" height="10" rx="1" fill="#e08fb0"/></svg>
                        <div>
                            <strong class="adw-ramka-tytul"><?php echo esc_html( $u['gaudete_tytul'] ); ?></strong>
                            <p><?php echo wp_kses( $u['gaudete_tekst'], [ 'em' => [], 'strong' => [], 'br' => [] ] ); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ( $u['przycisk_tekst'] && $link ) : ?>
                    <a class="adw-przycisk" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $u['przycisk_tekst'] ); ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg></a>
                    <?php endif; ?>
                </article>

                <aside class="adw-bok" aria-label="Postęp wędrówki">
                    <div class="adw-licznik">
                        <span class="adw-licznik-liczba"><?php echo $swieta ? 'Dziś' : esc_html( $zostalo . ' ' . ( $zostalo === 1 ? 'dzień' : 'dni' ) ); ?></span>
                        <span class="adw-etykieta"><?php echo $swieta ? 'Boże Narodzenie' : 'do Bożego Narodzenia'; ?></span>
                    </div>

                    <div class="adw-km">
                        <div class="adw-km-siatka">
                            <div class="adw-km-pole">
                                <span class="adw-etykieta"><?php echo $swieta ? 'Cała droga' : 'Dziś'; ?></span>
                                <strong><?php echo esc_html( $fmt_km( $swieta ? $km_razem : $dzien['km'] ) ); ?></strong>
                            </div>
                            <div class="adw-km-pole adw-km-pole--zloto">
                                <span class="adw-etykieta">Pozostało</span>
                                <strong><?php echo $pozostalo > 0 ? esc_html( $fmt_km( $pozostalo ) ) : 'U celu'; ?></strong>
                            </div>
                        </div>
                        <div class="adw-pasek" role="progressbar" aria-label="Przebyta droga" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( parafia_adwent_n( $km_razem ) ); ?>" aria-valuenow="<?php echo esc_attr( parafia_adwent_n( $km_dzis_przebyte ) ); ?>">
                            <span style="width: <?php echo esc_attr( parafia_adwent_n( $km_dzis_przebyte / $km_razem * 100 ) ); ?>%"></span>
                        </div>
                        <span class="adw-km-podpis">Przebyto <?php echo esc_html( $fmt_km( $km_dzis_przebyte ) ); ?> z <?php echo esc_html( $fmt_km( $km_razem ) ); ?></span>
                    </div>

                    <div class="adw-wieniec">
                        <span class="adw-etykieta">Wieniec adwentowy</span>
                        <div class="adw-swiece" aria-hidden="true">
                            <?php for ( $k = 0; $k < 4; $k++ ) :
                                $pali = $k < $swiece; ?>
                            <div class="adw-swieca<?php echo $k === 2 ? ' adw-swieca--rozowa' : ''; ?><?php echo $pali ? ' adw-swieca--pali' : ''; ?>" style="--h: <?php echo 44 + 6 * $k; ?>px">
                                <svg class="adw-plomien" width="12" height="20" viewBox="0 0 12 20"><path d="M6 0 C9 6 12 9 12 13 A6 6 0 0 1 0 13 C0 9 3 6 6 0 Z" fill="#f4c55a"/><path d="M6 8 C7.5 11 9 12 9 14 A3 3 0 0 1 3 14 C3 12 4.5 11 6 8 Z" fill="#fff1c9"/></svg>
                                <span class="adw-wosk"></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                        <span class="adw-galazki" aria-hidden="true"></span>
                        <div class="adw-swiece-nr" aria-hidden="true"><span>I</span><span>II</span><span>III</span><span>IV</span></div>
                        <span class="adw-wieniec-podpis"><?php
                            echo $swieta
                                ? 'Wszystkie świece płoną — Chrystus się narodził'
                                : esc_html( 'Płonie ' . $swiece . ' z 4 świec' . ( $swiece >= 3 ? ' · różowa to świeca Gaudete' : '' ) );
                        ?></span>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>
    <?php
}

/* ═══════════════════════════════════════════
   PANEL — STRONA EDYCJI
   ═══════════════════════════════════════════ */

function parafia_adwent_menu() {
    add_menu_page(
        'Adwent — Droga do Betlejem',
        'Adwent',
        PARAFIA_ADWENT_UPRAWNIENIE,
        PARAFIA_ADWENT_MENU,
        'parafia_adwent_strona_panelu',
        'dashicons-star-filled',
        7
    );
}
add_action( 'admin_menu', 'parafia_adwent_menu' );

function parafia_adwent_pole_tekst( $nazwa, $wartosc, $etykieta, $klasa = 'large-text' ) {
    $id = 'adw-' . md5( $nazwa );
    printf(
        '<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="text" id="%1$s" name="%3$s" value="%4$s" class="%5$s"></p>',
        esc_attr( $id ), esc_html( $etykieta ), esc_attr( $nazwa ), esc_attr( $wartosc ), esc_attr( $klasa )
    );
}

function parafia_adwent_pole_obszar( $nazwa, $wartosc, $etykieta, $wiersze = 4 ) {
    $id = 'adw-' . md5( $nazwa );
    printf(
        '<p><label for="%1$s"><strong>%2$s</strong></label><br><textarea id="%1$s" name="%3$s" rows="%4$d" class="large-text">%5$s</textarea></p>',
        esc_attr( $id ), esc_html( $etykieta ), esc_attr( $nazwa ), (int) $wiersze, esc_textarea( $wartosc )
    );
}

function parafia_adwent_strona_panelu() {
    if ( ! current_user_can( PARAFIA_ADWENT_UPRAWNIENIE ) ) return;

    $u     = parafia_adwent_ustawienia();
    $rok   = parafia_adwent_rok_sezonu( current_datetime() );
    $start = parafia_adwent_start( $rok, $u );
    $bn    = new DateTimeImmutable( $rok . '-12-25', wp_timezone() );
    $n     = (int) $start->diff( $bn )->format( '%a' );
    $dni   = is_array( $u['dni'] ) ? $u['dni'] : [];
    $teraz = parafia_adwent_stan( current_datetime(), $u );
    $km_razem = 0;
    for ( $k = 0; $k < $n; $k++ ) $km_razem += (float) ( $dni[ $k ]['km'] ?? 0 );
    $podglad_url = function ( DateTimeImmutable $d ) {
        return add_query_arg( 'adwent_podglad', $d->format( 'Y-m-d' ), home_url( '/' ) ) . '#adwent-naglowek';
    };
    ?>
<div class="wrap parafia-adwent-panel">
    <h1>Adwent — Droga do Betlejem</h1>

    <?php if ( isset( $_GET['zapisano'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p>Zmiany zostały zapisane.</p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['przywrocono'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p>Przywrócono domyślne treści.</p></div>
    <?php endif; ?>

    <div class="notice notice-info inline"><p>
        Adwent <?php echo (int) $rok; ?>: od <strong><?php echo esc_html( parafia_adwent_data_slownie( $start ) ); ?></strong>
        do Wigilii — <strong><?php echo (int) $n; ?> dni</strong> drogi, łącznie <strong><?php echo esc_html( parafia_adwent_n( $km_razem ) ); ?> km</strong>.
        <?php if ( empty( $u['wlaczony'] ) ) : ?>
            Widżet jest <strong>wyłączony</strong>.
        <?php elseif ( $teraz ) : ?>
            Widżet jest teraz <strong>widoczny</strong> na stronie głównej<?php echo ! empty( $u['tryb_testowy'] ) ? ' (tylko dla redaktorów — tryb testowy)' : ''; ?>.
        <?php else : ?>
            Widżet pojawi się na stronie głównej automatycznie <?php echo esc_html( parafia_adwent_data_slownie( $start, false ) ); ?>.
        <?php endif; ?>
        <a href="<?php echo esc_url( $podglad_url( $start ) ); ?>" target="_blank" rel="noopener">Zobacz podgląd pierwszego dnia</a>
    </p></div>

    <style>
        .parafia-adwent-panel .adw-blok { background: #fff; border: 1px solid #dcdcde; border-radius: 6px; padding: 4px 20px 12px; margin: 18px 0; max-width: 980px; }
        .parafia-adwent-panel details.adw-dzien-edycja { background: #fff; border: 1px solid #dcdcde; border-radius: 6px; margin: 8px 0; max-width: 980px; }
        .parafia-adwent-panel details.adw-dzien-edycja > summary { cursor: pointer; padding: 12px 16px; font-weight: 600; display: flex; gap: 12px; flex-wrap: wrap; align-items: baseline; }
        .parafia-adwent-panel details.adw-dzien-edycja > summary span { font-weight: 400; color: #50575e; }
        .parafia-adwent-panel details.adw-dzien-edycja[open] > summary { border-bottom: 1px solid #dcdcde; }
        .parafia-adwent-panel .adw-dzien-pola { padding: 4px 16px 12px; }
        .parafia-adwent-panel .adw-siatka { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 0 20px; }
        .parafia-adwent-panel h2.adw-tydzien-naglowek { margin: 28px 0 6px; font-size: 1.15em; }
        .parafia-adwent-panel .adw-pusty { color: #b32d2e; font-weight: 400; }
    </style>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="parafia_adwent_zapisz">
        <input type="hidden" name="adw_rok" value="<?php echo (int) $rok; ?>">
        <?php wp_nonce_field( 'parafia_adwent_zapisz', 'parafia_adwent_nonce' ); ?>

        <div class="adw-blok">
            <h2>Wyświetlanie</h2>
            <p><label><input type="checkbox" name="adw[wlaczony]" value="1" <?php checked( ! empty( $u['wlaczony'] ) ); ?>> Pokazuj widżet na stronie głównej w czasie Adwentu</label></p>
            <p><label><input type="checkbox" name="adw[tryb_testowy]" value="1" <?php checked( ! empty( $u['tryb_testowy'] ) ); ?>> Tryb testowy — widżet widzą tylko zalogowani redaktorzy</label></p>
            <div class="adw-siatka">
                <p><label for="adw-start"><strong>Pierwszy dzień</strong></label><br>
                    <input type="date" id="adw-start" name="adw[start]" value="<?php echo esc_attr( $u['start'] ); ?>"><br>
                    <span class="description">Puste = automatycznie I niedziela Adwentu (w tym roku <?php echo esc_html( parafia_adwent_data_slownie( parafia_adwent_pierwsza_niedziela( $rok ), false ) ); ?>).</span></p>
                <p><label for="adw-koniec"><strong>Pokazuj do</strong></label><br>
                    <select id="adw-koniec" name="adw[koniec]">
                        <?php foreach ( parafia_adwent_terminy_konca() as $kod => $nazwa ) : ?>
                            <option value="<?php echo esc_attr( $kod ); ?>" <?php selected( $u['koniec'], $kod ); ?>><?php echo esc_html( $nazwa ); ?></option>
                        <?php endforeach; ?>
                    </select><br>
                    <span class="description">Od 25 grudnia widżet pokazuje grotę i treść dnia Bożego Narodzenia.</span></p>
            </div>
        </div>

        <div class="adw-blok">
            <h2>Nagłówek sekcji i przycisk</h2>
            <?php
            parafia_adwent_pole_tekst( 'adw[naglowek_male]', $u['naglowek_male'], 'Mały napis nad tytułem ({rok} zamienia się na bieżący rok)', 'regular-text' );
            parafia_adwent_pole_tekst( 'adw[naglowek]', $u['naglowek'], 'Tytuł', 'regular-text' );
            parafia_adwent_pole_obszar( 'adw[wstep]', $u['wstep'], 'Wstęp', 2 );
            ?>
            <div class="adw-siatka">
                <?php
                parafia_adwent_pole_tekst( 'adw[przycisk_tekst]', $u['przycisk_tekst'], 'Tekst przycisku (puste = bez przycisku)', 'regular-text' );
                parafia_adwent_pole_tekst( 'adw[przycisk_link]', $u['przycisk_link'], 'Link przycisku', 'regular-text' );
                ?>
            </div>
        </div>

        <div class="adw-blok">
            <h2>Tygodnie i przystanki na trasie</h2>
            <?php
            $rzymskie = [ 'I', 'II', 'III', 'IV' ];
            for ( $k = 0; $k < 4; $k++ ) {
                parafia_adwent_pole_tekst( "adw[tygodnie][$k]", $u['tygodnie'][ $k ] ?? '', $rzymskie[ $k ] . ' tydzień — napis nad tytułem dnia' );
            }
            parafia_adwent_pole_tekst( 'adw[tytul_swieta]', $u['tytul_swieta'], 'Napis nad tytułem 25 grudnia', 'regular-text' );
            ?>
            <p class="description">Przystanki są oznaczone na mapie. Postać Maryi i Józefa przesuwa się według kilometrów wpisanych przy każdym dniu. Ostatni przystanek leży zawsze na końcu całej drogi.</p>
            <div class="adw-siatka">
                <?php for ( $k = 0; $k < 5; $k++ ) :
                    $p = $u['przystanki'][ $k ] ?? [ 'nazwa' => '', 'km' => 0 ]; ?>
                    <p>
                        <label for="adw-p-<?php echo $k; ?>"><strong>Przystanek <?php echo $k + 1; ?></strong></label><br>
                        <input type="text" id="adw-p-<?php echo $k; ?>" name="adw[przystanki][<?php echo $k; ?>][nazwa]" value="<?php echo esc_attr( $p['nazwa'] ); ?>" class="regular-text" style="width: 60%">
                        <?php if ( $k > 0 && $k < 4 ) : ?>
                            <input type="number" min="0" step="0.5" name="adw[przystanki][<?php echo $k; ?>][km]" value="<?php echo esc_attr( $p['km'] ); ?>" style="width: 80px" aria-label="Kilometr przystanku <?php echo $k + 1; ?>"> km
                        <?php else : ?>
                            <span class="description"><?php echo $k === 0 ? 'start, 0 km' : 'meta'; ?></span>
                        <?php endif; ?>
                    </p>
                <?php endfor; ?>
            </div>
        </div>

        <div class="adw-blok">
            <h2>Niedziela Gaudete</h2>
            <p class="description">Ramka pokazuje się pod opisem dnia w III niedzielę Adwentu.</p>
            <?php
            parafia_adwent_pole_tekst( 'adw[gaudete_tytul]', $u['gaudete_tytul'], 'Tytuł ramki', 'regular-text' );
            parafia_adwent_pole_obszar( 'adw[gaudete_tekst]', $u['gaudete_tekst'], 'Treść (można użyć <em>kursywy</em> i <strong>pogrubienia</strong>)', 4 );
            ?>
        </div>

        <h2>Dni Adwentu <?php echo (int) $rok; ?></h2>
        <p class="description">Kliknij dzień, aby go rozwinąć. „Pozostało” oblicza się samo z kilometrów.</p>
        <?php
        $przebyte = 0;
        for ( $k = 0; $k < $n; $k++ ) :
            if ( $k % 7 === 0 ) {
                echo '<h2 class="adw-tydzien-naglowek">' . esc_html( $u['tygodnie'][ min( 3, intdiv( $k, 7 ) ) ] ?? '' ) . '</h2>';
            }
            $d    = array_merge( parafia_adwent_pola_dnia(), (array) ( $dni[ $k ] ?? [] ) );
            $data = $start->modify( '+' . $k . ' days' );
            $przebyte += (float) $d['km'];
            ?>
            <details class="adw-dzien-edycja">
                <summary>
                    Dzień <?php echo $k + 1; ?> — <?php echo esc_html( parafia_adwent_data_slownie( $data ) ); ?>
                    <span><?php echo $d['tytul'] ? esc_html( $d['tytul'] ) : '<em class="adw-pusty">brak treści</em>'; ?></span>
                    <span><?php echo esc_html( parafia_adwent_n( $d['km'] ) ); ?> km · pozostało <?php echo esc_html( parafia_adwent_n( max( 0, $km_razem - $przebyte ) ) ); ?> km</span>
                </summary>
                <div class="adw-dzien-pola">
                    <?php
                    parafia_adwent_pole_tekst( "adw_dni[$k][tytul]", $d['tytul'], 'Tytuł dnia' );
                    parafia_adwent_pole_tekst( "adw_dni[$k][ewangelia]", $d['ewangelia'], 'Ewangelia dnia (sigla, święto)' );
                    parafia_adwent_pole_obszar( "adw_dni[$k][cytat]", $d['cytat'], 'Cytat z Ewangelii', 2 );
                    parafia_adwent_pole_obszar( "adw_dni[$k][opis]", $d['opis'], 'Opis', 5 );
                    parafia_adwent_pole_obszar( "adw_dni[$k][slad]", $d['slad'], 'Ślad historii (puste = bez ramki)', 3 );
                    ?>
                    <p>
                        <label for="adw-km-<?php echo $k; ?>"><strong>Kilometry tego dnia</strong></label><br>
                        <input type="number" id="adw-km-<?php echo $k; ?>" min="0" step="0.5" name="adw_dni[<?php echo $k; ?>][km]" value="<?php echo esc_attr( parafia_adwent_n( $d['km'] ) ); ?>" style="width: 90px">
                        &nbsp; <a href="<?php echo esc_url( $podglad_url( $data ) ); ?>" target="_blank" rel="noopener">Podgląd tego dnia na stronie</a>
                    </p>
                </div>
            </details>
        <?php endfor; ?>

        <h2 class="adw-tydzien-naglowek"><?php echo esc_html( $u['tytul_swieta'] ); ?> — 25 grudnia</h2>
        <details class="adw-dzien-edycja">
            <summary>25 grudnia <span><?php echo esc_html( $u['swieta']['tytul'] ); ?></span></summary>
            <div class="adw-dzien-pola">
                <?php
                parafia_adwent_pole_tekst( 'adw[swieta][tytul]', $u['swieta']['tytul'], 'Tytuł' );
                parafia_adwent_pole_tekst( 'adw[swieta][ewangelia]', $u['swieta']['ewangelia'], 'Ewangelia' );
                parafia_adwent_pole_obszar( 'adw[swieta][cytat]', $u['swieta']['cytat'], 'Cytat', 2 );
                parafia_adwent_pole_obszar( 'adw[swieta][opis]', $u['swieta']['opis'], 'Opis', 4 );
                parafia_adwent_pole_obszar( 'adw[swieta][slad]', $u['swieta']['slad'], 'Ślad historii (puste = bez ramki)', 3 );
                ?>
                <p><a href="<?php echo esc_url( $podglad_url( $bn ) ); ?>" target="_blank" rel="noopener">Podgląd 25 grudnia na stronie</a></p>
            </div>
        </details>

        <?php submit_button( 'Zapisz zmiany' ); ?>
    </form>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return window.confirm('Przywrócić domyślne treści? Wszystkie zmiany wprowadzone w tym panelu zostaną utracone.');">
        <input type="hidden" name="action" value="parafia_adwent_przywroc">
        <?php wp_nonce_field( 'parafia_adwent_przywroc', 'parafia_adwent_nonce' ); ?>
        <?php submit_button( 'Przywróć domyślne treści', 'secondary', 'submit', false ); ?>
    </form>
</div>
    <?php
}

/* ═══════════════════════════════════════════
   PANEL — ZAPIS
   ═══════════════════════════════════════════ */

function parafia_adwent_zapisz() {
    if ( ! current_user_can( PARAFIA_ADWENT_UPRAWNIENIE ) ) wp_die( 'Brak uprawnień.' );
    check_admin_referer( 'parafia_adwent_zapisz', 'parafia_adwent_nonce' );

    $stare = parafia_adwent_ustawienia();
    $we    = isset( $_POST['adw'] ) && is_array( $_POST['adw'] ) ? wp_unslash( $_POST['adw'] ) : [];
    $we_d  = isset( $_POST['adw_dni'] ) && is_array( $_POST['adw_dni'] ) ? wp_unslash( $_POST['adw_dni'] ) : [];
    $tekst = function ( $v ) { return sanitize_text_field( (string) $v ); };
    $dlugi = function ( $v ) { return sanitize_textarea_field( (string) $v ); };
    $km    = function ( $v ) { return max( 0, round( (float) str_replace( ',', '.', (string) $v ), 1 ) ); };

    $start = $tekst( $we['start'] ?? '' );
    if ( $start && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start ) ) $start = '';
    $koniec = $tekst( $we['koniec'] ?? '01-06' );
    if ( ! isset( parafia_adwent_terminy_konca()[ $koniec ] ) ) $koniec = '01-06';

    $nowe = [
        'wlaczony'       => empty( $we['wlaczony'] ) ? 0 : 1,
        'tryb_testowy'   => empty( $we['tryb_testowy'] ) ? 0 : 1,
        'start'          => $start,
        'koniec'         => $koniec,
        'naglowek_male'  => $tekst( $we['naglowek_male'] ?? '' ),
        'naglowek'       => $tekst( $we['naglowek'] ?? '' ),
        'wstep'          => $dlugi( $we['wstep'] ?? '' ),
        'przycisk_tekst' => $tekst( $we['przycisk_tekst'] ?? '' ),
        'przycisk_link'  => esc_url_raw( trim( (string) ( $we['przycisk_link'] ?? '' ) ) ),
        'tygodnie'       => [],
        'tytul_swieta'   => $tekst( $we['tytul_swieta'] ?? '' ),
        'przystanki'     => [],
        'gaudete_tytul'  => $tekst( $we['gaudete_tytul'] ?? '' ),
        'gaudete_tekst'  => wp_kses( (string) ( $we['gaudete_tekst'] ?? '' ), [ 'em' => [], 'strong' => [], 'br' => [] ] ),
        'swieta'         => [],
        'dni'            => is_array( $stare['dni'] ) ? $stare['dni'] : [],
    ];
    for ( $k = 0; $k < 4; $k++ ) {
        $nowe['tygodnie'][ $k ] = $tekst( $we['tygodnie'][ $k ] ?? '' );
    }
    for ( $k = 0; $k < 5; $k++ ) {
        $nowe['przystanki'][ $k ] = [
            'nazwa' => $tekst( $we['przystanki'][ $k ]['nazwa'] ?? '' ),
            'km'    => $km( $we['przystanki'][ $k ]['km'] ?? ( $stare['przystanki'][ $k ]['km'] ?? 0 ) ),
        ];
    }
    foreach ( [ 'tytul', 'ewangelia' ] as $pole ) $nowe['swieta'][ $pole ] = $tekst( $we['swieta'][ $pole ] ?? '' );
    foreach ( [ 'cytat', 'opis', 'slad' ] as $pole ) $nowe['swieta'][ $pole ] = $dlugi( $we['swieta'][ $pole ] ?? '' );

    foreach ( $we_d as $k => $d ) {
        $k = (int) $k;
        if ( $k < 0 || $k > 40 || ! is_array( $d ) ) continue;
        $nowe['dni'][ $k ] = [
            'tytul'     => $tekst( $d['tytul'] ?? '' ),
            'ewangelia' => $tekst( $d['ewangelia'] ?? '' ),
            'cytat'     => $dlugi( $d['cytat'] ?? '' ),
            'opis'      => $dlugi( $d['opis'] ?? '' ),
            'slad'      => $dlugi( $d['slad'] ?? '' ),
            'km'        => $km( $d['km'] ?? 0 ),
        ];
    }
    ksort( $nowe['dni'] );

    update_option( PARAFIA_ADWENT_OPCJA, $nowe, false );
    wp_safe_redirect( add_query_arg( [ 'page' => PARAFIA_ADWENT_MENU, 'zapisano' => 1 ], admin_url( 'admin.php' ) ) );
    exit;
}
add_action( 'admin_post_parafia_adwent_zapisz', 'parafia_adwent_zapisz' );

function parafia_adwent_przywroc() {
    if ( ! current_user_can( PARAFIA_ADWENT_UPRAWNIENIE ) ) wp_die( 'Brak uprawnień.' );
    check_admin_referer( 'parafia_adwent_przywroc', 'parafia_adwent_nonce' );
    delete_option( PARAFIA_ADWENT_OPCJA );
    wp_safe_redirect( add_query_arg( [ 'page' => PARAFIA_ADWENT_MENU, 'przywrocono' => 1 ], admin_url( 'admin.php' ) ) );
    exit;
}
add_action( 'admin_post_parafia_adwent_przywroc', 'parafia_adwent_przywroc' );
