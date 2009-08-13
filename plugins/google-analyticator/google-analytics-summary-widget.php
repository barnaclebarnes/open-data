<?php

/**
 * Creates a summary widget for Google Analytics stats
 *
 * @author Spiral Web Consulting
 **/
class GoogleAnalyticsSummary
{
	var $api = false;
	var $id = false;
	
	/**
	 * Start the process of including the widget
	 **/
	function GoogleAnalyticsSummary()
	{
		add_action('wp_dashboard_setup', array($this, 'addDashboardWidget'));
		add_action('admin_print_scripts-index.php', array($this, 'addJavascript'));
		add_action('admin_head-index.php', array($this, 'addTopJs'));
		add_action('wp_ajax_ga_stats_widget', array($this, 'ajaxWidget'));
	}
	
	/**
	 * Add the widget to the dashboard
	 **/
	function addDashboardWidget()
	{
		# Check if the user is an admin
		if ( current_user_can('level_' . get_option(key_ga_admin_level)) ) {
			wp_add_dashboard_widget('google-analytics-summary', __('Google Analytics Summary', 'google-analyticator'), array($this, 'widget'));
		}
	}
	
	/**
	 * Add the scripts to the admin
	 **/
	function addJavascript()
	{
		# Include the Sparklines graphing library
		wp_enqueue_script('jquery-sparklines', plugins_url('/google-analyticator/jquery.sparkline.min.js'), array('jquery'), '1.4.2');
	}
	
	/**
	 * Add the Javascript to the top
	 **/
	function addTopJs()
	{
		?>
		<script type="text/javascript">
		
			jQuery(document).ready(function(){
				
				// Grab the widget data
				jQuery.ajax({
					type: 'post',
					url: 'admin-ajax.php',
					data: {
						action: 'ga_stats_widget',
						_ajax_nonce: '<?php echo wp_create_nonce("ga_stats_widget"); ?>'
					},
					success: function(html) {
						// Hide the loading message
						jQuery('#google-analytics-summary .inside').hide();
						
						// Place the widget data in the area
						jQuery('#google-analytics-summary .inside').html(html);
						
						// Display the widget data
						jQuery('#google-analytics-summary .inside').slideDown();
						
						// Handle displaying the graph
						if ( navigator.appName != 'Microsoft Internet Explorer' ) {
							jQuery('.ga_visits').sparkline(ga_visits, { type:'line', width:'100%', height:'75px', lineColor:'#aaa', fillColor:'#f0f0f0', spotColor:false, minSpotColor:false, maxSpotColor:false, chartRangeMin:0 });
						} else {
							// Hide the widget graph
							jQuery('.ga_visits_title').remove();
							jQuery('.ga_visits').remove();
						}
					}
				});
			
			});
		
		</script>
		<?php
	}
	
	/**
	 * The widget display
	 **/
	function widget()
	{
		echo '<small>' . __('Loading') . '...</small>';
	}
	
	/**
	 * AJAX data for the widget
	 **/
	function ajaxWidget()
	{
		# Check the ajax widget
		check_ajax_referer('ga_stats_widget');
		
		# Attempt to login and get the current account
		$this->id = $this->getAnalyticsAccount();
		$this->api->setAccount($this->id);
		
		# Check that we can display the widget before continuing
		if ( $this->id == false ) {
			# Output error message
			echo '<p style="margin: 0;">' . __('No Analytics account selected. Double check you are authenticated with Google on Google Analyticator\'s settings page and make sure an account is selected.', 'google-analyticator') . '</p>';

			# Add Javascript variable to prevent breaking the Javascript
			echo '<script type="text/javascript">var ga_visits = [];</script>';

			die();
		}
		
		# Add the header information for the visits graph
		echo '<p class="ga_visits_title" style="font-style: italic; font-family: Georgia, \'Times New Roman\', \'Bitstream Charter\', Times, serif; margin-top: 0px; color: #777; font-size: 13px;">' . __('Visits Over the Past 30 Days', 'google-analyticator') . '</p>';
		
		# Add the sparkline for the past 30 days
		$this->getVisitsGraph();
		
		# Add the summary header
		echo '<p style="font-style: italic; font-family: Georgia, \'Times New Roman\', \'Bitstream Charter\', Times, serif; color: #777; font-size: 13px;">' . __('Site Usage', 'google-analyticator') . '</p>';
		
		# Add the visitor summary
		$this->getSiteUsage();
		
		# Add the top 5 posts header
		echo '<p style="font-style: italic; font-family: Georgia, \'Times New Roman\', \'Bitstream Charter\', Times, serif; color: #777; font-size: 13px;">' . __('Top Pages', 'google-analyticator') . '</p>';
		
		# Add the top 5 posts
		$this->getTopPages();
		
		# Add the tab information
		echo '<table width="100%"><tr><td width="50%" valign="top">';
		
		# Add the top 5 referrers header
		echo '<p style="font-style: italic; font-family: Georgia, \'Times New Roman\', \'Bitstream Charter\', Times, serif; color: #777; font-size: 13px;">' . __('Top Referrers', 'google-analyticator') . '</p>';
		
		# Add the referrer information
		$this->getTopReferrers();
		
		# Add the second column
		echo '</td><td valign="top">';
		
		# Add the top 5 search header
		echo '<p style="font-style: italic; font-family: Georgia, \'Times New Roman\', \'Bitstream Charter\', Times, serif; color: #777; font-size: 13px;">' . __('Top Searches', 'google-analyticator') . '</p>';
		
		# Add the search information
		$this->getTopSearches();
		
		# Close the table
		echo '</td></tr></table>';
		
		die();
	}
	
