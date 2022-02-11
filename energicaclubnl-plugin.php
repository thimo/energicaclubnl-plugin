<?php
/*
Plugin Name: EnergicaClub.nl Site Plugin
Description: Site specific code changes for energicaclub.nl
*/

// https://www.wpbeginner.com/wp-tutorials/how-to-display-a-list-of-child-pages-for-a-parent-page-in-wordpress/
function wpb_list_child_pages() {
  global $post;

  if (is_page() && $post->post_parent) {
    $parent_id = $post->post_parent;
  } else {
    $parent_id = $post->ID;
  }
  $childpages = wp_list_pages('sort_column=menu_order&title_li=&child_of=' . $parent_id . '&echo=0');

  if ($childpages) {
    $string = '<ul class="wpb_page_list widget"><h2 class="widget-title">' . get_the_title($parent_id) . '</h2>' . $childpages . '</ul>';
  }

  return $string;
}
add_shortcode('wpb_childpages', 'wpb_list_child_pages');

// Disable login language selector
add_filter('login_display_language_dropdown', '__return_false');

// Load custom CSS and Javascript
function enqueue_related_pages_scripts_and_styles()
{
  wp_enqueue_style('related-styles', plugins_url('/style.css', __FILE__));
  wp_enqueue_script('releated-script', plugins_url('/script.js', __FILE__), array('jquery'));
}
add_action('wp_enqueue_scripts', 'enqueue_related_pages_scripts_and_styles');
?>
