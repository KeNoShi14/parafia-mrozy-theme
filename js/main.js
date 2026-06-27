(function () {
    'use strict';

    function initSlider() {
        var slides = document.querySelectorAll('.hero-slide');
        var dots   = document.querySelectorAll('.hero-dots span');
        var prev   = document.querySelector('.hero-prev');
        var next   = document.querySelector('.hero-next');

        if (!slides.length) return;

        var current = 0;
        var timer;

        function goTo(index) {
            slides[current].classList.remove('active');
            slides[current].setAttribute('aria-hidden', 'true');
            slides[current].style.pointerEvents = 'none';
            if (dots[current]) {
                dots[current].classList.remove('active');
                dots[current].setAttribute('aria-selected', 'false');
                dots[current].setAttribute('tabindex', '-1');
            }
            current = (index + slides.length) % slides.length;
            slides[current].classList.add('active');
            slides[current].removeAttribute('aria-hidden');
            slides[current].style.pointerEvents = 'auto';
            if (dots[current]) {
                dots[current].classList.add('active');
                dots[current].setAttribute('aria-selected', 'true');
                dots[current].setAttribute('tabindex', '0');
            }
        }

        slides.forEach(function(slide, i) {
            slide.style.pointerEvents = i === 0 ? 'auto' : 'none';
        });

        function startAuto() {
            timer = setInterval(function () { goTo(current + 1); }, 5000);
        }

        function resetAuto() {
            clearInterval(timer);
            startAuto();
        }

        if (prev) prev.addEventListener('click', function () { goTo(current - 1); resetAuto(); });
        if (next) next.addEventListener('click', function () { goTo(current + 1); resetAuto(); });

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { goTo(i); resetAuto(); });
            dot.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); goTo(i); resetAuto(); }
                if (e.key === 'ArrowRight') { goTo(i + 1); resetAuto(); }
                if (e.key === 'ArrowLeft')  { goTo(i - 1); resetAuto(); }
            });
        });

        var touchStartX = 0;
        var slider = document.querySelector('.hero-slider');
        if (slider) {
            slider.addEventListener('touchstart', function (e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            slider.addEventListener('touchend', function (e) {
                var diff = touchStartX - e.changedTouches[0].screenX;
                if (Math.abs(diff) > 50) {
                    goTo(diff > 0 ? current + 1 : current - 1);
                    resetAuto();
                }
            }, { passive: true });
        }

        var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (slides.length > 1 && !prefersReduced) startAuto();
    }

    function initMobileMenu() {
        var toggle = document.querySelector('.menu-toggle');
        var nav    = document.querySelector('#primary-navigation');
        var header = document.getElementById('masthead');

        if (!toggle || !nav) return;

        function updateNavTop() {
            if (header && window.innerWidth <= 768) {
                var rect = header.getBoundingClientRect();
                var bottom = rect.bottom;
                nav.style.top = bottom + 'px';
                nav.style.maxHeight = (window.innerHeight - bottom) + 'px';
            }
        }

        toggle.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) updateNavTop();
            if (!isOpen) closeAllSubmenus();
        });

        window.addEventListener('resize', updateNavTop);
        window.addEventListener('scroll', function() {
            if (nav.classList.contains('open')) updateNavTop();
        }, { passive: true });

        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
                closeAllSubmenus();
            }
        });

        function closeAllSubmenus() {
            nav.querySelectorAll('li.mobile-open').forEach(function(li) {
                li.classList.remove('mobile-open');
                var sub = li.querySelector('ul');
                if (sub) sub.classList.remove('mobile-open');
            });
        }

        nav.querySelectorAll('li:has(> ul) > a').forEach(function(link) {
            link.addEventListener('click', function(e) {
                if (window.innerWidth > 768) return;
                e.preventDefault();
                var li = this.parentElement;
                var sub = li.querySelector('ul');
                var isOpen = li.classList.contains('mobile-open');

                closeAllSubmenus();

                if (!isOpen) {
                    li.classList.add('mobile-open');
                    if (sub) sub.classList.add('mobile-open');
                }
            });
        });

        nav.querySelectorAll('a[href="#"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
            });
        });
    }

    function initStickyHeader() {
        var header = document.getElementById('masthead');
        if (!header) return;

        function onScroll() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    function initLazyLoad() {
        if (!('IntersectionObserver' in window)) return;

        var images = document.querySelectorAll('img[loading="lazy"]');
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                    }
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '200px' });

        images.forEach(function (img) { observer.observe(img); });
    }

    function initA11yToolbar() {
        var toggleBtn  = document.getElementById('a11y-toggle');
        var closeBtn   = document.getElementById('a11y-panel-close');
        var panel      = document.getElementById('a11y-panel');
        var overlay    = document.getElementById('a11y-overlay');
        var body       = document.body;

        if (!toggleBtn || !panel) return;

        var toggleOptions = [
            ['a11y-contrast',       'a11y-high-contrast'],
            ['a11y-grayscale',      'a11y-grayscale'],
            ['a11y-negative',       'a11y-negative'],
            ['a11y-desaturate',     'a11y-desaturate'],
            ['a11y-underline',      'a11y-underline-links'],
            ['a11y-readable',       'a11y-readable-font'],
            ['a11y-dyslexia',       'a11y-dyslexia'],
            ['a11y-stop-anim',      'a11y-stop-animations'],
            ['a11y-big-cursor',     'a11y-big-cursor'],
            ['a11y-focus-highlight','a11y-focus-highlight'],
        ];

        var settings = JSON.parse(localStorage.getItem('pm-a11y') || '{}');
        var fontSize  = settings.fontSize || 100;

        applyFontSize(fontSize);
        toggleOptions.forEach(function(opt) {
            if (settings[opt[0]]) {
                body.classList.add(opt[1]);
                var btn = document.getElementById(opt[0]);
                if (btn) btn.setAttribute('aria-pressed', 'true');
            }
        });

        function openPanel() {
            panel.classList.add('is-open');
            overlay.classList.add('is-open');
            panel.removeAttribute('aria-hidden');
            toggleBtn.setAttribute('aria-expanded', 'true');
            closeBtn.focus();
        }

        function closePanel() {
            panel.classList.remove('is-open');
            overlay.classList.remove('is-open');
            panel.setAttribute('aria-hidden', 'true');
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.focus();
        }

        function saveSettings() {
            var data = { fontSize: fontSize };
            toggleOptions.forEach(function(opt) {
                data[opt[0]] = body.classList.contains(opt[1]);
            });
            localStorage.setItem('pm-a11y', JSON.stringify(data));
        }

        function applyFontSize(size) {
            document.documentElement.style.fontSize = size + '%';
        }

        toggleBtn.addEventListener('click', openPanel);
        closeBtn.addEventListener('click', closePanel);
        overlay.addEventListener('click', closePanel);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && panel.classList.contains('is-open')) closePanel();
        });

        document.getElementById('a11y-font-increase').addEventListener('click', function() {
            if (fontSize < 150) { fontSize += 10; applyFontSize(fontSize); saveSettings(); }
        });
        document.getElementById('a11y-font-decrease').addEventListener('click', function() {
            if (fontSize > 80) { fontSize -= 10; applyFontSize(fontSize); saveSettings(); }
        });
        document.getElementById('a11y-font-reset').addEventListener('click', function() {
            fontSize = 100; applyFontSize(fontSize); saveSettings();
        });

        toggleOptions.forEach(function(opt) {
            var btn = document.getElementById(opt[0]);
            if (!btn) return;
            btn.addEventListener('click', function() {
                var active = body.classList.toggle(opt[1]);
                this.setAttribute('aria-pressed', active ? 'true' : 'false');
                saveSettings();
            });
        });

        document.getElementById('a11y-reset-all').addEventListener('click', function() {
            fontSize = 100;
            applyFontSize(fontSize);
            toggleOptions.forEach(function(opt) {
                body.classList.remove(opt[1]);
                var btn = document.getElementById(opt[0]);
                if (btn) btn.setAttribute('aria-pressed', 'false');
            });
            localStorage.removeItem('pm-a11y');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSlider();
        initMobileMenu();
        initStickyHeader();
        initSmoothScroll();
        initLazyLoad();
        initThemeToggle();
        initA11yToolbar();
    });

})();

    function initThemeToggle() {
        var toggle = document.getElementById('theme-toggle');
        var html   = document.documentElement;

        if (!toggle) return;

        var saved = localStorage.getItem('pm-theme');
        if (saved) {
            html.setAttribute('data-theme', saved);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            html.setAttribute('data-theme', 'dark');
        }

        toggle.addEventListener('click', function () {
            var current = html.getAttribute('data-theme');
            var next    = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('pm-theme', next);
        });

        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                if (!localStorage.getItem('pm-theme')) {
                    html.setAttribute('data-theme', e.matches ? 'dark' : 'light');
                }
            });
        }
    }
