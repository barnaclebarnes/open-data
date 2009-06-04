<?php 
/*
 * Plugin Name: Google Analyticator
 * Version: 4.1.1
 * Plugin URI: http://plugins.spiralwebconsulting.com/analyticator.html
 * Description: Adds the necessary JavaScript code to enable <a href="http://www.google.com/analytics/">Google's Analytics</a>. After enabling this plugin visit <a href="options-general.php?page=google-analyticator.php">the settings page</a> and enter your Google Analytics' UID and enable logging.
 * Author: Spiral Web Consulting
 * Author URI: http://spiralwebconsulting.com/
 */

# Include Google Analytics Stats widget
if ( function_exists('curl_init') ) {
	require_once('google-analytics-stats.php');
	$google_analytics_stats = new GoogleStatsWidget();
}

// Constants for enabled/disabled state
define("ga_enabled", "enabled", true);
define("ga_disabled", "disabled", true);

// Defaults, etc.
define("key_ga_uid", "ga_uid", true);
define("key_ga_status", "ga_status", true);
define("key_ga_admin", "ga_admin_status", true);
define("key_ga_admin_disable", "ga_admin_disable", true);
define("key_ga_admin_level", "ga_admin_level", true);
define("key_ga_adsense", "ga_adsense", true);
define("key_ga_extra", "ga_extra", true);
define("key_ga_extra_after", "ga_extra_after", true);
define("key_ga_outbound", "ga_outbound", true);
define("key_ga_outbound_prefix", "ga_outbound_prefix", true);
define("key_ga_downloads", "ga_downloads", true);
define("key_ga_downloads_prefix", "ga_downloads_prefix", true);
define("key_ga_footer", "ga_footer", true);
define("key_ga_specify_http", "ga_specify_http", true);

define("ga_uid_default", "XX-XXXXX-X", true);
define("ga_status_default", ga_disabled, true);
define("ga_admin_default", ga_enabled, true);
define("ga_admin_disable_default", 'remove', true);
define("ga_admin_level_default", 8, true);
define("ga_adsense_default", "", true);
define("ga_extra_default", "", true);
define("ga_extra_after_default", "", true);
define("ga_outbound_default", ga_enabled, true);
define("ga_outbound_prefix_default", 'outgoing', true);
define("ga_downloads_default", "", true);
define("ga_downloads_prefix_default", "download", true);
define("ga_footer_default", ga_disabled, true);
define("ga_specify_http_default", "auto", true);

// Create the default key and status
add_option(key_ga_status, ga_status_default, 'If Google Analytics logging in turned on or off.');
add_option(key_ga_uid, ga_uid_default, 'Your Google Analytics UID.');
add_option(key_ga_admin, ga_admin_default, 'If WordPress admins are counted in Google Analytics.');
add_option(key_ga_admin_disable, ga_admin_disable_default, '');
add_option(key_ga_admin_level, ga_admin_level_default, 'The level to consider a user a WordPress admin.');
add_option(key_ga_adsense, ga_adsense_default, '');
add_option(key_ga_extra, ga_extra_default, 'Addition Google Analytics tracking options');
add_option(key_ga_extra_after, ga_extra_after_default, 'Addition Google Analytics tracking options');
add_option(key_ga_outbound, ga_outbound_default, 'Add tracking of outbound links');
add_option(key_ga_outbound_prefix, ga_outbound_prefix_default, 'Add tracking of outbound links');
add_option(key_ga_downloads, ga_downloads_default, 'Download extensions to track with Google Analyticator');
add_option(key_ga_downloads_prefix, ga_downloads_prefix_default, 'Download extensions to track with Google Analyticator');
add_option(key_ga_footer, ga_footer_default, 'If Google Analyticator is outputting in the footer');
add_option(key_ga_specify_http, ga_specify_http_default, 'Automatically detect the http/https settings');

// Create a option page for settings
add_action('admin_init', 'ga_admin_init');
add_action('admin_menu', 'add_ga_option_page');

