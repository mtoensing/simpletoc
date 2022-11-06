<?php

/**
 * Plugin Name:   SimpleTOC - Table of Contents Block
 * Plugin URI:    https://marc.tv/simpletoc-wordpress-inhaltsverzeichnis-plugin-gutenberg/
 * Description:   SEO-friendly Table of Contents Gutenberg block. No JavaScript and no CSS means faster loading.
 * Version:       5.0.45
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

  if ( $className !== ''|| $wrapper_enabled || $attributes['accordion']  ) {
    $wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'simpletoc' ] );
    $pre_html = "<div $wrapper_attrs>";
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
  @$dom->loadHTML($html_wo_nbs, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

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
  $content = utf8_decode($dom->saveHTML($dom->documentElement));

  return $content;
}

function generateToc( $headings, $attributes )
{

  $list = '';
  $html = '';
  $min_depth = 6;
  $listtype = 'ul';
  $absolute_url = '';
  $inital_depth = 6;
  $link_class = '';
  $styles = '';

  $title_text = esc_html( trim( $attributes['title_text'] ) );
  if ( ! $title_text ) {
    $title_text = __('Table of Contents', 'simpletoc');
  }

  $alignclass = '';
  if ( isset ($attributes['align']) ) {
    $align = $attributes['align'];
    $alignclass = 'align' . $align;
  }

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

  /* If there is a custom min level, make sure it is the baseline. */
  if ($attributes['min_level'] > $min_depth) {
    $min_depth = $attributes['min_level'];
  }

  $itemcount = 0;

  foreach ($headings as $line => $headline) {

    $title = strip_tags($headline);
    $page = '';
    $dom = new \DOMDocument();
    @$dom->loadHTML($headline, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new \DOMXPath($dom);
    $nodes = $xpath->query('//*/@data-page');
    $accordion_start = '';
    $accordion_end = '';
    $html = '';
    $title_level = $attributes['title_level'];

    if ( isset($nodes[0] ) && $nodes[0]->nodeValue > 1) {
      $page = $nodes[0]->nodeValue . '/';
      $absolute_url = get_permalink();
    }

    $link = simpletoc_sanitize_string($title);
    if (isset($nodes[0]) && !empty($nodes[0]->ownerElement->getAttribute('id'))) {
      // if the node already has an attribute id, use that as anchor
      $link = $nodes[0]->ownerElement->getAttribute('id');
    }

    $this_depth = (int) $headings[$line][2];
    if (isset($headings[$line + 1][2])) {
      $next_depth = (int) $headings[$line + 1][2];
    } else {
      $next_depth = '';
    }

	// Get class attributes and check for `simpletoc-hidden` to exclude the headline.
	preg_match( '/class="([^"]+)"/', $headline, $matches );
	$exclude_headline = false;
	if ( isset( $matches[1] ) ) {
		$headline_classes = explode( ' ', $matches[1] );
		if ( in_array( 'simpletoc-hidden', $headline_classes, true ) ) {
			$exclude_headline = true;
		}
	}

    // skip this heading because a max depth is set.
    if ($this_depth > $attributes['max_level'] or $exclude_headline) {
      goto closelist;
    }

    // skip this heading because a min depth is set.
    if( $this_depth < $attributes['min_level'] ){
      continue;
    }

    $itemcount++;

    // start list
    if ($this_depth == $min_depth) {
      $list .= "<li>\n";
    } else {
      // we are not as base level. Start opening levels until base is reached.
      for ($min_depth; $min_depth < $this_depth; $min_depth++) {
        $list .= "\n\t\t<" . $listtype . "><li>\n";
      }
    }

    $list .= "<a " . $link_class . " href=\"" . $absolute_url . esc_html($page) . "#" . $link . "\">" . $title . "</a>";

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

  if ( $attributes['accordion'] === true ) {

    wp_enqueue_script (
      'simpletoc-accordion',
      plugin_dir_url( __FILE__ ) . 'src/accordion.js',
      array(),
      '5.0.45',
      true
    );
  
    wp_enqueue_style (
      'simpletoc-accordion',
       plugin_dir_url( __FILE__ ) . 'src/accordion.css', 
       array(),
       '5.0.45'
    );

    $accordion_start = '<button type="button" class="simpletoc-collapsible">' . $title_text . '</button>
    <div class="simpletoc-content">';
    
    /* class simpletoc-content closing div  */
    $accordion_end = '</div>'; 
  }

  $html .= $accordion_start;

  if ($attributes['no_title'] === false && $attributes['accordion'] === false) {
    if( $title_level > 0 ) {
      $html = '<h' . $title_level .' class="simpletoc-title ' . $alignclass . '">' . $title_text . '</h' . $title_level . '>';
    } else {
      $html = '<p class="simpletoc-title ' . $alignclass . '"><strong>' . $title_text . '</strong></p>';
    }
  }
  $html .= "<" . $listtype . " class=\"simpletoc-list\" " . $styles ."  " . $alignclass .">\n" . $list . "</li></" . $listtype . ">";

  if($itemcount < 1) {
    $html = '';
  }

  $html .= $accordion_end;
  return $html;
}


