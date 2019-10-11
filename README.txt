=== demovox ===
Contributors: spschweiz, horlacher
Donate link: https://demovox.ch/#spenden
Tags: initiative, referendum, switzerland, collect, signature, signatures
Requires at least: 4.9
Tested up to: 5.2
Stable tag: 1.0
Requires PHP: 7.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Collect signatures for Swiss popular initiatives and referendums.

== Description ==
Collect signatures for Swiss popular initiatives and referendums.
= Open =
Demovox is the first truly open platform suitable for efficient collection.
= Secure =
Security is of great concern to us. The organization that runs the plugin is always the only one in control of the data and it can be stored encrypted in the database.
= Fast =
Allows many people to sign up at the same time, as it avoids high server loads by generating the signature sheet PDFs on the browser instead.
= Packed with features =
* Opt-in check box
* Track signature sheets
* Reminder for unfinished sign ups or missing signature sheets
* Local initiatives (communal and cantonal)
* Four national languages plus english are supported for any frontend text or mail content
* Signature sheets with turned parts by 90°, 180° and 270° (for example letter sheets)
* Swiss abroad
* Supports API for address completion and commune identification by a given address (optional)
* Continuous export to REST API of your CRM
* Counts how many signatures a referrer has provided (link the signature page with a "src" param)

== Screenshots ==
1. Example of a demovox installation on the popular initiative [Prämien-Entlastungs-Initiative](https://bezahlbare-praemien.ch/), for which the plugin was initially built for
2. demovox installation on the [Initiative pour les glaciers](https://glaciers.pssuisse.ch/) (or [Gletscher-Initiative](https://gletscher.spschweiz.ch/) in german)

== Frequently Asked Questions ==
= Do you provide support to run this plugin? =
We don't provide free support, you can contact us at [demovox.ch](https://demovox.ch).
= Is there a documentation? =
Yes, take a look at [demovox.ch/docs/demovox/](https://demovox.ch/docs/demovox/)
= How can I donate? =
If you want to make a donation, you're very welcome to do that on [demovox.ch](https://demovox.ch).
= How can I contribute to this project? =
Our code is on [github.com/spschweiz/demovox](https://gi 0thub.com/spschweiz/demovox).
If you want to add a feature or add a bugfix, please submit a pull-request.
= Something does not work as expected =
We do not provide any warranty or free support, but you're welcome to create an issue on [github.com/spschweiz/demovox](https://github.com/spschweiz/demovox).

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
5. Optionally use [demovox_count] to show the number of collected signatures and [demovox_optin] for the opt-in edit form.

== Upgrade Notice ==
* 1.0 This is the first stable Release.

== Changelog ==

= 1.0 =
* First release

= 1.1 =
* Signatures can be listed and searched in the backend
* The signature counter now features a thousands separator
* Admin option to continuously export signatures to a REST API of your CRM

= 1.2 =
* Support for local initiatives (commune or canton)
* Separate success page for swiss abroad
* Shortcodes for first name and last name on opt-in page and success pages
* Configuration GUI improvements

= 1.3 =
* Browser compatibility improvements
* PDF print button

For more details, see our commit log: https://github.com/spschweiz/demovox/commits/master