<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function parafia_mrozy_extra_customizer( $wp_customize ) {

    $wp_customize->add_section( 'parafia_colors', [
        'title'    => __( 'Kolory motywu', 'parafia-mrozy' ),
        'priority' => 32,
    ] );

    $colors = [
        'color_gold'    => [ 'label' => 'Kolor złoty (główny akcent)', 'default' => '#c9a84c' ],
        'color_dark_bg' => [ 'label' => 'Kolor ciemnego tła (nagłówek/stopka)', 'default' => '#1c1c1c' ],
    ];

    foreach ( $colors as $key => $args ) {
        $wp_customize->add_setting( $key, [
            'default'           => $args['default'],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage',
        ] );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, [
            'label'   => $args['label'],
            'section' => 'parafia_colors',
        ] ) );
    }
    $wp_customize->add_section( 'parafia_kaplani', [
        'title'    => __( 'Kapłani — zdjęcia', 'parafia-mrozy' ),
        'priority' => 34,
    ] );

    $photos = [
        'foto_proboszcz' => 'Zdjęcie proboszcza (Ks. Pietrzak)',
        'foto_wikariusz' => 'Zdjęcie wikariusza (Ks. Łyskowicz)',
    ];

    foreach ( $photos as $key => $label ) {
        $wp_customize->add_setting( $key, [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ] );
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, [
            'label'   => $label,
            'section' => 'parafia_kaplani',
        ] ) );
    }
}
add_action( 'customize_register', 'parafia_mrozy_extra_customizer' );

function parafia_mrozy_dynamic_css() {
    $gold    = get_theme_mod( 'color_gold', '#c9a84c' );
    $dark_bg = get_theme_mod( 'color_dark_bg', '#1c1c1c' );

    if ( $gold === '#c9a84c' && $dark_bg === '#1c1c1c' ) return;

    echo '<style id="parafia-mrozy-dynamic-css">
        :root {
            --color-gold: ' . esc_attr( $gold ) . ';
            --color-dark-bg: ' . esc_attr( $dark_bg ) . ';
        }
    </style>';
}
add_action( 'wp_head', 'parafia_mrozy_dynamic_css' );
