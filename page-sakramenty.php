<?php
get_header();

if ( ! function_exists( 'parafia_sakr_ikona' ) ) :
function parafia_sakr_ikona( $id ) {
    $obrazki = [
        'chrzest'      => 'chrzest.png',
        'komunia'      => 'komunia.png',
        'bierzmowanie' => 'bierzmowanie.png',
        'malzenstwo'   => 'malzenstwo.png',
        'pogrzeb'      => 'pogrzeb.png',
        'pokuta'       => 'pokuta.png',
        'chorzy'       => 'chorzy.png',
    ];
    $plik = $obrazki[ $id ] ?? 'chrzest.png';
    $url  = get_template_directory_uri() . '/images/sakramenty/' . $plik;
    return '<img src="' . esc_url( $url ) . '" alt="" class="sakr-icon-img" loading="lazy">';
}
endif;

$pid = get_the_ID();

// Pola każdego sakramentu — pełny zestaw kluczy (kompatybilność wstecz).
$sakr_pola = [ 'id'=>'', 'nazwa'=>'', 'ikona'=>'', 'opis'=>'', 'terminy'=>'', 'zgloszenie'=>'', 'dokumenty'=>'', 'wymagania'=>'', 'dodatkowe'=>'' ];

$default_lista = [
    [ 'id'=>'chrzest',     'nazwa'=>'Chrzest Święty',          'ikona'=>'chrzest'      ],
    [ 'id'=>'komunia',     'nazwa'=>'Pierwsza Komunia Święta', 'ikona'=>'komunia'      ],
    [ 'id'=>'bierzmowanie','nazwa'=>'Bierzmowanie',            'ikona'=>'bierzmowanie' ],
    [ 'id'=>'malzenstwo',  'nazwa'=>'Sakrament Małżeństwa',    'ikona'=>'malzenstwo'   ],
    [ 'id'=>'chorzy',      'nazwa'=>'Namaszczenie Chorych',    'ikona'=>'chorzy'       ],
    [ 'id'=>'pogrzeb',     'nazwa'=>'Pogrzeb Katolicki',       'ikona'=>'pogrzeb'      ],
];

$sakramenty = function_exists( 'parafia_sakr_pobierz_liste' )
    ? parafia_sakr_pobierz_liste( $pid )
    : ( get_post_meta( $pid, '_sakr_lista', true ) ? json_decode( get_post_meta( $pid, '_sakr_lista', true ), true ) : [] );
if ( ! is_array( $sakramenty ) ) {
    $sakramenty = [];
}
// Gdy brak danych lub pusta lista — pokaż domyślny zestaw sakramentów.
if ( empty( $sakramenty ) ) {
    $sakramenty = $default_lista;
}

// Dopełnij brakujące pola w każdej pozycji (działa też dla starej struktury w bazie).
foreach ( $sakramenty as &$_s ) {
    $_s = array_merge( $sakr_pola, is_array( $_s ) ? $_s : [] );
}
unset( $_s );

$aktywna = isset($_GET['sakrament']) ? sanitize_key($_GET['sakrament']) : ( $sakramenty[0]['id'] ?? 'chrzest' );

// Link do kancelarii — z fallbackiem, gdy strona „kontakt” nie istnieje.
$kontakt_page = get_page_by_path( 'kontakt' );
$kontakt_url  = $kontakt_page ? get_permalink( $kontakt_page ) : home_url( '/' );
?>

