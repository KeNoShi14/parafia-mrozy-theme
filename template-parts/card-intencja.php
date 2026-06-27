<?php
$pdf_url = get_post_meta( get_the_ID(), '_intencja_pdf_url', true );
if ( $pdf_url ) {
    $pdf_url = set_url_scheme( $pdf_url, 'https' );
}
$has_pdf = ! empty( $pdf_url );
$card_href = $has_pdf ? esc_url( $pdf_url ) : get_the_permalink();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card intencja-card' . ( $has_pdf ? ' has-pdf' : '' ) ); ?>
    <?php if ( $has_pdf ) : ?>
        data-pdf="<?php echo esc_url( $pdf_url ); ?>"
        role="button"
        tabindex="0"
        aria-label="<?php echo esc_attr( get_the_title() ); ?> – otwórz PDF"
    <?php endif; ?>>

    <div class="card-thumb">
        <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'card-thumb', [ 'alt' => '' ] ); ?>
        <?php elseif ( $has_pdf ) : ?>
            <div class="pdf-preview-wrap" data-pdf="<?php echo esc_url( $pdf_url ); ?>">
                <canvas class="pdf-preview-canvas"></canvas>
                <div class="pdf-preview-loading">
                    <div class="pm-spinner-ring"></div>
                </div>
            </div>
        <?php else : ?>
            <div class="pdf-thumb-placeholder">
                <svg viewBox="0 0 60 75" xmlns="http://www.w3.org/2000/svg" width="60" height="75">
                    <rect x="2" y="2" width="56" height="71" rx="4" fill="#1e1e1e" stroke="#c9a84c" stroke-width="1.5"/>
                    <path d="M36 2 L56 22 L36 22 Z" fill="#c9a84c" opacity=".5"/>
                    <text x="30" y="48" text-anchor="middle" font-family="Montserrat,sans-serif" font-size="13" font-weight="700" fill="#c9a84c">PDF</text>
                </svg>
            </div>
        <?php endif; ?>

        <?php if ( $has_pdf ) : ?>
            <span class="pdf-badge" aria-hidden="true">.PDF</span>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <p class="card-meta">
            <?php echo get_the_date( 'd M, Y' ); ?> |
            <span class="cat-link">Intencje</span>
        </p>
        <h3 class="card-title">
            <?php if ( ! $has_pdf ) : ?>
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            <?php else : ?>
                <?php the_title(); ?>
            <?php endif; ?>
        </h3>

        <?php if ( $has_pdf ) : ?>
            <p class="pdf-cta">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
                Kliknij aby otworzyć intencje
            </p>
        <?php endif; ?>
    </div>

</article>
