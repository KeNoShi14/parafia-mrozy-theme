<?php
/**
 * Szablon strony: Standardy ochrony dzieci.
 * Treść dokumentu wyświetlana jest z pliku PDF (pliki/standardy-ochrony-dzieci.pdf).
 */
get_header();

$pdf_url = parafia_standardy_pdf_url();
$telefon = pm_get( 'parafia_phone', '25 757 41 93' );
$email   = sanitize_email( pm_get( 'parafia_email', 'parafiamrozy@gmail.com' ) );

$standardy = [
    1 => 'Stworzenie i zachowanie bezpiecznego środowiska w parafii',
    2 => 'Weryfikacja, delegowanie i edukacja kapłanów, osób konsekrowanych i świeckich pracujących z dziećmi',
    3 => 'Sposób reagowania na oskarżenia lub niewłaściwe zachowania',
    4 => 'Zapewnienie opieki i wsparcia osobom skrzywdzonym',
    5 => 'Sposób postępowania z oskarżonymi o wykorzystanie seksualne i przemoc',
    6 => 'Zasady chroniące w obszarze parafialnym',
    7 => 'Edukacja dzieci oraz osób bezbronnych w ochronie swoich granic',
    8 => 'Szkolenie i stałe wsparcie dla osób zajmujących się profilaktyką',
    9 => 'Zapewnienie jakości i ciągłości działań w zakresie prewencji',
];
$zalaczniki = [
    1 => 'Oświadczenie o krajach zamieszkania w ciągu ostatnich 20 lat',
    2 => 'Oświadczenie o zapoznaniu się z polityką ochrony dzieci',
    3 => 'Zasady sporządzania notatki dotyczącej zdarzenia',
];
?>

<main id="primary" class="site-main" role="main">

    <div class="historia-hero">
        <div class="container">
            <p class="historia-hero__eyebrow">Parafia pw. św. Teresy od Dzieciątka Jezus w Mrozach</p>
            <h1 class="historia-hero__title"><?php the_title(); ?></h1>
            <p class="historia-hero__subtitle">Zasady, dzięki którym dzieci w naszej parafii są bezpieczne, szanowane i otoczone troską.</p>
        </div>
    </div>

    <div class="container sod">

        <div class="sod-intro">
            <p>
                Dokument realizuje obowiązek wprowadzenia standardów ochrony dzieci przed krzywdzeniem
                we wszystkich instytucjach, w których przebywają dzieci (ustawa z dnia 28 lipca 2023 r.
                o zmianie ustawy Kodeks rodzinny i opiekuńczy oraz niektórych innych ustaw, Dz.U. 2023 poz. 1606).
            </p>
            <p>
                Ludzie Kościoła przyjmują nie tylko ustawową, lecz przede wszystkim moralną odpowiedzialność
                za ochronę i promowanie dobra wszystkich dzieci. Niedopuszczalne jest stosowanie przez
                kogokolwiek wobec dziecka przemocy w jakiejkolwiek formie.
            </p>
            <?php
            // Dodatkowa treść wpisana w edytorze strony (opcjonalnie).
            while ( have_posts() ) : the_post();
                if ( '' !== trim( get_the_content() ) ) {
                    echo '<div class="sod-intro__extra">';
                    the_content();
                    echo '</div>';
                }
            endwhile;
            ?>
        </div>

        <section class="sod-help" aria-labelledby="sod-help-title">
            <h2 id="sod-help-title" class="sod-help__title">Dziecku dzieje się krzywda? Nie czekaj.</h2>
            <div class="sod-help__grid">
                <div class="sod-help__col">
                    <p>
                        Każdą informację o niewłaściwym zachowaniu traktujemy poważnie. Zgłoszenie możesz
                        przekazać proboszczowi lub osobie zaufanej w parafii. Każdy, kto dowie się
                        o przestępstwie ściganym z urzędu, ma obowiązek zawiadomić prokuraturę lub policję
                        (art. 304 k.p.k.).
                    </p>
                    <ul class="sod-help__contact">
                        <?php if ( $telefon ) : ?>
                            <li>Parafia: <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telefon ) ); ?>"><?php echo esc_html( $telefon ); ?></a></li>
                        <?php endif; ?>
                        <?php if ( $email ) : ?>
                            <li>E-mail: <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <ul class="sod-help__phones">
                    <li><a href="tel:112"><strong>112</strong></a><span>Numer alarmowy – gdy zagrożone jest życie lub zdrowie</span></li>
                    <li><a href="tel:116111"><strong>116 111</strong></a><span>Telefon zaufania dla dzieci i młodzieży (bezpłatny, całodobowy)</span></li>
                    <li><a href="tel:800121212"><strong>800 12 12 12</strong></a><span>Dziecięcy Telefon Zaufania Rzecznika Praw Dziecka</span></li>
                    <li><a href="https://www.niebieskalinia.info/" target="_blank" rel="noopener"><strong>Niebieska Linia</strong></a><span>Pomoc osobom doznającym przemocy domowej</span></li>
                </ul>
            </div>
        </section>

        <section class="sod-toc" aria-labelledby="sod-toc-title">
            <h2 id="sod-toc-title" class="sod-section-title">Co zawiera dokument</h2>
            <ol class="sod-toc__list">
                <?php foreach ( $standardy as $nr => $tytul ) : ?>
                    <li class="sod-toc__item">
                        <span class="sod-toc__nr">Standard <?php echo (int) $nr; ?></span>
                        <span class="sod-toc__name"><?php echo esc_html( $tytul ); ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
            <p class="sod-toc__annex-label">Załączniki</p>
            <ul class="sod-toc__annex">
                <?php foreach ( $zalaczniki as $nr => $tytul ) : ?>
                    <li><strong>Załącznik <?php echo (int) $nr; ?>.</strong> <?php echo esc_html( $tytul ); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="sod-doc" aria-labelledby="sod-doc-title">
            <div class="sod-doc__head">
                <h2 id="sod-doc-title" class="sod-section-title">Pełna treść dokumentu</h2>
                <div class="sod-doc__actions">
                    <a class="sod-btn" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener">Otwórz w nowej karcie</a>
                    <a class="sod-btn sod-btn--gold" href="<?php echo esc_url( $pdf_url ); ?>" download>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Pobierz PDF
                    </a>
                </div>
            </div>

            <div class="sod-viewer" id="sod-viewer" data-pdf="<?php echo esc_url( $pdf_url ); ?>">
                <p class="sod-viewer__loading">Wczytywanie dokumentu…</p>
                <noscript>
                    <p>Aby przeczytać dokument, <a href="<?php echo esc_url( $pdf_url ); ?>">pobierz plik PDF</a>.</p>
                </noscript>
            </div>
        </section>

    </div>
</main>

<?php
get_footer();
