<?php
get_header();

$warianty = [
    'zmartwychwstal',
    'owieczka',
];

$wariant = $warianty[ array_rand($warianty) ];
?>


<?php if ( $wariant === 'zmartwychwstal' ) : ?>
<!-- ═══════════════════════════════════════════
     WARIANT: Nie ma Go tu, Zmartwychwstał
     ═══════════════════════════════════════════ -->
<main id="primary" class="site-main" role="main">
<style>
.page-404 {
    min-height: 70vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 60px 20px;
    background: var(--color-white);
}
[data-theme="dark"] .page-404 { background: var(--bg-primary); }
.page-404-image {
    width: 260px; height: auto; max-width: 90%; margin-bottom: 32px;
    filter: drop-shadow(0 8px 32px rgba(201,168,76,0.2));
    border-radius: 8px;
}
.page-404-num {
    font-family: 'Cinzel', 'Georgia', serif;
    font-size: clamp(5rem, 18vw, 10rem);
    font-weight: 700; color: transparent;
    -webkit-text-stroke: 2px var(--color-gold);
    letter-spacing: 0.1em; line-height: 1;
    margin-bottom: 16px;
    text-shadow: 0 0 60px rgba(201,168,76,0.15);
}
.page-404-quote {
    font-family: 'EB Garamond', 'Georgia', serif;
    font-size: clamp(1.2rem, 3vw, 1.7rem);
    font-style: italic; font-weight: 500;
    color: var(--color-dark); line-height: 1.5;
    margin-bottom: 8px; max-width: 600px;
}
[data-theme="dark"] .page-404-quote { color: var(--text-primary); }
.page-404-source {
    font-family: 'Montserrat', sans-serif; font-size: 0.9rem;
    color: var(--color-gold-dark); letter-spacing: 0.08em; margin-bottom: 32px;
}
.page-404-sub {
    font-family: 'Montserrat', sans-serif; font-size: 0.95rem;
    color: var(--color-text-light); margin-bottom: 32px;
}
[data-theme="dark"] .page-404-sub { color: var(--text-secondary); }
.page-404-link {
    display: inline-block; padding: 13px 36px;
    border: 1px solid var(--color-gold); border-radius: 30px;
    color: var(--color-gold-dark); font-family: 'Montserrat', sans-serif;
    font-size: 0.85rem; font-weight: 600; text-decoration: none;
    letter-spacing: 0.1em; text-transform: uppercase;
    background: transparent; transition: all 0.3s;
}
.page-404-link:hover {
    background: var(--color-gold); color: var(--color-dark);
    box-shadow: 0 4px 20px rgba(201,168,76,0.3);
}
</style>
<div class="page-404">
    <img
        src="<?php echo esc_url( get_template_directory_uri() . '/img/chrystus-zmartwychwstaly.jpg' ); ?>"
        alt="Chrystus Zmartwychwstały" class="page-404-image"
        onerror="this.style.display='none'">
    <div class="page-404-num">404</div>
    <p class="page-404-quote">„Nie ma Go tu, zmartwychwstał!"</p>
    <p class="page-404-source">Łk 24, 6</p>
    <p class="page-404-sub"><?php esc_html_e( 'Strona, której szukasz, nie istnieje.', 'parafia-mrozy' ); ?></p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="page-404-link">
        &larr; <?php esc_html_e( 'Wróć na stronę główną', 'parafia-mrozy' ); ?>
    </a>
</div>
</main>


<?php elseif ( $wariant === 'owieczka' ) : ?>
<!-- ═══════════════════════════════════════════
     WARIANT: Zaginiona Owieczka (film)
     ═══════════════════════════════════════════ -->
<main id="primary" class="site-main err404-dark" role="main">
    <div class="err404-wrap">

        <div class="err404-video-side">
            <div class="err404-video-frame">
                <video autoplay loop muted playsinline preload="auto" class="err404-video" aria-hidden="true">
                    <source src="<?php echo esc_url( get_template_directory_uri() . '/images/owieczka-compressed.mp4' ); ?>" type="video/mp4">
                </video>
                <div class="err404-video-border"></div>
            </div>
        </div>

        <div class="err404-content-side">
            <p class="err404-eyebrow">Błąd 404</p>
            <h1 class="err404-title">Ta strona się<br>zagubiła&hellip;</h1>
            <div class="err404-divider"></div>
            <p class="err404-quote">
                &bdquo;Któż z was, mając sto owiec, a zgubiwszy jedną z nich,
                nie zostawia dziewięćdziesięciu dziewięciu na pustkowiu
                i nie idzie za zgubioną, aż ją znajdzie?&rdquo;
            </p>
            <p class="err404-quote-ref">Łk 15, 4</p>
            <p class="err404-desc">
                Strona której szukasz nie istnieje lub została przeniesiona.<br>
                Wróć na stronę główną parafii.
            </p>
            <div class="err404-actions">
                <a href="<?php echo esc_url( home_url('/') ); ?>" class="err404-btn err404-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Wróć do owczarni
                </a>
                <a href="<?php echo esc_url( get_permalink( get_page_by_path('kontakt') ) ); ?>" class="err404-btn err404-btn--secondary">
                    Kontakt z parafią
                </a>
            </div>
        </div>

    </div>
</main>


<?php
?>


<?php endif; ?>

<?php get_footer(); ?>
