<?php
get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
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
                    <p><?php esc_html_e( 'Nie znaleziono wpisów.', 'parafia-mrozy' ); ?></p>
                <?php endif; ?>
            </div>

            <?php get_sidebar(); ?>

        </div>
    </div>
</main>

<?php get_footer(); ?>