// Initialize the options
function ga_admin_init() {
	// Register out options so WordPress knows about them
	if ( function_exists('register_setting') ) {
		register_setting('google-analyticator', key_ga_status, '');
		register_setting('google-analyticator', key_ga_uid, '');
		register_setting('google-analyticator', key_ga_admin, '');
		register_setting('google-analyticator', key_ga_admin_disable, '');
		register_setting('google-analyticator', key_ga_admin_level, '');
		register_setting('google-analyticator', key_ga_adsense, '');
		register_setting('google-analyticator', key_ga_extra, '');
		register_setting('google-analyticator', key_ga_extra_after, '');
		register_setting('google-analyticator', key_ga_outbound, '');
		register_setting('google-analyticator', key_ga_outbound_prefix, '');
		register_setting('google-analyticator', key_ga_downloads, '');
		register_setting('google-analyticator', key_ga_downloads_prefix, '');
		register_setting('google-analyticator', key_ga_footer, '');
		register_setting('google-analyticator', key_ga_specify_http, '');
	}
}

// Initialize outbound link tracking
add_action('init', 'ga_outgoing_links');

// Hook in the options page function
function add_ga_option_page() {
	global $wpdb;
	add_options_page('Google Analyticator Settings', 'Google Analytics', 8, basename(__FILE__), 'ga_options_page');
}

