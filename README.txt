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

== Screenshots ==

1. Example of a demovox installation on the popular initiative "Prämien-Entlastungs-Initiative", for which the plugin was initially built for

== Requirements ==
* PHP 7
* WordPress
* SSL certificate for HTTPS (unsecure connection is not allowed)
* Optional feature requires the PHP modules "GMP" or "BC Math"

== Installation ==
1. Upload directory `demovox` to the Wordpress Plugin directory (/wp-content/plugins/)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in WordPress admin
4. Place shortcodes [demovox_form] on a page
5. Optionally use and [demovox_count] to get the number of collected signatures and [demovox_optin] for the opt-in form.

== Changelog ==

= 1.0 =
* First release