<?php
/*
Plugin Name: Advanced Category Excluder Widgets
Version: 1.3.1.1
Plugin URI: http://advanced-category-excluder.dev.rain.hu
Description: This plugin adds some basic widgetsm that support category exclusion
Author: DjZoNe
Author URI: http://djz.hu/
*/

/** 
  * The display code is from includes/widgets.php from version 2.6
  */


class AceRecentPostsWidget 
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

    $widget = new AceRecentPostsWidget();

    // This registers our widget so it appears with the other available
    // widgets and can be dragged and dropped into any active sidebars.
    register_sidebar_widget('ACE Recent Posts', array($widget,'display'));

    // This registers our optional widget control form.
    register_widget_control('ACE Recent Posts', array($widget,'control'), 280, 300);
  }

  function control() {
    // Get our options and see if we're handling a form submission.
    $options = get_option('ace_widget_recent_posts');
      
    if ( !is_array($options) )
      $options = array('title'=>'',
		       'count' => $this->count,
		       'hierarchical' => $this->hierarchical,
		       'dropdown' => $this->dropdown );
    
    
    if ( !empty($_POST['ace-recent-posts-submit']) ) 
    {
    
			$options['title'] = trim(strip_tags(stripslashes($_POST['ace-recent-posts-title'])));
			$options['number'] = (int) $_POST['ace-recent-posts-number'];
			
			if ($options['number'] > 15) $options['number'] = 15;//The limit

		  update_option('ace_widget_recent_posts', $options);
     }    

		$title = attribute_escape( $options['title'] );
		$number = (int) $options['number'];


?>
			<p>
				<label for="recent-posts-title">
					<?php _e( 'Title:' ); ?>
					<input class="widefat" id="recent-posts-title" name="ace-recent-posts-title" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>

			<p>
				<label for="recent-posts-number"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="recent-posts-number" name="ace-recent-posts-number" type="text" value="<?php echo $number; ?>" />
				</label>
				<br />
				<small><?php _e('(at most 15)'); ?></small>
			</p>

			<input type="hidden" name="ace-recent-posts-submit" value="1" />
<?php
  }

  function display($args) 
  {
    global $wpdb, $wp_query, $ace_targets;
    // $args is an array of strings that help widgets to conform to
    // the active theme: before_widget, before_title, after_widget,
    // and after_title are the array keys. Default tags: li and h2.
    extract($args);

    $options = get_option('ace_widget_recent_posts');
      
    if ( !is_array($options) )
      $options = array('title'=>'',
		       'number' => $this->number );

  $cats_to_exclude = '';


	$title = empty($options['title']) ? __('Recent Posts') : apply_filters('widget_title', $options['title']);
	$number = $options['number'];

/* Here comes ACE patch ;) */

    switch(get_option('ace_settings_exclude_method'))
    {
      case "smart":
      
        $cats_to_exclude = get_option("ace_categories_is_home");  

        if ($wp_query->is_single)
        {
          $cats = split(',',$cats_to_exclude);
          
          /**
           * If this is a single post, and the 
           */
          $c = count($cats);
          for($i=0;$i<$c;$i++)
          {
            /**
             * If the post is in one category that has been selected for exclusion 
             */             
            if(in_category($cats[$i])) 
            {
              unset($cats[$i]);
            }
          }
          $cats_to_exclude = join(",",(array) $cats);
        }
        elseif ($wp_query->is_category)
        {
          $cats = split(',',$cats_to_exclude);
          
          $c = count($cats);
          for($i=0;$i<$c;$i++)
          {
          /**
           * If this category is beeing listed 
           */          
            if($cats[$i] == $wp_query->query_vars['cat']) 
            {
              unset($cats[$i]);
            }
          }
          $cats_to_exclude = join(",",(array) $cats);
          unset($cats);
        }
        else
        {
          /**
           * The same as in normal mode. Keep in sync
           */                     
        	foreach ($ace_targets as $key=>$val) 
        	{
        	   if ($wp_query->$key == 1) $filter = $key;    	
        	}
        	
        	/**
        	 * If this is empty is_home exclusion is in affect
        	 */                   	
        	if (!empty($filter) && $filter != "")
          { 
            $cats_to_exclude = get_option("ace_categories_".$filter);
          }
        	
        }        
        
      break;
      
      case "front":
        $cats_to_exclude = get_option("ace_categories_is_home");      
      break; 

      case "none":
        $cats_to_exclude = "";
      break;
      
      default:
      case "normal":
      
      	foreach ($ace_targets as $key=>$val) 
      	{
      	   if ($wp_query->$key == 1) $filter = $key;    	
      	} 
        $cats_to_exclude = get_option("ace_categories_".$filter);
              
      break;
  	}
  
  /**
   * If we got categories to exclude, we want negative values of them
   * because WP_Query requires negative values, in a comma separeted list
   * to the 'cat' value.      
   */     
  if (!empty($cats_to_exclude))
  {
    $cats = array();
    foreach(explode(',',$cats_to_exclude) as $category )
    {
      $cats[]=0-$category;
    }
    
    /**
     * Yes, we overwrite here.
     */         
    $cats_to_exclude = implode(",",$cats);
  }

    /**
     * Suppress filters is IMPORTANT
     * 
     * This widget now is better than the original. 
     * Not joking ;)          
     */         
    $r = new WP_Query(array('showposts' => $number, 'what_to_show' => 'posts', 'nopaging' => 0, 'post_status' => 'publish','cat'=>$cats_to_exclude,'suppress_filters'=>1));
  	
  	if ($r->have_posts()) :
  ?>
  		<?php echo $before_widget; ?>
  			<?php echo $before_title . $title . $after_title; ?>
  			<ul>
  			<?php  while ($r->have_posts()) : $r->the_post(); ?>
  			<li><a href="<?php the_permalink() ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?> </a></li>
  			<?php endwhile; ?>
  			</ul>
  		<?php echo $after_widget; ?>
  <?php
  		wp_reset_query();  // Restore global post data stomped by the_post().
  	endif;
    }
}

add_action('widgets_init', array('AceRecentPostsWidget','init'));

?>