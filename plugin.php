<?php
/**
 * Plugin Name:   SimpleTOC - Table of Contents Block
 * Plugin URI:    https://marc.tv/simpletoc-wordpress-inhaltsverzeichnis-plugin-gutenberg/
 * Description:   SEO-friendly Table of Contents Gutenberg block. No JavaScript and no CSS means faster loading.
 * Version:       6.9.6
 * Author:        Marc Tönsing
 * Author URI:    https://toensing.com
 * Text Domain:   simpletoc
 * License: GPL   v2 or later
 * License URI:   http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package simpletoc
 */

namespace MToensing\SimpleTOC;

require_once __DIR__ . '/simpletoc-admin-settings.php';
require_once __DIR__ . '/simpletoc-class-headline-ids.php';

/**
 * Prevents direct execution of the plugin file.
 * If a WordPress function does not exist, it means that the file has not been run by WordPress.
 */
if ( ! defined( 'ABSPATH' ) || ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Registers the SimpleTOC block, adds a filter for plugin row meta, and sets script translations.
 *
 * This function registers the SimpleTOC block by specifying the build directory and render callback function.
 * It also sets the script translations for the block editor script and adds a filter for the plugin row meta.
 */
function register_simpletoc_block() {

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'simpletoc-toc-editor-script', 'simpletoc' );
	}

	add_filter( 'plugin_row_meta', __NAMESPACE__ . '\simpletoc_plugin_meta', 10, 2 );

	register_block_type(
		__DIR__ . '/build',
		array(
			'render_callback' => __NAMESPACE__ . '\render_callback_simpletoc',
		)
	);
}

add_action( 'init', __NAMESPACE__ . '\register_simpletoc_block' );

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
add_filter(
	'load_script_translations',
	function ( $translations, $file, $handle, $domain ) {
		if ( 'simpletoc' === $domain && $translations ) {
			// List of translations that we inject into the block-editor JS.
			$dynamic_translations = array(
				'Table of Contents' => __( 'Table of Contents', 'simpletoc' ),
			);

			$changed = false;
			$obj     = json_decode( $translations, true );

			// Confirm that the translation JSON is valid.
			if ( isset( $obj['locale_data'] ) && isset( $obj['locale_data']['messages'] ) ) {
				$messages = $obj['locale_data']['messages'];

				// Inject dynamic translations, when needed.
				foreach ( $dynamic_translations as $key => $locale ) {
					if ( empty( $messages[ $key ] )
					|| ! is_array( $messages[ $key ] )
					|| ! array_key_exists( 0, $messages[ $key ] )
					|| $locale !== $messages[ $key ][0]
					) {
						$messages[ $key ] = array( $locale );
						$changed          = true;
					}
				}

				// Only modify the translations string when locales did change.
				if ( $changed ) {
					$obj['locale_data']['messages'] = $messages;
					$translations                   = wp_json_encode( $obj );
				}
			}
		}

		return $translations;
	},
	10,
	4
);

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
add_filter(
	'block_type_metadata_settings',
	function ( $settings, $metadata ) {
		if ( 'simpletoc/toc' === $metadata['name'] ) {
			$settings['attributes']['title_text']['default'] = __( 'Table of Contents', 'simpletoc' );
		}

		return $settings;
	},
	10,
	2
);

/**
 * Filter to add plugins to the TOC list for Rank Math plugin
 *
 * @param array TOC plugins.
 */
add_filter(
	'rank_math/researches/toc_plugins',
	function ( $toc_plugins ) {
		$toc_plugins['simpletoc/plugin.php'] = 'SimpleTOC';
		return $toc_plugins;
	}
);



/**
 * Adds IDs to the headings of the provided post content using a recursive block structure.
 *
 * @param string $content The content to add IDs to.
 * @return string The content with IDs added to its headings
 */
function simpletoc_add_ids_to_content( $content ) {

	$blocks = parse_blocks( $content );

	$blocks = add_ids_to_blocks_recursive( $blocks );

	$content = serialize_blocks( $blocks );

	return $content;
}

