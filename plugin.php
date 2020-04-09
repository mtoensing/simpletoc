<?php
/**
 * Plugin Name: SimpleTOC
 * Plugin URI: https://github.com/mtoensing/simpletoc
 * Description: Adds a basic "table of contents" Gutenberg block.
 * Version: 0.1
 * Author:  Marc Tönsing, Paul de Wouters
 * Author URI: marc.tv
 * Text Domain: simpletoc
 * Domain Path: /languages
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

 namespace SimpleTOC;

defined( 'ABSPATH' ) || exit;

/**
 * Load all translations for our plugin from the MO file.
*/
add_action( 'init', __NAMESPACE__ . '\\load_textdomain' );
add_action( 'init', __NAMESPACE__ . '\\register_block' );
add_action( 'init', __NAMESPACE__ . '\\simpletocinit' );

function load_textdomain() {
	load_plugin_textdomain( 'simpletoc', false, basename( __DIR__ ) . '/languages' );
}

function simpletocinit() {

    wp_register_style(
        'simpletoc-editor',
        plugins_url( 'editor.css', __FILE__ ),
        array( 'wp-edit-blocks' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'editor.css' )
    );

}

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * Passes translations to JavaScript.
 */


function register_block() {

	if ( ! function_exists( 'register_block_type' ) ) {
		// Gutenberg is not active.
		return;
	}

	wp_register_script(
		'simpletoc',
		plugins_url( 'build/index.js', __FILE__ ),
		[ 'wp-blocks', 'wp-i18n', 'wp-element' ],
		filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' )
	);


	register_block_type( 'simpletoc/toc', [
		'editor_script' => 'simpletoc',
    'editor_style' => 'simpletoc-editor',
		'render_callback' => __NAMESPACE__ . '\\render_callback'
	 ] );

  if ( function_exists( 'wp_set_script_translations' ) ) {
    /**
     * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
     * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
     * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
     */
    wp_set_script_translations( 'simpletoc', 'simpletoc-domain' );
  }

}

function render_callback( $attributes, $content ) {
	$blocks = parse_blocks( get_the_content( get_the_ID()));
	if ( empty( $blocks ) ) {
		return 'No contents.';
	}

  //add only if block is used.
  add_filter( 'render_block', __NAMESPACE__ . '\\filter_block', 10, 2 );

	$headings = array_values( array_filter( $blocks, function( $block ){
		return $block['blockName'] === 'core/heading';
	}) );
	if ( empty( $headings ) ) {
		return 'No headings.';
	}
	$heading_contents = array_column( $headings, 'innerHTML');

    // Keep it simple so that the user can add this.
		//$output .= '<h2>' . __( 'Table of Contents', 'simpletoc' ) . '</h2>';

		$output .= '<ul class="toc">';
			foreach ( $heading_contents as $heading_content ) {
				preg_match( '|<h[^>]+>(.*)</h[^>]+>|iU', $heading_content , $matches );

				$link = sanitize_title_with_dashes( $matches[1]);
				$output .= '<li><a href="#' . $link . '">' . $matches[1] . '</a></li>';
			}
		$output .= '</ul>';

	return $output;
}

function filter_block( $block_content, $block ) {
	if ( $block['blockName'] !== 'core/heading' ) {
		return $block_content;
	}

	preg_match('/\\n<(h[2-6](?:.*))>(.*)<\/(h[2-6])>\\n/', $block_content , $matches );
	$link = sanitize_title_with_dashes( $matches[2] );
  $start = preg_replace('#\s(id|class)="[^"]+"#', '', $matches[1]);
	return "\n<{$start} id='{$link}'>" . $matches[2] . "</{$matches[3]}>\n";
}
