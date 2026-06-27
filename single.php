<?php
get_header();
?>

<main id="primary" class="site-main" role="main">
    <div class="container">
        <div class="content-area">

            <div id="content" class="site-content">
                <?php while ( have_posts() ) : the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <header class="single-post-header">
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                        <p class="post-meta">
                            <?php echo get_the_date( 'd F Y' ); ?>
                            <?php
                            $cats = get_the_category();
                            if ( $cats ) {
                                echo ' | ';
                                $cat_links = array_map( function( $c ) {
                                    return '<a href="' . esc_url( get_category_link( $c ) ) . '">' . esc_html( $c->name ) . '</a>';
                                }, $cats );
                                echo implode( ', ', $cat_links );
                            }
                            ?>
                        </p>
                    </header>

                    <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail" style="margin-bottom:25px;">
                        <?php the_post_thumbnail( 'large', [ 'style' => 'width:100%;height:auto;border-radius:4px;' ] ); ?>
                    </div>
                    <?php endif; ?>

                    <div class="entry-content">
                        <?php the_content(); ?>
                        <?php wp_link_pages(); ?>
                    </div>

                </article>

                <?php endwhile; ?>
            </div>

            <?php get_sidebar(); ?>

        </div>
    </div>
</main>

<?php get_footer(); ?>
