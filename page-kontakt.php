<?php
get_header();

$form_sent    = false;
$form_error   = false;
$form_message = '';

if ( isset( $_POST['pm_contact_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pm_contact_nonce'] ) ), 'pm_contact_form' ) ) {

    $name    = sanitize_text_field( wp_unslash( $_POST['pm_name']    ?? '' ) );
    $email   = sanitize_email(      wp_unslash( $_POST['pm_email']   ?? '' ) );
    $subject = sanitize_text_field( wp_unslash( $_POST['pm_subject'] ?? '' ) );
    $message = sanitize_textarea_field( wp_unslash( $_POST['pm_message'] ?? '' ) );

    if ( empty( $name ) || empty( $email ) || empty( $subject ) || empty( $message ) ) {
        $form_error   = true;
        $form_message = 'Proszę wypełnić wszystkie pola formularza.';
    } elseif ( ! is_email( $email ) ) {
        $form_error   = true;
        $form_message = 'Podany adres e-mail jest nieprawidłowy.';
    } else {
        $to      = pm_get( 'parafia_email', 'parafiamrozy@gmail.com' );
        $subject_mail = '[Kontakt] ' . $subject;
        $body    = "Wiadomość ze strony parafii\n\n";
        $body   .= "Imię i nazwisko: {$name}\n";
        $body   .= "E-mail: {$email}\n";
        $body   .= "Temat: {$subject}\n\n";
        $body   .= "Treść:\n{$message}";
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: Strona Parafii <' . $to . '>',
            'Reply-To: ' . $name . ' <' . $email . '>',
        ];

        if ( wp_mail( $to, $subject_mail, $body, $headers ) ) {
            $form_sent    = true;
            $form_message = 'Dziękujemy! Wiadomość została wysłana. Odpiszemy najszybciej jak to możliwe.';
        } else {
            $form_error   = true;
            $form_message = 'Przepraszamy, wystąpił błąd podczas wysyłania wiadomości. Prosimy spróbować ponownie lub skontaktować się telefonicznie.';
        }
    }
}
?>

<main id="primary" class="site-main" role="main">

    <div class="contact-hero">
        <div class="container">
            <h1 class="contact-hero-title">Kontakt</h1>
            <p class="contact-hero-subtitle">Skontaktuj się z nami — jesteśmy do Twojej dyspozycji</p>
        </div>
    </div>

    <div class="contact-page">
        <div class="container">
            <div class="contact-grid">

                <div class="contact-info">

                    <div class="contact-card">
                        <div class="contact-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div class="contact-card-body">
                            <h3>Adres</h3>
                            <p>
                                Parafia pw. św. Teresy od Dzieciątka Jezus<br>
                                <?php echo esc_html( pm_get( 'parafia_address', 'ul. Św. Teresy 3, 05-320 Mrozy' ) ); ?>
                            </p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.82a16 16 0 0 0 6.29 6.29l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div class="contact-card-body">
                            <h3>Telefon</h3>
                            <p>
                                <a href="tel:<?php echo esc_attr( preg_replace( '/\s/', '', pm_get( 'parafia_phone', '257574193' ) ) ); ?>">
                                    <?php echo esc_html( pm_get( 'parafia_phone', '25 757 41 93' ) ); ?>
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <div class="contact-card-body">
                            <h3>E-mail</h3>
                            <p>
                                <a href="mailto:<?php echo esc_attr( pm_get( 'parafia_email', 'parafiamrozy@gmail.com' ) ); ?>">
                                    <?php echo esc_html( pm_get( 'parafia_email', 'parafiamrozy@gmail.com' ) ); ?>
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <div class="contact-card-body">
                            <h3>Kancelaria parafialna</h3>
                            <p>
                                Wtorek: 16:30 – 17:30<br>
                                Środa: 16:30 – 17:30<br>
                                Czwartek: 8:45 – 9:30<br>
                                Piątek: 16:30 – 17:30<br>
                                Sobota: 8:45 – 9:30
                            </p>
                            <p class="contact-note">W Pierwszy Piątek Miesiąca kancelaria nieczynna</p>
                        </div>
                    </div>

                </div>

                <div class="contact-form-wrap">
                    <h2 class="contact-form-title">Napisz do nas</h2>

                    <?php if ( $form_sent ) : ?>
                        <div class="contact-alert contact-alert-success" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <?php echo esc_html( $form_message ); ?>
                        </div>
                    <?php elseif ( $form_error ) : ?>
                        <div class="contact-alert contact-alert-error" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <?php echo esc_html( $form_message ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! $form_sent ) : ?>
                    <form class="contact-form" method="post" action="#contact-form" id="contact-form" novalidate>
                        <?php wp_nonce_field( 'pm_contact_form', 'pm_contact_nonce' ); ?>

                        <div class="contact-form-row">
                            <div class="contact-field">
                                <label for="pm_name">Imię i nazwisko <span aria-hidden="true">*</span></label>
                                <input
                                    type="text"
                                    id="pm_name"
                                    name="pm_name"
                                    value="<?php echo esc_attr( $_POST['pm_name'] ?? '' ); ?>"
                                    required
                                    autocomplete="name"
                                    aria-required="true"
                                    placeholder="Jan Kowalski"
                                >
                            </div>
                            <div class="contact-field">
                                <label for="pm_email">Adres e-mail <span aria-hidden="true">*</span></label>
                                <input
                                    type="email"
                                    id="pm_email"
                                    name="pm_email"
                                    value="<?php echo esc_attr( $_POST['pm_email'] ?? '' ); ?>"
                                    required
                                    autocomplete="email"
                                    aria-required="true"
                                    placeholder="jan@example.com"
                                >
                            </div>
                        </div>

                        <div class="contact-field">
                            <label for="pm_subject">Temat <span aria-hidden="true">*</span></label>
                            <input
                                type="text"
                                id="pm_subject"
                                name="pm_subject"
                                value="<?php echo esc_attr( $_POST['pm_subject'] ?? '' ); ?>"
                                required
                                aria-required="true"
                                placeholder="Temat wiadomości"
                            >
                        </div>

                        <div class="contact-field">
                            <label for="pm_message">Wiadomość <span aria-hidden="true">*</span></label>
                            <textarea
                                id="pm_message"
                                name="pm_message"
                                rows="6"
                                required
                                aria-required="true"
                                placeholder="Treść wiadomości..."
                            ><?php echo esc_textarea( $_POST['pm_message'] ?? '' ); ?></textarea>
                        </div>

                        <p class="contact-required-note"><span aria-hidden="true">*</span> Pola obowiązkowe</p>

                        <button type="submit" class="contact-submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            Wyślij wiadomość
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

            </div>

            <div class="contact-map-wrap">
                <h2 class="contact-map-title">Jak do nas trafić</h2>
                <iframe
                    class="contact-map"
                    src="<?php echo esc_url( get_theme_mod( 'parafia_map_url', 'https://maps.google.com/maps?q=Parafia+Rzk%C5%82+w%C5%9B%C4%85w+Teresa+Mrozy&output=embed' ) ); ?>"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Mapa dojazdu do parafii w Mrozach"
                    aria-label="Mapa Google z lokalizacją parafii"
                ></iframe>
            </div>

        </div>
    </div>

</main>

<?php get_footer(); ?>