add_filter( 'the_content', __NAMESPACE__ . '\simpletoc_add_ids_to_content', 1 );

/**
 * Recursively adds IDs to the headings of a nested block structure.
 *
 * @param array $blocks The blocks to add IDs to.
 * @return array The blocks with IDs added to their headings
 */
function add_ids_to_blocks_recursive( $blocks ) {

	$supported_blocks = array(
		'core/heading',
		'generateblocks/text',
		'generateblocks/headline',
	);

	/**
	 * Filter to add supported blocks for IDs.
	 *
	 * @param array $supported_blocks The array of supported blocks.
	 */
	$supported_blocks = apply_filters( 'simpletoc_supported_blocks_for_ids', $supported_blocks );

	// Need two separate instances so that IDs aren't double coubnted.
	$inner_html_id_instance    = new SimpleTOC_Headline_Ids();
	$inner_content_id_instance = new SimpleTOC_Headline_Ids();

	foreach ( $blocks as &$block ) {
		if ( isset( $block['blockName'] ) && in_array( $block['blockName'], $supported_blocks, true ) && isset( $block['innerHTML'] ) && isset( $block['innerContent'] ) && isset( $block['innerContent'][0] ) ) {
			$block['innerHTML']       = add_anchor_attribute( $block['innerHTML'], $inner_html_id_instance );
			$block['innerContent'][0] = add_anchor_attribute( $block['innerContent'][0], $inner_content_id_instance );
		} elseif ( isset( $block['attrs']['ref'] ) ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedElseif
			// search in reusable blocks (this is not finished because I ran out of ideas.)
			// $reusable_block_id = $block['attrs']['ref'];
			// $reusable_block_content = parse_blocks(get_post($reusable_block_id)->post_content);.
		} elseif ( ! empty( $block['innerBlocks'] ) ) {
			// search in groups.
			$block['innerBlocks'] = add_ids_to_blocks_recursive( $block['innerBlocks'] );
		}
	}

	return $blocks;
}

/**
 * Renders a Table of Contents block for a post
 *
 * @param array $attributes An array of attributes for the Table of Contents block.
 * @return string The HTML output for the Table of Contents block
 */
function render_callback_simpletoc( $attributes ) {
	$is_backend  = defined( 'REST_REQUEST' ) && REST_REQUEST && 'edit' === filter_input( INPUT_GET, 'context' );
	$title_text  = $attributes['title_text'] ? esc_html( trim( $attributes['title_text'] ) ) : __( 'Table of Contents', 'simpletoc' );
	$alignclass  = ! empty( $attributes['align'] ) ? 'align' . $attributes['align'] : '';
	$class_name  = ! empty( $attributes['className'] ) ? wp_strip_all_tags( $attributes['className'] ) : '';
	$title_level = $attributes['title_level'];

	$wrapper_enabled = apply_filters( 'simpletoc_wrapper_enabled', false ) || true === (bool) get_option( 'simpletoc_wrapper_enabled', false ) || true === (bool) get_option( 'simpletoc_accordion_enabled', false );

	$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'simpletoc' ) );
	$pre_html      = ( ! empty( $class_name ) || $wrapper_enabled || $attributes['accordion'] || $attributes['wrapper'] ) ? '<div role="navigation" aria-label="' . __( 'Table of Contents', 'simpletoc' ) . '" ' . $wrapper_attrs . '>' : '';
	$post_html     = ( ! empty( $class_name ) || $wrapper_enabled || $attributes['accordion'] || $attributes['wrapper'] ) ? '</div>' : '';

	$post   = get_post();
	$blocks = ! is_null( $post ) && ! is_null( $post->post_content ) ? parse_blocks( $post->post_content ) : '';

	$headings       = array_reverse( filter_headings_recursive( $blocks ) );
	$headings       = simpletoc_add_pagenumber( $blocks, $headings );
	$headings_clean = array_map( 'trim', $headings );
	$toc_html       = generate_toc( $headings_clean, $attributes );

	if ( empty( $blocks ) ) {
		return get_empty_blocks_message( $is_backend, $attributes, $title_level, $alignclass, $title_text, __( 'No blocks found.', 'simpletoc' ), __( 'Save or update post first.', 'simpletoc' ) );
	}

	if ( empty( $headings_clean ) ) {
		return get_empty_blocks_message( $is_backend, $attributes, $title_level, $alignclass, $title_text, __( 'No headings found.', 'simpletoc' ), __( 'Save or update post first.', 'simpletoc' ) );
	}

	if ( empty( $toc_html ) ) {
		return get_empty_blocks_message( $is_backend, $attributes, $title_level, $alignclass, $title_text, __( 'No headings found.', 'simpletoc' ), __( 'Check minimal and maximum level block settings.', 'simpletoc' ) );
	}

	return $pre_html . $toc_html . $post_html;
}

