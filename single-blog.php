<?php
/**
 * Note that this file isn't loaded unless you uncomment the sample in index.php!
 */

get_header();
while (have_posts()) { the_post(); ?>

  <article <?php post_class(); ?>>
    <?php the_title(); ?>
    <?php the_content(); ?>
  </article>

<?php
}

get_footer();
