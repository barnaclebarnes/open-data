=== WP-DB-Backup ===
Contributors: filosofo, skippy, Firas, LaughingLizard, MtDewVirus, Podz, Ringmaster
Donate link: http://www.ilfilosofo.com/blog/wp-db-backup/
Tags: mysql, database, backup, cron
Requires at least: 2.0.3
Tested up to: 2.8
Stable tag: 2.2.2

On-demand backup of your WordPress database.

== Description ==

WP-DB-Backup allows you easily to backup your core WordPress database tables.  You may also backup other tables in the same database.

Released under the terms of the GNU GPL, version 2.
   http://www.fsf.org/licensing/licenses/gpl.html

              NO WARRANTY.

	Copyright (c) 2008 Austin Matzko

== Installation ==
1. Copy the wp-db-backup.php file to /wp-content/plugins/
1. Activate the plugin at your blog's Admin -> Plugins screen
1. The plugin will attempt to create a directory /wp-content/backup-*/ inside your WordPress directory.
1. You may need to make /wp-content writable (at least temporarily) for it to create this directory. 
   For example:
   `$ cd /wordpress/`
   `$ chgrp www-data wp-content` (where "`www-data`" is the group your FTP client uses)
   `$ chmod g+w wp-content`

== Frequently Asked Questions ==

= How do I restore my database from a backup? =

Briefly, use phpMyAdmin, which is included with most hosting control panels. More details and links to further explanations are [here](http://codex.wordpress.org/Restoring_Your_Database_From_Backup).

= Why can't I schedule automatic backups to be saved to my server? =

Although WP-DB-Backup provides the option of saving the backup file to the server, I strongly discourage anyone from leaving backed-up database files on the server. If the server is not perfectly configured, then someone could gain access to your data, and I do not want to make it easy for that to happen.

= My backup stops or hangs without completing. =

If you edit the text of wp-db-backup.php in a text editor like Notepad, you’ll see around line 50 the following:

`/**
* Set MOD_EVASIVE_OVERRIDE to true
* and increase MOD_EVASIVE_DELAY
* if the backup stops prematurely.
*/
// define('MOD_EVASIVE_OVERRIDE', false);
define('MOD_EVASIVE_DELAY', '500');`

Do what it says: un-comment MOD_EVASIVE_OVERRIDE and set it to true like so:

`define('MOD_EVASIVE_OVERRIDE', true);`

That will slow down the plugin, and you can slow it even further by increasing the MOD_EVASIVE_DELAY number from 500.

Better yet, put the lines that define the `MOD_EVASIVE_OVERRIDE` and `MOD_EVASIVE_DELAY` constants in your wp-config.php file, so your settings don't get erased when you upgrade the plugin.

= What is wp-db-backup.pot for? =

This files is used by non-English users to translate the display into their native language.  Translators are encouraged to send me translated files, which will be made available to others here:
http://www.ilfilosofo.com/blog/wp-db-backup/i18n/
http://dev.wp-plugins.org/browser/wp-db-backup/i18n/

= Why are only the core database files backed up by default? =

Because it's a fairly safe bet that the core WordPress files will be successfully backed up.  Plugins vary wildly in the amount of data that they store.  For instance, it's not uncommon for some statistics plugins to have tens of megabytes worth of visitor statistics.  These are not exactly essential items to restore after a catastrophic failure.  Most poeple can reasonably live without this data in their backups.

== Usage ==
1. Click the Manage menu in your WordPress admin area.
1. Click the Backup sub-menu.

1. The plugin will look for other tables in the same database.  You may elect to include other tables in the backup.
  ** NOTE **
  Including other tables in your backup may substantially increase the size of the backup file!
  This may prevent you from emailing the backup file because it's too big.

1. Select how you'd like the backup to be delivered:
* Save to server : this will create a file in /wp-content/backup-*/ for you to retreive later
* Download to your computer : this will send the backup file to your browser to be downloaded
* Email : this will email the backup file to the address you specify

1. Click "Backup!" and your database backup will be delivered to you.

The filename of the backup file will be of the form
   DB_prefix_date.sql
DB = the name of your WordPress database, as defined in wp-config.php
prefix = the table prefix for this WordPress blog, as defined in wp-config.php
date = CCYYmmdd_B format:  20050711_039
       the "B" is the internet "Swatch" time.  
       See the PHP date() function for details.

When having the database backup emailed or sent to your browser for immediate download, the backup file will be _deleted_ from the server when the transfer is finished.  Only if you select delivery method "Save to server" will the backup file remain on your server.

   *** SECURITY WARNING ***
   Your database backup contains sensitive information,
   and should not be left on the server for any extended
   period of time.  The "Save to server" delivery method is provided
   as a convenience only.  I will not accept any responsibility
   if other people obtain your backup file.
   *** SECURITY WARNING ***

== Advanced ==
If you are using WordPress version 2.1 or newer, you can schedule automated backups to be sent to the email address 
of your choice.

== Translators ==
Thanks to following people for providing translation files for WP-DB-Backup:
* Gilles Wittezaele
* İzzet Emre Erkan
* Michele Spagnuolo
* Rune Gulbrandsøy
* Sergey Biryukov
* Tai
* Timm Severin
* 吴曦