function ga_options_page() {
	// If we are a postback, store the options
	if (isset($_POST['info_update'])) {
//		if ( wp_verify_nonce($_POST['ga-nonce-key'], 'google-analyticator') ) {
			
			// Update the status
			$ga_status = $_POST[key_ga_status];
			if (($ga_status != ga_enabled) && ($ga_status != ga_disabled))
				$ga_status = ga_status_default;
			update_option(key_ga_status, $ga_status);

			// Update the UID
			$ga_uid = $_POST[key_ga_uid];
			if ($ga_uid == '')
				$ga_uid = ga_uid_default;
			update_option(key_ga_uid, $ga_uid);

			// Update the admin logging
			$ga_admin = $_POST[key_ga_admin];
			if (($ga_admin != ga_enabled) && ($ga_admin != ga_disabled))
				$ga_admin = ga_admin_default;
			update_option(key_ga_admin, $ga_admin);
			
			// Update the admin disable setting
			$ga_admin_disable = $_POST[key_ga_admin_disable];
			if ( $ga_admin_disable == '' )
				$ga_admin_disable = ga_admin_disable_default;
			update_option(key_ga_admin_disable, $ga_admin_disable);
			
			// Update the admin level
			$ga_admin_level = $_POST[key_ga_admin_level];
			if ( $ga_admin_level == '' )
				$ga_admin_level = ga_admin_level_default;
			update_option(key_ga_admin_level, $ga_admin_level);

			// Update the extra tracking code
			$ga_extra = $_POST[key_ga_extra];
			update_option(key_ga_extra, $ga_extra);

			// Update the extra after tracking code
			$ga_extra_after = $_POST[key_ga_extra_after];
			update_option(key_ga_extra_after, $ga_extra_after);
			
			// Update the adsense key
			$ga_adsense = $_POST[key_ga_adsense];
			update_option(key_ga_adsense, $ga_adsense);

			// Update the outbound tracking
			$ga_outbound = $_POST[key_ga_outbound];
			if (($ga_outbound != ga_enabled) && ($ga_outbound != ga_disabled))
				$ga_outbound = ga_outbound_default;
			update_option(key_ga_outbound, $ga_outbound);
			
			// Update the outbound prefix
			$ga_outbound_prefix = $_POST[key_ga_outbound_prefix];
			if ($ga_outbound_prefix == '')
				$ga_outbound_prefix = ga_outbound_prefix_default;
			update_option(key_ga_outbound_prefix, $ga_outbound_prefix);

			// Update the download tracking code
			$ga_downloads = $_POST[key_ga_downloads];
			update_option(key_ga_downloads, $ga_downloads);
			
			// Update the download prefix
			$ga_downloads_prefix = $_POST[key_ga_downloads_prefix];
			if ($ga_downloads_prefix == '')
				$ga_downloads_prefix = ga_downloads_prefix_default;
			update_option(key_ga_downloads_prefix, $ga_downloads_prefix);

			// Update the footer
			$ga_footer = $_POST[key_ga_footer];
			if (($ga_footer != ga_enabled) && ($ga_footer != ga_disabled))
				$ga_footer = ga_footer_default;
			update_option(key_ga_footer, $ga_footer);
			
			// Update the HTTP status
			$ga_specify_http = $_POST[key_ga_specify_http];
			if ( $ga_specify_http == '' )
				$ga_specify_http = 'auto';
			update_option(key_ga_specify_http, $ga_specify_http);
			
			# Update the stat options
			update_option('google_stats_user', $_POST['google_stats_user']);
			update_option('google_stats_password', $_POST['google_stats_password']);

			// Give an updated message
			echo "<div class='updated fade'><p><strong>Google Analyticator settings saved.</strong></p></div>";
//		}
	}

	// Output the options page
	?>

		<div class="wrap">
		<form method="post" action="options-general.php?page=google-analyticator.php">
			<h2>Google Analyticator Settings</h2>
			
			<p><em>
				Google Analyticator is brought to you for free by <a href="http://spiralwebconsulting.com/">Spiral Web Consulting</a>. Spiral Web Consulting is a small web development firm specializing in PHP development. Visit our website to learn more, and don't hesitate to ask us to develop your next big WordPress plugin idea.
			</em></p>
			
			<h3>Basic Settings</h3>
			<?php if (get_option(key_ga_status) == ga_disabled) { ?>
				<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				Google Analytics integration is currently <strong>DISABLED</strong>.
				</div>
			<?php } ?>
			<?php if ((get_option(key_ga_uid) == "XX-XXXXX-X") && (get_option(key_ga_status) != ga_disabled)) { ?>
				<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				Google Analytics integration is currently enabled, but you did not enter a UID. Tracking will not occur.
				</div>
			<?php } ?>
			<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_status ?>">Google Analytics logging is:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_status."' id='".key_ga_status."'>\n";
						
						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_status) == ga_enabled)
							echo " selected='selected'";
						echo ">Enabled</option>\n";
						
						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_status) == ga_disabled)
							echo" selected='selected'";
						echo ">Disabled</option>\n";
						
						echo "</select>\n";
						?>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_uid; ?>">Your Google Analytics' UID:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_ga_uid."' ";
						echo "id='".key_ga_uid."' ";
						echo "value='".get_option(key_ga_uid)."' />\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Enter your Google Analytics' UID in this box. The UID is needed for Google Analytics to log your website stats. Your UID can be found by looking in the JavaScript Google Analytics gives you to put on your page. Look for your UID in between <strong>_uacct = "UA-11111-1";</strong> in the JavaScript. In this example you would put <strong>UA-11111-1</strong> in the UID box.</p>
					</td>
				</tr>
			</table>
			<h3>Advanced Settings</h3>
				<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<?php if ( function_exists('curl_init') ) { ?>
				<tr valign="top">
					<th scope="row">
						<label for="google_stats_user">Google Username:</label>
					</th>
					<td>
						<input type="text" size="40" name="google_stats_user" id="google_stats_user" value="<?php echo stripslashes(get_option('google_stats_user')); ?>" />
						<br /><span class="setting-description">Your Google Analytics account's username. This is needed to authenticate with Google for use with the stats widget.</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="google_stats_password">Google Password:</label>
					</th>
					<td>
						<input type="password" size="40" name="google_stats_password" id="google_stats_password" value="<?php echo stripslashes(get_option('google_stats_password')); ?>" />
						<br /><span class="setting-description">Your Google Analytics account's password. This is needed to authenticate with Google for use with the stats widget.</span>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_admin ?>">WordPress admin logging:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_admin."' id='".key_ga_admin."'>\n";
						
						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_admin) == ga_enabled)
							echo " selected='selected'";
						echo ">Enabled</option>\n";
						
						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_admin) == ga_disabled)
							echo" selected='selected'";
						echo ">Disabled</option>\n";
						
						echo "</select>\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Disabling this option will prevent all logged in WordPress admins from showing up on your Google Analytics reports. A WordPress admin is defined as a user with a level <?php
						echo "<input type='text' size='2' ";
						echo "name='".key_ga_admin_level."' ";
						echo "id='".key_ga_admin_level."' ";
						echo "value='".stripslashes(get_option(key_ga_admin_level))."' />\n";
						?> or higher. Your user level is <?php
						if ( current_user_can('level_10') )
							echo '10';
						elseif ( current_user_can('level_9') )
							echo '9';
						elseif ( current_user_can('level_8') )
							echo '8';
						elseif ( current_user_can('level_7') )
							echo '7';
						elseif ( current_user_can('level_6') )
							echo '6';
						elseif ( current_user_can('level_5') )
							echo '5';
						elseif ( current_user_can('level_4') )
							echo '4';
						elseif ( current_user_can('level_3') )
							echo '3';
						elseif ( current_user_can('level_2') )
							echo '2';
						elseif ( current_user_can('level_1') )
							echo '1';
						else
							echo '0';
						?>.</p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_admin_disable ?>">Admin tracking disable method:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_admin_disable."' id='".key_ga_admin_disable."'>\n";
						
						echo "<option value='remove'";
						if(get_option(key_ga_admin_disable) == 'remove')
							echo " selected='selected'";
						echo ">Remove</option>\n";
						
						echo "<option value='admin'";
						if(get_option(key_ga_admin_disable) == 'admin')
							echo" selected='selected'";
						echo ">Use 'admin' variable</option>\n";
						
						echo "</select>\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Selecting the "Remove" option will physically remove the tracking code from logged in admin users. Selecting the "Use 'admin' variable" option will assign a variable called 'admin' to logged in admin users. This option will allow Google Analytics' site overlay feature to work, but you will have to manually configure Google Analytics to exclude tracking from hits with the 'admin' variable.</p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_footer ?>">Footer tracking code:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_footer."' id='".key_ga_footer."'>\n";
						
						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_footer) == ga_enabled)
							echo " selected='selected'";
						echo ">Enabled</option>\n";
						
						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_footer) == ga_disabled)
							echo" selected='selected'";
						echo ">Disabled</option>\n";
						
						echo "</select>\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Enabling this option will insert the Google Analytics tracking code in your site's footer instead of your header. This will speed up your page loading if turned on. Not all themes support code in the footer, so if you turn this option on, be sure to check the Analytics code is still displayed on your site.</p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_outbound ?>">Outbound link tracking:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_outbound."' id='".key_ga_outbound."'>\n";
						
						echo "<option value='".ga_enabled."'";
						if(get_option(key_ga_outbound) == ga_enabled)
							echo " selected='selected'";
						echo ">Enabled</option>\n";
						
						echo "<option value='".ga_disabled."'";
						if(get_option(key_ga_outbound) == ga_disabled)
							echo" selected='selected'";
						echo ">Disabled</option>\n";
						
						echo "</select>\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Disabling this option will turn off the tracking of outbound links. It's recommended not to disable this option unless you're a privacy advocate (now why would you be using Google Analytics in the first place?) or it's causing some kind of weird issue.</p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_outbound_prefix; ?>">Prefix external links with:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_ga_outbound_prefix."' ";
						echo "id='".key_ga_outbound_prefix."' ";
						echo "value='".stripslashes(get_option(key_ga_outbound_prefix))."' />\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Enter a name for the section tracked external links will appear under.</em></p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_downloads; ?>">Download extensions to track:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_ga_downloads."' ";
						echo "id='".key_ga_downloads."' ";
						echo "value='".stripslashes(get_option(key_ga_downloads))."' />\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Enter any extensions of files you would like to be tracked as a download. For example to track all MP3s and PDFs enter <strong>mp3,pdf</strong>. <em>Outbound link tracking must be enabled for downloads to be tracked.</em></p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_downloads_prefix; ?>">Prefix download links with:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_ga_downloads_prefix."' ";
						echo "id='".key_ga_download_sprefix."' ";
						echo "value='".stripslashes(get_option(key_ga_downloads_prefix))."' />\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Enter a name for the section tracked download links will appear under.</em></p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_adsense; ?>">Google Adsense ID:</label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='50' ";
						echo "name='".key_ga_adsense."' ";
						echo "id='".key_ga_adsense."' ";
						echo "value='".get_option(key_ga_adsense)."' />\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Enter your Google Adsense ID assigned by Google Analytics in this box. This enables Analytics tracking of Adsense information if your Adsense and Analytics accounts are linked. Note: Google recommends the Analytics tracking code is placed in the header with this option enabled, however, a fix is included in this plugin. To follow the official specs, do not enable footer tracking.</p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_extra; ?>">Additional tracking code<br />(before tracker initialization):</label>
					</th>
					<td>
						<?php
						echo "<textarea cols='50' rows='8' ";
						echo "name='".key_ga_extra."' ";
						echo "id='".key_ga_extra."'>";
						echo stripslashes(get_option(key_ga_extra))."</textarea>\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Enter any additional lines of tracking code that you would like to include in the Google Analytics tracking script. The code in this section will be displayed <strong>before</strong> the Google Analytics tracker is initialized. Read <a href="http://www.google.com/analytics/InstallingGATrackingCode.pdf">Google Analytics tracker manual</a> to learn what code goes here and how to use it.</p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_extra_after; ?>">Additional tracking code<br />(after tracker initialization):</label>
					</th>
					<td>
						<?php
						echo "<textarea cols='50' rows='8' ";
						echo "name='".key_ga_extra_after."' ";
						echo "id='".key_ga_extra_after."'>";
						echo stripslashes(get_option(key_ga_extra_after))."</textarea>\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Enter any additional lines of tracking code that you would like to include in the Google Analytics tracking script. The code in this section will be displayed <strong>after</strong> the Google Analytics tracker is initialized. Read <a href="http://www.google.com/analytics/InstallingGATrackingCode.pdf">Google Analytics tracker manual</a> to learn what code goes here and how to use it.</p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_ga_specify_http; ?>">Specify HTTP detection:</label>
					</th>
					<td>
						<?php
						echo "<select name='".key_ga_specify_http."' id='".key_ga_specify_http."'>\n";
						
						echo "<option value='auto'";
						if(get_option(key_ga_specify_http) == 'auto')
							echo " selected='selected'";
						echo ">Auto Detect</option>\n";
						
						echo "<option value='http'";
						if(get_option(key_ga_specify_http) == 'http')
							echo " selected='selected'";
						echo ">HTTP</option>\n";
						
						echo "<option value='https'";
						if(get_option(key_ga_specify_http) == 'https')
							echo " selected='selected'";
						echo ">HTTPS</option>\n";
						
						echo "</select>\n";
						?>
						<p style="margin: 5px 10px;" class="setting-description">Explicitly set the type of HTTP connection your website uses. Setting this option instead of relying on the auto detect may resolve the _gat is undefined error message.</p>
					</td>
				</tr>
				</table>
			<p class="submit">
				<?php if ( function_exists('settings_fields') ) settings_fields('google-analyticator'); ?>
				<input type='submit' name='info_update' value='Save Changes' />
			</p>
		</div>
		</form>

