<?php
/*
Plugin Name: Advanced Category Excluder
Version: 1.4.3
Plugin URI: http://ace.dev.rain.hu
Description: This plugin helps you to manage your content, RSS feeds, sidebar widgets, and fine tune where you want to display your posts, pages, links, link categories, or hide.
Author: DjZoNe
Author URI: http://djz.hu/
*/

global $ace_targets, $ace_settings, $ace_version;

$ace_version = '1.4.3';

if ($_GET['page'] == 'ace_page_main')
{
  ace_page_main();
}

function ace_admin_head() 
{
  global $ace_version;
  
  $_ace_version = get_option('ace_version');
  
 if ($_ace_version != $ace_version)
 {
    add_action('admin_notices','ace_admin_notices');
    update_option('ace_version',$ace_version);    
 }
 remove_filter('posts_join', 'ace_join');
 remove_filter('posts_where', 'ace_where');
 remove_filter('posts_distinct', 'ace_distinct');
 remove_filter('get_bookmarks', 'ace_get_bookmarks');
 remove_filter('getarchives_where','ace_getarchives_where');
 //remove_filter('getarchives_join','ace_getarchives_join');
 remove_filter('get_terms','ace_get_terms');
}

function ace_admin_notices($msg)
{
  global $ace_version;
  
  $url = 'http://ace.dev.rain.hu';   
  $msg = sprintf(__('It seems <strong>ACE plugin</strong> is just upgraded to the latest version %1$s. Please <strong>review the changes</strong> at our homepage <a href="%2$s" target="_blank">%2$s</a>','ace'),$ace_version,$url);

  echo "<div id='update-nag'>$msg</div>";  
}
function ace_getarchives_where($where,$r="")
{
	return ace_where($where,'is_archive');
}

function ace_getarchives_join($join,$r="")
{
//	return ace_join($where,'is_archive');
}

function ace_get_bookmarks($bookmarks)
{
	$filter = ace_get_section();
  
  $links_to_exclude = explode(",",get_option("ace_link_sections_".$filter));
	$linkcategories_to_exclude = explode(",",get_option("ace_linkcategory_sections_".$filter));
		
	//print_r($linkcategories_to_exclude);
	//print_r($bookmarks);
	
	$c = count($bookmarks);
	for($i=0; $i<$c; $i++)
	{
		if (in_array($bookmarks[$i]->link_id,$links_to_exclude) || in_array($bookmarks[$i]->term_id,$linkcategories_to_exclude))
		{
			unset($bookmarks[$i]);
		}
	}
	
	
	//print_r($bookmarks);
	//print_r($links_to_exclude);
	
	return $bookmarks;
}

function ace_list_pages_excludes($excludes)
{
	$filter = ace_get_section();
    $posts_to_exclude = get_option("ace_page_sections_".$filter);
	
	return explode(",",$posts_to_exclude);
}

function ace_oldstylefilter($cats_to_exclude, $field)
/* 
 * This function is to generate old style category / taxonomy filter for
 * MySQL 4.0 database server family
 */
{
    $cats = explode(",",$cats_to_exclude);

    $out = "";
	 
    for($i = 0; $i < count($cats); $i++)
    {
    	$out .= "$field != '".$cats[$i]."'";
    	if ($i+1 != count($cats)) $out .= " AND ";
    }
    
    return $out;
}

