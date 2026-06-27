<?php
get_header();

$prob_imie = get_post_meta( get_the_ID(), '_kaplani_prob_imie', true ) ?: 'Ks. Krzysztof Pietrzak';
$prob_od   = get_post_meta( get_the_ID(), '_kaplani_prob_od',   true ) ?: '6 lipca 2013';
$wik_imie  = trim( (string) get_post_meta( get_the_ID(), '_kaplani_wik_imie',  true ) );
$wik_od    = trim( (string) get_post_meta( get_the_ID(), '_kaplani_wik_od',    true ) );
$rez_imie  = trim( (string) get_post_meta( get_the_ID(), '_kaplani_rez_imie',  true ) );
$rez_od    = trim( (string) get_post_meta( get_the_ID(), '_kaplani_rez_od',    true ) );

$foto_prob_id  = (int) get_post_meta( get_the_ID(), '_kaplani_foto_proboszcz_id', true );
$foto_wik_id   = (int) get_post_meta( get_the_ID(), '_kaplani_foto_wikariusz_id', true );
$foto_rez_id   = (int) get_post_meta( get_the_ID(), '_kaplani_foto_rezydent_id',  true );
$foto_prob_url = $foto_prob_id ? wp_get_attachment_image_url( $foto_prob_id, 'large' ) : '';
$foto_wik_url  = $foto_wik_id  ? wp_get_attachment_image_url( $foto_wik_id,  'large' ) : '';
$foto_rez_url  = $foto_rez_id  ? wp_get_attachment_image_url( $foto_rez_id,  'large' ) : '';

// Karty obecnych wyświetlamy tylko gdy podane jest imię i nazwisko.
$ma_wikariusza = ( $wik_imie !== '' );
$ma_rezydenta  = ( $rez_imie !== '' );

$default_prob = json_encode([
    [ 'imie' => 'Ks. Jankowski Eugeniusz',       'od' => '07.12.2008', 'do' => '06.07.2013', 'zmarl' => false ],
    [ 'imie' => 'Ks. Kasprzak Marek',            'od' => '24.06.1999', 'do' => '07.12.2008', 'zmarl' => false ],
    [ 'imie' => 'Ks. Nowosielski Wiesław',       'od' => '07.02.1986', 'do' => '24.06.1999', 'zmarl' => false ],
    [ 'imie' => 'Ks. Tartas Tadeusz',            'od' => '14.02.1980', 'do' => '03.02.1986', 'zmarl' => true  ],
    [ 'imie' => 'Ks. Kajtaniak Marian',          'od' => '05.08.1965', 'do' => '14.02.1980', 'zmarl' => false ],
    [ 'imie' => 'Ks. Mińkowski Bogumił Marian',  'od' => '29.04.1956', 'do' => '05.08.1965', 'zmarl' => false ],
    [ 'imie' => 'Ks. Kaplewski Paweł Marian',    'od' => '31.10.1948', 'do' => '16.03.1956', 'zmarl' => false ],
    [ 'imie' => 'Ks. Fertak Kazimierz',          'od' => '03.02.1941', 'do' => '10.06.1948', 'zmarl' => true  ],
    [ 'imie' => 'Ks. Dobrowolski Leon',          'od' => '01.09.1939', 'do' => '03.02.1941', 'zmarl' => false ],
    [ 'imie' => 'Ks. Lejchte Zdzisław',          'od' => '27.10.1934', 'do' => '01.09.1939', 'zmarl' => false ],
    [ 'imie' => 'Ks. Kołodziejski Jan',          'od' => '01.04.1931', 'do' => '27.10.1934', 'zmarl' => false ],
], JSON_UNESCAPED_UNICODE);

