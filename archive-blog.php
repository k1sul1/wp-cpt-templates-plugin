<?php
/**
 * Note that this file isn't loaded unless you uncomment the sample in index.php!
 */

get_header();
while (have_posts()) { the_post(); ?>

  <article <?php post_class("blog-lift"); ?>>
    <div class="blog-lift__image">
      <?php
      if (has_post_thumbnail()) {
        $img = wp_get_attachment_image_src(get_post_thumbnail_id(), "large");
        echo "<img src='$img[0]'>";
      } ?>
    </div>
    <div class="blog-lift__content">
      <time><?php echo get_the_time("d.m.Y"); ?></time>
      <h2><?php the_title(); ?></h2>
      <p><?php the_excerpt(); ?></p>

      <a href="<?php the_permalink(); ?>" class="readmore">
        Read more
      </a>
      </div>
  </article>

<?php
}

get_footer();
