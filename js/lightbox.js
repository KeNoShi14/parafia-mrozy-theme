(function () {
    'use strict';

    var lb, lbImg, lbClose, lbPrev, lbNext, lbCounter, lbCaption, lbSpinner;
    var items   = [];
    var current = 0;
    var lbBuilt = false;

    function buildLightbox() {
        if (lbBuilt) return;

        if (!document.getElementById('pm-lightbox')) {
            var div = document.createElement('div');
            div.innerHTML = '<div id="pm-lightbox" role="dialog" aria-modal="true" aria-label="Podgląd zdjęcia">' +
                '<div class="pm-lb-backdrop"></div>' +
                '<div class="pm-lb-ui">' +
                    '<div class="pm-lb-header">' +
                        '<span class="pm-lb-counter" id="pm-lb-counter"></span>' +
                        '<button class="pm-lb-close" id="pm-lb-close" aria-label="Zamknij">' +
                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                        '</button>' +
                    '</div>' +
                    '<div class="pm-lb-stage">' +
                        '<button class="pm-lb-nav pm-lb-prev" id="pm-lb-prev" aria-label="Poprzednie zdjęcie">&#8249;</button>' +
                        '<div class="pm-lb-img-wrap">' +
                            '<div class="pm-lb-spinner"><div class="pm-spinner-ring"></div></div>' +
                            '<img id="pm-lb-img" src="" alt="">' +
                        '</div>' +
                        '<button class="pm-lb-nav pm-lb-next" id="pm-lb-next" aria-label="Następne zdjęcie">&#8250;</button>' +
                    '</div>' +
                    '<div class="pm-lb-caption" id="pm-lb-caption"></div>' +
                '</div>' +
            '</div>';
            document.body.appendChild(div.firstChild);
        }

        lb        = document.getElementById('pm-lightbox');
        lbImg     = document.getElementById('pm-lb-img');
        lbClose   = document.getElementById('pm-lb-close');
        lbPrev    = document.getElementById('pm-lb-prev');
        lbNext    = document.getElementById('pm-lb-next');
        lbCounter = document.getElementById('pm-lb-counter');
        lbCaption = document.getElementById('pm-lb-caption');
        lbSpinner = lb ? lb.querySelector('.pm-lb-spinner') : null;
        if (!lb) return;

        lb.querySelector('.pm-lb-backdrop').addEventListener('click', closeLb);
        lbClose.addEventListener('click', closeLb);
        lbPrev.addEventListener('click', function () { go(current - 1); });
        lbNext.addEventListener('click', function () { go(current + 1); });

        document.addEventListener('keydown', function (e) {
            if (!lb.classList.contains('pm-lb-open')) return;
            if (e.key === 'Escape')     closeLb();
            if (e.key === 'ArrowLeft')  go(current - 1);
            if (e.key === 'ArrowRight') go(current + 1);
        });

        var touchX = 0;
        lb.addEventListener('touchstart', function (e) {
            touchX = e.changedTouches[0].screenX;
        }, { passive: true });
        lb.addEventListener('touchend', function (e) {
            var diff = touchX - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 50) go(diff > 0 ? current + 1 : current - 1);
        }, { passive: true });

        lbBuilt = true;
    }

    function openLb(index) {
        if (!lb) return;
        current = index;
        lb.classList.add('pm-lb-open');
        document.body.style.overflow = 'hidden';
        loadImage(index);
    }

    function closeLb() {
        if (!lb) return;
        lb.classList.remove('pm-lb-open');
        document.body.style.overflow = '';
        lbImg.src = '';
    }

    function go(index) {
        if (!items.length) return;
        current = (index + items.length) % items.length;
        loadImage(current);
    }

    function loadImage(index) {
        var item = items[index];
        if (!item || !lbImg) return;
        if (lbSpinner) lbSpinner.style.display = 'flex';
        lbImg.style.opacity = '0';
        lbCounter.textContent = (index + 1) + ' / ' + items.length;
        lbCaption.textContent = item.caption || '';

        var img = new Image();
        img.onload = function () {
            lbImg.src = item.src;
            lbImg.alt = item.caption || '';
            if (lbSpinner) lbSpinner.style.display = 'none';
            lbImg.style.opacity = '1';
        };
        img.onerror = function () {
            if (lbSpinner) lbSpinner.style.display = 'none';
        };
        img.src = item.src;
    }

    function makeItem(fullSrc, thumbSrc, caption, index) {
        var a = document.createElement('a');
        a.className = 'pm-lightbox-item';
        a.href = fullSrc;
        a.setAttribute('data-index', index);
        a.setAttribute('role', 'button');
        a.setAttribute('tabindex', '0');
        a.setAttribute('aria-label', caption || 'Otwórz zdjęcie');

        var img = document.createElement('img');
        img.src = thumbSrc;
        img.alt = caption;
        img.loading = 'lazy';
        a.appendChild(img);

        var ov = document.createElement('span');
        ov.className = 'pm-lightbox-item__overlay';
        ov.setAttribute('aria-hidden', 'true');
        ov.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="19" y1="19" x2="14.65" y2="14.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>';
        a.appendChild(ov);

        a.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openLb(parseInt(a.getAttribute('data-index')));
        });
        a.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openLb(parseInt(a.getAttribute('data-index')));
            }
        });

        return a;
    }

    function initWordPressGallery(done) {
        var old = document.getElementById('pm-gallery-wp');
        if (old) old.remove();

        var galleries = document.querySelectorAll(
            '.entry-content .wp-block-gallery, .entry-content .gallery'
        );

        if (!galleries.length) { if (done) done(); return; }

        galleries.forEach(function (g) { g.style.display = 'none'; });

        var data = [];
        galleries.forEach(function (gallery) {
            var figures = gallery.querySelectorAll('figure, li');
            if (figures.length) {
                figures.forEach(function (fig) {
                    var img = fig.querySelector('img');
                    if (!img) return;
                    var linkEl = fig.querySelector('a');
                    data.push({ img: img, link: linkEl });
                });
            } else {
                gallery.querySelectorAll('img').forEach(function (img) {
                    var linkEl = img.closest('a');
                    data.push({ img: img, link: linkEl });
                });
            }
        });

        if (!data.length) { if (done) done(); return; }

        function getSrc(entry) {
            var img = entry.img;

            var fullSrc = '', thumbSrc = '';
            if (img.srcset) {
                var parts = img.srcset.split(',').map(function (s) {
                    var p = s.trim().split(/\s+/);
                    return { url: p[0], w: parseInt(p[1]) || 0 };
                }).filter(function (p) {
                    return p.url && p.url.indexOf('data:') !== 0;
                });
                if (parts.length) {
                    parts.sort(function (a, b) { return b.w - a.w; });
                    fullSrc = parts[0].url;
                    parts.sort(function (a, b) { return a.w - b.w; });
                    thumbSrc = parts[0].url;
                }
            }

            if (!fullSrc && img.src && img.src.indexOf('data:') !== 0) {
                fullSrc = thumbSrc = img.src;
            }

            if (!fullSrc && img.currentSrc && img.currentSrc.indexOf('data:') !== 0) {
                fullSrc = thumbSrc = img.currentSrc;
            }

            if (!fullSrc && entry.link && entry.link.href
                && entry.link.href.indexOf('data:') !== 0
                && /\.(jpe?g|png|gif|webp)/i.test(entry.link.href)) {
                fullSrc = entry.link.href;
                if (!thumbSrc) thumbSrc = fullSrc;
            }

            if (!fullSrc && img.dataset.src && img.dataset.src.indexOf('data:') !== 0) {
                fullSrc = thumbSrc = img.dataset.src;
            }

            return { full: fullSrc, thumb: thumbSrc || fullSrc };
        }

        function hasAllSrcs() {
            return data.every(function (entry) {
                var s = getSrc(entry);
                return s.full && s.full.indexOf('data:') !== 0;
            });
        }

        function build() {
            var container = document.createElement('div');
            container.id = 'pm-gallery-wp';
            container.className = 'pm-lightbox-grid';

            data.forEach(function (entry) {
                var s = getSrc(entry);
                if (!s.full) return;
                var caption = entry.img.getAttribute('alt') || entry.img.getAttribute('title') || '';
                var idx = items.length;
                items.push({ src: s.full, caption: caption });
                var el = makeItem(s.full, s.thumb, caption, idx);
                container.appendChild(el);
            });

            if (!container.children.length) {
                galleries.forEach(function (g) { g.style.display = ''; });
                if (done) done();
                return;
            }

            galleries[0].parentNode.insertBefore(container, galleries[0]);

            if ('IntersectionObserver' in window) {
                var obs = new IntersectionObserver(function (entries) {
                    entries.forEach(function (e) {
                        if (e.isIntersecting) {
                            e.target.classList.add('pm-item-visible');
                            obs.unobserve(e.target);
                        }
                    });
                }, { rootMargin: '60px' });
                container.querySelectorAll('.pm-lightbox-item').forEach(function (el) {
                    obs.observe(el);
                });
            } else {
                container.querySelectorAll('.pm-lightbox-item').forEach(function (el) {
                    el.classList.add('pm-item-visible');
                });
            }

            if (done) done();
        }

        if (hasAllSrcs()) { build(); return; }

        var attempts = 0;
        var interval = setInterval(function () {
            attempts++;
            if (hasAllSrcs() || attempts >= 50) {
                clearInterval(interval);
                build();
            }
        }, 100);
    }

    function animateItems(container) {
        var els = (container || document).querySelectorAll('.pm-lightbox-item:not(.pm-item-visible)');
        if (!els.length) return;
        if ('IntersectionObserver' in window) {
            var obs = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
                        e.target.classList.add('pm-item-visible');
                        obs.unobserve(e.target);
                    }
                });
            }, { rootMargin: '60px' });
            els.forEach(function(el) { obs.observe(el); });
        } else {
            els.forEach(function(el) { el.classList.add('pm-item-visible'); });
        }
    }

    function initGallery() {
        var grid = document.getElementById('pm-gallery');
        if (!grid) return;

        grid.querySelectorAll('.pm-lightbox-item').forEach(function (link) {
            var fresh = link.cloneNode(true);
            link.parentNode.replaceChild(fresh, link);

            var idx = items.length;
            items.push({
                src:     fresh.getAttribute('href'),
                caption: fresh.getAttribute('data-caption') || '',
            });

            fresh.setAttribute('data-index', idx);

            if (!fresh.querySelector('.pm-lightbox-item__overlay')) {
                var ov = document.createElement('span');
                ov.className = 'pm-lightbox-item__overlay';
                ov.setAttribute('aria-hidden', 'true');
                ov.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="19" y1="19" x2="14.65" y2="14.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>';
                fresh.appendChild(ov);
            }

            fresh.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openLb(parseInt(fresh.getAttribute('data-index')));
            });
            fresh.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openLb(parseInt(fresh.getAttribute('data-index')));
                }
            });
        });

        animateItems(grid);
    }

    function initAll() {
        items = [];
        buildLightbox();

        var hasWpGallery = !!document.querySelector(
            '.entry-content .wp-block-gallery, .entry-content .gallery'
        );

        if (hasWpGallery) {
            initWordPressGallery(function () {
                initGallery();
            });
        } else {
            initGallery();
        }
    }

    document.addEventListener('DOMContentLoaded', initAll);

    window.addEventListener('pageshow', function (e) {
        if (e.persisted) initAll();
    });

})();
