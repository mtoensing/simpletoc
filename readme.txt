=== SimpleTOC - Table of Contents Block ===
Contributors: MarcDK
Tags: Gutenberg, block, TOC, Table of Contents, AMP
Requires at least: 5.9
Donate link: https://marc.tv/out/donate
Tested up to: 6.0
Stable tag: 6.0.37
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

SEO-friendly Table of Contents Gutenberg block. No JavaScript and no CSS means faster loading.

== Description ==

Add a Table of Contents block to your posts and pages. The TOC is a nested list of links to all heading found in the post or page. To use it, simply add a block and search for "SimpleTOC" or just "TOC". 

The maximum depth of the toc can be configured in in the blocks' sidebar among many other options. There can hide the headline "Table of Contents" and add your own by using a normal heading block.  

SimpleTOC is open-source and developed on [GitHub Pages](https://github.com/mtoensing/SimpleTOC). If you find a bug or have an idea for a feature please feel free to contribute and create a pull request. 

[Spin up](https://demo.tastewp.com/simpletoc) a new WordPress instance with the SimpleTOC plugin already installed.

= Features =

* Zero configuration: Add the SimpleTOC block to your post and that's it. 
* Minimal and valid HTML output.
* No JavaScript or CSS added. Except you enable the accordion menu.
* Designed for Gutenberg.
* Style SimpleTOC with Gutenberg's native group styling options.
* Inherits the style of your theme.
* Translated in [multiple languages](https://translate.wordpress.org/projects/wp-plugins/simpletoc/). Including German, Japanese, Chinese (Taiwan), Dutch, Brazilian Portuguese, French, Spanish and Latvia.

= Customization = 

* Add background and text color with Gutenberg groups.
* Native block support for wide and full width.
* Control the maximum depth of the headings.
* Choose between an ordered and unordered HTML list.
* Toggle list indent.
* Disable the h2 heading of the TOC block and add your own.

= Compatibility =

* GeneratePress and Rank Math support.
* Works with popular AMP plugins.

== Changelog ==

= 5.0.37 =
* Feature: Hide SimpleTOC in an accordion menu. This will add minimal inline JavaScript and css styles.
* Feature: Option to change SimpleTOC block heading.

= 5.0.36 =
* Feature: [Spin up](https://demo.tastewp.com/simpletoc) a new WordPress instance with the SimpleTOC plugin already installed.
* Fix: Better handling for `simpletoc-hidden` class to hide headings. Thank you blronaldhuereca!

= 5.0.34 =
* Feature: Moved list controls to toolbar and rearranged settings to fit block order.
* Feature: Filter for developers to wrap toc with a div tag. Thank you rafaucau!
* Fix: Minor localization tweaks. 

= 5.0.31 =
* Feature: Filter for developers to exclude specific blocks. See faq for details. Thanks rafaucau!
* Feature: Easier development with @wordpress/env to start a dev environment. Thanks rafaucau!

= 5.0.28 =
* Feature: Change the TOC headline in the block options. Thank you Philipp Stracker!

= 5.0.17 =
* Feature: Support for custom Anchor IDs in headings. Thank you Matthias Altmann!

== Installation ==

SimpleTOC can be found and installed via the Plugin menu within WordPress administration (Plugins -> Add New). Alternatively, it can be downloaded from WordPress.org and installed manually...

In Gutenberg, add a block and search for "SimpleTOC" or just "TOC". Please save your content before you use the block.

== Frequently Asked Questions ==

= How do I add a background color to SimpleTOC using Gutenberg groups? =

Select the block and select "group" in the context menu. Apply "background color", "link color" and "text color" to this group. SimpleTOC will inherit these styles. You would like to use this styled SimpleTOC group next time you write a post? Convert it to a reusable block.

= How to exclude a single heading from the TOC? = 

If you really want to hide a single heading from the table of contents, then add the CSS class "simpletoc-hidden" to a heading block. But first, think about the reason you would like to hide a specific heading. Maybe you would like to remove all headings of a specific depth level. Then there is an option for that in the blocks options in Gutenberg. If you think this heading should not be part of the toc perhaps it is not needed in the post itself?

= I would like to save my SimpleTOC settings as default. Is that possible?

You can convert your configured SimpleTOC block into a reusable block in Gutenberg. It will keep its settings. This way, you can use your desired settings for each new post by adding the reusable block.

= How to add a div tag wrapper to the TOC? =

If you add a custom class to the SimpleTOC block in "Advanced" and then "Additional CSS Class(es)" a div with that class will be wrapped around the HTML output. You can force this with a filter, too.

Example: 

    add_filter( 'simpletoc_wrapper_enabled', '__return_true' );

= How to allow developers to exclude specific headings programmatically? = 

Use the 'simpletoc_excluded_blocks' filter. For example, this code will exclude heading blocks that are inside a column block.

Example: 

    add_filter( 'simpletoc_excluded_blocks', function ( array $blocks ) {
        $blocks[] = 'core/column';

        return $blocks;
    } );

= How do I add SimpleTOC to all articles automatically?  =

I don’t see an easy solution at the moment. SimpleTOC is only a block that can be placed in your post. If there was a plugin that adds blocks to every post, then this would be the solution. I think this should be another separate plug-in to keep the code of SimpleTOC clean and … well, simple. Maybe someone knows of a plug-in that adds blocks automatically to all posts with some parameters and settings? What about site editing on WordPress? I think the core team is working on something like that. I will keep this post open. If I have gained more knowledge how to solve this, I will add this feature. 

== Screenshots ==
1. SimpleTOC block in Gutenberg editor.
2. SimpleTOC in the post.
3. Simple but powerful. Customize each TOC as you like.
4. Control the maximum depth of the headings.
5. SimpleTOC styled with Gutenbergs native group styles.
6. SimpleTOC Advanced Features
7. Gutenberg Heading block: Set a custom anchor  

== Credits ==

This plugin is forked from [pdewouters](https://github.com/pdewouters/gutentoc) and uses code from [Easy-Table-of-Contents](https://github.com/shazahm1/Easy-Table-of-Contents)

Many thanks to [Tom J Nowell](https://tomjn.com) and and Sally CJ who both helped me a lot with my questions over at wordpress.stackexchange.com

Thanks to Quintus Valerius Soranus for inventing the Table of Contents around 100 BC. 

