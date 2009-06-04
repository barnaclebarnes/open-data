<?php

/**
 * The Google Analytics Stats Widget
 *
 * @author Spiral Web Consulting
 **/
class GoogleStatsWidget
{
	
	/**
	 * Start the Google Stats Widget
	 **/
	function GoogleStatsWidget()
	{
		add_action('widgets_init', array($this, 'init'));
	}
	
	/**
	 * Register the widget with WordPress
	 **/
	function init()
	{
		register_sidebar_widget('Google Analytics Stats', array($this, 'statsWidget'));
		register_widget_control('Google Analytics Stats', array($this, 'widgetControl'), 400, 400);
	}
	
	/**
	 * The widget output code
	 **/
	function statsWidget($args)
	{
		extract($args);
		
		# Get the options
		$options = get_option('widget_google_stats');
		
		# Before the widget
		echo $before_widget;
		
		# The title
		echo $before_title . $options['title'] . $after_title;
		
		# Make the stats chicklet
		$this->initiateBackground($options['pageBg'], $options['font']);
		$this->beginWidget($options['font'], $options['widgetBg']);
		$this->widgetInfo($this->getUniqueVisitors($options['account'], $options['timeFrame']), $options['line1'], $options['line2'], $options['innerBg'], $options['font']);
		$this->endWidget();
		
		# After the widget
		echo $after_widget;
	}
	
	/**
	 * A custom function for outputting the widget
	 *
	 * @param account - Google Analytics Profile ID found on your profile settings page. 
	 *                  (For more info check out http://plugins.spiralwebconsulting.com/forums/viewtopic.php?f=5&t=128)
	 * @param timeFrame - The number of previous day's traffic stats used. (Example: 1 = Yesterday, 2 = Last Two Days, etc...)
	 * @param line1 - The upper line of text displayed by the widget
	 * @param line2 - The lower line of text displayed by the widget
	 * @param pageBg - The background color of the entire page in hexadecimal form without the #
	 * @param widgetBg - The background color of the widget in hexadecimal form without the #
	 * @param innerBg - The background color of the inner part of the widget in hexadecimal form without the #
	 * @param font - The font color in hexadecimal form without the #
	 **/
	function outputWidget($account, $timeFrame, $line1, $line2, $pageBg, $widgetBg, $innerBg, $font)
	{
		$this->initiateBackground($pageBg, $font);
		$this->beginWidget($font, $widgetBg);
		$this->widgetInfo($this->getUniqueVisitors('ga:' . $account, $timeFrame), $line1, $line2, $innerBg, $font);
		$this->endWidget();
	}
	
	/**
	 * The settings for the stats widget
	 **/
	function widgetControl()
	{
		# Get the widget options
		$options = get_option('widget_google_stats');
		if ( !is_array($options) ) {
			$options = array('title'=>'', 'account'=>'', 'timeFrame'=>'1', 'pageBg'=>'fff', 'widgetBg'=>'999', 'innerBg'=>'fff', 'font'=>'333', 'line1'=>'Unique', 'line2'=>'Visitors');
		}
		
		# Save the options
		if ( $_POST['google-stats-submit'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['google_stats_title']));
			$options['account'] = strip_tags(stripslashes($_POST['google_stats_account']));
			$options['timeFrame'] = strip_tags(stripslashes($_POST['google_stats_timeFrame']));
			$options['pageBg'] = strip_tags(stripslashes($_POST['google_stats_pageBg']));
			$options['widgetBg'] = strip_tags(stripslashes($_POST['google_stats_widgetBg']));
			$options['innerBg'] = strip_tags(stripslashes($_POST['google_stats_innerBg']));
			$options['font'] = strip_tags(stripslashes($_POST['google_stats_font']));
			$options['line1'] = strip_tags(stripslashes($_POST['google_stats_line1']));
			$options['line2'] = strip_tags(stripslashes($_POST['google_stats_line2']));
			update_option('widget_google_stats', $options);
		}
		
		# Sanitize widget options
		$title = htmlspecialchars($options['title']);
		$acnt = htmlspecialchars($options['account']);
		$timeFrame = htmlspecialchars($options['timeFrame']);
		$pageBg = htmlspecialchars($options['pageBg']);
		$widgetBg = htmlspecialchars($options['widgetBg']);
		$innerBg = htmlspecialchars($options['innerBg']);
		$font = htmlspecialchars($options['font']);
		$line1 = htmlspecialchars($options['line1']);
		$line2 = htmlspecialchars($options['line2']);
		
