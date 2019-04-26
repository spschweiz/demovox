[![SP Schweiz](https://www.sp-ps.ch/sites/all/themes/sp_ps/logo.png)](https://www.sp-ps.ch)

[![Demovox](assets/logo-demovox-small.png?raw=true "Demovox")](https://demovox.ch)
# Demovox

We use Github to maintain our Code. If you want to use our WordPress Plugin as a prebuilt Zip-File, visit our Website and download it from there.

Get the latest Stable Version: https://demovox.ch

## Description

demovox is a tool to collect signatures for Swiss popular initiatives by offering the visitor a personalized signature sheet.

It was initially built for the popular initiative [Prämien-Entlastungs-Initiative](https://bezahlbare-praemien.ch).

## Donations

This Plugin was developed by the [Socialist Party of Switzerland](https://www.sp-ps.ch).

If you want to use this Plugin for a Swiss Referendum or a Initiative consider a donation at https://demovox.ch


## Requirements

* PHP 7
* WordPress
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

Please install the following:
* [Python](https://www.python.org/)
* [Ruby](https://www.ruby-lang.org/)
* [node.js](https://nodejs.org/) (tested with v8.10.0)
* [composer](https://getcomposer.org/)

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

| Version | Description |
| ------- | ----------- |
| 1.0 | First release |

## Authors

Thanks goes to these wonderful people:
- [@horlacher](https://github.com/Horlacher) - Initial work
- [@tsueri](https://github.com/tsueri) - Testing, website [demovox.ch](https://demovox.ch)
- [@dbu](https://github.com/dbu) - Code review
- [@sweleck](https://github.com/sweleck) - Contributions

See also the list of [contributors](https://github.com/spschweiz/demovox/contributors) who participated in this project.

## License

This project is licensed under the GPLv3 License - see the [LICENSE.txt](LICENSE.txt) file for details.
