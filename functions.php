<?php

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PARAFIA_MROZY_VERSION', '1.4.0' );

require_once get_template_directory() . '/inc/webp.php';
require_once get_template_directory() . '/inc/galeria.php';
require_once get_template_directory() . '/inc/ustawienia.php';
require_once get_template_directory() . '/inc/uprawnienia.php';
require_once get_template_directory() . '/inc/adwent.php';

function parafia_mrozy_setup() {
    load_theme_textdomain( 'parafia-mrozy', get_template_directory() . '/languages' );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
    ] );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'responsive-embeds' );

    add_theme_support( 'custom-logo', [
        'height'      => 80,
        'width'       => 80,
        'flex-height' => true,
        'flex-width'  => true,
    ] );

    add_image_size( 'card-thumb', 600, 380, true );
    add_image_size( 'hero-image', 1400, 600, true );
    add_image_size( 'gallery-thumb', 500, 375, true );

    register_nav_menus( [
        'primary' => __( 'Menu główne', 'parafia-mrozy' ),
        'footer'  => __( 'Menu stopki', 'parafia-mrozy' ),
    ] );
}
add_action( 'after_setup_theme', 'parafia_mrozy_setup' );


function parafia_mrozy_page_templates_map() {
    return [
        'page-sakramenty.php'      => 'Sakramenty',
        'page-kaplani.php'         => 'Kapłani parafii',
        'page-kancelaria.php'      => 'Kancelaria parafialna',
        'page-msze.php'            => 'Msze święte i nabożeństwa',
        'page-chorzy.php'          => 'Wizyty u chorych',
        'page-transmisje.php'      => 'Transmisje',
        'page-group.php'           => 'Grupa / wspólnota',
        'page-historia.php'        => 'Historia parafii',
        'page-kontakt.php'         => 'Kontakt',
        'page-czytania.php'        => 'Czytania',
        'page-lso.php'             => 'Liturgiczna Służba Ołtarza',
        'page-bielanki.php'        => 'Bielanki',
        'page-galerie.php'         => 'Galerie',
        'page-wirtualny-spacer.php'=> 'Wirtualny spacer',
    ];
}


function parafia_mrozy_register_page_templates( $templates ) {
    foreach ( parafia_mrozy_page_templates_map() as $plik => $nazwa ) {
        if ( file_exists( get_theme_file_path( $plik ) ) ) {
            $templates[ $plik ] = $nazwa;
        }
    }
    return $templates;
}
add_filter( 'theme_page_templates', 'parafia_mrozy_register_page_templates' );


function parafia_mrozy_template_by_slug( $template ) {
    if ( ! is_page() ) {
        return $template;
    }
    $post = get_queried_object();
    if ( ! $post instanceof WP_Post ) {
        return $template;
    }
    // Jeśli szablon został wybrany ręcznie, nie ingerujemy.
    $wybrany = get_post_meta( $post->ID, '_wp_page_template', true );
    if ( $wybrany && 'default' !== $wybrany ) {
        return $template;
    }
    $kandydat = 'page-' . $post->post_name . '.php';
    if ( isset( parafia_mrozy_page_templates_map()[ $kandydat ] ) ) {
        $sciezka = get_theme_file_path( $kandydat );
        if ( file_exists( $sciezka ) ) {
            return $sciezka;
        }
    }
    return $template;
}
add_filter( 'template_include', 'parafia_mrozy_template_by_slug', 20 );


function parafia_mrozy_strona_ma_szablon( $post, $plik_szablonu ) {
    if ( ! $post instanceof WP_Post ) {
        $post = get_post( $post );
    }
    if ( ! $post ) {
        return false;
    }
    $wybrany = get_post_meta( $post->ID, '_wp_page_template', true );
    if ( $wybrany === $plik_szablonu ) {
        return true;
    }
    // Dopasowanie przez slug tylko gdy nie wybrano innego, niedomyślnego szablonu.
    if ( ( ! $wybrany || 'default' === $wybrany ) && ( 'page-' . $post->post_name . '.php' ) === $plik_szablonu ) {
        return true;
    }
    return false;
}

function parafia_mrozy_scripts() {
    wp_enqueue_style(
        'parafia-mrozy-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'parafia-mrozy-style',
        get_stylesheet_uri(),
        [],
        PARAFIA_MROZY_VERSION
    );

    wp_enqueue_script(
        'parafia-mrozy-main',
        get_template_directory_uri() . '/js/main.js',
        [],
        PARAFIA_MROZY_VERSION,
        true
    );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'parafia_mrozy_scripts' );


function parafia_mrozy_register_cpt() {

    register_post_type( 'ogloszenia', [
        'labels' => [
            'name'               => __( 'Ogłoszenia parafialne', 'parafia-mrozy' ),
            'singular_name'      => __( 'Ogłoszenie', 'parafia-mrozy' ),
            'add_new'            => __( 'Dodaj ogłoszenie', 'parafia-mrozy' ),
            'add_new_item'       => __( 'Dodaj nowe ogłoszenie', 'parafia-mrozy' ),
            'edit_item'          => __( 'Edytuj ogłoszenie', 'parafia-mrozy' ),
            'view_item'          => __( 'Zobacz ogłoszenie', 'parafia-mrozy' ),
            'all_items'          => __( 'Wszystkie ogłoszenia', 'parafia-mrozy' ),
            'search_items'       => __( 'Szukaj ogłoszeń', 'parafia-mrozy' ),
            'not_found'          => __( 'Nie znaleziono ogłoszeń', 'parafia-mrozy' ),
        ],
        'public'             => true,
        'has_archive'        => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-megaphone',
        'capability_type'    => [ 'ogloszenie', 'ogloszenia' ],
        'map_meta_cap'       => true,
        'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'rewrite'            => [ 'slug' => 'ogloszenia' ],
    ] );

    register_post_type( 'intencje', [
        'labels' => [
            'name'               => __( 'Intencje Mszy Św.', 'parafia-mrozy' ),
            'singular_name'      => __( 'Intencja', 'parafia-mrozy' ),
            'add_new'            => __( 'Dodaj intencję', 'parafia-mrozy' ),
            'add_new_item'       => __( 'Dodaj nową intencję', 'parafia-mrozy' ),
            'edit_item'          => __( 'Edytuj intencję', 'parafia-mrozy' ),
            'all_items'          => __( 'Wszystkie intencje', 'parafia-mrozy' ),
            'not_found'          => __( 'Nie znaleziono intencji', 'parafia-mrozy' ),
        ],
        'public'             => true,
        'has_archive'        => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-book-alt',
        'capability_type'    => [ 'intencja', 'intencje_mszalne' ],
        'map_meta_cap'       => true,
        'supports'           => [ 'title', 'editor', 'thumbnail' ],
        'rewrite'            => [ 'slug' => 'intencje' ],
    ] );

}
add_action( 'init', 'parafia_mrozy_register_cpt' );

