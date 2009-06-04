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


class AceCategoriesWidget 
{
  /**
   * Default values
   */
  
  var $title = '';
  var $count = '0';
  var $hierarchical = '0';
  var $dropdown = '0';  

  // static init callback
  function init() 
  {
    // Check for the required plugin functions. This will prevent fatal
    // errors occurring when you deactivate the dynamic-sidebar plugin.
    if ( !function_exists('register_sidebar_widget') )
      return;

    $widget = new AceCategoriesWidget();

    // This registers our widget so it appears with the other available
    // widgets and can be dragged and dropped into any active sidebars.
    register_sidebar_widget('ACE Categories', array($widget,'display'));

    // This registers our optional widget control form.
    register_widget_control('ACE Categories', array($widget,'control'), 280, 300);
  }

  function control() {
    // Get our options and see if we're handling a form submission.
    $options = get_option('ace_widget_categories');
      
    if ( !is_array($options) )
      $options = array('title'=>'',
		       'count' => $this->count,
		       'hierarchical' => $this->hierarchical,
		       'dropdown' => $this->dropdown );
    
    
    if ( !empty($_POST['ace-categories-submit']) ) 
    {
    
			$options['title'] = trim(strip_tags(stripslashes($_POST['ace-categories-title'])));
			$options['count'] = isset($_POST['ace-categories-count']);
			$options['hierarchical'] = isset($_POST['ace-categories-hierarchical']);
			$options['dropdown'] = isset($_POST['ace-categories-dropdown']);

		  update_option('ace_widget_categories', $options);
     }    

		$title = attribute_escape( $options['title'] );
		$count = (bool) $options['count'];
		$hierarchical = (bool) $options['hierarchical'];
		$dropdown = (bool) $options['dropdown'];

?>
			<p>
				<label for="categories-title">
					<?php _e( 'Title:' ); ?>
					<input class="widefat" id="categories-title" name="ace-categories-title" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>

			<p>
				<label for="categories-dropdown">
					<input type="checkbox" class="checkbox" id="categories-dropdown" name="ace-categories-dropdown" <?php checked( $dropdown, true ); ?> />
					<?php _e( 'Show as dropdown' ); ?>
				</label>
				<br />
				<label for="categories-count">
					<input type="checkbox" class="checkbox" id="categories-count" name="ace-categories-count" <?php checked( $count, true ); ?> />
					<?php _e( 'Show post counts' ); ?>
				</label>
				<br />
				<label for="categories-hierarchical">
					<input type="checkbox" class="checkbox" id="categories-hierarchical" name="ace-categories-hierarchical" <?php checked( $hierarchical, true ); ?> />
					<?php _e( 'Show hierarchy' ); ?>
				</label>
			</p>

			<input type="hidden" name="ace-categories-submit" value="1" />
<?php
  }

  function display($args) 
  {
    global $wpdb, $wp_query, $ace_targets;
    // $args is an array of strings that help widgets to conform to
    // the active theme: before_widget, before_title, after_widget,
    // and after_title are the array keys. Default tags: li and h2.
    extract($args);

    $options = get_option('ace_widget_categories');
      
    if ( !is_array($options) )
      $options = array('title'=>'',
		       'count' => $this->count,
		       'hierarchical' => $this->hierarchical,
		       'dropdown' => $this->dropdown );

	$c = $options['count'] ? '1' : '0';
	$h = $options['hierarchical'] ? '1' : '0';
	$d = $options['dropdown'] ? '1' : '0';
  $cats_to_exclude = '';


	$title = empty($options['title']) ? __('Categories') : apply_filters('widget_title', $options['title']);

	echo $before_widget;
	echo $before_title . $title . $after_title;

/* Here comes ACE patch ;) */

  if (is_array($ace_targets) && get_option("ace_settings_hide"))
  {
  	foreach ($ace_targets as $key=>$val) 
  	{
      if (!empty($wp_query->$key) && $wp_query->$key == 1) $filter = $key;  	   
       /**
        * XXX: Still have to fix here
        */                   	
  	}
  
    $cats_to_exclude = get_option("ace_categories_".$filter);
  }

  $cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h, 'exclude' => $cats_to_exclude);

	if ( $d ) 
  {
		$cat_args['show_option_none'] = __('Select Category');
		wp_dropdown_categories($cat_args);
?>

<script type='text/javascript'>
/* <![CDATA[ */
    var dropdown = document.getElementById("cat");
    function onCatChange() 
    {
  		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) 
      {
  			location.href = "<?php echo get_option('home'); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
  		}
    }
    dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
	}
  else 
  {
?>
		<ul>
		<?php 
			$cat_args['title_li'] = '';
			wp_list_categories($cat_args); 
		?>
		</ul>
<?php
	     }
	     echo $after_widget;  
    }
}

add_action('widgets_init', array('AceCategoriesWidget','init'));

?>