<?php
$categories = get_the_category();
$cat = $categories ? $categories[0] : null;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>

    <?php if ( has_post_thumbnail() ) : ?>
    <div class="card-thumb">
        <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
            <?php the_post_thumbnail( 'card-thumb', [ 'alt' => '' ] ); ?>
        </a>
    </div>
    <?php endif; ?>

    <div class="card-body">
        <p class="card-meta">
            <?php echo get_the_date( 'd M, Y' ); ?>
            <?php if ( $cat ) : ?>
                | <?php echo parafia_mrozy_category_label( $cat->term_id ); ?>
            <?php endif; ?>
        </p>

        <h3 class="card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <div class="card-excerpt">
            <?php the_excerpt(); ?>
        </div>
    </div>

</article>
