[![SP Schweiz](https://www.sp-ps.ch/sites/all/themes/sp_ps/logo.png)](https://www.sp-ps.ch)

[![Demovox](assets/logo-demovox-small.png?raw=true "Demovox")](https://demovox.ch)
# Demovox (dev readme)

[![Build Status](https://travis-ci.org/spschweiz/demovox.svg?branch=master)](https://travis-ci.org/spschweiz/demovox)
[![open bugs](https://img.shields.io/github/issues/spschweiz/demovox/bug?label=bugs&logo=GitHub)](https://github.com/spschweiz/demovox/issues)
[![WordPress plugin downloads](https://img.shields.io/wordpress/plugin/dt/demovox?logo=WordPress)](https://wordpress.org/plugins/demovox/)
[![WordPress plugin version](https://img.shields.io/wordpress/plugin/v/demovox?label=plugin&logo=WordPress)](https://wordpress.org/plugins/demovox/)
[![WordPress tested version](https://img.shields.io/wordpress/plugin/tested/demovox?logo=WordPress)](https://wordpress.org/plugins/demovox/)

## Description

WordPress plugin [demovox](https://wordpress.org/plugins/demovox/) is a tool to collect signatures for Swiss popular initiatives by offering the visitor a personalized signature sheet.

This Plugin was developed by the [Socialist Party of Switzerland](https://www.sp-ps.ch), it was initially built for the popular initiative [Prämien-Entlastungs-Initiative](https://bezahlbare-praemien.ch) in 2019. It has been used for numerous others since, like [umverkehR](https://www.umverkehr.ch/) (2020) and [AHVx13](https://www.ahvx13.ch/) (2020) by [SGB](https://www.sgb.ch), to name a few.   

## Requirements

* PHP >= 7.4
* MySQL >= 5.6.5
* WordPress >= 4.9
* SSL certificate for HTTPS (non-https is only allowed for development)
* Optional feature requires the PHP modules "GMP" or "BC Math"

## Productive installation 

Simply install the WordPress plugin [demovox](https://wordpress.org/plugins/demovox/) from within the Plugin manager of your WordPress installation. You can find the admin manual or the prebuilt Zip-File for manual installation on [demovox.ch](https://demovox.ch).

If this plugin is of help for you, please consider a [donation](https://demovox.ch) and write a review on the [demovox WordPress plugin page](https://wordpress.org/plugins/demovox/).

## Development

Please send us a pull request for any improvements on the plugin. 

### Installation

1. Pull `demovox` from git
2. Use the webserver `wordpress` container from `docker-compose.yaml` (for development purposes), which includes a [WordPress webserver](http://localhost:80/) and [mailhog](http://localhost:8025/) for mail testing.
   Or use your own webserver (see below).
3. Install required project packages and generate assets with grunt (see below)
4. Activate the plugin through the 'Plugins' menu in [local WordPress](http://localhost:80/)
5. Place shortcodes [demovox_form] on a page or use the automatically generated page
6. Optionally use and [demovox_count] to show the number of collected signatures and [demovox_optin] for opt-in edit form
7. Configure the plugin in WordPress admin.
Allow non-https access in the advanced settings of the plugin and disable "Redirect clients to secure HTTPS".

#### Own webserver

You can use your own webserver (see requirements above) and map the demovox directory to `[WordPressDir]/wp-content/plugins/demovox`.

While developing, you might want to work on a web server without SSL. Enable `WP_DEBUG` in `wp-config.php` and you probably want to disable `WP_DEBUG_DISPLAY`. Then
open the Advanced settings of the plugin in the WordPress backend and disable "Redirect clients to secure HTTPS".

### Building assets

#### Docker container

Start `buildserver` from `docker/docker-compose.yaml`. The plugin is monted in `/var/demovox/`

#### Manual installation (instead of docker container)

If you don't want to use the docker container, install the following build dependencies:
[Python](https://www.python.org/), [Ruby](https://www.ruby-lang.org/),
[node.js](https://nodejs.org/) (use v8.10.0 as po2mo fails with higher versions), [composer](https://getcomposer.org/),
[gettext](https://packages.ubuntu.com/bionic/gettext).
Install required NPM packages with `npm install grunt-cli sass -g`
 
#### Install required project packages

Go to demovox directory (e.g. `cd /var/demovox/`)
```
npm install
composer install --no-dev
```

Install required packages for tests:
```
composer install
chmod +x bin/install-wp-tests.sh
bin/install-wp-tests.sh wordpress_test root root demovox-db
```

#### Grunt Commands

##### Generate assets for development

Generate minified JS and CSS files and compile .mo translation files:
```
grunt buildAssets
```

##### Build for wordpress.org repo

Build plugin snapshot in `demovox/buildWpOrg` which can be uploaded to the WordPress.org repository:
```
grunt buildWpOrg
```

##### Running the tests

There are just a few PHPUnit tests available yet, feel free to contribute some and send a pull-request to increase the test coverage.
```
grunt test
```

##### Other commands

Build plugin snapshot as ZIP in `demovox/demovox.zip`, which can be uploaded to a remote WordPress installation:
```
grunt buildZip
```

Show all other available commands:
```
grunt availabletasks
```

#### Generate admin settings .po files
Enable WP_DEBUG and open the System info (WP backend -> demovox -> System info) to generate the .po file for the setting strings.

## Changelog

See README.txt and [commit log](https://github.com/spschweiz/demovox/commits/master).

## Authors

See also the list of [contributors](https://github.com/spschweiz/demovox/contributors) who participated in this project.
Thanks to [@dbu](https://github.com/dbu) for code review.

## License

This project is licensed under the GPLv3 License - see the [LICENSE.txt](LICENSE.txt) file for details.
