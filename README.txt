=== demovox ===
Contributors: (this should be a list of wordpress.org userid's)
Donate link: https://github.com/spschweiz/demovox
Tags: initiative, switzerland, collect, signature, signatures
Requires at least: 1.0
Tested up to: 1.0
Stable tag: 1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

demovox is a tool to collect signatures for Swiss popular initiatives by offering the visitor a personalized
signature sheet.

== Description ==



== Installation ==

1. Upload directory `demovox` to the Wordpress Plugin directory (/wp-content/plugins/)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in WordPress admin
4. Place shortcodes `[demovox_form_shortcode]` and `[demovox_count_shortcode]` on a WordPress page or post
3. Configure demovox in WordPress
4. Place shortcodes [demovox_form] on a page
5. Optionally use and [demovox_count] to get the number of collected signatures and [demovox_optin] for the opt-in form.

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0 =
* First release

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above.  This may be of use for extremely complicated
plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.

== Requirements ==
* PHP 7
* WordPress
* SSL certificate for HTTPS (unsecure connection is not allowed)
* Optional feature requires the PHP modules "GMP" or "BC Math"

== DEV preparation ==

= Preparation =
Install Python, node.js and composer:
* https://www.python.org/
* https://nodejs.org/
* https://getcomposer.org/
Download required packages:
`$npm install
$composer install`
Generate assets:
`$grunt buildAssets`
Compile .mo translation files:
`$grunt translations`
build zip:
`$grunt buildZip`

= Unencrypted access =
As developer you might want to work on a web server without SSL configured. Enable `WP_DEBUG` in `wp-config.php`. Then
open the Advanced settings of the plugin in the WordPress backend and disable "Redirect clients to secure HTTPS".

== A brief Markdown Example ==

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing

Here's a link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].
Titles are optional, naturally.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`

[![SP Schweiz](https://www.sp-ps.ch/sites/all/themes/sp_ps/logo.png)](http://sp-ps.ch)