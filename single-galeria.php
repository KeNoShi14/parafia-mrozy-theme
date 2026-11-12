<?php
get_header();
?>

<main id="primary" class="site-main">
<?php while ( have_posts() ) : the_post(); ?>

    <!-- Nagłówek galerii -->
    <div class="gallery-single-header">
        <div class="container">
            <p class="gallery-breadcrumb">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Strona główna</a>
                <span>›</span>
                <a href="<?php echo esc_url( home_url( '/galerie/' ) ); ?>">Galerie</a>
                <span>›</span>
                <?php the_title(); ?>
            </p>
            <h1 class="gallery-single-title"><?php the_title(); ?></h1>
            <?php
            $zdjecia_ids = parafia_mrozy_galeria_zdjecia( get_the_ID() );
            $opis        = (string) get_post_field( 'post_excerpt', get_the_ID() );
            ?>
            <p class="gallery-single-meta">
                <?php echo esc_html( parafia_mrozy_galeria_data_tekst( parafia_mrozy_galeria_data( get_the_ID() ) ) ); ?>
                <?php if ( $zdjecia_ids ) : ?>
                    · <?php echo esc_html( parafia_mrozy_galeria_liczba( count( $zdjecia_ids ) ) ); ?>
                <?php endif; ?>
                <?php if ( '' !== trim( $opis ) ) : ?>
                    <span class="gallery-single-desc"><?php echo nl2br( esc_html( trim( $opis ) ) ); ?></span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Siatka zdjęć -->
    <div class="container">
        <div class="gallery-single-body">

            <?php
            $images = [];
            foreach ( $zdjecia_ids as $img_id ) {
                $img = get_post( $img_id );
                if ( $img && 'attachment' === $img->post_type ) {
                    $images[ $img_id ] = $img;
                }
            }
            ?>

            <?php if ( ! empty( $images ) ) : ?>

                <div class="pm-lightbox-grid" id="pm-gallery">
                    <?php
                    $index = 0;
                    foreach ( $images as $image ) :
                        $full_url  = wp_get_attachment_image_url( $image->ID, 'full' );
                        $thumb_url = wp_get_attachment_image_url( $image->ID, 'large' );
                        $alt       = trim( (string) get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) );
                        $caption   = trim( $image->post_excerpt );
                        if ( '' === $alt ) {
                            $alt = '' !== $caption ? $caption : sprintf( '%s – zdjęcie %d', get_the_title(), $index + 1 );
                        }
                    ?>
                    <a href="<?php echo esc_url( $full_url ); ?>"
                       class="pm-lightbox-item"
                       data-index="<?php echo $index; ?>"
                       data-caption="<?php echo esc_attr( $caption ); ?>"
                       aria-label="<?php echo esc_attr( $alt ); ?>">
                        <img src="<?php echo esc_url( $thumb_url ); ?>"
                             alt="<?php echo esc_attr( $alt ); ?>"
                             loading="<?php echo $index < 8 ? 'eager' : 'lazy'; ?>">
                        <div class="pm-lightbox-overlay">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        </div>
                    </a>
                    <?php $index++; endforeach; ?>
                </div>

            <?php else : ?>
                <p class="gallery-empty">Brak zdjęć w tej galerii.</p>
            <?php endif; ?>

            <!-- Opis ze starszych galerii (tekst w treści wpisu, bez bloków zdjęć) -->
            <?php
            $blocks = ( '' === trim( $opis ) && ! parafia_mrozy_galeria_nowy_format( get_the_ID() ) ) ? parse_blocks( get_the_content() ) : [];
            $text_blocks = array_filter( $blocks, function( $block ) {
                return ! in_array( $block['blockName'], [
                    'core/gallery', 'core/image', 'core/media-text',
                ] );
            } );
            if ( ! empty( $text_blocks ) ) :
                $text_html = '';
                foreach ( $text_blocks as $block ) {
                    $text_html .= render_block( $block );
                }
                if ( trim( strip_tags( $text_html ) ) ) :
            ?>
            <div class="gallery-single-content entry-content">
                <?php echo wp_kses_post( $text_html ); ?>
            </div>
            <?php endif; endif; ?>

        </div><!-- .gallery-single-body -->

        <!-- Nawigacja -->
        <div class="gallery-nav">
            <?php
            $prev = parafia_mrozy_galeria_sasiad( get_the_ID(), 'starsze' );
            $next = parafia_mrozy_galeria_sasiad( get_the_ID(), 'nowsze' );
            ?>
            <?php if ( $prev ) : ?>
                <a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="gallery-nav-btn">
                    ← <?php echo esc_html( get_the_title( $prev ) ); ?>
                </a>
            <?php endif; ?>
            <a href="<?php echo esc_url( home_url( '/galerie/' ) ); ?>" class="gallery-nav-btn gallery-nav-all">
                Wszystkie galerie
            </a>
            <?php if ( $next ) : ?>
                <a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="gallery-nav-btn">
                    <?php echo esc_html( get_the_title( $next ) ); ?> →
                </a>
            <?php endif; ?>
        </div>

    </div><!-- .container -->

<?php endwhile; ?>
</main>

<!-- LIGHTBOX -->
<div id="pm-lightbox" role="dialog" aria-modal="true" aria-label="Podgląd zdjęcia">
    <div class="pm-lb-backdrop"></div>
    <div class="pm-lb-ui">
        <div class="pm-lb-header">
            <span class="pm-lb-counter" id="pm-lb-counter"></span>
            <button class="pm-lb-close" id="pm-lb-close" aria-label="Zamknij">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="pm-lb-stage">
            <button class="pm-lb-nav pm-lb-prev" id="pm-lb-prev" aria-label="Poprzednie zdjęcie">&#8249;</button>
            <div class="pm-lb-img-wrap">
                <div class="pm-lb-spinner"><div class="pm-spinner-ring"></div></div>
                <img id="pm-lb-img" src="" alt="">
            </div>
            <button class="pm-lb-nav pm-lb-next" id="pm-lb-next" aria-label="Następne zdjęcie">&#8250;</button>
        </div>
        <div class="pm-lb-caption" id="pm-lb-caption"></div>
    </div>
</div>

<?php get_footer(); ?>
