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
            <p class="gallery-single-meta">
                <?php echo get_the_date( 'd F Y' ); ?>
                <?php if ( has_excerpt() ) : ?>
                    <span class="gallery-single-desc"><?php the_excerpt(); ?></span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Siatka zdjęć -->
    <div class="container">
        <div class="gallery-single-body">

            <?php
            $images = [];
            $content = get_the_content();
            preg_match_all( '/wp-image-(\d+)/', $content, $matches );
            if ( ! empty( $matches[1] ) ) {
                foreach ( array_unique( $matches[1] ) as $img_id ) {
                    $img = get_post( intval( $img_id ) );
                    if ( $img && $img->post_type === 'attachment' ) {
                        $images[ $img_id ] = $img;
                    }
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
                        $alt       = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
                        $caption   = $image->post_excerpt ?: $image->post_title;
                    ?>
                    <a href="<?php echo esc_url( $full_url ); ?>"
                       class="pm-lightbox-item"
                       data-index="<?php echo $index; ?>"
                       data-caption="<?php echo esc_attr( $caption ); ?>"
                       aria-label="<?php echo esc_attr( $alt ?: $caption ); ?>">
                        <img src="<?php echo esc_url( $thumb_url ); ?>"
                             alt="<?php echo esc_attr( $alt ?: $caption ); ?>"
                             loading="lazy">
                        <div class="pm-lightbox-overlay">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        </div>
                    </a>
                    <?php $index++; endforeach; ?>
                </div>

            <?php else : ?>
                <p class="gallery-empty">Brak zdjęć w tej galerii.</p>
            <?php endif; ?>

            <!-- Opis tekstowy (tylko paragraf, bez bloków galerii) -->
            <?php
            $blocks = parse_blocks( get_the_content() );
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
            $prev = get_previous_post( true, '', 'category' );
            $next = get_next_post( true, '', 'category' );
            ?>
            <?php if ( $prev ) : ?>
                <a href="<?php echo get_permalink( $prev ); ?>" class="gallery-nav-btn">
                    ← <?php echo get_the_title( $prev ); ?>
                </a>
            <?php endif; ?>
            <a href="<?php echo esc_url( home_url( '/galerie/' ) ); ?>" class="gallery-nav-btn gallery-nav-all">
                Wszystkie galerie
            </a>
            <?php if ( $next ) : ?>
                <a href="<?php echo get_permalink( $next ); ?>" class="gallery-nav-btn">
                    <?php echo get_the_title( $next ); ?> →
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
