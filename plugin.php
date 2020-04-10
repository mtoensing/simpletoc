<?php
/**
 * Plugin Name: SimpleTOC - Table of Contents Block
 * Plugin URI: https://github.com/mtoensing/simpletoc
 * Description: Adds a basic "Table of Contents" Gutenberg block.
 * Version: 0.1
 * Author:  Marc Tönsing, Paul de Wouters
 * Author URI: marc.tv
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace SimpleTOC;

defined('ABSPATH') || exit;

/**
 * Load all translations for our plugin from the MO file.
*/
add_action('init', __NAMESPACE__ . '\\register_block');
add_action('init', __NAMESPACE__ . '\\simpletocinit');

function simpletocinit()
{
    $asset_file = include(plugin_dir_path(__FILE__) . 'build/index.asset.php');

    wp_register_script(
    'simpletoc',
    plugins_url('build/index.js', __FILE__),
    [ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-server-side-render' ],
    filemtime(plugin_dir_path(__FILE__) . 'build/index.js')
    );

    wp_register_style(
    'simpletoc-editor',
    plugins_url('editor.css', __FILE__),
    array( 'wp-edit-blocks' ),
    filemtime(plugin_dir_path(__FILE__) . 'editor.css')
    );
}

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 */

function register_block()
{
    if (! function_exists('register_block_type')) {
        // Gutenberg is not active.
        return;
    }

    register_block_type('simpletoc/toc', [
    'editor_script' => 'simpletoc',
    'editor_style' => 'simpletoc-editor',
    'render_callback' => __NAMESPACE__ . '\\render_callback'
   ]);
}

function render_callback($attributes, $content)
{
    $blocks = parse_blocks(get_the_content(get_the_ID()));

    if (empty($blocks)) {
        return 'No contents.';
    }

    //add only if block is used in this post.
    add_filter('render_block', __NAMESPACE__ . '\\filter_block', 10, 2);

    $headings = array_values(array_filter($blocks, function ($block) {
        return $block['blockName'] === 'core/heading';
    }));

    if (empty($headings)) {
        return 'No headings found.';
    }

    $heading_contents = array_column($headings, 'innerHTML');

    $output .= '<ul class="toc">';
    foreach ($heading_contents as $heading_content) {
        preg_match('|<h[^>]+>(.*)</h[^>]+>|iU', $heading_content, $matches);
        $link = sanitize_title_with_dashes($matches[1]);
        $output .= '<li><a href="#' . $link . '">' . $matches[1] . '</a></li>';
    }
    $output .= '</ul>';

    return $output;
}

function filter_block($block_content, $block)
{
    if ($block['blockName'] !== 'core/heading') {
        return $block_content;
    }

    preg_match('/\\n<(h[2-4](?:.*))>(.*)<\/(h[2-4])>\\n/', $block_content, $matches);
    $link = sanitize_title_with_dashes($matches[2]);
    $start = preg_replace('#\s(id|class)="[^"]+"#', '', $matches[1]);
    return "\n<{$start} id='{$link}'>" . $matches[2] . "</{$matches[3]}>\n";
}
