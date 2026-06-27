<?php
get_header();

$pid        = get_the_ID();
$quote      = get_post_meta( $pid, 'pg_quote',       true );
$quote_src  = get_post_meta( $pid, 'pg_quote_src',   true );
$contact    = get_post_meta( $pid, 'pg_contact',     true );
$email      = get_post_meta( $pid, 'pg_email',       true );
$meetings   = get_post_meta( $pid, 'pg_meetings',    true );
$gallery    = get_post_meta( $pid, 'pg_gallery',     true );
?>

<main id="primary" class="site-main" role="main">

    <!-- ── HERO ── -->
    <div class="pg-hero">
        <?php if ( has_post_thumbnail() ) : ?>
        <div class="pg-hero-img">
            <?php the_post_thumbnail( 'large' ); ?>
            <div class="pg-hero-overlay"></div>
        </div>
        <?php endif; ?>
        <div class="pg-hero-content container">
            <h1 class="pg-hero-title"><?php the_title(); ?></h1>
            <?php if ( $meetings ) : ?>
            <p class="pg-hero-meetings">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo esc_html( $meetings ); ?>
            </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
    <div class="pg-wrap">

        <!-- ── OPIS ── -->
        <div class="pg-description">
            <?php the_content(); ?>
        </div>

        <!-- ── CYTAT ŚWIĘTEGO (opcjonalny) ── -->
        <?php if ( $quote ) : ?>
        <blockquote class="pg-quote">
            <p><?php echo esc_html( $quote ); ?></p>
            <?php if ( $quote_src ) : ?>
            <cite><?php echo esc_html( $quote_src ); ?></cite>
            <?php endif; ?>
        </blockquote>
        <?php endif; ?>

        <!-- ── KONTAKT ── -->
        <?php if ( $contact || $email ) : ?>
        <div class="pg-contact-box">
            <h2 class="pg-section-title">
                <span class="pg-section-title-icon">✉</span>
                Kontakt
            </h2>
            <div class="pg-contact-inner">
                <?php if ( $contact ) : ?>
                <div class="pg-contact-row">
                    <span class="pg-contact-label">Opiekun / kontakt</span>
                    <span class="pg-contact-value"><?php echo esc_html( $contact ); ?></span>
                </div>
                <?php endif; ?>
                <?php if ( $email ) : ?>
                <div class="pg-contact-row">
                    <span class="pg-contact-label">E-mail</span>
                    <a href="mailto:<?php echo esc_attr( $email ); ?>" class="pg-contact-email">
                        <?php echo esc_html( $email ); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── GALERIA ── -->
        <?php
        $gallery_ids = $gallery ? array_slice( array_map( 'trim', explode( ',', $gallery ) ), 0, 5 ) : [];
        if ( $gallery_ids ) : ?>
        <div class="pg-gallery-section">
            <h2 class="pg-section-title">
                <span class="pg-section-title-icon">📷</span>
                Galeria
            </h2>
            <div class="pg-gallery pg-gallery-<?php echo count($gallery_ids) === 1 ? 'single' : ( count($gallery_ids) <= 2 ? 'two' : 'grid' ); ?>">
                <?php foreach ( $gallery_ids as $img_id ) :
                    $thumb = wp_get_attachment_image_src( (int)$img_id, 'medium_large' );
                    $full  = wp_get_attachment_image_src( (int)$img_id, 'large' );
                    $alt   = get_post_meta( (int)$img_id, '_wp_attachment_image_alt', true );
                    if ( ! $thumb ) continue; ?>
                <button class="pg-gallery-item"
                        data-full="<?php echo esc_url( $full[0] ); ?>"
                        data-index="<?php echo esc_attr( $img_id ); ?>"
                        aria-label="Powiększ zdjęcie">
                    <img src="<?php echo esc_url( $thumb[0] ); ?>"
                         alt="<?php echo esc_attr( $alt ); ?>"
                         loading="lazy">
                    <span class="pg-gallery-zoom" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- .pg-wrap -->
    </div><!-- .container -->

</main>

<!-- ── LIGHTBOX ── -->
<div class="pg-lightbox" id="pgLightbox" role="dialog" aria-modal="true" aria-label="Podgląd zdjęcia">
    <button class="pg-lb-btn pg-lb-close" id="pgClose" aria-label="Zamknij">&times;</button>
    <button class="pg-lb-btn pg-lb-prev"  id="pgPrev"  aria-label="Poprzednie">&#8249;</button>
    <div class="pg-lb-img-wrap">
        <img src="" alt="" id="pgLbImg">
    </div>
    <button class="pg-lb-btn pg-lb-next"  id="pgNext"  aria-label="Następne">&#8250;</button>
    <div class="pg-lb-counter" id="pgCounter"></div>
