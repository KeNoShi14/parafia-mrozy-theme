<?php
get_header();

$today  = new DateTime( 'today', new DateTimeZone('Europe/Warsaw') );
$start  = clone $today;
$start->modify( '-2 days' );

$dni_tygodnia = ['Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota','Niedziela'];
$days = [];
for ( $i = 0; $i < 10; $i++ ) {
    $d = clone $start;
    $d->modify( "+{$i} days" );
    $dow    = (int) $d->format('N') - 1;
    $days[] = [
        'date'  => $d->format('Y-m-d'),
        'label' => $dni_tygodnia[$dow] . ', ' . $d->format('j.n.Y'),
        'short' => $dni_tygodnia[$dow],
        'today' => $d->format('Y-m-d') === $today->format('Y-m-d'),
    ];
}
?>

<main id="primary" class="site-main" role="main">

    <!-- ── HERO ── -->
    <div class="czyt-hero">
        <div class="czyt-hero-overlay"></div>
        <div class="czyt-hero-content container">
            <p class="czyt-hero-eyebrow">Parafia pw. Św. Teresy od Dzieciątka Jezus w Mrozach</p>
            <h1 class="czyt-hero-title">Czytania liturgiczne</h1>
            <p class="czyt-hero-sub">
                <?php
                    $end = clone $today;
                    $end->modify('+7 days');
                    echo $start->format('j.n') . '–' . $end->format('j.n.Y');
                ?>
            </p>
        </div>
    </div>

    <div class="container">
    <div class="czyt-wrap">

        <!-- ── NAWIGACJA PO DNIACH ── -->
        <nav class="czyt-nav" aria-label="Nawigacja po dniach tygodnia">
            <?php foreach ( $days as $i => $d ) : ?>
            <button
                class="czyt-nav-btn<?php echo $d['today'] ? ' czyt-nav-btn--active' : ''; ?>"
                data-target="day-<?php echo $i; ?>"
                aria-pressed="<?php echo $d['today'] ? 'true' : 'false'; ?>"
            >
                <span class="czyt-nav-short"><?php echo mb_substr($d['short'], 0, 3); ?></span>
                <span class="czyt-nav-full"><?php echo $d['short']; ?></span>
            </button>
            <?php endforeach; ?>
        </nav>

        <!-- ── PANELE Z CZYTANIAMI ── -->
        <div class="czyt-panels">
        <?php foreach ( $days as $i => $d ) : ?>
        <div
            class="czyt-panel<?php echo $d['today'] ? ' czyt-panel--active' : ''; ?>"
            id="day-<?php echo $i; ?>"
            aria-hidden="<?php echo $d['today'] ? 'false' : 'true'; ?>"
        >
            <div class="czyt-panel-header">
                <h2 class="czyt-panel-title"><?php echo esc_html($d['label']); ?></h2>
                <?php if ($d['today']) : ?>
                <span class="czyt-badge">Dziś</span>
                <?php endif; ?>
            </div>

            <div class="czyt-widget-wrap" id="widget-<?php echo $i; ?>">
                <div class="czyt-loading" aria-live="polite">
                    <span class="czyt-spinner" aria-hidden="true"></span>
                    Ładowanie czytań…
                </div>
            </div>

        </div>
        <?php endforeach; ?>
        </div>

        <!-- ── STOPKA ── -->
        <p class="czyt-source">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Źródło czytań: <a href="https://www.niedziela.pl" target="_blank" rel="noopener">niedziela.pl</a>
        </p>

    </div><!-- .czyt-wrap -->
    </div><!-- .container -->

</main>

