=== SimpleTOC - Table of Contents Block ===
Contributors: MarcDK
Tags: AMP, Gutenberg, block, TOC, Table of Contents
Requires at least: 5.0
Donate link: https://marc.tv/out/donate
Tested up to: 5.8
Stable tag: 4.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a custom Table of Contents Gutenberg block.

== Description ==

In Gutenberg, add a block and search for "SimpleTOC" or just "TOC". You need to save your post before you add the block. It works by parsing the post content and retrieving the heading blocks and creates a new dynamic block with a list of links to the headings.

Hide the headline "Table of Contents" and set a maximum display depth in the blocks' sidebar configuration. Add the CSS class "simpletoc-hidden" to a heading block to remove that specific heading from the generated TOC.

= Features =

* No javascript or css added.
* Designed for Gutenberg.
* Compatible with AMP plugins.
* Minimal and valid HTML output.
* Inherits the style of your theme.
* Support for column block layouts. 
* Control the maximum depth of the headings.
* Choose between an ordered and unordered html list.
* SEO friendly: Disable the h2 heading of the TOC block and add your own.
* Comes with English, French, Spanish, German, and Brazilian Portuguese translations.
* Works with non-latin texts. Tested with Japanese and Arabic.
* Finds headlines in groups and reusable blocks. And in groups within reusable blocks.
* Rank Math support.

== Changelog ==

= 4.9 =
* Feature: Prevent warnings when HTML of posts is not valid.

= 4.8 =
* Feature: SimpleTOC can be found in Gutenberg by entering "Table of Contents".

= 4.5 =
* Feature: Add the css class "smooth-scroll" to the links. This enables smooth scrolling in themes like GeneratePress. 

= 4.4.9 =
* Feature: Support for headlines in reusable blocks. 

= 4.4.8 =
* Feature: Added option to toggle absolute urls. 

= 4.4.7 =
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

== Frequently Asked Questions ==

= Why did you do this? =

Because I needed a simple plugin to do this job and decided to do it on his own. I believe that a Table of Contents does not need Javascript and additional css. Furthermore the plugin should work out-of-the-box without any configuration. 

= How do I change the TOC heading ‘Table of contents’ to some other words? =

Hide the headline in the sidebar options of SimpleTOC and add your own heading.

= How do I add SimpleTOC to all articles automatically?  =

I don’t see an easy solution at the moment. SimpleTOC is only a block that can be placed in your post. If there would be a plugin that adds blocks to every post then this would be the solution. I think this should be another separate plug-in to keep the code of SimpleTOC clean and … well, simple. Maybe someone knows of a plug-in that adds blocks automatically to all posts with some parameters and settings? What about site editing in WordPress? I think the core team is working on something like that. I will keep this post open. If I have gained more knowledge how to solve this I will add this feature. 

= How do I add a background color to SimpleTOC? =

SimpleTOC is simple. It will never load JavaScript or CSS. But you can add custom CSS yourself to your theme: 

`.simpletoc {
    padding: 1em 2em;
    background: #bada55;
}`

If you want to add a color including the headline, add a custom css class to the SimpleTOC block. Name it "toctoc" or something like that. 

`.toctoc {
    padding: 1em 1em;
    background: #bada55;
}`

If you use that CSS it will add a colored background to the whole TOC including the heading.

= How do I add smooth scrolling? =

You can optionally add the css class "smooth-scroll" to each link the TOC. Then you can install plugin that uses these classes.

== Screenshots ==
1. SimpleTOC block in Gutenberg editor.
2. SimpleTOC in the post.
3. Simple but powerful. Customize each TOC as you like.
4. Control the maximum depth of the headings.

== Credits ==

This plugin is forked from https://github.com/pdewouters/gutentoc by pdewouters and uses code from https://github.com/shazahm1/Easy-Table-of-Contents by shazahm1

Many thanks to Tom J Nowell https://tomjn.com and and Sally CJ who both helped me a lot with my questions over at wordpress.stackexchange.com