	/**
	 * Get the current analytics account
	 *
	 * @return the analytics account
	 **/
	function getAnalyticsAccount()
	{
		$accounts = array();

		# Get the class for interacting with the Google Analytics
		require_once('class.analytics.stats.php');

		# Create a new Gdata call
		$this->api = new GoogleAnalyticsStats();

		# Check if Google sucessfully logged in
		if ( ! $this->api->checkLogin() )
			return false;

		# Get a list of accounts
		$accounts = $this->api->getAnalyticsAccounts();
		
		# Check if we actually have accounts
		if ( !is_array($accounts) )
			return false;
		
		# Check if we have a list of accounts
		if ( count($accounts) <= 0 )
			return false;

		# Loop throught the account and return the current account
		foreach ( $accounts AS $account ) {
			
			# Check if the UID matches the selected UID
			if ( $account['ga:webPropertyId'] == get_option('ga_uid') )
				return $account['id'];
			
		}
		
		return false;
	}
	
	/**
	 * Get the visitors graph
	 **/
	function getVisitsGraph()
	{
		# Get the value from the database
		$cached = unserialize(get_option('google_stats_visitsGraph_' . $this->id));
		$updated = false;

		# Check if the call has been made before
		if ( is_array($cached) ) {

			# Check if the last time called was within two hours, if so, mark to not retrieve and grab the stats array
			if ( $cached['lastcalled'] > ( time() - 7200 ) ) {
				$updated = true;
				$stats = $cached['stats'];
			}
		
		}
		
		# If the stats need to be updated
		if ( ! $updated ) {
		
			# Get the metrics needed to build the visits graph
			$before = date('Y-m-d', strtotime('-31 days'));
			$yesterday = date('Y-m-d', strtotime('-1 day'));
			$stats = $this->api->getMetrics('ga:visits', $before, $yesterday, 'ga:date', 'ga:date');
			
			# Store the serialized stats in the database
			$newStats = serialize(array('stats'=>$stats, 'lastcalled'=>time()));
			update_option('google_stats_visitsGraph_' . $this->id, $newStats);
		
		}
		
		# Create a list of the data points for graphing
		$data = '';
		$max = 0;
		
		# Check the size of the stats array
		if ( !isset($stats) || !is_array($stats) || count($stats) <= 0 ) {
			$data = '0,0';
		} else {
			foreach ( $stats AS $stat ) {
				# Verify the number is numeric
				if ( is_numeric($stat['ga:visits']) )
					$data .= $stat['ga:visits'] . ',';
				else
					$data .= '0,';
			
				# Update the max value if greater
				if ( $max < $stat['ga:visits'] )
					$max = $stat['ga:visits'];
			}
			
			# Shorten the string to remove the last comma
			$data = substr($data, 0, -1);
		}
		
		# Add a fake stat if need be
		if ( !isset($stat['ga:visits']) )
			$stat['ga:visits'] = 0;
		
		# Output the graph
		echo '<script type="text/javascript">var ga_visits = [' . $data . '];</script>';
		echo '<span class="ga_visits" title="' . sprintf(__('The most visits on a single day was %d. Yesterday there were %d visits.', 'google-analyticator'), $max, $stat['ga:visits']) . '"></span>';
	}
	
