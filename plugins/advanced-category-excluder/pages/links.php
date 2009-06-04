<?php
    global $wpdb, $ace_targets;

	unset($ace_targets['is_feed']);
	unset($ace_targets['is_comment_feed']);
	unset($ace_targets['norobots']);	
	
    if ($_POST['submit'])
    // is this form submited?
    {
      if (!empty($_POST['categories']))
      {  
        if (is_array($ace_targets))
  	    {
  	    
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
        		    update_option("ace_link_sections_".$key,$s);
        		}
        		// no, so we clean up.
        		else update_option("ace_link_sections_".$key,'');
        		
            
        		unset($data);
      	  }
  	    }
	    }
    }
    
    if ($_POST['empty'])
    {
    	foreach ($ace_targets as $key=>$val)
    	{
    	    update_option("ace_link_sections_".$key,"");
    	}
    }


  $links = get_bookmarks(array('echo'=>'0'));
    
?>
<div class="wrap">
  <h2><?php _e('Advanced Category Excluder','ace'); ?>&nbsp;-&nbsp;<?php _e('Links','ace'); ?></h2>
  <div class="metabox-holder" id="poststuff">
  <form method="post">
    <div id="ace_categories_usage" class="postbox">
      <h3 class="hndle">
        <span><?php _e('Usage','ace'); ?></span>
      </h3>
      <div class="inside">
      	<ul>
      	 <li><?php echo sprintf(__("Here are %s different sections on the site. You can define section by section which links would you like to hide.","ace"), count($ace_targets)); ?></li>
      	</ul>
      </div>
    </div>
    <div class="postbox">
        <h3 class="hndle"><?php _e('Sections','ace'); ?></h3>
        <div class="inside">                      
     <?php 
      foreach ($ace_targets as $key=>$val):
      $_cats = explode(",",get_option("ace_link_sections_".$key));
     ?>
        <div style="width: 16%; float: left;">     
          <br />
          <strong><?php echo $val ?></strong><br />
          <br />                      
          <?php foreach ($links as $link): ?>
    		    <label>
              <input type="checkbox" name="categories_<?php echo $key; ?>[]" value="<?php echo $link->link_id; ?>" <?php if (in_array($link->link_id,$_cats)) echo "checked"; ?> /><?php echo $link->link_name; ?>
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