/**
 * Generates an HTML message for empty blocks cases in the Table of Contents.
 *
 * @param bool   $is_backend    Indicates if the request is from the backend (i.e., the WordPress editor).
 * @param array  $attributes    An array of attributes for the Table of Contents block.
 * @param int    $title_level   The heading level for the Table of Contents title.
 * @param string $alignclass    The CSS class for alignment of the Table of Contents block.
 * @param string $title_text    The text for the Table of Contents title.
 * @param string $warning_text1 The first part of the warning message to be displayed.
 * @param string $warning_text2 The second part of the warning message to be displayed.
 *
 * @return string The HTML output for the empty blocks message.
 */
function get_empty_blocks_message( $is_backend, $attributes, $title_level, $alignclass, $title_text, $warning_text1, $warning_text2 ) {
	$html = '';

	if ( $is_backend ) {
		$html .= sprintf( '<h%d class="simpletoc-title %s">%s</h%d>', $title_level, $alignclass, $title_text, $title_level );
		$html .= sprintf( '<p class="components-notice is-warning %s">%s %s</p>', $alignclass, $warning_text1, $warning_text2 );
	}

	return $html;
}

/**
 * Adds page numbers to headings in the provided blocks array.
 *
 * @param array $blocks The array of blocks to process.
 * @param array $headings The array of headings to add page numbers to.
 * @return array The modified headings array with page numbers added.
 */
function simpletoc_add_pagenumber( $blocks, $headings ) {
	$pages = 1;

	if ( ! is_array( $blocks ) ) {
		return $headings;
	}

	foreach ( $blocks as $block => $inner_block ) {
		// count nextpage blocks.
		if ( isset( $blocks[ $block ]['blockName'] ) && 'core/nextpage' === $blocks[ $block ]['blockName'] ) {
			++$pages;
		}

		if ( isset( $blocks[ $block ]['blockName'] ) && 'core/heading' === $blocks[ $block ]['blockName'] ) {
			// make sure its a headline.
			foreach ( $headings as $heading => &$inner_heading ) {
				if ( $inner_heading === $blocks[ $block ]['innerHTML'] ) {
					$inner_heading = preg_replace( '/(<h1|<h2|<h3|<h4|<h5|<h6)/i', '$1 data-page="' . $pages . '"', $blocks[ $block ]['innerHTML'] );
				}
			}
		}
	}
	return $headings;
}

/**
 * Return all headings with a recursive walk through all blocks.
 * This includes groups and reusable block with groups within reusable blocks.
 *
 * @param array[] $blocks The blocks to filter headings from.
 * @return array[]
 */
