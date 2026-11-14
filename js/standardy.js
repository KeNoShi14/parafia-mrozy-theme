/* Standardy ochrony dzieci – wyświetlanie wszystkich stron PDF na stronie (pdf.js). */
(function () {
    'use strict';

    var viewer = document.getElementById('sod-viewer');
    if (!viewer) return;

    var url = viewer.getAttribute('data-pdf');
    var loading = viewer.querySelector('.sod-viewer__loading');

    function fail() {
        viewer.innerHTML =
            '<p class="sod-viewer__error">Nie udało się wyświetlić dokumentu na stronie. ' +
            '<a href="' + url + '" target="_blank" rel="noopener">Otwórz plik PDF</a>.</p>';
    }

    function start(tries) {
        tries = tries || 0;
        if (typeof window.pdfjsLib === 'undefined') {
            if (tries < 50) return setTimeout(function () { start(tries + 1); }, 100);
            return fail();
        }
        var lib = window.pdfjsLib;
        lib.GlobalWorkerOptions.workerSrc = (window.pmStandardy && window.pmStandardy.workerSrc) || '';

        lib.getDocument(url).promise.then(function (pdf) {
            if (loading) loading.remove();
            var pages = [];

            for (var n = 1; n <= pdf.numPages; n++) {
                var box = document.createElement('div');
                box.className = 'sod-page';
                box.setAttribute('aria-label', 'Strona ' + n + ' z ' + pdf.numPages);
                box.dataset.page = n;
                var canvas = document.createElement('canvas');
                canvas.setAttribute('role', 'img');
                canvas.setAttribute('aria-label', 'Strona ' + n + ' dokumentu Standardy ochrony dzieci');
                box.appendChild(canvas);
                var nr = document.createElement('span');
                nr.className = 'sod-page__nr';
                nr.textContent = n + ' / ' + pdf.numPages;
                box.appendChild(nr);
                viewer.appendChild(box);
                pages.push(box);
            }

            var rendered = {};
            var lastWidth = 0;

            function render(box) {
                var n = +box.dataset.page;
                var width = viewer.clientWidth;
                if (rendered[n] === width) return;
                rendered[n] = width;
                pdf.getPage(n).then(function (page) {
                    var base = page.getViewport({ scale: 1 });
                    var ratio = window.devicePixelRatio || 1;
                    var scale = Math.min(width / base.width, 2);
                    var vp = page.getViewport({ scale: scale * ratio });
                    var canvas = box.querySelector('canvas');
                    canvas.width = vp.width;
                    canvas.height = vp.height;
                    page.render({ canvasContext: canvas.getContext('2d'), viewport: vp });
                });
            }

            // Zarezerwuj miejsce (proporcje pierwszej strony), żeby przewijanie nie skakało.
            pdf.getPage(1).then(function (p) {
                var v = p.getViewport({ scale: 1 });
                pages.forEach(function (b) { b.style.aspectRatio = v.width + ' / ' + v.height; });
            });

            if ('IntersectionObserver' in window) {
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (e) { if (e.isIntersecting) render(e.target); });
                }, { rootMargin: '600px 0px' });
                pages.forEach(function (b) { io.observe(b); });
            } else {
                pages.forEach(render);
            }

            lastWidth = viewer.clientWidth;
            var t;
            window.addEventListener('resize', function () {
                clearTimeout(t);
                t = setTimeout(function () {
                    if (Math.abs(viewer.clientWidth - lastWidth) < 40) return;
                    lastWidth = viewer.clientWidth;
                    var top = window.innerHeight;
                    pages.forEach(function (b) {
                        var r = b.getBoundingClientRect();
                        if (r.bottom > -600 && r.top < top + 600) render(b);
                        else delete rendered[+b.dataset.page];
                    });
                }, 200);
            });
        }).catch(fail);
    }

    start();
})();
