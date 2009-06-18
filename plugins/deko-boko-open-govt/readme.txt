=== Deko Boko ===
Contributors: toppa
Donate link: http://www.toppa.com/deko-boko-wordpress-plugin
Tags: email, contact, spam, captcha
Requires at least: 2.5
Tested up to: 2.8
Stable tag: 1.2.2

Deko Boko is a simple but highly extensible contact form, integrating reCAPTCHA for handling spam.

== Description ==

Why write yet another email contact form for WordPress? There are two things that make Deko Boko unique:

1. It uses [reCAPTCHA](http://recaptcha.net/) for handling spam. reCAPTCHA is a great project that uses data from its captcha forms to help digitize books.

2. The Deko Boko contact form can be extended any way you want, but without the need for complicated admin menus. If you're comfortable editing HTML, then you can add any number and any type of input fields to the contact form. You can control which fields are optional or required. When the form is submitted, any fields that you added will have their data included in the body of the email.

**Additional Features**

* The form layout is controlled by a CSS styled list, which provides a great deal of flexibility. With CSS edits you can change the position of the field labels to top-aligned, left-justified, or right-justified. Deko Boko uses the techniques outlined in [Cameron Adam's excellent article on form layout](http://www.sitepoint.com/article/fancy-form-design-css).

* Plays nicely with [WP-reCAPTCHA](http://wordpress.org/extend/plugins/wp-recaptcha/), the WordPress plugin for using reCAPTCHA to protect against comment spam. If you already have a API key set up with WP-reCAPTCHA, Deko Boko will automatically use it.

* Includes selectors for using different themes and languages with the reCAPTCHA widget, as well as support for custom CSS for the reCAPTCHA widget.

* Support for multiple, custom contact forms.

* "CC Me" option for users to receive a copy of the message they submit to you. You can specify header text and footer text to "wrap" this message. Deko Boko can automatically include the name of your blog and a timestamp in the header or footer text.

* Security in addition to reCAPTCHA is included. Deko Boko protects against email header injections and XSS attacks.

**New Features in Version 1.2**

* You can have Deko Boko load its stylesheet only on pages where you use the Deko Boko contact form, so it won't be loaded unnecessarily on other pages.

* Localization support: a dekoboko.pot file is included to enable translations to other languages. French and Spanish translations included in version 1.2.2.

* A sample form is included, to help you make your own custom contact form.

* You can put a custom copy of dekoboko.css in your active theme folder, so you won't lose your stylesheet customizations when upgrading Deko Boko.

* Uninstall option.

**Bug Fixes**

1.2.1: correctly saves the reCAPTCHA API keys if they hadn't been set previously (was failing to save if WP-reCAPTCHA hadn't been installed previously); correctly cleans up old-style Deko Boko options from the database.

1.2.2: fixes display of selected language in the Settings form (was always defaulting to English); added French and Spanish translations.

== Installation ==

**Installation**

Download the zip file, unzip it, and copy the "dekoboko" folder to your plugins directory. Then activate it from your plugin panel. After successful activation, Deko Boko will appear under your "Settings" tab. Note that Deko Boko requires WordPress 2.5 or higher.

*Important note to upgraders from version 1.1 or earlier:* you will need to deactivate and then reactivate Deko Boko after you upload the new files. Also, the contact form now uses a [nonce field](http://codex.wordpress.org/WordPress_Nonces) for additional security. If you have made your own contact form template, you will need to add a nonce hidden input field, like this:

&lt;?php wp\_nonce\_field('dekoboko\_nonce', 'dekoboko\_nonce'); ?&gt;

== Frequently Asked Questions ==

Please go to [the Deko Boko page on my site](http://www.toppa.com/deko-boko-wordpress-plugin) for a Usage Guide and other information.

For troubleshooting help, please [post a comment in my latest Deko Boko post](http://www.toppa.com/category/wordpress-and-web-programming/deko-boko/).
