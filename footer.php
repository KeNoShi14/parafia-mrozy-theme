<footer id="colophon" class="site-footer" role="contentinfo">
    <div class="container">

        <div class="footer-grid">

            <!-- Kolumna 1: Dane kontaktowe -->
            <div class="footer-widget">
                <div class="footer-logo">
                    <?php if ( has_custom_logo() ) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <img src="<?php echo get_template_directory_uri(); ?>/img/logo.png" alt="Logo parafii" height="130" width="130">
                    <?php endif; ?>
                    <div class="footer-parish-name">
                        Parafia Rzymskokatolicka<br>
                        pw. św. Teresy od Dzieciątka Jezus<br>
                        w Mrozach
                    </div>
                </div>

                <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                    <?php dynamic_sidebar( 'footer-1' ); ?>
                <?php else : ?>
                    <address>
                        <?php echo pm_get( 'parafia_address', 'ul. Św. Teresy 3, 05-320 Mrozy' ); ?><br>
                        tel. <?php echo pm_get( 'parafia_phone', '25 757 41 93' ); ?><br>
                        <a href="mailto:<?php echo pm_get( 'parafia_email', 'parafiamrozy@gmail.com' ); ?>">
                            <?php echo pm_get( 'parafia_email', 'parafiamrozy@gmail.com' ); ?>
                        </a>
                    </address>
                <?php endif; ?>
            </div>

            <!-- Kolumna 2: Nr konta + Przydatne strony -->
            <div class="footer-widget">
                <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
                    <?php dynamic_sidebar( 'footer-2' ); ?>
                <?php else : ?>
                    <h4>Nr konta parafii</h4>
                    <p class="footer-bank">
                        <?php echo pm_get( 'parafia_bank', 'Bank Spółdzielczy w Mrozach' ); ?>
                    </p>
                    <p class="footer-iban">
                        <?php echo pm_get( 'parafia_konto', '58 9227 0004 0000 0808 2000 0010' ); ?>
                    </p>
                    <p class="footer-przelew">
                        <span class="footer-przelew__label">Tytuł przelewu:</span>
                        <span class="footer-przelew__val">Darowizna na cele kultu religijnego</span>
                    </p>

                    <h4 style="margin-top:20px;">Przydatne strony</h4>
                    <ul>
                        <li><a href="https://niezbednik.niedziela.pl/" target="_blank" rel="noopener">Niezbędnik Katolika</a></li>
                        <li><a href="https://ekai.pl/" target="_blank" rel="noopener">Katolicka Agencja Informacyjna</a></li>
                        <li><a href="https://www.vaticannews.va/pl.html" target="_blank" rel="noopener">Vatican News</a></li>
                        <li><a href="https://diecezja.waw.pl/" target="_blank" rel="noopener">Diecezja Warszawsko-Praska</a></li>
                        <li><a href="https://www.idziemy.pl/" target="_blank" rel="noopener">Idziemy</a></li>
                        <li><a href="https://gosc.pl/" target="_blank" rel="noopener">Gość niedzielny</a></li>
                        <li><a href="https://malygosc.pl/" target="_blank" rel="noopener">Mały gość</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Kolumna 3: Mapa dojazdu -->
            <div class="footer-widget">
                <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
                    <?php dynamic_sidebar( 'footer-3' ); ?>
                <?php else : ?>
                    <h4>Mapa dojazdu</h4>
                    <iframe
                        class="footer-map"
                        src="<?php echo esc_url( get_theme_mod( 'parafia_map_url', 'https://maps.google.com/maps?q=Parafia+Rzk%C5%82+w%C5%9B%C4%85w+Teresa+Mrozy&output=embed' ) ); ?>"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Mapa dojazdu do parafii"
                    ></iframe>
                <?php endif; ?>
            </div>

        </div><!-- .footer-grid -->

    </div><!-- .container -->

    <div class="footer-bottom">
        <div class="container">
            <p>
                &copy; <?php echo date( 'Y' ); ?>
                <?php bloginfo( 'name' ); ?>.
                <?php esc_html_e( 'Wszelkie prawa zastrzeżone.', 'parafia-mrozy' ); ?>
            </p>
            <p class="footer-credit">Stworzone z ❤️ przez Kacper Makowski na wyższą Chwałę Bożą</p>
        </div>
    </div>

</footer>

<?php wp_footer(); ?>
</body>
</html>
