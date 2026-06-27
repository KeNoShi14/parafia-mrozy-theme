<?php
get_header();

$pid = get_the_ID();

$intro = get_post_meta( $pid, '_chorzy_intro', true )
    ?: 'Kościół otacza szczególną troską osoby chore i starsze, które nie mogą uczestniczyć w życiu parafialnym. Kapłani regularnie odwiedzają chorych w domach, przynosząc Komunię Świętą, sakrament namaszczenia chorych oraz duchowe wsparcie.';

$default_wizyty = json_encode([
    [
        'termin'    => 'Pierwszy Czwartek Miesiąca',
        'miejscowosc' => 'Grodzisk',
        'od'        => '10:00',
        'kaplán'    => 'Ks. Proboszcz',
        'uwaga'     => '',
    ],
    [
        'termin'    => 'Pierwszy Piątek Miesiąca',
        'miejscowosc' => 'Mrozy',
        'od'        => '10:00',
        'kaplán'    => 'Ks. Proboszcz',
        'uwaga'     => '',
    ],
    [
        'termin'    => 'Pierwsza Sobota Miesiąca',
        'miejscowosc' => 'Cała parafia',
        'od'        => '10:00',
        'kaplán'    => 'Ks. Wikariusz',
        'uwaga'     => 'Wizyty zgłaszane indywidualnie przyjmuje również ks. Proboszcz',
    ],
], JSON_UNESCAPED_UNICODE);

$wizyty_json = get_post_meta( $pid, '_chorzy_wizyty', true ) ?: $default_wizyty;
$wizyty = json_decode( $wizyty_json, true ) ?: [];

$zgloszenie = get_post_meta( $pid, '_chorzy_zgloszenie', true )
    ?: 'Aby zgłosić chorego do wizyty, prosimy o kontakt z kancelarią parafialną osobiście lub telefonicznie. Można również zgłosić się bezpośrednio do księdza po Mszy Świętej.';

$telefon = esc_html( pm_get( 'parafia_phone', '25 757 41 93' ) );
$email   = sanitize_email( get_theme_mod( 'parafia_email', 'parafiamrozy@gmail.com' ) );

$ikony_termin = [
    'Czwartek' => '4',
    'Piątek'   => '5',
    'Sobota'   => '6',
];
?>

<main id="primary" class="site-main" role="main">

    <!-- Hero -->
    <div class="chorzy-hero">
        <div class="container">
            <p class="chorzy-hero__eyebrow">Parafia pw. św. Teresy od Dzieciątka Jezus</p>
            <h1 class="chorzy-hero__title">Wizyty u Chorych</h1>
            <p class="chorzy-hero__subtitle"><?php echo esc_html( $intro ); ?></p>
        </div>
    </div>

    <div class="container">
        <div class="chorzy-layout">

            <!-- Harmonogram wizyt -->
            <section class="chorzy-section" aria-labelledby="chorzy-harmonogram">
                <h2 class="chorzy-section__title" id="chorzy-harmonogram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Harmonogram wizyt
                </h2>

                <div class="chorzy-cards">
                    <?php foreach ( $wizyty as $i => $w ) : ?>
                    <div class="chorzy-card">
                        <div class="chorzy-card__num" aria-hidden="true"
                             data-termin="<?php echo esc_attr( $w['termin'] ); ?>"
                             id="chorzy-num-<?php echo $i; ?>">—</div>
                        <div class="chorzy-card__body">
                            <p class="chorzy-card__termin">
                                <?php
                                $t = esc_html( $w['termin'] );
                                $t = preg_replace(
                                    '/(Pierws[zy|za]+\s+(?:Czwartek|Piątek|Sobota|Sobotę))/u',
                                    '<strong>$1</strong>',
                                    $t
                                );
                                echo $t;
                                ?>
                            </p>
                            <div class="chorzy-card__meta">
                                <?php if ( $w['miejscowosc'] ) : ?>
                                <span class="chorzy-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <?php echo esc_html( $w['miejscowosc'] ); ?>
                                </span>
                                <?php endif; ?>
                                <?php if ( $w['od'] ) : ?>
                                <span class="chorzy-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    od <?php echo esc_html( $w['od'] ); ?>
                                </span>
                                <?php endif; ?>
                                <?php if ( $w['kaplán'] ) : ?>
                                <span class="chorzy-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <?php echo esc_html( $w['kaplán'] ); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if ( ! empty( $w['uwaga'] ) ) : ?>
                            <p class="chorzy-card__uwaga">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <?php echo esc_html( $w['uwaga'] ); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Jak zgłosić -->
            <section class="chorzy-section chorzy-section--zgloszenie" aria-labelledby="chorzy-zgloszenie">
                <h2 class="chorzy-section__title" id="chorzy-zgloszenie">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.82a16 16 0 0 0 6.29 6.29l.98-.98a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Jak zgłosić chorego
                </h2>
                <div class="chorzy-zgloszenie-box">
                    <p class="chorzy-zgloszenie-box__text"><?php echo esc_html( $zgloszenie ); ?></p>
                    <div class="chorzy-kontakt-row">
                        <a href="tel:<?php echo esc_attr( preg_replace('/[^0-9+]/', '', $telefon) ); ?>"
                           class="chorzy-kontakt-btn chorzy-kontakt-btn--tel">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.82a16 16 0 0 0 6.29 6.29l.98-.98a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <?php echo esc_html( $telefon ); ?>
                        </a>
                        <a href="mailto:<?php echo esc_attr( $email ); ?>"
                           class="chorzy-kontakt-btn chorzy-kontakt-btn--email">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <?php echo esc_html( $email ); ?>
                        </a>
                    </div>
                </div>
            </section>

        </div><!-- /.chorzy-layout -->
    </div><!-- /.container -->

</main>

<script>
(function(){
    var DAYS = {
        'Niedziela': 0, 'Poniedziałek': 1, 'Wtorek': 2,
        'Środa': 3, 'Czwartek': 4, 'Piątek': 5, 'Sobota': 6
    };

    function firstWeekdayOfMonth(targetDay, ref) {
        ref = ref || new Date();
        var d = new Date(ref.getFullYear(), ref.getMonth(), 1);
        while (d.getDay() !== targetDay) d.setDate(d.getDate() + 1);
        var today = new Date(ref.getFullYear(), ref.getMonth(), ref.getDate());
        if (d < today) {
            d = new Date(ref.getFullYear(), ref.getMonth() + 1, 1);
            while (d.getDay() !== targetDay) d.setDate(d.getDate() + 1);
        }
        return d;
    }

    var MONTHS = ['stycznia','lutego','marca','kwietnia','maja','czerwca',
                  'lipca','sierpnia','września','października','listopada','grudnia'];

    document.querySelectorAll('.chorzy-card__num[data-termin]').forEach(function(el) {
        var termin = el.dataset.termin;

        var dayName = null;
        Object.keys(DAYS).forEach(function(n) {
            if (termin.indexOf(n) !== -1) dayName = n;
        });
        if (dayName === null) return;

        var targetDay = DAYS[dayName];
        var date = firstWeekdayOfMonth(targetDay);

        el.innerHTML =
            '<span class="chorzy-num__day">' + date.getDate() + '</span>' +
            '<span class="chorzy-num__month">' + MONTHS[date.getMonth()] + '</span>';
    });
})();
</script>

<?php get_footer(); ?>