		$accounts = array();
		
		# Check if a username has been set
		if ( get_option('google_stats_user') != '' ) {
		
			# Get the class for interacting with the Google Analytics
			require_once('class.analytics.stats.php');
		
			# Create a new Gdata call
			$stats = new GoogleAnalyticsStats(stripslashes(get_option('google_stats_user')), stripslashes(get_option('google_stats_password')), true);
		
			# Get a list of accounts
			$accounts = $stats->getAnalyticsAccounts();
			
		}
		
		# Output the options
		echo '<p style="text-align:right;"><label for="google_stats_title">' . __('Title:') . ' <input style="width: 250px;" id="google_stats_title" name="google_stats_title" type="text" value="' . $title . '" /></label></p>';
		# The list of accounts
		echo '<p style="text-align:right;"><label for="google_stats_account">' . __('Analytics account: ');
		echo '<select name="google_stats_account" style="margin-top: -3px; margin-bottom: 10px;">';
		if ( count($accounts) > 0 )
			foreach ( $accounts AS $account ) { $select = ( $acnt == $account['id'] ) ? ' selected="selected"' : ''; echo '<option value="' . $account['id'] . '"' . $select . '>' . $account['title'] . '</option>'; }
		else
			echo '<option value="">No accounts. Set user/pass in settings.</option>';
		echo '</select>';
		# Time frame
		echo '<p style="text-align:right;"><label for="google_stats_timeFrame">' . __('Days of data to get:') . ' <input style="width: 150px;" id="google_stats_timeFrame" name="google_stats_timeFrame" type="text" value="' . $timeFrame . '" /></label></p>';		
		# Page background
		echo '<p style="text-align:right;"><label for="google_stats_pageBg">' . __('Page background:') . ' <input style="width: 150px;" id="google_stats_pageBg" name="google_stats_pageBg" type="text" value="' . $pageBg . '" /></label></p>';
		# Widget background
		echo '<p style="text-align:right;"><label for="google_stats_widgetBg">' . __('Widget background:') . ' <input style="width: 150px;" id="google_stats_widgetBg" name="google_stats_widgetBg" type="text" value="' . $widgetBg . '" /></label></p>';
		# Inner background
		echo '<p style="text-align:right;"><label for="google_stats_innerBg">' . __('Inner background:') . ' <input style="width: 150px;" id="google_stats_innerBg" name="google_stats_innerBg" type="text" value="' . $innerBg . '" /></label></p>';
		# Font color
		echo '<p style="text-align:right;"><label for="google_stats_font">' . __('Font color:') . ' <input style="width: 150px;" id="google_stats_font" name="google_stats_font" type="text" value="' . $font . '" /></label></p>';
		# Text line 1
		echo '<p style="text-align:right;"><label for="google_stats_line1">' . __('Line 1 text:') . ' <input style="width: 200px;" id="google_stats_line1" name="google_stats_line1" type="text" value="' . $line1 . '" /></label></p>';
		# Text line 2
		echo '<p style="text-align:right;"><label for="google_stats_line2">' . __('Line 2 text:') . ' <input style="width: 200px;" id="google_stats_line2" name="google_stats_line2" type="text" value="' . $line2 . '" /></label></p>';
		# Mark the form as updated
		echo '<input type="hidden" id="google-stats-submit" name="google-stats-submit" value="1" />';
	}

	/**
	 * This function is used to display the background color behind the widget. This is necessary
	 * for the Google Analytics text to have the same background color as the page.
	 *
	 * @param $font_color - Hexadecimal value for the font color used within the Widget (does not effect "Powered By Google Analytics Text"). This effects border color as well.
	 * @param $page_background_color - Hexadecimal value for the page background color
	 * @return void
	 **/
	function initiateBackground($page_background_color = 'FFF', $font_color = '000')
	{
		echo '<br />';
		echo '<div style="background:#' . $page_background_color . ';font-size:12px;color:#' . $font_color . ';font-family:"Lucida Grande",Helvetica,Verdana,Sans-Serif;">';
	}
	
	/**
	 * This function starts the widget. The font color and widget background color are customizable.
	 *
	 * @param $font_color - Hexadecimal value for the font color used within the Widget (does not effect "Powered By Google Analytics Text"). This effects border color as well.
	 * @param $widget_background_color - Hexadecimal value for the widget background color.
	 * @return void
	 **/
	function beginWidget($font_color = '000', $widget_background_color = 'FFF')
	{
		echo '<table style="width:auto!important;border-width:2px;border-color:#' . $font_color . ';border-style:solid;background:#' . $widget_background_color . ';"><tr">';
	}
	
	/**
	 * This function encases the text that appears on the right hand side of the widget.
	 * Both lines of text are customizable by each individual user.
	 *
	 * It also displays the visitor count that was pulled from the user's Google Analytics account.
	 *
	 * @param $visitor_count - Number of unique visits to the site pulled from the user's Google Analytics account.
	 * @param $line_one - First line of text displayed on the right hand side of the widget.
	 * @param $line_two - Second line of text displayed on the right hand side of the widget.
	 * @param $inner_background_color - Hexadecimal value for the background color that surrounds the Visitor Count.
	 * @param $font_color - Hexadecimal value for the font color used within the Widget (does not effect "Powered By Google Analytics Text"). This effects border color as well
	 * @return void
	 **/
	function widgetInfo($visitor_count, $line_one = 'Unique', $line_two = 'Visitors', $inner_background_color = 'FFF', $font_color = '000')
	{
		
		echo '<td style="width:auto!important;border-width:1px;border-color:#' . $font_color . ';border-style:solid;padding:0px 5px 0px 5px;text-align:right;background:#' . $inner_background_color . ';min-width:80px;*width:80px!important;"><div style="min-width:80px;">'. $visitor_count . '</div></td>';
		
		echo '<td style="width:auto!important;padding:0px 5px 0px 5px;text-align:center;font-size:11px;">' . $line_one . '<br />' . $line_two . '</td>';
		
	}
	
	/**
	 * The function is used strictly for visual appearance. It also displays the Google Analytics text.
	 *
	 * @return void
	 **/
	function endWidget()
	{
		// This closes off the widget.
		echo '</tr></table>';
		
		// The following is used to displayed the "Powered By Google Anayltics" text.
		echo '<div style="font-size:9px;color:#666666;margin-top:0px;font-family:Verdana;">Powered By <a href="http://google.com/analytics/" alt="Google Analytics" style="text-decoration:none;"><img src="' . plugins_url('/google-analyticator/ga_logo.png') . '" alt="Google Analytics" style="border:0px;position:relative;top:4px;" /></a></div></div>';
	}
	
	/**
	 * Grabs the cached value of the unique visits for the previous day
	 *
	 * @param account - the account to get the unique visitors from
	 * @param time - the amount of days to get
	 * @return void
	 **/
	function getUniqueVisitors($account, $time = 1)
	{
		# Get the value from the database
		$visits = unserialize(get_option('google_stats_visits_' . $account));
		
		# Check to make sure the timeframe is an int and greater than one
		$time = (int) $time;
		if ( $time < 1 )
			$time = 1;
		
		# Check if the call has been made before
		if ( is_array($visits) ) {
			
			# Check if the last time called was within two hours, if so, return that
			if ( $visits['lastcalled'] > ( time() - 7200 ) )
				return $visits['unique'];
			
		}
		
		# If here, the call has not been made or it is expired
		
		# Get the class for interacting with the Google Analytics
		require_once('class.analytics.stats.php');
		
		# Create a new Gdata call
		$stats = new GoogleAnalyticsStats(stripslashes(get_option('google_stats_user')), stripslashes(get_option('google_stats_password')), true);
		
		# Set the account to the one requested
		$stats->setAccount($account);
		
		# Get the latest stats
		$before = date('Y-m-d', strtotime('-' . $time . ' days'));
		$yesterday = date('Y-m-d', strtotime('-1 day'));
		$uniques = number_format($stats->getMetric('ga:visitors', $before, $yesterday));
		
		# Make the array for database storage
		$visit = serialize(array('unique'=>$uniques, 'lastcalled'=>time()));
		
		# Store the array
		update_option('google_stats_visits_' . $account, $visit);
		
		# Return the visits
		return $uniques;
	}

} // END class

?>