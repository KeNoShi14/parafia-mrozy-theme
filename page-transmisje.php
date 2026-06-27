<?php
get_header();

$pid = get_the_ID();

$default = json_encode([
    [
        'kategoria' => 'Polska',
        'nazwa'     => 'Jasna Góra — Częstochowa',
        'opis'      => 'Sanktuarium Matki Bożej Częstochowskiej',
        'kanal_id'  => 'UCc5GDaJiPYHBjEaVCSMmMKQ',
        'kanal_url' => 'https://www.youtube.com/@JasnaGora_Czestochowa',
    ],
    [
        'kategoria' => 'Polska',
        'nazwa'     => 'Licheń — Bazylika NMP',
        'opis'      => 'Największa bazylika w Polsce',
        'kanal_id'  => 'UCWtbBAHzpcJm1RkBDLXFNGg',
        'kanal_url' => 'https://www.youtube.com/@SanktLichen',
    ],
    [
        'kategoria' => 'Polska',
        'nazwa'     => 'Niepokalanów',
        'opis'      => 'Franciszkański klasztor św. Maksymiliana Kolbego',
        'kanal_id'  => 'UCMsLbbnOkD-tH5ExbxJLCKA',
        'kanal_url' => 'https://www.youtube.com/@Niepokalanow',
    ],
    [
        'kategoria' => 'Polska',
        'nazwa'     => 'Boże Miłosierdzie — Kraków Łagiewniki',
        'opis'      => 'Miejsce objawień św. Faustyny',
        'kanal_id'  => 'UCpko5nF9OQHB3U0vMZq8YgQ',
        'kanal_url' => 'https://www.youtube.com/@DivineMercySanctuary',
    ],
    [
        'kategoria' => 'Polska',
        'nazwa'     => 'Kalwaria Zebrzydowska',
        'opis'      => 'Sanktuarium Pasji Pańskiej i Matki Bożej',
        'kanal_id'  => 'UCXXXkalwariaXXX',
        'kanal_url' => 'https://www.youtube.com/@KalwariaZebrzydowska',
    ],
    [
        'kategoria' => 'Świat',
        'nazwa'     => 'Watykan — Bazylika św. Piotra',
        'opis'      => 'Transmisje papieskie z Watykanu',
        'kanal_id'  => 'UC8I0fCFwt-h4h2HulcHBD4g',
        'kanal_url' => 'https://www.youtube.com/@vatican',
    ],
    [
        'kategoria' => 'Świat',
        'nazwa'     => 'Fatima — Portugalia',
        'opis'      => 'Sanktuarium Matki Bożej Fatimskiej',
        'kanal_id'  => 'UCXXXfatimaXXXXXXXXXXXXX',
        'kanal_url' => 'https://www.youtube.com/@SantuariodeFatima',
    ],
    [
        'kategoria' => 'Świat',
        'nazwa'     => 'Lourdes — Francja',
        'opis'      => 'Sanktuarium Matki Bożej z Lourdes',
        'kanal_id'  => 'UCXXXlourdesXXXXXXXXXXXX',
        'kanal_url' => 'https://www.youtube.com/@LourdesOfficiel',
    ],
    [
        'kategoria' => 'Świat',
        'nazwa'     => 'Medjugorie',
        'opis'      => 'Parafia św. Jakuba w Medjugoriu',
        'kanal_id'  => 'UCXXXmedjugorieXXXXXXXXX',
        'kanal_url' => 'https://www.youtube.com/@medjugorje',
    ],
], JSON_UNESCAPED_UNICODE);

$sanktuaria_json = get_post_meta( $pid, '_trans_sanktuaria', true ) ?: $default;
$sanktuaria = json_decode( $sanktuaria_json, true ) ?: [];

$polska = array_values( array_filter( $sanktuaria, fn($s) => $s['kategoria'] === 'Polska' ) );
$swiat  = array_values( array_filter( $sanktuaria, fn($s) => $s['kategoria'] === 'Świat' ) );

$aktywna_kat = isset($_GET['kat']) && $_GET['kat'] === 'swiat' ? 'swiat' : 'polska';
?>

