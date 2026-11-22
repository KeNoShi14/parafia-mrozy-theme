<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function parafia_turnstile_site_key() {
    if ( defined( 'PARAFIA_TURNSTILE_SITE_KEY' ) && PARAFIA_TURNSTILE_SITE_KEY ) {
        return (string) PARAFIA_TURNSTILE_SITE_KEY;
    }
    return trim( (string) get_theme_mod( 'pm_turnstile_site_key', '' ) );
}

function parafia_turnstile_secret_key() {
    if ( defined( 'PARAFIA_TURNSTILE_SECRET_KEY' ) && PARAFIA_TURNSTILE_SECRET_KEY ) {
        return (string) PARAFIA_TURNSTILE_SECRET_KEY;
    }
    return trim( (string) get_theme_mod( 'pm_turnstile_secret_key', '' ) );
}

function parafia_turnstile_enabled() {
    return '' !== parafia_turnstile_site_key() && '' !== parafia_turnstile_secret_key();
}

function parafia_turnstile_field( $action = 'formularz' ) {
    if ( ! parafia_turnstile_enabled() ) {
        return;
    }

    wp_enqueue_script(
        'cloudflare-turnstile',
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        [],
        null,
        [ 'in_footer' => true, 'strategy' => 'defer' ]
    );
    printf(
        '<div class="cf-turnstile contact-turnstile" data-sitekey="%1$s" data-action="%2$s" data-language="pl" data-theme="auto"></div>',
        esc_attr( parafia_turnstile_site_key() ),
        esc_attr( $action )
    );
}

function parafia_turnstile_verify() {
    if ( ! parafia_turnstile_enabled() ) {
        return true;
    }

    $token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
    if ( '' === $token ) {
        return 'Prosimy potwierdzić, że nie jesteś robotem (pole weryfikacji nad przyciskiem).';
    }

    $odpowiedz = wp_remote_post(
        'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        [
            'timeout' => 10,
            'body'    => [
                'secret'   => parafia_turnstile_secret_key(),
                'response' => $token,
                'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
            ],
        ]
    );

    if ( is_wp_error( $odpowiedz ) || 200 !== (int) wp_remote_retrieve_response_code( $odpowiedz ) ) {
        return 'Nie udało się zweryfikować formularza. Prosimy spróbować ponownie za chwilę.';
    }

    $dane = json_decode( wp_remote_retrieve_body( $odpowiedz ), true );
    if ( empty( $dane['success'] ) ) {
        return 'Weryfikacja nie powiodła się lub wygasła. Prosimy zaznaczyć pole ponownie i wysłać wiadomość.';
    }

    return true;
}
