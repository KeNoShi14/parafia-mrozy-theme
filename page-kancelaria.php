<?php
get_header();

$pid = get_the_ID();

function kanc_get( $meta_key, $theme_key, $fallback ) {
    $pid = get_the_ID();
    $val = get_post_meta( $pid, '_kanc_' . $meta_key, true );
    if ( $val !== '' && $val !== false ) return esc_html( $val );
    return pm_get( $theme_key, $fallback );
}

$nazwa      = kanc_get( 'nazwa',   'parafia_nazwa',   'Parafia Rzymskokatolicka pw. św. Teresy od Dzieciątka Jezus w Mrozach' );
$adres      = kanc_get( 'adres',   'parafia_address', 'ul. Św. Teresy 3, 05-320 Mrozy' );
$telefon    = kanc_get( 'telefon', 'parafia_phone',   '25 757 41 93' );
$email_raw  = get_post_meta( $pid, '_kanc_email', true ) ?: get_theme_mod( 'parafia_email', 'parafiamrozy@gmail.com' );
$email      = sanitize_email( $email_raw );
$nip        = kanc_get( 'nip',     'parafia_nip',     '' );
$regon      = kanc_get( 'regon',   'parafia_regon',   '' );
$konto      = kanc_get( 'konto',   'parafia_konto',   '' );

$default_godziny = json_encode([
    [ 'dzien' => 'Wtorek',    'od' => '16:30', 'do' => '17:30', 'uwaga' => '' ],
    [ 'dzien' => 'Środa',     'od' => '16:30', 'do' => '17:30', 'uwaga' => '' ],
    [ 'dzien' => 'Czwartek',  'od' => '8:45',  'do' => '9:30',  'uwaga' => '' ],
    [ 'dzien' => 'Piątek',    'od' => '16:30', 'do' => '17:30', 'uwaga' => '' ],
    [ 'dzien' => 'Sobota',    'od' => '8:45',  'do' => '9:30',  'uwaga' => '' ],
], JSON_UNESCAPED_UNICODE);
$godziny_json = get_post_meta( $pid, '_kanc_godziny', true ) ?: $default_godziny;
$godziny = json_decode( $godziny_json, true ) ?: [];

$uwaga_global = kanc_get( 'uwaga_global', '', 'W Pierwszy Piątek Miesiąca kancelaria nieczynna' );

?>