<?php
}

// Add the script
$ga_in_footer = false;
if (get_option(key_ga_footer) == ga_enabled) {
	$ga_in_footer = true;
	add_action('wp_head', 'add_ga_adsense');
	add_action('wp_footer', 'add_google_analytics');
} else {
	add_action('wp_head', 'add_google_analytics');
}

/**
 * Adds the Analytics Adsense tracking code to the header if the main Analytics tracking code is in the footer.
 * Idea and code for Adsense tracking with main code in footer props William Charles Nickerson on May 16, 2009.
 **/
function add_ga_adsense() {
	$uid = stripslashes(get_option(key_ga_uid));
	// If GA is enabled and has a valid key
	if (  (get_option(key_ga_status) != ga_disabled ) && ( $uid != "XX-XXXXX-X" )) {
		// Display page tracking if user is not an admin
		if ( ( get_option(key_ga_admin) == ga_enabled || !current_user_can('level_' . get_option(key_ga_admin_level)) ) && get_option(key_ga_admin_disable) == 'remove' || get_option(key_ga_admin_disable) != 'remove' ) {
			echo "<!-- Google Analytics Tracking by Google Analyticator: http://plugins.spiralwebconsulting.com/analyticator.html -->\n";
			if ( get_option(key_ga_adsense) != '' ) {
				echo '	<script type="text/javascript">window.google_analytics_uacct = "' . get_option(key_ga_adsense) . "\";</script>\n\n";
			}
		}
	}
}