</div>

<style>
.pg-hero {
    position: relative;
    min-height: 320px;
    display: flex;
    align-items: flex-end;
    overflow: hidden;
    margin-bottom: 0;
    background: var(--color-dark-bg);
}

.pg-hero-img {
    position: absolute;
    inset: 0;
}

.pg-hero-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    display: block;
}

.pg-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.80) 0%, rgba(0,0,0,0.35) 55%, rgba(0,0,0,0.10) 100%);
}

.pg-hero-content {
    position: relative;
    z-index: 1;
    padding-top: 80px;
    padding-bottom: 36px;
}

.pg-hero-title {
    font-family: var(--font-main);
    font-size: clamp(1.8rem, 5vw, 2.8rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 10px;
    line-height: 1.15;
    text-shadow: 0 2px 12px rgba(0,0,0,0.5);
}

.pg-hero-meetings {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--color-gold-light);
    font-size: 0.9rem;
    font-weight: 500;
    margin: 0;
    background: rgba(0,0,0,0.35);
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid rgba(201,168,76,0.3);
}

.pg-hero:not(:has(.pg-hero-img)) {
    min-height: 160px;
    background: linear-gradient(135deg, #1a1a2e, #2d1f4e);
    align-items: center;
}

.pg-wrap {
    max-width: 820px;
    margin: 0 auto;
    padding: 48px 0 64px;
}

.pg-quote {
    position: relative;
    margin: 0 0 40px;
    padding: 28px 32px 24px 52px;
    background: rgba(201,168,76,0.07);
    border-left: 4px solid var(--color-gold);
    border-radius: 0 10px 10px 0;
}

.pg-quote::before {
    content: '\201C';
    position: absolute;
    left: 12px;
    top: 10px;
    font-size: 4rem;
    line-height: 1;
    color: var(--color-gold);
    opacity: 0.5;
    font-family: Georgia, serif;
}

.pg-quote p {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    font-style: italic;
    color: var(--text-primary, var(--color-dark));
    line-height: 1.7;
    margin: 0 0 10px;
}

.pg-quote cite {
    font-size: 0.85rem;
    font-style: normal;
    font-weight: 600;
    color: var(--color-gold-dark);
    letter-spacing: 0.04em;
}

.pg-quote cite::before { content: '— '; }

.pg-description {
    font-size: 1rem;
    line-height: 1.85;
    color: var(--text-primary, var(--color-dark));
    margin-bottom: 44px;
}

.pg-description p   { margin-bottom: 16px; }
.pg-description h2,
.pg-description h3  {
    font-family: var(--font-main);
    font-weight: 700;
    color: var(--color-gold-dark);
    margin: 28px 0 12px;
}
.pg-description ul,
.pg-description ol  { padding-left: 20px; margin-bottom: 16px; }
.pg-description li  { margin-bottom: 6px; }
.pg-description strong { color: inherit; font-weight: 700; }

.pg-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-main);
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--color-gold-dark);
    margin: 0 0 18px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(201,168,76,0.25);
    letter-spacing: 0.02em;
}

.pg-section-title-icon {
    font-size: 1.15rem;
}

.pg-contact-box {
    margin-bottom: 44px;
}

.pg-contact-inner {
    background: rgba(201,168,76,0.06);
    border: 1px solid rgba(201,168,76,0.2);
    border-radius: 10px;
    overflow: hidden;
}

.pg-contact-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(201,168,76,0.1);
}

.pg-contact-row:last-child { border-bottom: none; }

.pg-contact-label {
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-gold-dark);
    min-width: 140px;
    flex-shrink: 0;
}

.pg-contact-value {
    font-size: 0.95rem;
    color: var(--text-primary, var(--color-dark));
    font-weight: 500;
}

.pg-contact-email {
    font-size: 0.95rem;
    color: var(--color-gold-dark);
    font-weight: 600;
    text-decoration: none;
    border-bottom: 1px solid rgba(201,168,76,0.35);
    transition: border-color 0.2s, color 0.2s;
}

.pg-contact-email:hover {
    color: var(--color-gold);
    border-color: var(--color-gold);
}

@media (max-width: 480px) {
    .pg-contact-row    { flex-direction: column; align-items: flex-start; gap: 4px; }
    .pg-contact-label  { min-width: 0; }
}

.pg-gallery-section { margin-bottom: 48px; }

.pg-gallery {
    display: grid;
    gap: 10px;
}

