<?php
/*
Plugin Name: CPT Templates
Author: Christian Nikkanen / redandblue
*/

defined("ABSPATH") or die("Wat r u doing?");


class CPT_Template {
  private $theme_overloading;
  private $post_type;
  private $taxonomy;
  private $template_directory;

  public function __construct() {
    $this->enableThemeOverloading();
  }

  /**
   * Get template path while honouring theme overloading value.
   *
   * @param string $template
   * @return string Path to template.
   */
  private function getTemplatePath($template) {
    if ($this->theme_overloading) {
      if ($file = locate_template([$template])) {
        return $file;
      }
    }

    return $this->template_directory . $template;
  }

  /**
   * Enables theme overloading. On by default.
   *
   * @return $this
   */
  public function enableThemeOverloading() {
    $this->theme_overloading = true;

    return $this;
  }

  /**
   * Disables theme overloading. Off by default.
   *
   * @return $this
   */
  public function disableThemeOverloading() {
    $this->theme_overloading = false;

    return $this;
  }

  /**
   * Filters archive templates and adds $this->post_type to query.
   *
   * @return $this
   */
  public function captureArchive() {
    add_filter("template_include", function($template) {
      if (is_post_type_archive($this->post_type->name)) {
        return $this->getTemplatePath("archive-{$this->post_type->name}.php");
      } else if (is_tax($this->taxonomy->name)) {
        return $this->getTemplatePath("taxonomy-{$this->taxonomy->name}.php");
      }

      return $template;
    });

    add_filter("pre_get_posts", function($query) {
      if ($query->is_tax($this->taxonomy->name)) {
        $query->set("post_type", $this->taxonomy->object_type);
      }

      return $query;
    });

    return $this;
  }

  /**
   * Filters single page template.
   *
   * @return $this
   */
  public function captureSingle() {
    add_filter("single_template", function($single) {
      global $post;

      if ($post->post_type === $this->post_type->name) {
        return $this->getTemplatePath("single-{$this->post_type->name}.php");
      }

      return $single;
    }, 999);

    return $this;
  }

  /**
   * @return $this
   */
  public function registerTemplateDirectory($path = "./") {
    if ($path[strlen($path) - 1] !== DIRECTORY_SEPARATOR) { // Not UTF safe.
      $path[] = DIRECTORY_SEPARATOR;
    }

    $this->template_directory = $path;
    return $this;
  }

  /**
   * Registers a post type.
   *
   * @param string $name Post type slug.
   * @param array $args Args for register_post_type.
   * @return $this
   */
  public function createCustomPostType($name = "cpt-default", $args = []) {
    if ($this->post_type) {
      throw new Exception("Only one post type per instance is allowed.");
    }

    $maybe_post_type_object = register_post_type($name, $args);

    if (is_wp_error($maybe_post_type_object)) {
      throw $maybe_post_type_object;
    } else {
      $this->post_type = $maybe_post_type_object;
    }

    return $this;
  }

  /**
   * Registers a taxonomy and associates it for object types.
   *
   * @param string $name Taxonomy slug.
   * @param array|string|boolean $obj_type What post types to associate with.
   * @param array $args Args for register_taxonomy.
   * @return $this
   */
  public function createCustomTaxonomy($name = "cpt-dtaxonomy", $obj_type = false, $args = []) {
    if ($this->taxonomy) {
      throw new Exception("Only one taxonomy per instance is allowed. Create a new instance without post type if you require multiple taxonomies.");
    } else if (taxonomy_exists($name)) {
      throw new Exception("Taxonomy with that name exists already.");
    }

    register_taxonomy($name, $obj_type, $args);

    if (is_array($obj_type)) {
      foreach ($obj_type as $type) {
        if (!register_taxonomy_for_object_type($name, $type)) {
          throw new Exception("Unable to register $name for $type");
        }
      }
    }

    $this->taxonomy = get_taxonomy($name);;
    return $this;
  }
}

/*
  // Sample usage.

  add_action("init", function() {
    $blog = new CPT_Template();
    $blog->disableThemeOverloading()
      ->registerTemplateDirectory(plugin_dir_path(__FILE__))
      ->createCustomPostType("blog", [
        "labels" => [

        ],
        "public" => true,
        "publicly_queryable" => true,
        "supports" => ["title", "editor", "thumbnail", "excerpt"],
        "has_archive" => true,
        "menu_position" => 0
      ])
      ->createCustomTaxonomy("topic", "blog", [
        "labels" => [

        ],
        "public" => true,
        "publicly_queryable" => true,
        "hierarchical" => true
      ])
      ->captureSingle()
      ->captureArchive();

    // Flush rules *once* after creating a new post type or taxonomy, don't
    // leave on because the operation is expensive.
    // flush_rewrite_rules();
  });
*/
