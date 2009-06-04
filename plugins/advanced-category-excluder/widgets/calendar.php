<?php
/*
Plugin Name: Advanced Category Excluder Widgets
Version: 1.3
Plugin URI: http://advanced-category-excluder.dev.rain.hu
Description: This plugin adds some basic widgetsm that support category exclusion
Author: DjZoNe
Author URI: http://djz.hu/
*/

/** 
  * The display code is from includes/widgets.php from version 2.6
  */


class AceCalendarWidget 
{
  /**
   * Default values
   */
  
  var $title = '';

  // static init callback
  function init() 
  {
    // Check for the required plugin functions. This will prevent fatal
    // errors occurring when you deactivate the dynamic-sidebar plugin.
    if ( !function_exists('register_sidebar_widget') )
      return;

    $widget = new AceCalendarWidget();

    // This registers our widget so it appears with the other available
    // widgets and can be dragged and dropped into any active sidebars.
    register_sidebar_widget('ACE Calendar', array($widget,'display'));

    // This registers our optional widget control form.
    register_widget_control('ACE Calendar', array($widget,'control'), 280, 300);
  }

  function control() {
    // Get our options and see if we're handling a form submission.
    $options = get_option('ace_widget_calendar');
      
    if ( !is_array($options) )
      $options = array('title'=>''
	  );
    
    
    if ( !empty($_POST['ace-calendar-submit']) ) 
    {
    
			$options['title'] = trim(strip_tags(stripslashes($_POST['ace-calendar-title'])));

			update_option('ace_widget_calendar', $options);
     }    

		$title = attribute_escape( $options['title'] );

?>
			<p>
				<label for="calendar-title">
					<?php _e( 'Title:' ); ?>
					<input class="widefat" id="calendar-title" name="ace-calendar-title" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>

			<input type="hidden" name="ace-calendar-submit" value="1" />
<?php
  }

  function display($args) 
  {
    global $wpdb, $wp_query, $ace_targets;
    // $args is an array of strings that help widgets to conform to
    // the active theme: before_widget, before_title, after_widget,
    // and after_title are the array keys. Default tags: li and h2.
	
	extract($args);
	$options = get_option('ace_widget_calendar');
	$title = apply_filters('widget_title', $options['title']);
	if ( empty($title) )
		$title = '&nbsp;';

	$cats_to_exclude = '';

	if (is_array($ace_targets))
	{
		foreach ($ace_targets as $key=>$val) 
		{
      if (!empty($wp_query->$key) && $wp_query->$key == 1) $filter = $key;          	
		}
		$cats_to_exclude = get_option("ace_categories_".$filter);
	}

	echo $before_widget . $before_title . $title . $after_title;
	echo '<div id="calendar_wrap">';
	$this->get_ace_calendar();
	echo '</div>';
	echo $after_widget;


    }
	
	
	
	
	
	
	
