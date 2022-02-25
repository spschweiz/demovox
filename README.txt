=== demovox ===
Contributors: spschweiz, horlacher
Donate link: https://demovox.ch/#spenden
Tags: initiative, referendum, switzerland, collect, signature, signatures
Requires at least: 4.9
Tested up to: 5.9
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
* Track signature sheet reception with QR codes and shortcodes
* Reminder mails for unfinished sign ups or missing signature sheets
* Local initiatives (communal or cantonal)
* Swiss abroad
* Four national languages plus english are supported for any frontend text or mail content
* Signature sheets with turned parts by 90°, 180° and 270° (for example folded letter sheets)
* Supports API for address completion and commune identification by a given address
* Continuous export to a REST API of your CRM
* Integrated queue system for mail submission
* Optimized server performance by generating PDFs on the client and option to delay crons (mail submission, export and indexing) during high server load times
* Counts how many signatures a signature source (referrer) has provided. (Call landing page with a "demovox_src" param and the source name as value, or just the "src" param on a landing page with a [demovox_form] shortcode.)

== Screenshots ==
1. Example of a demovox installation on the popular initiative [Prämien-Entlastungs-Initiative](https://bezahlbare-praemien.ch/), for which the plugin was initially built for
2. demovox installation on the [Initiative pour les glaciers](https://glaciers.pssuisse.ch/) (or [Gletscher-Initiative](https://gletscher.spschweiz.ch/) in german)
3. Cantonal [Elternzeit Initiative](https://elternzeit-initiative.ch/) in Zürich (by [SP Kanton Zürich](https://spkantonzh.ch/))
4. Communal [Hafeninitiative](https://www.hafeninitiative.ch/) in Basel (by [JUSO BS](https://bs.juso.ch/)
5. Referendum [Nein zu den Kapfjet-Milliarden](https://www.kampfjets-nein.ch/) (by [GSoA](https://www.gsoa.ch/))
6. [Initiative für eine 13. AHV-Rente](https://www.ahvx13.ch/) (by [SGB - USS](https://www.sgb.ch/))

== Frequently Asked Questions ==
= Do you provide support to run this plugin? =
We don't provide free support, you can contact us at [demovox.ch](https://demovox.ch).
= Is there a documentation? =
Yes, take a look at [demovox.ch/docs/demovox/](https://demovox.ch/docs/demovox/)
= How can I donate? =
If you want to make a donation, you're very welcome to do that on [demovox.ch](https://demovox.ch).
= How can I contribute to this project? =
Our code is on [github.com/spschweiz/demovox](https://github.com/spschweiz/demovox).
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
* 1.3 Classes on success page elements have changed, check the design on your page

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

= 2.0 =
* Signature source (aka referrer) is kept in PHP session (info persists when visitor visits to other pages and signs later)
* Status messages on PDF generation
* Improved error handling
* Automated code testing with PHPUnit
* Matomo support for ajax and PDF actions
* Code refactoring
* Various bug fixes

= 2.1 =
* Global signature source parameter "demovox_src", which doesn't require the demovox sign shortcode on the landing page
* Submission of forms is optionally by AJAX
* Option stop reminders at specified date

= 2.2 =
* Option to print signee names on the PDF
* Sign-up form: classes for css styling
* Skip demovox cron execution while the plugin is disabled
* More detailed statistics

= 2.3 =
* Option to require signee to confirm his email address (by entering it twice)
* Option to allow signee to set a title
* Improved cron description
* Sysinfo: improved descriptions & send realistic urls for test mails
* Improved config descriptions
* New mail placeholder {link_home} (WordPress Front Page)
* Allow {link_optin} in signup reminder mails
* New email placeholder {guid} as workaround for translation plugins which don't create translated URLs

= 2.4 =
* Support Wordpress 5.5 with PHPMailer 6

= 2.5 =
* Fix italian translation (birthdate)
* Fix Charts in backend
* Remove duplicate entries in settings

= 2.6 =
* Add Shortcodes for Street, Street No, Zip, City and Mail

= 2.6.1 =
* Remove gender-* and replace with ":"

For more details, see our commit log: https://github.com/spschweiz/demovox/commits/master