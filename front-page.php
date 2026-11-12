<?php
get_header();
?>

<main id="primary" class="site-main" role="main">

    <?php
    $hero_posts = new WP_Query( [
        'post_type'      => 'post',
        'posts_per_page' => 5,
        'meta_query'     => [
            [
                'key'     => '_is_hero_slide',
                'value'   => '1',
                'compare' => '=',
            ],
        ],
    ] );

    if ( ! $hero_posts->have_posts() ) {
        $hero_posts = new WP_Query( [
            'post_type'      => 'post',
            'posts_per_page' => 3,
            'category_name'  => 'aktualnosci',
        ] );
    }

    if ( $hero_posts->have_posts() ) : ?>
    <section class="hero-slider" aria-label="<?php esc_attr_e( 'Slider główny', 'parafia-mrozy' ); ?>" aria-roledescription="carousel">

        <button class="hero-prev" aria-label="Poprzedni slajd" aria-controls="hero-slides">&#8249;</button>
        <button class="hero-next" aria-label="Następny slajd" aria-controls="hero-slides">&#8250;</button>

        <div id="hero-slides" aria-live="polite">
        <?php $slide_index = 0; while ( $hero_posts->have_posts() ) : $hero_posts->the_post(); ?>
        <div class="hero-slide <?php echo $slide_index === 0 ? 'active' : ''; ?>"
             role="group"
             aria-roledescription="slajd"
             aria-label="<?php echo esc_attr( ($slide_index + 1) . ' z ' . $hero_posts->post_count ); ?>"
             <?php echo $slide_index !== 0 ? 'aria-hidden="true"' : ''; ?>>
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'hero-image', [ 'alt' => get_the_title() ] ); ?>
            <?php else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/img/hero-placeholder.jpg" alt="<?php the_title_attribute(); ?>">
            <?php endif; ?>
            <div class="hero-caption">
                <h2><a href="<?php the_permalink(); ?>" style="color:inherit;"><?php the_title(); ?></a></h2>
            </div>
        </div>
        <?php $slide_index++; endwhile; wp_reset_postdata(); ?>
        </div>

        <div class="hero-dots" role="tablist" aria-label="Slajdy">
            <?php for ( $i = 0; $i < $slide_index; $i++ ) : ?>
                <span class="<?php echo $i === 0 ? 'active' : ''; ?>"
                      role="tab"
                      tabindex="<?php echo $i === 0 ? '0' : '-1'; ?>"
                      aria-label="Slajd <?php echo $i + 1; ?>"
                      aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"></span>
            <?php endfor; ?>
        </div>

    </section>
    <?php endif; ?>

    <?php
    // Adwent: „Droga do Betlejem” — widoczne tylko w okresie ustawionym w panelu (menu „Adwent”).
    if ( function_exists( 'parafia_mrozy_adwent_sekcja' ) ) {
        parafia_mrozy_adwent_sekcja();
    }
    ?>

    <section class="section-wrapper aktualnosci-section">
        <div class="container">
            <h2 class="section-heading"><?php esc_html_e( 'Aktualności parafialne', 'parafia-mrozy' ); ?></h2>

            <?php
            $aktualnosci = new WP_Query( [
                'post_type'      => 'post',
                'posts_per_page' => 6,
                'category_name'  => 'aktualnosci',
            ] );
            ?>

            <?php if ( $aktualnosci->have_posts() ) : ?>
            <div class="posts-grid">
                <?php while ( $aktualnosci->have_posts() ) : $aktualnosci->the_post();
                    get_template_part( 'template-parts/card', 'post' );
                endwhile; wp_reset_postdata(); ?>
            </div>

            <div class="read-more-wrap" style="margin-top:25px;">
                <a href="<?php echo esc_url( get_category_link( get_category_by_slug( 'aktualnosci' ) ) ); ?>" class="read-more-link">
                    <?php esc_html_e( 'Starsze wpisy &raquo;', 'parafia-mrozy' ); ?>
                </a>
            </div>
            <?php else : ?>
                <p><?php esc_html_e( 'Brak aktualności.', 'parafia-mrozy' ); ?></p>
            <?php endif; ?>

        </div>
    </section>


    <section class="section-wrapper ogloszenia-section">
        <div class="container">
            <h2 class="section-heading"><?php esc_html_e( 'Ogłoszenia parafialne', 'parafia-mrozy' ); ?></h2>

            <?php
            $ogloszenia = new WP_Query( [
                'post_type'      => 'ogloszenia',
                'posts_per_page' => 3,
            ] );
            ?>

            <?php if ( $ogloszenia->have_posts() ) : ?>
            <div class="posts-grid">
                <?php while ( $ogloszenia->have_posts() ) : $ogloszenia->the_post();
                    get_template_part( 'template-parts/card', 'post' );
                endwhile; wp_reset_postdata(); ?>
            </div>
            <?php else : ?>
                <p><?php esc_html_e( 'Brak ogłoszeń.', 'parafia-mrozy' ); ?></p>
            <?php endif; ?>

        </div>
    </section>


    <section class="section-wrapper intencje-section">
        <div class="container">
            <h2 class="section-heading"><?php esc_html_e( 'Intencje Mszy św.', 'parafia-mrozy' ); ?></h2>

            <?php
            $intencje = new WP_Query( [
                'post_type'      => 'intencje',
                'posts_per_page' => 3,
            ] );
            ?>

            <?php if ( $intencje->have_posts() ) : ?>
            <div class="posts-grid">
                <?php while ( $intencje->have_posts() ) : $intencje->the_post();
                    get_template_part( 'template-parts/card', 'intencja' );
                endwhile; wp_reset_postdata(); ?>
            </div>
            <?php else : ?>
                <p><?php esc_html_e( 'Brak intencji.', 'parafia-mrozy' ); ?></p>
            <?php endif; ?>

        </div>
    </section>

    <section class="section-wrapper rocznica-section">
        <div class="container">
            <div class="rocznica-inner">
                <p class="rocznica-label"><?php esc_html_e( 'Do 100. rocznicy erygowania parafii', 'parafia-mrozy' ); ?></p>
                <h2 class="rocznica-title"><?php esc_html_e( '1 kwietnia 2031', 'parafia-mrozy' ); ?></h2>
                <div class="rocznica-grid">
                    <div class="rocznica-card"><span class="rocznica-number" id="rc-lata">–</span><span class="rocznica-unit"><?php esc_html_e( 'lat', 'parafia-mrozy' ); ?></span></div>
                    <div class="rocznica-card"><span class="rocznica-number" id="rc-miesiace">–</span><span class="rocznica-unit"><?php esc_html_e( 'miesięcy', 'parafia-mrozy' ); ?></span></div>
                    <div class="rocznica-card"><span class="rocznica-number" id="rc-dni">–</span><span class="rocznica-unit"><?php esc_html_e( 'dni', 'parafia-mrozy' ); ?></span></div>
                    <div class="rocznica-card"><span class="rocznica-number" id="rc-godziny">–</span><span class="rocznica-unit"><?php esc_html_e( 'godzin', 'parafia-mrozy' ); ?></span></div>
                    <div class="rocznica-card"><span class="rocznica-number" id="rc-minuty">–</span><span class="rocznica-unit"><?php esc_html_e( 'minut', 'parafia-mrozy' ); ?></span></div>
                    <div class="rocznica-card"><span class="rocznica-number" id="rc-sekundy">–</span><span class="rocznica-unit"><?php esc_html_e( 'sekund', 'parafia-mrozy' ); ?></span></div>
                </div>
                <div class="rocznica-divider" aria-hidden="true">
                    <span></span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18"><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                    <span></span>
                </div>
                <p class="rocznica-sub"><?php esc_html_e( 'Parafia pw. Św. Teresy od Dzieciątka Jezus w Mrozach · erygowana 1 kwietnia 1931', 'parafia-mrozy' ); ?></p>
            </div>
        </div>
    </section>

    <script>
    (function () {
        var cel = new Date(2031, 3, 1, 0, 0, 0);
        function pad(n) { return n < 10 ? '0' + n : '' + n; }

        function oblicz() {
            var teraz = new Date();
            var ms = cel - teraz;
            if (ms <= 0) return;

            var tmp = new Date(teraz);
            var lata = 0, miesiace = 0, dni = 0;
            while (true) { var n  = new Date(tmp); n.setFullYear(n.getFullYear() + 1); if (n > cel) break; lata++;    tmp = n;  }
            while (true) { var n2 = new Date(tmp); n2.setMonth(n2.getMonth() + 1);     if (n2 > cel) break; miesiace++; tmp = n2; }
            while (true) { var n3 = new Date(tmp); n3.setDate(n3.getDate() + 1);       if (n3 > cel) break; dni++;     tmp = n3; }

            document.getElementById('rc-lata').textContent     = lata;
            document.getElementById('rc-miesiace').textContent  = miesiace;
            document.getElementById('rc-dni').textContent      = dni;
            document.getElementById('rc-godziny').textContent  = Math.floor(ms / 3600000) % 24;
            document.getElementById('rc-minuty').textContent   = pad(Math.floor(ms / 60000) % 60);
            document.getElementById('rc-sekundy').textContent  = pad(Math.floor(ms / 1000) % 60);
        }

        oblicz();
        setInterval(oblicz, 1000);
    })();
    </script>

    <section class="gallery-section">
        <div class="container">
            <h2 class="section-heading"><?php esc_html_e( 'Galeria', 'parafia-mrozy' ); ?></h2>

            <?php
            $gallery_posts = new WP_Query( parafia_mrozy_galeria_args( [
                'posts_per_page' => 3,
            ] ) );
            ?>

            <?php if ( $gallery_posts->have_posts() ) : ?>
            <div class="gallery-grid">
                <?php while ( $gallery_posts->have_posts() ) : $gallery_posts->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="gallery-item">
                    <?php $okladka = parafia_mrozy_galeria_okladka( get_the_ID() ); ?>
                    <?php if ( $okladka ) : ?>
                        <?php echo wp_get_attachment_image( $okladka, 'gallery-thumb', false, [ 'alt' => get_the_title() ] ); ?>
                    <?php else : ?>
                        <img src="<?php echo get_template_directory_uri(); ?>/img/gallery-placeholder.jpg" alt="<?php the_title_attribute(); ?>">
                    <?php endif; ?>
                    <div class="gallery-caption"><?php the_title(); ?></div>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <?php
            $total = (int) $gallery_posts->found_posts;

            if ( $total > 3 ) :
                $galeria_url = home_url('/galerie/');
            ?>
            <div class="read-more-wrap" style="margin-top:25px;">
                <a href="<?php echo esc_url( $galeria_url ); ?>" class="read-more-link">
                    Więcej galerii zdjęć »
                </a>
            </div>
            <?php endif; ?>

            <?php endif; ?>

        </div>
    </section>


    <?php

    function parafia_easter( $year ) {
        $a = $year % 19;
        $b = intdiv( $year, 100 );
        $c = $year % 100;
        $d = intdiv( $b, 4 );
        $e = $b % 4;
        $f = intdiv( $b + 8, 25 );
        $g = intdiv( $b - $f + 1, 3 );
        $h = ( 19 * $a + $b - $d - $g + 15 ) % 30;
        $i = intdiv( $c, 4 );
        $k = $c % 4;
        $l = ( 32 + 2 * $e + 2 * $i - $h - $k ) % 7;
        $m = intdiv( $a + 11 * $h + 22 * $l, 451 );
        $month = intdiv( $h + $l - 7 * $m + 114, 31 );
        $day   = ( ( $h + $l - 7 * $m + 114 ) % 31 ) + 1;
        return mktime( 0, 0, 0, $month, $day, $year );
    }

    function parafia_liturgical_dates( $year ) {
        $easter      = parafia_easter( $year );
        $ash         = strtotime( '-46 days', $easter );
        $pentecost   = strtotime( '+49 days', $easter );
        $holy_thu    = strtotime( '-3 days',  $easter );
        $good_fri    = strtotime( '-2 days',  $easter );
        $holy_sat    = strtotime( '-1 day',   $easter );
        $palm        = strtotime( '-7 days',  $easter );
        $ascension   = strtotime( '+42 days', $easter );
        $high_priest = strtotime( '+53 days', $easter );
        $corpus      = strtotime( '+60 days', $easter );
        $sacred_h    = strtotime( '+68 days', $easter );
        $christmas   = mktime( 0, 0, 0, 12, 25, $year );
        $xmas_dow    = (int) date( 'w', $christmas );
        $days_back   = $xmas_dow === 0 ? 28 : ( 28 - $xmas_dow );
        $first_adv   = strtotime( "-{$days_back} days", $christmas );
        $christ_king = strtotime( '-7 days', $first_adv );
        $epiphany    = mktime( 0, 0, 0, 1, 6, $year );
        $ep_dow      = (int) date( 'w', $epiphany );
        $baptism     = $ep_dow === 0
            ? mktime( 0, 0, 0, 1, 13, $year )
            : strtotime( '+' . ( 7 - $ep_dow ) . ' days', $epiphany );
        return compact( 'easter','ash','pentecost','holy_thu','good_fri','holy_sat',
                        'palm','ascension','corpus','sacred_h','high_priest','christmas',
                        'first_adv','christ_king','epiphany','baptism' );
    }

    function parafia_get_season( $ts ) {
        $year  = (int) date( 'Y', $ts );
        $month = (int) date( 'n', $ts );
        $d     = $ts;

        if ( $month <= 2 ) {
            $prev_xmas_eve = mktime( 0, 0, 0, 12, 24, $year - 1 );
            $cur  = parafia_liturgical_dates( $year );
            if ( $d >= $prev_xmas_eve && $d <= $cur['baptism'] ) return 'christmas';
            $day_after_bap = strtotime( '+1 day', $cur['baptism'] );
            $day_before_ash = strtotime( '-1 day', $cur['ash'] );
            if ( $d >= $day_after_bap && $d <= $day_before_ash ) return 'ordinary-winter';
            if ( $d >= $cur['ash'] && $d <= $cur['holy_sat'] ) return 'lent';
            if ( $d >= $cur['easter'] && $d <= $cur['pentecost'] ) return 'easter';
            return 'ordinary-winter';
        }

        $dates = parafia_liturgical_dates( $year );
        if ( $d >= $dates['ash'] && $d <= $dates['holy_sat'] )     return 'lent';
        if ( $d >= $dates['easter'] && $d <= $dates['pentecost'] ) return 'easter';
        $day_after_pent = strtotime( '+1 day', $dates['pentecost'] );
        if ( $d >= $day_after_pent && $d <= $dates['christ_king'] ) return 'ordinary-summer';
        $dec23 = mktime( 0, 0, 0, 12, 23, $year );
        if ( $d >= $dates['first_adv'] && $d <= $dec23 ) return 'advent';
        $xmas_eve = mktime( 0, 0, 0, 12, 24, $year );
        if ( $d >= $xmas_eve ) return 'christmas';
        return 'ordinary-summer';
    }

    function parafia_get_feasts( $year ) {
        $d = parafia_liturgical_dates( $year );
        return [
            [ 'name' => 'Świętej Bożej Rodzicielki Maryi',               'ts' => mktime(0,0,0,1,1,$year),  'rank' => 'U' ],
            [ 'name' => 'Objawienie Pańskie',                             'ts' => mktime(0,0,0,1,6,$year),  'rank' => 'U' ],
            [ 'name' => 'Ofiarowanie Pańskie',                            'ts' => mktime(0,0,0,2,2,$year),  'rank' => 'Ś' ],
            [ 'name' => 'Świętych Cyryla i Metodego',                     'ts' => mktime(0,0,0,2,14,$year), 'rank' => 'Ś' ],
            [ 'name' => 'Św. Kazimierza',                                 'ts' => mktime(0,0,0,3,4,$year),  'rank' => 'Ś' ],
            [ 'name' => 'Św. Józefa, Oblubieńca NMP',                     'ts' => mktime(0,0,0,3,19,$year), 'rank' => 'U' ],
            [ 'name' => 'Zwiastowanie Pańskie',                           'ts' => mktime(0,0,0,3,25,$year), 'rank' => 'U' ],
            [ 'name' => 'Św. Wojciecha',                                  'ts' => mktime(0,0,0,4,23,$year), 'rank' => 'U' ],
            [ 'name' => 'Św. Marka',                                      'ts' => mktime(0,0,0,4,25,$year), 'rank' => 'Ś' ],
            [ 'name' => 'Najświętszej Maryi Panny, Królowej Polski',      'ts' => mktime(0,0,0,5, $year === 2026 ? 2 : 3, $year), 'rank' => 'U' ],
            [ 'name' => 'Św. Atanazego, biskupa i doktora Kościoła',      'ts' => mktime(0,0,0,5, $year === 2026 ? 4 : 2, $year), 'rank' => $year === 2026 ? 'w' : 'W' ],
            [ 'name' => 'Św. Floriana, męczennika',                        'ts' => mktime(0,0,0,5,4,$year), 'rank' => 'w' ],
            [ 'name' => 'Św. Stanisława, biskupa i męczennika',           'ts' => mktime(0,0,0,5,8,$year),  'rank' => 'U' ],
            [ 'name' => 'Nawiedzenie Najświętszej Maryi Panny',           'ts' => mktime(0,0,0,5,31,$year), 'rank' => 'Ś' ],
            [ 'name' => 'Św. Andrzeja Boboli',                            'ts' => mktime(0,0,0,5,16,$year), 'rank' => 'U' ],
            [ 'name' => 'Narodzenie św. Jana Chrzciciela',                'ts' => mktime(0,0,0,6,24,$year), 'rank' => 'U' ],
            [ 'name' => 'Świętych Apostołów Piotra i Pawła',              'ts' => mktime(0,0,0,6,29,$year), 'rank' => 'U' ],
            [ 'name' => 'Wniebowzięcie Najświętszej Maryi Panny',        'ts' => mktime(0,0,0,8,15,$year), 'rank' => 'U' ],
            [ 'name' => 'Najświętszej Maryi Panny Częstochowskiej',       'ts' => mktime(0,0,0,8,26,$year), 'rank' => 'U' ],
            [ 'name' => 'Narodzenie Najświętszej Maryi Panny',            'ts' => mktime(0,0,0,9,8,$year),  'rank' => 'Ś' ],
            [ 'name' => 'Podwyższenie Krzyża Świętego',                   'ts' => mktime(0,0,0,9,14,$year), 'rank' => 'Ś' ],
            [ 'name' => 'Św. Stanisława Kostki',                          'ts' => mktime(0,0,0,9,18,$year), 'rank' => 'Ś' ],
            [ 'name' => 'Wszyscy Święci',                                 'ts' => mktime(0,0,0,11,1,$year), 'rank' => 'U' ],
            [ 'name' => 'Niepokalane Poczęcie NMP',                       'ts' => mktime(0,0,0,12,8,$year), 'rank' => 'U' ],
            [ 'name' => 'Boże Narodzenie',                                'ts' => mktime(0,0,0,12,25,$year),'rank' => 'U' ],
            [ 'name' => 'Bł. Ignacego Kłopotowskiego, prezbitera',        'ts' => mktime(0,0,0,9,7,$year),  'rank' => 'W' ],
            [ 'name' => 'Św. Zygmunta Szczęsnego Felińskiego, biskupa',   'ts' => mktime(0,0,0,9,17,$year), 'rank' => 'W' ],
            [ 'name' => 'Rocznica poświęcenia kościoła katedralnego',     'ts' => mktime(0,0,0,9,29,$year), 'rank' => 'Ś' ],
            [ 'name' => 'NMP Ostrobramskiej, Matki Miłosierdzia',         'ts' => mktime(0,0,0,11,16,$year),'rank' => 'W' ],
            [ 'name' => 'Chrzest Pański',             'ts' => $d['baptism'],   'rank' => 'Ś' ],
            [ 'name' => 'Niedziela Palmowa',           'ts' => $d['palm'],      'rank' => 'U' ],
            [ 'name' => 'Wielki Czwartek',             'ts' => $d['holy_thu'],  'rank' => 'U' ],
            [ 'name' => 'Wielki Piątek',               'ts' => $d['good_fri'],  'rank' => 'U' ],
            [ 'name' => 'Wielka Sobota',               'ts' => $d['holy_sat'],  'rank' => 'U' ],
            [ 'name' => 'Wielkanoc',                   'ts' => $d['easter'],    'rank' => 'U' ],
            [ 'name' => 'Wniebowstąpienie Pańskie',   'ts' => $d['ascension'], 'rank' => 'U' ],
            [ 'name' => 'Zesłanie Ducha Świętego',    'ts' => $d['pentecost'], 'rank' => 'U' ],
            [ 'name' => 'Najświętszej Trójcy',        'ts' => strtotime('+7 days', $d['pentecost']), 'rank' => 'U' ],
            [ 'name' => 'Boże Ciało',                 'ts' => $d['corpus'],    'rank' => 'U' ],
            [ 'name' => 'Jezusa Chrystusa Najwyższego i Wiecznego Kapłana', 'ts' => $d['high_priest'], 'rank' => 'Ś' ],
            [ 'name' => 'Najświętszego Serca Pana Jezusa', 'ts' => $d['sacred_h'], 'rank' => 'U' ],
            [ 'name' => 'Jezusa Chrystusa, Króla Wszechświata', 'ts' => $d['christ_king'], 'rank' => 'U' ],
        ];
    }

    $season_config = [
        'advent'          => [ 'name' => 'Adwent',                   'color' => '#7B1FA2', 'text' => '#fff' ],
        'christmas'       => [ 'name' => 'Okres Bożego Narodzenia',  'color' => '#F9A825', 'text' => '#1a1a1a' ],
        'ordinary-winter' => [ 'name' => 'Okres zwykły',             'color' => '#2E7D32', 'text' => '#fff' ],
        'lent'            => [ 'name' => 'Wielki Post',               'color' => '#4527A0', 'text' => '#fff' ],
        'easter'          => [ 'name' => 'Okres Wielkanocny',         'color' => '#F57F17', 'text' => '#1a1a1a' ],
        'ordinary-summer' => [ 'name' => 'Okres zwykły',             'color' => '#388E3C', 'text' => '#fff' ],
    ];

    $rank_label = [ 'U' => 'Uroczystość', 'Ś' => 'Święto', 'W' => 'Wspomnienie', 'w' => 'Wspomnienie dowolne' ];

    $now      = mktime( 0, 0, 0, (int)date('n'), (int)date('j'), (int)date('Y') );
    $year_now = (int) date( 'Y' );
    $season   = parafia_get_season( $now );
    $cfg      = $season_config[ $season ];

    $all_feasts   = parafia_get_feasts( $year_now );
    $today_feasts = array_filter( $all_feasts, fn($f) => date('z Y',$f['ts']) === date('z Y',$now) );
    usort( $today_feasts, fn($a,$b) => strcmp($a['rank'],$b['rank']) );

    $upcoming = array_filter( $all_feasts, fn($f) => $f['ts'] > $now );
    usort( $upcoming, fn($a,$b) => $a['ts'] - $b['ts'] );
    $upcoming = array_slice( $upcoming, 0, 3 );

    $season_end = null;
    $dates_now  = parafia_liturgical_dates( $year_now );
    switch ( $season ) {
        case 'advent':          $season_end = strtotime('-1 day', mktime(0,0,0,12,24,$year_now)); break;
        case 'christmas':
            $next = parafia_liturgical_dates( $year_now + 1 );
            $season_end = $dates_now['baptism'] > $now ? $dates_now['baptism'] : $next['baptism'];
            break;
        case 'ordinary-winter': $season_end = strtotime('-1 day', $dates_now['ash']); break;
        case 'lent':            $season_end = $dates_now['holy_sat']; break;
        case 'easter':          $season_end = $dates_now['pentecost']; break;
        case 'ordinary-summer': $season_end = $dates_now['christ_king']; break;
    }
    $days_left = $season_end ? (int) ceil( ($season_end - $now) / 86400 ) : 0;
    ?>

    <section class="liturgical-section" style="--liturgical-color: <?php echo esc_attr($cfg['color']); ?>; --liturgical-text: <?php echo esc_attr($cfg['text']); ?>;">
        <div class="container">
            <h2 class="section-heading"><?php esc_html_e( 'Kalendarz liturgiczny', 'parafia-mrozy' ); ?></h2>
            <div class="liturgical-grid">

                <!-- Okres liturgiczny -->
                <div class="liturgical-card liturgical-season">
                    <div class="liturgical-season-bar"></div>
                    <div class="liturgical-card-inner">
                        <span class="liturgical-label">Aktualny okres</span>
                        <strong class="liturgical-season-name"><?php echo esc_html( $cfg['name'] ); ?></strong>
                        <span class="liturgical-days-left">
                            <?php if ( $days_left > 0 ) : ?>
                                jeszcze <?php echo $days_left; ?> <?php echo $days_left === 1 ? 'dzień' : ( $days_left < 5 ? 'dni' : 'dni' ); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- Dzisiejsze święto -->
                <div class="liturgical-card liturgical-today">
                    <span class="liturgical-label">Dziś świętujemy</span>
                    <?php if ( $today_feasts ) :
                        $f = reset( $today_feasts ); ?>
                        <strong class="liturgical-feast-name"><?php echo esc_html( $f['name'] ); ?></strong>
                        <span class="liturgical-rank liturgical-rank-<?php echo esc_attr( $f['rank'] === 'w' ? 'wd' : strtolower($f['rank']) ); ?>">
                            <?php echo esc_html( $rank_label[ $f['rank'] ] ?? '' ); ?>
                        </span>
                    <?php else : ?>
                        <span class="liturgical-no-feast">Dzień powszedni</span>
                    <?php endif; ?>
                </div>

                <!-- Nadchodzące święta -->
                <div class="liturgical-card liturgical-upcoming">
                    <span class="liturgical-label">Nadchodzące święta</span>
                    <ul class="liturgical-upcoming-list">
                        <?php foreach ( $upcoming as $f ) : ?>
                        <li>
                            <span class="liturgical-upcoming-date"><?php echo esc_html( date_i18n( 'j M', $f['ts'] ) ); ?></span>
                            <span class="liturgical-upcoming-name"><?php echo esc_html( $f['name'] ); ?></span>
                            <span class="liturgical-rank liturgical-rank-<?php echo esc_attr( $f['rank'] === 'w' ? 'wd' : strtolower($f['rank']) ); ?>">
                                <?php echo esc_html( $rank_label[ $f['rank'] ] ?? '' ); ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <section class="info-panel">
        <div class="container">
            <div class="info-panel-grid">

                <!-- Kancelaria -->
                <div class="info-block">
                    <h3><?php esc_html_e( 'Kancelaria parafialna', 'parafia-mrozy' ); ?></h3>

                    <h4><?php esc_html_e( 'Godziny otwarcia', 'parafia-mrozy' ); ?></h4>
                    <p>
                        <?php esc_html_e( 'Wtorek: 16:30 – 17:30', 'parafia-mrozy' ); ?><br>
                        <?php esc_html_e( 'Środa: 16:30 – 17:30', 'parafia-mrozy' ); ?><br>
                        <?php esc_html_e( 'Czwartek: 8:45 – 9:30', 'parafia-mrozy' ); ?><br>
                        <?php esc_html_e( 'Piątek: 16:30 – 17:30', 'parafia-mrozy' ); ?><br>
                        <?php esc_html_e( 'Sobota: 8:45 – 9:30', 'parafia-mrozy' ); ?>
                    </p>

                    <h4 style="color:#e57373;"><?php esc_html_e( 'W Pierwszy Piątek Miesiąca', 'parafia-mrozy' ); ?></h4>
                    <p style="color:#e57373;"><?php esc_html_e( 'kancelaria nieczynna!', 'parafia-mrozy' ); ?></p>

                    <h4><?php esc_html_e( 'Dane kontaktowe', 'parafia-mrozy' ); ?></h4>
                    <p>
                        Parafia Rzymskokatolicka pw. św. Teresy<br>
                        od Dzieciątka Jezus w Mrozach<br>
                        <?php echo pm_get( 'parafia_address', 'ul. Św. Teresy 3, 05-320 Mrozy' ); ?><br>
                        tel. <?php echo pm_get( 'parafia_phone', '25 757 41 93' ); ?><br>
                        <a href="mailto:<?php echo pm_get( 'parafia_email', 'parafiamrozy@gmail.com' ); ?>" style="color:var(--color-gold-light);">
                            <?php echo pm_get( 'parafia_email', 'parafiamrozy@gmail.com' ); ?>
                        </a>
                    </p>
                </div>

                <!-- Porządek Mszy -->
                <div class="info-block">
                    <h3><?php esc_html_e( 'Porządek Mszy Św. i Nabożeństw', 'parafia-mrozy' ); ?></h3>

                    <h4><?php esc_html_e( 'Msze Święte', 'parafia-mrozy' ); ?></h4>
                    <table class="schedule-table">
                        <tr>
                            <td><?php esc_html_e( 'W dni powszednie:', 'parafia-mrozy' ); ?></td>
                            <td><?php echo pm_get( 'msze_powszednie', '07:00 i 18:00' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'W niedzielę:', 'parafia-mrozy' ); ?></td>
                            <td><?php echo pm_get( 'msze_niedziela', '07:00, 08:30, 10:00, 12:00 i 18:00' ); ?></td>
                        </tr>
                    </table>

                    <h4><?php esc_html_e( 'Spowiedź', 'parafia-mrozy' ); ?></h4>
                    <p><?php echo pm_get( 'spowiedz', 'Przed każdą Mszą Świętą oraz w Pierwszy Piątek Miesiąca' ); ?></p>

                    <h4><?php esc_html_e( 'Różaniec', 'parafia-mrozy' ); ?></h4>
                    <p><?php echo pm_get( 'rozaniec', 'Niedziela: 17:30' ); ?></p>
                </div>

            </div>
        </div>
    </section>


    <section class="quote-section">
        <div class="container">
            <div class="quote-inner">
                <div class="quote-photo">
                    <img src="<?php echo get_template_directory_uri(); ?>/img/sw-teresa.png"
                         alt="Święta Teresa od Dzieciątka Jezus">
                </div>
                <blockquote>
                    <p>„zrozumiałam, że kościół ma Serce i że Serce to <strong>PŁONIE MIŁOŚCIĄ</strong>. zrozumiałam, że jedynie miłość sprawia, że działają członki Kościoła, że gdyby Miłość wygasła, Apostołowie nie głosiliby Ewangelii, Męczennicy odmówiliby przelewania swojej krwi. Zrozumiałam, że miłość zawiera wszystkie powołania, że miłość jest wszystkim, obejmuje wszystkie czasy i wszystkie miejsca… jednym słowem jest wieczna!"</p>
                    <cite>(por. św. Teresa od Dzieciątka Jezus, Dzieje Duszy, 2005, s. 264)</cite>
                </blockquote>
            </div>
        </div>
    </section>



    <section class="vatican-news-section">
        <div class="container">
            <h2 class="section-heading"><?php esc_html_e( 'Vatican News', 'parafia-mrozy' ); ?></h2>
            <div class="vn-iframe-wrap">
                <iframe id="VaticanNews"
                        data-src="https://vaticannews.pl/news?lang=pl&amp;template=full&amp;count=8&amp;fs=14&amp;link=%23235787&amp;date=%23828282&amp;video=1&amp;brand=0&amp;cols=full"
                        height="600" scrolling="no" title="Vatican News"></iframe>
                <noscript>
                    <iframe src="https://vaticannews.pl/news?lang=pl&amp;template=full&amp;count=8&amp;fs=14&amp;link=%23235787&amp;date=%23828282&amp;video=1&amp;brand=0&amp;cols=full"
                            width="100%" height="1900" style="border:0;" title="Vatican News"></iframe>
                </noscript>
            </div>
            <div class="vn-footer">
                <a href="https://www.vaticannews.va/pl.html" target="_blank" rel="noopener" class="vn-footer__link">
                    Więcej na Vatican News »
                </a>
            </div>
            <script>
            (function () {
                var f = document.getElementById( 'VaticanNews' );
                if ( ! f ) { return; }
                var SRC = f.getAttribute( 'data-src' );
                if ( ! SRC ) { return; }
                var VN_ORIGIN = 'https://vaticannews.pl';
                var lastWidth = 0;
                var resizeTimer;

                window.addEventListener( 'message', function ( e ) {
                    if ( e.origin !== VN_ORIGIN ) { return; }
                    var d = e.data;
                    if ( ! d || d.source !== 'vn-embed' ) { return; }
                    var h = parseInt( d.height, 10 );
                    if ( h > 0 && h < 20000 ) { f.style.height = h + 'px'; }
                } );

                function loadWidget() {
                    lastWidth = f.clientWidth;
                    f.style.height = '600px';
                    f.src = SRC + ( SRC.indexOf( '?' ) > -1 ? '&' : '?' ) + '_w=' + lastWidth + '.' + Date.now();
                }

                if ( document.readyState === 'complete' ) { loadWidget(); }
                else { window.addEventListener( 'load', loadWidget ); }

                window.addEventListener( 'resize', function () {
                    if ( Math.abs( f.clientWidth - lastWidth ) < 20 ) { return; }
                    clearTimeout( resizeTimer );
                    resizeTimer = setTimeout( loadWidget, 300 );
                } );
            })();
            </script>
        </div>
    </section>



</main>

<?php get_footer(); ?>