<style>
.czyt-hero {
    position: relative;
    min-height: 220px;
    display: flex;
    align-items: flex-end;
    background: linear-gradient(135deg, #1a1a2e 0%, #2d1f4e 60%, #1a1a2e 100%);
    overflow: hidden;
    margin-bottom: 0;
}
.czyt-hero-overlay {
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c9a84c' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.czyt-hero-content {
    position: relative;
    z-index: 1;
    padding-top: 60px;
    padding-bottom: 32px;
}
.czyt-hero-eyebrow {
    font-size: 12px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(201,168,76,0.7);
    margin: 0 0 8px;
}
.czyt-hero-title {
    font-family: var(--font-main);
    font-size: clamp(1.8rem, 5vw, 2.6rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 8px;
    line-height: 1.15;
}
.czyt-hero-sub {
    font-size: 0.95rem;
    color: var(--color-gold-light, #e8c96d);
    margin: 0;
}

.czyt-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 0 0 64px;
}

.czyt-nav {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 4px;
    margin: 28px 0 0;
    background: rgba(201,168,76,0.07);
    border: 1px solid rgba(201,168,76,0.2);
    border-radius: 10px;
    padding: 6px;
}
.czyt-nav-btn {
    padding: 10px 4px;
    font-family: var(--font-main);
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--color-gold-dark, #8a6a1f);
    background: transparent;
    border: 1px solid transparent;
    border-radius: 7px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    line-height: 1.3;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}
.czyt-nav-btn:hover {
    background: rgba(201,168,76,0.12);
    border-color: rgba(201,168,76,0.3);
}
.czyt-nav-btn--active {
    background: var(--color-gold, #c9a84c) !important;
    border-color: var(--color-gold, #c9a84c) !important;
    color: #fff !important;
}
.czyt-nav-short { display: none; font-size: 0.75rem; }
.czyt-nav-full  { display: block; }

@media (max-width: 700px) {
    .czyt-nav { grid-template-columns: repeat(5, 1fr); gap: 3px; padding: 5px; }
    .czyt-nav-btn { padding: 8px 2px; font-size: 0.68rem; }
    .czyt-nav-short { display: block; }
    .czyt-nav-full  { display: none; }
}
@media (max-width: 400px) {
    .czyt-nav { grid-template-columns: repeat(5, 1fr); }
}

.czyt-panels { margin-top: 20px; }

.czyt-panel {
    display: none;
}
.czyt-panel--active {
    display: block;
    animation: czytFadeIn 0.25s ease;
}
@keyframes czytFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.czyt-panel-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid rgba(201,168,76,0.25);
}
.czyt-panel-title {
    font-family: var(--font-main);
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--color-gold-dark, #8a6a1f);
    margin: 0;
}
.czyt-badge {
    background: var(--color-gold, #c9a84c);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
}

.czyt-loading {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 40px 20px;
    color: var(--color-text-light, #888);
    font-size: 0.9rem;
    font-style: italic;
}
.czyt-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(201,168,76,0.25);
    border-top-color: var(--color-gold, #c9a84c);
    border-radius: 50%;
    animation: czytSpin 0.8s linear infinite;
    flex-shrink: 0;
}
@keyframes czytSpin { to { transform: rotate(360deg); } }

.czyt-info-dnia {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.82rem;
    color: var(--color-text-light, #888);
    letter-spacing: 0.05em;
    margin: 0 0 24px;
    text-transform: uppercase;
}

.czyt-msza + .czyt-msza { margin-top: 48px; border-top: 1px solid rgba(201,168,76,0.2); padding-top: 40px; }

.czyt-msza-tytul {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #fff;
    background: var(--color-gold, #c9a84c);
    padding: 8px 16px;
    border-radius: 5px;
    margin: 0 0 28px;
    display: inline-block;
}

.czyt-msza-tabs {
    display: flex;
    gap: 6px;
    margin: 0 0 24px;
    border-bottom: 2px solid rgba(201,168,76,0.2);
    padding-bottom: 0;
    flex-wrap: wrap;
}
.czyt-msza-tab {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--color-gold-dark, #8a6a1f);
    background: rgba(201,168,76,0.07);
    border: 1px solid rgba(201,168,76,0.25);
    border-bottom: none;
    border-radius: 6px 6px 0 0;
    padding: 9px 18px 11px;
    cursor: pointer;
    transition: background 0.18s, color 0.18s;
    position: relative;
    bottom: -2px;
}
.czyt-msza-tab:hover {
    background: rgba(201,168,76,0.15);
}
.czyt-msza-tab--active {
    background: var(--color-gold, #c9a84c);
    border-color: var(--color-gold, #c9a84c);
    color: #fff;
    z-index: 1;
}
.czyt-msza-panel {
    animation: czytFadeIn 0.2s ease;
}
@media (max-width: 500px) {
    .czyt-msza-tab { font-size: 0.75rem; padding: 8px 12px 10px; }
}

.czyt-blok {
    margin-bottom: 36px;
}
.czyt-blok:last-child { margin-bottom: 0; }

.czyt-blok-header {
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid rgba(201,168,76,0.2);
}
.czyt-blok--ewangelia .czyt-blok-header { border-bottom-color: rgba(26,26,46,0.15); }
.czyt-blok--psalm .czyt-blok-header     { border-bottom-color: rgba(201,168,76,0.3); }

.czyt-blok-nazwa {
    font-family: 'Montserrat', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    color: var(--color-gold-dark, #8a6a1f);
    margin: 0 0 4px;
    line-height: 1.3;
}
.czyt-blok--ewangelia .czyt-blok-nazwa { color: #1a1a2e; }
[data-theme="dark"] .czyt-blok--ewangelia .czyt-blok-nazwa { color: var(--color-gold-light, #e8c96d); }

.czyt-blok-sigla-inline {
    font-weight: 400;
    font-size: 0.9em;
    color: var(--color-text-light, #888);
}

.czyt-blok-podtytul {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.9rem;
    font-style: italic;
    color: var(--color-text-light, #666);
    margin: 0;
}
[data-theme="dark"] .czyt-blok-podtytul { color: var(--text-secondary, #aaa); }

.czyt-blok-body { padding-left: 4px; }

.czyt-perykopa-wstep {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.9rem;
    color: var(--color-dark, #1a1a1a);
    margin: 0 0 14px;
    line-height: 1.6;
}
[data-theme="dark"] .czyt-perykopa-wstep { color: var(--text-primary, #f0f0f0); }

.czyt-akapit {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.95rem;
    line-height: 1.9;
    color: var(--color-dark, #1a1a1a);
    margin: 0 0 12px;
}
[data-theme="dark"] .czyt-akapit { color: var(--text-primary, #f0f0f0); }

.czyt-akapit--aklamacja {
    font-size: 0.95rem;
    text-align: center;
    font-style: italic;
    color: var(--color-dark, #1a1a1a);
}

.czyt-werset {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.92rem;
    line-height: 1.85;
    color: var(--color-dark, #1a1a1a);
    margin: 0 0 10px;
    padding-left: 6px;
    border-left: 2px solid rgba(201,168,76,0.15);
}
[data-theme="dark"] .czyt-werset { color: var(--text-primary, #f0f0f0); }

.czyt-refren-label {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.88rem;
    color: var(--color-gold-dark, #8a6a1f);
    margin: 0 0 14px;
    padding: 8px 14px;
    background: rgba(201,168,76,0.05);
    border-radius: 4px;
}

.czyt-refren {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.9rem;
    color: var(--color-gold-dark, #8a6a1f);
    background: rgba(201,168,76,0.08);
    padding: 8px 14px;
    border-radius: 4px;
    margin: 0 0 16px;
    line-height: 1.5;
}
.czyt-refren--aklamacja {
    text-align: center;
    margin-top: 6px;
    background: rgba(201,168,76,0.1);
}

.czyt-dodatki {
    margin-top: 40px;
    background: rgba(201,168,76,0.05);
    border: 1px solid rgba(201,168,76,0.2);
    border-radius: 10px;
    padding: 20px 22px;
}
.czyt-dodatki-tytul {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--color-gold-dark, #8a6a1f);
    margin: 0 0 14px;
}
.czyt-dodatek { margin-bottom: 10px; }
.czyt-dodatek-stytul {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--color-gold-dark, #8a6a1f);
    margin: 0 0 3px;
}
.czyt-dodatek-tresc {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.85rem;
    color: var(--color-text-light, #666);
    margin: 0;
    line-height: 1.6;
}
[data-theme="dark"] .czyt-dodatek-tresc { color: var(--text-secondary, #aaa); }

.czyt-zakonczenie {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.88rem;
    font-style: italic;
    color: var(--color-text-light, #777);
    margin: 12px 0 0;
    padding-top: 10px;
    border-top: 1px solid rgba(201,168,76,0.15);
}

.czyt-error {
    padding: 32px 20px;
    text-align: center;
    color: var(--color-text-light, #888);
    font-style: italic;
    font-size: 0.9rem;
    border: 1px dashed rgba(201,168,76,0.25);
    border-radius: 8px;
}

.czyt-source {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--color-text-light, #999);
    margin-top: 28px;
}
.czyt-source a {
    color: var(--color-gold-dark, #8a6a1f);
    text-decoration: none;
    border-bottom: 1px solid rgba(201,168,76,0.3);
}
.czyt-source a:hover { border-color: var(--color-gold); }
</style>

<script>
(function () {
    var days    = <?php echo json_encode( array_values( array_map(function($d){ return $d['date']; }, $days) ) ); ?>;
    var proxyUrl = '<?php echo esc_js( get_template_directory_uri() . '/liturgia-proxy.php' ); ?>';

    var loaded  = {};
    var panels  = document.querySelectorAll('.czyt-panel');
    var navBtns = document.querySelectorAll('.czyt-nav-btn');

    var IKONY = {
        czytanie:  '📖',
        psalm:     '🎵',
        aklamacja: '✝',
        ewangelia: '✠',
        sekwencja: '🕊',
    };

    navBtns.forEach(function (btn, i) {
        btn.addEventListener('click', function () {
            navBtns.forEach(function (b) { b.classList.remove('czyt-nav-btn--active'); b.setAttribute('aria-pressed','false'); });
            panels.forEach(function (p)  { p.classList.remove('czyt-panel--active');    p.setAttribute('aria-hidden','true'); });
            btn.classList.add('czyt-nav-btn--active');
            btn.setAttribute('aria-pressed','true');
            var t = document.getElementById(btn.dataset.target);
            t.classList.add('czyt-panel--active');
            t.setAttribute('aria-hidden','false');
            loadDay(i);
        });
    });

    function loadDay(idx) {
        if (loaded[idx]) return;
        loaded[idx] = true;

        var wrap    = document.getElementById('widget-' + idx);
        var loading = wrap ? wrap.querySelector('.czyt-loading') : null;

        fetch(proxyUrl + '?date=' + days[idx])
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (loading) loading.remove();
                if (data.error) {
                    wrap.innerHTML = '<p class="czyt-error">⚠ ' + data.error + '</p>';
                    return;
                }
                wrap.innerHTML = renderLiturgia(data);
            })
            .catch(function(){
                if (loading) loading.remove();
                wrap.innerHTML = '<p class="czyt-error">⚠ Nie udało się załadować czytań. Spróbuj odświeżyć stronę.</p>';
            });
    }

    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function nl2br(s) {
        return esc(s).replace(/\n/g, '<br>');
    }

    function renderMsza(msza) {
        var html = '';
        msza.czytania.forEach(function(czyt) {
            var typ = czyt.typ || 'czytanie';
            html += '<div class="czyt-blok czyt-blok--' + typ + '">';

            html += '<div class="czyt-blok-header">';
            var naglowek = esc(czyt.nazwa);
            if (czyt.sigla) naglowek += ' <span class="czyt-blok-sigla-inline">(' + esc(czyt.sigla) + ')</span>';
            html += '<p class="czyt-blok-nazwa">' + naglowek + '</p>';
            if (czyt.tytul) html += '<p class="czyt-blok-podtytul">' + esc(czyt.tytul) + '</p>';
            html += '</div>';

            html += '<div class="czyt-blok-body">';

            if (typ === 'psalm') {
                if (czyt.refren) html += '<p class="czyt-refren-label">Refren: <strong>' + esc(czyt.refren) + '</strong></p>';
                (czyt.strofy || []).forEach(function(strofa) {
                    html += '<p class="czyt-werset">' + nl2br(strofa) + '</p>';
                    if (czyt.refren) html += '<p class="czyt-refren"><strong>' + esc(czyt.refren) + '</strong></p>';
                });
            } else if (typ === 'aklamacja') {
                if (czyt.refren) html += '<p class="czyt-refren czyt-refren--aklamacja"><strong>' + esc(czyt.refren) + '</strong></p>';
                czyt.tresc.forEach(function(t) {
                    html += '<p class="czyt-akapit czyt-akapit--aklamacja">' + nl2br(t) + '</p>';
                });
            } else {
                if (czyt.wstep)       html += '<p class="czyt-perykopa-wstep"><strong>' + esc(czyt.wstep) + '</strong></p>';
                czyt.tresc.forEach(function(t) {
                    html += '<p class="czyt-akapit">' + nl2br(t) + '</p>';
                });
                if (czyt.zakonczenie) html += '<p class="czyt-zakonczenie">' + esc(czyt.zakonczenie) + '</p>';
            }

            html += '</div></div>';
        });
        return html;
    }

    function renderLiturgia(data) {
        var html = '';

        if (data.rok) {
            html += '<p class="czyt-info-dnia">' + esc(data.rok) + '</p>';
        }

        var msze = (data.msze || []).filter(function(m) { return m.czytania && m.czytania.length > 0; });

        if (msze.length > 1) {
            var uid = 'mtab-' + Math.random().toString(36).slice(2, 7);

            html += '<div class="czyt-msza-tabs" role="tablist" aria-label="Msze">';
            msze.forEach(function(msza, i) {
                var label = msza.tytul || ('Msza ' + (i + 1));
                html += '<button'
                    + ' class="czyt-msza-tab' + (i === 0 ? ' czyt-msza-tab--active' : '') + '"'
                    + ' role="tab"'
                    + ' aria-selected="' + (i === 0 ? 'true' : 'false') + '"'
                    + ' aria-controls="' + uid + '-panel-' + i + '"'
                    + ' id="' + uid + '-tab-' + i + '"'
                    + ' data-uid="' + uid + '"'
                    + ' data-idx="' + i + '"'
                    + '>' + esc(label) + '</button>';
            });
            html += '</div>';

            msze.forEach(function(msza, i) {
                html += '<div'
                    + ' class="czyt-msza-panel' + (i === 0 ? ' czyt-msza-panel--active' : '') + '"'
                    + ' role="tabpanel"'
                    + ' id="' + uid + '-panel-' + i + '"'
                    + ' aria-labelledby="' + uid + '-tab-' + i + '"'
                    + (i === 0 ? '' : ' hidden')
                    + '>';
                html += renderMsza(msza);
                html += '</div>';
            });

        } else {
            if (msze.length && msze[0].tytul) {
                html += '<p class="czyt-msza-tytul">' + esc(msze[0].tytul) + '</p>';
            }
            if (msze.length) {
                html += '<div class="czyt-msza">' + renderMsza(msze[0]) + '</div>';
            }
        }

        if (data.dodatki && data.dodatki.length) {
            html += '<div class="czyt-dodatki"><p class="czyt-dodatki-tytul">Rozważania i materiały</p>';
            data.dodatki.forEach(function(d) {
                html += '<div class="czyt-dodatek">';
                if (d.stytul) html += '<p class="czyt-dodatek-stytul">' + esc(d.stytul) + '</p>';
                if (d.tresc)  html += '<p class="czyt-dodatek-tresc">'  + esc(d.tresc)  + '</p>';
                html += '</div>';
            });
            html += '</div>';
        }

        return html;
    }

    document.addEventListener('click', function(e) {
        var tab = e.target.closest('.czyt-msza-tab');
        if (!tab) return;
        var uid   = tab.dataset.uid;
        var idx   = parseInt(tab.dataset.idx, 10);
        var wrap  = tab.closest('.czyt-widget-wrap');
        if (!wrap) return;

        wrap.querySelectorAll('.czyt-msza-tab').forEach(function(t) {
            t.classList.remove('czyt-msza-tab--active');
            t.setAttribute('aria-selected', 'false');
        });
        wrap.querySelectorAll('.czyt-msza-panel').forEach(function(p) {
            p.classList.remove('czyt-msza-panel--active');
            p.hidden = true;
        });

        tab.classList.add('czyt-msza-tab--active');
        tab.setAttribute('aria-selected', 'true');
        var panel = wrap.querySelector('#' + uid + '-panel-' + idx);
        if (panel) { panel.classList.add('czyt-msza-panel--active'); panel.hidden = false; }
    });

    var activeIdx = 0;
    navBtns.forEach(function(btn, i) { if (btn.classList.contains('czyt-nav-btn--active')) activeIdx = i; });
    loadDay(activeIdx);

})();
</script>

<?php get_footer(); ?>
