<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <script>
        (function(){
            var t = localStorage.getItem('pm-theme');
            if (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) t = 'dark';
            if (t) document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#primary"><?php esc_html_e( 'Przejdź do treści', 'parafia-mrozy' ); ?></a>

<header id="masthead" class="site-header" role="banner">
    <div class="header-inner">

        <div class="site-logo">
            <?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <img src="<?php echo get_template_directory_uri(); ?>/img/logo.png" alt="<?php bloginfo( 'name' ); ?>" height="80" width="80">
                </a>
            <?php endif; ?>
            <div class="site-name">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:inherit;">
                    <?php bloginfo( 'name' ); ?>
                </a>
            </div>
        </div>

        <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'parafia-mrozy' ); ?>">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav id="primary-navigation" aria-label="<?php esc_attr_e( 'Menu główne', 'parafia-mrozy' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'menu_id'        => 'primary-menu',
                'container'      => false,
                'fallback_cb'    => 'parafia_mrozy_fallback_menu',
            ] );
            ?>
        </nav>

        <!-- Dark/Light mode toggle -->
        <button class="theme-toggle" id="theme-toggle" aria-label="Przełącz motyw ciemny/jasny">
            <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
        </button>

        <!-- Przycisk dostępności -->
        <button class="a11y-toggle" id="a11y-toggle" aria-label="Opcje dostępności" aria-expanded="false" aria-controls="a11y-panel">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <circle cx="12" cy="8" r="1" fill="currentColor" stroke="none"/>
                <line x1="12" y1="11" x2="12" y2="17"/>
                <line x1="9" y1="13" x2="15" y2="13"/>
            </svg>
        </button>

    </div>
</header>

<!-- Panel dostępności -->
<div id="a11y-panel" class="a11y-panel" role="dialog" aria-label="Opcje dostępności" aria-hidden="true">
    <div class="a11y-panel-inner">
        <div class="a11y-panel-header">
            <h2 class="a11y-panel-title">Dostępność</h2>
            <button class="a11y-panel-close" id="a11y-panel-close" aria-label="Zamknij panel dostępności">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="a11y-section">
            <p class="a11y-section-label">Rozmiar tekstu</p>
            <div class="a11y-btn-group">
                <button class="a11y-btn" id="a11y-font-decrease" aria-label="Zmniejsz tekst">A<span aria-hidden="true">-</span></button>
                <button class="a11y-btn a11y-btn-reset" id="a11y-font-reset" aria-label="Domyślny rozmiar tekstu">A</button>
                <button class="a11y-btn a11y-btn-large" id="a11y-font-increase" aria-label="Zwiększ tekst">A<span aria-hidden="true">+</span></button>
            </div>
        </div>

        <div class="a11y-section">
            <p class="a11y-section-label">Wygląd</p>
            <button class="a11y-toggle-btn" id="a11y-contrast" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true">◑</span>
                Wysoki kontrast
            </button>
            <button class="a11y-toggle-btn" id="a11y-grayscale" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true">◐</span>
                Skala szarości
            </button>
            <button class="a11y-toggle-btn" id="a11y-negative" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true">◍</span>
                Negatyw kolorów
            </button>
            <button class="a11y-toggle-btn" id="a11y-desaturate" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true">◎</span>
                Zmniejsz nasycenie
            </button>
        </div>

        <div class="a11y-section">
            <p class="a11y-section-label">Tekst i czytanie</p>
            <button class="a11y-toggle-btn" id="a11y-underline" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true"><u>A</u></span>
                Podkreślenie linków
            </button>
            <button class="a11y-toggle-btn" id="a11y-readable" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true">Aa</span>
                Czytelna czcionka
            </button>
            <button class="a11y-toggle-btn" id="a11y-dyslexia" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true">Dy</span>
                Tryb dysleksji
            </button>
        </div>

        <div class="a11y-section">
            <p class="a11y-section-label">Nawigacja</p>
            <button class="a11y-toggle-btn" id="a11y-stop-anim" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true">⏸</span>
                Zatrzymaj animacje
            </button>
            <button class="a11y-toggle-btn" id="a11y-big-cursor" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true">↖</span>
                Duży kursor
            </button>
            <button class="a11y-toggle-btn" id="a11y-focus-highlight" aria-pressed="false">
                <span class="a11y-toggle-icon" aria-hidden="true">⬡</span>
                Widoczny focus
            </button>
        </div>

        <div class="a11y-section">
            <p class="a11y-section-label">Skróty klawiaturowe</p>
            <div class="a11y-shortcuts">
                <div class="a11y-shortcut"><kbd>Tab</kbd> Następny element</div>
                <div class="a11y-shortcut"><kbd>Shift+Tab</kbd> Poprzedni element</div>
                <div class="a11y-shortcut"><kbd>Enter</kbd> Aktywuj link/przycisk</div>
                <div class="a11y-shortcut"><kbd>Esc</kbd> Zamknij panel</div>
            </div>
        </div>

        <div class="a11y-section">
            <button class="a11y-reset-all" id="a11y-reset-all">
                Resetuj wszystkie ustawienia
            </button>
        </div>
    </div>