function ace_where(&$where,$filter="") 
{
    global $wpdb, $wp_query;

    if((!empty($wp_query->is_category) || !empty($wp_query->is_tag) || get_option("ace_settings_onlyinwidget"))) return $where;
    /* If we are in a category archive, tag archive, or only in widgets don't apply filters */
   
    if (empty($filter))
    {
    	$filter = ace_get_section();
    }    
    
    $cats_to_exclude = get_option("ace_categories_".$filter);
	    
    if ( !empty($filter) && !empty($cats_to_exclude) && strlen($cats_to_exclude) > 0)
    {
		$sql_version = substr(mysql_get_server_info(),0,3);
		
    	if (empty($wpdb->term_taxonomy)) 
	    // wordpress version < 2.3
	    {
	       if ($sql_version == "4.0")
	       // old mysql without subquery
	       {
		        $where .= " AND ( ".ace_oldstylefilter($cats_to_exclude, "$wpdb->post2cat.category_id")." ) ";
	       }
	       else
	       // at least MySQL 4.1
	       {
		      $where .= " AND $wpdb->post2cat.category_id NOT IN (" . $cats_to_exclude . ")";
	       }
	     }
    	 else
       {
  	    if ($sql_version == "4.0")
  	    {
  		    $where .= " AND ".$wpdb->term_taxonomy.".taxonomy = 'category' AND ";	    
  		    $where .= "( ".ace_oldstylefilter($cats_to_exclude, "$wpdb->term_taxonomy.term_id")." )";
  	    }
  	    else
  	    {
      		$where .= " AND NOT EXISTS (";
      		$where .= "SELECT * FROM ".$wpdb->term_relationships." JOIN ".$wpdb->term_taxonomy." ON ".$wpdb->term_taxonomy.".term_taxonomy_id = ".$wpdb->term_relationships.".term_taxonomy_id ";
      		$where .= "WHERE ".$wpdb->term_relationships.".object_id = ".$wpdb->posts.".ID AND ".$wpdb->term_taxonomy.".taxonomy = 'category' AND ".$wpdb->term_taxonomy.".term_id IN (" . $cats_to_exclude . ") )";
  	    }
      }
    }
	
    return $where;   
}

function ace_join(&$join,$filter="") 
{
    global $wpdb, $wp_query;

    if(!empty($wp_query->is_category) || !empty($wp_query->is_tag) || get_option("ace_settings_onlyinwidget")) return $join;
    /* If we are in a category archive, tag archive, or only in widgets don't apply filters */

	if (empty($filter))
	{	
		$filter = ace_get_section();
	}

	$cats_to_exclude = get_option("ace_categories_".$filter);	
	
    if ( !empty($filter) && strlen($cats_to_exclude) > 0) 
    {
    	if (empty($wpdb->term_relationships))
    	{
    	    if (!preg_match("/post2cat/i",$join)) $join .= " LEFT JOIN $wpdb->post2cat ON $wpdb->post2cat.post_id = $wpdb->posts.ID";
    	}
    	else
    	{
   	      if (!preg_match("/$wpdb->term_relationships/i",$join)) $join .=" LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) ";
    	    if (!preg_match("/$wpdb->term_taxonomy/i",$join)) $join .=" LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id";
    	}
    }
    
    return $join;
}

function ace_distinct($distinct) 
{
   global  $wp_query;
   
   // this won't hurt by default.
   
   return "distinct";
}

function ace_list_categories(&$args)
/* 
 * Manipulating category list 
 */
{

  if (get_option("ace_settings_hide") == 1 && get_option("ace_categories_is_home") != "")
  /* Only if the user wants this function */
  { 
    $cats = explode(",",get_option("ace_categories_is_home"));
       
    if (count($cats) > 0)
    /* if there is any category to hide :) */
    {
      $args = str_replace('</h2><ul>','</h2><ul>'.chr(10),$args);
      /* Insert a line break after the heading. */
    
      $rows = explode("\n",$args);    
    
      $p = "";
      
      for ($i=0; $i <= count($cats); $i++)
      /* 
        Yes, we are now creating a regular expression for ereg.
      */
      {
        if ($cats[$i] != "")
        {
          $catData = get_category($cats[$i]);
          /**
           * Here we get the name of the category, because that's the only thing we can exclude by
           *  in early 2.x versions of WordPress           
           */
          $p .= $catData->cat_name;
          if ($i+1 < count($cats)) $p .= "|";
          /**
           * If we'll get more object to exclude we add a PREG pattern OR, which is a pipe '|'
           */                     
        }
        $pattern = "(".$p.")";
      }

      if (!empty($pattern))
      {
        for($j = 0; $j <= count($rows); $j++ )
        {
          if(preg_match("/\b".$pattern."\b/i",$rows[$j]))
          /* We have the <li> starting tag on the first line, and the ending
          on de second line, so we kill'em all :) */ 
          {
              unset($rows[$j]);
              unset($rows[$j+1]);
          }
        }
        $args = implode("\n",$rows);
      }
    }
  }
  return($args);
}

/**
 * ACE Dashboard page functions
 **/

