<?php
get_header();
?>

<main id="primary" class="site-main" role="main">
<div class="container">
<div class="content-area">

<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

    <header class="single-post-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
    <div style="margin-bottom:25px;">
        <?php the_post_thumbnail( 'large', [ 'style' => 'width:100%;height:auto;border-radius:4px;' ] ); ?>
    </div>
    <?php endif; ?>

    <div class="lso-wrap">
    <div class="lso-panel lso-panel--standalone">

        <div class="lso-hero lso-hero--bielanki">
            <div class="lso-hero-icon">✿</div>
            <h2>Bielanki</h2>
            <p class="lso-hero-motto">Z radością, we wspólnocie idziemy z Maryją do Jezusa!</p>
        </div>

        <div class="lso-content">

            <p>Bielanki stanowią <strong>dziewczęcą służbę liturgiczną</strong> i skupiają dziewczęta, które swoim życiem i postępowaniem chcą naśladować Maryję oraz nieść radość i dobro w każde miejsce, do którego się udadzą.</p>
            <p>Pod okiem opiekunów pogłębiają swoją wiarę i więź z Kościołem, uczą się odpowiedzialności za wspólnotę, poznają liturgię. Poprzez spotkania, modlitwę, zabawę i wspólnie spędzony czas dziewczęta odkrywają swoje talenty i uczą się, że świętość zaczyna się w codzienności.</p>

            <h3>Wartości formacji</h3>
            <p>Formacja bielanek, inspirowana postawą Maryi – Tej, która uczy, jak pięknie i mądrze wzrastać, kładzie szczególny nacisk na następujące wartości:</p>

            <div class="lso-values">
                <div class="lso-value-card">
                    <div class="lso-value-icon">🤍</div>
                    <h4>Czystość serca</h4>
                    <p>Rozumiana nie tylko jako niewinność, ale jako szczerość intencji, prostota i prawda w relacjach.</p>
                </div>
                <div class="lso-value-card">
                    <div class="lso-value-icon">🌸</div>
                    <h4>Pokora</h4>
                    <p>Umiejętność zauważania innych, gotowość do służby bez potrzeby bycia w centrum.</p>
                </div>
                <div class="lso-value-card">
                    <div class="lso-value-icon">🕊</div>
                    <h4>Posłuszeństwo i zaufanie Bogu</h4>
                    <p>Przyjęcie Maryjnego <em>„fiat"</em>, które uczy odwagi w czynieniu dobra, nawet gdy nie jest to łatwe.</p>
                </div>
                <div class="lso-value-card">
                    <div class="lso-value-icon">☀️</div>
                    <h4>Radość</h4>
                    <p>Prawda wypływająca z bycia blisko Boga i ludzi.</p>
                </div>
                <div class="lso-value-card">
                    <div class="lso-value-icon">💛</div>
                    <h4>Wrażliwość na drugiego człowieka</h4>
                    <p>Gotowość do niesienia pomocy, uśmiechu i nadziei tam, gdzie są potrzebne.</p>
                </div>
            </div>

            <h3>Spotkania</h3>
            <p>Spotkania odbywają się <strong>co dwa tygodnie w soboty o godzinie 10:00</strong> w Sali Caritas.</p>

            <div class="lso-dates">
                <h4>Terminy najbliższych spotkań:</h4>
                <div class="lso-dates-grid">
                    <span class="lso-date">25.04</span>
                    <span class="lso-date">9.05</span>
                    <span class="lso-date">23.05</span>
                    <span class="lso-date">6.06</span>
                    <span class="lso-date">20.06</span>
                </div>
                <p class="lso-dates-note">📍 Sala Caritas</p>
            </div>

            <div class="lso-motto-box lso-motto-box--bielanki">
                <p>✿ Zapraszamy wszystkie dziewczęta! ✿</p>
                <p><em>Z radością, we wspólnocie idziemy z Maryją do Jezusa!</em></p>
            </div>

            <h3>Opiekunki</h3>
            <div class="lso-contact">
                <div class="lso-contact-card">
                    <div class="lso-contact-icon">🌸</div>
                    <div>
                        <strong>p. Magda</strong>
                        <span>Opiekunka grupy</span>
                    </div>
                </div>
                <div class="lso-contact-card">
                    <div class="lso-contact-icon">🌸</div>
                    <div>
                        <strong>p. Agnieszka</strong>
                        <span>Opiekunka grupy</span>
                    </div>
                </div>
            </div>
            <p>Kontakt: <a href="mailto:bielankimrozy@gmail.com" class="lso-mail">bielankimrozy@gmail.com</a></p>

            <h3>Galeria</h3>
            <div class="lso-gallery" id="gallery-bielanki">
                <?php
                $gallery_ids = get_post_meta( get_the_ID(), 'lso_gallery_bielanki', true );
                if ( $gallery_ids ) :
                    $ids = explode( ',', $gallery_ids );
                    foreach ( $ids as $img_id ) :
                        $img  = wp_get_attachment_image_src( trim($img_id), 'medium' );
                        $full = wp_get_attachment_image_src( trim($img_id), 'large' );
                        if ( $img ) : ?>
                        <button class="lso-gallery-item"
                                data-full="<?php echo esc_url($full[0]); ?>"
                                data-gallery="bielanki"
                                aria-label="Powiększ zdjęcie">
                            <img src="<?php echo esc_url($img[0]); ?>" alt="" loading="lazy">
                        </button>
                        <?php endif;
                    endforeach;
                else : ?>
                <p class="lso-gallery-empty">📷 Zdjęcia wkrótce</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
    </div><!-- / lso-wrap -->

