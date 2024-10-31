<?php
/*
Plugin Name: Post Scriptum
Plugin URI: http://www.mfd-consult.dk/post-scriptum/
Description: A simple plugin adding optional per-category and per-tag text/HTML at the end of the post content.
Author: Morten Høybye Frederiksen
Version: 1.0
Author URI: http://www.wasab.dk/morten/
*/

/**
* Remove post script from description value, when used in other contexts.
*/
function post_scriptum_description($description, $term=false) {
  return preg_replace('|PS:\s+(\S+.+)$|s', '', $description);
}

/**
* Add post script for each post term to end of content.
*/
function post_scriptum_content($content) {
  global $id;

  if (function_exists('get_object_taxonomies')) {
    // Determine taxonomies.
    $taxonomies = get_object_taxonomies('post');
    // Get terms.
    $terms = wp_get_object_terms($id, $taxonomies);
  } else {
    // Translate categories into terms.
    $terms = get_the_category($id);
    foreach ($terms as $t => $term) {
      $terms[$t]->taxonomy = 'category';
      $terms[$t]->description = $term->category_description;
    }
  }

  // Loop through terms, generation post script from description.
  $ps = '';
  foreach ($terms as $term) {
    $desc = preg_replace('|^http:\S+|s', '', $term->description);
    $desc = preg_replace('|^.*PS:\s+(\S+.+)$|s', '$1', $desc);
    if (empty($desc))
      continue;
    $ps .= '<div class="post-scriptum-' . $term->taxonomy . '-' . $term->slug . '">' . $desc . '</div>';
  }

  return $content . '<div class="post-scriptum">' . $ps . '</div>';
}

// Add filters.
add_filter('category_description', 'post_scriptum_description');
add_filter('term_description', 'post_scriptum_description');
add_filter('the_content', 'post_scriptum_content');
remove_filter('pre_category_description', 'wp_filter_kses');

// EOF
