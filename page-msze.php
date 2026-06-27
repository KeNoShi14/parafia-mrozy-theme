<?php
get_header();

$pid = get_the_ID();

function msze_get( $key, $theme_key, $fallback ) {
    $pid = get_the_ID();
    $val = get_post_meta( $pid, '_msze_' . $key, true );
    if ( $val !== '' && $val !== false ) return esc_html( $val );
    return pm_get( $theme_key, $fallback );
}

$default_powszednie = json_encode([
    [ 'godz' => '07:00', 'uwaga' => '' ],
    [ 'godz' => '18:00', 'uwaga' => '' ],
], JSON_UNESCAPED_UNICODE);

$default_niedzielne = json_encode([
    [ 'godz' => '07:00', 'uwaga' => '' ],
    [ 'godz' => '08:30', 'uwaga' => '' ],
    [ 'godz' => '10:00', 'uwaga' => 'Dla dzieci' ],
    [ 'godz' => '12:00', 'uwaga' => '' ],
    [ 'godz' => '18:00', 'uwaga' => '' ],
], JSON_UNESCAPED_UNICODE);

$default_spowiedz = json_encode([
    [ 'kiedy' => 'Dni powszednie',           'uwaga' => 'Przed każdą Mszą Świętą' ],
    [ 'kiedy' => 'Niedziela',                'uwaga' => 'Przed każdą Mszą Świętą' ],
    [ 'kiedy' => 'Pierwszy Piątek Miesiąca', 'uwaga' => 'W godzinach porannych' ],
], JSON_UNESCAPED_UNICODE);

$powszednie_json = get_post_meta( $pid, '_msze_powszednie', true ) ?: $default_powszednie;
$niedzielne_json = get_post_meta( $pid, '_msze_niedzielne', true ) ?: $default_niedzielne;
$spowiedz_json   = get_post_meta( $pid, '_msze_spowiedz',   true ) ?: $default_spowiedz;
$uwaga_global    = get_post_meta( $pid, '_msze_uwaga',       true ) ?: '';

$powszednie = json_decode( $powszednie_json, true ) ?: [];
$niedzielne = json_decode( $niedzielne_json, true ) ?: [];
$spowiedz   = json_decode( $spowiedz_json,   true ) ?: [];

$default_nabozenstwa = json_encode([
    [ 'nazwa' => 'Różaniec',                          'kiedy' => 'Codziennie przed wieczorną Mszą Świętą', 'okres' => '' ],
    [ 'nazwa' => 'Droga Krzyżowa',                    'kiedy' => 'Piątki Wielkiego Postu o godz. 17:30',   'okres' => 'Wielki Post' ],
    [ 'nazwa' => 'Gorzkie Żale',                      'kiedy' => 'Niedziele Wielkiego Postu o godz. 17:30','okres' => 'Wielki Post' ],
    [ 'nazwa' => 'Nabożeństwo majowe',                'kiedy' => 'Codziennie o godz. 18:00',               'okres' => 'Maj' ],
    [ 'nazwa' => 'Koronka do Miłosierdzia Bożego',    'kiedy' => 'Codziennie o godz. 15:00',               'okres' => '' ],
    [ 'nazwa' => 'Nabożeństwo czerwcowe',              'kiedy' => 'Codziennie o godz. 18:00',               'okres' => 'Czerwiec' ],
    [ 'nazwa' => 'Nabożeństwo różańcowe październikowe','kiedy'=> 'Codziennie przed wieczorną Mszą Świętą', 'okres' => 'Październik' ],
], JSON_UNESCAPED_UNICODE);

$nabozenstwa_json = get_post_meta( $pid, '_msze_nabozenstwa', true ) ?: $default_nabozenstwa;
$nabozenstwa      = json_decode( $nabozenstwa_json, true ) ?: [];

$nabozenstwa_stale  = array_values( array_filter( $nabozenstwa, fn($n) => empty($n['okres']) ) );
$nabozenstwa_okresy = [];
foreach ( $nabozenstwa as $n ) {
    if ( ! empty($n['okres']) ) {
        $nabozenstwa_okresy[ $n['okres'] ][] = $n;
    }
}
?>


