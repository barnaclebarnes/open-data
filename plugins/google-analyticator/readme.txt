=== Google Analyticator ===
Contributors: cavemonkey50, jesse_altman, spiralwebconsulting
Donate link: http://plugins.spiralwebconsulting.com/analyticator.html#donate
Tags: stats, statistics, google, analytics, google analytics, tracking, widget
Requires at least: 2.7
Tested up to: 2.8.3
Stable tag: 5.3

Adds the necessary JavaScript code to enable Google Analytics. Includes widgets for Analytics data display.

== Description ==

Google Analyticator adds the necessary JavaScript code to enable Google Analytics logging on any WordPress blog. This eliminates the need to edit your template code to begin logging. Google Analyticator also includes several widgets for displaying Analytics data in the admin and on your blog.

*Google Analyticator is brought to you for free by [Spiral Web Consulting](http://spiralwebconsulting.com/). Spiral Web Consulting is a small web development firm specializing in PHP development. Visit our website to learn more, and don't hesitate to ask us to develop your next big WordPress plugin idea.*

= Features =

Google Analyticator Has the Following Features:

- Supports standard Google Analytics tracking
- **NEW!** Includes an admin dashboard widget that displays a graph of the last 30 days of visitors, a summary of site usage, the top pages, the top referrers, and the top searches
- Includes a widget that can be used to display visitor stat information on the front-end
- Supports outbound link tracking of all links on the page, including links not managed by WordPress
- Supports download link tracking
- Supports event tracking with outbound links / downloads instead of the old pageview tracking method
- Allows hiding of Administrator visits without affecting Google Analytics' site overlay feature
- Supports any advanced tracking code Google provides
- Installs easily - unlike other plugins, the user doesn't even have to know their Analytics UID
- Allows tracking code to be placed in the footer to ensure faster load times
- Provides complete control over options; disable any feature if needed
- **NEW!** Supports localization - get the settings page in your language of choice

For more information, visit the [Google Analyticator plugin page](http://plugins.spiralwebconsulting.com/analyticator.html).

== Installation ==

Please visit [Spiral Web Consulting's forum](http://plugins.spiralwebconsulting.com/forums/viewtopic.php?f=5&t=17) for installation information.

== Frequently Asked Questions ==

Please visit [Spiral Web Consulting's forum](http://plugins.spiralwebconsulting.com/forums/viewforum.php?f=5) for the latest FAQ information.

== Screenshots ==

1. An example of the admin dashboard widget displaying stats pulled from Google Analytics.
2. The top half of the settings page.
3. The configuration options for the front-end widget.
4. An example of a front-end widget configuration.
5. An example of a front-end widget configuration.
6. An example of a front-end widget configuration.

== Changelog ==

= 5.3 =
* Converts API data call to AJAX to reduce the memory needed on page loads.
* Removes memory_limit alterations, since the default amount should be enough now.
* Disables the summary dashboard widget for non-admins, as defined by the admin level setting on Google Analyticator's settings page.

= 5.2.1 =
* Corrects a potential html sanitation vulnerability with text retrieved from the Google Analytics API.

= 5.2 =
* Adds support for deauthorizing with Google Analytics.
* Increases checks on the memory limit and now prevents the memory intensive functionality from running if there is insufficient memory.
* Adds authentication compatibility modes for users having issues with cURL and PHP Streams.
* Improves detection of Google Accounts that are not linked to Analytics accounts.
* Improves detection of accounts without stats.
* Cleans up the authentication URL, preventing the malformed URL error that Google would sometimes display.
* Removes hosted Google accounts from Google's authentication page.
* Adds an error message to the settings page if Google authentication fails.

= 5.1 =
* Fixes the broken/frozen error on the Dashboard introduced in Google Analyticator 5.0.
* Fixes an Internal Server Error received on the settings page under IIS servers.
* Adds an option to completely disable the included widgets.
* Removes the outbound and download prefixes from the Javascript if event tracking is enabled.
* Fixes a bug where the settings page always thought the Google account was authenticated.
* Prevents the Google API from even attempting to connect to Google's servers if the account is not authenticated.
* Increases the checks on returned Google API data to prevent unexpected states.
* Adds missing localized string to settings title.
* Removes the Google authentication and widgets from WordPress 2.7 due to compatibility issues. Users wishing to authenticate and use the widgets should upgrade to WordPress 2.8.
* Prevents PHP warnings from displaying on the dashboard summary widget when an Analytics account is new and does not have a history of data.

= 5.0 =
* Adds a new admin dashboard widget that displays a graph of the last 30 days of visitors, a summary of site usage, the top pages, the top referrers, and the top searches.
* Changes the Google authentication method to AuthSub. This removes the Google username / password requirement. **Users who had previously entered their username / password in the settings page will need to revisit the settings page and authenticate for the widget to continue to function.**
* Adds support for automatically retrieving an Analytics account's UID if Google Analyticator is authenticated with Google.
* Updates the Google Analytics API class to use the WordPress HTTP API, thus removing cURL as a core requirement for the widget.
* Updates the UID setting help to remove old urchin.js references and provide additional help for finding a UID.
* Prepares all strings for localization. If you would like to translate Google Analyticator, [visit our forums](http://plugins.spiralwebconsulting.com/forums/viewforum.php?f=16).

= 4.3.4 =
* Fixes a bug that was breaking the save button on the settings page in IE.
* Prevents the widget from grabbing Analytics data earlier January 1, 2005.
* Fixes an incorrect default state for the event tracking option.
* Adds the date range used for widget data in an HTML comment to prevent misrepresented stats.

= 4.3.3 =
* Corrects XHTML validator errors present in the stat widget.
* Removes some stray code.

= 4.3.2 =
* Adds support for WordPress' new changelog readme.txt standard. Version information is now available from within the plugin updater.
* Enhances the links on the plugin page. Adds a settings, FAQ, and support link.

= 4.3.1 =
* Fixes a bug that broke the widget page when a username was not entered in settings.

= 4.3 =
* Adds support for event tracking of outbound links and downloads. This is the new, recommended way to track outbound links and downloads with Analytics. Event tracking is enabled by default, so users wishing to keep using the old method should disable this option immediately. See our FAQ for more information.
* Prevents files that are stored on external servers from being tracked as both a download and an external link.
* Corrects a file extension case sensitivity issue that prevented certain download links from being tracked.
* Includes a minified version of the outbound/download tracking javascript instead of the full code.
* Fixes a text size inconstancy on the settings page.

= 4.2.3 =
* Improves error reporting with API authentication.

= 4.2.2 =
* Fixes a bug in IE8 that would not allow the widget to display in the admin properly.

= 4.2.1 =
* Fixes an issue where stable versions of WordPress 2.8 were not using the new widget API.
* Changes SimplePie include to use WordPress' version if possible, since SimplePie is included in WordPress 2.8.
* Adds version number to the Google Analyticator comment.

= 4.2 =
* Adds support for the WordPress 2.8 widget API.
* Removes Google Analyticator comment in the header that if footer tracking is enabled.

= 4.1.1 =
* Adds support for tracking code in the footer with Adsense integration.
* Corrects the widget image location for users with WordPress installed in a sub-directory.
* Prevents Google API calls when widget information is not configured.
* Supports WordPress 2.8.

= 4.1 =
* Fixes a bug that was causing the Stats Widget to display "0" in every instance.
* Adds functionality to allow a custom timeframe to be configured for the visitors widget.
* Adds a function to enable use of the widget for users not using WordPress widgets.
* Adds an option to output the code needed to link Analytics and Adsense accounts.

= 4.0.1 =
* Disables stat widget if cURL does not exist.

= 4.0 =
* Adds Google Analytics API support.
* Adds a widget that will use your Google Analytics stats to display a visitor counter on your front-end.
* Adds functionality to make widget highly customizable in regards to color and text.

= 3.0.3 =
* Fixes a Javascript error on pages that have links without href tags.

= 3.0.2 =
* Improves display of external/download links in Google Analytics (strips http/https from url).
* Fixes a PHP warning message being displayed on pages with error reporting enabled.

= 3.01 =
* Adds an option to disable admin tracking by either removing the tracking code or setting a variable.
* Removes the external tracking code from back-end admin pages that was causing conflicts with other plugins.

= 3.0 =
* Google Analyticator is now supported by Spiral Web Consulting.
* Corrects bugs preventing both external and download link tracking from working.
* Adds settings to configure the external and download link tracking prefixes.
* Changes the way disabling admin tracking works. Now uses a line of code instead of removing the tracking code altogether. This will allow features like the site overlay to work.

= 2.40 =
* Replaces the PHP-based external tracking solution with a jQuery-based one.

= 2.3 =
* Updates the Analytics script to match a change by Google. This should resolve the undefined _gat errors.

= 2.24 =
* Fixes comment author issues once and for all.
* Fixes a SVN merge issue that prevented people from getting the last update.

= 2.23 =
* Reverting last version as it caused issues.

= 2.22 =
* Improves comment author regex causing some issues in WordPress 2.7. Thanks to jdub.

= 2.21 =
* Adds WordPress 2.7 support.

= 2.2 =
* Adds an option to specify the GA script location instead of relying on Google’s auto detect code. This may resolve the _gat is undefined errors.

= 2.14 =
* Stops the external link tracking code from appearing in feeds, breaking feed validation.
* Adds compatibility for a very rare few users who could not save options.

= 2.13 =
* Stops the external link tracking code from appearing in feeds, breaking feed validation.

= 2.12 =
* Applies the new administrator level selection to outbound tracking (I forgot to that in the last release).
* Fixes a potential plugin conflict.

= 2.11 =
* Adds an option to change what Google Analyticator considers a WordPress administrator.

= 2.1 =
* Fixes a bug preventing options from being saved under WordPress 2.5.
* Updates option page to comply with WordPress 2.5 user interface changes.
* Note: Users of WordPress 2.3 may wish to stay on 2.02 as the UI will look ‘weird’ under 2.3.

= 2.02 =
* Corrects potential XHTML validation issues with external link tracking.

= 2.01 =
* Corrects XHTML validation issues with ga.js.

= 2.0 =
* Adds support for the latest version of Google Analytics’ tracking code (ga.js).
* Reverts external link/download tracking method back to writing the tracking code in the HTML source, due to the previous Javascript library no longer being support. Users of previous Google Analyticator versions may safely delete ga_external-links.js.
* Slightly modified the way extra code is handled. There are now two sections (before tracker initialization and after tracker initialization) to handle ga.js’ extra functions. Refer to Google Analytics’ support documentation for use of these sections.

= 1.54 =
* Corrects problem where certain installation of WordPress do not have the user level value.

= 1.53 =
* Finally fixes the “Are you sure?” bug some users experience.

= 1.52 =
* Addresses compatibility issue with other JavaScript plugins.

= 1.5 =
* Now using JavaScript solution for keeping track of external links instead of the current URL rewrite method. JavaScript library is courtesy of Terenzani.it.
* Note: Google Analyticator is now in a folder. If upgrading from a version less than 1.5, delete google-analyticator.php from your /wp-content/plugins/ folder before proceeding.

= 1.42 =
* Fixes a bug where outbound link tracking would be disabled if the tracking code was in the footer.

= 1.41 =
* Added an option to insert the tracking code in the footer instead of the header.

= 1.4 =
* Adds support for download tracking.

= 1.31 =
* Fixes a small bug with backslashes in the additional tracking code box.

= 1.3 =
* WordPress 2.0 beta is now supported.
* Missing options page bug is finally fixed.

= 1.2 =
* Added support for outbound links.

= 1.12 =
* Fixing missing option button page bug.

= 1.11 =
* Fixed a bug where options page would sometimes not display.

= 1.1 =
* Added an option to disable administrator logging.
* Added an option to add any additional tracking code that Google has.

= 1.0 =
* Initial release.