	/**
	 * Get the site usage
	 **/
	function getSiteUsage()
	{
		# Get the value from the database
		$cached = unserialize(get_option('google_stats_siteUsage_' . $this->id));
		$updated = false;

		# Check if the call has been made before
		if ( is_array($cached) ) {

			# Check if the last time called was within two hours, if so, mark to not retrieve and grab the stats array
			if ( $cached['lastcalled'] > ( time() - 7200 ) ) {
				$updated = true;
				$stats = $cached['stats'];
			}
		
		}
		
		# If the stats need to be updated
		if ( ! $updated ) {
		
			# Get the metrics needed to build the usage table
			$before = date('Y-m-d', strtotime('-31 days'));
			$yesterday = date('Y-m-d', strtotime('-1 day'));
			$stats = $this->api->getMetrics('ga:visits,ga:bounces,ga:entrances,ga:pageviews,ga:timeOnSite,ga:newVisits', $before, $yesterday);
			
			# Store the serialized stats in the database
			$newStats = serialize(array('stats'=>$stats, 'lastcalled'=>time()));
			update_option('google_stats_siteUsage_' . $this->id, $newStats);
		
		}
		
		# Create the site usage table
		?>
		<table width="100%">
			<tr>
				<td width=""><strong><?php echo number_format($stats[0]['ga:visits']); ?></strong></td>
				<td width=""><?php _e('Visits', 'google-analyticator'); ?></td>
				<?php if ( $stats[0]['ga:entrances'] <= 0 ) { ?>
					<td width="20%"><strong>0.00%</strong></td>
				<?php } else { ?>
					<td width="20%"><strong><?php echo number_format(round(($stats[0]['ga:bounces']/$stats[0]['ga:entrances'])*100, 2), 2); ?>%</strong></td>
				<?php } ?>
				<td width="30%"><?php _e('Bounce Rate', 'google-analyticator'); ?></td>
			</tr>
			<tr>
				<td><strong><?php echo number_format($stats[0]['ga:pageviews']); ?></strong></td>
				<td><?php _e('Pageviews', 'google-analyticator'); ?></td>
				<?php if ( $stats[0]['ga:visits'] <= 0 ) { ?>
					<td><strong>00:00:00</strong></td>
				<?php } else { ?>
					<td><strong><?php echo $this->sec2Time($stats[0]['ga:timeOnSite']/$stats[0]['ga:visits']); ?></strong></td>
				<?php } ?>
				<td><?php _e('Avg. Time on Site', 'google-analyticator'); ?></td>
			</tr>
			<tr>
				<?php if ( $stats[0]['ga:visits'] <= 0 ) { ?>
					<td><strong>0.00</strong></td>
				<?php } else { ?>
					<td><strong><?php echo number_format(round($stats[0]['ga:pageviews']/$stats[0]['ga:visits'], 2), 2); ?></strong></td>
				<?php } ?>
				<td><?php _e('Pages/Visit', 'google-analyticator'); ?></td>
				<?php if ( $stats[0]['ga:visits'] <= 0 ) { ?>
					<td><strong>0.00%</strong></td>
				<?php } else { ?>
					<td><strong><?php echo number_format(round(($stats[0]['ga:newVisits']/$stats[0]['ga:visits'])*100, 2), 2); ?>%</strong></td>
				<?php } ?>
				<td><?php _e('% New Visits', 'google-analyticator'); ?></td>
			</tr>
		</table>
		<?php
	}
	
	/**
	 * Get the top pages
	 **/
	function getTopPages()
	{
		# Get the value from the database
		$cached = unserialize(get_option('google_stats_topPages_' . $this->id));
		$updated = false;

		# Check if the call has been made before
		if ( is_array($cached) ) {

			# Check if the last time called was within two hours, if so, mark to not retrieve and grab the stats array
			if ( $cached['lastcalled'] > ( time() - 7200 ) ) {
				$updated = true;
				$stats = $cached['stats'];
			}
		
		}
		
		# If the stats need to be updated
		if ( ! $updated ) {
		
			# Get the metrics needed to build the top pages
			$before = date('Y-m-d', strtotime('-31 days'));
			$yesterday = date('Y-m-d', strtotime('-1 day'));
			$stats = $this->api->getMetrics('ga:pageviews', $before, $yesterday, 'ga:pageTitle,ga:pagePath', '-ga:pageviews', 'ga:pagePath!%3D%2F', '5');
			
			# Store the serialized stats in the database
			$newStats = serialize(array('stats'=>$stats, 'lastcalled'=>time()));
			update_option('google_stats_topPages_' . $this->id, $newStats);
		
		}
		
		# Check the size of the stats array
		if ( count($stats) <= 0 || !is_array($stats) ) {
			echo '<p>' . __('There is no data for view.', 'google-analyticator') . '</p>';
		} else {
			# Build the top pages list
			echo '<ol>';
			
			# Loop through each stat
			foreach ( $stats AS $stat ) {
				echo '<li><a href="' . esc_html($stat['ga:pagePath']) . '">' . $stat['ga:pageTitle'] . '</a> - ' . number_format($stat['ga:pageviews']) . ' ' . __('Views', 'google-analyticator') . '</li>';
			}
			
			# Finish the list
			echo '</ol>';
		}
	}
	
