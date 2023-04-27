<?php

/**
 * The template for displaying archive pages.
 *
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package storefront
 */


get_header(); ?>

<div id="primary" class="content-area">
  <main id="main" class="site-main" role="main">
    <h1>Våra butiker</h1>
    <?php
    $args = array(
      'post_type'      => 'butik',
      'posts_per_page' => 10,
    );

    // Poster från den här post-typ (butik som anges i functions.php)
    $loop = new WP_Query($args);

    while ($loop->have_posts()) {
      $loop->the_post();
    ?>
      <div class="entry-content">
        <a href=<?php echo get_post_permalink() ?>>
          <?php the_title(); ?>
        </a>
        <?php the_content(); ?>
      </div>
    <?php
    }

    ?>



  </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
