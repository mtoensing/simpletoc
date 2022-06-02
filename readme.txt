=== SimpleTOC - Table of Contents Block ===
Contributors: MarcDK
Tags: Gutenberg, block, TOC, Table of Contents, AMP
Requires at least: 5.9
Donate link: https://marc.tv/out/donate
Tested up to: 6.0
Stable tag: 5.0.17
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a custom Table of Contents Gutenberg block.

== Description ==

Add a Table of Contents block to your posts and pages. The TOC is a nested list of links to all heading found in the post or page. To use it, simply add a block and search for "SimpleTOC" or just "TOC". 

The maximum depth of the toc can be configured in in the blocks' sidebar among many other options. There can hide the headline "Table of Contents" and add your own by using a normal heading block.  

= Features =

* Zero configuration: Add the SimpleTOC block to your post and that's it. 
* Minimal and valid HTML output.
* No JavaScript or CSS added.
* Designed for Gutenberg.
* Style SimpleTOC with Gutenberg's native group styling options.
* Inherits the style of your theme.
* Comes with English, Chinese (Taiwan), French, Spanish, German, Latvia and Brazilian Portuguese translations.

= Customization = 

* Add background and text color with Gutenberg groups.
* Native block support for wide and full width.
* Control the maximum depth of the headings.
* Choose between an ordered and unordered HTML list.
* Toggle list indent.
* Disable the h2 heading of the TOC block and add your own.

= Compatibility =

* GeneratePress and Rank Math support.
* Works with all popular AMP plugins.

== Changelog ==

= 5.0.17 =
* Feature: Support for Custom Anchor IDs in headings. Thank you Matthias Altmann!

= 5.0.16 =
* Fix: Anchor ids will be generated for all posts. This should brute-force the fix for https://wordpress.org/support/topic/scroll-stopped-working/ 

= 5.0.11 = 
* Feature: Support for GeneratePress headings.
* Fix: Headings in groups work again.

= 5.0.9 = 
* Works with WordPress 6.0
* Support for paginated posts.

= 5.0.8 =

* Fix: Requires at least WordPress 5.8. and not 5.9.
* Fix: Better compatibility with Twenty-Twenty-One.
* Fix: Localization is back again.
* Feature: Added "simpletoc-title" and "simpletoc-list" CSS classes. This might break existing custom CSS styles that have not been applied with Gutenberg. 
* Feature: Toggle list indent.
* Feature: Better "group" support. Put SimpleTOC in a group and style it with Gutenberg. See FAQ for details.
* Feature: Native block support for wide and full width.
* Feature: Added block.json and refactoring with coding standards for Gutenberg blocks.

= 4.5 =
* Feature: Add the CSS class "smooth-scroll" to the links. This enables smooth scrolling in themes like GeneratePress. 

= 4.4.9 =
* Feature: Support for headlines in reusable blocks. 

= 4.4.8 =
* Feature: Added option to toggle absolute URLs. 

= 4.4.7 =
* Feature: Added option to replace ul tag with ol tag. This will add decimal numbers to each heading in the TOC.

= 4.3 =
* Feature: Support for non-latin headlines. SimpleTOC now uses a character block list rather than an allow list.
* Feature: Add the CSS class "simpletoc-hidden" to the heading block to remove it from the Table of Contents.

= 4.1.1 =
* Feature: Experimental support for Arabic Text.

= 3.9 =
* Added Brazilian Portuguese translations to the translations. Thanks Ralden Souza!

= 3.5 =
* Added support for Rank Math plugin.

== Installation ==

SimpleTOC can be found and installed via the Plugin menu within WordPress administration (Plugins -> Add New). Alternatively, it can be downloaded from WordPress.org and installed manually...

In Gutenberg, add a block and search for "SimpleTOC" or just "TOC". Please save your content before you use the block.

== Frequently Asked Questions ==

= Why did you do this? =

Because I needed a simple plugin to do this job and decided to do it on his own. I believe that a Table of Contents does not need Javascript and additional css. Furthermore the plugin should work out-of-the-box without any configuration. 

= How do I change the TOC heading ‘Table of contents’ to some other words? =

Hide the headline in the sidebar options of SimpleTOC and add your own heading.

= How do I add SimpleTOC to all articles automatically?  =

I don’t see an easy solution at the moment. SimpleTOC is only a block that can be placed in your post. If there would be a plugin that adds blocks to every post then this would be the solution. I think this should be another separate plug-in to keep the code of SimpleTOC clean and … well, simple. Maybe someone knows of a plug-in that adds blocks automatically to all posts with some parameters and settings? What about site editing in WordPress? I think the core team is working on something like that. I will keep this post open. If I have gained more knowledge how to solve this I will add this feature. 

= How do I add a background color to SimpleTOC using Gutenberg groups? =

Select the block and select "group" in the context menu. Apply "background color", "link color" and "text color" to this group. SimpleTOC will inherit these styles. You would like to use this styled SimpleTOC group next time you write a post? Convert it to a reusable block.

= How do I add smooth scrolling? =

You can optionally add the css class "smooth-scroll" to each link the TOC. Then you can install plugin that uses these classes.

= How do I hide a single heading? = 

If you really want to hide a single heading from the table of contents then add the CSS class "simpletoc-hidden" to a heading block. But first of all, think about the reason you want to hide a specific heading. Maybe you want to remove all headins of a specific depth level. Then there is an option for that in the blocks options in Gutenberg. If you think this heading should not be part of the toc maybe it is not needed in the post itself?

== Screenshots ==
1. SimpleTOC block in Gutenberg editor.
2. SimpleTOC in the post.
3. Simple but powerful. Customize each TOC as you like.
4. Control the maximum depth of the headings.
5. Style SimpleTOC with Gutenbergs native group styling options.

== Credits ==

This plugin is forked from https://github.com/pdewouters/gutentoc by pdewouters and uses code from https://github.com/shazahm1/Easy-Table-of-Contents by shazahm1

Many thanks to Tom J Nowell https://tomjn.com and and Sally CJ who both helped me a lot with my questions over at wordpress.stackexchange.com

Thanks to Quintus Valerius Soranus for inventing the Table of Contents around 100 BC. 

SimpleTOC is developed on GitHub: https://github.com/mtoensing/simpletoc 