	/**
	 * Get the top referrers
	 **/
	function getTopReferrers()
	{
		# Get the value from the database
		$cached = unserialize(get_option('google_stats_topReferrers_' . $this->id));
		$updated = false;

		# Check if the call has been made before
		if ( is_array($cached) ) {

			# Check if the last time called was within two hours, if so, mark to not retrieve and grab the stats array
			if ( $cached['lastcalled'] > ( time() - 7200 ) ) {
				$updated = true;
				$stats = $cached['stats'];
			}
		
		}
		
		# If the stats need to be updated
		if ( ! $updated ) {
		
			# Get the metrics needed to build the top referrers
			$before = date('Y-m-d', strtotime('-31 days'));
			$yesterday = date('Y-m-d', strtotime('-1 day'));
			$stats = $this->api->getMetrics('ga:visits', $before, $yesterday, 'ga:source,ga:medium', '-ga:visits', 'ga:medium%3D%3Dreferral', '5');
			
			# Store the serialized stats in the database
			$newStats = serialize(array('stats'=>$stats, 'lastcalled'=>time()));
			update_option('google_stats_topReferrers_' . $this->id, $newStats);
		
		}
		
		# Check the size of the stats array
		if ( count($stats) <= 0 || !is_array($stats) ) {
			echo '<p>' . __('There is no data for view.', 'google-analyticator') . '</p>';
		} else {
			# Build the top pages list
			echo '<ol>';
		
			# Loop through each stat
			foreach ( $stats AS $stat ) {
				echo '<li><strong>' . esc_html($stat['ga:source']) . '</strong> - ' . number_format($stat['ga:visits']) . ' ' . __('Visits', 'google-analyticator') . '</li>';
			}
		
			# Finish the list
			echo '</ol>';
		}
	}
	
	/**
	 * Get the top searches
	 **/
	function getTopSearches()
	{
		# Get the value from the database
		$cached = unserialize(get_option('google_stats_topSearches_' . $this->id));
		$updated = false;

		# Check if the call has been made before
		if ( is_array($cached) ) {

			# Check if the last time called was within two hours, if so, mark to not retrieve and grab the stats array
			if ( $cached['lastcalled'] > ( time() - 7200 ) ) {
				$updated = true;
				$stats = $cached['stats'];
			}
		
		}
		
		# If the stats need to be updated
		if ( ! $updated ) {
		
			# Get the metrics needed to build the top searches
			$before = date('Y-m-d', strtotime('-31 days'));
			$yesterday = date('Y-m-d', strtotime('-1 day'));
			$stats = $this->api->getMetrics('ga:visits', $before, $yesterday, 'ga:keyword', '-ga:visits', 'ga:keyword!%3D(not%20set)', '5');
			
			# Store the serialized stats in the database
			$newStats = serialize(array('stats'=>$stats, 'lastcalled'=>time()));
			update_option('google_stats_topSearches_' . $this->id, $newStats);
		
		}
		
		# Check the size of the stats array
		if ( count($stats) <= 0 || !is_array($stats) ) {
			echo '<p>' . __('There is no data for view.', 'google-analyticator') . '</p>';
		} else {
			# Build the top pages list
			echo '<ol>';
		
			# Loop through each stat
			foreach ( $stats AS $stat ) {
				echo '<li><strong>' . esc_html($stat['ga:keyword']) . '</strong> - ' . number_format($stat['ga:visits']) . ' ' . __('Visits', 'google-analyticator') . '</li>';
			}
		
			# Finish the list
			echo '</ol>';
		}
	}
	
	/**
	 * Convert second to a time format
	 **/
	function sec2Time($time)
	{
		# Check if numeric
		if ( is_numeric($time) ) {
			$value = array(
				"years" => '00',
				"days" => '00',
				"hours" => '00',
				"minutes" => '00',
				"seconds" => '00'
	    	);
			if ( $time >= 31556926 ) {
				$value["years"] = floor($time/31556926);
				$time = ($time%31556926);
			}
			if ( $time >= 86400 ) {
				$value["days"] = floor($time/86400);
				$time = ($time%86400);
			}
			if ( $time >= 3600 ) {
				$value["hours"] = str_pad(floor($time/3600), 2, 0, STR_PAD_LEFT);
				$time = ($time%3600);
			}
			if ( $time >= 60 ) {
				$value["minutes"] = str_pad(floor($time/60), 2, 0, STR_PAD_LEFT);
				$time = ($time%60);
			}
			$value["seconds"] = str_pad(floor($time), 2, 0, STR_PAD_LEFT);
		
			# Get the hour:minute:second version
			return $value['hours'] . ':' . $value['minutes'] . ':' . $value['seconds'];
		} else {
			return false;
		}
	}
	
} // END class 

?>