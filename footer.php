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
                        <?php echo implode( '<br>', array_map( 'esc_html', pm_wiersze( 'pm_stopka_nazwa' ) ) ); ?>
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
                        <span class="footer-przelew__val"><?php echo pm_get( 'pm_tytul_przelewu' ); ?></span>
                    </p>

                    <?php $przydatne = pm_linki( 'pm_stopka_linki' ); ?>
                    <?php if ( $przydatne ) : ?>
                    <h4 style="margin-top:20px;">Przydatne strony</h4>
                    <ul>
                        <?php foreach ( $przydatne as $link ) : ?>
                        <li><a href="<?php echo esc_url( $link[1] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $link[0] ); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
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
                        src="<?php echo esc_url( pm_opcja( 'parafia_map_url' ) ); ?>"
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
            <?php if ( '' !== pm_opcja( 'pm_stopka_podpis' ) ) : ?>
            <p class="footer-credit"><?php echo esc_html( pm_opcja( 'pm_stopka_podpis' ) ); ?></p>
            <?php endif; ?>
        </div>
    </div>

</footer>

<?php wp_footer(); ?>
</body>
</html>