	function get_ace_calendar($initial = true) {
		global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

		$key = md5( $m . $monthnum . $year );
		if ( $cache = wp_cache_get( 'get_ace_calendar', 'ace_calendar' ) ) {
			if ( isset( $cache[ $key ] ) ) {
				echo $cache[ $key ];
				return;
			}
		}

		ob_start();
		// Quick check. If we have no posts at all, abort!
		if ( !$posts ) {
			$gotsome = $wpdb->get_var("SELECT ID from $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 1");
			if ( !$gotsome )
				return;
		}

		if ( isset($_GET['w']) )
			$w = ''.intval($_GET['w']);

		// week_begins = 0 stands for Sunday
		$week_begins = intval(get_option('start_of_week'));

		// Let's figure out when we are
		if ( !empty($monthnum) && !empty($year) ) {
			$thismonth = ''.zeroise(intval($monthnum), 2);
			$thisyear = ''.intval($year);
		} elseif ( !empty($w) ) {
			// We need to get the month from MySQL
			$thisyear = ''.intval(substr($m, 0, 4));
			$d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
			$thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('${thisyear}0101', INTERVAL $d DAY) ), '%m')");
		} elseif ( !empty($m) ) {
			$thisyear = ''.intval(substr($m, 0, 4));
			if ( strlen($m) < 6 )
					$thismonth = '01';
			else
					$thismonth = ''.zeroise(intval(substr($m, 4, 2)), 2);
		} else {
			$thisyear = gmdate('Y', current_time('timestamp'));
			$thismonth = gmdate('m', current_time('timestamp'));
		}

		$unixmonth = mktime(0, 0 , 0, $thismonth, 1, $thisyear);

		// Get the next and previous month and year with at least one post
		$previous = $wpdb->get_row("SELECT DISTINCT MONTH(post_date) AS month, YEAR(post_date) AS year
			FROM $wpdb->posts
			WHERE ". apply_filters("posts_where", "
				post_date < '$thisyear-$thismonth-01'
			AND	post_type = 'post' AND post_status = 'publish'
			") ."
				ORDER BY post_date DESC
				LIMIT 1");
		$next = $wpdb->get_row("SELECT	DISTINCT MONTH(post_date) AS month, YEAR(post_date) AS year
			FROM $wpdb->posts
			WHERE ". apply_filters("posts_where", "
				post_date >	'$thisyear-$thismonth-01'
			AND	MONTH( post_date ) != MONTH( '$thisyear-$thismonth-01' )
			AND	post_type = 'post' AND post_status = 'publish'
			") ."
				ORDER	BY post_date ASC
				LIMIT 1");
		
		echo '<table id="wp-calendar" summary="' . __('Calendar') . '">
		<caption>' . sprintf(_c('%1$s %2$s|Used as a calendar caption'), $wp_locale->get_month($thismonth), date('Y', $unixmonth)) . '</caption>
		<thead>
		<tr>';

		$myweek = array();

		for ( $wdcount=0; $wdcount<=6; $wdcount++ ) {
			$myweek[] = $wp_locale->get_weekday(($wdcount+$week_begins)%7);
		}

		foreach ( $myweek as $wd ) {
			$day_name = (true == $initial) ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
			echo "\n\t\t<th abbr=\"$wd\" scope=\"col\" title=\"$wd\">$day_name</th>";
		}

		echo '
		</tr>
		</thead>

		<tfoot>
		<tr>';

		if ( $previous ) {
			echo "\n\t\t".'<td abbr="' . $wp_locale->get_month($previous->month) . '" colspan="3" id="prev"><a href="' .
			get_month_link($previous->year, $previous->month) . '" title="' . sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($previous->month),
				date('Y', mktime(0, 0 , 0, $previous->month, 1, $previous->year))) . '">&laquo; ' . $wp_locale->get_month_abbrev($wp_locale->get_month($previous->month)) . '</a></td>';
		} else {
			echo "\n\t\t".'<td colspan="3" id="prev" class="pad">&nbsp;</td>';
		}

		echo "\n\t\t".'<td class="pad">&nbsp;</td>';

		if ( $next ) {
			echo "\n\t\t".'<td abbr="' . $wp_locale->get_month($next->month) . '" colspan="3" id="next"><a href="' .
			get_month_link($next->year, $next->month) . '" title="' . sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($next->month),
				date('Y', mktime(0, 0 , 0, $next->month, 1, $next->year))) . '">' . $wp_locale->get_month_abbrev($wp_locale->get_month($next->month)) . ' &raquo;</a></td>';
		} else {
			echo "\n\t\t".'<td colspan="3" id="next" class="pad">&nbsp;</td>';
		}

		echo '
		</tr>
		</tfoot>

		<tbody>
		<tr>';

		// Get days with posts
		$dayswithposts = $wpdb->get_results("SELECT DISTINCT DAYOFMONTH(post_date)
			FROM $wpdb->posts WHERE ". apply_filters("posts_where", "
			MONTH(post_date) = '$thismonth'
			AND YEAR(post_date) = '$thisyear'
			AND post_type = 'post' AND post_status = 'publish'
			AND post_date < '" . current_time('mysql') . '\''), ARRAY_N
			
		);
		if ( $dayswithposts ) {
			foreach ( $dayswithposts as $daywith ) {
				$daywithpost[] = $daywith[0];
			}
		} else {
			$daywithpost = array();
		}

		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'camino') !== false || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'safari') !== false)
			$ak_title_separator = "\n";
		else
			$ak_title_separator = ', ';

		$ak_titles_for_day = array();
		$ak_post_titles = $wpdb->get_results("SELECT post_title, DAYOFMONTH(post_date) as dom 
			FROM $wpdb->posts WHERE ". apply_filters("posts_where", "
				YEAR(post_date) = '$thisyear' 
			AND MONTH(post_date) = '$thismonth' 
			AND post_date < '".current_time('mysql')."' 
			AND post_type = 'post' AND post_status = 'publish'
			")
		);
		if ( $ak_post_titles ) {
			foreach ( $ak_post_titles as $ak_post_title ) {

					$post_title = apply_filters( "the_title", $ak_post_title->post_title );
					$post_title = str_replace('"', '&quot;', wptexturize( $post_title ));

					if ( empty($ak_titles_for_day['day_'.$ak_post_title->dom]) )
						$ak_titles_for_day['day_'.$ak_post_title->dom] = '';
					if ( empty($ak_titles_for_day["$ak_post_title->dom"]) ) // first one
						$ak_titles_for_day["$ak_post_title->dom"] = $post_title;
					else
						$ak_titles_for_day["$ak_post_title->dom"] .= $ak_title_separator . $post_title;
			}
		}


		// See how much we should pad in the beginning
		$pad = calendar_week_mod(date('w', $unixmonth)-$week_begins);
		if ( 0 != $pad )
			echo "\n\t\t".'<td colspan="'.$pad.'" class="pad">&nbsp;</td>';

		$daysinmonth = intval(date('t', $unixmonth));
		for ( $day = 1; $day <= $daysinmonth; ++$day ) {
			if ( isset($newrow) && $newrow )
				echo "\n\t</tr>\n\t<tr>\n\t\t";
			$newrow = false;

			if ( $day == gmdate('j', (time() + (get_option('gmt_offset') * 3600))) && $thismonth == gmdate('m', time()+(get_option('gmt_offset') * 3600)) && $thisyear == gmdate('Y', time()+(get_option('gmt_offset') * 3600)) )
				echo '<td id="today">';
			else
				echo '<td>';

			if ( in_array($day, $daywithpost) ) // any posts today?
					echo '<a href="' . get_day_link($thisyear, $thismonth, $day) . "\" title=\"$ak_titles_for_day[$day]\">$day</a>";
			else
				echo $day;
			echo '</td>';

			if ( 6 == calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins) )
				$newrow = true;
		}

		$pad = 7 - calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins);
		if ( $pad != 0 && $pad != 7 )
			echo "\n\t\t".'<td class="pad" colspan="'.$pad.'">&nbsp;</td>';

		echo "\n\t</tr>\n\t</tbody>\n\t</table>";

		$output = ob_get_contents();
		ob_end_clean();
		echo $output;
		$cache[ $key ] = $output;
		wp_cache_set( 'get_ace_calendar', $cache, 'ace_calendar' );
	}
}

add_action('widgets_init', array('AceCalendarWidget','init'));

?>