function filter_headings_recursive( $blocks ) {
	$arr = array();

	if ( ! is_array( $blocks ) ) {
		return $arr;
	}

	// allow developers to ignore specific blocks.
	$ignored_blocks = apply_filters( 'simpletoc_excluded_blocks', array() );

	foreach ( $blocks as $inner_block ) {
		if ( is_array( $inner_block ) ) {
			// if block is ignored, skip.
			if ( isset( $inner_block['blockName'] ) && in_array( $inner_block['blockName'], $ignored_blocks, true ) ) {
				continue;
			}

			if ( isset( $inner_block['attrs']['ref'] ) ) {
				// search in reusable blocks.
				$post = get_post( $inner_block['attrs']['ref'] );
				if ( $post ) {
					$e_arr = parse_blocks( $post->post_content );
					$arr   = array_merge( filter_headings_recursive( $e_arr ), $arr );
				}
			} else {
				// search in groups.
				$arr = array_merge( filter_headings_recursive( $inner_block ), $arr );
			}
		} else {
			if ( isset( $blocks['blockName'] ) && ( 'core/heading' === $blocks['blockName'] ) && 'core/heading' !== $inner_block ) {
				// make sure it's a headline.
				if ( preg_match( '/(<h1|<h2|<h3|<h4|<h5|<h6)/i', $inner_block ) ) {
					$arr[] = $inner_block;
				}
			}

			$supported_third_party_blocks = array(
				'generateblocks/headline', /* GenerateBlocks 1.x */
				'generateblocks/text', /* GenerateBlocks 2.0 */
			);

			/**
			 * Filter to add supported third party blocks.
			 *
			 * @param array $supported_third_party_blocks The array of supported third party blocks.
			 * @return array The modified array of supported third party blocks.
			 */
			$supported_third_party_blocks = apply_filters(
				'simpletoc_supported_third_party_blocks',
				$supported_third_party_blocks
			);

			if ( isset( $blocks['blockName'] ) && in_array( $blocks['blockName'], $supported_third_party_blocks, true ) && 'core/heading' !== $inner_block ) {
				// make sure it's a headline.
				if ( preg_match( '/(<h1|<h2|<h3|<h4|<h5|<h6)/i', $inner_block ) ) {
					$arr[] = $inner_block;
				}
			}
		}
	}

	return $arr;
}

/**
 * Sanitizes a string to be used as an anchor attribute in HTML by removing punctuation, non-breaking spaces, umlauts, and accents,
 * and replacing whitespace and other characters with dashes.
 *
 * @param string $string_to_sanitize The input string to be sanitized.
 * @return string The sanitized string encoded for use in a URL.
 */
function simpletoc_sanitize_string( $string_to_sanitize ) {
	// remove punctuation.
	$zero_punctuation = preg_replace( '/\p{P}/u', '', $string_to_sanitize );
	// remove non-breaking spaces.
	$html_wo_nbs = str_replace( '&nbsp;', ' ', $zero_punctuation );
	// remove umlauts and accents.
	$string_without_accents = remove_accents( $html_wo_nbs );
	// Sanitizes a title, replacing whitespace and a few other characters with dashes.
	$sanitized_string = sanitize_title_with_dashes( $string_without_accents );
	// Encode for use in an url.
	$urlencoded = rawurlencode( $sanitized_string );
	return $urlencoded;
}

/**
 * Add additional plugin meta links to the SimpleTOC plugin page.
 *
 * @param array  $links An array of plugin meta links.
 * @param string $file The plugin file path.
 * @return array The modified array of plugin meta links.
 */
function simpletoc_plugin_meta( $links, $file ) {

	if ( false !== strpos( $file, 'simpletoc' ) ) {
		$links = array_merge( $links, array( '<a href="https://wordpress.org/support/plugin/simpletoc">' . esc_html__( 'Support', 'simpletoc' ) . '</a>' ) );
		$links = array_merge( $links, array( '<a href="https://marc.tv/out/donate">' . esc_html__( 'Donate', 'simpletoc' ) . '</a>' ) );
		$links = array_merge( $links, array( '<a href="https://wordpress.org/support/plugin/simpletoc/reviews/#new-post">' . esc_html__( 'Write a review', 'simpletoc' ) . '&nbsp;⭐️⭐️⭐️⭐️⭐️</a>' ) );
	}

	return $links;
}

/**
 * Adds an ID attribute to all Heading tags in the provided HTML.
 *
 * @param string                 $html The HTML content to modify.
 * @param SimpleTOC_Headline_Ids $headline_class_instance The instance of the SimpleTOC_Headline_Ids class.
 * @return string The modified HTML content with ID attributes added to the Heading tags
 */
