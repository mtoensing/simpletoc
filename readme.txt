=== SimpleTOC - Table of Contents Block ===
Contributors: MarcDK
Tags: AMP, Gutenberg, block, TOC, Table of Contents
Requires at least: 5.0
Donate link: https://marc.tv/out/donate
Tested up to: 5.6.2
Stable tag: 4.4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a custom Table of Contents Gutenberg block.

== Description ==

In Gutenberg, add a block and search for "SimpleTOC" or just "TOC". You need to save your post before you add the block. It works by parsing the post content and retrieving the heading blocks and creates a new dynamic block with a list of links to the headings.

Hide the headline "Table of Contents" and set a maximum display depth in the blocks' sidebar configuration. Add the CSS class "simpletoc-hidden" to a heading block to remove that specific heading from the generated TOC.

= Features =

* Designed for Gutenberg.
* Compatible with AMP plugins.
* Minimal and valid HTML output.
* No javascript or css added.
* Inherits the style of your theme.
* Support for column block layouts. 
* Control the maximum depth of the headings.
* Choose between an ordered and unordered html list.
* SEO friendly: Disable the h2 heading of the TOC block and add your own.
* Comes with English, French, Spanish, German, and Brazilian Portuguese translations.
* Works with non-latin texts. Tested with Japanese and Arabic.
* Rank Math support.

This plugin is forked from https://github.com/pdewouters/gutentoc by pdewouters and uses code from https://github.com/shazahm1/Easy-Table-of-Contents by shazahm1

Many thanks to Tom J Nowell https://tomjn.com and and Sally CJ who both helped me a lot with my questions over at wordpress.stackexchange.com

== Changelog ==

= 4.4.2 =
* Feature: Added option to replace ul tag with ol tag. This will add decimal numbers to each heading in the TOC.
* Feature: Works in nested blockes. This means support for column block layouts. 

= 4.3 =
* Feature: Support for non-latin headlines. SimpleTOC now uses a character block list rather than an allow list.
* Feature: Add the CSS class "simpletoc-hidden" to the heading block to remove it from the Table of Contents.

= 4.1.1 =
* Feature: Experimental support for Arabic Text.

= 4.0 =
* Feature: Added option to set maximum level of the headings.

= 3.9 =
* Added Brazilian Portuguese translations to the translations. Thanks Ralden Souza!

= 3.5 =
* Added support for Rank Math plugin.

== Installation ==

SimpleTOC can be found and installed via the Plugin menu within WordPress administration (Plugins -> Add New). Alternatively, it can be downloaded from WordPress.org and installed manually...

In Gutenberg, add a block and search for "SimpleTOC" or just "TOC". Please save your content before you use the block.

## Screenshots ##
1. SimpleTOC block in Gutenberg editor.
2. SimpleTOC in the post.
3. Simple but powerful. Customize each TOC as you like.
4. Control the maximum depth of the headings.
