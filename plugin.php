<?php

/**
 * Plugin Name:   SimpleTOC - Table of Contents Block
 * Plugin URI:    https://marc.tv/simpletoc-wordpress-inhaltsverzeichnis-plugin-gutenberg/
 * Description:   SEO-friendly Table of Contents Gutenberg block. No JavaScript and no CSS means faster loading.
 * Version:       5.0.57
 * Author:        Marc Tönsing
 * Author URI:    https://marc.tv
 * Text Domain:   simpletoc
 * License: GPL   v2 or later
 * License URI:   http://www.gnu.org/licenses/gpl-2.0.html
 *
 */


 require_once plugin_dir_path(__FILE__) . 'simpletoc-admin-settings.php';

/**
 * Prevents direct execution of the plugin file.
 * If a WordPress function does not exist, it means that the file has not been run by WordPress.
 */
if ( ! function_exists( 'add_filter' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit;
}

/**
 * Initialise frontend and backend and register block
 **/
function register_simpletoc_block()
{

  if ( function_exists( 'wp_set_script_translations' ) ) {
    wp_set_script_translations( 'simpletoc-toc-editor-script', 'simpletoc' );
  }

  add_filter('plugin_row_meta', __NAMESPACE__ . '\\simpletoc_plugin_meta', 10, 2);

  register_block_type( __DIR__ . '/build', [
    'render_callback' => __NAMESPACE__ . '\\render_callback_simpletoc'
  ]);

}

add_action( 'init', 'register_simpletoc_block' );

/**
 * Inject potentially missing translations into the block-editor i18n
 * collection.
 *
 * This keeps the plugin backwards compatible, in case the user did not
 * update translations on their website (yet).
 *
 * @param string|false|null $translations JSON-encoded translation data. Default null.
 * @param string|false      $file         Path to the translation file to load. False if there isn't one.
 * @param string            $handle       Name of the script to register a translation domain to.
 * @param string            $domain       The text domain.
 *
 * @return string|false|null JSON string
 */
add_filter( 'load_script_translations', function($translations, $file, $handle, $domain) {
  if ( 'simpletoc' === $domain && $translations ) {
    // List of translations that we inject into the block-editor JS.
    $dynamic_translations = [
      'Table of Contents' => __( 'Table of Contents', 'simpletoc' ),
    ];

    $changed = false;
    $obj = json_decode( $translations, true );

    // Confirm that the translation JSON is valid.
    if ( isset( $obj['locale_data'] ) && isset( $obj['locale_data']['messages'] ) ) {
      $messages = $obj['locale_data']['messages'];

      // Inject dynamic translations, when needed.
      foreach ( $dynamic_translations as $key => $locale ) {
        if (
          empty( $messages[ $key ] )
          || ! is_array( $messages[$key] )
          || ! array_key_exists( 0, $messages[ $key ] )
          || $locale !== $messages[ $key ][0]
        ) {
          $messages[ $key ] = [ $locale ];
          $changed = true;
        }
      }

	  // Only modify the translations string when locales did change.
      if ( $changed ) {
        $obj['locale_data']['messages'] = $messages;
        $translations = wp_json_encode( $obj );
      }
    }
  }

  return $translations;
}, 10, 4 );

/**
 * Sets the default value of translatable attributes.
 *
 * Values inside block.json are static strings that are not translated. This
 * filter inserts relevant translations i
 *
 * @param array $settings Array of determined settings for registering a block type.
 * @param array $metadata Metadata provided for registering a block type.
 *
 * @return array Modified settings array.
 */
add_filter( 'block_type_metadata_settings', function( $settings, $metadata ) {
  if ( 'simpletoc/toc' === $metadata['name'] ) {
    $settings['attributes']['title_text']['default'] = __( 'Table of Contents', 'simpletoc' );
  }

  return $settings;
}, 10, 2);

/**
 * Filter to add plugins to the TOC list for Rank Math plugin
 *
 * @param array TOC plugins.
 */

add_filter('rank_math/researches/toc_plugins', function ($toc_plugins) {
  $toc_plugins['simpletoc/plugin.php'] = 'SimpleTOC';
  return $toc_plugins;
});

add_filter( 'the_content', 'simpletoc_addIDstoContent', 1 );

function simpletoc_addIDstoContent( $content ) {

  $blocks = parse_blocks( $content );

  $blocks = addIDstoBlocks_recursive( $blocks );

  $content = serialize_blocks( $blocks );

  return $content;
}

function addIDstoBlocks_recursive( $blocks ) {

  foreach ( $blocks as &$block ) {
     if (isset($block['blockName']) && ( $block['blockName'] === 'core/heading' || $block['blockName'] === 'generateblocks/headline') && isset($block['innerHTML']) && isset($block['innerContent']) && isset($block['innerContent'][0]) ){
        $block['innerHTML'] = addAnchorAttribute($block['innerHTML']);
        $block['innerContent'][0] = addAnchorAttribute($block['innerContent'][0]);
      } else if( isset($block['attrs']['ref']) ){
        // search in reusable blocks (this is not finished because I ran out of ideas.)
        // $reusable_block_id = $block['attrs']['ref'];
        // $reusable_block_content = parse_blocks(get_post($reusable_block_id)->post_content);
      } else if ( ! empty( $block['innerBlocks'] ) ) {
        // search in groups
        $block['innerBlocks'] = addIDstoBlocks_recursive( $block['innerBlocks'] );
      }
  }

  return $blocks;
}

/**
 * Render block output
 *
 */

function render_callback_simpletoc( $attributes )
{

  $is_backend = defined('REST_REQUEST') && true === REST_REQUEST && 'edit' === filter_input(INPUT_GET, 'context');

  $title_text = esc_html( trim( $attributes['title_text'] ) );
  if ( ! $title_text ) {
    $title_text = __('Table of Contents', 'simpletoc');
  }

  $alignclass = '';
  if ( isset ($attributes['align']) ) {
    $align = $attributes['align'];
    $alignclass = 'align' . $align;
  }

  $className = '';
  if ( isset( $attributes['className'] ) ) {
    $className = strip_tags(htmlspecialchars($attributes['className'] ));
  }

  $pre_html = '';
  $post_html = '';
  $title_level = $attributes['title_level'];

  // By default, the wrapper is not enabled because it causes problems on some themes
  $wrapper_enabled = apply_filters( 'simpletoc_wrapper_enabled', false );

  // Check if filter was set externally 
  if (isset($wrapper_enabled)){ 
    // Check if the wrapper is enabled in the settings
    if (get_option('simpletoc_wrapper_enabled') == 1) {
      $wrapper_enabled = true;
    }
  }

  if (get_option('simpletoc_accordion_enabled') == 1) {
    $wrapper_enabled = true;
  }

  if ( $className !== '' || $wrapper_enabled || $attributes['accordion'] || $attributes['wrapper']  ) {
    $wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'simpletoc' ] );
    $pre_html = '<div role="navigation" aria-label="'. __('Table of Contents', 'simpletoc') . '" ' . $wrapper_attrs . '>';
    $post_html = '</div>';
  }

  $post = get_post();
  if ( is_null($post) || is_null($post->post_content) ) {
    $blocks = '';
  } else {
    $blocks = parse_blocks($post->post_content);
  }

  if ( empty($blocks) ) {
    $html = '';
    if( $is_backend == true ) {
      if ($attributes['no_title'] === false ) {
        $html = '<h' . $title_level .' class="simpletoc-title ' . $alignclass . '">' . $title_text . '</h' . $title_level . '>';
      }

      $html .= '<p class="components-notice is-warning ' . $alignclass . '">' . __('No blocks found.', 'simpletoc')  . ' ' . __('Save or update post first.', 'simpletoc') . '</p>';
    }
    return $html;
  }

  $headings = array_reverse(filter_headings_recursive($blocks));

  // enrich headings with pages as a data-attribute
  $headings = simpletoc_add_pagenumber($blocks, $headings);

  $headings_clean = array_map('trim', $headings);

  if ( empty( $headings_clean ) ) {
    $html = '';
    if( $is_backend == true ) {

      if ($attributes['no_title'] == false) {
        $html = '<h' . $title_level .' class="simpletoc-title ' . $alignclass . '">' . $title_text . '</h' . $title_level . '>';
      }

      $html .= '<p class="components-notice is-warning ' . $alignclass . '">' . __('No headings found.', 'simpletoc') . ' ' . __('Save or update post first.', 'simpletoc') . '</p>';
    }
    return $html;
  }

  $toclist = generateToc($headings_clean, $attributes);

  if ( empty( $toclist ) ) {
    $html = '';
    if( $is_backend == true ) {

      if ($attributes['no_title'] == false) {
        $html = '<h' . $title_level .' class="simpletoc-title ' . $alignclass . '">' . $title_text . '</h' . $title_level . '>';
      }

      $html .= '<p class="components-notice is-warning ' . $alignclass . '">' . __('No headings found.', 'simpletoc') . ' ' . __('Check minimal and maximum level block settings.', 'simpletoc') . '</p>';
    }
    return $html;

  }

  $output = $pre_html . $toclist . $post_html;

  return $output;
}