<main id="primary" class="site-main" role="main">

    <!-- Hero -->
    <div class="kanc-hero">
        <div class="container">
            <p class="kanc-hero__eyebrow">Parafia pw. św. Teresy od Dzieciątka Jezus</p>
            <h1 class="kanc-hero__title">Kancelaria Parafialna</h1>
            <p class="kanc-hero__subtitle">Informacje o godzinach otwarcia i danych kontaktowych.</p>
            <div class="kanc-status kanc-status--closed" id="kanc-status-badge">
                <span class="kanc-status__dot"></span>
                <span id="kanc-status-label">Sprawdzanie...</span>
            </div>
            <?php
            $godziny_js = json_encode( $godziny, JSON_UNESCAPED_UNICODE );
            ?>
            <script>
            (function(){
                var godziny = <?php echo $godziny_js; ?>;
                var dni = ['Niedziela','Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota'];

                function checkStatus() {
                    var now   = new Date();
                    var dzis  = dni[ now.getDay() ];
                    var czas  = now.getHours() * 100 + now.getMinutes();
                    var open  = false;

                    for (var i = 0; i < godziny.length; i++) {
                        var g = godziny[i];
                        if (g.dzien !== dzis) continue;
                        var od = parseInt(g.od.replace(':', ''));
                        var doo = parseInt(g.do.replace(':', ''));
                        if (czas >= od && czas <= doo) { open = true; break; }
                    }

                    var badge = document.getElementById('kanc-status-badge');
                    var label = document.getElementById('kanc-status-label');
                    if (!badge || !label) return;

                    badge.classList.remove('kanc-status--open', 'kanc-status--closed');
                    badge.classList.add(open ? 'kanc-status--open' : 'kanc-status--closed');
                    label.textContent = open ? 'Trwa dyżur w kancelarii' : 'Kancelaria zamknięta';
                }

                checkStatus();
                setInterval(checkStatus, 60000);
            })();
            </script>
        </div>
    </div>

    <div class="container">
        <div class="kanc-2col">

                <!-- Godziny -->
                <div class="kanc-card">
                    <div class="kanc-card__header">
                        <svg class="kanc-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <h2 class="kanc-card__title">Godziny otwarcia</h2>
                    </div>
                    <div class="kanc-card__body">
                        <div class="kanc-hours-list" role="list" aria-label="Godziny otwarcia kancelarii">
                            <?php foreach ( $godziny as $idx => $g ) : ?>
                            <div class="kanc-hours-row" role="listitem"
                                 data-dzien="<?php echo esc_attr( $g['dzien'] ); ?>"
                                 data-od="<?php echo esc_attr( $g['od'] ); ?>"
                                 data-do="<?php echo esc_attr( $g['do'] ); ?>">
                                <span class="kanc-hours-row__day">
                                    <span class="kanc-today-dot" aria-label="Dziś" style="display:none"></span>
                                    <?php echo esc_html( $g['dzien'] ); ?>
                                </span>
                                <span class="kanc-hours-row__time">
                                    <?php echo esc_html( $g['od'] ) . ' – ' . esc_html( $g['do'] ); ?>
                                </span>
                                <?php if ( ! empty( $g['uwaga'] ) ) : ?>
                                <span class="kanc-hours-row__note"><?php echo esc_html( $g['uwaga'] ); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            <script>
                            (function(){
                                var dni = ['Niedziela','Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota'];
                                var dzis = dni[ new Date().getDay() ];
                                document.querySelectorAll('.kanc-hours-row').forEach(function(row){
                                    if (row.dataset.dzien === dzis) {
                                        row.classList.add('kanc-hours-row--today');
                                        var dot = row.querySelector('.kanc-today-dot');
                                        if (dot) dot.style.display = '';
                                    }
                                });
                            })();
                            </script>
                        </div>
                        <?php if ( $uwaga_global ) : ?>
                        <p class="kanc-alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <?php
                            $alert_text = esc_html( $uwaga_global );
                            $alert_text = preg_replace(
                                '/Pierwszy Piątek Miesiąca/u',
                                '<strong>Pierwszy Piątek Miesiąca</strong>',
                                $alert_text
                            );
                            echo $alert_text;
                            ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dane kontaktowe -->
                <div class="kanc-card">
                    <div class="kanc-card__header">
                        <svg class="kanc-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                        <h2 class="kanc-card__title">Dane kontaktowe</h2>
                    </div>
                    <div class="kanc-card__body">
                        <ul class="kanc-contact-list">
                            <li class="kanc-contact-list__item">
                                <span class="kanc-contact-list__label">Adres</span>
                                <?php
                                $adres_encoded_google = urlencode( $adres );
                                $adres_encoded_apple  = urlencode( $adres );
                                $google_url = "https://www.google.com/maps/search/?api=1&query=" . $adres_encoded_google;
                                $apple_url  = "https://maps.apple.com/?q=" . $adres_encoded_apple;
                                ?>
                                <a href="<?php echo esc_url( $google_url ); ?>"
                                   class="kanc-contact-list__val kanc-link kanc-map-link"
                                   data-google="<?php echo esc_url( $google_url ); ?>"
                                   data-apple="<?php echo esc_url( $apple_url ); ?>"
                                   target="_blank" rel="noopener"
                                   aria-label="Otwórz adres w mapach">
                                    <?php echo nl2br( esc_html( $adres ) ); ?>
                                    <svg class="kanc-map-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                </a>
                                <script>
                                (function(){
                                    var a = document.querySelector('.kanc-map-link');
                                    if (!a) return;
                                    var ios = /iPad|iPhone|iPod/.test(navigator.userAgent)
                                           || (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);
                                    var mac = /Macintosh/.test(navigator.userAgent) && navigator.maxTouchPoints === 0;
                                    if (ios || mac) a.href = a.dataset.apple;
                                    else           a.href = a.dataset.google;
                                })();
                                </script>
                            </li>
                            <li class="kanc-contact-list__item">
                                <span class="kanc-contact-list__label">Telefon</span>
                                <a href="tel:<?php echo esc_attr( preg_replace('/[^0-9+]/', '', $telefon) ); ?>"
                                   class="kanc-contact-list__val kanc-link">
                                    <?php echo esc_html( $telefon ); ?>
                                </a>
                            </li>
                            <li class="kanc-contact-list__item">
                                <span class="kanc-contact-list__label">E-mail</span>
                                <a href="mailto:<?php echo esc_attr( $email ); ?>"
                                   class="kanc-contact-list__val kanc-link">
                                    <?php echo esc_html( $email ); ?>
                                </a>
                            </li>
                            <?php if ( $nip ) : ?>
                            <li class="kanc-contact-list__item">
                                <span class="kanc-contact-list__label">NIP</span>
                                <span class="kanc-contact-list__val"><?php echo esc_html( $nip ); ?></span>
                            </li>
                            <?php endif; ?>
                            <?php if ( $regon ) : ?>
                            <li class="kanc-contact-list__item">
                                <span class="kanc-contact-list__label">REGON</span>
                                <span class="kanc-contact-list__val"><?php echo esc_html( $regon ); ?></span>
                            </li>
                            <?php endif; ?>
                            <?php if ( $konto ) : ?>
                            <li class="kanc-contact-list__item">
                                <span class="kanc-contact-list__label">Konto bankowe</span>
                                <span class="kanc-contact-list__val kanc-konto"><?php echo esc_html( $konto ); ?></span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

            </div><!-- /.kanc-col -->


        </div><!-- /.kanc-grid -->
    </div><!-- /.container -->


    <!-- Wsparcie -->
    <div class="container">
        <div class="kanc-wsparcie">
            <div class="kanc-wsparcie__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </div>
            <div class="kanc-wsparcie__body">
                <h2 class="kanc-wsparcie__title">Wesprzyj parafię</h2>
                <p class="kanc-wsparcie__desc">
                    Jeśli chcesz wesprzeć działalność naszej parafii, możesz przekazać darowiznę przelewem bankowym.
                    Każda wpłata pomaga w utrzymaniu kościoła i realizacji inicjatyw duszpasterskich. Dziękujemy!
                </p>
                <div class="kanc-konto-box">
                    <div class="kanc-konto-box__row">
                        <span class="kanc-konto-box__label">Odbiorca</span>
                        <span class="kanc-konto-box__val"><?php echo esc_html( pm_get( 'parafia_nazwa', 'Parafia Rzymskokatolicka pw. św. Teresy od Dzieciątka Jezus w Mrozach' ) ); ?></span>
                    </div>
                    <div class="kanc-konto-box__row">
                        <span class="kanc-konto-box__label">Bank</span>
                        <span class="kanc-konto-box__val"><?php echo esc_html( pm_get( 'parafia_bank',  'Bank Spółdzielczy w Mrozach' ) ); ?></span>
                    </div>
                    <div class="kanc-konto-box__row kanc-konto-box__row--konto">
                        <span class="kanc-konto-box__label">Nr konta</span>
                        <span class="kanc-konto-box__val kanc-konto-box__iban" id="kanc-iban"><?php echo esc_html( pm_get( 'parafia_konto', '58 9227 0004 0000 0808 2000 0010' ) ); ?></span>
                        <button type="button" class="kanc-copy-btn" id="kanc-copy-btn"
                                onclick="(function(){
                                    var t = document.getElementById('kanc-iban').textContent;
                                    navigator.clipboard.writeText(t).then(function(){
                                        var b = document.getElementById('kanc-copy-btn');
                                        b.classList.add('copied');
                                        b.querySelector('.kanc-copy-label').textContent = 'Skopiowano!';
                                        setTimeout(function(){ b.classList.remove('copied'); b.querySelector('.kanc-copy-label').textContent = 'Kopiuj'; }, 2000);
                                    });
                                })()"
                                aria-label="Kopiuj numer konta">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                            <span class="kanc-copy-label">Kopiuj</span>
                        </button>
                    </div>
                    <div class="kanc-konto-box__row">
                        <span class="kanc-konto-box__label">Tytuł przelewu</span>
                        <span class="kanc-konto-box__val" style="color:var(--color-text-light);font-style:italic;">Darowizna na cele kultu religijnego</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<?php get_footer(); ?>