function ace_adminmenu()
{
    if ( function_exists('add_menu_page') ) 
    {
      add_menu_page(__('ACE', 'ace'), __('ACE', 'ace'), 'manage_options', 'advanced-category-excluder/main.php');
    }
    
  	if (function_exists('add_submenu_page')) 
    {
			add_submenu_page('advanced-category-excluder/main.php', __('Settings', 'ace'), __('Settings', 'ace'), 'manage_options', 'advanced-category-excluder/main.php');
			add_submenu_page('advanced-category-excluder/main.php', __('Categories', 'ace'), __('Categories', 'ace'), 'manage_options', 'advanced-category-excluder/pages/categories.php');
			add_submenu_page('advanced-category-excluder/main.php', __('Pages', 'ace'), __('Pages', 'ace'), 'manage_options', 'advanced-category-excluder/pages/pages.php');		  
			add_submenu_page('advanced-category-excluder/main.php', __('Links', 'ace'), __('Links', 'ace'), 'manage_options', 'advanced-category-excluder/pages/links.php');
			add_submenu_page('advanced-category-excluder/main.php', __('Link categories', 'ace'), __('Link categories', 'ace'), 'manage_options', 'advanced-category-excluder/pages/link_categories.php');			
			add_submenu_page('advanced-category-excluder/main.php', __('Tags', 'ace'), __('Tags', 'ace'), 'manage_options', 'advanced-category-excluder/pages/tags.php');
			add_submenu_page('advanced-category-excluder/main.php', __('Plugins homepage', 'ace'), __('Plugin homepage', 'ace'), 'manage_options', 'advanced-category-excluder/pages/home.php');
    }
}

function ace_page_main()
{
    global $wpdb, $ace_targets, $ace_settings;
    
    $ace_subpage = 1;
	  if (!empty($_GET['subpage'])) $ace_subpage = intval($_GET['subpage']); 
}

function ace_init()
{
  global $ace_targets, $ace_settings, $ace_methods;

	if (function_exists('load_plugin_textdomain')) 
  {
		if ( !defined('WP_PLUGIN_DIR') ) 
    {
			load_plugin_textdomain('ace','wp-content/plugins/advaced-category-excluder/lang');
		}
    else 
    {
			load_plugin_textdomain('ace', false, dirname(plugin_basename(__FILE__)) . '/lang');
		}
	}
	
	$ace_targets = array('is_archive'=>__('Archive','ace'),'is_home'=>__('Home','ace'),'is_feed'=>__('RSS Posts','ace'),'is_comment_feed'=>__('RSS Comments','ace'),'is_search'=>__('Search','ace'), 'is_page'=>__('Pages', ace), 'is_single'=>__('Single Posts', ace),'norobots'=>__('Disable robots','ace'));
  
  $ace_settings = array( 
      'hide'=>__('Do you want the categories selected for <strong>Home</strong> section, to be hidden from <strong>category list</strong> as well?','ace'),
      'onlyinwidget'=>__('Do you want the posts related to the categories selected for <strong>Home</strong> section to be <strong>only</strong> excluded from the <strong>sidebar widget</strong>s? (Recent Posts, Recent Commants, Calendar)','ace'),      
      'showempty'=>__('Do you want the category lister, to list the empty categories?','ace'),
      'ec3'=>sprintf(__('Do you want to display Event Calendar default category in the <a href="%s">Categories</a> tab?','ace'), $_SERVER['PHP_SELF']."?page=ace_page_main&amp;subpage=2"),
      'exclude_method'=>__('What <strong>exclusion method</strong> do you want to use in recent comments / recent posts <strong>widgets</strong>?','ace'),
      'xsg_category'=>__('Select a section to export excluded categories into <strong>XML Sitemap Generator</strong>:','ace')
      );
      
  $ace_methods = array(
    'smart' => __('This means, what widgets shows on the front shows everywhere on the site, exept when listing an excluded category, reading a post that is in an excluded category, or meet with another rule (archive, search). In that case the related comments/posts from that category will be shown as well. This method is introduced in ACE 1.3.','ace'),
    'front' => __('This means, that the widgets displays what they would on the front. No exeption.','ace'),
    'normal' => __('This means widget always using the actualy exclusion rules, depends on what part of the page you are browsing. It could be different on the front, in the search and in the archive','ace'),
    'none' => __('No exclusion in widgets','ace')
  );
}

/**
 * This function exports your settings to XML Sitemap Generator plugin
 */ 