.pg-gallery-grid   { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
.pg-gallery-two    { grid-template-columns: 1fr 1fr; }
.pg-gallery-single { grid-template-columns: 1fr; max-width: 480px; }

.pg-gallery-item {
    position: relative;
    display: block;
    border: none;
    padding: 0;
    background: var(--color-dark-bg);
    border-radius: 8px;
    overflow: hidden;
    cursor: zoom-in;
    aspect-ratio: 4 / 3;
    transition: transform 0.25s;
}

.pg-gallery-item:hover { transform: scale(1.02); }
.pg-gallery-item:hover .pg-gallery-zoom { opacity: 1; }

.pg-gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: filter 0.25s;
}

.pg-gallery-item:hover img { filter: brightness(0.75); }

.pg-gallery-zoom {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    opacity: 0;
    transition: opacity 0.25s;
    pointer-events: none;
}

@media (max-width: 540px) {
    .pg-gallery-grid { grid-template-columns: 1fr 1fr; }
    .pg-gallery-two  { grid-template-columns: 1fr 1fr; }
}

.pg-lightbox {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0,0,0,0.93);
    align-items: center;
    justify-content: center;
    animation: pgLBIn 0.2s ease;
}
@keyframes pgLBIn { from { opacity: 0 } to { opacity: 1 } }
.pg-lightbox.open { display: flex; }

.pg-lb-img-wrap {
    max-width: 92vw;
    max-height: 90vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pg-lightbox img {
    max-width: 90vw;
    max-height: 86vh;
    border-radius: 6px;
    box-shadow: 0 8px 48px rgba(0,0,0,0.6);
    object-fit: contain;
    display: block;
    transition: opacity 0.18s;
}

.pg-lb-btn {
    position: fixed;
    background: rgba(201,168,76,0.15);
    border: 1px solid rgba(201,168,76,0.4);
    color: var(--color-gold);
    border-radius: 50%;
    width: 48px;
    height: 48px;
    font-size: 1.4rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, transform 0.15s;
    z-index: 10000;
    line-height: 1;
}
.pg-lb-btn:hover { background: rgba(201,168,76,0.35); }

.pg-lb-close { top: 18px; right: 18px; font-size: 1.6rem; }
.pg-lb-prev  { left: 16px;  top: 50%; transform: translateY(-50%); }
.pg-lb-next  { right: 16px; top: 50%; transform: translateY(-50%); }
.pg-lb-prev:hover { transform: translateY(-50%) scale(1.1); }
.pg-lb-next:hover { transform: translateY(-50%) scale(1.1); }

.pg-lb-counter {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    color: rgba(201,168,76,0.7);
    font-family: var(--font-main);
    font-size: 0.85rem;
    letter-spacing: 0.1em;
}

@media (max-width: 600px) {
    .pg-lb-prev { left: 6px; }
    .pg-lb-next { right: 6px; }
    .pg-lb-btn  { width: 40px; height: 40px; }
    .pg-wrap    { padding: 32px 0 48px; }
}
</style>

<script>
(function () {
    const lb      = document.getElementById('pgLightbox');
    if ( ! lb ) return;
    const lbImg   = document.getElementById('pgLbImg');
    const counter = document.getElementById('pgCounter');
    const btnClose = document.getElementById('pgClose');
    const btnPrev  = document.getElementById('pgPrev');
    const btnNext  = document.getElementById('pgNext');

    const items = Array.from( document.querySelectorAll('.pg-gallery-item') )
                       .map( b => b.getAttribute('data-full') );
    let current = 0;

    function open(i) {
        current = i;
        show();
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
        btnClose.focus();
    }

    function close() {
        lb.classList.remove('open');
        document.body.style.overflow = '';
    }

    function show() {
        lbImg.style.opacity = '0';
        lbImg.src = items[current];
        lbImg.onload = function () { lbImg.style.opacity = '1'; };
        counter.textContent = (current + 1) + ' / ' + items.length;
        btnPrev.style.display = items.length > 1 ? 'flex' : 'none';
        btnNext.style.display = items.length > 1 ? 'flex' : 'none';
    }

    function prev() { current = (current - 1 + items.length) % items.length; show(); }
    function next() { current = (current + 1) % items.length; show(); }

    document.querySelectorAll('.pg-gallery-item').forEach(function (btn, i) {
        btn.addEventListener('click', function () { open(i); });
    });

    btnClose.addEventListener('click', close);
    btnPrev.addEventListener('click', prev);
    btnNext.addEventListener('click', next);

    lb.addEventListener('click', function (e) { if (e.target === lb) close(); });

    document.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape')     close();
        if (e.key === 'ArrowLeft')  prev();
        if (e.key === 'ArrowRight') next();
    });

    let tx = 0;
    lb.addEventListener('touchstart', function (e) { tx = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend',   function (e) {
        const d = tx - e.changedTouches[0].clientX;
        if (Math.abs(d) > 50) d > 0 ? next() : prev();
    }, { passive: true });
})();
</script>

<?php get_footer(); ?>