</article>

</div><!-- .content-area -->
</div><!-- .container -->
</main>

<style>
.lso-wrap { max-width: 820px; margin: 0 auto; }

.lso-panel--standalone {
    border: 1px solid var(--color-gold);
    border-radius: 8px;
    padding: 40px;
    margin-bottom: 40px;
}
@media (max-width: 600px) {
    .lso-panel--standalone { padding: 24px 18px; }
}

.lso-hero {
    text-align: center;
    padding: 32px 20px 36px;
    margin: -40px -40px 36px;
    border-radius: 8px 8px 0 0;
}
.lso-hero--bielanki {
    background: linear-gradient(135deg, #2a1a2e 0%, #4e1f3a 50%, #2a1a2e 100%);
}
.lso-hero-icon { font-size: 2.5rem; margin-bottom: 12px; opacity: 0.9; }
.lso-hero h2 {
    font-family: var(--font-heading);
    font-size: clamp(1.4rem, 4vw, 2rem);
    font-weight: 700;
    color: var(--color-gold);
    margin-bottom: 10px;
    letter-spacing: 0.04em;
}
.lso-hero-motto { font-size: 1rem; font-style: italic; color: rgba(201,168,76,0.75); }
@media (max-width: 600px) {
    .lso-hero { margin: -24px -18px 28px; padding: 28px 16px 30px; }
}

.lso-content p { line-height: 1.8; margin-bottom: 16px; color: var(--color-dark); }
[data-theme="dark"] .lso-content p { color: var(--text-primary); }
.lso-content h3 {
    font-family: var(--font-heading);
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--color-gold-dark);
    margin: 32px 0 14px;
    padding-bottom: 6px;
    border-bottom: 1px solid rgba(201,168,76,0.25);
    letter-spacing: 0.03em;
}

.lso-values {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin: 20px 0 28px;
}
.lso-value-card {
    background: rgba(201,168,76,0.06);
    border: 1px solid rgba(201,168,76,0.2);
    border-radius: 8px;
    padding: 20px 18px;
    transition: border-color 0.25s;
}
.lso-value-card:hover { border-color: var(--color-gold); }
.lso-value-icon { font-size: 1.5rem; margin-bottom: 8px; }
.lso-value-card h4 {
    font-family: var(--font-heading);
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--color-gold-dark);
    margin-bottom: 8px;
}
.lso-value-card p { font-size: 0.85rem !important; line-height: 1.6; margin-bottom: 0 !important; }