function parafia_mrozy_widgets_init() {
    register_sidebar( [
        'name'          => __( 'Sidebar główny', 'parafia-mrozy' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Widgety w sidebarze', 'parafia-mrozy' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ] );

    register_sidebar( [
        'name'          => __( 'Stopka – kolumna 1', 'parafia-mrozy' ),
        'id'            => 'footer-1',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ] );

    register_sidebar( [
        'name'          => __( 'Stopka – kolumna 2', 'parafia-mrozy' ),
        'id'            => 'footer-2',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ] );

    register_sidebar( [
        'name'          => __( 'Stopka – kolumna 3', 'parafia-mrozy' ),
        'id'            => 'footer-3',
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ] );
}
add_action( 'widgets_init', 'parafia_mrozy_widgets_init' );

// Dane parafii i porządek Mszy: panel Parafia → Ustawienia strony (inc/ustawienia.php).

function pm_get( $key, $fallback = '' ) {
    $pola = function_exists( 'parafia_mrozy_pola_ustawien' ) ? parafia_mrozy_pola_ustawien() : [];
    if ( isset( $pola[ $key ] ) ) {
        return esc_html( pm_opcja( $key ) );
    }
    return esc_html( get_theme_mod( $key, $fallback ) );
}

function parafia_mrozy_excerpt_length( $length ) {
    return 25;
}
add_filter( 'excerpt_length', 'parafia_mrozy_excerpt_length' );

function parafia_mrozy_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'parafia_mrozy_excerpt_more' );

function parafia_mrozy_add_meta_boxes() {
    add_meta_box(
        'hero_slide_meta',
        __( 'Ustawienia slajdera hero', 'parafia-mrozy' ),
        'parafia_mrozy_hero_meta_callback',
        'page',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'parafia_mrozy_add_meta_boxes' );

function parafia_mrozy_hero_meta_callback( $post ) {
    wp_nonce_field( 'parafia_hero_nonce', 'hero_nonce' );
    $is_hero = get_post_meta( $post->ID, '_is_hero_slide', true );
    echo '<label><input type="checkbox" name="is_hero_slide" value="1" ' . checked( $is_hero, '1', false ) . '> ';
    echo __( 'Użyj tego wpisu w slajderze hero', 'parafia-mrozy' ) . '</label>';
}

function parafia_mrozy_save_hero_meta( $post_id ) {
    if ( ! isset( $_POST['hero_nonce'] ) || ! wp_verify_nonce( $_POST['hero_nonce'], 'parafia_hero_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    update_post_meta( $post_id, '_is_hero_slide', isset( $_POST['is_hero_slide'] ) ? '1' : '' );
}
add_action( 'save_post', 'parafia_mrozy_save_hero_meta' );

function parafia_mrozy_category_label( $cat_id ) {
    if ( ! $cat_id ) return '';
    $cat = get_category( $cat_id );
    if ( ! $cat || is_wp_error( $cat ) ) return '';
    return '<a href="' . esc_url( get_category_link( $cat_id ) ) . '" class="cat-link">' . esc_html( $cat->name ) . '</a>';
}

function parafia_mrozy_rewrite_flush() {
    parafia_mrozy_register_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'parafia_mrozy_rewrite_flush' );


function parafia_mrozy_intencje_meta_boxes() {
    add_meta_box(
        'intencja_pdf_meta',
        __( 'Plik PDF z intencjami', 'parafia-mrozy' ),
        'parafia_mrozy_intencja_pdf_callback',
        'intencje',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'parafia_mrozy_intencje_meta_boxes' );

function parafia_mrozy_intencja_pdf_callback( $post ) {
    wp_nonce_field( 'parafia_intencja_pdf_nonce', 'intencja_pdf_nonce' );
    $pdf_id  = get_post_meta( $post->ID, '_intencja_pdf_id', true );
    $pdf_url = get_post_meta( $post->ID, '_intencja_pdf_url', true );
    ?>
    <div style="padding: 8px 0;">

        <div id="intencja-pdf-preview" style="margin-bottom: 12px; <?php echo $pdf_url ? '' : 'display:none;'; ?>">
            <?php if ( $pdf_url ) : ?>
                <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:6px;color:#c9a84c;text-decoration:none;font-size:13px;">
                    <span style="background:#c9a84c;color:#fff;padding:2px 7px;border-radius:3px;font-size:11px;font-weight:700;">PDF</span>
                    <?php echo esc_html( basename( $pdf_url ) ); ?>
                </a>
            <?php endif; ?>
        </div>

        <input type="hidden" id="intencja_pdf_id" name="intencja_pdf_id" value="<?php echo esc_attr( $pdf_id ); ?>">
        <input type="hidden" id="intencja_pdf_url" name="intencja_pdf_url" value="<?php echo esc_url( $pdf_url ); ?>">

        <button type="button" id="intencja-pdf-upload-btn" class="button button-secondary">
            <?php echo $pdf_url ? __( 'Zmień plik PDF', 'parafia-mrozy' ) : __( 'Dodaj plik PDF', 'parafia-mrozy' ); ?>
        </button>

        <?php if ( $pdf_url ) : ?>
        <button type="button" id="intencja-pdf-remove-btn" class="button" style="margin-left:6px;color:#a00;">
            <?php _e( 'Usuń PDF', 'parafia-mrozy' ); ?>
        </button>
        <?php endif; ?>

        <p class="description" style="margin-top:8px;">
            <?php _e( 'Wgraj plik PDF z tygodniowymi intencjami. Plik otworzy się w nowej karcie po kliknięciu karty intencji.', 'parafia-mrozy' ); ?>
        </p>
    </div>

    <script>
    (function($){
        var frame;

        $('#intencja-pdf-upload-btn').on('click', function(e){
            e.preventDefault();

            if ( frame ) { frame.open(); return; }

            frame = wp.media({
                title: 'Wybierz lub wgraj plik PDF',
                button: { text: 'Wybierz plik' },
                library: { type: 'application/pdf' },
                multiple: false
            });

            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $('#intencja_pdf_id').val( attachment.id );
                $('#intencja_pdf_url').val( attachment.url );

                var preview = $('#intencja-pdf-preview');
                preview.html(
                    '<a href="' + attachment.url + '" target="_blank" style="display:inline-flex;align-items:center;gap:6px;color:#c9a84c;text-decoration:none;font-size:13px;">' +
                    '<span style="background:#c9a84c;color:#fff;padding:2px 7px;border-radius:3px;font-size:11px;font-weight:700;">PDF</span>' +
                    attachment.filename +
                    '</a>'
                ).show();

                $('#intencja-pdf-upload-btn').text('Zmień plik PDF');

                if ( !$('#intencja-pdf-remove-btn').length ) {
                    $('<button type="button" id="intencja-pdf-remove-btn" class="button" style="margin-left:6px;color:#a00;">Usuń PDF</button>')
                        .insertAfter('#intencja-pdf-upload-btn')
                        .on('click', removePdf);
                }
            });

            frame.open();
        });

        function removePdf(e) {
            e.preventDefault();
            $('#intencja_pdf_id').val('');
            $('#intencja_pdf_url').val('');
            $('#intencja-pdf-preview').hide().html('');
            $('#intencja-pdf-upload-btn').text('Dodaj plik PDF');
            $(this).remove();
        }

        $(document).on('click', '#intencja-pdf-remove-btn', removePdf);

    })(jQuery);
    </script>
    <?php
}

function parafia_mrozy_save_intencja_pdf( $post_id ) {
    if ( ! isset( $_POST['intencja_pdf_nonce'] ) || ! wp_verify_nonce( $_POST['intencja_pdf_nonce'], 'parafia_intencja_pdf_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( get_post_type( $post_id ) !== 'intencje' ) return;

    if ( isset( $_POST['intencja_pdf_id'] ) ) {
        update_post_meta( $post_id, '_intencja_pdf_id', absint( $_POST['intencja_pdf_id'] ) );
    }
    if ( isset( $_POST['intencja_pdf_url'] ) ) {
        $pdf_url_raw = esc_url_raw( $_POST['intencja_pdf_url'] );
        $pdf_url_raw = set_url_scheme( $pdf_url_raw, 'https' );
        update_post_meta( $post_id, '_intencja_pdf_url', $pdf_url_raw );
    }
}
add_action( 'save_post', 'parafia_mrozy_save_intencja_pdf' );

function parafia_mrozy_enqueue_media_uploader( $hook ) {
    global $post;
    if ( ( $hook === 'post-new.php' || $hook === 'post.php' ) && isset( $post ) && $post->post_type === 'intencje' ) {
        wp_enqueue_media();
    }
}
add_action( 'admin_enqueue_scripts', 'parafia_mrozy_enqueue_media_uploader' );


function parafia_mrozy_enqueue_pdfjs() {
    if ( ! is_front_page() && ! is_post_type_archive( 'intencje' ) ) return;

    wp_enqueue_script(
        'pdfjs-lib',
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js',
        [],
        '3.4.120',
        true
    );

    wp_enqueue_script(
        'parafia-mrozy-pdf-preview',
        get_template_directory_uri() . '/js/pdf-preview.js',
        [ 'pdfjs-lib' ],
        PARAFIA_MROZY_VERSION,
        true
    );

    wp_localize_script( 'parafia-mrozy-pdf-preview', 'pmPdfjs', [
        'workerSrc' => 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js',
    ] );
}
add_action( 'wp_enqueue_scripts', 'parafia_mrozy_enqueue_pdfjs' );


function parafia_mrozy_enqueue_lightbox() {
    if ( ! is_singular( 'post' ) ) return;

    wp_enqueue_script(
        'parafia-mrozy-lightbox',
        get_template_directory_uri() . '/js/lightbox.js',
        [],
        PARAFIA_MROZY_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'parafia_mrozy_enqueue_lightbox' );


function parafia_mrozy_gallery_template( $template ) {
    if ( is_singular( 'post' ) ) {
        $cats  = get_the_category();
        $slugs = wp_list_pluck( $cats, 'slug' );
        if ( in_array( 'galeria', $slugs ) ) {
            $gallery_tpl = get_template_directory() . '/single-galeria.php';
            if ( file_exists( $gallery_tpl ) ) {
                return $gallery_tpl;
            }
        }
    }
    return $template;
}
add_filter( 'template_include', 'parafia_mrozy_gallery_template' );

function parafia_mrozy_aria_current( $atts, $item ) {
    if ( in_array( 'current-menu-item', $item->classes ) ) {
        $atts['aria-current'] = 'page';
    }
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'parafia_mrozy_aria_current', 10, 2 );

function parafia_mrozy_document_lang( $output ) {
    return str_replace( 'lang="pl-PL"', 'lang="pl"', $output );
}
add_filter( 'language_attributes', 'parafia_mrozy_document_lang' );


function parafia_mrozy_disable_iframe_lazy( $attr, $tag ) {
    if ( isset( $attr['id'] ) && $attr['id'] === 'VaticanNews' ) {
        unset( $attr['loading'] );
    }
    return $attr;
}
add_filter( 'wp_iframe_tag_add_loading_attr', 'parafia_mrozy_disable_iframe_lazy', 10, 2 );


function parafia_mrozy_lso_gallery_meta_box() {
    add_meta_box(
        'lso_gallery',
        'Galeria LSO — ID zdjęć',
        'parafia_mrozy_lso_gallery_callback',
        'page',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'parafia_mrozy_lso_gallery_meta_box' );

function parafia_mrozy_lso_gallery_callback( $post ) {
    wp_nonce_field( 'lso_gallery_nonce', 'lso_gallery_nonce' );
    $min = get_post_meta( $post->ID, 'lso_gallery_ministranci', true );
    $bie = get_post_meta( $post->ID, 'lso_gallery_bielanki',   true );
    echo '<p><label><strong>Ministranci i Lektorzy — ID zdjęć (po przecinku):</strong></label><br>';
    echo '<input type="text" name="lso_gallery_ministranci" value="' . esc_attr( $min ) . '" style="width:100%;margin-top:4px;" placeholder="np. 42,57,83"></p>';
    echo '<p><label><strong>Bielanki — ID zdjęć (po przecinku):</strong></label><br>';
    echo '<input type="text" name="lso_gallery_bielanki" value="' . esc_attr( $bie ) . '" style="width:100%;margin-top:4px;" placeholder="np. 44,61,90"></p>';
    echo '<p style="color:#888;font-size:12px;">ID zdjęcia znajdziesz w Bibliotece multimediów — kliknij zdjęcie i sprawdź URL (np. ?item=<strong>42</strong>).</p>';
}

function parafia_mrozy_lso_gallery_save( $post_id ) {
    if ( ! isset( $_POST['lso_gallery_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['lso_gallery_nonce'], 'lso_gallery_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['lso_gallery_ministranci'] ) )
        update_post_meta( $post_id, 'lso_gallery_ministranci', sanitize_text_field( $_POST['lso_gallery_ministranci'] ) );
    if ( isset( $_POST['lso_gallery_bielanki'] ) )
        update_post_meta( $post_id, 'lso_gallery_bielanki', sanitize_text_field( $_POST['lso_gallery_bielanki'] ) );
}
add_action( 'save_post', 'parafia_mrozy_lso_gallery_save' );



// Konwersja zdjęć do WebP przy wgrywaniu: inc/webp.php


function parafia_pg_meta_boxes() {
    add_meta_box(
        'pg_group_details',
        '⛪ Dane grupy parafialnej',
        'parafia_pg_meta_callback',
        'page',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'parafia_pg_meta_boxes' );

function parafia_pg_meta_callback( $post ) {
    $template = get_post_meta( $post->ID, '_wp_page_template', true );
    if ( $template !== 'page-group.php' ) {
        echo '<p style="color:#888">Ten panel jest aktywny tylko gdy szablon strony ustawiony jest na <strong>Grupa parafialna</strong>.</p>';
        return;
    }

    wp_nonce_field( 'pg_group_nonce', 'pg_group_nonce' );

    $fields = [
        'pg_meetings'  => [ 'label' => '📅 Terminy spotkań',        'placeholder' => 'np. co dwie soboty o 10:00 w Sali Caritas',  'type' => 'text' ],
        'pg_quote'     => [ 'label' => '✝ Cytat świętego (opcja)',   'placeholder' => 'np. Wszystko mogę w Tym, który mnie umacnia', 'type' => 'textarea' ],
        'pg_quote_src' => [ 'label' => '📖 Źródło cytatu',           'placeholder' => 'np. św. Paweł Apostoł, Flp 4, 13',           'type' => 'text' ],
        'pg_contact'   => [ 'label' => '👤 Opiekun / kontakt',       'placeholder' => 'np. ks. Tomasz Jarzębski',                   'type' => 'text' ],
        'pg_email'     => [ 'label' => '✉ Adres e-mail',             'placeholder' => 'np. grupa@parafia.pl',                       'type' => 'text' ],
        'pg_gallery'   => [ 'label' => '📷 Galeria — ID zdjęć (maks 5, po przecinku)', 'placeholder' => 'np. 42, 57, 83, 91, 104',  'type' => 'text' ],
    ];

    echo '<table style="width:100%;border-collapse:collapse;">';
    foreach ( $fields as $key => $f ) {
        $val = get_post_meta( $post->ID, $key, true );
        echo '<tr><td style="padding:10px 0 4px;font-weight:600;font-size:13px;">' . esc_html($f['label']) . '</td></tr>';
        echo '<tr><td style="padding:0 0 16px;">';
        if ( $f['type'] === 'textarea' ) {
            echo '<textarea name="' . esc_attr($key) . '" rows="3" style="width:100%;border:1px solid #ddd;border-radius:4px;padding:8px;font-size:13px;" placeholder="' . esc_attr($f['placeholder']) . '">' . esc_textarea($val) . '</textarea>';
        } else {
            echo '<input type="text" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" style="width:100%;border:1px solid #ddd;border-radius:4px;padding:8px;font-size:13px;" placeholder="' . esc_attr($f['placeholder']) . '">';
        }
        echo '</td></tr>';
    }
    echo '</table>';
    echo '<p style="color:#888;font-size:12px;margin-top:4px;">💡 ID zdjęcia znajdziesz klikając zdjęcie w Bibliotece multimediów i sprawdzając URL (np. ?item=<strong>42</strong>).</p>';
}

function parafia_pg_save_meta( $post_id ) {
    if ( ! isset( $_POST['pg_group_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['pg_group_nonce'], 'pg_group_nonce' ) ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $keys = [ 'pg_meetings', 'pg_quote', 'pg_quote_src', 'pg_contact', 'pg_email', 'pg_gallery' ];
    foreach ( $keys as $key ) {
        if ( isset( $_POST[$key] ) ) {
            update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[$key] ) ) );
        }
    }
}
add_action( 'save_post', 'parafia_pg_save_meta' );


function parafia_show_attachment_id( $form_fields, $post ) {
    $form_fields['attachment_id'] = [
        'label' => 'ID pliku',
        'input' => 'html',
        'html'  => '<input type="text" readonly value="' . esc_attr( $post->ID ) . '" style="width:80px;font-weight:700;font-size:14px;background:#f0f6fc;border:1px solid #c9a84c;border-radius:4px;padding:4px 8px;cursor:text;" onclick="this.select()" title="Kliknij aby zaznaczyć">
                    <button type="button" onclick="navigator.clipboard.writeText(\'' . esc_js( $post->ID ) . '\');this.textContent=\'✓ Skopiowano\';setTimeout(()=>this.textContent=\'Kopiuj\',2000)" style="margin-left:6px;padding:4px 10px;background:#c9a84c;color:#1a1a1a;border:none;border-radius:4px;cursor:pointer;font-size:12px;font-weight:600;">Kopiuj</button>',
        'helps' => 'Użyj tego ID aby dodać zdjęcie do galerii grupy.',
    ];
    return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'parafia_show_attachment_id', 10, 2 );


function parafia_kaplani_register_meta_box() {
    add_meta_box(
        'parafia_kaplani_dane',
        '⛪ Kapłani parafii — dane i zdjęcia',
        'parafia_kaplani_meta_box_html',
        'page',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'parafia_kaplani_register_meta_box' );

function parafia_kaplani_meta_box_html( $post ) {
    $template = get_post_meta( $post->ID, '_wp_page_template', true );
    if ( $template !== 'page-kaplani.php' ) {
        echo '<p style="color:#888;font-style:italic;">Ten panel jest aktywny tylko gdy wybrany szablon to <strong>Kapłani Parafii</strong>.</p>';
        return;
    }

    wp_nonce_field( 'parafia_kaplani_nonce_action', 'parafia_kaplani_nonce' );

    $prob_imie = get_post_meta( $post->ID, '_kaplani_prob_imie', true ) ?: 'Ks. Krzysztof Pietrzak';
    $prob_od   = get_post_meta( $post->ID, '_kaplani_prob_od',   true ) ?: '6 lipca 2013';
    $wik_imie  = get_post_meta( $post->ID, '_kaplani_wik_imie',  true );
    $wik_od    = get_post_meta( $post->ID, '_kaplani_wik_od',    true );
    $rez_imie  = get_post_meta( $post->ID, '_kaplani_rez_imie',  true );
    $rez_od    = get_post_meta( $post->ID, '_kaplani_rez_od',    true );

    $foto_prob_id  = (int) get_post_meta( $post->ID, '_kaplani_foto_proboszcz_id', true );
    $foto_wik_id   = (int) get_post_meta( $post->ID, '_kaplani_foto_wikariusz_id', true );
    $foto_rez_id   = (int) get_post_meta( $post->ID, '_kaplani_foto_rezydent_id',  true );
    $foto_prob_url = $foto_prob_id ? wp_get_attachment_image_url( $foto_prob_id, 'medium' ) : '';
    $foto_wik_url  = $foto_wik_id  ? wp_get_attachment_image_url( $foto_wik_id,  'medium' ) : '';
    $foto_rez_url  = $foto_rez_id  ? wp_get_attachment_image_url( $foto_rez_id,  'medium' ) : '';

    $default_prob = json_encode([
        [ 'imie' => 'Ks. Jankowski Eugeniusz',       'od' => '07.12.2008', 'do' => '06.07.2013', 'zmarl' => false ],
        [ 'imie' => 'Ks. Kasprzak Marek',            'od' => '24.06.1999', 'do' => '07.12.2008', 'zmarl' => false ],
        [ 'imie' => 'Ks. Nowosielski Wiesław',       'od' => '07.02.1986', 'do' => '24.06.1999', 'zmarl' => false ],
        [ 'imie' => 'Ks. Tartas Tadeusz',            'od' => '14.02.1980', 'do' => '03.02.1986', 'zmarl' => true  ],
        [ 'imie' => 'Ks. Kajtaniak Marian',          'od' => '05.08.1965', 'do' => '14.02.1980', 'zmarl' => false ],
        [ 'imie' => 'Ks. Mińkowski Bogumił Marian',  'od' => '29.04.1956', 'do' => '05.08.1965', 'zmarl' => false ],
        [ 'imie' => 'Ks. Kaplewski Paweł Marian',    'od' => '31.10.1948', 'do' => '16.03.1956', 'zmarl' => false ],
        [ 'imie' => 'Ks. Fertak Kazimierz',          'od' => '03.02.1941', 'do' => '10.06.1948', 'zmarl' => true  ],
        [ 'imie' => 'Ks. Dobrowolski Leon',          'od' => '01.09.1939', 'do' => '03.02.1941', 'zmarl' => false ],
        [ 'imie' => 'Ks. Lejchte Zdzisław',          'od' => '27.10.1934', 'do' => '01.09.1939', 'zmarl' => false ],
        [ 'imie' => 'Ks. Kołodziejski Jan',          'od' => '01.04.1931', 'do' => '27.10.1934', 'zmarl' => false ],
    ], JSON_UNESCAPED_UNICODE);

    $default_wik = json_encode([
        [ 'imie' => 'Ks. Bodziak Marcin',       'od' => '26.08.2018', 'do' => '26.08.2021', 'zmarl' => false ],
        [ 'imie' => 'Ks. Ziółkowski Robert',    'od' => '28.08.2011', 'do' => '26.08.2018', 'zmarl' => false ],
        [ 'imie' => 'Ks. Kołbuk Jacek',         'od' => '01.07.2007', 'do' => '28.08.2011', 'zmarl' => false ],
        [ 'imie' => 'Ks. Zdanowicz Marek',      'od' => '02.09.2004', 'do' => '01.07.2007', 'zmarl' => false ],
        [ 'imie' => 'Ks. Skowyra Zbigniew',     'od' => '28.06.2003', 'do' => '02.09.2004', 'zmarl' => false ],
        [ 'imie' => 'Ks. Zieliński Paweł',      'od' => '29.06.2002', 'do' => '28.06.2003', 'zmarl' => false ],
        [ 'imie' => 'Ks. Sobczak Sławomir',     'od' => '26.08.2000', 'do' => '02.07.2002', 'zmarl' => false ],
        [ 'imie' => 'Ks. Budzyński Krzysztof',  'od' => '01.07.1999', 'do' => '26.08.2000', 'zmarl' => false ],
        [ 'imie' => 'Ks. Cymerman Paweł',       'od' => '25.05.1996', 'do' => '24.06.1999', 'zmarl' => true  ],
        [ 'imie' => 'Ks. Główka Piotr',         'od' => '28.05.1995', 'do' => '22.06.1996', 'zmarl' => false ],
        [ 'imie' => 'Ks. Sałański Bogdan',      'od' => '22.06.1992', 'do' => '22.06.1995', 'zmarl' => false ],
        [ 'imie' => 'Ks. Osiadacz Tomasz',      'od' => '21.05.1989', 'do' => '22.06.1992', 'zmarl' => false ],
        [ 'imie' => 'Ks. Korbik Dariusz Edward','od' => '10.06.1987', 'do' => '15.06.1989', 'zmarl' => false ],
        [ 'imie' => 'Ks. Kapłański Zbigniew',   'od' => '16.06.1986', 'do' => '10.06.1987', 'zmarl' => false ],
        [ 'imie' => 'Ks. Osiński Wacław',       'od' => '13.06.1984', 'do' => '16.06.1986', 'zmarl' => false ],
        [ 'imie' => 'Ks. Krześniak Leszek',     'od' => '10.06.1983', 'do' => '13.06.1984', 'zmarl' => false ],
        [ 'imie' => 'Ks. Grędowski Władysław',  'od' => '03.06.1980', 'do' => '10.06.1983', 'zmarl' => false ],
    ], JSON_UNESCAPED_UNICODE);

    $lista_prob_json = get_post_meta( $post->ID, '_kaplani_lista_proboszczow', true ) ?: $default_prob;
    $lista_wik_json  = get_post_meta( $post->ID, '_kaplani_lista_wikariuszy',  true ) ?: $default_wik;
    $lista_prob = json_decode( $lista_prob_json, true ) ?: [];
    $lista_wik  = json_decode( $lista_wik_json,  true ) ?: [];

    ?>
    <style>
        .km-tabs { display: flex; gap: 0; border-bottom: 2px solid #c9a84c; margin-bottom: 20px; }
        .km-tab  { padding: 8px 20px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; background: #f6f7f7; color: #50575e; border-radius: 4px 4px 0 0; margin-right: 4px; }
        .km-tab.active { background: #c9a84c; color: #fff; }
        .km-panel { display: none; }
        .km-panel.active { display: block; }

        .km-current-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .km-current-card { background: #f6f7f7; border: 1px solid #ddd; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
        .km-current-card h4 { margin: 0 0 4px; font-size: 13px; color: #1d2327; }
        .km-field-row { display: flex; flex-direction: column; gap: 4px; }
        .km-field-row label { font-size: 12px; color: #50575e; font-weight: 600; }
        .km-field-row input[type=text] { width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 13px; }
        .km-photo-preview { width: 100%; max-width: 140px; aspect-ratio: 3/4; object-fit: cover; object-position: top; border-radius: 6px; border: 2px solid #c9a84c; display: block; }
        .km-photo-placeholder { width: 140px; aspect-ratio: 3/4; background: #f0f0f1; border: 2px dashed #c9a84c; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #8c8f94; font-size: 12px; text-align: center; padding: 10px; }
        .km-btns { display: flex; gap: 6px; flex-wrap: wrap; }
        .km-btns .button { font-size: 12px !important; }

        .km-list { display: flex; flex-direction: column; gap: 0; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; margin-bottom: 12px; }
        .km-list-row { display: grid; grid-template-columns: 22px 1fr 110px 110px 80px 36px; gap: 6px; align-items: center; padding: 8px 10px; border-bottom: 1px solid #eee; background: #fff; transition: background 0.15s, box-shadow 0.15s; }
        .km-list-row:last-child { border-bottom: none; }
        .km-list-row:nth-child(even) { background: #fafafa; }
        .km-list-row input[type=text] { width: 100%; padding: 5px 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 12px; }
        .km-list-row input[type=checkbox] { margin: 0 auto; display: block; }
        .km-list-header { display: grid; grid-template-columns: 22px 1fr 110px 110px 80px 36px; gap: 6px; padding: 6px 10px; background: #f0f0f0; border-bottom: 1px solid #ddd; }
        .km-list-header span { font-size: 11px; font-weight: 700; color: #50575e; text-transform: uppercase; letter-spacing: .04em; }
        .km-add-row { margin-top: 8px; }
        .km-del-btn { background: none; border: none; cursor: pointer; color: #cc1818; font-size: 16px; padding: 0; line-height: 1; }
        .km-drag-handle { cursor: grab; color: #aaa; font-size: 14px; line-height: 1; text-align: center; user-select: none; padding-top: 1px; }
        .km-drag-handle:active { cursor: grabbing; }
        .km-list-row.sortable-ghost { opacity: 0.4; background: #fff8e6 !important; }
        .km-list-row.sortable-chosen { box-shadow: 0 2px 12px rgba(201,168,76,0.25); background: #fffdf5 !important; z-index: 10; }
    </style>

    <input type="hidden" name="kaplani_lista_proboszczow" id="kaplani_lista_proboszczow"
           value="<?php echo esc_attr( $lista_prob_json ); ?>">
    <input type="hidden" name="kaplani_lista_wikariuszy" id="kaplani_lista_wikariuszy"
           value="<?php echo esc_attr( $lista_wik_json ); ?>">

    <!-- Zakładki -->
    <div class="km-tabs">
        <button type="button" class="km-tab active" data-panel="km-panel-obecni">👤 Obecni kapłani</button>
        <button type="button" class="km-tab" data-panel="km-panel-prob">📋 Proboszczowie</button>
        <button type="button" class="km-tab" data-panel="km-panel-wik">📋 Wikariusze</button>
    </div>

    <!-- Panel: Obecni -->
    <div class="km-panel active" id="km-panel-obecni">
        <div class="km-current-grid">

            <!-- Proboszcz -->
            <div class="km-current-card">
                <h4>🟡 Proboszcz</h4>
                <div class="km-field-row">
                    <label for="kaplani_prob_imie">Imię i nazwisko</label>
                    <input type="text" id="kaplani_prob_imie" name="kaplani_prob_imie"
                           value="<?php echo esc_attr( $prob_imie ); ?>" placeholder="Ks. Imię Nazwisko">
                </div>
                <div class="km-field-row">
                    <label for="kaplani_prob_od">Posługuje od</label>
                    <input type="text" id="kaplani_prob_od" name="kaplani_prob_od"
                           value="<?php echo esc_attr( $prob_od ); ?>" placeholder="np. 6 lipca 2013">
                </div>
                <div id="km-prob-preview">
                    <?php if ( $foto_prob_url ) : ?>
                        <img src="<?php echo esc_url( $foto_prob_url ); ?>" class="km-photo-preview" alt="">
                    <?php else : ?>
                        <div class="km-photo-placeholder">Brak zdjęcia</div>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="kaplani_foto_proboszcz_id" id="kaplani_foto_proboszcz_id"
                       value="<?php echo esc_attr( $foto_prob_id ?: '' ); ?>">
                <div class="km-btns">
                    <button type="button" class="button button-primary kaplani-upload-btn"
                            data-target="kaplani_foto_proboszcz_id" data-preview="km-prob-preview"
                            data-title="Wybierz zdjęcie proboszcza">
                        <?php echo $foto_prob_id ? '🔄 Zmień zdjęcie' : '📷 Dodaj zdjęcie'; ?>
                    </button>
                    <?php if ( $foto_prob_id ) : ?>
                    <button type="button" class="button kaplani-remove-btn"
                            data-target="kaplani_foto_proboszcz_id" data-preview="km-prob-preview">Usuń</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Wikariusz -->
            <div class="km-current-card">
                <h4>🔘 Wikariusz</h4>
                <div class="km-field-row">
                    <label for="kaplani_wik_imie">Imię i nazwisko</label>
                    <input type="text" id="kaplani_wik_imie" name="kaplani_wik_imie"
                           value="<?php echo esc_attr( $wik_imie ); ?>" placeholder="Ks. Imię Nazwisko">
                </div>
                <div class="km-field-row">
                    <label for="kaplani_wik_od">Posługuje od</label>
                    <input type="text" id="kaplani_wik_od" name="kaplani_wik_od"
                           value="<?php echo esc_attr( $wik_od ); ?>" placeholder="np. 26 sierpnia 2021">
                </div>
                <div id="km-wik-preview">
                    <?php if ( $foto_wik_url ) : ?>
                        <img src="<?php echo esc_url( $foto_wik_url ); ?>" class="km-photo-preview" alt="">
                    <?php else : ?>
                        <div class="km-photo-placeholder">Brak zdjęcia</div>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="kaplani_foto_wikariusz_id" id="kaplani_foto_wikariusz_id"
                       value="<?php echo esc_attr( $foto_wik_id ?: '' ); ?>">
                <div class="km-btns">
                    <button type="button" class="button button-primary kaplani-upload-btn"
                            data-target="kaplani_foto_wikariusz_id" data-preview="km-wik-preview"
                            data-title="Wybierz zdjęcie wikariusza">
                        <?php echo $foto_wik_id ? '🔄 Zmień zdjęcie' : '📷 Dodaj zdjęcie'; ?>
                    </button>
                    <?php if ( $foto_wik_id ) : ?>
                    <button type="button" class="button kaplani-remove-btn"
                            data-target="kaplani_foto_wikariusz_id" data-preview="km-wik-preview">Usuń</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rezydent -->
            <div class="km-current-card">
                <h4>🔘 Rezydent</h4>
                <div class="km-field-row">
                    <label for="kaplani_rez_imie">Imię i nazwisko</label>
                    <input type="text" id="kaplani_rez_imie" name="kaplani_rez_imie"
                           value="<?php echo esc_attr( $rez_imie ); ?>" placeholder="Ks. Imię Nazwisko">
                </div>
                <div class="km-field-row">
                    <label for="kaplani_rez_od">Posługuje od</label>
                    <input type="text" id="kaplani_rez_od" name="kaplani_rez_od"
                           value="<?php echo esc_attr( $rez_od ); ?>" placeholder="np. 1 września 2024">
                </div>
                <div id="km-rez-preview">
                    <?php if ( $foto_rez_url ) : ?>
                        <img src="<?php echo esc_url( $foto_rez_url ); ?>" class="km-photo-preview" alt="">
                    <?php else : ?>
                        <div class="km-photo-placeholder">Brak zdjęcia</div>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="kaplani_foto_rezydent_id" id="kaplani_foto_rezydent_id"
                       value="<?php echo esc_attr( $foto_rez_id ?: '' ); ?>">
                <div class="km-btns">
                    <button type="button" class="button button-primary kaplani-upload-btn"
                            data-target="kaplani_foto_rezydent_id" data-preview="km-rez-preview"
                            data-title="Wybierz zdjęcie rezydenta">
                        <?php echo $foto_rez_id ? '🔄 Zmień zdjęcie' : '📷 Dodaj zdjęcie'; ?>
                    </button>
                    <?php if ( $foto_rez_id ) : ?>
                    <button type="button" class="button kaplani-remove-btn"
                            data-target="kaplani_foto_rezydent_id" data-preview="km-rez-preview">Usuń</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <p style="color:#666;font-size:12px;margin-top:14px;">
            💡 Pozostaw pole <strong>„Imię i nazwisko"</strong> puste, aby ukryć danego kapłana (wikariusza lub rezydenta) na stronie.
        </p>
    </div>

    <!-- Panel: Proboszczowie historyczni -->
    <div class="km-panel" id="km-panel-prob">
        <p style="color:#666;font-size:12px;margin-bottom:12px;">Lista byłych proboszczów (od najnowszego). Zaznacz ✝ jeśli kapłan już nie żyje.</p>
        <div class="km-list-header">
            <span></span><span>Imię i nazwisko</span><span>Data od</span><span>Data do</span><span>✝ Zmarły</span><span></span>
        </div>
        <div class="km-list" id="km-prob-list">
            <?php foreach ( $lista_prob as $i => $k ) : ?>
            <div class="km-list-row" data-list="prob">
                <span class="km-drag-handle" title="Przeciągnij aby zmienić kolejność">⠿</span>
                <input type="text" class="km-imie" value="<?php echo esc_attr( $k['imie'] ); ?>" placeholder="Ks. Imię Nazwisko">
                <input type="text" class="km-od"   value="<?php echo esc_attr( $k['od'] );   ?>" placeholder="dd.mm.rrrr">
                <input type="text" class="km-do"   value="<?php echo esc_attr( $k['do'] );   ?>" placeholder="dd.mm.rrrr">
                <input type="checkbox" class="km-zmarl" <?php checked( $k['zmarl'] ); ?>>
                <button type="button" class="km-del-btn" title="Usuń wiersz">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button km-add-row" data-list="prob">+ Dodaj proboszcza</button>
    </div>

    <!-- Panel: Wikariusze historyczni -->
    <div class="km-panel" id="km-panel-wik">
        <p style="color:#666;font-size:12px;margin-bottom:12px;">Lista byłych wikariuszy (od najnowszego). Zaznacz ✝ jeśli kapłan już nie żyje.</p>
        <div class="km-list-header">
            <span>Imię i nazwisko</span><span>Data od</span><span>Data do</span><span>✝ Zmarły</span><span></span>
        </div>
        <div class="km-list" id="km-wik-list">
            <?php foreach ( $lista_wik as $i => $k ) : ?>
            <div class="km-list-row" data-list="wik">
                <span class="km-drag-handle" title="Przeciągnij aby zmienić kolejność">⠿</span>
                <input type="text" class="km-imie" value="<?php echo esc_attr( $k['imie'] ); ?>" placeholder="Ks. Imię Nazwisko">
                <input type="text" class="km-od"   value="<?php echo esc_attr( $k['od'] );   ?>" placeholder="dd.mm.rrrr">
                <input type="text" class="km-do"   value="<?php echo esc_attr( $k['do'] );   ?>" placeholder="dd.mm.rrrr">
                <input type="checkbox" class="km-zmarl" <?php checked( $k['zmarl'] ); ?>>
                <button type="button" class="km-del-btn" title="Usuń wiersz">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button km-add-row" data-list="wik">+ Dodaj wikariusza</button>
    </div>

    <script>
    jQuery(function($){

        $('.km-tab').on('click', function(){
            $('.km-tab').removeClass('active');
            $('.km-panel').removeClass('active');
            $(this).addClass('active');
            $('#' + $(this).data('panel')).addClass('active');
        });

        function serialize_list(listId, hiddenId) {
            var data = [];
            $('#' + listId + ' .km-list-row').each(function(){
                data.push({
                    imie:  $(this).find('.km-imie').val(),
                    od:    $(this).find('.km-od').val(),
                    do:    $(this).find('.km-do').val(),
                    zmarl: $(this).find('.km-zmarl').is(':checked')
                });
            });
            $('#' + hiddenId).val(JSON.stringify(data));
        }

        function serialize_all() {
            serialize_list('km-prob-list', 'kaplani_lista_proboszczow');
            serialize_list('km-wik-list',  'kaplani_lista_wikariuszy');
        }

        $('form#post, #post').on('submit', serialize_all);

        $(document).on('click', '.editor-post-publish-button, .editor-post-save-draft, ' +
            '.editor-post-publish-panel__publish-button, ' +
            'button.is-primary[type="button"], ' +
            '#publish, #save-action input[type=submit]', function(){
            serialize_all();
        });

        function attach_change_listeners() {
            $('#km-prob-list, #km-wik-list').on(
                'input change',
                '.km-imie, .km-od, .km-do, .km-zmarl',
                function() { serialize_all(); }
            );
        }
        attach_change_listeners();

        $('.km-add-row').on('click', function(){
            var list = $(this).data('list');
            var row = '<div class="km-list-row" data-list="' + list + '">' +
                '<span class="km-drag-handle" title="Przeciągnij aby zmienić kolejność">⠿</span>' +
                '<input type="text" class="km-imie" placeholder="Ks. Imię Nazwisko">' +
                '<input type="text" class="km-od"   placeholder="dd.mm.rrrr">' +
                '<input type="text" class="km-do"   placeholder="dd.mm.rrrr">' +
                '<input type="checkbox" class="km-zmarl">' +
                '<button type="button" class="km-del-btn" title="Usuń">✕</button>' +
                '</div>';
            $('#km-' + list + '-list').append(row);
            serialize_all();
        });

        $(document).on('click', '.km-del-btn', function(){
            if ( confirm('Usunąć ten wiersz?') ) {
                $(this).closest('.km-list-row').remove();
                serialize_all();
            }
        });

        $(document).on('click', '.kaplani-upload-btn', function(e){
            e.preventDefault();
            var btn = $(this), targetId = btn.data('target'), previewId = btn.data('preview');
            var frame = wp.media({ title: btn.data('title') || 'Wybierz zdjęcie',
                button: { text: 'Użyj tego zdjęcia' }, multiple: false, library: { type: 'image' } });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
                $('#' + targetId).val(att.id);
                $('#' + previewId).html('<img src="' + url + '" class="km-photo-preview" alt="">');
                btn.text('🔄 Zmień zdjęcie');
                if (!btn.next('.kaplani-remove-btn').length)
                    btn.after('<button type="button" class="button kaplani-remove-btn" data-target="' + targetId + '" data-preview="' + previewId + '">Usuń</button>');
            });
            frame.open();
        });

        $(document).on('click', '.kaplani-remove-btn', function(e){
            e.preventDefault();
            var btn = $(this), targetId = btn.data('target'), previewId = btn.data('preview');
            $('#' + targetId).val('');
            $('#' + previewId).html('<div class="km-photo-placeholder">Brak zdjęcia</div>');
            btn.prev('.kaplani-upload-btn').text('📷 Dodaj zdjęcie');
            btn.remove();
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
    jQuery(function($){
        function init_sortable(listId, hiddenId) {
            var el = document.getElementById(listId);
            if (!el) return;
            Sortable.create(el, {
                handle: '.km-drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function() {
                    serialize_list(listId, hiddenId);
                }
            });
        }
        init_sortable('km-prob-list', 'kaplani_lista_proboszczow');
        init_sortable('km-wik-list',  'kaplani_lista_wikariuszy');

        $('.km-add-row').on('click.sortable', function() {
            var list = $(this).data('list');
            setTimeout(function() {
                init_sortable('km-' + list + '-list', 'kaplani_lista_' + (list === 'prob' ? 'proboszczow' : 'wikariuszy'));
            }, 50);
        });
    });
    </script>
    <?php
}

function parafia_kaplani_save_meta( $post_id ) {
    if ( ! isset( $_POST['parafia_kaplani_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['parafia_kaplani_nonce'], 'parafia_kaplani_nonce_action' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $text_fields = [ 'kaplani_prob_imie', 'kaplani_prob_od', 'kaplani_wik_imie', 'kaplani_wik_od', 'kaplani_rez_imie', 'kaplani_rez_od' ];
    foreach ( $text_fields as $f ) {
        if ( isset( $_POST[ $f ] ) )
            update_post_meta( $post_id, '_' . $f, sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) );
    }

    foreach ( [ 'kaplani_foto_proboszcz_id', 'kaplani_foto_wikariusz_id', 'kaplani_foto_rezydent_id' ] as $f ) {
        if ( isset( $_POST[ $f ] ) ) {
            $val = absint( $_POST[ $f ] );
            $val ? update_post_meta( $post_id, '_' . $f, $val ) : delete_post_meta( $post_id, '_' . $f );
        }
    }

    foreach ( [ 'kaplani_lista_proboszczow', 'kaplani_lista_wikariuszy' ] as $f ) {
        if ( isset( $_POST[ $f ] ) ) {
            $decoded = json_decode( wp_unslash( $_POST[ $f ] ), true );
            if ( is_array( $decoded ) ) {
                $clean = array_map( function( $row ) {
                    return [
                        'imie'  => sanitize_text_field( $row['imie'] ?? '' ),
                        'od'    => sanitize_text_field( $row['od']   ?? '' ),
                        'do'    => sanitize_text_field( $row['do']   ?? '' ),
                        'zmarl' => ! empty( $row['zmarl'] ),
                    ];
                }, $decoded );
                update_post_meta( $post_id, '_' . $f, json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
            }
        }
    }
}
add_action( 'save_post', 'parafia_kaplani_save_meta' );

function parafia_kaplani_enqueue_media( $hook ) {
    global $post;
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) return;
    if ( ! isset( $post ) || $post->post_type !== 'page' ) return;
    if ( get_post_meta( $post->ID, '_wp_page_template', true ) === 'page-kaplani.php' )
        wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'parafia_kaplani_enqueue_media' );


function parafia_kanc_register_meta_box() {
    add_meta_box(
        'parafia_kancelaria_dane',
        '🕐 Kancelaria parafialna — dane',
        'parafia_kanc_meta_box_html',
        'page', 'normal', 'high'
    );
}
add_action( 'add_meta_boxes', 'parafia_kanc_register_meta_box' );

function parafia_kanc_meta_box_html( $post ) {
    $template = get_post_meta( $post->ID, '_wp_page_template', true );
    if ( $template !== 'page-kancelaria.php' ) {
        echo '<p style="color:#888;font-style:italic;">Ten panel jest aktywny tylko gdy wybrany szablon to <strong>Kancelaria Parafialna</strong>.</p>';
        return;
    }
    wp_nonce_field( 'parafia_kanc_nonce_action', 'parafia_kanc_nonce' );

    $pid = $post->ID;

    $fields_kontakt = [
        'nazwa'   => [ 'label' => 'Pełna nazwa parafii',  'placeholder' => 'Parafia Rzymskokatolicka pw. …' ],
        'adres'   => [ 'label' => 'Adres',                 'placeholder' => 'ul. Św. Teresy 3, 05-320 Mrozy' ],
        'telefon' => [ 'label' => 'Telefon',               'placeholder' => '25 757 41 93' ],
        'email'   => [ 'label' => 'E-mail',                'placeholder' => 'parafiamrozy@gmail.com' ],
        'nip'     => [ 'label' => 'NIP',                   'placeholder' => '' ],
        'regon'   => [ 'label' => 'REGON',                 'placeholder' => '' ],
        'konto'   => [ 'label' => 'Numer konta bankowego', 'placeholder' => 'XX XXXX XXXX XXXX XXXX XXXX XXXX' ],
    ];


    ?>
    <style>
        .kanc-mb-tabs { display:flex; gap:0; border-bottom:2px solid #c9a84c; margin-bottom:20px; }
        .kanc-mb-tab  { padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:#f6f7f7; color:#50575e; border-radius:4px 4px 0 0; margin-right:4px; }
        .kanc-mb-tab.active { background:#c9a84c; color:#fff; }
        .kanc-mb-panel { display:none; }
        .kanc-mb-panel.active { display:block; }

        .kanc-field-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:4px; }
        .kanc-field-grid.cols-1 { grid-template-columns:1fr; }
        .kanc-field { display:flex; flex-direction:column; gap:4px; }
        .kanc-field label { font-size:12px; font-weight:600; color:#50575e; }
        .kanc-field input[type=text], .kanc-field input[type=email], .kanc-field textarea {
            width:100%; padding:6px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; font-family:inherit;
        }
        .kanc-field textarea { resize:vertical; min-height:60px; }

        .kanc-hours-mb { display:flex; flex-direction:column; gap:0; border:1px solid #ddd; border-radius:6px; overflow:hidden; margin-bottom:10px; }
        .kanc-hours-row { display:grid; grid-template-columns:22px 130px 80px 80px 1fr 36px; gap:6px; align-items:center; padding:7px 10px; border-bottom:1px solid #eee; background:#fff; }
        .kanc-hours-row:last-child { border-bottom:none; }
        .kanc-hours-row:nth-child(even) { background:#fafafa; }
        .kanc-hours-header { display:grid; grid-template-columns:22px 130px 80px 80px 1fr 36px; gap:6px; padding:5px 10px; background:#f0f0f0; border-bottom:1px solid #ddd; }
        .kanc-hours-header span { font-size:11px; font-weight:700; color:#50575e; text-transform:uppercase; }
        .kanc-hours-row input[type=text] { width:100%; padding:4px 7px; border:1px solid #ddd; border-radius:3px; font-size:12px; }
        .kanc-drag-handle { cursor:grab; color:#aaa; font-size:14px; text-align:center; user-select:none; }
        .kanc-drag-handle:active { cursor:grabbing; }
        .kanc-del-btn { background:none; border:none; cursor:pointer; color:#cc1818; font-size:15px; padding:0; line-height:1; }
        .kanc-hours-row.sortable-ghost { opacity:.4; background:#fff8e6 !important; }
        .kanc-hours-row.sortable-chosen { box-shadow:0 2px 10px rgba(201,168,76,.2); background:#fffdf5 !important; }

        .kanc-list-mb { display:flex; flex-direction:column; gap:0; border:1px solid #ddd; border-radius:6px; overflow:hidden; margin-bottom:10px; }
        .kanc-list-row { display:grid; gap:6px; align-items:center; padding:7px 10px; border-bottom:1px solid #eee; background:#fff; }
        .kanc-list-row:last-child { border-bottom:none; }
        .kanc-list-row:nth-child(even) { background:#fafafa; }
        .kanc-list-row--nabozenstwo { grid-template-columns:1fr 1fr 36px; }
        .kanc-list-row--sakrament   { grid-template-columns:160px 1fr 36px; }
        .kanc-list-row input[type=text], .kanc-list-row textarea { width:100%; padding:4px 7px; border:1px solid #ddd; border-radius:3px; font-size:12px; font-family:inherit; }
        .kanc-list-row textarea { resize:vertical; min-height:48px; }
        .kanc-section-label { font-size:13px; font-weight:600; color:#1d2327; margin:18px 0 8px; }
    </style>

    <!-- Ukryte pola JSON -->
    <input type="hidden" id="kanc_godziny"           name="kanc_godziny"           value="<?php echo esc_attr( $godziny_json ); ?>">

    <!-- Zakładki -->
    <div class="kanc-mb-tabs">
        <button type="button" class="kanc-mb-tab active" data-panel="kanc-panel-kontakt">📍 Kontakt</button>
        <button type="button" class="kanc-mb-tab" data-panel="kanc-panel-godziny">🕐 Godziny</button>
    </div>

    <!-- Panel: Kontakt -->
    <div class="kanc-mb-panel active" id="kanc-panel-kontakt">
        <div class="kanc-field-grid cols-1">
            <?php foreach ( $fields_kontakt as $key => $f ) : $val = get_post_meta( $pid, '_kanc_' . $key, true ); ?>
            <div class="kanc-field">
                <label for="kanc_<?php echo esc_attr($key); ?>"><?php echo esc_html( $f['label'] ); ?></label>
                <input type="text" id="kanc_<?php echo esc_attr($key); ?>"
                       name="kanc_<?php echo esc_attr($key); ?>"
                       value="<?php echo esc_attr( $val ); ?>"
                       placeholder="<?php echo esc_attr( $f['placeholder'] ); ?>">
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Panel: Godziny -->
    <div class="kanc-mb-panel" id="kanc-panel-godziny">
        <div class="kanc-hours-header">
            <span></span><span>Dzień</span><span>Od</span><span>Do</span><span>Uwaga (opcja)</span><span></span>
        </div>
        <div class="kanc-hours-mb" id="kanc-hours-list">
            <?php foreach ( $godziny as $g ) : ?>
            <div class="kanc-hours-row">
                <span class="kanc-drag-handle" title="Przeciągnij">⠿</span>
                <input type="text" class="kanc-h-dzien" value="<?php echo esc_attr( $g['dzien'] ); ?>" placeholder="Poniedziałek">
                <input type="text" class="kanc-h-od"    value="<?php echo esc_attr( $g['od'] ); ?>"   placeholder="8:00">
                <input type="text" class="kanc-h-do"    value="<?php echo esc_attr( $g['do'] ); ?>"   placeholder="17:00">
                <input type="text" class="kanc-h-uwaga" value="<?php echo esc_attr( $g['uwaga'] ); ?>" placeholder="np. tylko w wakacje">
                <button type="button" class="kanc-del-btn kanc-del-hour" title="Usuń">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button" id="kanc-add-hour">+ Dodaj dzień</button>

        <p class="kanc-section-label" style="margin-top:22px;">Komunikat o wyjątkach</p>
        <div class="kanc-field">
            <label for="kanc_uwaga_global">Uwaga wyświetlana pod tabelą (np. zamknięcia)</label>
            <input type="text" id="kanc_uwaga_global" name="kanc_uwaga_global"
                   value="<?php echo esc_attr( $uwaga_global ); ?>"
                   placeholder="W Pierwszy Piątek Miesiąca kancelaria nieczynna">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
    jQuery(function($){

        $('.kanc-mb-tab').on('click', function(){
            $('.kanc-mb-tab').removeClass('active');
            $('.kanc-mb-panel').removeClass('active');
            $(this).addClass('active');
            $('#' + $(this).data('panel')).addClass('active');
        });

        function serialize_hours() {
            var data = [];
            $('#kanc-hours-list .kanc-hours-row').each(function(){
                data.push({
                    dzien: $(this).find('.kanc-h-dzien').val(),
                    od:    $(this).find('.kanc-h-od').val(),
                    do:    $(this).find('.kanc-h-do').val(),
                    uwaga: $(this).find('.kanc-h-uwaga').val()
                });
            });
            $('#kanc_godziny').val(JSON.stringify(data));
        }

);


        $(document).on('click', '.kanc-del-hour', function(){ $(this).closest('.kanc-hours-row').remove(); serialize_hours(); });
    });
    </script>
    <?php
}

function parafia_kanc_save_meta( $post_id ) {
    if ( ! isset( $_POST['parafia_kanc_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['parafia_kanc_nonce'], 'parafia_kanc_nonce_action' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $text_fields = [ 'nazwa','adres','telefon','email','nip','regon','konto',
                     'msze_powszednie','msze_niedziela','spowiedz','rozaniec','uwaga_global' ];
    foreach ( $text_fields as $f ) {
        if ( isset( $_POST[ 'kanc_' . $f ] ) )
            update_post_meta( $post_id, '_kanc_' . $f, sanitize_text_field( wp_unslash( $_POST[ 'kanc_' . $f ] ) ) );
    }

    if ( isset( $_POST['kanc_godziny'] ) ) {
        $dec = json_decode( wp_unslash( $_POST['kanc_godziny'] ), true );
        if ( is_array( $dec ) ) {
            $clean = array_map( fn($r) => [
                'dzien' => sanitize_text_field( $r['dzien'] ?? '' ),
                'od'    => sanitize_text_field( $r['od']    ?? '' ),
                'do'    => sanitize_text_field( $r['do']    ?? '' ),
                'uwaga' => sanitize_text_field( $r['uwaga'] ?? '' ),
            ], $dec );
            update_post_meta( $post_id, '_kanc_godziny', json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
        }
    }

}
add_action( 'save_post', 'parafia_kanc_save_meta' );


function parafia_msze_register_meta_box() {
    add_meta_box( 'parafia_msze_dane', '✝ Msze Święte — dane', 'parafia_msze_meta_box_html', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'parafia_msze_register_meta_box' );

function parafia_msze_meta_box_html( $post ) {
    $template = get_post_meta( $post->ID, '_wp_page_template', true );
    if ( $template !== 'page-msze.php' ) {
        echo '<p style="color:#888;font-style:italic;">Ten panel jest aktywny tylko gdy wybrany szablon to <strong>Msze Święte</strong>.</p>';
        return;
    }
    wp_nonce_field( 'parafia_msze_nonce_action', 'parafia_msze_nonce' );
    $pid = $post->ID;

    $default_pow = json_encode([ ['godz'=>'07:00','uwaga'=>''], ['godz'=>'18:00','uwaga'=>''] ], JSON_UNESCAPED_UNICODE);
    $default_nd  = json_encode([ ['godz'=>'07:00','uwaga'=>''], ['godz'=>'08:30','uwaga'=>''], ['godz'=>'10:00','uwaga'=>'Dla dzieci'], ['godz'=>'12:00','uwaga'=>''], ['godz'=>'18:00','uwaga'=>''] ], JSON_UNESCAPED_UNICODE);
    $default_sp  = json_encode([ ['kiedy'=>'Dni powszednie','uwaga'=>'Przed każdą Mszą Świętą'], ['kiedy'=>'Niedziela','uwaga'=>'Przed każdą Mszą Świętą'], ['kiedy'=>'Pierwszy Piątek Miesiąca','uwaga'=>'W godzinach porannych'] ], JSON_UNESCAPED_UNICODE);
    $default_nab = json_encode([
        [ 'nazwa' => 'Różaniec',                           'kiedy' => 'Codziennie przed wieczorną Mszą Świętą', 'okres' => '' ],
        [ 'nazwa' => 'Droga Krzyżowa',                     'kiedy' => 'Piątki Wielkiego Postu o godz. 17:30',   'okres' => 'Wielki Post' ],
        [ 'nazwa' => 'Gorzkie Żale',                       'kiedy' => 'Niedziele Wielkiego Postu o godz. 17:30','okres' => 'Wielki Post' ],
        [ 'nazwa' => 'Nabożeństwo majowe',                 'kiedy' => 'Codziennie o godz. 18:00',               'okres' => 'Maj' ],
        [ 'nazwa' => 'Koronka do Miłosierdzia Bożego',     'kiedy' => 'Codziennie o godz. 15:00',               'okres' => '' ],
        [ 'nazwa' => 'Nabożeństwo czerwcowe',               'kiedy' => 'Codziennie o godz. 18:00',               'okres' => 'Czerwiec' ],
        [ 'nazwa' => 'Nabożeństwo różańcowe październikowe','kiedy' => 'Codziennie przed wieczorną Mszą Świętą', 'okres' => 'Październik' ],
    ], JSON_UNESCAPED_UNICODE);

    $pow_json = get_post_meta( $pid, '_msze_powszednie',  true ) ?: $default_pow;
    $nd_json  = get_post_meta( $pid, '_msze_niedzielne',  true ) ?: $default_nd;
    $sp_json  = get_post_meta( $pid, '_msze_spowiedz',    true ) ?: $default_sp;
    $nab_json = get_post_meta( $pid, '_msze_nabozenstwa', true ) ?: $default_nab;
    $uwaga    = get_post_meta( $pid, '_msze_uwaga',        true ) ?: '';

    $pow = json_decode( $pow_json, true ) ?: [];
    $nd  = json_decode( $nd_json,  true ) ?: [];
    $sp  = json_decode( $sp_json,  true ) ?: [];
    $nab = json_decode( $nab_json, true ) ?: [];
    ?>
    <style>
        .mm-tabs { display:flex; gap:0; border-bottom:2px solid #c9a84c; margin-bottom:20px; }
        .mm-tab  { padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:#f6f7f7; color:#50575e; border-radius:4px 4px 0 0; margin-right:4px; }
        .mm-tab.active { background:#c9a84c; color:#fff; }
        .mm-panel { display:none; }
        .mm-panel.active { display:block; }
        .mm-list { display:flex; flex-direction:column; gap:0; border:1px solid #ddd; border-radius:6px; overflow:hidden; margin-bottom:10px; }
        .mm-row  { display:grid; gap:8px; align-items:center; padding:8px 10px; border-bottom:1px solid #eee; background:#fff; }
        .mm-row:last-child { border-bottom:none; }
        .mm-row:nth-child(even) { background:#fafafa; }
        .mm-row--msza    { grid-template-columns:22px 90px 1fr 36px; }
        .mm-row--spow    { grid-template-columns:22px 1fr 1fr 36px; }
        .mm-row input[type=text] { width:100%; padding:5px 8px; border:1px solid #ddd; border-radius:3px; font-size:12px; }
        .mm-header { display:grid; gap:8px; padding:5px 10px; background:#f0f0f0; border-bottom:1px solid #ddd; font-size:11px; font-weight:700; color:#50575e; text-transform:uppercase; }
        .mm-header--msza { grid-template-columns:22px 90px 1fr 36px; }
        .mm-header--spow { grid-template-columns:22px 1fr 1fr 36px; }
        .mm-drag { cursor:grab; color:#aaa; font-size:14px; text-align:center; user-select:none; }
        .mm-drag:active { cursor:grabbing; }
        .mm-del  { background:none; border:none; cursor:pointer; color:#cc1818; font-size:15px; padding:0; line-height:1; }
        .mm-row.sortable-ghost  { opacity:.4; background:#fff8e6 !important; }
        .mm-row.sortable-chosen { box-shadow:0 2px 10px rgba(201,168,76,.2); background:#fffdf5 !important; }
        .mm-field { display:flex; flex-direction:column; gap:4px; margin-bottom:12px; }
        .mm-field label { font-size:12px; font-weight:600; color:#50575e; }
        .mm-field input { padding:6px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; }
    </style>

    <input type="hidden" id="msze_powszednie"  name="msze_powszednie"  value="<?php echo esc_attr($pow_json); ?>">
    <input type="hidden" id="msze_nabozenstwa" name="msze_nabozenstwa" value="<?php echo esc_attr($nab_json); ?>">
    <input type="hidden" id="msze_niedzielne" name="msze_niedzielne" value="<?php echo esc_attr($nd_json); ?>">
    <input type="hidden" id="msze_spowiedz"   name="msze_spowiedz"   value="<?php echo esc_attr($sp_json); ?>">

    <div class="mm-tabs">
        <button type="button" class="mm-tab active" data-panel="mm-pow">📅 Dni powszednie</button>
        <button type="button" class="mm-tab" data-panel="mm-nd">☀️ Niedziela</button>
        <button type="button" class="mm-tab" data-panel="mm-sp">🙏 Spowiedź</button>
        <button type="button" class="mm-tab" data-panel="mm-nab">🕯️ Nabożeństwa</button>
    </div>

    <!-- Powszednie -->
    <div class="mm-panel active" id="mm-pow">
        <div class="mm-header mm-header--msza"><span></span><span>Godzina</span><span>Uwaga (opcja)</span><span></span></div>
        <div class="mm-list" id="mm-list-pow">
            <?php foreach ($pow as $m): ?>
            <div class="mm-row mm-row--msza">
                <span class="mm-drag">⠿</span>
                <input type="text" class="mm-godz"  value="<?php echo esc_attr($m['godz']); ?>"  placeholder="07:00">
                <input type="text" class="mm-uwaga" value="<?php echo esc_attr($m['uwaga']); ?>" placeholder="np. W intencji…">
                <button type="button" class="mm-del">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button mm-add" data-list="pow">+ Dodaj Mszę</button>
    </div>

    <!-- Niedzielne -->
    <div class="mm-panel" id="mm-nd">
        <div class="mm-header mm-header--msza"><span></span><span>Godzina</span><span>Uwaga (opcja)</span><span></span></div>
        <div class="mm-list" id="mm-list-nd">
            <?php foreach ($nd as $m): ?>
            <div class="mm-row mm-row--msza">
                <span class="mm-drag">⠿</span>
                <input type="text" class="mm-godz"  value="<?php echo esc_attr($m['godz']); ?>"  placeholder="07:00">
                <input type="text" class="mm-uwaga" value="<?php echo esc_attr($m['uwaga']); ?>" placeholder="np. Dla dzieci">
                <button type="button" class="mm-del">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button mm-add" data-list="nd">+ Dodaj Mszę</button>
    </div>

    <!-- Spowiedź -->
    <div class="mm-panel" id="mm-sp">
        <div class="mm-header mm-header--spow"><span></span><span>Kiedy</span><span>Opis</span><span></span></div>
        <div class="mm-list" id="mm-list-sp">
            <?php foreach ($sp as $s): ?>
            <div class="mm-row mm-row--spow">
                <span class="mm-drag">⠿</span>
                <input type="text" class="mm-kiedy" value="<?php echo esc_attr($s['kiedy']); ?>" placeholder="np. Niedziela">
                <input type="text" class="mm-opiss" value="<?php echo esc_attr($s['uwaga']); ?>" placeholder="np. Przed każdą Mszą">
                <button type="button" class="mm-del">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button mm-add" data-list="sp">+ Dodaj termin</button>
    </div>

    <!-- Panel: Nabożeństwa -->
    <div class="mm-panel" id="mm-nab">
        <p style="font-size:12px;color:#666;margin-bottom:12px;">Nabożeństwa są grupowane wg okresu liturgicznego. Zostaw pole <strong>Okres</strong> puste jeśli nabożeństwo trwa przez cały rok.</p>
        <div style="display:grid;grid-template-columns:22px 1fr 1fr 100px 36px;gap:6px;padding:5px 10px;background:#f0f0f0;border:1px solid #ddd;border-radius:6px 6px 0 0;border-bottom:none;">
            <span style="font-size:11px;font-weight:700;color:#50575e;text-transform:uppercase;"></span>
            <span style="font-size:11px;font-weight:700;color:#50575e;text-transform:uppercase;">Nazwa nabożeństwa</span>
            <span style="font-size:11px;font-weight:700;color:#50575e;text-transform:uppercase;">Kiedy</span>
            <span style="font-size:11px;font-weight:700;color:#50575e;text-transform:uppercase;">Okres (opcja)</span>
            <span></span>
        </div>
        <div class="mm-list" id="mm-list-nab" style="border-radius:0 0 6px 6px;">
            <?php foreach ($nab as $n) : ?>
            <div class="mm-row" style="display:grid;grid-template-columns:22px 1fr 1fr 100px 36px;gap:6px;align-items:center;padding:8px 10px;border-bottom:1px solid #eee;background:#fff;">
                <span class="mm-drag">⠿</span>
                <input type="text" class="mm-nab-nazwa" value="<?php echo esc_attr($n['nazwa']); ?>" placeholder="np. Różaniec">
                <input type="text" class="mm-nab-kiedy" value="<?php echo esc_attr($n['kiedy']); ?>" placeholder="np. Codziennie o 17:30">
                <input type="text" class="mm-nab-okres" value="<?php echo esc_attr($n['okres']); ?>" placeholder="np. Maj">
                <button type="button" class="mm-del">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button mm-add" data-list="nab" style="margin-top:8px">+ Dodaj nabożeństwo</button>

        <p style="margin-top:20px;font-size:13px;font-weight:600;color:#1d2327;">Uwaga globalna (wyświetlana pod całą stroną)</p>
        <div class="mm-field">
            <label for="msze_uwaga">Komunikat (opcjonalny)</label>
            <input type="text" id="msze_uwaga" name="msze_uwaga"
                   value="<?php echo esc_attr($uwaga); ?>"
                   placeholder="np. W Pierwszy Piątek Miesiąca spowiedź od 6:30">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
    jQuery(function($){
        $('.mm-tab').on('click', function(){
            $('.mm-tab').removeClass('active'); $('.mm-panel').removeClass('active');
            $(this).addClass('active'); $('#' + $(this).data('panel')).addClass('active');
        });

        function serialize(listId, hiddenId, type) {
            var data = [];
            $('#mm-list-' + listId + ' .mm-row').each(function(){
                if (type === 'msza')
                    data.push({ godz: $(this).find('.mm-godz').val(), uwaga: $(this).find('.mm-uwaga').val() });
                else
                    data.push({ kiedy: $(this).find('.mm-kiedy').val(), uwaga: $(this).find('.mm-opiss').val() });
            });
            $('#' + hiddenId).val(JSON.stringify(data));
        }

        function serializeAll() {
            serialize('pow', 'msze_powszednie', 'msza');
            serialize('nd',  'msze_niedzielne', 'msza');
            serialize('sp',  'msze_spowiedz',   'spow');
            serialize_nab();
        }

        function serialize_nab() {
            var data = [];
            $('#mm-list-nab .mm-row').each(function(){
                data.push({
                    nazwa: $(this).find('.mm-nab-nazwa').val(),
                    kiedy: $(this).find('.mm-nab-kiedy').val(),
                    okres: $(this).find('.mm-nab-okres').val()
                });
            });
            $('#msze_nabozenstwa').val(JSON.stringify(data));
        }

        $('form#post, #post').on('submit', serializeAll);
        $(document).on('click', '.editor-post-publish-button, .editor-post-save-draft, #publish, #save-action input[type=submit], button.is-primary[type="button"]', serializeAll);
        $('#mm-list-pow, #mm-list-nd').on('input change', 'input', serializeAll);
        $('#mm-list-sp').on('input change', 'input', serializeAll);

        $('#mm-list-nab').on('input change', 'input', serialize_nab);

        ['pow','nd','sp'].forEach(function(id){
            Sortable.create(document.getElementById('mm-list-' + id), {
                handle:'.mm-drag', animation:150,
                ghostClass:'sortable-ghost', chosenClass:'sortable-chosen',
                onEnd: serializeAll
            });
        });

        Sortable.create(document.getElementById('mm-list-nab'), {
            handle:'.mm-drag', animation:150,
            ghostClass:'sortable-ghost', chosenClass:'sortable-chosen',
            onEnd: serialize_nab
        });

        $(document).on('click', '.mm-add', function(){
            var list = $(this).data('list');
            var isMsza = (list === 'pow' || list === 'nd');
            var isNab  = (list === 'nab');
            var row = isMsza
                ? '<div class="mm-row mm-row--msza"><span class="mm-drag">⠿</span><input type="text" class="mm-godz" placeholder="07:00"><input type="text" class="mm-uwaga" placeholder="Uwaga"><button type="button" class="mm-del">✕</button></div>'
                : isNab
                ? '<div class="mm-row" style="display:grid;grid-template-columns:22px 1fr 1fr 100px 36px;gap:6px;align-items:center;padding:8px 10px;border-bottom:1px solid #eee;background:#fff;"><span class="mm-drag">⠿</span><input type="text" class="mm-nab-nazwa" placeholder="Nazwa"><input type="text" class="mm-nab-kiedy" placeholder="Kiedy"><input type="text" class="mm-nab-okres" placeholder="Okres (opcja)"><button type="button" class="mm-del">✕</button></div>'
                : '<div class="mm-row mm-row--spow"><span class="mm-drag">⠿</span><input type="text" class="mm-kiedy" placeholder="Kiedy"><input type="text" class="mm-opiss" placeholder="Opis"><button type="button" class="mm-del">✕</button></div>';
            $('#mm-list-' + list).append(row);
            serializeAll();
        });

        $(document).on('click', '.mm-del', function(){
            if (confirm('Usunąć ten wiersz?')) { $(this).closest('.mm-row').remove(); serializeAll(); }
        });
    });
    </script>
    <?php
}

function parafia_msze_save_meta( $post_id ) {
    if ( ! isset( $_POST['parafia_msze_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['parafia_msze_nonce'], 'parafia_msze_nonce_action' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['msze_uwaga'] ) )
        update_post_meta( $post_id, '_msze_uwaga', sanitize_text_field( wp_unslash( $_POST['msze_uwaga'] ) ) );

    foreach ( [ 'msze_powszednie' => 'msza', 'msze_niedzielne' => 'msza', 'msze_spowiedz' => 'spow', 'msze_nabozenstwa' => 'nab' ] as $field => $type ) {
        if ( ! isset( $_POST[ $field ] ) ) continue;
        $dec = json_decode( wp_unslash( $_POST[ $field ] ), true );
        if ( ! is_array( $dec ) ) continue;
        $clean = array_map( function($r) use ($type) {
            if ( $type === 'msza' ) return [ 'godz' => sanitize_text_field($r['godz']??''), 'uwaga' => sanitize_text_field($r['uwaga']??'') ];
            if ( $type === 'nab'  ) return [ 'nazwa' => sanitize_text_field($r['nazwa']??''), 'kiedy' => sanitize_text_field($r['kiedy']??''), 'okres' => sanitize_text_field($r['okres']??'') ];
            return [ 'kiedy' => sanitize_text_field($r['kiedy']??''), 'uwaga' => sanitize_text_field($r['uwaga']??'') ];
        }, $dec );
        update_post_meta( $post_id, '_' . $field, json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
    }
}
add_action( 'save_post', 'parafia_msze_save_meta' );


/**
 * Naprawia historycznie uszkodzone treści sakramentów:
 *  - literalne sekwencje unicode zapisane bez backslasha (np. "u015b" -> "ś"),
 *  - osierocone końce linii zapisane jako "rn" (z \r\n) lub "n" (z \n)
 *    na granicy elementów listy.
 * Bezpieczne: zamiana następuje tylko gdy po "rn"/"n" jest WIELKA litera, więc
 * prawdziwe słowa ("czarny", "ziarno", "wierny", "wnętrze") pozostają nietknięte.
 */
function parafia_sakr_napraw_tekst( $tekst ) {
    if ( ! is_string( $tekst ) || $tekst === '' ) {
        return $tekst;
    }
    // 1. "uXXXX" (dokładnie 4 znaki hex) -> odpowiedni znak Unicode.
    $tekst = preg_replace_callback(
        '/u([0-9a-fA-F]{4})/',
        function ( $m ) {
            $cp = hexdec( $m[1] );
            $znak = mb_convert_encoding( pack( 'N', $cp ), 'UTF-8', 'UTF-32BE' );
            return $znak !== false ? $znak : $m[0];
        },
        $tekst
    );
    // 2. Prawdziwe CRLF/CR -> LF (na wypadek mieszanych końców linii).
    $tekst = str_replace( [ "\r\n", "\r" ], "\n", $tekst );
    // 3. Osierocone "rn" (z \r\n): po ) . : lub literze, przed wielką literą.
    $tekst = preg_replace( '/(?<=[\)\.\:\p{L}])rn(?=\p{Lu})/u', "\n", $tekst );
    // 4. Osierocone pojedyncze "n" (z \n): po ) . : lub MAŁEJ literze, przed wielką literą.
    //    Mała litera w warunku wstecznym chroni przed rozbijaniem słów (po wielkiej litery
    //    nigdy nie ma końca elementu w tych treściach).
    $tekst = preg_replace( '/(?<=[\)\.\:\p{Ll}])n(?=\p{Lu})/u', "\n", $tekst );
    return $tekst;
}

/**
 * Odczytuje listę sakramentów z bazy, dekoduje i naprawia uszkodzone treści.
 * Zwraca tablicę pozycji z kompletem kluczy. Pusta/uszkodzona -> [].
 */
function parafia_sakr_pobierz_liste( $post_id ) {
    $raw = get_post_meta( $post_id, '_sakr_lista', true );
    $lista = $raw ? json_decode( $raw, true ) : [];
    if ( ! is_array( $lista ) ) {
        $lista = [];
    }
    $pola_tekstowe = [ 'opis', 'terminy', 'zgloszenie', 'dokumenty', 'wymagania', 'dodatkowe' ];
    foreach ( $lista as &$s ) {
        if ( ! is_array( $s ) ) { $s = []; continue; }
        if ( isset( $s['nazwa'] ) ) {
            $s['nazwa'] = parafia_sakr_napraw_tekst( $s['nazwa'] );
        }
        foreach ( $pola_tekstowe as $p ) {
            if ( isset( $s[ $p ] ) ) {
                $s[ $p ] = parafia_sakr_napraw_tekst( $s[ $p ] );
            }
        }
    }
    unset( $s );
    return $lista;
}

function parafia_sakr_register_meta_box() {
    add_meta_box( 'parafia_sakr_dane', '💒 Sakramenty — treści', 'parafia_sakr_meta_box_html', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'parafia_sakr_register_meta_box' );

function parafia_sakr_meta_box_html( $post ) {
    if ( ! parafia_mrozy_strona_ma_szablon( $post, 'page-sakramenty.php' ) ) {
        echo '<p style="color:#888;font-style:italic;">Ten panel jest aktywny tylko dla strony korzystającej z szablonu <strong>Sakramenty</strong> (wybierz go w prawym panelu „Szablon”, lub nadaj stronie adres / slug „sakramenty”).</p>';
        return;
    }
    wp_nonce_field( 'parafia_sakr_nonce_action', 'parafia_sakr_nonce' );
    $pid = $post->ID;

    $puste = [ 'opis'=>'', 'terminy'=>'', 'zgloszenie'=>'', 'dokumenty'=>'', 'wymagania'=>'', 'dodatkowe'=>'' ];
    $default = json_encode([
        array_merge([ 'id'=>'chrzest',     'nazwa'=>'Chrzest Święty',          'ikona'=>'chrzest'     ], $puste),
        array_merge([ 'id'=>'komunia',     'nazwa'=>'Pierwsza Komunia Święta', 'ikona'=>'komunia'     ], $puste),
        array_merge([ 'id'=>'bierzmowanie','nazwa'=>'Bierzmowanie',            'ikona'=>'bierzmowanie'], $puste),
        array_merge([ 'id'=>'malzenstwo',  'nazwa'=>'Sakrament Małżeństwa',    'ikona'=>'malzenstwo'  ], $puste),
        array_merge([ 'id'=>'chorzy',      'nazwa'=>'Namaszczenie Chorych',    'ikona'=>'chorzy'      ], $puste),
        array_merge([ 'id'=>'pogrzeb',     'nazwa'=>'Pogrzeb Katolicki',       'ikona'=>'pogrzeb'     ], $puste),
    ], JSON_UNESCAPED_UNICODE);

    // Oryginalna treść sakramentów (z dokumentu parafii) — używana przez przycisk
    // „Przywróć oryginalną treść”. Łamania linii (\n) są tu kontrolowane i pewne.
    $tresc_domyslna = [
        [
            'id' => "chrzest",
            'nazwa' => "Chrzest Święty",
            'ikona' => "chrzest",
            'opis' => "Chrzest święty jest pierwszym sakramentem, jaki przyjmujemy w swoim życiu. Jest on bramą do przyjęcia kolejnych sakramentów. Dzięki jego przyjęciu stajemy się członkami wspólnoty Kościoła Katolickiego, a nade wszystko otrzymujemy godność dzieci Bożych.",
            'terminy' => "Sakrament chrztu świętego udzielany jest w naszej parafii w każdą niedzielę podczas Mszy świętej o godzinie 12.00. W wyjątkowych sytuacjach istnieje możliwość zmiany dnia i godziny chrztu, jednak wymaga to indywidualnego porozumienia z księdzem w kancelarii.",
            'zgloszenie' => "Zgłoszenie dziecka do chrztu powinno nastąpić w kancelarii parafialnej około dwóch tygodni przed planowanym udzieleniem sakramentu. Zgłosić do chrztu może jeden z rodziców dziecka.",
            'dokumenty' => "Dowód osobisty rodzica zgłaszającego\nAkt urodzenia dziecka (z Urzędu Stanu Cywilnego)\nZgoda proboszcza parafii zamieszkania na chrzest poza własną parafią (jeśli rodzice mieszkają poza naszą parafią)\nDane rodziców chrzestnych: imię, nazwisko, wiek, adres",
            'wymagania' => "Rodzice mają obowiązek zapewnić dziecku pełne warunki wychowania katolickiego.\nGdy rodzice nie są w sakramentalnym związku małżeńskim, a nie mają przeszkód do zawarcia ślubu — zalecane jest odłożenie chrztu do czasu zawarcia małżeństwa.\nW związkach, w których istnieją przeszkody do zawarcia katolickiego małżeństwa, rodzice składają pisemne zobowiązanie o katolickim wychowaniu dziecka.\nChrzestny powinien być praktykującym katolikiem, który przyjął chrzest, bierzmowanie i Eucharystię.\nChrzestny prowadzi życie zgodne z wiarą — nie może żyć w związku niesakramentalnym.\nChrzestny przedstawia zaświadczenie z parafii miejsca zamieszkania, że może pełnić tę funkcję.\nOsoba ochrzczona z niekatolickiej wspólnoty chrześcijańskiej może pełnić jedynie funkcję świadka chrztu obok chrzestnego katolika.\nDziecko powinno mieć jednego lub parę rodziców chrzestnych, którzy będą wspierać rodziców w wychowaniu dziecka w wierze.",
            'dodatkowe' => "Rzeczy potrzebne do chrztu:\n\n• BIAŁA SZATA – ozdobna biała chusta, którą dziecko otrzymuje bezpośrednio po chrzcie. Symbolizuje, że po chrzcie dziecko stało się nowym stworzeniem, oczyszczonym z grzechu, i przyoblekło się w Chrystusa. Na szacie może być wypisane imię dziecka oraz data chrztu.\n\n• ŚWIECA CHRZCIELNA – zapalana po chrzcie na znak, że ochrzczony stał się dzieckiem światłości.",
        ],
        [
            'id' => "komunia",
            'nazwa' => "Pierwsza Komunia Święta",
            'ikona' => "komunia",
            'opis' => "Pierwsza Komunia Święta to pierwsze pełne uczestnictwo dziecka w Eucharystii poprzez przyjęcie Ciała Pańskiego. Informacje o godzinach Mszy świętych w niedziele i dni powszednie znajdują się na podstronie „Msze święte i nabożeństwa”.",
            'terminy' => "Data I Komunii Świętej ogłaszana jest na jednym z pierwszych spotkań organizacyjnych dla rodziców dzieci chętnych do przystąpienia do tego sakramentu.",
            'zgloszenie' => "Na początku każdego roku szkolnego ogłaszany jest nowy nabór kandydatów do przyjęcia I Komunii Świętej. Przyjmowani są uczniowie klas 3 szkoły podstawowej oraz starsi, którzy jeszcze nie przyjęli tego sakramentu.",
            'dokumenty' => "Akt chrztu dziecka (dotyczy dzieci ochrzczonych w innej parafii niż św. Teresy od Dzieciątka Jezus w Mrozach)\nZgoda na I Komunię poza parafią (dotyczy rodzin zamieszkujących teren innej parafii)",
            'wymagania' => "Dzieci przygotowujące się do I Komunii są zobowiązane uczestniczyć w każdą niedzielę we Mszy świętej o godzinie 10.00.\nW drugą niedzielę miesiąca wszystkie przygotowujące się dzieci wraz z rodzicami zaproszone są na spotkanie po Mszy świętej.\nRodzice uczestniczą w spotkaniach formacyjnych raz w miesiącu, według porządku podanego w ogłoszeniach.",
            'dodatkowe' => "",
        ],
        [
            'id' => "bierzmowanie",
            'nazwa' => "Bierzmowanie",
            'ikona' => "bierzmowanie",
            'opis' => "Bierzmowanie jest ostatnim z trzech — zaraz po Chrzcie i Eucharystii — sakramentem wtajemniczenia chrześcijańskiego. Jego przyjęcie jest konieczne, aby dopełnić łaski chrztu świętego. Zwyczajnym szafarzem tego sakramentu jest biskup. Poprzez udzielenie darów Ducha Świętego sakrament ten uzdalnia osobę, która go przyjmuje, do mężnego wyznawania wiary i postępowania według jej zasad (por. Obrzędy bierzmowania).",
            'terminy' => "Data udzielenia sakramentu ogłaszana jest pod koniec okresu przygotowania do bierzmowania. Zwykle jest to jesień każdego roku.",
            'zgloszenie' => "Na początku każdego roku szkolnego ogłaszany jest nowy nabór kandydatów. Przyjmowani są uczniowie klas 8 szkoły podstawowej oraz młodzież starsza. Osoby dorosłe, które w odpowiednim czasie nie przyjęły tego sakramentu, a chciałyby się do niego przygotować, zapraszamy do kancelarii parafialnej w celu ustalenia przygotowania.",
            'dokumenty' => "Akt chrztu (dotyczy osób ochrzczonych w innej parafii niż św. Teresy od Dzieciątka Jezus w Mrozach)\nZgoda na bierzmowanie poza parafią (dotyczy osób zamieszkujących teren innej parafii)\nDeklaracja przystąpienia do przygotowania, składana w wyznaczonym terminie",
            'wymagania' => "Świadkiem bierzmowania może być osoba, która sama już przyjęła ten sakrament.\nDo zadań świadka należy umacnianie w wierze osoby, której jest świadkiem.\nSpotkania formacyjne odbywają się w wyznaczonych grupach, według ustalonego na początku przygotowania porządku.",
            'dodatkowe' => "",
        ],
        [
            'id' => "malzenstwo",
            'nazwa' => "Sakrament Małżeństwa",
            'ikona' => "malzenstwo",
            'opis' => "Kościół naucza, że podstawowymi celami małżeństwa jest wzajemne uświęcenie małżonków oraz zrodzenie i wychowanie potomstwa (por. KKK 1660). Sakrament ten jest widocznym znakiem związku łączącego Jezusa Chrystusa z Jego Kościołem — Oblubieńca i Oblubienicy. Poprzez małżeństwo Bóg udoskonala miłość małżonków, umacniając ich jedność oraz uświęcając wspólną drogę ku życiu wiecznemu (por. KKK 1661).",
            'terminy' => "Termin ustalany jest zawsze indywidualnie z narzeczonymi. Zwykle sakrament małżeństwa narzeczeni zawierają w sobotę. Istnieje możliwość zawarcia go w inne dni, jednak ze względu na pokutny charakter piątku nie zaleca się tego dnia (jest to możliwe, lecz wymaga odpowiedniej dyspensy). Planowany termin ślubu dobrze jest zgłosić do kancelarii rok wcześniej.",
            'zgloszenie' => "Narzeczeni swoje pragnienie zawarcia małżeństwa powinni zgłosić do kancelarii parafialnej na 3 miesiące przed planowaną datą ślubu. Od tego momentu rozpoczyna się formalne przygotowanie. Formalności przedślubne załatwia się w parafii, w której narzeczeni planują zawrzeć małżeństwo.",
            'dokumenty' => "Zaświadczenie o ukończeniu katechizacji przedmałżeńskiej (kursu przedmałżeńskiego)\nZgoda proboszcza parafii zamieszkania jednego z narzeczonych na załatwienie formalności poza macierzystą parafią (jeśli żadne z narzeczonych nie jest naszym parafianinem)\nŚwiadectwo chrztu obu stron (wystawione nie później niż 6 miesięcy wstecz)\nŚwiadectwo bierzmowania (jeśli na świadectwie chrztu nie ma o nim informacji)\nDowody osobiste\nAkt zgonu współmałżonka (dotyczy wdów i wdowców)\nWyrok sądu biskupiego stwierdzający nieważność poprzedniego małżeństwa (dla osób, które zawarły wcześniej nieważny związek)\nAkt zawarcia i akt rozwiązania małżeństwa cywilnego (dla osób, które zawarły wcześniej związek cywilny)\nZaświadczenie z USC o braku okoliczności wyłączających zawarcie małżeństwa — w dwóch egzemplarzach, sporządzone do 6 miesięcy przed ślubem (dla ślubu konkordatowego); jeśli związek cywilny zawarto wcześniej — akt ślubu cywilnego",
            'wymagania' => "Pierwszym etapem jest przygotowanie duchowe — wytrwała modlitwa o łaskę znalezienia dobrego współmałżonka.\nDokonuje się ono również przez trwanie w łasce sakramentalnej i regularne przystępowanie do sakramentów świętych.",
            'dodatkowe' => "Odpowiedzi na wszelkie nurtujące narzeczonych pytania można uzyskać podczas pierwszego spotkania z księdzem w kancelarii parafialnej. Podczas tej rozmowy ksiądz poinformuje narzeczonych, co krok po kroku należy zrobić i przygotować, aby małżeństwo mogło zostać godnie i ważnie zawarte.",
        ],
        [
            'id' => "chorzy",
            'nazwa' => "Namaszczenie Chorych",
            'ikona' => "chorzy",
            'opis' => "Namaszczenie chorych nie jest sakramentem przeznaczonym tylko dla tych, którzy znajdują się w ostatecznym niebezpieczeństwie utraty życia. Odpowiednia pora na jego przyjęcie jest już wówczas, gdy wiernym zaczyna grozić niebezpieczeństwo śmierci z powodu choroby lub starości (por. KKK 1514).",
            'terminy' => "",
            'zgloszenie' => "W sytuacjach nagłych prosimy o kontakt telefoniczny lub osobisty z konkretnym księdzem.",
            'dokumenty' => "",
            'wymagania' => "Jeśli chory, który już otrzymał namaszczenie, wyzdrowiał, a następnie nabył nową ciężką chorobę lub jego stan poważnie się pogorszył — może powtórnie przyjąć ten sakrament.\nWłaściwą praktyką jest poprosić kapłana o udzielenie sakramentu przed poważną operacją.\nPraktyka ta dotyczy również osób starszych, u których starość się pogłębiła (por. KKK 1515).",
            'dodatkowe' => "Osoby przyjmujące kapłana, który przybywa do domu chorego, by udzielić Komunii Świętej oraz innych sakramentów, proszone są o przygotowanie — w miarę możliwości — stołu nakrytego białym obrusem, świec oraz wody święconej wraz z kropidłem.",
        ],
        [
            'id' => "pogrzeb",
            'nazwa' => "Pogrzeb Katolicki",
            'ikona' => "pogrzeb",
            'opis' => "Pogrzeb katolicki jest uczynkiem miłosierdzia co do ciała. Wyraża naszą miłość do osoby zmarłej, której poprzez ten akt — jak zwykło się mawiać — chcemy oddać ostatnią posługę. Prawo do załatwienia formalności związanych z pochówkiem ma najbliższa rodzina zmarłego.",
            'terminy' => "Termin ustalany jest indywidualnie. Pogrzeby w naszej parafii mogą odbywać się od poniedziałku do soboty. Godzina pogrzebu również ustalana jest indywidualnie. Pogrzeby kończące się pochówkiem w Mrozach ustalane są na godzinę 11.00 oraz 13.00.",
            'zgloszenie' => "Zgłoszenie pogrzebu odbywa się w kancelarii parafialnej w godzinach jej urzędowania lub w innym uzgodnionym terminie.",
            'dokumenty' => "Zgoda proboszcza na pogrzeb poza parafią (nie dotyczy naszych parafian)\nAkt zgonu (do wglądu)\nZaświadczenie o udzieleniu sakramentu Namaszczenia Chorych (jeśli przed śmiercią zostały udzielone sakramenty)\nKarta zgonu dla administracji cmentarza, wydana przez szpital (pozostaje w kancelarii parafialnej)",
            'wymagania' => "",
            'dodatkowe' => "",
        ],
    ];

    $sakramenty = parafia_sakr_pobierz_liste( $pid );
    // Gdy brak danych lub pusta lista (np. po błędnym zapisie) — użyj domyślnej listy,
    // aby panel zawsze pokazywał komplet sakramentów do edycji.
    if ( empty( $sakramenty ) ) {
        $sakramenty = json_decode( $default, true );
    }
    // Kompatybilność wstecz: dopełnij brakujące pola, by uniknąć notice'ów.
    foreach ( $sakramenty as &$_s ) {
        $_s = array_merge(
            [ 'id'=>'', 'nazwa'=>'', 'ikona'=>'', 'opis'=>'', 'terminy'=>'', 'zgloszenie'=>'', 'dokumenty'=>'', 'wymagania'=>'', 'dodatkowe'=>'' ],
            is_array( $_s ) ? $_s : []
        );
    }
    unset( $_s );
    ?>
    <style>
        .sk-tabs { display:flex; gap:0; border-bottom:2px solid #c9a84c; margin-bottom:20px; flex-wrap:wrap; }
        .sk-tab  { padding:8px 14px; font-size:12px; font-weight:600; cursor:pointer; border:none; background:#f6f7f7; color:#50575e; border-radius:4px 4px 0 0; margin-right:3px; margin-bottom:-2px; }
        .sk-tab.active { background:#c9a84c; color:#fff; }
        .sk-panel { display:none; }
        .sk-panel.active { display:block; }
        .sk-field { display:flex; flex-direction:column; gap:4px; margin-bottom:14px; }
        .sk-field label { font-size:12px; font-weight:600; color:#50575e; }
        .sk-field input[type=text], .sk-field textarea { width:100%; padding:7px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; font-family:inherit; }
        .sk-field textarea { resize:vertical; min-height:80px; }
        .sk-field textarea.tall { min-height:120px; }
        .sk-field .sk-hint { font-size:11px; color:#8c8f94; margin-top:2px; }
        .sk-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    </style>

    <div class="sk-tabs">
        <?php foreach ( $sakramenty as $i => $s ) : ?>
        <button type="button" class="sk-tab <?php echo $i===0?'active':''; ?>"
                data-panel="sk-panel-<?php echo $i; ?>">
            <?php echo esc_html( $s['nazwa'] ); ?>
        </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ( $sakramenty as $i => $s ) : ?>
    <div class="sk-panel <?php echo $i===0?'active':''; ?>" id="sk-panel-<?php echo $i; ?>" data-index="<?php echo $i; ?>">
        <div class="sk-grid">
            <div class="sk-field">
                <label>Nazwa sakramentu</label>
                <input type="text" class="sk-nazwa" name="sakr[<?php echo $i; ?>][nazwa]" value="<?php echo esc_attr($s['nazwa']); ?>" placeholder="np. Chrzest Święty">
            </div>
            <div class="sk-field">
                <label>ID (nie zmieniaj jeśli nie musisz)</label>
                <input type="text" class="sk-id" name="sakr[<?php echo $i; ?>][id]" value="<?php echo esc_attr($s['id']); ?>" placeholder="np. chrzest">
                <span class="sk-hint">Małe litery, bez spacji, bez polskich znaków</span>
            </div>
        </div>
        <input type="hidden" class="sk-ikona" name="sakr[<?php echo $i; ?>][ikona]" value="<?php echo esc_attr($s['ikona']); ?>">
        <div class="sk-field">
            <label>Krótki opis sakramentu</label>
            <textarea class="sk-opis" name="sakr[<?php echo $i; ?>][opis]"><?php echo esc_textarea($s['opis']); ?></textarea>
        </div>
        <div class="sk-field">
            <label>Terminy</label>
            <textarea class="sk-terminy" name="sakr[<?php echo $i; ?>][terminy]"><?php echo esc_textarea($s['terminy']); ?></textarea>
            <span class="sk-hint">Np. dni i godziny udzielania sakramentu</span>
        </div>
        <div class="sk-field">
            <label>Jak się zgłosić</label>
            <textarea class="sk-zgloszenie" name="sakr[<?php echo $i; ?>][zgloszenie]"><?php echo esc_textarea($s['zgloszenie']); ?></textarea>
        </div>
        <div class="sk-field">
            <label>Wymagane dokumenty</label>
            <textarea class="sk-dokumenty tall" name="sakr[<?php echo $i; ?>][dokumenty]"><?php echo esc_textarea($s['dokumenty']); ?></textarea>
            <span class="sk-hint">Każdy dokument w osobnej linii</span>
        </div>
        <div class="sk-field">
            <label>Wymagania / warunki</label>
            <textarea class="sk-wymagania tall" name="sakr[<?php echo $i; ?>][wymagania]"><?php echo esc_textarea($s['wymagania']); ?></textarea>
            <span class="sk-hint">Np. wymagania wobec rodziców, chrzestnych, świadków, spotkania formacyjne. Każdy punkt w osobnej linii.</span>
        </div>
        <div class="sk-field">
            <label>Dodatkowe informacje</label>
            <textarea class="sk-dodatkowe tall" name="sakr[<?php echo $i; ?>][dodatkowe]"><?php echo esc_textarea($s['dodatkowe']); ?></textarea>
            <span class="sk-hint">Np. rzeczy potrzebne, przygotowanie, pozostałe uwagi</span>
        </div>
        <div class="sk-field" style="border-top:1px solid #eee;padding-top:12px;">
            <button type="button" class="button sk-del-sakr" style="color:#cc1818;align-self:flex-start;">🗑 Usuń ten sakrament</button>
        </div>
    </div>
    <?php endforeach; ?>

    <p style="margin-top:8px;">
        <button type="button" class="button button-secondary" id="sk-add-sakr">+ Dodaj sakrament</button>
        <button type="button" class="button" id="sk-restore-tresc" style="margin-left:6px;">↺ Przywróć oryginalną treść</button>
        <span class="sk-hint" style="margin-left:8px;">Nowy sakrament pojawi się jako kolejna zakładka. Pamiętaj o zapisaniu strony.</span>
    </p>

    <script>
    // Oryginalna treść przekazana z PHP — json_encode gwarantuje poprawne łamania linii.
    var SAKR_TRESC_DOMYSLNA = <?php echo wp_json_encode( $tresc_domyslna, JSON_UNESCAPED_UNICODE ); ?>;
    </script>

    <script>
    jQuery(function($){

        var POLA = ['nazwa','id','ikona','opis','terminy','zgloszenie','dokumenty','wymagania','dodatkowe'];

        // Przełączanie zakładek (delegacja — działa też dla dodanych później)
        $(document).on('click', '.sk-tab', function(){
            $('.sk-tab').removeClass('active'); $('.sk-panel').removeClass('active');
            $(this).addClass('active');
            $('#' + $(this).data('panel')).addClass('active');
        });

        // Przenumeruj atrybuty name="sakr[i][pole]" zgodnie z aktualną kolejnością paneli.
        // Dzięki temu pola wysyłają się natywnie z formularzem (niezależnie od JS).
        function reindexNames() {
            $('.sk-panel').each(function(i){
                var $panel = $(this);
                $panel.attr('data-index', i).attr('id', 'sk-panel-' + i);
                POLA.forEach(function(pole){
                    $panel.find('.sk-' + pole).attr('name', 'sakr[' + i + '][' + pole + ']');
                });
            });
        }

        // Przebuduj listę zakładek na podstawie aktualnych paneli
        function rebuildTabs() {
            reindexNames();
            var $tabs = $('.sk-tabs');
            $tabs.empty();
            $('.sk-panel').each(function(i){
                var nazwa = $(this).find('.sk-nazwa').val() || 'Nowy sakrament';
                $('<button type="button" class="sk-tab"></button>')
                    .attr('data-panel', 'sk-panel-' + i)
                    .text(nazwa)
                    .appendTo($tabs);
            });
            if ( ! $('.sk-panel.active').length ) {
                $('.sk-panel').first().addClass('active');
            }
            var activeId = $('.sk-panel.active').attr('id');
            $('.sk-tab').removeClass('active').filter('[data-panel="' + activeId + '"]').addClass('active');
        }

        // Aktualizacja nazwy w zakładce na żywo
        $(document).on('input', '.sk-nazwa', function(){
            var id = $(this).closest('.sk-panel').attr('id');
            $('.sk-tab[data-panel="' + id + '"]').text($(this).val() || 'Nowy sakrament');
        });

        // Pusty panel dla nowego sakramentu (name dodawane przez reindexNames)
        function blankPanelHtml() {
            return ''
                + '<div class="sk-panel">'
                + '  <div class="sk-grid">'
                + '    <div class="sk-field"><label>Nazwa sakramentu</label>'
                + '      <input type="text" class="sk-nazwa" value="" placeholder="np. Sakrament Pokuty"></div>'
                + '    <div class="sk-field"><label>ID (nie zmieniaj jeśli nie musisz)</label>'
                + '      <input type="text" class="sk-id" value="" placeholder="np. pokuta">'
                + '      <span class="sk-hint">Małe litery, bez spacji, bez polskich znaków</span></div>'
                + '  </div>'
                + '  <input type="hidden" class="sk-ikona" value="">'
                + '  <div class="sk-field"><label>Krótki opis sakramentu</label><textarea class="sk-opis"></textarea></div>'
                + '  <div class="sk-field"><label>Terminy</label><textarea class="sk-terminy"></textarea>'
                + '    <span class="sk-hint">Np. dni i godziny udzielania sakramentu</span></div>'
                + '  <div class="sk-field"><label>Jak się zgłosić</label><textarea class="sk-zgloszenie"></textarea></div>'
                + '  <div class="sk-field"><label>Wymagane dokumenty</label><textarea class="sk-dokumenty tall"></textarea>'
                + '    <span class="sk-hint">Każdy dokument w osobnej linii</span></div>'
                + '  <div class="sk-field"><label>Wymagania / warunki</label><textarea class="sk-wymagania tall"></textarea>'
                + '    <span class="sk-hint">Każdy punkt w osobnej linii.</span></div>'
                + '  <div class="sk-field"><label>Dodatkowe informacje</label><textarea class="sk-dodatkowe tall"></textarea>'
                + '    <span class="sk-hint">Np. rzeczy potrzebne, przygotowanie, pozostałe uwagi</span></div>'
                + '  <div class="sk-field" style="border-top:1px solid #eee;padding-top:12px;">'
                + '    <button type="button" class="button sk-del-sakr" style="color:#cc1818;align-self:flex-start;">🗑 Usuń ten sakrament</button></div>'
                + '</div>';
        }

        // Dodaj sakrament
        $('#sk-add-sakr').on('click', function(){
            var $new = $(blankPanelHtml());
            $('.sk-panel').removeClass('active');
            $new.addClass('active');
            $(this).closest('p').before($new);
            rebuildTabs();
            var newId = $new.attr('id');
            $('.sk-tab').removeClass('active').filter('[data-panel="' + newId + '"]').addClass('active');
        });

        // Buduje panel wypełniony danymi sakramentu (obiekt z SAKR_TRESC_DOMYSLNA)
        function filledPanel(s) {
            var $p = $(blankPanelHtml());
            $p.find('.sk-ikona').val(s.ikona || s.id || '');
            $p.find('.sk-nazwa').val(s.nazwa || '');
            $p.find('.sk-id').val(s.id || '');
            $p.find('.sk-opis').val(s.opis || '');
            $p.find('.sk-terminy').val(s.terminy || '');
            $p.find('.sk-zgloszenie').val(s.zgloszenie || '');
            $p.find('.sk-dokumenty').val(s.dokumenty || '');
            $p.find('.sk-wymagania').val(s.wymagania || '');
            $p.find('.sk-dodatkowe').val(s.dodatkowe || '');
            return $p;
        }

        // Przywróć oryginalną treść — zastępuje wszystkie panele kompletem z dokumentu.
        $('#sk-restore-tresc').on('click', function(){
            if ( ! confirm('Zastąpić obecną treść wszystkich sakramentów oryginalną treścią z dokumentu parafii? Zmiana zapisze się po zapisaniu strony.') ) return;
            var $anchor = $(this).closest('p');
            $('.sk-panel').remove();
            SAKR_TRESC_DOMYSLNA.forEach(function(s, idx){
                var $p = filledPanel(s);
                if ( idx === 0 ) $p.addClass('active');
                $anchor.before($p);
            });
            rebuildTabs();
            $('.sk-panel').removeClass('active').first().addClass('active');
            $('.sk-tab').removeClass('active').first().addClass('active');
        });

        // Usuń sakrament
        $(document).on('click', '.sk-del-sakr', function(){
            if ( $('.sk-panel').length <= 1 ) {
                alert('Musi pozostać przynajmniej jeden sakrament.');
                return;
            }
            if ( ! confirm('Usunąć ten sakrament? Zmiana zapisze się po zapisaniu strony.') ) return;
            var wasActive = $(this).closest('.sk-panel').hasClass('active');
            $(this).closest('.sk-panel').remove();
            if ( wasActive ) { $('.sk-panel').first().addClass('active'); }
            rebuildTabs();
        });

        // Inicjalizacja — ustaw poprawne indeksy name i zbuduj zakładki
        rebuildTabs();
    });
    </script>
    <?php
}

function parafia_sakr_save_meta( $post_id ) {
    if ( ! isset( $_POST['parafia_sakr_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['parafia_sakr_nonce'], 'parafia_sakr_nonce_action' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Pola wysyłane są natywnie jako tablica sakr[INDEKS][POLE] — niezależnie od JS.
    if ( ! isset( $_POST['sakr'] ) || ! is_array( $_POST['sakr'] ) ) {
        return; // metabox nie był na stronie / brak danych — nie ruszamy zapisanej treści
    }

    $surowe = wp_unslash( $_POST['sakr'] );
    $clean    = [];
    $uzyte_id = [];

    // Normalizacja końców linii: CRLF / CR -> LF (front używa "\n").
    $norm = function( $tekst ) {
        return str_replace( [ "\r\n", "\r" ], "\n", (string) $tekst );
    };

    foreach ( $surowe as $s ) {
        if ( ! is_array( $s ) ) continue;

        $nazwa = sanitize_text_field( $s['nazwa'] ?? '' );
        $id    = sanitize_key( $s['id'] ?? '' );

        // Pomiń całkowicie pustą pozycję.
        if ( $nazwa === '' && $id === '' ) continue;

        // Jeśli brak ID, wygeneruj je z nazwy (transliteracja do ASCII).
        if ( $id === '' && $nazwa !== '' ) {
            $id = sanitize_key( sanitize_title( remove_accents( $nazwa ) ) );
        }
        if ( $id === '' ) continue;

        // Zapewnij unikalność ID.
        $bazowe = $id; $n = 2;
        while ( in_array( $id, $uzyte_id, true ) ) {
            $id = $bazowe . '-' . $n; $n++;
        }
        $uzyte_id[] = $id;

        $clean[] = [
            'id'         => $id,
            'nazwa'      => $nazwa,
            'ikona'      => sanitize_key( $s['ikona'] ?? '' ) ?: $id,
            'opis'       => $norm( sanitize_textarea_field( $s['opis'] ?? '' ) ),
            'terminy'    => $norm( sanitize_textarea_field( $s['terminy'] ?? '' ) ),
            'zgloszenie' => $norm( sanitize_textarea_field( $s['zgloszenie'] ?? '' ) ),
            'dokumenty'  => $norm( sanitize_textarea_field( $s['dokumenty'] ?? '' ) ),
            'wymagania'  => $norm( sanitize_textarea_field( $s['wymagania'] ?? '' ) ),
            'dodatkowe'  => $norm( sanitize_textarea_field( $s['dodatkowe'] ?? '' ) ),
        ];
    }

    // Zapisujemy tylko, gdy mamy co zapisać — pusta lista nie nadpisze treści.
    // JSON_UNESCAPED_UNICODE zachowuje polskie znaki (ś, ł, ę) zamiast \u....
    if ( ! empty( $clean ) ) {
        update_post_meta( $post_id, '_sakr_lista', wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
    }
}
add_action( 'save_post', 'parafia_sakr_save_meta' );


function parafia_chorzy_register_meta_box() {
    add_meta_box( 'parafia_chorzy_dane', '🏥 Wizyty u Chorych — dane', 'parafia_chorzy_meta_box_html', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'parafia_chorzy_register_meta_box' );

function parafia_chorzy_meta_box_html( $post ) {
    $template = get_post_meta( $post->ID, '_wp_page_template', true );
    if ( $template !== 'page-chorzy.php' ) {
        echo '<p style="color:#888;font-style:italic;">Ten panel jest aktywny tylko gdy wybrany szablon to <strong>Wizyty u Chorych</strong>.</p>';
        return;
    }
    wp_nonce_field( 'parafia_chorzy_nonce_action', 'parafia_chorzy_nonce' );
    $pid = $post->ID;

    $intro      = get_post_meta( $pid, '_chorzy_intro',      true ) ?: 'Kościół otacza szczególną troską osoby chore i starsze...';
    $zgloszenie = get_post_meta( $pid, '_chorzy_zgloszenie', true ) ?: 'Aby zgłosić chorego do wizyty, prosimy o kontakt z kancelarią...';

    $default_wizyty = json_encode([
        [ 'termin'=>'Pierwszy Czwartek Miesiąca', 'miejscowosc'=>'Grodzisk',      'od'=>'10:00', 'kaplán'=>'Ks. Proboszcz',  'uwaga'=>'' ],
        [ 'termin'=>'Pierwszy Piątek Miesiąca',   'miejscowosc'=>'Mrozy',         'od'=>'10:00', 'kaplán'=>'Ks. Proboszcz',  'uwaga'=>'' ],
        [ 'termin'=>'Pierwsza Sobota Miesiąca',   'miejscowosc'=>'Cała parafia',   'od'=>'10:00', 'kaplán'=>'Ks. Wikariusz',  'uwaga'=>'Wizyty zgłaszane indywidualnie przyjmuje również ks. Proboszcz' ],
    ], JSON_UNESCAPED_UNICODE);

    $wizyty_json = get_post_meta( $pid, '_chorzy_wizyty', true ) ?: $default_wizyty;
    $wizyty = json_decode( $wizyty_json, true ) ?: [];
    ?>
    <style>
        .ch-field { display:flex; flex-direction:column; gap:4px; margin-bottom:16px; }
        .ch-field label { font-size:12px; font-weight:600; color:#50575e; }
        .ch-field input[type=text], .ch-field textarea { width:100%; padding:7px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; font-family:inherit; }
        .ch-field textarea { resize:vertical; min-height:70px; }
        .ch-divider { border:none; border-top:2px solid #c9a84c; margin:20px 0 16px; }
        .ch-list-header { display:grid; grid-template-columns:22px 1fr 120px 80px 140px 1fr 36px; gap:6px; padding:5px 10px; background:#f0f0f0; border:1px solid #ddd; border-radius:6px 6px 0 0; border-bottom:none; }
        .ch-list-header span { font-size:11px; font-weight:700; color:#50575e; text-transform:uppercase; }
        .ch-list { display:flex; flex-direction:column; border:1px solid #ddd; border-radius:0 0 6px 6px; overflow:hidden; margin-bottom:10px; }
        .ch-row { display:grid; grid-template-columns:22px 1fr 120px 80px 140px 1fr 36px; gap:6px; align-items:center; padding:8px 10px; border-bottom:1px solid #eee; background:#fff; }
        .ch-row:last-child { border-bottom:none; }
        .ch-row:nth-child(even) { background:#fafafa; }
        .ch-row input[type=text] { width:100%; padding:5px 8px; border:1px solid #ddd; border-radius:3px; font-size:12px; }
        .ch-drag { cursor:grab; color:#aaa; font-size:14px; text-align:center; user-select:none; }
        .ch-drag:active { cursor:grabbing; }
        .ch-del  { background:none; border:none; cursor:pointer; color:#cc1818; font-size:15px; padding:0; line-height:1; }
        .ch-row.sortable-ghost  { opacity:.4; background:#fff8e6 !important; }
        .ch-row.sortable-chosen { box-shadow:0 2px 10px rgba(201,168,76,.2); background:#fffdf5 !important; }
    </style>

    <input type="hidden" id="chorzy_wizyty" name="chorzy_wizyty" value="<?php echo esc_attr($wizyty_json); ?>">

    <div class="ch-field">
        <label for="chorzy_intro">Wprowadzenie (wyświetlane w hero)</label>
        <textarea id="chorzy_intro" name="chorzy_intro"><?php echo esc_textarea($intro); ?></textarea>
    </div>

    <hr class="ch-divider">
    <p style="font-size:13px;font-weight:700;color:#1d2327;margin-bottom:12px;">📅 Harmonogram wizyt</p>

    <div class="ch-list-header">
        <span></span><span>Termin</span><span>Miejscowość</span><span>Od godz.</span><span>Kapłan</span><span>Uwaga</span><span></span>
    </div>
    <div class="ch-list" id="chorzy-list">
        <?php foreach ( $wizyty as $w ) : ?>
        <div class="ch-row">
            <span class="ch-drag">⠿</span>
            <input type="text" class="ch-termin"      value="<?php echo esc_attr($w['termin']); ?>"      placeholder="np. Pierwszy Piątek Miesiąca">
            <input type="text" class="ch-miejscowosc" value="<?php echo esc_attr($w['miejscowosc']); ?>" placeholder="np. Mrozy">
            <input type="text" class="ch-od"          value="<?php echo esc_attr($w['od']); ?>"          placeholder="10:00">
            <input type="text" class="ch-kaplan"      value="<?php echo esc_attr($w['kaplán']); ?>"      placeholder="np. Ks. Proboszcz">
            <input type="text" class="ch-uwaga"       value="<?php echo esc_attr($w['uwaga']); ?>"       placeholder="Uwaga (opcja)">
            <button type="button" class="ch-del">✕</button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" id="chorzy-add">+ Dodaj wizytę</button>

    <hr class="ch-divider">

    <div class="ch-field">
        <label for="chorzy_zgloszenie">Jak zgłosić chorego</label>
        <textarea id="chorzy_zgloszenie" name="chorzy_zgloszenie"><?php echo esc_textarea($zgloszenie); ?></textarea>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
    jQuery(function($){
        function serialize() {
            var data = [];
            $('#chorzy-list .ch-row').each(function(){
                data.push({
                    termin:       $(this).find('.ch-termin').val(),
                    miejscowosc:  $(this).find('.ch-miejscowosc').val(),
                    od:           $(this).find('.ch-od').val(),
                    'kaplán':     $(this).find('.ch-kaplan').val(),
                    uwaga:        $(this).find('.ch-uwaga').val(),
                });
            });
            $('#chorzy_wizyty').val(JSON.stringify(data));
        }

        $('#chorzy-list').on('input change', 'input', serialize);
        $('form#post, #post').on('submit', serialize);
        $(document).on('click', '.editor-post-publish-button, .editor-post-save-draft, #publish, #save-action input[type=submit], button.is-primary[type="button"]', serialize);

        Sortable.create(document.getElementById('chorzy-list'), {
            handle: '.ch-drag', animation: 150,
            ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen',
            onEnd: serialize
        });

        $('#chorzy-add').on('click', function(){
            $('#chorzy-list').append(
                '<div class="ch-row">' +
                '<span class="ch-drag">⠿</span>' +
                '<input type="text" class="ch-termin"      placeholder="np. Pierwszy Piątek Miesiąca">' +
                '<input type="text" class="ch-miejscowosc" placeholder="np. Mrozy">' +
                '<input type="text" class="ch-od"          placeholder="10:00">' +
                '<input type="text" class="ch-kaplan"      placeholder="np. Ks. Proboszcz">' +
                '<input type="text" class="ch-uwaga"       placeholder="Uwaga (opcja)">' +
                '<button type="button" class="ch-del">✕</button>' +
                '</div>'
            );
            serialize();
        });

        $(document).on('click', '.ch-del', function(){
            if (confirm('Usunąć tę wizytę?')) { $(this).closest('.ch-row').remove(); serialize(); }
        });
    });
    </script>
    <?php
}

function parafia_chorzy_save_meta( $post_id ) {
    if ( ! isset( $_POST['parafia_chorzy_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['parafia_chorzy_nonce'], 'parafia_chorzy_nonce_action' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    foreach ( [ 'chorzy_intro', 'chorzy_zgloszenie' ] as $f ) {
        if ( isset( $_POST[ $f ] ) )
            update_post_meta( $post_id, '_' . $f, sanitize_textarea_field( wp_unslash( $_POST[ $f ] ) ) );
    }

    if ( isset( $_POST['chorzy_wizyty'] ) ) {
        $dec = json_decode( wp_unslash( $_POST['chorzy_wizyty'] ), true );
        if ( is_array( $dec ) ) {
            $clean = array_map( fn($r) => [
                'termin'      => sanitize_text_field( $r['termin']      ?? '' ),
                'miejscowosc' => sanitize_text_field( $r['miejscowosc'] ?? '' ),
                'od'          => sanitize_text_field( $r['od']          ?? '' ),
                'kaplán'      => sanitize_text_field( $r['kaplán']      ?? '' ),
                'uwaga'       => sanitize_text_field( $r['uwaga']       ?? '' ),
            ], $dec );
            update_post_meta( $post_id, '_chorzy_wizyty', json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
        }
    }
}
add_action( 'save_post', 'parafia_chorzy_save_meta' );


function parafia_trans_register_meta_box() {
    add_meta_box( 'parafia_trans_dane', '📺 Transmisje — sanktuaria', 'parafia_trans_meta_box_html', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'parafia_trans_register_meta_box' );

function parafia_trans_meta_box_html( $post ) {
    $template = get_post_meta( $post->ID, '_wp_page_template', true );
    if ( $template !== 'page-transmisje.php' ) {
        echo '<p style="color:#888;font-style:italic;">Ten panel aktywny tylko dla szablonu <strong>Transmisje</strong>.</p>';
        return;
    }
    wp_nonce_field( 'parafia_trans_nonce_action', 'parafia_trans_nonce' );
    $pid = $post->ID;

    $json = get_post_meta( $pid, '_trans_sanktuaria', true ) ?: '[]';
    $sanktuaria = json_decode( $json, true ) ?: [];
    ?>
    <style>
        .tr-info { background:#e8f4fd; border:1px solid #b8d9f0; border-radius:6px; padding:10px 14px; font-size:12px; color:#2c5f8a; margin-bottom:16px; line-height:1.6; }
        .tr-info strong { color:#1a4a72; }
        .tr-tabs { display:flex; gap:0; border-bottom:2px solid #c9a84c; margin-bottom:16px; }
        .tr-tab  { padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:#f6f7f7; color:#50575e; border-radius:4px 4px 0 0; margin-right:4px; }
        .tr-tab.active { background:#c9a84c; color:#fff; }
        .tr-panel { display:none; }
        .tr-panel.active { display:block; }
        .tr-list { display:flex; flex-direction:column; gap:0; border:1px solid #ddd; border-radius:6px; overflow:hidden; margin-bottom:10px; }
        .tr-row  { display:grid; grid-template-columns:22px 1fr 110px 1fr 1fr 36px; gap:6px; align-items:center; padding:8px 10px; border-bottom:1px solid #eee; background:#fff; }
        .tr-row:last-child { border-bottom:none; }
        .tr-row:nth-child(even) { background:#fafafa; }
        .tr-row input[type=text] { width:100%; padding:4px 7px; border:1px solid #ddd; border-radius:3px; font-size:12px; }
        .tr-row input[type=checkbox] { margin:0 auto; display:block; width:16px; height:16px; }
        .tr-header { display:grid; grid-template-columns:22px 1fr 110px 1fr 1fr 36px; gap:6px; padding:5px 10px; background:#f0f0f0; border-bottom:1px solid #ddd; font-size:11px; font-weight:700; color:#50575e; text-transform:uppercase; border-radius:6px 6px 0 0; }
        .tr-drag { cursor:grab; color:#aaa; font-size:14px; text-align:center; user-select:none; }
        .tr-drag:active { cursor:grabbing; }
        .tr-del  { background:none; border:none; cursor:pointer; color:#cc1818; font-size:15px; padding:0; line-height:1; }
        .tr-row.sortable-ghost  { opacity:.4; background:#fff8e6 !important; }
        .tr-row.sortable-chosen { box-shadow:0 2px 10px rgba(201,168,76,.2); background:#fffdf5 !important; }
        .tr-live-badge { display:inline-block; background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; border-radius:3px; font-size:10px; font-weight:700; padding:1px 6px; letter-spacing:.05em; }
    </style>

    <div class="tr-info">
        <strong>ℹ️ Dwa sposoby osadzania:</strong><br><br>
        <strong>1. URL transmisji live (zalecane)</strong> — gdy sanktuarium nadaje, wejdź na YouTube, skopiuj URL transmisji (np. <code>youtube.com/watch?v=abc123</code>) i wklej w pole <em>URL transmisji live</em>. Po zakończeniu transmisji usuń ten URL.<br><br>
        <strong>2. ID kanału (automatyczne)</strong> — wpisz ID kanału (np. <code>UCKAtPxfE2RAHSCwDABMMeAg</code>) — player spróbuje automatycznie wykryć live. Może nie działać dla wszystkich kanałów.<br><br>
        <strong>Priorytet:</strong> jeśli oba są wypełnione, URL transmisji live ma pierwszeństwo.
    </div>

    <input type="hidden" id="trans_sanktuaria" name="trans_sanktuaria" value="<?php echo esc_attr($json); ?>">

    <div class="tr-tabs">
        <button type="button" class="tr-tab active" data-panel="tr-polska">🇵🇱 Polska</button>
        <button type="button" class="tr-tab" data-panel="tr-swiat">🌍 Świat</button>
    </div>

    <?php foreach ( ['Polska' => 'polska', 'Świat' => 'swiat'] as $kat => $panel_id ) :
        $lista = array_values( array_filter( $sanktuaria, fn($s) => $s['kategoria'] === $kat ) );
    ?>
    <div class="tr-panel <?php echo $panel_id === 'polska' ? 'active' : ''; ?>" id="tr-<?php echo $panel_id; ?>">
        <div class="tr-header">
            <span></span><span>Nazwa</span><span>URL kanału YT</span><span>ID transmisji live</span><span>Aktywna</span><span></span>
        </div>
        <div class="tr-list" id="tr-list-<?php echo $panel_id; ?>" data-kat="<?php echo esc_attr($kat); ?>">
            <?php foreach ( $lista as $s ) : ?>
            <div class="tr-row">
                <span class="tr-drag">⠿</span>
                <input type="text" class="tr-nazwa"     value="<?php echo esc_attr($s['nazwa']);     ?>" placeholder="np. Jasna Góra">
                <input type="text" class="tr-kanal-id"  value="<?php echo esc_attr($s['kanal_id']);  ?>" placeholder="UCc5GDaJi...">
                <input type="text" class="tr-live-url"  value="<?php echo esc_attr($s['live_url'] ?? ''); ?>" placeholder="URL transmisji live (opcja)">
                <input type="text" class="tr-kanal-url" value="<?php echo esc_attr($s['kanal_url']); ?>" placeholder="https://youtube.com/@kanal">
                <button type="button" class="tr-del">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button tr-add" data-kat="<?php echo esc_attr($kat); ?>" data-panel="<?php echo $panel_id; ?>" style="margin-top:8px">
            + Dodaj sanktuarium
        </button>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
    jQuery(function($){
        $('.tr-tab').on('click', function(){
            $('.tr-tab').removeClass('active'); $('.tr-panel').removeClass('active');
            $(this).addClass('active'); $('#' + $(this).data('panel')).addClass('active');
        });

        function serializeAll() {
            var data = [];
            $('#tr-list-polska .tr-row').each(function(){
                data.push({
                    kategoria: 'Polska',
                    nazwa:     $(this).find('.tr-nazwa').val(),
                    kanal_id:  $(this).find('.tr-kanal-id').val(),
                    live_url:  $(this).find('.tr-live-url').val(),
                    kanal_url: $(this).find('.tr-kanal-url').val(),
                    opis:      '',
                });
            });
            $('#tr-list-swiat .tr-row').each(function(){
                data.push({
                    kategoria: 'Świat',
                    nazwa:     $(this).find('.tr-nazwa').val(),
                    kanal_id:  $(this).find('.tr-kanal-id').val(),
                    live_url:  $(this).find('.tr-live-url').val(),
                    kanal_url: $(this).find('.tr-kanal-url').val(),
                    opis:      '',
                });
            });
            $('#trans_sanktuaria').val(JSON.stringify(data));
        }

        $('#tr-list-polska, #tr-list-swiat').on('input change', 'input', serializeAll);
        $('form#post, #post').on('submit', serializeAll);
        $(document).on('click', '.editor-post-publish-button, .editor-post-save-draft, #publish, #save-action input[type=submit], button.is-primary[type="button"]', serializeAll);

        ['polska','swiat'].forEach(function(id){
            Sortable.create(document.getElementById('tr-list-' + id), {
                handle: '.tr-drag', animation: 150,
                ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen',
                onEnd: serializeAll
            });
        });

        $(document).on('click', '.tr-add', function(){
            var kat   = $(this).data('kat');
            var panel = $(this).data('panel');
            $('#tr-list-' + panel).append(
                '<div class="tr-row">' +
                '<span class="tr-drag">⠿</span>' +
                '<input type="text" class="tr-nazwa"     placeholder="np. Jasna Góra">' +
                '<input type="text" class="tr-kanal-id"  placeholder="UCc5GDaJi...">' +
                '<input type="text" class="tr-live-url"  placeholder="URL transmisji live (opcja)">' +
                '<input type="text" class="tr-kanal-url" placeholder="https://youtube.com/@kanal">' +
                '<button type="button" class="tr-del">✕</button>' +
                '</div>'
            );
            serializeAll();
        });

        $(document).on('click', '.tr-del', function(){
            if (confirm('Usunąć to sanktuarium?')) { $(this).closest('.tr-row').remove(); serializeAll(); }
        });
    });
    </script>
    <?php
}

function parafia_trans_save_meta( $post_id ) {
    if ( ! isset( $_POST['parafia_trans_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['parafia_trans_nonce'], 'parafia_trans_nonce_action' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['trans_sanktuaria'] ) ) {
        $dec = json_decode( wp_unslash( $_POST['trans_sanktuaria'] ), true );
        if ( is_array( $dec ) ) {
            $clean = array_map( fn($s) => [
                'kategoria' => sanitize_text_field( $s['kategoria'] ?? '' ),
                'nazwa'     => sanitize_text_field( $s['nazwa']     ?? '' ),
                'kanal_id'  => sanitize_text_field( $s['kanal_id']  ?? '' ),
                'live_url'  => esc_url_raw(          $s['live_url']  ?? '' ),
                'kanal_url' => esc_url_raw(          $s['kanal_url'] ?? '' ),
                'opis'      => '',
            ], $dec );
            update_post_meta( $post_id, '_trans_sanktuaria', json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
        }
    }
}
add_action( 'save_post', 'parafia_trans_save_meta' );



add_action( 'rest_api_init', function() {
    register_rest_route( 'parafia/v1', '/seo/(?P<post_id>\d+)', [
        'methods'             => 'POST',
        'callback'            => 'parafia_seo_save',
        'permission_callback' => function( $req ) {
            $token = $req->get_header('X-Parafia-Token');
            $saved = get_option('parafia_seo_token', '');
            return $saved && hash_equals( $saved, (string) $token );
        },
        'args' => [
            'post_id' => [ 'required' => true, 'type' => 'integer' ],
        ],
    ]);

    register_rest_route( 'parafia/v1', '/content/(?P<post_id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'parafia_seo_get_content',
        'permission_callback' => function( $req ) {
            $token = $req->get_header('X-Parafia-Token');
            $saved = get_option('parafia_seo_token', '');
            return $saved && hash_equals( $saved, (string) $token );
        },
    ]);
});

function parafia_seo_save( WP_REST_Request $req ) {
    $post_id = (int) $req->get_param('post_id');
    $body    = $req->get_json_params();

    if ( ! get_post( $post_id ) )
        return new WP_Error( 'not_found', 'Wpis nie istnieje', ['status' => 404] );

    $title       = sanitize_text_field( $body['title']       ?? '' );
    $description = sanitize_textarea_field( $body['description'] ?? '' );
    $keywords    = sanitize_text_field( $body['keywords']    ?? '' );

    if ( $title )       update_post_meta( $post_id, '_yoast_wpseo_title',    $title );
    if ( $description ) update_post_meta( $post_id, '_yoast_wpseo_metadesc', $description );
    if ( $keywords )    update_post_meta( $post_id, '_yoast_wpseo_focuskw',  $keywords );

    return rest_ensure_response([
        'success'     => true,
        'post_id'     => $post_id,
        'title'       => $title,
        'description' => $description,
        'keywords'    => $keywords,
    ]);
}

function parafia_seo_get_content( WP_REST_Request $req ) {
    $post_id = (int) $req->get_param('post_id');
    $post    = get_post( $post_id );

    if ( ! $post )
        return new WP_Error( 'not_found', 'Wpis nie istnieje', ['status' => 404] );

    $content = wp_strip_all_tags( do_shortcode( $post->post_content ) );
    $content = preg_replace( '/\s+/', ' ', trim( $content ) );

    $content_source = 'editor';
    if ( mb_strlen( $content ) < 50 ) {
        $content_source = 'theme_meta';
        $extra = [];

        $template = get_post_meta( $post_id, '_wp_page_template', true );

        $template_context = [
            'page-kontakt.php'      => 'Strona kontaktowa parafii z mapą dojazdu, danymi adresowymi, telefonem i e-mailem.',
            'page-kancelaria.php'   => 'Kancelaria parafialna — godziny otwarcia, dane kontaktowe, informacja o możliwości wsparcia parafii przelewem.',
            'page-msze.php'         => 'Porządek Mszy Świętych i nabożeństw — godziny Mszy w dni powszednie i niedziele, spowiedź, nabożeństwa okresu liturgicznego.',
            'page-historia.php'     => 'Historia parafii Mrozy — oś czasu od erygowania parafii w 1931 roku do czasów współczesnych.',
            'page-kaplani.php'      => 'Kapłani parafii Mrozy — obecny proboszcz, wikariusz oraz lista wszystkich kapłanów posługujących od 1931 roku.',
            'page-sakramenty.php'   => 'Sakramenty w parafii — informacje o Chrzcie, Pierwszej Komunii, Bierzmowaniu, Małżeństwie i Pogrzebie katolickim wraz z wymaganymi dokumentami.',
            'page-chorzy.php'       => 'Wizyty u chorych — harmonogram odwiedzin chorych przez kapłanów parafii Mrozy w pierwszym czwartku, piątku i sobotę miesiąca.',
            'page-transmisje.php'   => 'Transmisje Mszy Świętych na żywo z sanktuariów w Polsce i na świecie — Jasna Góra, Licheń, Watykan, Fatima i inne.',
            'page-lso.php'          => 'Liturgiczna Służba Ołtarza — ministranci i lektorzy w parafii Mrozy.',
            'page-group.php'        => 'Grupy parafialne i wspólnoty działające w parafii Mrozy.',
        ];

        if ( isset( $template_context[ $template ] ) ) {
            $extra[] = $template_context[ $template ];
        }

        $meta_keys = [
            '_kanc_adres'        => 'Adres kancelarii',
            '_kanc_telefon'      => 'Telefon',
            '_kanc_email'        => 'E-mail',
            '_chorzy_intro'      => 'Opis wizyt u chorych',
            '_chorzy_zgloszenie' => 'Jak zgłosić chorego',
            '_sakr_lista'        => null,
        ];

        foreach ( $meta_keys as $key => $label ) {
            $val = get_post_meta( $post_id, $key, true );
            if ( ! $val ) continue;

            if ( $key === '_sakr_lista' ) {
                $sakr = json_decode( $val, true );
                if ( is_array( $sakr ) ) {
                    $nazwy = array_column( $sakr, 'nazwa' );
                    $extra[] = 'Sakramenty: ' . implode( ', ', $nazwy );
                }
            } elseif ( $label ) {
                $extra[] = $label . ': ' . wp_strip_all_tags( $val );
            }
        }

        $extra[] = 'Parafia: ' . pm_get( 'parafia_nazwa', 'Parafia Rzymskokatolicka pw. św. Teresy od Dzieciątka Jezus w Mrozach' );
        $extra[] = 'Adres: '   . pm_get( 'parafia_address', 'ul. Św. Teresy 3, 05-320 Mrozy' );
        $extra[] = 'Telefon: ' . pm_get( 'parafia_phone',   '25 757 41 93' );

        $content = implode( ' | ', array_filter( $extra ) );
    }

    return rest_ensure_response([
        'post_id'        => $post_id,
        'title'          => $post->post_title,
        'content'        => mb_substr( $content, 0, 3000 ),
        'content_source' => $content_source,
        'post_type'      => $post->post_type,
        'permalink'      => get_permalink( $post_id ),
        'template'       => get_post_meta( $post_id, '_wp_page_template', true ),
        'current_seo'    => [
            'title'       => get_post_meta( $post_id, '_yoast_wpseo_title',    true ),
            'description' => get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ),
            'keywords'    => get_post_meta( $post_id, '_yoast_wpseo_focuskw',  true ),
        ],
    ]);
}

function parafia_seo_on_publish( $new_status, $old_status, $post ) {
    if ( $new_status !== 'publish' ) return;
    if ( ! in_array( $post->post_type, ['post', 'page'] ) ) return;
    $existing = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
    if ( ! empty( $existing ) ) return;

    $webhook_url = get_option('parafia_seo_webhook_url', '');
    if ( ! $webhook_url ) return;

    wp_remote_post( $webhook_url, [
        'timeout'  => 5,
        'blocking' => false,
        'headers'  => [ 'Content-Type' => 'application/json' ],
        'body'     => json_encode([
            'post_id'   => $post->ID,
            'post_type' => $post->post_type,
            'title'     => $post->post_title,
            'site_url'  => get_site_url(),
        ]),
    ]);
}
add_action( 'transition_post_status', 'parafia_seo_on_publish', 10, 3 );

function parafia_seo_register_meta_box() {
    add_meta_box(
        'parafia_seo_generator',
        '🔍 Auto SEO (Claude AI)',
        'parafia_seo_meta_box_html',
        [ 'post', 'page' ],
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'parafia_seo_register_meta_box' );

function parafia_seo_meta_box_html( $post ) {
    $title       = get_post_meta( $post->ID, '_yoast_wpseo_title',    true );
    $description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
    $keywords    = get_post_meta( $post->ID, '_yoast_wpseo_focuskw',  true );
    $webhook     = get_option('parafia_seo_webhook_url', '');
    $token       = get_option('parafia_seo_token', '');
    ?>
    <style>
        .seo-mb { font-size:13px; }
        .seo-mb__status { padding:8px 10px; border-radius:5px; margin-bottom:10px; font-size:12px; display:none; }
        .seo-mb__status--ok  { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
        .seo-mb__status--err { background:#ffebee; color:#c62828; border:1px solid #ef9a9a; }
        .seo-mb__status--loading { background:#fff8e6; color:#856404; border:1px solid #c9a84c; }
        .seo-mb__current { background:#f6f7f7; border:1px solid #ddd; border-radius:5px; padding:8px 10px; margin-bottom:10px; }
        .seo-mb__current p { margin:0 0 4px; font-size:11px; color:#50575e; }
        .seo-mb__current strong { color:#1d2327; font-size:12px; }
        .seo-mb__btn { width:100%; padding:8px; background:#c9a84c; color:#fff; border:none; border-radius:5px; font-size:13px; font-weight:600; cursor:pointer; transition:.2s; }
        .seo-mb__btn:hover { background:#b8972e; }
        .seo-mb__btn:disabled { background:#ccc; cursor:not-allowed; }
        .seo-mb__hint { font-size:11px; color:#8c8f94; margin-top:8px; text-align:center; }
    </style>

    <div class="seo-mb">
        <div class="seo-mb__status" id="seo-status"></div>

        <!-- Aktualny stan SEO -->
        <?php if ( $description || $title ) : ?>
        <div class="seo-mb__current">
            <?php if ( $title ) : ?>
            <p>Tytuł SEO:</p>
            <strong><?php echo esc_html( $title ); ?></strong>
            <?php endif; ?>
            <?php if ( $description ) : ?>
            <p style="margin-top:6px">Opis:</p>
            <strong><?php echo esc_html( mb_substr($description, 0, 80) . (mb_strlen($description) > 80 ? '…' : '') ); ?></strong>
            <?php endif; ?>
        </div>
        <?php else : ?>
        <p style="color:#8c8f94;font-size:12px;margin-bottom:10px;">Brak opisu SEO — kliknij przycisk aby wygenerować.</p>
        <?php endif; ?>

        <?php if ( $webhook && $token ) : ?>
        <button type="button" class="seo-mb__btn" id="seo-generate-btn"
                data-post-id="<?php echo esc_attr( $post->ID ); ?>"
                data-nonce="<?php echo esc_attr( wp_create_nonce( 'parafia_seo_generuj_' . $post->ID ) ); ?>">
            ✨ Generuj opisy SEO
        </button>
        <p class="seo-mb__hint">Odświeży się po ok. 10–20 sekundach</p>
        <?php else : ?>
        <p style="color:#cc1818;font-size:12px;">⚠️ Skonfiguruj URL webhooka i token w <a href="<?php echo admin_url('options-general.php'); ?>">Ustawienia → Ogólne</a>.</p>
        <?php endif; ?>
    </div>

    <script>
    jQuery(function($){
        $('#seo-generate-btn').on('click', function(){
            var btn      = $(this);
            var postId   = btn.data('post-id');
            var status   = $('#seo-status');

            btn.prop('disabled', true).text('⏳ Generowanie…');
            status.removeClass('seo-mb__status--ok seo-mb__status--err')
                  .addClass('seo-mb__status seo-mb__status--loading')
                  .text('Wysyłam do Claude AI… proszę czekać.')
                  .show();

            $.ajax({
                url:  <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
                type: 'POST',
                dataType: 'json',
                timeout: 30000,
                data: {
                    action:  'parafia_seo_generuj',
                    post_id: postId,
                    nonce:   btn.data('nonce')
                },
                success: function( odp ) {
                    if ( ! odp || ! odp.success ) {
                        status.removeClass('seo-mb__status--loading')
                              .addClass('seo-mb__status--err')
                              .text('❌ Błąd: ' + ( odp && odp.data && odp.data.message ? odp.data.message : 'nieznany błąd.' ));
                        btn.prop('disabled', false).text('✨ Generuj opisy SEO');
                        return;
                    }
                    status.text('Wysłano do Claude AI — opisy pojawią się za ok. 15 sekund…');
                    setTimeout(function(){
                        status.removeClass('seo-mb__status--loading')
                              .addClass('seo-mb__status--ok')
                              .text('✅ Gotowe! Odśwież stronę aby zobaczyć nowe opisy SEO.');
                        btn.prop('disabled', false).text('🔄 Generuj ponownie');
                    }, 15000);
                },
                error: function( xhr, textStatus ) {
                    var msg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
                        ? xhr.responseJSON.data.message
                        : ( 'timeout' === textStatus ? 'WordPress nie doczekał się odpowiedzi.' : 'brak odpowiedzi WordPressa (' + ( xhr.status || textStatus ) + ').' );
                    status.removeClass('seo-mb__status--loading')
                          .addClass('seo-mb__status--err')
                          .text('❌ Błąd: ' + msg);
                    btn.prop('disabled', false).text('✨ Generuj opisy SEO');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * Przycisk „Generuj opisy SEO”: WordPress (serwer) wywołuje webhook n8n.
 * Dzięki temu nie ma problemów z CORS ani http/https w przeglądarce,
 * adres webhooka i token nie trafiają do kodu strony, a błąd n8n jest czytelny.
 */
function parafia_seo_ajax_generuj() {
    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $nonce   = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

    if ( ! $post_id || ! wp_verify_nonce( $nonce, 'parafia_seo_generuj_' . $post_id ) ) {
        wp_send_json_error( [ 'message' => 'Sesja wygasła — odśwież stronę i spróbuj ponownie.' ], 403 );
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( [ 'message' => 'Brak uprawnień do edycji tego wpisu.' ], 403 );
    }
    $post = get_post( $post_id );
    if ( ! $post ) {
        wp_send_json_error( [ 'message' => 'Wpis nie istnieje.' ], 404 );
    }

    $webhook_url = get_option( 'parafia_seo_webhook_url', '' );
    if ( ! $webhook_url ) {
        wp_send_json_error( [ 'message' => 'Brak adresu webhooka N8N w Ustawienia → Ogólne.' ], 400 );
    }

    $odp = wp_remote_post( $webhook_url, [
        'timeout' => 20,
        'headers' => [ 'Content-Type' => 'application/json' ],
        'body'    => wp_json_encode( [
            'post_id'   => $post->ID,
            'post_type' => $post->post_type,
            'title'     => $post->post_title,
            'site_url'  => get_site_url(),
            'manual'    => true,
        ] ),
    ] );

    if ( is_wp_error( $odp ) ) {
        wp_send_json_error( [ 'message' => 'Nie udało się połączyć z N8N (' . $webhook_url . '): ' . $odp->get_error_message() ], 502 );
    }

    $kod = (int) wp_remote_retrieve_response_code( $odp );
    if ( $kod < 200 || $kod >= 300 ) {
        $tresc = json_decode( wp_remote_retrieve_body( $odp ), true );
        $opis  = is_array( $tresc ) && ! empty( $tresc['message'] ) ? $tresc['message'] : wp_strip_all_tags( mb_substr( wp_remote_retrieve_body( $odp ), 0, 200 ) );
        if ( 404 === $kod && false !== strpos( $webhook_url, '/webhook-test/' ) ) {
            $opis .= ' — to adres testowy; w ustawieniach wpisz adres produkcyjny (/webhook/…) i aktywuj workflow.';
        } elseif ( 404 === $kod ) {
            $opis .= ' — sprawdź, czy workflow jest aktywny w N8N.';
        }
        wp_send_json_error( [ 'message' => 'N8N odpowiedział kodem ' . $kod . ( $opis ? ': ' . $opis : '' ) ], 502 );
    }

    wp_send_json_success( [ 'status' => $kod ] );
}
add_action( 'wp_ajax_parafia_seo_generuj', 'parafia_seo_ajax_generuj' );

function parafia_seo_settings() {
    add_settings_section( 'parafia_seo_section', 'Auto SEO (Claude AI)', '__return_false', 'general' );

    register_setting( 'general', 'parafia_seo_token',       [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'general', 'parafia_seo_webhook_url', [ 'sanitize_callback' => 'esc_url_raw' ] );

    add_settings_field( 'parafia_seo_token', 'Token autoryzacyjny', function() {
        $val = get_option('parafia_seo_token', '');
        echo '<input type="text" name="parafia_seo_token" value="' . esc_attr($val) . '" class="regular-text" placeholder="Wygeneruj: openssl rand -hex 32">';
        echo '<p class="description">Ten sam token wpisz w N8N jako zmienną <code>PARAFIA_SEO_TOKEN</code>.</p>';
    }, 'general', 'parafia_seo_section' );

    add_settings_field( 'parafia_seo_webhook_url', 'URL webhooka N8N', function() {
        $val = get_option('parafia_seo_webhook_url', '');
        echo '<input type="url" name="parafia_seo_webhook_url" value="' . esc_attr($val) . '" class="regular-text" placeholder="https://twoje-n8n.pl/webhook/seo-generator">';
        echo '<p class="description">URL webhooka z workflow N8N poniżej.</p>';
    }, 'general', 'parafia_seo_section' );
}
add_action( 'admin_init', 'parafia_seo_settings' );

add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'sakrament';
    return $vars;
});

function parafia_get_vatican_news( $count = 8 ) {
    // 1) Świeży cache — ustawiany TYLKO gdy mamy realne pozycje (nigdy pustką).
    $fresh = get_transient( 'parafia_vatican_news_rss' );
    if ( is_array( $fresh ) && ! empty( $fresh ) ) {
        return $fresh;
    }

    // 2) Throttle nieudanych prób: nie pukamy do feeda co request, ale w tym
    //    czasie i tak pokazujemy ostatnią udaną porcję (graceful degradation).
    if ( get_transient( 'parafia_vn_throttle' ) ) {
        $backup = get_option( 'parafia_vn_backup', array() );
        return is_array( $backup ) ? $backup : array();
    }

    // Pobieramy feed jawnym żądaniem HTTP, zamiast fetch_feed(). Cloudflare przed
    // Vatican News odrzuca domyślną sygnaturę żądania WordPressa kodem 415, dlatego
    // wymuszamy HTTP/1.1, przeglądarkowy User-Agent i nagłówek Accept dla XML/RSS
    // (zestaw zweryfikowany jako zwracający 200 + poprawny RSS).
    $items = parafia_fetch_vatican_news_items( (int) $count );

    if ( ! empty( $items ) ) {
        // Sukces: cache 30 min + trwała kopia awaryjna (last-good).
        set_transient( 'parafia_vatican_news_rss', $items, 30 * MINUTE_IN_SECONDS );
        update_option( 'parafia_vn_backup', $items, false );
        return $items;
    }

    // Porażka: krótki throttle 5 min (zamiast 30-min zatrucia pustką),
    // a użytkownikowi pokazujemy ostatnią udaną porcję, jeśli istnieje.
    set_transient( 'parafia_vn_throttle', 1, 5 * MINUTE_IN_SECONDS );
    $backup = get_option( 'parafia_vn_backup', array() );
    return is_array( $backup ) ? $backup : array();
}

/**
 * Pobiera i parsuje feed RSS Vatican News.
 *
 * Świadomie NIE używa fetch_feed()/SimplePie: domyślne żądanie WordPressa jest
 * odrzucane przez Cloudflare kodem HTTP 415. Pobieramy więc surowy XML jawnym
 * wp_remote_get() z nagłówkami zweryfikowanymi jako akceptowane (HTTP/1.1,
 * przeglądarkowy UA, Accept dla RSS/XML), a następnie parsujemy SimpleXML-em —
 * bez zależności od wersji WP i bez ukrytej 12-godzinnej warstwy cache SimplePie.
 *
 * @param int $count Maksymalna liczba pozycji do zwrócenia.
 * @return array<int,array{title:string,link:string,date:int,desc:string,thumb:string}>
 */
function parafia_fetch_vatican_news_items( $count = 8 ) {
    $url   = 'https://vaticannews.pl/pl.rss.xml';
    $count = max( 1, (int) $count );

    $response = wp_remote_get( $url, array(
        'timeout'     => 15,
        'redirection' => 3,
        'httpversion' => '1.1',
        'user-agent'  => 'Mozilla/5.0 (compatible; ParafiaMrozy/1.0; +https://parafia-mrozy.pl)',
        'headers'     => array(
            'Accept' => 'application/rss+xml, application/xml;q=0.9, */*;q=0.8',
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        error_log( '[ParafiaVN] HTTP: ' . $response->get_error_message() );
        return array();
    }

    $code = (int) wp_remote_retrieve_response_code( $response );
    $body = (string) wp_remote_retrieve_body( $response );

    if ( 200 !== $code ) {
        error_log( '[ParafiaVN] nieoczekiwany kod HTTP ' . $code );
        return array();
    }
    if ( '' === trim( $body ) ) {
        error_log( '[ParafiaVN] pusta odpowiedź feedu' );
        return array();
    }

    // Parsowanie z wyciszeniem błędów libxml i blokadą sieci (bezpieczeństwo: brak XXE).
    $prev_errors  = libxml_use_internal_errors( true );
    $prev_entity  = function_exists( 'libxml_disable_entity_loader' ) ? libxml_disable_entity_loader( true ) : null;
    $xml          = simplexml_load_string( $body, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET );
    if ( null !== $prev_entity ) {
        libxml_disable_entity_loader( $prev_entity );
    }
    libxml_clear_errors();
    libxml_use_internal_errors( $prev_errors );

    if ( false === $xml || ! isset( $xml->channel->item ) ) {
        error_log( '[ParafiaVN] błąd parsowania XML lub brak <item> w kanale' );
        return array();
    }

    $media_ns = 'http://search.yahoo.com/mrss/';
    $items    = array();

    foreach ( $xml->channel->item as $node ) {
        if ( count( $items ) >= $count ) {
            break;
        }

        // Miniatura: najpierw <media:content url="...">, w razie braku <enclosure url="...">.
        $thumb     = '';
        $media     = $node->children( $media_ns );
        if ( isset( $media->content ) ) {
            $attrs = $media->content->attributes();
            if ( isset( $attrs->url ) ) {
                $thumb = (string) $attrs->url;
            }
        }
        if ( '' === $thumb && isset( $node->enclosure ) ) {
            $enc_attrs = $node->enclosure->attributes();
            if ( isset( $enc_attrs->url ) ) {
                $thumb = (string) $enc_attrs->url;
            }
        }

        // pubDate w formacie RFC822 → znacznik czasu Unix; 0 gdy brak/niepoprawny.
        $timestamp = 0;
        if ( isset( $node->pubDate ) ) {
            $parsed = strtotime( (string) $node->pubDate );
            if ( false !== $parsed ) {
                $timestamp = (int) $parsed;
            }
        }

        $items[] = array(
            'title' => wp_strip_all_tags( (string) $node->title ),
            'link'  => esc_url_raw( (string) $node->link ),
            'date'  => $timestamp,
            'desc'  => wp_strip_all_tags( (string) $node->description ),
            'thumb' => esc_url_raw( $thumb ),
        );
    }

    return $items;
}
