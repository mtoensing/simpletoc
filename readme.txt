=== SimpleTOC - Table of Contents Block ===
Contributors: MarcDK
Tags: TOC, Table of Contents, Block, Accessibility, Table
Requires at least: 5.9
Donate link: https://marc.tv/out/donate
Tested up to: 6.8
Stable tag: 6.7.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

SEO-friendly Table of Contents Gutenberg block. No JavaScript and no CSS means faster loading.

== Description ==

Add a Table of Contents block to your posts and pages. The TOC is a nested list of links to all heading found in the post or page. To use it, simply add a block and search for "SimpleTOC" or just "TOC". 

The maximum depth of the toc can be configured in in the blocks' sidebar among many other options. There can hide the headline "Table of Contents" and add your own by using a normal heading block.  

SimpleTOC is open-source and developed on [GitHub Pages](https://github.com/mtoensing/SimpleTOC). If you find a bug or have an idea for a feature please feel free to contribute and create a pull request. 

[Spin up](https://demo.tastewp.com/simpletoc) a new WordPress instance with the SimpleTOC plugin already installed.

= Accessibility =

This plugin is designed & developed for WCAG 2.2 level AA conformance. The plugin is tested with assistive technology and intended to be accessible, however some third party plugins or themes may affect the individual accessibility on a given website. If you find an accessibility issue, please let us know and we'll try to address it promptly.

= Features =

* Designed for Gutenberg.
* Zero configuration: Add the SimpleTOC block to your post and that's it. 
* Minimal and valid HTML output.
* Utilizes the browser's built-in details tag for a collapsible interface.
* No JavaScript or CSS added. Unless you activate the accordion menu.
* Style SimpleTOC with Gutenberg's native group styling options.
* Inherits the style of your theme.
* Smooth scrolling effect using CSS. 
* Accessibility built-in by following web standards.
* Optional ARIA Label and navigation role attributes.
* Translated in [multiple languages](https://translate.wordpress.org/projects/wp-plugins/simpletoc/). Including German, Japanese, Chinese (Taiwan), Dutch, Brazilian Portuguese, French, Spanish and Latvia.
* Ideal for creating a Frequently Asked Questions section on your website.

= Customization = 

* Administrators can utilize global settings to supersede the individual block settings.
* Add background and text color with Gutenberg groups.
* Native block support for wide and full width.
* Control the maximum depth of the headings.
* Choose between an ordered, bullet HTML list. Or indent the list.
* Select a heading level or turn it into a paragraph.
* Disable the h2 heading of the TOC block and add your own.

= Compatibility =

* GeneratePress and Rank Math support.
* Works with popular AMP plugins.

== Changelog ==

= 6.7.3 =
* Added: Tested compatibility with WordPress 6.8

= 6.7.2 = 
* Added: Support for GenerateBlocks 2.0. Thanks @blronaldhuereca 
* Added: Included an accessibility statement in the plugin description. Thanks Gen Herres.

= 6.6.1 = 
* Added: Initialize the JavaScript for the TOC accordion on page load or after the document has fully loaded. Thanks blronaldhuereca https://github.com/mtoensing/simpletoc/pull/63 

= 6.6.0 =
* Added: Testest compatibility with WordPress 6.7

= 6.5.6 =
* Fixed: All script versions have been updated to prevent caching issues.
* Fixed: Legacy Accordion was always expanded. Thanks Francesco Palmieri.
* Added: Updated dependencies for improved performance and stability.​
* Added: Testest compatibility with WordPress 6.6

= 6.4.3 =
* Added: A pointer cursor on hover for the hidden TOC

= 6.4.3 =
* Added: Utilizes the browser's built-in details tag for a collapsible interface. Thanks @infinitnet
* Compatibility with WordPress 6.5
* Fixed: Minor localization problems.   

= 6.3.2 =
* Fixed: Option for automatic refresh did not work in some instances.

= 6.3.0 =
* Added: Option for automatic refresh. This can be disabled in the blocks advanced settings.
* Added: Option to globally disable automatic refresh in the WordPress SimpleTOC settings.

= 6.2.0 =
* Added: Implemented smooth animation for improved user interaction in the accordion menu.
* Added: Upgraded styling of the accordion menu for a more visually appealing and modern user experience.

= 6.1.0 =
* Fixed: Broken markup when tags closed for headers below minimum. Thanks @harmoney !

= 6.0.10 =
* Added aria-hidden attribute to icon in accordion. Thanks Alex Stine!

= 6.0.9 =
* Added ARIA accessibility labels for the accordion. Thanks Amber Hinds!
* Fixed: Caching of accordion JavaScript. Thanks jghitchcock!
* Added correct ARIA controls attribute.

== Installation ==

SimpleTOC can be found and installed via the Plugin menu within WordPress administration (Plugins -> Add New). Alternatively, it can be downloaded from WordPress.org and installed manually...

In Gutenberg, add a block and search for "SimpleTOC" or just "TOC". Please save your content before you use the block.

== Frequently Asked Questions ==

= Configuring Global Settings in SimpleTOC =

SimpleTOC allows you to configure global settings for your WordPress website. These settings can be enforced globally, overriding any block-level configurations that may exist. To access these settings, navigate to the SimpleTOC section of the WordPress Settings.

= How do I add a background color to SimpleTOC using Gutenberg groups? =

Select the block and select "group" in the context menu. Apply "background color", "link color" and "text color" to this group. SimpleTOC will inherit these styles. You would like to use this styled SimpleTOC group next time you write a post? Convert it to a reusable block.

= How to exclude a single heading from the TOC? = 

If you really want to hide a single heading from the table of contents, then add the CSS class "simpletoc-hidden" to a heading block. You can find this field in the same place as the HTML anchor field: In the Block > Advanced sidebar. But first, think about the reason you would like to hide a specific heading. Maybe you would like to remove all headings of a specific depth level. Then there is an option for that in the blocks options in Gutenberg. If you think this heading should not be part of the toc perhaps it is not needed in the post itself?

= I would like to save my SimpleTOC settings as default. Is that possible?

You can convert your configured SimpleTOC block into a reusable block in Gutenberg. It will keep its settings. This way, you can use your desired settings for each new post by adding the reusable block.

= How to add a div tag wrapper to the TOC? =

If you add a custom class to the SimpleTOC block in "Advanced" and then "Additional CSS Class(es)" a div with that class will be wrapped around the HTML output. 

= How to allow developers to exclude specific headings programmatically? = 

Use the 'simpletoc_excluded_blocks' filter. For example, this code will exclude heading blocks that are inside a column block.

Example: 

    add_filter( 'simpletoc_excluded_blocks', function ( array $blocks ) {
        $blocks[] = 'core/column';

        return $blocks;
    } );


= How do I change the color of the accordion menu? =

The heavy plus character I used can not be colored with css without hacks. But you can change the icon to something else and change the color of the new icon. 

    .simpletoc-collapsible::after {
        content: "✖";
        color: #e94c89;
    } 

= How do I add SimpleTOC to all articles automatically?  =

I don’t see an easy solution at the moment. SimpleTOC is only a block that can be placed in your post. If there was a plugin that adds blocks to every post, then this would be the solution. I think this should be another separate plug-in to keep the code of SimpleTOC clean and … well, simple. 

== Screenshots ==
1. SimpleTOC block in Gutenberg editor.
2. SimpleTOC in the post.
3. Simple but powerful. Customize each TOC as you like.
4. Control the maximum depth of the headings.
5. SimpleTOC styled with Gutenbergs native group styles.
6. SimpleTOC Advanced Features
7. Gutenberg Heading block: Set a custom anchor  
8. SimpleTOC hidden in the accordion menu.
9. SimpleTOC global settings.

== Credits ==

This plugin is forked from [pdewouters](https://github.com/pdewouters/gutentoc) and uses code from [Easy-Table-of-Contents](https://github.com/shazahm1/Easy-Table-of-Contents)

Many thanks to [Tom J Nowell](https://tomjn.com) and and Sally CJ who both helped me a lot with my questions over at wordpress.stackexchange.com

Thanks to Quintus Valerius Soranus for inventing the Table of Contents around 100 BC. 