<main id="primary" class="site-main" role="main">

    <!-- Hero -->
    <div class="sakr-hero">
        <div class="container">
            <p class="sakr-hero__eyebrow">Parafia pw. św. Teresy od Dzieciątka Jezus</p>
            <h1 class="sakr-hero__title">Sakramenty</h1>
            <p class="sakr-hero__subtitle">Sakramenty są widzialnymi znakami niewidzialnej łaski Bożej. Kościół udziela ich wiernym na każdym etapie życia.</p>
        </div>
    </div>

    <div class="container">

        <!-- Nawigacja sakramentów -->
        <nav class="sakr-nav" aria-label="Wybór sakramentu" role="tablist">
            <?php foreach ( $sakramenty as $s ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'sakrament', $s['id'], get_permalink( $pid ) ) ); ?>"
               class="sakr-nav__item <?php echo $aktywna === $s['id'] ? 'sakr-nav__item--active' : ''; ?>"
               role="tab"
               aria-selected="<?php echo $aktywna === $s['id'] ? 'true' : 'false'; ?>">
                <span class="sakr-nav__icon" aria-hidden="true"><?php echo parafia_sakr_ikona( $s['ikona'] ); ?></span>
                <span class="sakr-nav__label"><?php echo esc_html( $s['nazwa'] ); ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Treść aktywnego sakramentu -->
        <?php foreach ( $sakramenty as $s ) :
            if ( $s['id'] !== $aktywna ) continue; ?>

        <div class="sakr-content" role="tabpanel" aria-label="<?php echo esc_attr( $s['nazwa'] ); ?>">

            <div class="sakr-content__header">
                <div class="sakr-content__icon-wrap" aria-hidden="true">
                    <?php echo parafia_sakr_ikona( $s['ikona'] ); ?>
                </div>
                <h2 class="sakr-content__title"><?php echo esc_html( $s['nazwa'] ); ?></h2>
            </div>

            <div class="sakr-content__grid">

                <!-- Opis -->
                <?php if ( ! empty( $s['opis'] ) ) : ?>
                <div class="sakr-card sakr-card--opis">
                    <div class="sakr-card__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <h3>O sakramencie</h3>
                    </div>
                    <p class="sakr-card__text"><?php echo nl2br( esc_html( $s['opis'] ) ); ?></p>
                </div>
                <?php endif; ?>

                <!-- Terminy -->
                <?php if ( ! empty( $s['terminy'] ) ) : ?>
                <div class="sakr-card sakr-card--terminy">
                    <div class="sakr-card__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <h3>Terminy</h3>
                    </div>
                    <p class="sakr-card__text"><?php echo nl2br( esc_html( $s['terminy'] ) ); ?></p>
                </div>
                <?php endif; ?>

                <!-- Jak się zgłosić -->
                <?php if ( ! empty( $s['zgloszenie'] ) ) : ?>
                <div class="sakr-card sakr-card--zgloszenie">
                    <div class="sakr-card__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <h3>Jak się zgłosić</h3>
                    </div>
                    <p class="sakr-card__text"><?php echo nl2br( esc_html( $s['zgloszenie'] ) ); ?></p>
                    <a href="<?php echo esc_url( $kontakt_url ); ?>" class="sakr-card__cta">
                        Skontaktuj się z kancelarią
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
                <?php endif; ?>

                <!-- Wymagane dokumenty -->
                <?php if ( ! empty( $s['dokumenty'] ) ) :
                    $docs = array_filter( array_map( 'trim', explode( "\n", $s['dokumenty'] ) ) );
                    if ( $docs ) : ?>
                <div class="sakr-card sakr-card--dokumenty">
                    <div class="sakr-card__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <h3>Wymagane dokumenty</h3>
                    </div>
                    <ul class="sakr-docs-list">
                        <?php foreach ( $docs as $doc ) : ?>
                        <li class="sakr-docs-list__item">
                            <span class="sakr-docs-list__check" aria-hidden="true">✓</span>
                            <?php echo esc_html( $doc ); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; endif; ?>

                <!-- Wymagania / warunki -->
                <?php if ( ! empty( $s['wymagania'] ) ) :
                    $reqs = array_filter( array_map( 'trim', explode( "\n", $s['wymagania'] ) ) );
                    if ( $reqs ) : ?>
                <div class="sakr-card sakr-card--wymagania">
                    <div class="sakr-card__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        <h3>Wymagania i warunki</h3>
                    </div>
                    <ul class="sakr-docs-list">
                        <?php foreach ( $reqs as $req ) : ?>
                        <li class="sakr-docs-list__item">
                            <span class="sakr-docs-list__check" aria-hidden="true">✓</span>
                            <?php echo esc_html( $req ); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; endif; ?>

                <!-- Dodatkowe informacje -->
                <?php if ( ! empty( $s['dodatkowe'] ) ) :
                    $dods = array_filter( array_map( 'trim', explode( "\n", $s['dodatkowe'] ) ) );
                    if ( $dods ) : ?>
                <div class="sakr-card sakr-card--dodatkowe">
                    <div class="sakr-card__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <h3>Dodatkowe informacje</h3>
                    </div>
                    <ul class="sakr-docs-list">
                        <?php foreach ( $dods as $dod ) : ?>
                        <li class="sakr-docs-list__item">
                            <span class="sakr-docs-list__check" aria-hidden="true">✓</span>
                            <?php echo esc_html( $dod ); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; endif; ?>

                <!-- Brak jakiejkolwiek treści -->
                <?php if ( empty( $s['opis'] ) && empty( $s['terminy'] ) && empty( $s['zgloszenie'] ) && empty( $s['dokumenty'] ) && empty( $s['wymagania'] ) && empty( $s['dodatkowe'] ) ) : ?>
                <div class="sakr-card sakr-card--opis">
                    <div class="sakr-card__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <h3><?php echo esc_html( $s['nazwa'] ); ?></h3>
                    </div>
                    <p class="sakr-card__placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Informacje o tym sakramencie są w przygotowaniu. W razie pytań prosimy o kontakt z kancelarią.
                    </p>
                    <a href="<?php echo esc_url( $kontakt_url ); ?>" class="sakr-card__cta" style="margin-top:0;">
                        Skontaktuj się z kancelarią
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
                <?php endif; ?>

            </div><!-- /.sakr-content__grid -->

        </div><!-- /.sakr-content -->

        <?php endforeach; ?>

    </div><!-- /.container -->

</main>

<?php
get_footer();
?>