</div>
<div class="a11y-overlay" id="a11y-overlay" aria-hidden="true"></div>

<?php
function parafia_mrozy_fallback_menu() {
    echo '<ul id="primary-menu">';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Strona główna</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/aktualnosci/' ) ) . '">Aktualności</a></li>';
    echo '<li><a href="#" aria-haspopup="true">O parafii</a>
        <ul>
            <li><a href="' . esc_url( home_url( '/o-parafii/historia-parafii/' ) ) . '">Historia parafii</a></li>
            <li><a href="' . esc_url( home_url( '/o-parafii/ksieza/' ) ) . '">Księża</a></li>
            <li><a href="' . esc_url( home_url( '/o-parafii/rady-parafialne/' ) ) . '">Rady parafialne</a></li>
            <li><a href="https://mrozy.grobonet.com/" target="_blank" rel="noopener">Cmentarz</a></li>
            <li><a href="https://www.karmel.pl/swieta-teresa-od-dzieciatka-jezus-teresa-martin-1873-1897/" target="_blank" rel="noopener">Patronka</a></li>
        </ul>
    </li>';
    echo '<li><a href="#" aria-haspopup="true">Duszpasterstwo</a>
        <ul>
            <li><a href="' . esc_url( home_url( '/duszpasterstwo/msze-swiete/' ) ) . '">Msze Święte</a></li>
            <li><a href="' . esc_url( home_url( '/duszpasterstwo/sakramenty/' ) ) . '">Sakramenty</a></li>
            <li><a href="' . esc_url( home_url( '/kontakt/' ) ) . '">Kancelaria</a></li>
        </ul>
    </li>';
    echo '<li><a href="#" aria-haspopup="true">Grupy</a>
        <ul>
            <li><a href="' . esc_url( home_url( '/grupy/grupa-koronkowa/' ) ) . '">Grupa koronkowa</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/matki-w-modlitwie/' ) ) . '">Grupa „Matki w Modlitwie"</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/kola-zywego-rozanca/' ) ) . '">Koła Żywego Różańca</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/liturgiczna-sluzba-oltarza/' ) ) . '">Liturgiczna Służba Ołtarza</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/margaretki/' ) ) . '">Margaretki</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/orkiestra-parafialna/' ) ) . '">Orkiestra parafialna</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/ruch-szensztacki/' ) ) . '">Ruch Szensztacki</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/rycerki-niepokalanej/' ) ) . '">Rycerki Niepokalanej</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/schola-parafialna/' ) ) . '">Schola parafialna</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/sursum-corda/' ) ) . '">Grupa uwielbieniowa „Sursum Corda"</a></li>
            <li><a href="' . esc_url( home_url( '/grupy/towarzystwo-przyjaciol-seminarium/' ) ) . '">Towarzystwo Przyjaciół Seminarium</a></li>
        </ul>
    </li>';
    echo '<li><a href="' . esc_url( home_url( '/wsparcie/' ) ) . '">Wsparcie</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/kontakt/' ) ) . '">Kontakt</a></li>';
    echo '</ul>';
}
?>
