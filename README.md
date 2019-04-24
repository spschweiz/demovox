[![SP Schweiz](https://www.sp-ps.ch/sites/all/themes/sp_ps/logo.png)](http://sp-ps.ch)
# Demovox

We use Github to maintain our Code. If you want to use our Plugin as a prebuilt Zip-File, visit our Website and Download it from there.
You cant get the latest Stable Version at https://demovox.ch

## Description

demovox is a tool to collect signatures for Swiss popular initiatives by offering the visitor a personalized signature sheet.

## Donations

This Plugin was developed by the [Socialist Party of Switzerland](https://www.sp-ps.ch)

If you want to use this Plugin for a Swiss Referendum or a Initiative consider a donation at https://demovox.ch


## Requirements
* PHP 7
* WordPress
* SSL certificate for HTTPS (unsecure connection is not allowed)
* Optional feature requires the PHP modules "GMP" or "BC Math"

## Dev preparation

Please install the following:
* Python (https://www.python.org/)
* Ruby (https://www.ruby-lang.org/)
* node.js (https://nodejs.org/)
* composer (https://getcomposer.org/)

Download required packages:

`npm install`

`npm install grunt-cli sass -g`

`composer install`

Generate assets:

`grunt buildAssets`

Compile .mo translation files:

`grunt translations`

build zip:

`grunt buildZip`

## Installation

1. Upload directory `demovox` to the Wordpress Plugin directory (/wp-content/plugins/)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in WordPress admin
4. Place shortcodes `[demovox_form_shortcode]` and `[demovox_count_shortcode]` on a WordPress page or post
3. Configure demovox in WordPress
4. Place shortcodes [demovox_form] on a page
5. Optionally use and [demovox_count] to get the number of collected signatures and [demovox_optin] for the opt-in form.

## Unencrypted access

As developer you might want to work on a web server without SSL configured. Enable `WP_DEBUG` in `wp-config.php`. Then
open the Advanced settings of the plugin in the WordPress backend and disable "Redirect clients to secure HTTPS".

## Changelog

| Version | Description |
| ------- | ----------- |
| 1.0 | First release |