function add_anchor_attribute( $html, $headline_class_instance = null ) {

	// remove non-breaking space entites from input HTML.
	$html_wo_nbs = str_replace( '&nbsp;', ' ', $html );

	// Thank you Nick Diego.
	if ( ! $html_wo_nbs ) {
		return $html;
	}

	libxml_use_internal_errors( true );
	$dom = new \DOMDocument();
	try {
		$dom->loadHTML( '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $html_wo_nbs, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	} catch ( \Exception $e ) {
		return $html;
	}

	// use xpath to select the Heading html tags.
	$xpath = new \DOMXPath( $dom );
	$tags  = $xpath->evaluate( '//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]' );

	// Loop through all the found tags.
	foreach ( $tags as $tag ) {
		// if tag already has an attribute "id" defined, no need for creating a new one.
		if ( ! empty( $tag->getAttribute( 'id' ) ) ) {
			continue;
		}
		// Set id attribute.
		$heading_text = trim( wp_strip_all_tags( $html ) );
		$anchor       = $headline_class_instance->get_headline_anchor( $heading_text );
		$tag->setAttribute( 'id', $anchor );
	}

	// Save the HTML changes.
	$content = $dom->saveHTML( $dom->documentElement ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	return $content;
}

/**
 * Generates a table of contents based on the provided headings and attributes
 *
 * @param array $headings An array of headings to include in the table of contents.
 * @param array $attributes An array of attributes to customize the output.
 * @return string The generated table of contents as HTML
 */
function generate_toc( $headings, $attributes ) {
	$list                        = '';
	$html                        = '';
	$min_depth                   = 6;
	$initial_depth               = 6;
	$align_class                 = isset( $attributes['align'] ) ? 'align' . $attributes['align'] : '';
	$styles                      = $attributes['remove_indent'] ? 'style="padding-left:0;list-style:none;"' : '';
	$list_type                   = $attributes['use_ol'] ? 'ol' : 'ul';
	$global_absolut_urls_enabled = get_option( 'simpletoc_absolute_urls_enabled', false );
	$absolute_url                = $attributes['use_absolute_urls'] || $global_absolut_urls_enabled ? get_permalink() : '';

	list($min_depth, $initial_depth) = find_min_depth( $headings, $attributes );

	$item_count   = 0;
	$headline_ids = new SimpleTOC_Headline_Ids();
	foreach ( $headings as $line => $headline ) {
		$this_depth       = (int) $headings[ $line ][2];
		$next_depth       = isset( $headings[ $line + 1 ][2] ) ? (int) $headings[ $line + 1 ][2] : '';
		$exclude_headline = should_exclude_headline( $headline, $attributes, $this_depth );
		$title            = trim( wp_strip_all_tags( $headline ) );
		$custom_id        = extract_id( $headline );
		$link             = $custom_id ? $custom_id : $headline_ids->get_headline_anchor( $title );
		if ( ! $exclude_headline ) {
			++$item_count;
			open_list( $list, $list_type, $min_depth, $this_depth );
			$page  = get_page_number_from_headline( $headline );
			$list .= '<a href="' . $absolute_url . $page . '#' . $link . '">' . $title . '</a>' . PHP_EOL;

		}
		close_list( $list, $list_type, $min_depth, $attributes['min_level'], $attributes['max_level'], $next_depth, $line, count( $headings ) - 1, $initial_depth, $this_depth );

	}

	$html = add_accordion_start( $html, $attributes, $item_count, $align_class );
	$html = add_hidden_markup_start( $html, $attributes, $item_count, $align_class );
	$html = add_smooth( $html, $attributes );

	// Add the table of contents list to the output if the list is not empty.
	if ( ! empty( $list ) ) {
		$html_class = 'simpletoc-list';
		if ( ! empty( $align_class ) ) {
			$html_class .= " $align_class";
		}

		$html_style = '';
		if ( ! empty( $styles ) ) {
			$html_style = " $styles";
		}

		$html .= "<$list_type class=\"$html_class\"$html_style>\n$list</li></$list_type>";
	}

	$html = add_accordion_end( $html, $attributes );
	$html = add_hidden_markup_end( $html, $attributes );

	// return an emtpy string if stripped result is empty.
	if ( empty( trim( wp_strip_all_tags( $html ) ) ) ) {
		$html = '';
	}

	return $html;
}

/**
 * Finds the minimum depth level of headings in the provided array and adjusts it based on the provided attributes
 *
 * @param array $headings An array of headings to search through.
 * @param array $attributes An array of attributes to adjust the minimum depth level.
 * @return array An array containing the minimum depth level and the initial depth level.
 */
function find_min_depth( $headings, $attributes ) {
	$min_depth     = 6;
	$initial_depth = 6;

	foreach ( $headings as $line => $headline ) {
		if ( $min_depth > $headings[ $line ][2] ) {
			$min_depth     = (int) $headings[ $line ][2];
			$initial_depth = $min_depth;
		}
	}

	if ( $attributes['min_level'] > $min_depth ) {
		$min_depth     = $attributes['min_level'];
		$initial_depth = $min_depth;
	}

	return array( $min_depth, $initial_depth );
}

/**
 * Determines if a given headline should be excluded based on the provided attributes.
 *
 * @param string $headline The headline to check for exclusion.
 * @param array  $attributes An array of attributes to use for exclusion.
 * @param int    $this_depth The depth level of the headline.
 * @return bool True if the headline should be excluded, false otherwise.
 */
function should_exclude_headline( $headline, $attributes, $this_depth ) {
	$exclude_headline = false;
	preg_match( '/class="([^"]+)"/', $headline, $matches );
	if ( ! empty( $matches[1] ) && strpos( $matches[1], 'simpletoc-hidden' ) !== false ) {
		$exclude_headline = true;
	}

	return ( $this_depth > $attributes['max_level'] || $exclude_headline || $this_depth < $attributes['min_level'] );
}

/**
 * The open_list function appends a new list item to the global $list variable, adding necessary opening tags if needed to maintain the correct nesting of the list.
 *
 * @param string &$list_to_append_to The global list variable to append the new list item to.
 * @param string $list_type The type of list to be created, either "ul" (unordered list) or "ol" (ordered list).
 * @param int    &$min_depth The minimum depth of headings that should be included in the table of contents.
 * @param int    $this_depth The depth of the current heading being processed.
 * @return void The function modifies the input $list_to_append_to variable directly.
 */
function open_list( &$list_to_append_to, $list_type, &$min_depth, $this_depth ) {
	if ( $this_depth === $min_depth ) {
		$list_to_append_to .= '<li>';
	} else {
		for ( $min_depth; $min_depth < $this_depth; $min_depth++ ) {
			$list_to_append_to .= "\n<" . $list_type . "><li>\n";
		}
	}
}

/**
 * Closes an HTML list tag and updates the list string and minimum depth variable as necessary.
 *
 * @param string   $list_to_append_to A reference to the list string being built.
 * @param string   $list_type The type of list tag being used (ul or ol).
 * @param int      $min_depth A reference to the minimum depth variable.
 * @param int      $min_level The minimum depth level of the headings.
 * @param int      $max_level Maximum depth setting, which is a high number like 6.
 * @param int|null $next_depth The depth of the next list item, or null if this is the last item.
 * @param int      $line The index of the current list item.
 * @param int      $last_line The index of the last list item.
 * @param int      $initial_depth The initial depth of the list.
 * @param int      $this_depth The depth of the current list item.
 * @return void
 */
function close_list( &$list_to_append_to, $list_type, &$min_depth, $min_level, $max_level, $next_depth, $line, $last_line, $initial_depth, $this_depth ) {
	if ( $line !== $last_line ) {
		$list_to_append_to .= PHP_EOL;
		if ( $next_depth < $this_depth ) {
			// Next heading goes back shallower in the ToC!
			if ( $next_depth >= $min_level ) {
				// Next heading is within min depth bounds and WILL get ToC'd
				// Close this item and step back shallower in the ToC.
				for ( $min_depth; $min_depth > $next_depth; $min_depth-- ) {
					$list_to_append_to .= "</li>\n</" . $list_type . ">\n";
				}
			} else { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedElse
				// SKIP CLOSING! Next heading won't be included in the ToC at all.
			}
		} elseif ( $next_depth === $this_depth ) {
			// Next heading is exactly as deep. Not going shallower or deeper in the ToC hierarchy.
			// E.g. this is h3, next is h3.
			if ( $next_depth < $min_level ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
				// E.g. this is h3, next is h3, min is h2
				// This heading didn't open a ToC item. Nothing to close.
			} else {
				// SKIP CLOSING! Next heading will open a new sub-list in the ToC.
				$list_to_append_to .= "</li>\n";
			}
		} else { // phpcs:ignore.
			// Next heading is deeper in the ToC.
			if ( $next_depth <= $max_level ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
				// Next deeper heading is within bounds and will open a new sub-list. Leave this one open.
				// E.g. this is h3, next is h4, min is h2, max is h5.
			} else { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedElse
				// Next heading is too deep and will be ignored. We'll close out coming up or finishing the ToC.
				// E.g. this is h3, next is h4, max is h3.
			}
		}
	} else {
		// This is the last line of the ToC. Close out the whole thing.
		// IMPORTANT NOTE: The overall ToC list will be wrapped in a list element and closed out.
		for ( $initial_depth; $initial_depth < $this_depth; $initial_depth++ ) {
			$list_to_append_to .= "</li>\n</" . $list_type . ">\n";
		}
	}
}

/**
 * Adds smooth scrolling styles to the output HTML, if enabled by global option or block attribute.
 *
 * @param string $html The HTML string to which the styles will be added.
 * @param array  $attributes An array of block attributes.
 * @return string The modified HTML string with the added smooth scrolling styles.
 */
function add_smooth( $html, $attributes ) {
	// Add smooth scrolling styles, if enabled by global option or block attribute.
	$is_smooth_enabled = $attributes['add_smooth'] || true === (bool) get_option( 'simpletoc_smooth_enabled', false );
	$html             .= $is_smooth_enabled ? '<style>html { scroll-behavior: smooth; }</style>' : '';

	return $html;
}

/**
 * Enqueues the necessary CSS and JS files for the accordion functionality on the frontend.
 */
function enqueue_accordion_frontend() {
	wp_enqueue_script(
		'simpletoc-accordion',
		plugin_dir_url( __FILE__ ) . 'assets/accordion.js',
		array(),
		'6.9.0',
		true
	);

	wp_enqueue_style(
		'simpletoc-accordion',
		plugin_dir_url( __FILE__ ) . 'assets/accordion.css',
		array(),
		'6.9.0'
	);
}

/**
 * Adds the opening HTML tag(s) for the hidden markup element and the table of contents title, if applicable.
 *
 * @param string $html The HTML string to add the opening tag(s) to.
 * @param array  $attributes The attributes of the table of contents block.
 * @param int    $itemcount The number of items in the table of contents.
 * @param string $alignclass The alignment class for the table of contents block.
 */
function add_hidden_markup_start( $html, $attributes, $itemcount, $alignclass ) { // phpcs:ignore.
	$is_hidden_enabled = $attributes['hidden'];

	if ( $is_hidden_enabled ) {
		$title_text   = $attributes['title_text'] ? esc_html( trim( $attributes['title_text'] ) ) : esc_html__( 'Table of Contents', 'simpletoc' );
		$hidden_start = '<details id="simpletoc-details" class="simpletoc" aria-labelledby="simpletoc-title">
        <summary style="cursor: pointer;">' . $title_text . '</summary>';
		$html        .= $hidden_start;
	}

	// If there are no items in the table of contents, return an empty string.
	if ( $itemcount < 1 ) {
		return '';
	}

	return $html;
}

/**
 * Adds the closing HTML tag(s) for the hidden markup element if the hidden markup is enabled.
 *
 * @param string $html The HTML string to add the closing tag(s) to.
 * @param array  $attributes The attributes of the table of contents block.
 * @return string The modified HTML string with the closing tag(s) added.
 */
function add_hidden_markup_end( $html, $attributes ) {
	$is_hidden_enabled = $attributes['hidden'];

	if ( $is_hidden_enabled ) {
		$html .= '</details>';
	}

	return $html;
}

/**
 * Adds the opening HTML tag(s) for the accordion element and the table of contents title, if applicable.
 *
 * @param string $html The HTML string to add the opening tag(s) to.
 * @param array  $attributes The attributes of the table of contents block.
 * @param int    $itemcount The number of items in the table of contents.
 * @param string $alignclass The alignment class for the table of contents block.
 */
function add_accordion_start( $html, $attributes, $itemcount, $alignclass ) {
	// Check if accordion is enabled either through the function arguments or the options.
	$is_accordion_enabled = $attributes['accordion'] || true === (bool) get_option( 'simpletoc_accordion_enabled', false );
	$is_hidden_enabled    = $attributes['hidden'];

	// Start and end HTML for accordion, if enabled.
	$accordion_start = '';
	if ( $is_accordion_enabled ) {
		enqueue_accordion_frontend();
		$title_text      = $attributes['title_text'] ? esc_html( trim( $attributes['title_text'] ) ) : esc_html__( 'Table of Contents', 'simpletoc' );
		$accordion_start = '<h2 style="margin: 0;"><button type="button" aria-expanded="false" aria-controls="simpletoc-content-container" class="simpletoc-collapsible">' . $title_text . '<span class="simpletoc-icon" aria-hidden="true"></span></button></h2><div id="simpletoc-content-container" class="simpletoc-content">';
	}

	// Add the accordion start HTML to the output.
	$html .= $accordion_start;

	// Add the table of contents title, if not hidden and not in accordion mode.
	$show_title = ! $attributes['no_title'] && ! $is_accordion_enabled && ! $is_hidden_enabled;
	if ( $show_title ) {
		$title_tag  = $attributes['title_level'] > 0 ? "h{$attributes['title_level']}" : 'p';
		$title_tag  = wp_strip_all_tags( $title_tag );
		$html_class = 'simpletoc-title';

		if ( ! empty( $alignclass ) ) {
			$html_class .= " $alignclass";
		}

		$html = "<$title_tag class=\"$html_class\">{$attributes["title_text"]}</$title_tag>\n";
	}

	// If there are no items in the table of contents, return an empty string.
	if ( $itemcount < 1 ) {
		return '';
	}

	return $html;
}

/**
 * Adds the closing HTML tag(s) for the accordion element if the accordion is enabled.
 *
 * @param string $html The HTML string to add the closing tag(s) to.
 * @param array  $attributes The attributes of the table of contents block.
 * @return string The modified HTML string with the closing tag(s) added
 */
function add_accordion_end( $html, $attributes ) {
	// Check if accordion is enabled either through the function arguments or the options.
	$is_accordion_enabled = $attributes['accordion'] || true === (bool) get_option( 'simpletoc_accordion_enabled', false );

	if ( $is_accordion_enabled ) {
		$html .= '</div>';
	}

	return $html;
}

/**
 * Extracts the ID value from the provided heading HTML string.
 *
 * @param string $headline The heading HTML string to extract the ID value from.
 * @return mixed Returns the extracted ID value, or false if no ID value is found.
 */
function extract_id( $headline ) {
	$pattern = '/id="([^"]*)"/';
	preg_match( $pattern, $headline, $matches );
	$id_value = $matches[1] ?? false;

	if ( false !== $id_value ) {
		return $id_value;
	}

	return false;
}

/**
 * Gets the page number from a headline string.
 *
 * @param string $headline The headline string.
 * @return string The page number (in the format "X/") if it exists and is greater than 1, or an empty string otherwise.
 */
function get_page_number_from_headline( $headline ) {
	$dom = new \DOMDocument();

	try {
		$dom->loadHTML( '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $headline, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	} catch ( \Exception $e ) {
		return '';
	}

	$xpath = new \DOMXPath( $dom );
	$nodes = $xpath->query( '//*/@data-page' );

	if ( isset( $nodes[0] ) && $nodes[0]->nodeValue > 1 ) {
		$page_number = $nodes[0]->nodeValue . '/';
		return esc_html( $page_number );
	} else {
		return '';
	}
}