<?php
function msze_nab_ikona( $nazwa, $okres ) {
    $n = mb_strtolower( $nazwa, 'UTF-8' );
    $o = mb_strtolower( $okres,  'UTF-8' );

    if ( str_contains($n, 'różan') || str_contains($o, 'październik') )
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><circle cx="12" cy="3" r="1.5"/><circle cx="19.5" cy="7" r="1.5"/><circle cx="19.5" cy="17" r="1.5"/><circle cx="12" cy="21" r="1.5"/><circle cx="4.5" cy="17" r="1.5"/><circle cx="4.5" cy="7" r="1.5"/><line x1="12" y1="9" x2="12" y2="4.5"/><line x1="14.6" y1="10.4" x2="18.2" y2="8.3"/><line x1="14.6" y1="13.6" x2="18.2" y2="15.7"/><line x1="12" y1="15" x2="12" y2="19.5"/><line x1="9.4" y1="13.6" x2="5.8" y2="15.7"/><line x1="9.4" y1="10.4" x2="5.8" y2="8.3"/></svg>';

    if ( str_contains($n, 'krzyż') || str_contains($o, 'wielki post') && str_contains($n, 'droga') )
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"/><line x1="6" y1="7" x2="18" y2="7"/></svg>';

    if ( str_contains($n, 'gorzk') )
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/><line x1="12" y1="8" x2="12" y2="13"/></svg>';

    if ( str_contains($n, 'miłosierdz') || str_contains($n, 'koronk') )
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>';

    if ( str_contains($n, 'majow') || str_contains($o, 'maj') )
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a9 9 0 0 1 9 9c0 3.6-2.5 7.4-9 11-6.5-3.6-9-7.4-9-11a9 9 0 0 1 9-9z"/><circle cx="12" cy="11" r="3"/></svg>';

    if ( str_contains($n, 'czerwcow') || str_contains($o, 'czerwiec') )
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"/></svg>';

    if ( str_contains($n, 'nieszpor') )
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';

    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>';
}
?>

<main id="primary" class="site-main" role="main">

    <div class="msze-hero">
        <div class="container">
            <p class="msze-hero__eyebrow">Parafia pw. św. Teresy od Dzieciątka Jezus</p>
            <h1 class="msze-hero__title">Msze Święte</h1>
            <p class="msze-hero__subtitle">Porządek Mszy Świętych, nabożeństw i spowiedzi w parafii Mrozy.</p>
            <div class="msze-next" id="msze-next">
                <span class="msze-next__dot"></span>
                <span id="msze-next-label">Trwa wczytywanie...</span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="msze-layout">

            <!-- Dni powszednie -->
            <div class="msze-card">
                <div class="msze-card__header">
                    <svg class="msze-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <div>
                        <h2 class="msze-card__title">Dni powszednie</h2>
                        <span class="msze-card__sub">Poniedziałek – Sobota</span>
                    </div>
                </div>
                <div class="msze-card__body">
                    <div class="msze-list" id="msze-list-pow" role="list">
                        <?php foreach ( $powszednie as $m ) : ?>
                        <div class="msze-row" role="listitem" data-godz="<?php echo esc_attr( $m['godz'] ); ?>">
                            <span class="msze-row__cross" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width:14px;height:14px;display:inline-block;vertical-align:middle;"><line x1="12" y1="3" x2="12" y2="21"/><line x1="6" y1="8" x2="18" y2="8"/></svg></span>
                            <span class="msze-row__godz"><?php echo esc_html( $m['godz'] ); ?></span>
                            <?php if ( ! empty( $m['uwaga'] ) ) : ?>
                            <span class="msze-row__uwaga"><?php echo esc_html( $m['uwaga'] ); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Niedziela -->
            <div class="msze-card msze-card--niedziela">
                <div class="msze-card__header">
                    <svg class="msze-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <div>
                        <h2 class="msze-card__title">Niedziela</h2>
                        <span class="msze-card__sub">i Święta nakazane</span>
                    </div>
                </div>
                <div class="msze-card__body">
                    <div class="msze-list" id="msze-list-nd" role="list">
                        <?php foreach ( $niedzielne as $m ) : ?>
                        <div class="msze-row" role="listitem" data-godz="<?php echo esc_attr( $m['godz'] ); ?>">
                            <span class="msze-row__cross" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width:14px;height:14px;display:inline-block;vertical-align:middle;"><line x1="12" y1="3" x2="12" y2="21"/><line x1="6" y1="8" x2="18" y2="8"/></svg></span>
                            <span class="msze-row__godz"><?php echo esc_html( $m['godz'] ); ?></span>
                            <?php if ( ! empty( $m['uwaga'] ) ) : ?>
                            <span class="msze-row__uwaga"><?php echo esc_html( $m['uwaga'] ); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Spowiedź -->
            <div class="msze-card msze-card--spowiedz">
                <div class="msze-card__header">
                    <svg class="msze-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <div>
                        <h2 class="msze-card__title">Spowiedź</h2>
                        <span class="msze-card__sub">Sakrament Pokuty i Pojednania</span>
                    </div>
                </div>
                <div class="msze-card__body">
                    <div class="msze-spowiedz-list" role="list">
                        <?php foreach ( $spowiedz as $s ) : ?>
                        <div class="msze-spowiedz-row" role="listitem">
                            <span class="msze-spowiedz-row__kiedy"><?php echo esc_html( $s['kiedy'] ); ?></span>
                            <span class="msze-spowiedz-row__uwaga"><?php echo esc_html( $s['uwaga'] ); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div><!-- /.msze-layout -->

        <!-- ── Nabożeństwa ── -->
        <?php if ( ! empty( $nabozenstwa ) ) : ?>
        <div class="msze-nabozenstwa" id="msze-nabozenstwa">

            <div class="msze-nabozenstwa__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <h2>Nabożeństwa</h2>
            </div>

            <div class="msze-nabozenstwa__grid">

                <?php if ( ! empty($nabozenstwa_stale) ) : ?>
                <div class="msze-nab-card">
                    <h3 class="msze-nab-card__title">Przez cały rok</h3>
                    <div class="msze-nab-list">
                        <?php foreach ( $nabozenstwa_stale as $n ) : ?>
                        <div class="msze-nab-row">
                            <span class="msze-nab-row__icon" aria-hidden="true"><?php echo msze_nab_ikona( $n['nazwa'], $n['okres'] ); ?></span>
                            <div class="msze-nab-row__body">
                                <span class="msze-nab-row__nazwa"><?php echo esc_html( $n['nazwa'] ); ?></span>
                                <span class="msze-nab-row__kiedy"><?php echo esc_html( $n['kiedy'] ); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php foreach ( $nabozenstwa_okresy as $okres => $lista ) : ?>
                <div class="msze-nab-card">
                    <h3 class="msze-nab-card__title"><?php echo esc_html( $okres ); ?></h3>
                    <div class="msze-nab-list">
                        <?php foreach ( $lista as $n ) : ?>
                        <div class="msze-nab-row">
                            <span class="msze-nab-row__icon" aria-hidden="true"><?php echo msze_nab_ikona( $n['nazwa'], $n['okres'] ); ?></span>
                            <div class="msze-nab-row__body">
                                <span class="msze-nab-row__nazwa"><?php echo esc_html( $n['nazwa'] ); ?></span>
                                <span class="msze-nab-row__kiedy"><?php echo esc_html( $n['kiedy'] ); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

            </div><!-- /.msze-nabozenstwa__grid -->
        </div><!-- /.msze-nabozenstwa -->
        <?php endif; ?>

        <?php if ( $uwaga_global ) : ?>
        <div class="msze-uwaga-global">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php
            $t = esc_html( $uwaga_global );
            $t = preg_replace( '/Pierwszy Piątek Miesiąca/u', '<strong>Pierwszy Piątek Miesiąca</strong>', $t );
            echo $t;
            ?>
        </div>
        <?php endif; ?>

    </div><!-- /.container -->

