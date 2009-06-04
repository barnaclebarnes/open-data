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


class AceRecentCommentsWidget 
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

    $widget = new AceRecentCommentsWidget();

    // This registers our widget so it appears with the other available
    // widgets and can be dragged and dropped into any active sidebars.
    register_sidebar_widget('ACE Recent Comments', array($widget,'display'));

    // This registers our optional widget control form.
    register_widget_control('ACE Recent Comments', array($widget,'control'), 280, 300);
  }

  function control() {
    // Get our options and see if we're handling a form submission.
    $options = get_option('ace_widget_recent_comments');
      
    if ( !is_array($options) )
      $options = array('title'=>'',
		       'count' => $this->count,
		       'hierarchical' => $this->hierarchical,
		       'dropdown' => $this->dropdown );
    
    
    if ( !empty($_POST['ace-recent-comments-submit']) ) 
    {
    
			$options['title'] = trim(strip_tags(stripslashes($_POST['ace-recent-comments-title'])));
			$options['number'] = (int) $_POST['ace-recent-comments-number'];
			
			if ($options['number'] > 15) $options['number'] = 15;//The limit

		  update_option('ace_widget_recent_comments', $options);
     }    

		$title = attribute_escape( $options['title'] );
		$number = (int) $options['number'];


?>
			<p>
				<label for="recent-comments-title">
					<?php _e( 'Title:' ); ?>
					<input class="widefat" id="recent-comments-title" name="ace-recent-comments-title" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>

			<p>
				<label for="recent-comments-number"><?php _e('Number of comments to show:'); ?> <input style="width: 25px; text-align: center;" id="recent-comments-number" name="ace-recent-comments-number" type="text" value="<?php echo $number; ?>" />
				</label>
				<br />
				<small><?php _e('(at most 15)'); ?></small>
			</p>

			<input type="hidden" name="ace-recent-comments-submit" value="1" />
<?php
  }

  function display($args) 
  {
    global $wpdb, $wp_query, $ace_targets, $comment;
    // $args is an array of strings that help widgets to conform to
    // the active theme: before_widget, before_title, after_widget,
    // and after_title are the array keys. Default tags: li and h2.
    extract($args);

    $options = get_option('ace_widget_recent_comments');
      
    if ( !is_array($options) )
      $options = array('title'=>'',
		       'number' => $this->number );

    $cats_to_exclude = '';


	$title = empty($options['title']) ? __('Recent Comments') : apply_filters('widget_title', $options['title']);
	$number = $options['number'];

/* Here comes ACE patch ;) */

  $targets = "";

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
  
	if ( !$number = (int) $options['number'] )
		$number = 5;
	else if ( $number < 1 )
		$number = 1;
	else if ( $number > 15 )
		$number = 15;

/* ACE hack... :( */

  if (!empty($cats_to_exclude))
  {

    $CAT_FILTER = "";

    /* Before WP 2.3 */
    if (empty($wpdb->term_taxonomy))
    {
        foreach(explode(',',$cats_to_exclude) as $category )
        {
          if (strlen($CAT_FILTER)>1) $CAT_FILTER = ' AND '; 
          $CAT_FILTER.="AND category_id = '$category'";
        }    
    
        $POST_QUERY = "SELECT post_id FROM $wpdb->post2cat WHERE $CAT_FILTER";
    }
    else
    {
        foreach(explode(',',$cats_to_exclude) as $category )
        {
          if (!empty($CAT_FILTER)) $CAT_FILTER .= ' OR ';
          else $CAT_FILTER = ' WHERE ';
          $CAT_FILTER.= " $wpdb->term_taxonomy.term_id = '$category'";
        }    
    
        $POST_QUERY = "SELECT $wpdb->term_relationships.object_id as post_id FROM $wpdb->term_relationships LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id) $CAT_FILTER";
    
    }

    $posts = $wpdb->get_results($POST_QUERY);
  
    $POST_FILTER = "";
    /**
     * Get a list of posts, that we don't want to list the related comments of
     */              
    foreach( $posts as $post )
    {
      if (!empty($POST_FILTER)) $POST_FILTER.= " AND ";
      
      $POST_FILTER.=" comment_post_ID != '".$post->post_id."'";
    }
    
    if (!empty($POST_FILTER)) $POST_FILTER = " AND ( ".$POST_FILTER.") ";
    
  }

if ( !$comments = wp_cache_get( 'ace_recent_comments_'.$cats_to_exclude, 'widget' ) )
{ 
    $COMMENT_QUERY="SELECT comment_author, comment_author_url, comment_ID, comment_post_ID FROM $wpdb->comments  WHERE comment_approved = '1' $POST_FILTER ORDER BY comment_date_gmt DESC LIMIT $number";

		$comments = $wpdb->get_results($COMMENT_QUERY);
    wp_cache_add( 'ace_recent_comments'.$cats_to_exclude, $comments, 'widget' ); 
}

?>

		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul id="recentcomments"><?php
			if ( $comments ) : foreach ($comments as $comment) :
			echo  '<li class="recentcomments">' . sprintf(__('%1$s on %2$s'), get_comment_author_link(), '<a href="'. get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>';
			endforeach; endif;?></ul>
		<?php echo $after_widget; ?>
<?php
  }
}

add_action('widgets_init', array('AceRecentCommentsWidget','init'));

?>