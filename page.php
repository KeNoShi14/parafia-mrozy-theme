<?php
get_header();
?>

<main id="primary" class="site-main" role="main">
    <div class="container">
        <div class="content-area">
            <div id="content" class="site-content">
                <?php while ( have_posts() ) : the_post(); ?>

                <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="single-post-header">
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                    </header>

                    <?php if ( has_post_thumbnail() ) : ?>
                    <div style="margin-bottom:25px;">
                        <?php the_post_thumbnail( 'large', [ 'style' => 'width:100%;height:auto;border-radius:4px;' ] ); ?>
                    </div>
                    <?php endif; ?>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>

                <?php endwhile; ?>
            </div>
            <?php get_sidebar(); ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
