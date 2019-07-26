=== demovox ===
Contributors: spschweiz
Donate link: https://github.com/spschweiz/demovox
Tags: initiative, referendum, switzerland, collect, signature, signatures, online
Requires at least: 4.9
Tested up to: 5.2
Stable tag: 1.0
Requires PHP: 7.0.0
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

1. Example of a demovox installation on the popular initiative [Prämien-Entlastungs-Initiative](https://bezahlbare-praemien.ch/), for which the plugin was initially built for
2. demovox installation on the [Initiative pour les glaciers](https://glaciers.pssuisse.ch/) (or [Gletscher-Initiative](https://gletscher.spschweiz.ch/) in german)

== Frequently Asked Questions ==

= Do you provide support to run this plugin? =

We don't provide free support. You can contact us at [demovox.ch](https://demovox.ch).

= Is there a documentation? =

Yes. Checkout [demovox.ch/docs/demovox/](https://demovox.ch/docs/demovox/)

= How can I contribute to this project? =

Our code is on [github.com/spschweiz/demovox](https://github.com/spschweiz/demovox). If you want to make a donation, we're happy if you do that on [demovox.ch](https://demovox.ch).

== Requirements ==
* PHP >= 7.0
* MySQL >= 5.6.5
* WordPress >= 4.9
* SSL certificate for HTTPS (unsecure connection is not allowed)
* Optional feature requires the PHP modules "GMP" or "BC Math"

== Installation ==
1. Upload directory `demovox` to the Wordpress Plugin directory (/wp-content/plugins/)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in WordPress admin
4. Place shortcodes [demovox_form] on a page
5. Optionally use and [demovox_count] to get the number of collected signatures and [demovox_optin] for the opt-in form.

== Upgrade Notice ==
* 1.0 This is the first stable Release.

== Changelog ==

= 1.0 =
* First release

= 1.1 =
* Signatures can now be listed and searched in the backend
* The signature counter now features a thousands separator
* Admin option to export signatures to an API