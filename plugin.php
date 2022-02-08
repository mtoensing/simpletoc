<?php

/**
 * Plugin Name:   SimpleTOC - Table of Contents Block
 * Plugin URI:    https://marc.tv/simpletoc-wordpress-inhaltsverzeichnis-plugin-gutenberg/
 * Description:   Adds a basic "Table of Contents" Gutenberg block.
 * Version:       5.0.2
 * Author:        Marc Tönsing
 * Author URI:    https://marc.tv
 * Text Domain:   simpletoc
 * License: GPL   v2 or later
 * License URI:   http://www.gnu.org/licenses/gpl-2.0.html
 * 
 */

/**
 * Initalise frontend and backend and register block
 **/

function register_simpletoc_block()
{

  wp_set_script_translations( 'simpletoc-toc-editor-script', 'simpletoc' );

  add_filter('plugin_row_meta', __NAMESPACE__ . '\\simpletoc_plugin_meta', 10, 2);

  register_block_type( __DIR__ . '/build' , [
    'render_callback' => __NAMESPACE__ . '\\render_callback'
  ]);

}

add_action( 'init', 'register_simpletoc_block' );

/**
 * Filter to add plugins to the TOC list for Rank Math plugin
 *
 * @param array TOC plugins.
 */

add_filter('rank_math/researches/toc_plugins', function ($toc_plugins) {
  $toc_plugins['simpletoc/plugin.php'] = 'SimpleTOC';
  return $toc_plugins;
});

/**
 * Render block output 
 *
 */

function render_callback( $attributes )
{
  //add only if block is used in this post.
  add_filter('render_block', __NAMESPACE__ . '\\filter_block', 10, 2);

  $alignclass = '';
  if ( isset ($attributes['align']) ) {
    $align = $attributes['align'];
    $alignclass = 'align' . $align;
  }

  $className = '';
  if ( isset( $attributes['className'] ) ) {
    $className = strip_tags(htmlspecialchars($attributes['className']));
  }

  $post = get_post();
  $blocks = parse_blocks($post->post_content);

  if (empty($blocks)) {
    $html = '';
    if ($attributes['no_title'] == false) {
      $html = '<h2 class="simpletoc-title">' . __('Table of Contents2', 'simpletoc') . '</h2>';
    }
    $html .= '<p class="components-notice is-warning ' . $alignclass . '">' . __('No blocks found.', 'simpletoc')  . ' ' . __('Save or update post first.', 'simpletoc') . '</p>';
    return $html;
  }

  $headings = array_reverse(filter_headings_recursive($blocks));

  $headings_clean = array_map('trim', $headings);

  if (empty($headings_clean)) {
    $html = '';
    if ($attributes['no_title'] == false) {
      $html = '<h2 class="simpletoc-title">' . __('Table of Contents', 'simpletoc') . '</h2>';
    }
    $html .= '<p class="components-notice is-warning ' . $alignclass . '">' . __('No headings found.', 'simpletoc') . ' ' . __('Save or update post first.', 'simpletoc') . '</p>';
    return $html;
  }

  $output = generateToc($headings_clean, $attributes);

  $pre_html = '<div class="simpletoc ' . $className . ' ' . $alignclass . '">';
  $post_html = '</div>';
  $output = $pre_html . $output . $post_html;

  return $output;
}

/**
 * Return all headings with a recursive walk through all blocks. 
 * This includes groups and reusable block with groups within reusable blocks. 
 */

function filter_headings_recursive($blocks)
{

  $arr = array();

  foreach ($blocks as $block => $innerBlock) {

    if (is_array($innerBlock)) {

      if (isset($innerBlock['attrs']['ref'])) {
        // search in reusable blocks
        $e_arr = parse_blocks(get_post($innerBlock['attrs']['ref'])->post_content);
        $arr = array_merge(filter_headings_recursive($e_arr), $arr);
      } else {
        // search in groups
        $arr = array_merge(filter_headings_recursive($innerBlock), $arr);
      }
    } else {

      if (isset($blocks['blockName']) && $blocks['blockName'] === 'core/heading' && $innerBlock !== 'core/heading') {
        // make sure its a headline.
        if (preg_match("/(<h1|<h2|<h3|<h4|<h5|<h6)/i", $innerBlock)) {
          $arr[] = $innerBlock;
        }
      }
    }
  }

  return $arr;
}

/**
 * Remove all problematic characters for toc links 
 */

function simpletoc_sanitize_string($string)
{

  // remove punctuation
  $zero_punctuation = preg_replace("/\p{P}/u", "", $string);
  // remove non-breaking spaces
  $html_wo_nbs = str_replace("&nbsp;", " ", $zero_punctuation);
  // remove umlauts and accents
  $string_without_accents = remove_accents($html_wo_nbs);
  // Sanitizes a title, replacing whitespace and a few other characters with dashes.
  $sanitized_string = sanitize_title_with_dashes($string_without_accents);
  // Encode for use in an url
  $urlencoded = urlencode($sanitized_string);

  return $urlencoded;
}

