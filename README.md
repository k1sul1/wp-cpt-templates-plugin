# CPT Templates
Small plugin that reimplements template hierarchy in WordPress and allows you to create custom post types & their respective templates from a plugin.

The plugin doesn't do nothing out of the box. It simply loads the CPT_Template class which is used to actually do stuff.

## Howto
I recommend that you create a new plugin instead of modifying this directly.
The files in this plugin are only for demo purposes.

Sample plugin:
```php
<?php
/*
Plugin Name: My Plugin Name
Author: Your name
*/

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
```

Create `taxonomy-{$taxonomy_name}.php`, `single-{$post_type}.php` and `archive-{$post_type}.php` and place them in the same folder.

If you want to use a preprocessor for your stylesheets and the latest flavour of JavaScript, this plugin also comes with preconfigured build scripts, copy `package.json` and create `dist` & `src` folders to your project if you want to use them.