$default_wik = json_encode([
    [ 'imie' => 'Ks. Bodziak Marcin',       'od' => '26.08.2018', 'do' => '26.08.2021', 'zmarl' => false ],
    [ 'imie' => 'Ks. Ziółkowski Robert',    'od' => '28.08.2011', 'do' => '26.08.2018', 'zmarl' => false ],
    [ 'imie' => 'Ks. Kołbuk Jacek',         'od' => '01.07.2007', 'do' => '28.08.2011', 'zmarl' => false ],
    [ 'imie' => 'Ks. Zdanowicz Marek',      'od' => '02.09.2004', 'do' => '01.07.2007', 'zmarl' => false ],
    [ 'imie' => 'Ks. Skowyra Zbigniew',     'od' => '28.06.2003', 'do' => '02.09.2004', 'zmarl' => false ],
    [ 'imie' => 'Ks. Zieliński Paweł',      'od' => '29.06.2002', 'do' => '28.06.2003', 'zmarl' => false ],
    [ 'imie' => 'Ks. Sobczak Sławomir',     'od' => '26.08.2000', 'do' => '02.07.2002', 'zmarl' => false ],
    [ 'imie' => 'Ks. Budzyński Krzysztof',  'od' => '01.07.1999', 'do' => '26.08.2000', 'zmarl' => false ],
    [ 'imie' => 'Ks. Cymerman Paweł',       'od' => '25.05.1996', 'do' => '24.06.1999', 'zmarl' => true  ],
    [ 'imie' => 'Ks. Główka Piotr',         'od' => '28.05.1995', 'do' => '22.06.1996', 'zmarl' => false ],
    [ 'imie' => 'Ks. Sałański Bogdan',      'od' => '22.06.1992', 'do' => '22.06.1995', 'zmarl' => false ],
    [ 'imie' => 'Ks. Osiadacz Tomasz',      'od' => '21.05.1989', 'do' => '22.06.1992', 'zmarl' => false ],
    [ 'imie' => 'Ks. Korbik Dariusz Edward','od' => '10.06.1987', 'do' => '15.06.1989', 'zmarl' => false ],
    [ 'imie' => 'Ks. Kapłański Zbigniew',   'od' => '16.06.1986', 'do' => '10.06.1987', 'zmarl' => false ],
    [ 'imie' => 'Ks. Osiński Wacław',       'od' => '13.06.1984', 'do' => '16.06.1986', 'zmarl' => false ],
    [ 'imie' => 'Ks. Krześniak Leszek',     'od' => '10.06.1983', 'do' => '13.06.1984', 'zmarl' => false ],
    [ 'imie' => 'Ks. Grędowski Władysław',  'od' => '03.06.1980', 'do' => '10.06.1983', 'zmarl' => false ],
], JSON_UNESCAPED_UNICODE);

$lista_prob = json_decode( get_post_meta( get_the_ID(), '_kaplani_lista_proboszczow', true ) ?: $default_prob, true ) ?: [];
$lista_wik  = json_decode( get_post_meta( get_the_ID(), '_kaplani_lista_wikariuszy',  true ) ?: $default_wik,  true ) ?: [];

function kaplani_inicjaly( $imie ) {
    $parts = explode( ' ', trim( preg_replace('/Ks\.\s*/u', '', $imie) ) );
    $out = '';
    foreach ( array_slice( $parts, 0, 2 ) as $p ) $out .= mb_strtoupper( mb_substr( $p, 0, 1 ) );
    return $out ?: '?';
}
?>