<main id="primary" class="site-main" role="main">

    <div class="trans-hero">
        <div class="container">
            <p class="trans-hero__eyebrow">Parafia pw. św. Teresy od Dzieciątka Jezus</p>
            <h1 class="trans-hero__title">Transmisje Mszy Świętych</h1>
            <p class="trans-hero__subtitle">Dołącz do Mszy Świętej transmitowanej na żywo z sanktuariów w Polsce i na świecie.</p>
        </div>
    </div>

    <div class="container">

        <div class="trans-tabs">
            <a href="?kat=polska" class="trans-tab <?php echo $aktywna_kat === 'polska' ? 'trans-tab--active' : ''; ?>">
                <span class="trans-tab__flag">🇵🇱</span> Polska
                <span class="trans-tab__count"><?php echo count($polska); ?></span>
            </a>
            <a href="?kat=swiat" class="trans-tab <?php echo $aktywna_kat === 'swiat' ? 'trans-tab--active' : ''; ?>">
                <span class="trans-tab__flag">🌍</span> Świat
                <span class="trans-tab__count"><?php echo count($swiat); ?></span>
            </a>
        </div>

        <div class="trans-grid">
            <?php
            $lista = $aktywna_kat === 'polska' ? $polska : $swiat;
            foreach ( $lista as $s ) :
                $live_url  = $s['live_url'] ?? '';
                $kanal_id  = $s['kanal_id'] ?? '';
                $ma_kanal  = ! empty( $kanal_id ) || ! empty( $live_url );

                if ( ! empty( $live_url ) ) {
                    preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $live_url, $m);
                    $video_id  = $m[1] ?? '';
                    $embed_url = $video_id
                        ? 'https://www.youtube.com/embed/' . $video_id . '?autoplay=0&rel=0'
                        : esc_url( $live_url );
                } elseif ( ! empty( $kanal_id ) ) {
                    $embed_url = 'https://www.youtube.com/embed/live_stream?channel=' . esc_attr( $kanal_id ) . '&autoplay=0&rel=0';
                } else {
                    $embed_url = '';
                }
            ?>
            <div class="trans-card">

                <?php if ( $ma_kanal ) :
                    $thumb_id = '';
                    if ( ! empty( $live_url ) && ! empty( $video_id ) ) {
                        $thumb_id = $video_id;
                    } elseif ( ! empty( $kanal_id ) ) {
                        $thumb_id = $kanal_id;
                    }
                    $thumb_url = ! empty( $video_id )
                        ? 'https://i.ytimg.com/vi/' . esc_attr( $video_id ) . '/maxresdefault.jpg'
                        : '';
                ?>
                <!-- Fasada — miniatura + play, iframe ładuje się po kliknięciu -->
                <div class="trans-facade"
                     data-embed="<?php echo esc_attr( $embed_url ); ?>"
                     data-kanal="<?php echo esc_url( ! empty($s['kanal_url']) ? $s['kanal_url'] : '#' ); ?>"
                     role="button"
                     tabindex="0"
                     aria-label="Odtwórz transmisję <?php echo esc_attr( $s['nazwa'] ); ?>">

                    <?php if ( $thumb_url ) : ?>
                    <img class="trans-facade__thumb"
                         src="<?php echo esc_url( $thumb_url ); ?>"
                         alt="<?php echo esc_attr( $s['nazwa'] ); ?>"
                         loading="lazy"
                         onerror="this.style.display='none'">
                    <?php endif; ?>

                    <div class="trans-facade__overlay">
                        <div class="trans-facade__play" aria-hidden="true">
                            <svg viewBox="0 0 68 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M66.52 7.74C65.7 4.72 63.28 2.3 60.26 1.48 54.99 0 34 0 34 0S13.01 0 7.74 1.48C4.72 2.3 2.3 4.72 1.48 7.74 0 13.01 0 24 0 24s0 10.99 1.48 16.26c.82 3.02 3.24 5.44 6.26 6.26C13.01 48 34 48 34 48s20.99 0 26.26-1.48c3.02-.82 5.44-3.24 6.26-6.26C68 34.99 68 24 68 24S68 13.01 66.52 7.74z" fill="#FF0000"/>
                                <path d="M27 34l18-10-18-10v20z" fill="#fff"/>
                            </svg>
                        </div>
                        <p class="trans-facade__label">Kliknij aby odtworzyć</p>
                    </div>

                </div>
                <?php else : ?>
                <div class="trans-card__placeholder">
                    <div class="trans-placeholder__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M2 12h20"/>
                        </svg>
                    </div>
                    <p class="trans-placeholder__text">Brak ID kanału</p>
                    <p class="trans-placeholder__hint">Uzupełnij ID kanału YouTube w ustawieniach strony</p>
                </div>
                <?php endif; ?>

                <div class="trans-card__body">
                    <div class="trans-card__meta">
                        <h3 class="trans-card__nazwa"><?php echo esc_html( $s['nazwa'] ); ?></h3>
                        <p class="trans-card__opis"><?php echo esc_html( $s['opis'] ); ?></p>
                    </div>
                    <?php if ( ! empty( $s['kanal_url'] ) ) : ?>
                    <a href="<?php echo esc_url( $s['kanal_url'] ); ?>"
                       class="trans-card__link"
                       target="_blank" rel="noopener"
                       aria-label="Przejdź do kanału <?php echo esc_attr( $s['nazwa'] ); ?>">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/>
                        </svg>
                        Kanał YouTube
                    </a>
                    <?php endif; ?>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <div class="trans-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Gdy sanktuarium transmituje Mszę Świętą na żywo, player automatycznie ją wyświetli. Poza godzinami transmisji widoczny jest ostatni opublikowany film lub strona kanału.
        </div>

    </div>

</main>

<script>
(function(){
    document.querySelectorAll('.trans-facade').forEach(function(el){
        function activate() {
            var embedUrl = el.dataset.embed;
            if (!embedUrl) {
                window.open(el.dataset.kanal, '_blank');
                return;
            }
            var wrap = document.createElement('div');
            wrap.className = 'trans-player-wrap';
            var iframe = document.createElement('iframe');
            iframe.src = embedUrl + '&autoplay=1';
            iframe.title = el.getAttribute('aria-label') || '';
            iframe.frameBorder = '0';
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
            iframe.allowFullscreen = true;
            wrap.appendChild(iframe);

            iframe.onerror = function() {
                window.open(el.dataset.kanal, '_blank');
            };

            el.parentNode.replaceChild(wrap, el);
        }

        el.addEventListener('click', activate);
        el.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(); }
        });
    });
})();
</script>

<?php get_footer(); ?>