</main>

<script>
(function(){
    var powszednie = <?php echo json_encode( $powszednie, JSON_UNESCAPED_UNICODE ); ?>;
    var niedzielne = <?php echo json_encode( $niedzielne, JSON_UNESCAPED_UNICODE ); ?>;
    var dni = ['Niedziela','Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota'];

    function toMin(g) { var p = g.split(':'); return parseInt(p[0])*60+parseInt(p[1]); }

    function update() {
        var now   = new Date();
        var dzien = now.getDay();
        var czas  = now.getHours()*60 + now.getMinutes();
        var label = document.getElementById('msze-next-label');
        var badge = document.getElementById('msze-next');
        if (!label||!badge) return;

        var lista    = dzien===0 ? niedzielne : powszednie;
        var listId   = dzien===0 ? 'msze-list-nd' : 'msze-list-pow';

        document.querySelectorAll('.msze-row').forEach(function(r){
            r.classList.remove('msze-row--now','msze-row--next');
        });

        var nastepna = null;
        for (var i=0; i<lista.length; i++) {
            var min = toMin(lista[i].godz);
            if (czas >= min && czas <= min+50) {
                var rows = document.querySelectorAll('#'+listId+' .msze-row');
                rows.forEach(function(r){ if(r.dataset.godz===lista[i].godz) r.classList.add('msze-row--now'); });
            } else if (!nastepna && min > czas) {
                nastepna = lista[i];
                var rows2 = document.querySelectorAll('#'+listId+' .msze-row');
                rows2.forEach(function(r){ if(r.dataset.godz===nastepna.godz) r.classList.add('msze-row--next'); });
            }
        }

        if (nastepna) {
            badge.className = 'msze-next msze-next--soon';
            label.textContent = 'Najbliższa Msza Św. dziś o ' + nastepna.godz;
        } else {
            var jutro      = (dzien+1)%7;
            var listaJutro = jutro===0 ? niedzielne : powszednie;
            badge.className = 'msze-next msze-next--tomorrow';
            label.textContent = listaJutro.length
                ? 'Najbliższa Msza Św. jutro (' + dni[jutro] + ') o ' + listaJutro[0].godz
                : '';
        }
    }

    update();
    setInterval(update, 60000);
})();
</script>

<?php get_footer(); ?>
