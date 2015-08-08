=== Ejabberd Account Tools ===
Contributors: Beherit
Tags: xmpp, jabber, ejabberd
Donate link: http://beherit.pl/en/donations
Requires at least: 4.0
Tested up to: 4.3
Stable tag: 1.3.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Provide ejabberd account tools such as the registration form, deleting the account, resetting the account password.

== Description ==
Provide ejabberd account tools such as the registration form, deleting the account, resetting the account password. The plugin uses REST API (module mod_rest), is useful when the XMPP server is located on another machine. Easy to configure and use - just need to type REST API url and insert shortcuts on the page.

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
= 1.3.1 (2015-08-08) =
* Update FontAwesome.
= 1.3 (2015-07-23) =
* Added form to resetting the account password.
* Added form to deleting the account.
* Removing incorrect parameters from URL added to the emails.
* Changed the method of adding hints.
* Checking current private email address before sending message to change it.
* Repair captcha validation.
* Changed the form-response box style.
* Rename scripts files.
* Translation of the plugin metadata.
* Updated translations.
* Minor bugfix and changes.
= 1.2 (2015-06-30) =
* Added ability to show information hints on forms.
* Added more data to transients.
* Changes in default blocked logins regexp.
* Getting the properly default email address.
* Validating email address by checking MX record.
* Added vhosts support in changing email.
* Other minor changes.
= 1.1.2 (2015-06-24) =
* Removing slashes from the passwords.
* Improved post data.
* Minor changes in sending mails.
= 1.1 (2015-06-24) =
* The ability to change/add private email address.
* Turn off autocomplete on registration form.
* Properly added a link to the settings on plugins page.
* Small changes in translations.
* Minor visual changes.
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
= 1.3.1 =
* Update FontAwesome.
= 1.3 =
* Added form to resetting the account password.
* Added form to deleting the account.
* Removing incorrect parameters from URL added to the emails.
* Changed the method of adding hints.
* Checking current private email address before sending message to change it.
* Repair captcha validation.
* Changed the form-response box style.
* Rename scripts files.
* Translation of the plugin metadata.
* Updated translations.
* Minor bugfix and changes.
= 1.2 =
* Added ability to show information hints on forms.
* Added more data to transients.
* Changes in default blocked logins regexp.
* Getting the properly default email address.
* Validating email address by checking MX record.
* Added vhosts support in changing email.
* Other minor changes.
= 1.1.2 =
* Removing slashes from the passwords.
* Improved post data.
* Minor changes in sending mails.
= 1.1 =
* The ability to change/add private email address.
* Turn off autocomplete on registration form.
* Properly added a link to the settings on plugins page.
* Small changes in translations.
* Minor visual changes.
= 1.0.2 =
* Checking if selected login exists or not.
* Major changes in jQuery validation.
* Minify jQuery script.
* Additional verification in ajax to avoid cheating jQuery script.
* Proper resetting the form after success registration.
* Minor changes in translation.
= 1.0 =
* First public version.

== Other Notes ==
This plugin is using [HINT.css](https://github.com/chinchang/hint.css) and [Font Awesome](https://fortawesome.github.io/Font-Awesome/).