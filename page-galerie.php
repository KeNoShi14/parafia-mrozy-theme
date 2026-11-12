<?php
get_header();

$current_year = isset($_GET['rok']) ? (int)$_GET['rok'] : 0;
$paged        = max(1, get_query_var('paged'), isset($_GET['paged']) ? (int)$_GET['paged'] : 1);

$per_page_options = [6, 12, 24, 48];
$per_page = isset($_GET['na_stronie']) && in_array((int)$_GET['na_stronie'], $per_page_options)
    ? (int)$_GET['na_stronie']
    : 12;

$years = parafia_mrozy_galeria_lata();
if ( $current_year && ! in_array( $current_year, $years, true ) ) {
    $current_year = 0;
}

$galerie = new WP_Query( parafia_mrozy_galeria_args( [
    'posts_per_page' => $per_page,
    'paged'          => $paged,
], $current_year ) );
?>

<main id="primary" class="site-main" role="main">

    <!-- Hero -->
    <div class="galerie-hero">
        <div class="container">
            <p class="galerie-hero__eyebrow">Parafia pw. św. Teresy od Dzieciątka Jezus</p>
            <h1 class="galerie-hero__title">Galerie zdjęć</h1>
            <p class="galerie-hero__subtitle">Wspomnienia z życia parafii — Msze Święte, uroczystości i wydarzenia.</p>
        </div>
    </div>

    <div class="container">

        <!-- Filtr po roku + selector liczby elementów -->
        <?php
        $base_url = get_permalink();
        ?>
        <div class="galerie-toolbar">
            <div class="galerie-filter">
                <?php if ( count($years) > 1 ) : ?>
                <?php
                $all_url = $per_page !== 12 ? add_query_arg( 'na_stronie', $per_page, $base_url ) : $base_url;
                ?>
                <a href="<?php echo esc_url( $all_url ); ?>"
                   class="galerie-filter__btn <?php echo ! $current_year ? 'galerie-filter__btn--active' : ''; ?>">
                    Wszystkie
                </a>
                <?php foreach ( $years as $year ) :
                    $year_url = add_query_arg( 'rok', $year, $per_page !== 12 ? add_query_arg( 'na_stronie', $per_page, $base_url ) : $base_url );
                ?>
                <a href="<?php echo esc_url( $year_url ); ?>"
                   class="galerie-filter__btn <?php echo $current_year === $year ? 'galerie-filter__btn--active' : ''; ?>">
                    <?php echo esc_html( $year ); ?>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="galerie-per-page">
                <span class="galerie-per-page__label">Pokaż:</span>
                <?php foreach ( array( 6, 12, 24, 48 ) as $n ) :
                    $n_url = $current_year
                        ? add_query_arg( array( 'na_stronie' => $n, 'rok' => $current_year ), $base_url )
                        : add_query_arg( 'na_stronie', $n, $base_url );
                ?>
                <a href="<?php echo esc_url( $n_url ); ?>"
                   class="galerie-per-page__btn <?php echo $per_page === $n ? 'galerie-per-page__btn--active' : ''; ?>">
                    <?php echo esc_html( $n ); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Siatka galerii -->
        <?php if ($galerie->have_posts()) : ?>
        <div class="galerie-grid">
            <?php while ($galerie->have_posts()) : $galerie->the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="galerie-card">
                <div class="galerie-card__thumb">
                    <?php $okladka = parafia_mrozy_galeria_okladka( get_the_ID() ); ?>
                    <?php if ( $okladka ) : ?>
                        <?php echo wp_get_attachment_image( $okladka, 'large', false, [ 'alt' => get_the_title(), 'loading' => 'lazy' ] ); ?>
                    <?php else : ?>
                        <div class="galerie-card__placeholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="galerie-card__overlay">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <span>Zobacz galerię</span>
                    </div>
                </div>
                <div class="galerie-card__body">
                    <h3 class="galerie-card__title"><?php the_title(); ?></h3>
                    <p class="galerie-card__date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?php echo esc_html( parafia_mrozy_galeria_data_tekst( parafia_mrozy_galeria_data( get_the_ID() ) ) ); ?>
                    </p>
                    <?php
                    $count = count( parafia_mrozy_galeria_zdjecia( get_the_ID() ) );
                    if ($count > 0) :
                    ?>
                    <p class="galerie-card__count">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        <?php echo esc_html( parafia_mrozy_galeria_liczba( $count ) ); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <!-- Paginacja -->
        <?php if ($galerie->max_num_pages > 1) : ?>
        <div class="galerie-pagination">
            <?php
            $pagination_args = [];
            if ($current_year)    $pagination_args['rok']        = $current_year;
            if ($per_page !== 12) $pagination_args['na_stronie'] = $per_page;

            echo paginate_links([
                'base'      => add_query_arg( array_merge( $pagination_args, array( 'paged' => '%#%' ) ), $base_url ),
                'format'    => '',
                'current'   => $paged,
                'total'     => $galerie->max_num_pages,
                'prev_text' => '&lsaquo; Poprzednia',
                'next_text' => 'Następna &rsaquo;',
            ]);
            ?>
        </div>
        <?php endif; ?>

        <?php else : ?>
        <div class="galerie-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <p>Brak galerii<?php echo $current_year ? " z roku $current_year" : ''; ?>.</p>
        </div>
        <?php endif; ?>

    </div><!-- /.container -->

</main>

<?php get_footer(); ?>