<main id="primary" class="site-main" role="main">

    <div class="kaplani-hero">
        <div class="container">
            <p class="kaplani-hero__eyebrow">Parafia pw. św. Teresy od Dzieciątka Jezus</p>
            <h1 class="kaplani-hero__title">Kapłani Parafii</h1>
            <p class="kaplani-hero__subtitle">Duchowni posługujący w parafii Mrozy od jej erygowania w 1931 roku.</p>
        </div>
    </div>

    <div class="container">

        <!-- ══ OBECNI KAPŁANI ══ -->
        <section class="kaplani-section" aria-labelledby="heading-obecni">
            <h2 class="kaplani-section__title" id="heading-obecni">Obecni kapłani</h2>
            <div class="kaplani-current-grid">

                <!-- Proboszcz -->
                <div class="kaplani-card kaplani-card--proboszcz">
                    <div class="kaplani-card__photo-wrap">
                        <?php if ( $foto_prob_url ) : ?>
                            <img src="<?php echo esc_url( $foto_prob_url ); ?>"
                                 alt="<?php echo esc_attr( $prob_imie ); ?> — Proboszcz"
                                 class="kaplani-card__photo" loading="lazy">
                        <?php else : ?>
                            <div class="kaplani-card__photo-placeholder" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" fill="none">
                                    <circle cx="40" cy="30" r="16" fill="currentColor" opacity=".25"/>
                                    <path d="M10 72c0-16.569 13.431-30 30-30s30 13.431 30 30" fill="currentColor" opacity=".15"/>
                                </svg>
                                <span>Brak zdjęcia</span>
                            </div>
                        <?php endif; ?>
                        <span class="kaplani-card__role-badge">Proboszcz</span>
                    </div>
                    <div class="kaplani-card__body">
                        <h3 class="kaplani-card__name"><?php echo esc_html( $prob_imie ); ?></h3>
                        <p class="kaplani-card__since">posługuje od <strong><?php echo esc_html( $prob_od ); ?></strong></p>
                    </div>
                </div>

                <!-- Wikariusz -->
                <?php if ( $ma_wikariusza ) : ?>
                <div class="kaplani-card kaplani-card--wikariusz">
                    <div class="kaplani-card__photo-wrap">
                        <?php if ( $foto_wik_url ) : ?>
                            <img src="<?php echo esc_url( $foto_wik_url ); ?>"
                                 alt="<?php echo esc_attr( $wik_imie ); ?> — Wikariusz"
                                 class="kaplani-card__photo" loading="lazy">
                        <?php else : ?>
                            <div class="kaplani-card__photo-placeholder" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" fill="none">
                                    <circle cx="40" cy="30" r="16" fill="currentColor" opacity=".25"/>
                                    <path d="M10 72c0-16.569 13.431-30 30-30s30 13.431 30 30" fill="currentColor" opacity=".15"/>
                                </svg>
                                <span>Brak zdjęcia</span>
                            </div>
                        <?php endif; ?>
                        <span class="kaplani-card__role-badge kaplani-card__role-badge--wik">Wikariusz</span>
                    </div>
                    <div class="kaplani-card__body">
                        <h3 class="kaplani-card__name"><?php echo esc_html( $wik_imie ); ?></h3>
                        <?php if ( $wik_od !== '' ) : ?>
                        <p class="kaplani-card__since">posługuje od <strong><?php echo esc_html( $wik_od ); ?></strong></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Rezydent -->
                <?php if ( $ma_rezydenta ) : ?>
                <div class="kaplani-card kaplani-card--rezydent">
                    <div class="kaplani-card__photo-wrap">
                        <?php if ( $foto_rez_url ) : ?>
                            <img src="<?php echo esc_url( $foto_rez_url ); ?>"
                                 alt="<?php echo esc_attr( $rez_imie ); ?> — Rezydent"
                                 class="kaplani-card__photo" loading="lazy">
                        <?php else : ?>
                            <div class="kaplani-card__photo-placeholder" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" fill="none">
                                    <circle cx="40" cy="30" r="16" fill="currentColor" opacity=".25"/>
                                    <path d="M10 72c0-16.569 13.431-30 30-30s30 13.431 30 30" fill="currentColor" opacity=".15"/>
                                </svg>
                                <span>Brak zdjęcia</span>
                            </div>
                        <?php endif; ?>
                        <span class="kaplani-card__role-badge kaplani-card__role-badge--rez">Rezydent</span>
                    </div>
                    <div class="kaplani-card__body">
                        <h3 class="kaplani-card__name"><?php echo esc_html( $rez_imie ); ?></h3>
                        <?php if ( $rez_od !== '' ) : ?>
                        <p class="kaplani-card__since">posługuje od <strong><?php echo esc_html( $rez_od ); ?></strong></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </section>

        <!-- ══ FILTR ══ -->
        <?php
        $wszystkie_lata = [ 1931, (int) date('Y') ];
        foreach ( array_merge( $lista_prob, $lista_wik ) as $k ) {
            if ( preg_match('/(\d{4})/', $k['od'], $m) ) $wszystkie_lata[] = (int) $m[1];
            if ( ! empty($k['do']) && preg_match('/(\d{4})/', $k['do'], $m) ) $wszystkie_lata[] = (int) $m[1];
        }
        $rok_min = min( $wszystkie_lata );
        $rok_max = max( $wszystkie_lata );
        ?>

        <section class="kaplani-section kaplani-section--filter" aria-label="Filtrowanie po latach">
            <div class="kf-bar">
                <div class="kf-bar__label">
                    <span>Filtruj po latach posługi</span>
                    <button type="button" class="kf-reset" id="kf-reset">Pokaż wszystkich</button>
                </div>
                <div class="kf-slider-wrap" aria-label="Zakres lat">
                    <div class="kf-track" id="kf-track">
                        <div class="kf-range-fill" id="kf-fill"></div>
                        <input type="range" class="kf-thumb kf-thumb--min" id="kf-min"
                               min="<?php echo $rok_min; ?>" max="<?php echo $rok_max; ?>"
                               value="<?php echo $rok_min; ?>"
                               aria-label="Rok od">
                        <input type="range" class="kf-thumb kf-thumb--max" id="kf-max"
                               min="<?php echo $rok_min; ?>" max="<?php echo $rok_max; ?>"
                               value="<?php echo $rok_max; ?>"
                               aria-label="Rok do">
                    </div>
                    <div class="kf-labels">
                        <span class="kf-label--min" id="kf-label-min"><?php echo $rok_min; ?></span>
                        <span class="kf-label--max" id="kf-label-max"><?php echo $rok_max; ?></span>
                    </div>
                </div>
                <p class="kf-count" id="kf-count" aria-live="polite"></p>
            </div>
        </section>

        <!-- ══ PROBOSZCZOWIE ══ -->
        <section class="kaplani-section" aria-labelledby="heading-prob" id="section-prob">
            <h2 class="kaplani-section__title" id="heading-prob">Proboszczowie</h2>
            <div class="kaplani-list" role="list" id="list-prob">
                <?php foreach ( $lista_prob as $k ) :
                    $init = kaplani_inicjaly( $k['imie'] );
                    $daty = esc_html( $k['od'] ) . ( $k['do'] ? ' – ' . esc_html( $k['do'] ) : '' );
                    preg_match('/(\d{4})/', $k['od'], $m_od);
                    preg_match('/(\d{4})/', $k['do'] ?? '', $m_do);
                    $year_od = isset($m_od[1]) ? (int)$m_od[1] : $rok_min;
                    $year_do = isset($m_do[1]) ? (int)$m_do[1] : $rok_max;
                ?>
                <div class="kaplani-row<?php echo $k['zmarl'] ? ' kaplani-row--deceased' : ''; ?>"
                     role="listitem"
                     data-od="<?php echo $year_od; ?>"
                     data-do="<?php echo $year_do; ?>">
                    <div class="kaplani-row__avatar" aria-hidden="true">
                        <?php echo $k['zmarl'] ? '†' : esc_html( $init ); ?>
                    </div>
                    <div class="kaplani-row__body">
                        <span class="kaplani-row__name">
                            <?php if ( $k['zmarl'] ) echo '<span class="kaplani-dagger" aria-label="śp.">† </span>'; ?>
                            <?php echo esc_html( $k['imie'] ); ?>
                        </span>
                    </div>
                    <div class="kaplani-row__dates"><?php echo $daty; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="kaplani-empty" id="empty-prob" hidden>Brak proboszczów w wybranym przedziale lat.</p>
        </section>

        <!-- ══ WIKARIUSZE ══ -->
        <section class="kaplani-section" aria-labelledby="heading-wik" id="section-wik">
            <h2 class="kaplani-section__title" id="heading-wik">Wikariusze</h2>
            <div class="kaplani-list" role="list" id="list-wik">
                <?php foreach ( $lista_wik as $k ) :
                    $init = kaplani_inicjaly( $k['imie'] );
                    $daty = esc_html( $k['od'] ) . ( $k['do'] ? ' – ' . esc_html( $k['do'] ) : '' );
                    preg_match('/(\d{4})/', $k['od'], $m_od);
                    preg_match('/(\d{4})/', $k['do'] ?? '', $m_do);
                    $year_od = isset($m_od[1]) ? (int)$m_od[1] : $rok_min;
                    $year_do = isset($m_do[1]) ? (int)$m_do[1] : $rok_max;
                ?>
                <div class="kaplani-row<?php echo $k['zmarl'] ? ' kaplani-row--deceased' : ''; ?>"
                     role="listitem"
                     data-od="<?php echo $year_od; ?>"
                     data-do="<?php echo $year_do; ?>">
                    <div class="kaplani-row__avatar" aria-hidden="true">
                        <?php echo $k['zmarl'] ? '†' : esc_html( $init ); ?>
                    </div>
                    <div class="kaplani-row__body">
                        <span class="kaplani-row__name">
                            <?php if ( $k['zmarl'] ) echo '<span class="kaplani-dagger" aria-label="śp.">† </span>'; ?>
                            <?php echo esc_html( $k['imie'] ); ?>
                        </span>
                    </div>
                    <div class="kaplani-row__dates"><?php echo $daty; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="kaplani-empty" id="empty-wik" hidden>Brak wikariuszy w wybranym przedziale lat.</p>
        </section>

    </div>