function ace_xsg_update($categories="",$section="")
{
  $active = get_option("active_plugins");
  if (!in_array("google-sitemap-generator/sitemap.php",$active)) return;
          
  if (empty($section))
  {
    $section = get_option("ace_settings_xsg_category");  
  }

  if (empty($categories) && !empty($section))
  {
    $categories = get_option("ace_categories_".$section);
  }
  
  if (!is_array($categories))
  {
    $categories = explode(",",$categories);
  }

  $sm_options = get_option("sm_options");
  $sm_options["sm_b_exclude_cats"]=$categories;
  update_option("sm_options",$sm_options);
}

function ace_head() 
{
  global $ace_targets, $wp_query;
  $modifyheader = false;

  /**
   * Only if we are a single post request
   */     
  if ($wp_query->is_single)
  {
    $cats = split(',',get_option("ace_categories_norobots"));
      
    foreach ($cats as $cat)
    {
      /**
       * If the post is in one category that has been selected for exclusion
       * we'll hide it from robots.       
       */             
      if(in_category($cat)) $modifyheader = true;
    }
    
    if($modifyheader)
    {
      echo '<meta name="robots" content="noindex, nofollow">'."\n";
      echo '<!-- A.C.E. by DjZoNe -->';
      return true;
    }
    return false;
  }
  return false;
  
}

function ace_install()
{
  global $wpdb, $ace_targets, $ace_settings;

  foreach ($ace_targets as $key=>$v)
  {
    add_option("ace_categories_".$key,'','',true);
  }
  
  foreach ($ace_settings as $key=>$v)
  {
    switch($key)
    {
      case 'showempty':
        $val = 1;
      break;
      
      case 'exclude_method':
        $val = 'smart';
      break;
      
      default:
        $val = 0;
      break;
    }
    
    add_option("ace_settings_".$key,$val,'',true);
  }
}

function ace_uninstall()
{
  global $ace_version;

  $_ace_version = get_option('ace_version');
  
  if (!$_ace_version)
  {
    add_option("ace_version",$ace_version);
  }
  elseif ($_ace_version != $ace_version)
  {
    update_option("ace_version",$ace_version);
  }
}
function ace_get_section()
{
	global $wp_query, $ace_targets;
	
	if (is_array($ace_targets))
	{
		foreach ($ace_targets as $key=>$val) 
		{
		  if (!empty($wp_query->$key) && $wp_query->$key == 1) $filter = $key;             	
		}
	}
	
	return $filter;
}

function ace_get_terms($terms, $taxonomies="", $args="")
{
  global $wp_query;  

	if (empty($terms) || empty($terms[0])) return $terms;

	$taxonomy = $terms[0]->taxonomy;
	
	$filter = ace_get_section();
	
	switch($taxonomy)
	{		
		case "link_category":
			$items = get_option("ace_linkcategory_sections_".$filter);
		break;
		
		default:
			return $terms;
		break;
	}
	
	if (get_option("ace_settings_hide") == 1 && !empty($items))
	/* Only if the user wants this function */
	{
		$items = explode(",",$items);
		$c = count($items); 
	   
	    if ($c > 0)
	    /* if there is any category to hide :) */
	    {
			for ($i=0; $i <= $c; $i++)
			{			
				if (in_array($terms[$i]->term_id,$items))
				{
					unset($terms[$i]);					
				}
			}
		}
	}
	return $terms;
}

add_filter('posts_join', 'ace_join');
add_filter('posts_where', 'ace_where');
add_filter('posts_distinct', 'ace_distinct');

add_filter('get_terms','ace_get_terms'); 
//add_filter('wp_list_categories','ace_list_categories'); // deprecated

add_filter('getarchives_where','ace_getarchives_where');
//add_filter('getarchives_join','ace_getarchives_join');
add_filter('wp_list_pages_excludes','ace_list_pages_excludes');
add_filter('get_bookmarks','ace_get_bookmarks');

add_action('admin_menu', 'ace_adminmenu');
add_action('admin_head', 'ace_admin_head');
add_action('init','ace_init');

add_action('wp_head', 'ace_head');

add_action('activate_advanced-category-excluder/advanced-category-excluder.php', 'ace_install');
add_action('deactivate_advanced-category-excluder/advanced-category-excluder.php', 'ace_uninstall');
?>