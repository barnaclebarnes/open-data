<?php
    global $wpdb, $ace_targets;

    if ($_POST['submit'])
    // is this form submited?
    {
      if (!empty($_POST['categories']))
      {  
        if (is_array($ace_targets))
  	    {
          $xsg_category = get_option("ace_settings_xsg_category");
  	    
          foreach ($ace_targets as $key=>$val)
      	  {
        		$data = $_POST['categories_'.$key];
        		if (!empty($data))
        		// do we have selected categories?
        		{
        		    $c = count($data)-1; $s = "";
        		    foreach ($data as $k=>$v)
        		    {
                  $s.=trim($v); if ($c != $k) $s.=",";
                }
        		    update_option("ace_categories_".$key,$s);
        		}
        		// no, so we clean up.
        		else update_option("ace_categories_".$key,'');
        		
        		if($key == $xsg_category)
        		{
        		  if (empty($data)) $data = array();
              ace_xsg_update($data);
            }
            
        		unset($data);
      	  }
  	    }
	    }
    }
    
    if ($_POST['empty'])
    {
    	foreach ($ace_targets as $key=>$val)
    	{
    	    update_option("ace_categories_".$key,"");
    	}
    }

  if(!function_exists('get_terms'))
  /**
   * Wordpress 2.2 < way, to list out categories
   */
  {
    if(get_option("default_link_category") != "") $defaultLinkCat = get_option("default_link_category");    
    /**
     * This is deprecated since WP 2.3
     */
    if (!empty($ec3Category)) $exclude = 'exclude='.$defaultLinkCat.','.$ec3Category;
    else $exclude = 'exclude='.$defaultLinkCat; 
  }
  else
  {
    if (!empty($ec3Category)) $exclude = 'exclude='.$ec3Category;
    else $exclude = 'exclude=';
  }
  
  // for ec3 users
  if (get_option("ace_settings_ec3") != "1" && get_option("ec3_event_category") != "")
  {
    $exclude = 'exclude='.$defaultLinkCat.','.get_option("ec3_event_category");
  }
  else $exclude = 'exclude='.$defaultLinkCat;

  $hide_empty = '&hide_empty=1';
  if (get_option("ace_settings_showempty") == '1')
  {
    $hide_empty = '&hide_empty=0';
  }

  $categories = get_categories($exclude.$hide_empty);  
?>
<div class="wrap">
  <h2><?php _e('Advanced Category Excluder','ace'); ?>&nbsp;-&nbsp;<?php _e('Categories','ace'); ?></h2>
  <div class="metabox-holder" id="poststuff">
  <form method="post">
    <div id="ace_categories_usage" class="postbox">
      <h3 class="hndle">
        <span><?php _e('Usage','ace'); ?></span>
      </h3>
      <div class="inside">
      	<ul>
      	 <li><?php echo sprintf(__("Here are %s different sections on the site. You can define section by section which categories would you like to hide.","ace"), count($ace_targets)); ?></li>
      	</ul>
      </div>
    </div>
    <div class="postbox">
        <h3 class="hndle"><?php _e('Sections','ace'); ?></h3>
        <div class="inside">                      
     <?php 
      foreach ($ace_targets as $key=>$val):
      $_cats = explode(",",get_option("ace_categories_".$key));
     ?>
        <div style="width: 16%; float: left;">     
          <br />
          <strong><?php echo $val ?></strong><br />
          <br />                      
          <?php foreach ($categories as $category): ?>
    		    <label>
              <input type="checkbox" name="categories_<?php echo $key; ?>[]" value="<?php echo $category->cat_ID; ?>" <?php if (in_array($category->cat_ID,$_cats)) echo "checked"; ?> /><?php echo $category->cat_name; ?>
            </label>
            <br class="clear"/>
  		    <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
        <br class="clear"/>
        <br />
      	<input type="hidden" name="categories" value="1">    	
        <input class="button-primary action" type="submit" name="submit" value="<?php _e('Doit!','ace'); ?>"/>
        <input class="button-secondary action" type="submit" name="empty" value="<?php _e('Reset','ace'); ?>">       
        </div>
      </div>
      </form>      
      <div id="ace_donate" class="postbox">
        <h3 class="hndle">
          <span><?php _e('Donate!','ace'); ?></span>
        </h3>
        <div class="inside">
        	<ul>
        	 <li><strong><?php echo sprintf(__('If you like this plugin, please make <a href="%s" target="_blank">a donation here</a>'), "https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal@djz.hu&item_name=Advanced%20Category%20Excluder%20for%20Wordpress"); ?></strong></li>
        	</ul>
        </div>
      </div>  
  </div>
</div>    
<?php
    $wpdb->flush();
?>