</main>

<script>
(function() {
    var minInput  = document.getElementById('kf-min');
    var maxInput  = document.getElementById('kf-max');
    var fill      = document.getElementById('kf-fill');
    var labelMin  = document.getElementById('kf-label-min');
    var labelMax  = document.getElementById('kf-label-max');
    var countEl   = document.getElementById('kf-count');
    var resetBtn  = document.getElementById('kf-reset');

    var absMin = parseInt(minInput.min);
    var absMax = parseInt(maxInput.max);

    function pct(val) {
        return ((val - absMin) / (absMax - absMin)) * 100;
    }

    function updateFill() {
        var lo = pct(parseInt(minInput.value));
        var hi = pct(parseInt(maxInput.value));
        fill.style.left  = lo + '%';
        fill.style.width = (hi - lo) + '%';
    }

    function filterRows() {
        var from = parseInt(minInput.value);
        var to   = parseInt(maxInput.value);
        var total = 0, visible = 0;

        labelMin.textContent = from;
        labelMax.textContent = to;
        updateFill();

        ['prob', 'wik'].forEach(function(group) {
            var list    = document.getElementById('list-' + group);
            var empty   = document.getElementById('empty-' + group);
            var rows    = list ? list.querySelectorAll('.kaplani-row') : [];
            var shown   = 0;

            rows.forEach(function(row) {
                var od = parseInt(row.dataset.od);
                var doo = parseInt(row.dataset.do);
                var match = od <= to && doo >= from;
                row.style.display = match ? '' : 'none';
                if (match) { shown++; visible++; }
                total++;
            });

            if (empty) empty.hidden = (shown > 0);
        });

        var isFiltered = (from > absMin || to < absMax);
        countEl.textContent = isFiltered
            ? 'Wyświetlono ' + visible + ' kapłanów posługujących w latach ' + from + '–' + to
            : '';
        resetBtn.style.display = isFiltered ? 'inline-block' : 'none';
    }

    minInput.addEventListener('input', function() {
        if (parseInt(minInput.value) > parseInt(maxInput.value) - 1)
            minInput.value = parseInt(maxInput.value) - 1;
        filterRows();
    });

    maxInput.addEventListener('input', function() {
        if (parseInt(maxInput.value) < parseInt(minInput.value) + 1)
            maxInput.value = parseInt(minInput.value) + 1;
        filterRows();
    });

    resetBtn.addEventListener('click', function() {
        minInput.value = absMin;
        maxInput.value = absMax;
        filterRows();
    });

    updateFill();
    resetBtn.style.display = 'none';
})();
</script>

<?php get_footer(); ?>
