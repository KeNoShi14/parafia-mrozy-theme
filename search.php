<?php get_header(); ?>

<main id="primary" class="site-main">
    <div class="container">
        <header class="page-header" style="padding: 30px 0 20px; border-bottom: 1px solid #e0e0e0; margin-bottom: 30px;">
            <h1 class="section-heading" style="margin-bottom:0;">
                <?php printf( esc_html__( 'Wyniki wyszukiwania: %s', 'parafia-mrozy' ), '<em>' . get_search_query() . '</em>' ); ?>
            </h1>
        </header>

        <div class="content-area">
            <div id="content">
                <?php get_search_form(); ?>

                <?php if ( have_posts() ) : ?>
                    <div class="posts-grid" style="margin-top:25px;">
                        <?php while ( have_posts() ) : the_post();
                            get_template_part( 'template-parts/card', 'post' );
                        endwhile; ?>
                    </div>
                    <?php the_posts_pagination(); ?>
                <?php else : ?>
                    <p style="margin-top:20px;"><?php esc_html_e( 'Nie znaleziono wyników.', 'parafia-mrozy' ); ?></p>
                <?php endif; ?>
            </div>
            <?php get_sidebar(); ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