.lso-dates {
    background: rgba(201,168,76,0.06);
    border: 1px solid rgba(201,168,76,0.2);
    border-radius: 8px;
    padding: 24px 28px;
    margin: 20px 0 28px;
}
.lso-dates h4 {
    font-family: var(--font-heading);
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--color-gold-dark);
    margin-bottom: 14px;
}
.lso-dates-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.lso-date {
    background: var(--color-gold);
    color: var(--color-dark);
    font-size: 0.88rem;
    font-weight: 700;
    padding: 6px 16px;
    border-radius: 20px;
    letter-spacing: 0.04em;
}
.lso-dates-note { font-size: 0.88rem; color: var(--color-gold-dark); margin: 0 !important; font-style: italic; }

.lso-motto-box {
    margin-top: 36px;
    text-align: center;
    padding: 24px 20px;
    border-top: 1px solid rgba(201,168,76,0.3);
    border-bottom: 1px solid rgba(201,168,76,0.3);
}
.lso-motto-box p {
    font-family: var(--font-heading);
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--color-gold-dark) !important;
    margin-bottom: 4px !important;
    letter-spacing: 0.04em;
}
.lso-motto-box--bielanki p:last-child {
    font-size: 0.95rem;
    font-weight: 400;
    color: var(--color-gold-dark) !important;
}

.lso-contact { display: flex; flex-wrap: wrap; gap: 14px; margin: 16px 0 24px; }
.lso-contact-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: rgba(201,168,76,0.07);
    border: 1px solid rgba(201,168,76,0.25);
    border-radius: 10px;
    padding: 14px 20px;
    flex: 1;
    min-width: 200px;
    transition: border-color 0.25s;
}
.lso-contact-card:hover { border-color: var(--color-gold); }
.lso-contact-icon { font-size: 1.6rem; flex-shrink: 0; }
.lso-contact-card div { display: flex; flex-direction: column; gap: 3px; }
.lso-contact-card strong { font-family: var(--font-heading); font-size: 0.95rem; color: var(--color-gold-dark); }
.lso-contact-card span { font-size: 0.82rem; color: var(--color-text-light); font-style: italic; }
[data-theme="dark"] .lso-contact-card span { color: var(--text-secondary); }
.lso-mail {
    color: var(--color-gold-dark);
    font-weight: 600;
    text-decoration: none;
    border-bottom: 1px solid rgba(201,168,76,0.4);
    transition: border-color 0.2s;
}
.lso-mail:hover { border-color: var(--color-gold); }

.lso-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
    margin: 16px 0 28px;
}
.lso-gallery-item {
    display: block;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(201,168,76,0.2);
    transition: border-color 0.25s, transform 0.25s;
    aspect-ratio: 4/3;
    cursor: zoom-in;
    background: none;
    padding: 0;
}
.lso-gallery-item:hover { border-color: var(--color-gold); transform: scale(1.02); }
.lso-gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.lso-gallery-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px 20px;
    color: var(--color-text-light);
    font-style: italic;
    font-size: 0.9rem;
    border: 1px dashed rgba(201,168,76,0.25);
    border-radius: 8px;
}
[data-theme="dark"] .lso-gallery-empty { color: var(--text-secondary); }

