<?php
// https://github.com/amarinediary/Where-Is-My-Bread

// if (!defined('ABSPATH')) {
//   exit;
// };

// if (version_compare(PHP_VERSION, '8.0.0', '<')) {
//   return;
// };

if (!function_exists('get_the_crumbs')) {
  /**
   * Retrieve the crumbs.
   *
   * @since 1.0.0
   *
   * @return Array Crumbs array.
   */
  function get_the_crumbs() {
    global $post;

    $flour = $_SERVER['REQUEST_URI'];

    if (str_contains($flour, '?')) {
      $flour = substr($flour, 0, strpos($flour, '?'));
    };

    if (str_ends_with($flour, '/')) {
      $flour = explode('/', substr($flour, 1, -1));
    } else {
      $flour = explode('/', substr($flour, 1));
    };

    $crumbs = array();

    foreach ($flour as $crumb) {
      if (!empty($crumb)) {
        $slug = esc_html($crumb);

        if ($slug == 'discussion') {
          $parent_id = $post->post_parent;
          $url = get_permalink($parent_id);
          $slug = get_post($parent_id)->post_name;
        } else {
          $url = esc_url($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . substr(implode('/', $flour), 0, strpos(implode('/', $flour), $crumb)) . $crumb . '/');
        }

        array_push(
          $crumbs,
          array(
            'slug' => $slug,
            'url' => $url,
          )
        );
      }
    };

    $banned_slugs = array();

    $post_types = get_post_types(
      array(
        'public' => true,
      ),
      'objects'
    );

    foreach ($post_types as $post_type) {
      array_push($banned_slugs, $post_type->name);

      if (isset($post_type->rewrite['slug'])) {
        array_push($banned_slugs, $post_type->rewrite['slug']);
      };
    };

    $taxonomies = get_taxonomies(
      array(
        'public' => true,
      ),
      'objects'
    );

    foreach ($taxonomies as $taxonomy) {
      array_push($banned_slugs, $taxonomy->name);

      if (isset($taxonomy->rewrite['slug'])) {
        array_push($banned_slugs, $taxonomy->rewrite['slug']);
      };
    };

    $banned_crumbs = array();

    foreach ($banned_slugs as $banned_slug) {
      $slug = esc_html($banned_slug);

      $url = esc_url($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . substr(implode('/', $flour), 0, strpos(implode('/', $flour), $banned_slug)) . $banned_slug . '/');

      array_push(
        $banned_crumbs,
        array(
          'slug' => $slug,
          'url' => $url,
        )
      );
    };

    $crumbs = array_filter($crumbs, function ($crumb) use ($banned_slugs) {
      if (!in_array($crumb['slug'], $banned_slugs) && !in_array($crumb['url'], $banned_slugs)) {
        return !in_array($crumb['slug'], $banned_slugs);
      };
    });

    return $crumbs;
  };
};

if (!function_exists('the_bread')) {
  /**
   * Display the bread, a formatted crumbs list.
   *
   * @since 1.0.0
   *
   * @param   Array   $ingredients                    The bread arguments.
   * @param   Array   $ingredients['root']            Root crumb. Default to null.
   * @param   String  $ingredients['root']['slug']    Root crumb slug.
   * @param   String  $ingredients['root']['url']     Root crumb url.
   * @param   String  $ingredients['separator']       The crumb's separator. The separator is not escaped.
   * @param   Integer $ingredients['offset']          Crumbs offset. Accept positive/negative Integer. Default to "0". Refer to array_slice, https://www.php.net/manual/en/function.array-slice.php.
   * @param   Integer $ingredients['length']          Crumbs length. Accept positive/negative Integer. Default to "null". Refer to array_slice, https://www.php.net/manual/en/function.array-slice.php.
   *
   * @return  Array   The formatted crumbs list.
   */
  function the_bread($ingredients = array()) {
    if (empty($ingredients['root'])) {
      $root = null;
    } else {
      $root = $ingredients['root'];
    };

    if (empty($ingredients['offset'])) {
      $offset = 0;
    } else {
      $offset = $ingredients['offset'];
    };

    if (empty($ingredients['length'])) {
      $length = null;
    } else {
      $length = $ingredients['length'];
    };

    $crumbs = get_the_crumbs();

    if (!empty($root)) {
      array_unshift($crumbs, $ingredients['root']);
    };

    $crumbs = array_slice($crumbs, $offset, $length);

    if (!empty($crumbs)) {
      echo '<ol class="bread" itemscope itemtype="https://schema.org/BreadcrumbList">';

      $i = 0;

      foreach ($crumbs as $crumb) {
        $i++;

        if (url_to_postid($crumb['url'])) {
          $title = get_the_title(url_to_postid($crumb['url']));
        } elseif (get_page_by_path($crumb['slug'])) {
          $title = get_the_title(get_page_by_path($crumb['slug']));
        } else {
          $title = ucfirst(str_replace('-', ' ', $crumb['slug']));
        };

        echo '<li class="crumb" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="' . $crumb['url'] . '">
              <span itemprop="name">' . $title . '</span>
            </a>
            <meta itemprop="position" content="' . $i . '">
          </li>';

        if ($i !== sizeof($crumbs) && !empty($ingredients['separator'])) {
          echo '<span>' . $ingredients['separator'] . '</span>';
        };
      };

      echo '</ol>';
    };
  };
};

function enable_bread() {
  $ingredients = array(
    'separator' => " » ",
    'root' => array('slug' => 'home', 'url' => get_home_url()),
  );
  the_bread($ingredients);
}

add_action('buddyboss_theme_begin_content', 'enable_bread');
?>
