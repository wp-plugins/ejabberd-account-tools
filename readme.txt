=== Ejabberd Account Tools ===
Contributors: Beherit
Tags: xmpp, jabber, ejabberd
Donate link: http://beherit.pl/en/donations
Requires at least: 4.0
Tested up to: 4.2.2
Stable tag: 1.0.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Provide ejabberd account tools such as registration form, deleting an account, resetting account password.

== Description ==
Provide ejabberd account tools such as registration form, deleting an account (will be added soon), resetting account password (will be added soon). The plugin uses REST API (module mod_rest), is useful when the XMPP server is located on another machine. Easy to configure and use - just need to type REST API url and insert shortcuts on the page.

Plugin to work needs to install the [WordPress ReCaptcha Integration](https://wordpress.org/plugins/wp-recaptcha-integration/).

= Translations =
* English - default
* Polish (pl_PL) - by the plugin author

== Installation ==
This section describes how to install the plugin and get it working.

1. Install Ejabberd Account Tools either via the WordPress.org plugin directory, or by uploading the files to your server.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==
No questions yet.

== Changelog ==
= 1.x (2015-06-xx) =
* The ability to change/add private email address.
* Turn off autocomplete on registration form.
* Properly added a link to the settings on plugins page.

= 1.0.2 (2015-06-08) =
* Checking if selected login exists or not.
* Major changes in jQuery validation.
* Minify jQuery script.
* Additional verification in ajax to avoid cheating jQuery script.
* Proper resetting the form after success registration.
* Minor changes in translation.

= 1.0 (2015-06-06) =
* First public version.

== Upgrade Notice ==
= 1.0.2 (2015-06-08) =
* Checking if selected login exists or not.
* Major changes in jQuery validation.
* Additional verification in ajax to avoid cheating jquery script.
* Proper resetting the form after success registration.
* Minor changes in translation.

== Other Notes ==
This plugin is using [Font Awesome](https://fortawesome.github.io/Font-Awesome/).