.lso-lightbox {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0,0,0,0.92);
    align-items: center;
    justify-content: center;
    animation: lsoLBIn 0.2s ease;
}
@keyframes lsoLBIn { from { opacity:0 } to { opacity:1 } }
.lso-lightbox.open { display: flex; }
.lso-lightbox-img-wrap {
    position: relative;
    max-width: 92vw;
    max-height: 90vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.lso-lightbox img {
    max-width: 90vw;
    max-height: 86vh;
    border-radius: 6px;
    box-shadow: 0 8px 48px rgba(0,0,0,0.6);
    object-fit: contain;
    display: block;
    animation: lsoImgIn 0.2s ease;
}
@keyframes lsoImgIn { from { opacity:0; transform:scale(0.96) } to { opacity:1; transform:scale(1) } }
.lso-lb-btn {
    position: fixed;
    background: rgba(201,168,76,0.15);
    border: 1px solid rgba(201,168,76,0.4);
    color: #c9a84c;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    font-size: 1.3rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, transform 0.15s;
    z-index: 10000;
    line-height: 1;
}
.lso-lb-btn:hover { background: rgba(201,168,76,0.35); transform: scale(1.1); }
.lso-lb-close { top: 18px; right: 18px; font-size: 1.5rem; }
.lso-lb-prev  { left: 16px;  top: 50%; transform: translateY(-50%); }
.lso-lb-next  { right: 16px; top: 50%; transform: translateY(-50%); }
.lso-lb-prev:hover { transform: translateY(-50%) scale(1.1); }
.lso-lb-next:hover { transform: translateY(-50%) scale(1.1); }
.lso-lb-counter {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    color: rgba(201,168,76,0.7);
    font-size: 0.85rem;
    letter-spacing: 0.1em;
}
@media (max-width: 600px) {
    .lso-lb-prev { left: 6px; }
    .lso-lb-next { right: 6px; }
    .lso-lb-btn  { width: 40px; height: 40px; font-size: 1.1rem; }
}
</style>

<!-- LIGHTBOX -->
<div class="lso-lightbox" id="lsoLightbox" role="dialog" aria-modal="true" aria-label="Podgląd zdjęcia">
    <button class="lso-lb-btn lso-lb-close" id="lsoClose" aria-label="Zamknij">&times;</button>
    <button class="lso-lb-btn lso-lb-prev"  id="lsoPrev"  aria-label="Poprzednie">&#8249;</button>
    <div class="lso-lightbox-img-wrap">
        <img src="" alt="" id="lsoImg">
    </div>
    <button class="lso-lb-btn lso-lb-next"  id="lsoNext"  aria-label="Następne">&#8250;</button>
    <div class="lso-lb-counter" id="lsoCounter"></div>
</div>

<script>
(function () {
    const lb       = document.getElementById('lsoLightbox');
    const lbImg    = document.getElementById('lsoImg');
    const lbCount  = document.getElementById('lsoCounter');
    const btnClose = document.getElementById('lsoClose');
    const btnPrev  = document.getElementById('lsoPrev');
    const btnNext  = document.getElementById('lsoNext');

    let items = [], current = 0;

    function openLightbox(index) {
        items = Array.from(document.querySelectorAll('.lso-gallery-item')).map(function(btn) {
            return btn.getAttribute('data-full');
        });
        current = index;
        showImage();
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
        btnClose.focus();
    }
    function closeLightbox() {
        lb.classList.remove('open');
        document.body.style.overflow = '';
        lbImg.src = '';
    }
    function showImage() {
        lbImg.style.opacity = '0';
        lbImg.src = items[current];
        lbImg.onload = function() { lbImg.style.transition = 'opacity 0.2s'; lbImg.style.opacity = '1'; };
        lbCount.textContent = (current + 1) + ' / ' + items.length;
        btnPrev.style.display = items.length > 1 ? 'flex' : 'none';
        btnNext.style.display = items.length > 1 ? 'flex' : 'none';
    }
    function prev() { current = (current - 1 + items.length) % items.length; showImage(); }
    function next() { current = (current + 1) % items.length; showImage(); }

    document.querySelectorAll('.lso-gallery-item').forEach(function(btn, i) {
        btn.addEventListener('click', function() { openLightbox(i); });
    });

    btnClose.addEventListener('click', closeLightbox);
    btnPrev.addEventListener('click', prev);
    btnNext.addEventListener('click', next);
    lb.addEventListener('click', function(e) { if (e.target === lb) closeLightbox(); });
    document.addEventListener('keydown', function(e) {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape')     closeLightbox();
        if (e.key === 'ArrowLeft')  prev();
        if (e.key === 'ArrowRight') next();
    });
    let touchStartX = 0;
    lb.addEventListener('touchstart', function(e) { touchStartX = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function(e) {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) diff > 0 ? next() : prev();
    }, { passive: true });
})();
</script>

<?php get_footer(); ?>
