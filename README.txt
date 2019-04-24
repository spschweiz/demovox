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

With this plugin you can collect signatures for initiatives and referendums.
= Open =
Demovox is the first truly open platform suitable for efficient collection.
= Secure =
Security is of great concern to us. That's why we've added a few power features. The organization that runs the plugin is in control of the data. It is up to them to store the signatures encrypted in the database.
= Fast =
In order to be able to use this plugin even when a lot of people want to sign, we have outsourced the hardest work and made sure that regular tasks are only executed when the load on the server is not too high.


== Installation ==

1. Upload directory `demovox` to the Wordpress Plugin directory (/wp-content/plugins/)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in WordPress admin
4. Place shortcodes `[demovox_form_shortcode]` and `[demovox_count_shortcode]` on a WordPress page or post
3. Configure demovox in WordPress
4. Place shortcodes [demovox_form] on a page
5. Optionally use and [demovox_count] to get the number of collected signatures and [demovox_optin] for the opt-in form.

== Changelog ==

= 1.0 =
* First release

== Requirements ==
* PHP 7
* WordPress
* SSL certificate for HTTPS (unsecure connection is not allowed)
* Optional feature requires the PHP modules "GMP" or "BC Math"

= Unencrypted access =
As developer you might want to work on a web server without SSL configured. Enable `WP_DEBUG` in `wp-config.php`. Then
open the Advanced settings of the plugin in the WordPress backend and disable "Redirect clients to secure HTTPS".

[![SP Schweiz](https://www.sp-ps.ch/sites/all/themes/sp_ps/logo.png)](http://sp-ps.ch)