// The guts of the Google Analytics script
function add_google_analytics() {
	global $ga_in_footer;
	
	$uid = stripslashes(get_option(key_ga_uid));
	$extra = stripslashes(get_option(key_ga_extra));
	$extra_after = stripslashes(get_option(key_ga_extra_after));
	$extensions = str_replace (",", "|", get_option(key_ga_downloads));
	
	// If GA is enabled and has a valid key
	if (  (get_option(key_ga_status) != ga_disabled ) && ( $uid != "XX-XXXXX-X" )) {
		
		// Display page tracking if user is not an admin
		if ( ( get_option(key_ga_admin) == ga_enabled || !current_user_can('level_' . get_option(key_ga_admin_level)) ) && get_option(key_ga_admin_disable) == 'remove' || get_option(key_ga_admin_disable) != 'remove' ) {
		
			echo "<!-- Google Analytics Tracking by Google Analyticator: http://plugins.spiralwebconsulting.com/analyticator.html -->\n";
			# Google Adsense data if enabled
			if ( get_option(key_ga_adsense) != '' && !$ga_in_footer )
				echo '	<script type="text/javascript">window.google_analytics_uacct = "' . get_option(key_ga_adsense) . "\";</script>\n\n";
			
			// Pick the HTTP connection
			if ( get_option(key_ga_specify_http) == 'http' ) {
				echo "	<script type=\"text/javascript\" src=\"http://www.google-analytics.com/ga.js\"></script>\n\n";
			} elseif ( get_option(key_ga_specify_http) == 'https' ) {
				echo "	<script type=\"text/javascript\" src=\"https://ssl.google-analytics.com/ga.js\"></script>\n\n";
			} else {
				echo "	<script type=\"text/javascript\">\n";
				echo "		var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n";
				echo "		document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\n";
				echo "	</script>\n\n";
			}
		
			echo "	<script type=\"text/javascript\">\n";
			echo "	try {\n";
			echo "		var pageTracker = _gat._getTracker(\"$uid\");\n";
		
			// Insert extra before tracker code
			if ( '' != $extra )
				echo "		" . $extra . "\n";
		
			// Initialize the tracker
			echo "		pageTracker._initData();\n";
			echo "		pageTracker._trackPageview();\n";
		
			// Disable page tracking if admin is logged in
			if ( ( get_option(key_ga_admin) == ga_disabled ) && ( current_user_can('level_' . get_option(key_ga_admin_level)) ) )
				echo "		pageTracker._setVar('admin');\n";
		
			// Insert extra after tracker code
			if ( '' != $extra_after )
				echo "		" . $extra_after . "\n";
		
			echo "	} catch(err) {}</script>\n";
		
			// Include the file types to track
			$extensions = explode(',', stripslashes(get_option(key_ga_downloads)));
			$ext = "";
			foreach ( $extensions AS $extension )
				$ext .= "'$extension',";
			$ext = substr($ext, 0, -1);
		
			// Include the link tracking prefixes
			$outbound_prefix = stripslashes(get_option(key_ga_outbound_prefix));
			$downloads_prefix = stripslashes(get_option(key_ga_downloads_prefix));
		
			?>
			<script type="text/javascript">
				var fileTypes = [<?php echo $ext; ?>];
				var outboundPrefix = '/<?php echo $outbound_prefix; ?>/';
				var downloadsPrefix = '/<?php echo $downloads_prefix; ?>/';
			</script>
			<?php
		}
	}
}

/**
 * Adds outbound link tracking to Google Analyticator
 *
 * @author Ronald Heft
 **/
function ga_outgoing_links()
{
	// If GA is enabled and has a valid key
	if (  (get_option(key_ga_status) != ga_disabled ) && ( $uid != "XX-XXXXX-X" )) {
		// If outbound tracking is enabled
		if ( get_option(key_ga_outbound) == ga_enabled ) {
			// If this is not an admin page
			if ( !is_admin() ) {
				// Display page tracking if user is not an admin
				if ( ( get_option(key_ga_admin) == ga_enabled || !current_user_can('level_' . get_option(key_ga_admin_level)) ) && get_option(key_ga_admin_disable) == 'remove' || get_option(key_ga_admin_disable) != 'remove' ) {
					add_action('wp_print_scripts', 'ga_external_tracking_js');
				}
			}
		}
	}
}

/**
 * Adds the scripts required for outbound link tracking
 *
 * @author Ronald Heft
 **/
function ga_external_tracking_js()
{
	wp_enqueue_script('jquery');
	wp_enqueue_script('ga-external-tracking', plugins_url('/google-analyticator/external-tracking.js'));
}

?>