<?php

$panoramy = [
    [
        'plik'  => 'images/360/gorny360-after.jpeg',
        'tytul' => 'Kościół górny',
        'opis'  => 'Wnętrze kościoła górnego pw. Św. Teresy od Dzieciątka Jezus',
        'yaw'   => 0,
        'pitch' => 0,
    ],
    [
        'plik'  => 'images/360/dolny360.jpg',
        'tytul' => 'Kościół dolny',
        'opis'  => 'Wnętrze kościoła dolnego — kaplica codzienna',
        'yaw'   => 0,
        'pitch' => 0,
    ],
];

get_header();
?>

<main id="primary" class="site-main spacer-page" role="main">

    <!-- ── Hero ── -->
    <div class="galerie-hero">
        <div class="container">
            <p class="galerie-hero__eyebrow">Parafia pw. św. Teresy od Dzieciątka Jezus</p>
            <h1 class="galerie-hero__title">Wirtualny spacer</h1>
            <p class="galerie-hero__subtitle">Zajrzyj do naszego kościoła — obróć widok myszką lub palcem i odkryj każdy kąt świątyni.</p>
        </div>
    </div>

    <div class="container">

        <!-- ── Viewer 360° (początkowo ukryty) ── -->
        <div class="spacer-viewer" id="spacer-viewer" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Widok 360°">
            <div class="spacer-viewer__bar">
                <span class="spacer-viewer__title" id="spacer-viewer-title"></span>
                <div class="spacer-viewer__controls">
                    <button class="spacer-viewer__btn" id="spacer-fullscreen" aria-label="Pełny ekran" title="Pełny ekran">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                    </button>
                    <button class="spacer-viewer__btn spacer-viewer__close" id="spacer-close" aria-label="Zamknij widok">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="spacer-viewer__pano" id="spacer-pano"></div>
            <p class="spacer-viewer__hint" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/><path d="M12 8v4l3 3"/></svg>
                Przeciągnij myszką lub palcem aby obracać widok · Scroll = zoom
            </p>
        </div>

        <!-- ── Siatka miniatur ── -->
        <div class="spacer-grid" id="spacer-grid">
            <?php foreach ( $panoramy as $i => $p ) :
                $img_url = get_template_directory_uri() . '/' . $p['plik'];
            ?>
            <button
                class="spacer-card"
                data-index="<?php echo esc_attr( $i ); ?>"
                data-src="<?php echo esc_url( $img_url ); ?>"
                data-yaw="<?php echo esc_attr( $p['yaw'] ); ?>"
                data-pitch="<?php echo esc_attr( $p['pitch'] ); ?>"
                data-title="<?php echo esc_attr( $p['tytul'] ); ?>"
                aria-label="Otwórz panoramę: <?php echo esc_attr( $p['tytul'] ); ?>"
            >
                <div class="spacer-card__thumb">
                    <img
                        src="<?php echo esc_url( $img_url ); ?>"
                        alt="Podgląd panoramy: <?php echo esc_attr( $p['tytul'] ); ?>"
                        loading="lazy"
                    >
                    <div class="spacer-card__overlay" aria-hidden="true">
                        <span class="spacer-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="32" height="32" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        </span>
                        <span class="spacer-card__label">Otwórz panoramę 360°</span>
                    </div>
                </div>
                <div class="spacer-card__body">
                    <h2 class="spacer-card__title"><?php echo esc_html( $p['tytul'] ); ?></h2>
                    <p class="spacer-card__desc"><?php echo esc_html( $p['opis'] ); ?></p>
                </div>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- ── Informacja o przyszłych funkcjach ── -->
        <p class="spacer-coming-soon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Wkrótce: pełny wirtualny spacer z możliwością przechodzenia między ujęciami.
        </p>

    </div><!-- /.container -->

</main>

<!-- Pannellum CSS + JS (jsdelivr 2.5.7) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.7/build/pannellum.css">
<script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.7/build/pannellum.js" defer></script>

<script>
(function () {
    var viewer   = null;
    var viewerEl = document.getElementById('spacer-viewer');
    var panoEl   = document.getElementById('spacer-pano');
    var titleEl  = document.getElementById('spacer-viewer-title');
    var closeBtn = document.getElementById('spacer-close');
    var fsBtn    = document.getElementById('spacer-fullscreen');

    function openPanorama(src, yaw, pitch, title) {
        if (viewer) { try { viewer.destroy(); } catch(e) {} viewer = null; }

        titleEl.textContent = title;
        viewerEl.classList.add('spacer-viewer--open');
        viewerEl.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        function init() {
            if (typeof pannellum === 'undefined') { setTimeout(init, 50); return; }
            viewer = pannellum.viewer('spacer-pano', {
                type:           'equirectangular',
                panorama:       src,
                yaw:            yaw,
                pitch:          pitch,
                hfov:           100,
                autoLoad:       true,
                showControls:   false,
                mouseZoom:      true,
                keyboardZoom:   true,
                showFullscreenCtrl: false,
                strings: {
                    loadingLabel: 'Ładowanie panoramy…',
                    bylineLabel:  '',
                },
            });
        }
        init();
    }

    function closePanorama() {
        viewerEl.classList.remove('spacer-viewer--open');
        viewerEl.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (viewer) { try { viewer.destroy(); } catch(e) {} viewer = null; }
    }

    document.querySelectorAll('.spacer-card').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openPanorama(
                btn.dataset.src,
                parseFloat(btn.dataset.yaw)   || 0,
                parseFloat(btn.dataset.pitch) || 0,
                btn.dataset.title || ''
            );
        });
    });

    closeBtn.addEventListener('click', closePanorama);

    fsBtn.addEventListener('click', function () {
        var el = viewerEl;
        if (!document.fullscreenElement) {
            (el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || function(){}).call(el);
        } else {
            (document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || function(){}).call(document);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePanorama();
    });
})();
</script>

<?php get_footer(); ?>