function simpletoc_plugin_meta($links, $file)
{

  if (false !== strpos($file, 'simpletoc')) {
    $links = array_merge($links, array('<a href="https://wordpress.org/support/plugin/simpletoc">' . __('Support', 'simpletoc') . '</a>'));
    $links = array_merge($links, array('<a href="https://marc.tv/out/donate">' . __('Donate', 'simpletoc') . '</a>'));
    $links = array_merge($links, array('<a href="https://wordpress.org/support/plugin/simpletoc/reviews/#new-post">' . __('Write a review', 'simpletoc') . '&nbsp;⭐️⭐️⭐️⭐️⭐️</a>'));
  }

  return $links;
}

function addAnchorAttribute($html)
{

  // remove non-breaking space entites from input HTML
  $html_wo_nbs = str_replace("&nbsp;", " ", $html);

  // Thank you Nick Diego 
  if (!$html_wo_nbs) {
    return $html;
  }

  libxml_use_internal_errors(TRUE);
  $dom = new \DOMDocument();
  @$dom->loadHTML($html_wo_nbs, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

  // use xpath to select the Heading html tags.
  $xpath = new \DOMXPath($dom);
  $tags = $xpath->evaluate("//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]");

  // Loop through all the found tags
  foreach ($tags as $tag) {

    // Set id attribute
    $heading_text = strip_tags($html);
    $anchor = simpletoc_sanitize_string($heading_text);
    $tag->setAttribute("id", $anchor);
  }

  // Save the HTML changes
  $content = utf8_decode($dom->saveHTML($dom->documentElement));

  return $content;
}

function filter_block($block_content, $block)
{
  $className = '';

  if ($block['blockName'] !== 'core/heading') {
    return $block_content;
  }

  $block_content = addAnchorAttribute($block_content);

  return $block_content;
}

function generateToc($headings, $attributes)
{

  $list = '';
  $html = '';
  $min_depth = 6;
  $listtype = 'ul';
  $absolute_url = '';
  $inital_depth = 6;
  $link_class = '';
  $styles = '';

  if ($attributes['remove_indent'] == true) {
    $styles = 'style="padding-left:0;list-style:none;"';
  }
  
  if ($attributes['add_smooth'] == true) {
    $link_class = 'class="smooth-scroll"';
  }

  if ($attributes['use_ol'] == true) {
    $listtype = 'ol';
  }

  if ($attributes['use_absolute_urls'] == true) {
    $absolute_url = get_permalink();
  }

  foreach ($headings as $line => $headline) {
    if ($min_depth > $headings[$line][2]) {
      // search for lowest level
      $min_depth = (int) $headings[$line][2];
      $inital_depth = $min_depth;
    }
  }

  foreach ($headings as $line => $headline) {

    $title = strip_tags($headline);
    $link = simpletoc_sanitize_string($title);
    $this_depth = (int) $headings[$line][2];
    if (isset($headings[$line + 1][2])) {
      $next_depth = (int) $headings[$line + 1][2];
    } else {
      $next_depth = '';
    }

    // skip this heading because a max depth is set.
    if ($this_depth > $attributes['max_level'] or strpos($headline, 'class="simpletoc-hidden') > 0) {
      goto closelist;
    }

    // start list 
    if ($this_depth == $min_depth) {
      $list .= "<li>\n";
    } else {
      // we are not as base level. Start opening levels until base is reached.
      for ($min_depth; $min_depth < $this_depth; $min_depth++) {
        $list .= "\n\t\t<" . $listtype . "><li>\n";
      }
    }

    $list .= "<a " . $link_class . " href=\"" . $absolute_url . "#" . $link . "\">" . $title . "</a>";

    closelist:
    // close lists
    // check if this is not the last heading
    if ($line != count($headings) - 1) {
      // do we need to close the door behind us? 
      if ($min_depth > $next_depth) {
        // If yes, how many times?
        for ($min_depth; $min_depth > $next_depth; $min_depth--) {
          $list .= "</li></" . $listtype . ">\n";
        }
      }
      if ($min_depth == $next_depth) {
        $list .= "</li>";
      }
      // last heading
    } else {
      for ($inital_depth; $inital_depth < $this_depth; $inital_depth++) {
        $list .= "</li></" . $listtype . ">\n";
      }
    }
  }

  if ($attributes['no_title'] == false) {
    $html = "<h2 class=\"simpletoc-title\">" . __("Table of Contents", "simpletoc") . "</h2>";
  }
  $html .= "<" . $listtype . " class=\"simpletoc-list\" " . $styles .">\n" . $list . "</li></" . $listtype . ">";

  return $html;
}
