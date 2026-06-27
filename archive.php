<?php
get_header();
?>

<main id="primary" class="site-main" role="main">
    <div class="container">

        <header class="page-header" style="padding: 30px 0 20px; border-bottom: 1px solid #e0e0e0; margin-bottom: 30px;">
            <?php the_archive_title( '<h1 class="section-heading" style="margin-bottom:0;">', '</h1>' ); ?>
            <?php the_archive_description( '<div class="archive-description" style="margin-top:10px;color:#666;">', '</div>' ); ?>
        </header>

        <div class="content-area">
            <div id="content" class="site-content">
                <?php if ( have_posts() ) : ?>
                    <div class="posts-grid">
                        <?php while ( have_posts() ) : the_post();
                            get_template_part( 'template-parts/card', 'post' );
                        endwhile; ?>
                    </div>
                    <?php the_posts_pagination( [
                        'prev_text' => '&laquo; Poprzednie',
                        'next_text' => 'Następne &raquo;',
                        'class'     => 'posts-pagination',
                    ] ); ?>
                <?php else : ?>
                    <p><?php esc_html_e( 'Brak wpisów.', 'parafia-mrozy' ); ?></p>
                <?php endif; ?>
            </div>
            <?php get_sidebar(); ?>
        </div>

    </div>
</main>

<?php get_footer(); ?>
