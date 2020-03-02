[![SP Schweiz](https://www.sp-ps.ch/sites/all/themes/sp_ps/logo.png)](https://www.sp-ps.ch)

[![Demovox](assets/logo-demovox-small.png?raw=true "Demovox")](https://demovox.ch)
# Demovox (dev readme)

[![Build Status](https://travis-ci.org/spschweiz/demovox.svg?branch=master)](https://travis-ci.org/spschweiz/demovox)
[![open bugs](https://img.shields.io/github/issues/spschweiz/demovox/bug?label=bugs&logo=GitHub)](https://github.com/spschweiz/demovox/issues)
[![WordPress plugin downloads](https://img.shields.io/wordpress/plugin/dt/demovox?logo=WordPress)](https://wordpress.org/plugins/demovox/)
[![WordPress plugin version](https://img.shields.io/wordpress/plugin/v/demovox?label=plugin&logo=WordPress)](https://wordpress.org/plugins/demovox/)
[![WordPress tested version](https://img.shields.io/wordpress/plugin/tested/demovox?logo=WordPress)](https://wordpress.org/plugins/demovox/)

## Description

demovox is a tool to collect signatures for Swiss popular initiatives by offering the visitor a personalized signature sheet.

This Plugin was developed by the [Socialist Party of Switzerland](https://www.sp-ps.ch), it was initially built for the popular initiative [Prämien-Entlastungs-Initiative](https://bezahlbare-praemien.ch).

## Donations
If this plugin is of help for you, please consider a [donation](https://demovox.ch). 

## Requirements

* PHP >= 7.0 (feature "Hashid" requires >= 7.1.3)
* MySQL >= 5.6.5
* WordPress >= 4.9
* SSL certificate for HTTPS (unsecure connection is only allowed for development)
* Optional feature requires the PHP modules "GMP" or "BC Math"

## Installation (production)

Simply install the the WordPress plugin [demovox](https://wordpress.org/plugins/demovox/) from within the Plugin manager of your WordPress installation. You can find the admin manual or the prebuilt Zip-File for manual installation on [demovox.ch](https://demovox.ch).

## Dev

Please send a pull request for any improvements on the plugin. 

### Installation

1. Pull `demovox` from within the Wordpress Plugin directory (/wp-content/plugins/)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in WordPress admin
4. Place shortcodes [demovox_form] on a page
5. Optionally use and [demovox_count] to get the number of collected signatures and [demovox_optin] for the opt-in form.

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
composer install --no-dev
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

### Required packages for tests
```
composer install
bin/install-wp-tests.sh  <db-name> <db-user> <db-pass> [db-host]
```

### Running the tests

There are just a few PHPUnit tests available yet, feel free to contribute some and send a pull-request to increase the test coverage.
```
grunt test
```

### Unencrypted access

As a developer you might want to work on a web server without SSL configured. Enable `WP_DEBUG` in `wp-config.php`. Then
open the Advanced settings of the plugin in the WordPress backend and disable "Redirect clients to secure HTTPS".

## Changelog

See README.txt

## Authors

- [@Horlacher](https://github.com/Horlacher) - Initial work
- [@tsueri](https://github.com/tsueri) - Testing, website [demovox.ch](https://demovox.ch)
- [@dbu](https://github.com/dbu) - Code review
- [@sweleck](https://github.com/sweleck) - Contribution

See also the list of [contributors](https://github.com/spschweiz/demovox/contributors) who participated in this project.

## License

This project is licensed under the GPLv3 License - see the [LICENSE.txt](LICENSE.txt) file for details.
