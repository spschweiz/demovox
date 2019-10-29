[![SP Schweiz](https://www.sp-ps.ch/sites/all/themes/sp_ps/logo.png)](https://www.sp-ps.ch)

[![Demovox](assets/logo-demovox-small.png?raw=true "Demovox")](https://demovox.ch)
# Demovox (dev readme)

[![open issues](https://img.shields.io/github/issues/spschweiz/demovox?logo=GitHub)](https://github.com/spschweiz/demovox/issues)
[![last commit](https://img.shields.io/github/last-commit/spschweiz/demovox?logo=GitHub)](https://github.com/spschweiz/demovox/issues)
[![WordPress plugin downloads](https://img.shields.io/wordpress/plugin/dt/demovox?logo=WordPress)](https://wordpress.org/plugins/demovox/)
[![WordPress plugin version](https://img.shields.io/wordpress/plugin/v/demovox?label=plugin&logo=WordPress)](https://wordpress.org/plugins/demovox/)
[![WordPress tested version](https://img.shields.io/wordpress/plugin/tested/demovox?logo=WordPress)](https://wordpress.org/plugins/demovox/)

We use Github to maintain our Code.

Install the the WordPress plugin [demovox](https://wordpress.org/plugins/demovox/) from within the Plugin manager of your WordPress installation.

If you want to install the plugin manually from a prebuilt Zip-File, visit [our Website](https://demovox.ch) and download it from there.

## Description

demovox is a tool to collect signatures for Swiss popular initiatives by offering the visitor a personalized signature sheet.

It was initially built for the popular initiative [Prämien-Entlastungs-Initiative](https://bezahlbare-praemien.ch).

## Donations

This Plugin was developed by the [Socialist Party of Switzerland](https://www.sp-ps.ch).

If you want to use this Plugin for a Swiss Referendum or a Initiative consider a donation at https://demovox.ch


## Requirements

* PHP >= 7.0
* MySQL >= 5.6.5
* WordPress >= 4.9
* SSL certificate for HTTPS (unsecure connection is only allowed for development)
* Optional feature requires the PHP modules "GMP" or "BC Math"

## Installation

1. Pull `demovox` to the Wordpress Plugin directory (/wp-content/plugins/)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in WordPress admin
4. Place shortcodes [demovox_form] on a page
5. Optionally use and [demovox_count] to get the number of collected signatures and [demovox_optin] for the opt-in form.

## Dev

### Required packages

Please install the following build dependencies:
* [Python](https://www.python.org/)
* [Ruby](https://www.ruby-lang.org/)
* [node.js](https://nodejs.org/) (tested with v8.10.0)
* [composer](https://getcomposer.org/)
* [gettext](https://packages.ubuntu.com/bionic/gettext)

Download required packages:

```
npm install
npm install grunt-cli sass -g
composer install
```

### Generate assets

Generate minified JS and CSS files and compile .mo translation files:

```
grunt buildAssets
```

### Build zip

Create a ZIP which can be uploaded to a remote WordPress installation:

```
grunt buildZip
```

### Unencrypted access

As developer you might want to work on a web server without SSL configured. Enable `WP_DEBUG` in `wp-config.php`. Then
open the Advanced settings of the plugin in the WordPress backend and disable "Redirect clients to secure HTTPS".

### Running the tests

There are no automated tests available yet, feel free to implement them and send a pull-request.

## Changelog

See README.txt

## Authors

- [@Horlacher](https://github.com/Horlacher) - Initial work
- [@tsueri](https://github.com/tsueri) - Testing, website [demovox.ch](https://demovox.ch)
- [@dbu](https://github.com/dbu) - Code review
- [@sweleck](https://github.com/sweleck) - Contributions

See also the list of [contributors](https://github.com/spschweiz/demovox/contributors) who participated in this project.

## License

This project is licensed under the GPLv3 License - see the [LICENSE.txt](LICENSE.txt) file for details.