function simpletoc_add_pagenumber( $blocks, $headings ){
  $pages = 1;

  foreach ($blocks as $block => $innerBlock) {

    // count nextpage blocks
    if (isset($blocks[$block]['blockName']) && $blocks[$block]['blockName'] === 'core/nextpage' ){
      $pages++;
    }

    if (isset($blocks[$block]['blockName']) && $blocks[$block]["blockName"] === 'core/heading') {
      // make sure its a headline.
      foreach ($headings as $heading => &$innerHeading){
        if($innerHeading == $blocks[$block]["innerHTML"]){
          $innerHeading = preg_replace("/(<h1|<h2|<h3|<h4|<h5|<h6)/i", '$1 data-page="' . $pages . '"', $blocks[$block]["innerHTML"]);
        }
      }
    }
  }
  return $headings;
}

/**
 * Return all headings with a recursive walk through all blocks.
 * This includes groups and reusable block with groups within reusable blocks.
 * @var array[] $blocks
 * @return array[]
 */
function filter_headings_recursive( array $blocks ): array {
	$arr            = [];
	// allow developers to ignore specific blocks
	$ignored_blocks = apply_filters( 'simpletoc_excluded_blocks', [] );

	foreach ( $blocks as $innerBlock ) {

		if ( is_array( $innerBlock ) ) {

			// if block is ignored, skip
			if ( isset( $innerBlock['blockName'] ) && in_array( $innerBlock['blockName'], $ignored_blocks ) ) {
				continue;
			}

			if ( isset( $innerBlock['attrs']['ref'] ) ) {
				// search in reusable blocks
				$e_arr = parse_blocks( get_post( $innerBlock['attrs']['ref'] )->post_content );
				$arr   = array_merge( filter_headings_recursive( $e_arr ), $arr );
			} else {
				// search in groups
				$arr = array_merge( filter_headings_recursive( $innerBlock ), $arr );
			}
		} else {

			if ( isset( $blocks['blockName'] ) && ( $blocks['blockName'] === 'core/heading' ) && $innerBlock !== 'core/heading' ) {
				// make sure it's a headline.
				if ( preg_match( "/(<h1|<h2|<h3|<h4|<h5|<h6)/i", $innerBlock ) ) {
					$arr[] = $innerBlock;
				}
			}

			if ( isset( $blocks['blockName'] ) && ( $blocks['blockName'] === 'generateblocks/headline' ) && $innerBlock !== 'core/heading' ) {
				// make sure it's a headline.
				if ( preg_match( "/(<h1|<h2|<h3|<h4|<h5|<h6)/i", $innerBlock ) ) {
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

function simpletoc_sanitize_string( $string )
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

function simpletoc_plugin_meta( $links, $file )
{

  if (false !== strpos($file, 'simpletoc')) {
    $links = array_merge($links, array('<a href="https://wordpress.org/support/plugin/simpletoc">' . __('Support', 'simpletoc') . '</a>'));
    $links = array_merge($links, array('<a href="https://marc.tv/out/donate">' . __('Donate', 'simpletoc') . '</a>'));
    $links = array_merge($links, array('<a href="https://wordpress.org/support/plugin/simpletoc/reviews/#new-post">' . __('Write a review', 'simpletoc') . '&nbsp;⭐️⭐️⭐️⭐️⭐️</a>'));
  }

  return $links;
}

function addAnchorAttribute( $html )
{

  // remove non-breaking space entites from input HTML
  $html_wo_nbs = str_replace("&nbsp;", " ", $html);

  // Thank you Nick Diego
  if (!$html_wo_nbs) {
    return $html;
  }

  libxml_use_internal_errors(TRUE);
  $dom = new \DOMDocument();
  @$dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $html_wo_nbs, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

  // use xpath to select the Heading html tags.
  $xpath = new \DOMXPath($dom);
  $tags = $xpath->evaluate("//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]");

  // Loop through all the found tags
  foreach ($tags as $tag) {
    // if tag already has an attribute "id" defined, no need for creating a new one
    if (!empty($tag->getAttribute('id'))) {continue;}
    // Set id attribute
    $heading_text = strip_tags($html);
    $anchor = simpletoc_sanitize_string($heading_text);
    $tag->setAttribute("id", $anchor);
  }

  // Save the HTML changes
  $content = $dom->saveHTML($dom->documentElement);

  return $content;
}

function generateToc( $headings, $attributes )
{
  $list = '';
  $html = '';
  $min_depth = 6;
  $inital_depth = 6;
  $link_class = '';
  $alignclass = isset($attributes['align']) ? 'align' . $attributes['align'] : '';
  $styles = $attributes['remove_indent'] ? 'style="padding-left:0;list-style:none;"' : '';
  $listtype = $attributes['use_ol'] ? 'ol' : 'ul';
  $absolute_url = $attributes['use_absolute_urls'] ? get_permalink() : '';

  foreach ($headings as $line => $headline) {
    if ($min_depth > $headings[$line][2]) {
      // search for lowest level
      $min_depth = (int) $headings[$line][2];
      $inital_depth = $min_depth;
    }
  }

  /* If there is a custom min level, make sure it is the baseline. */
  if ($attributes['min_level'] > $min_depth) {
    $min_depth = $attributes['min_level'];
  }

  $itemcount = 0;

  foreach ($headings as $line => $headline) {
  
    $title = strip_tags($headline);
    $link = simpletoc_sanitize_string($title);
    $this_depth = (int) $headings[$line][2];
    $next_depth = isset($headings[$line + 1][2]) ? (int) $headings[$line + 1][2] : '';
  
    // Check for `simpletoc-hidden` class to exclude the headline.
    $exclude_headline = preg_match('/class="([^"]+)"/', $headline, $matches) && strpos($matches[1], 'simpletoc-hidden') !== false;
  
    // Skip this heading if max depth is set or the headline is excluded.
    if ($this_depth > $attributes['max_level'] || $exclude_headline) {
      goto close_list;
    }
  
    // Skip this heading if min depth is set.
    if ($this_depth < $attributes['min_level']) {
      continue;
    }
  
    $itemcount++;
  
    // Start list.
    if ($this_depth == $min_depth) {
      $list .= "<li>\n";
    } else {
      // Open levels until base is reached.
      for ($min_depth; $min_depth < $this_depth; $min_depth++) {
        $list .= "\n\t\t<" . $listtype . "><li>\n";
      }
    }
  
    // Add link.
    $list .= "<a " . $link_class . " href=\"" . $absolute_url . "#" . $link . "\">" . $title . "</a>";
  
    close_list:
    // Close lists.
    if ($line != count($headings) - 1) {
      // Close levels if next depth is smaller.
      if ($min_depth > $next_depth) {
        for ($min_depth; $min_depth > $next_depth; $min_depth--) {
          $list .= "</li></" . $listtype . ">\n";
        }
      }
      // Close current level if next depth is equal.
      if ($min_depth == $next_depth) {
        $list .= "</li>";
      }
    } else {
      // Close levels for last heading.
      for ($inital_depth; $inital_depth < $this_depth; $inital_depth++) {
        $list .= "</li></" . $listtype . ">\n";
      }
    }
  }
  
  $html = addAccordion($html, $attributes, $itemcount, $listtype, $styles,$alignclass,$list);

  $html = addSmooth($html, $attributes);
 
  return $html;
}

function addSmooth($html, $attributes) {
    // Add smooth scrolling styles, if enabled by global option or block attribute
    $isSmoothEnabled = $attributes['add_smooth'] || get_option('simpletoc_smooth_enabled') == 1;
    $html .= $isSmoothEnabled ? '<style>html { scroll-behavior: smooth; }</style>' : '';

    return $html;
}

function enqueue_accordion_frontend(){
  wp_enqueue_script (
    'simpletoc-accordion',
    plugin_dir_url( __FILE__ ) . 'src/accordion.js',
    array(),
    '5.0.50',
    true
  );

  wp_enqueue_style (
    'simpletoc-accordion',
     plugin_dir_url( __FILE__ ) . 'src/accordion.css',
     array(),
     '5.0.50'
  );
}

function addAccordion($html, $attributes, $itemcount, $listtype, $styles, $alignclass, $list) {
  // Check if accordion is enabled either through the function arguments or the options
  $isAccordionEnabled = $attributes['accordion'] || get_option('simpletoc_accordion_enabled') == 1;

  // Start and end HTML for accordion, if enabled
  $accordionStart = $accordionEnd = '';
  if ($isAccordionEnabled) {
    enqueue_accordion_frontend();
    $titleText = esc_html(trim($attributes['title_text'])) ?: __('Table of Contents', 'simpletoc');
    $accordionStart = "<button type='button' class='simpletoc-collapsible'>$titleText</button>
      <div class='simpletoc-content'>";
    $accordionEnd = '</div>';
  }

  // Add the accordion start HTML to the output
  $html .= $accordionStart;

  // Add the table of contents title, if not hidden and not in accordion mode
  $showTitle = !$attributes['no_title'] && !$isAccordionEnabled;
  if ($showTitle) {
    $titleTag = $attributes['title_level'] > 0 ? "h{$attributes['title_level']}" : 'p';
    $html = "<$titleTag class='simpletoc-title $alignclass'>{$attributes['title_text']}</$titleTag>";
  }

  // Add the table of contents list to the output
  $html .= "<$listtype class='simpletoc-list $styles $alignclass'>\n$list</li></$listtype>";

  // If there are no items in the table of contents, return an empty string
  if ($itemcount < 1) {
    return '';
  }

  // Add the accordion end HTML to the output
  $html .= $accordionEnd;

  return $html;
}