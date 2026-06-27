(function () {
    'use strict';

    var PDFJS_VERSION = '3.4.120';
    var CDN = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/' + PDFJS_VERSION + '/';

    function forceHttps(url) {
        if (!url) return url;
        return url.replace(/^http:\/\//i, 'https://');
    }

    function waitForPdfJs(cb, tries) {
        tries = tries || 0;
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                (window.pmPdfjs && window.pmPdfjs.workerSrc)
                    ? window.pmPdfjs.workerSrc
                    : CDN + 'pdf.worker.min.js';
            cb();
        } else if (tries < 40) {
            setTimeout(function () { waitForPdfJs(cb, tries + 1); }, 100);
        }
    }

    var modal, modalCanvas, modalClose, modalPrev, modalNext,
        modalPageInfo, modalDownload, modalSpinner;
    var currentPdf  = null;
    var currentPage = 1;
    var totalPages  = 0;
    var renderTask  = null;
    var zoomLevel   = 1;

    function buildModal() {
        if (document.getElementById('pm-pdf-modal')) return;

        var m = document.createElement('div');
        m.id = 'pm-pdf-modal';
        m.setAttribute('role', 'dialog');
        m.setAttribute('aria-modal', 'true');
        m.setAttribute('aria-label', 'Podgląd intencji');
        m.innerHTML = [
            '<div class="pm-modal-backdrop"></div>',
            '<div class="pm-modal-box">',
            '  <div class="pm-modal-header">',
            '    <div class="pm-modal-nav">',
            '      <button class="pm-btn-nav" id="pm-pdf-prev" aria-label="Poprzednia strona">&#8249;</button>',
            '      <span class="pm-page-info" id="pm-page-info">1 / 1</span>',
            '      <button class="pm-btn-nav" id="pm-pdf-next" aria-label="Następna strona">&#8250;</button>',
            '    </div>',
            '    <div class="pm-modal-actions">',
            '      <button class="pm-btn-zoom" id="pm-zoom-out" aria-label="Pomniejsz">−</button>',
            '      <button class="pm-btn-zoom" id="pm-zoom-reset" aria-label="Domyślny rozmiar" style="font-size:11px;width:auto;padding:0 8px;">100%</button>',
            '      <button class="pm-btn-zoom" id="pm-zoom-in" aria-label="Powiększ">+</button>',
            '      <a class="pm-btn-download" id="pm-pdf-download" href="#" download target="_blank" aria-label="Pobierz PDF">',
            '        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
            '        Pobierz',
            '      </a>',
            '      <button class="pm-btn-close" id="pm-pdf-close" aria-label="Zamknij">',
            '        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
            '      </button>',
            '    </div>',
            '  </div>',
            '  <div class="pm-modal-body">',
            '    <div class="pm-modal-spinner" id="pm-modal-spinner">',
            '      <div class="pm-spinner-ring"></div>',
            '    </div>',
            '    <canvas id="pm-modal-canvas"></canvas>',
            '  </div>',
            '</div>',
        ].join('');

        document.body.appendChild(m);

        modal        = m;
        modalCanvas  = m.querySelector('#pm-modal-canvas');
        modalClose   = m.querySelector('#pm-pdf-close');
        modalPrev    = m.querySelector('#pm-pdf-prev');
        modalNext    = m.querySelector('#pm-pdf-next');
        modalPageInfo= m.querySelector('#pm-page-info');
        modalDownload= m.querySelector('#pm-pdf-download');
        modalSpinner = m.querySelector('#pm-modal-spinner');

        var zoomStep     = 0.25;
        var zoomMin      = 0.5;
        var zoomMax      = 3;
        var zoomResetBtn = m.querySelector('#pm-zoom-reset');

        function applyZoom() {
            modalCanvas.style.transform       = 'scale(' + zoomLevel + ')';
            modalCanvas.style.transformOrigin = 'top center';
            modalCanvas.style.marginBottom    = zoomLevel > 1
                ? ((zoomLevel - 1) * modalCanvas.offsetHeight * 0.5) + 'px'
                : '0';
            zoomResetBtn.textContent = Math.round(zoomLevel * 100) + '%';
        }

        m.querySelector('#pm-zoom-in').addEventListener('click', function() {
            if (zoomLevel < zoomMax) { zoomLevel = Math.round((zoomLevel + zoomStep) * 100) / 100; applyZoom(); }
        });
        m.querySelector('#pm-zoom-out').addEventListener('click', function() {
            if (zoomLevel > zoomMin) { zoomLevel = Math.round((zoomLevel - zoomStep) * 100) / 100; applyZoom(); }
        });
        zoomResetBtn.addEventListener('click', function() {
            zoomLevel = 1; applyZoom();
        });

        m.querySelector('.pm-modal-body').addEventListener('wheel', function(e) {
            if (!e.ctrlKey) return;
            e.preventDefault();
            if (e.deltaY < 0 && zoomLevel < zoomMax) { zoomLevel = Math.round((zoomLevel + zoomStep) * 100) / 100; }
            if (e.deltaY > 0 && zoomLevel > zoomMin) { zoomLevel = Math.round((zoomLevel - zoomStep) * 100) / 100; }
            applyZoom();
        }, { passive: false });

        var initialPinchDist = null;
        var zoomAtPinchStart = 1;

        m.querySelector('.pm-modal-body').addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                var dx = e.touches[0].clientX - e.touches[1].clientX;
                var dy = e.touches[0].clientY - e.touches[1].clientY;
                initialPinchDist = Math.sqrt(dx * dx + dy * dy);
                zoomAtPinchStart = zoomLevel;
            }
        }, { passive: true });

        m.querySelector('.pm-modal-body').addEventListener('touchmove', function(e) {
            if (e.touches.length === 2 && initialPinchDist) {
                e.preventDefault();
                var dx = e.touches[0].clientX - e.touches[1].clientX;
                var dy = e.touches[0].clientY - e.touches[1].clientY;
                var dist = Math.sqrt(dx * dx + dy * dy);
                var ratio = dist / initialPinchDist;
                zoomLevel = Math.min(zoomMax, Math.max(zoomMin, Math.round(zoomAtPinchStart * ratio * 100) / 100));
                applyZoom();
            }
        }, { passive: false });

        m.querySelector('.pm-modal-body').addEventListener('touchend', function(e) {
            if (e.touches.length < 2) initialPinchDist = null;
        }, { passive: true });

        modalClose.addEventListener('click', closeModal);
        m.querySelector('.pm-modal-backdrop').addEventListener('click', closeModal);
        modalPrev.addEventListener('click', function () { goToPage(currentPage - 1); });
        modalNext.addEventListener('click', function () { goToPage(currentPage + 1); });

        document.addEventListener('keydown', function (e) {
            if (!modal.classList.contains('pm-open')) return;
            if (e.key === 'Escape')      closeModal();
            if (e.key === 'ArrowLeft')   goToPage(currentPage - 1);
            if (e.key === 'ArrowRight')  goToPage(currentPage + 1);
            if (e.key === '+' || (e.ctrlKey && e.key === '=')) { e.preventDefault(); if (zoomLevel < zoomMax) { zoomLevel = Math.round((zoomLevel + zoomStep) * 100) / 100; applyZoom(); } }
            if (e.key === '-' && e.ctrlKey) { e.preventDefault(); if (zoomLevel > zoomMin) { zoomLevel = Math.round((zoomLevel - zoomStep) * 100) / 100; applyZoom(); } }
        });
    }

    function openModal(pdfUrl) {
        pdfUrl = forceHttps(pdfUrl);
        buildModal();
        modal.classList.add('pm-open');
        document.body.style.overflow = 'hidden';
        modalSpinner.style.display = 'flex';
        modalCanvas.style.display  = 'none';
        modalDownload.href = pdfUrl;
        currentPage = 1;
        currentPdf  = null;
        zoomLevel   = 1;

        pdfjsLib.getDocument({
            url: pdfUrl,
            cMapUrl: CDN + 'cmaps/',
            cMapPacked: true,
        }).promise.then(function (pdf) {
            currentPdf = pdf;
            totalPages = pdf.numPages;
            renderModalPage(1);
        }).catch(function (err) {
            console.warn('Modal PDF error:', err);
            var errMsg = err && err.message ? err.message : '';
            var hint = errMsg.indexOf('http') !== -1 || errMsg.indexOf('fetch') !== -1
                ? 'Sprawdź czy plik jest dostępny przez HTTPS.'
                : 'Plik PDF jest niedostępny lub uszkodzony.';
            modalSpinner.innerHTML = '<div style="text-align:center;padding:20px;">'
                + '<svg width="40" height="40" viewBox="0 0 60 75" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:12px;">'
                + '<rect x="2" y="2" width="56" height="71" rx="4" fill="none" stroke="#c9a84c" stroke-width="1.5"/>'
                + '<text x="30" y="48" text-anchor="middle" font-family="Montserrat,sans-serif" font-size="13" font-weight="700" fill="#c9a84c">PDF</text>'
                + '</svg>'
                + '<p style="color:#c9a84c;font-size:13px;margin:0;">' + hint + '</p>'
                + '<p style="color:#888;font-size:11px;margin:8px 0 0;">Możesz pobrać plik bezpośrednio.</p>'
                + '</div>';
        });
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('pm-open');
        document.body.style.overflow = '';
        if (renderTask) { renderTask.cancel(); renderTask = null; }
        currentPdf = null;
    }

    function goToPage(n) {
        if (!currentPdf) return;
        n = Math.max(1, Math.min(n, totalPages));
        if (n === currentPage) return;
        currentPage = n;
        renderModalPage(n);
    }

    function renderModalPage(n) {
        if (!currentPdf) return;
        if (renderTask) { renderTask.cancel(); renderTask = null; }

        modalSpinner.style.display = 'flex';
        modalCanvas.style.display  = 'none';

        var dpr = window.devicePixelRatio || 1;

        currentPdf.getPage(n).then(function (page) {
            var maxW = modal.querySelector('.pm-modal-body').clientWidth - 32;
            var maxH = window.innerHeight * 0.80;
            var vp   = page.getViewport({ scale: 1 });
            var scale = Math.min(maxW / vp.width, maxH / vp.height);
            var scaledVp = page.getViewport({ scale: scale * dpr });

            modalCanvas.width  = scaledVp.width;
            modalCanvas.height = scaledVp.height;
            modalCanvas.style.width  = Math.floor(scaledVp.width  / dpr) + 'px';
            modalCanvas.style.height = Math.floor(scaledVp.height / dpr) + 'px';
            modalCanvas.style.transform    = '';
            modalCanvas.style.marginBottom = '';

            var ctx = modalCanvas.getContext('2d');
            ctx.clearRect(0, 0, modalCanvas.width, modalCanvas.height);

            renderTask = page.render({ canvasContext: ctx, viewport: scaledVp });
            return renderTask.promise;
        }).then(function () {
            modalSpinner.style.display = 'none';
            modalCanvas.style.display  = 'block';
            modalPageInfo.textContent  = currentPage + ' / ' + totalPages;
            modalPrev.disabled = currentPage <= 1;
            modalNext.disabled = currentPage >= totalPages;
            renderTask = null;
        }).catch(function (err) {
            if (err && err.name === 'RenderingCancelledException') return;
            console.warn('Render error:', err);
        });
    }

    function renderCardPreview(wrap) {
        var pdfUrl = forceHttps(wrap.getAttribute('data-pdf'));
        var canvas = wrap.querySelector('.pdf-preview-canvas');
        var loader = wrap.querySelector('.pdf-preview-loading');
        if (!pdfUrl || !canvas) return;

        pdfjsLib.getDocument({
            url: pdfUrl,
            cMapUrl: CDN + 'cmaps/',
            cMapPacked: true,
        }).promise.then(function (pdf) {
            return pdf.getPage(1);
        }).then(function (page) {
            var dpr = window.devicePixelRatio || 1;
            var w  = wrap.offsetWidth || 320;
            var vp = page.getViewport({ scale: 1 });
            var scale = w / vp.width;
            var sv = page.getViewport({ scale: scale * dpr });

            canvas.width  = sv.width;
            canvas.height = sv.height;
            canvas.style.width  = w + 'px';
            canvas.style.height = Math.floor(sv.height / dpr) + 'px';

            return page.render({ canvasContext: canvas.getContext('2d'), viewport: sv }).promise;
        }).then(function () {
            if (loader) loader.style.display = 'none';
            canvas.style.display = 'block';
        }).catch(function (err) {
            console.warn('Card preview error:', err);
            if (loader) {
                loader.innerHTML = [
                    '<svg viewBox="0 0 60 75" xmlns="http://www.w3.org/2000/svg" width="48" height="60">',
                    '<rect x="2" y="2" width="56" height="71" rx="4" fill="none" stroke="#c9a84c" stroke-width="1.5"/>',
                    '<path d="M36 2 L56 22 L36 22 Z" fill="#c9a84c" opacity=".4"/>',
                    '<text x="30" y="48" text-anchor="middle" font-family="Montserrat,sans-serif" font-size="13" font-weight="700" fill="#c9a84c">PDF</text>',
                    '</svg>',
                ].join('');
            }
        });
    }

    function init() {
        buildModal();

        document.querySelectorAll('.intencja-card.has-pdf[data-pdf]').forEach(function (card) {
            var url = card.getAttribute('data-pdf');
            if (!url) return;

            card.style.cursor = 'pointer';

            card.addEventListener('click', function () {
                openModal(url);
            });

            card.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openModal(url);
                }
            });
        });

        var wraps = document.querySelectorAll('.pdf-preview-wrap');
        if (!wraps.length) return;

        if ('IntersectionObserver' in window) {
            var obs = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        renderCardPreview(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '120px' });
            wraps.forEach(function (w) { obs.observe(w); });
        } else {
            wraps.forEach(renderCardPreview);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        waitForPdfJs(init);
    });

})();
