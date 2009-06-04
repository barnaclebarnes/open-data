<?php
global $wpdb, $ace_targets, $ace_settings, $ace_methods;

  $ace_help = array();
  $ace_help['hide']=__('If you check this one, the categories you selected for Home section will be remove from the Category lister sidebar widget.','ace');
  $ace_help['onlyinwidget']=__("If you select this, that will cause that your blog's Home page won't be affected, only your sidebar widgets.",'ace');
  $ace_help['showempty']=__('This is an inside setting. If you check that, you can select empty categories for exclusion at ACE Dashboard -> Categories subpage','ace');
  $ace_help['ec3']=__('If you select this, Event Calendars deafult category will be displayed at ACE Dashboard -> Categories subpage','ace');
  $ace_help['exclude_method']=__('Please choose an exclusion method for the widgets.','ace');  
  $ace_help['xsg_category']=__('If you choose one of these sections, the categories selected for exclusion in that section, will be exported into <strong>XML Sitemap Generator</strong> settings. So those settings will directly affect your sitemap generating. This is handy for SEO.','ace');      

  if (!empty($_POST['settings']))
  {
    foreach ($ace_settings as $k=>$v)
    {
      if (!empty($_POST[$k])) 
      {
        switch ($k)
        {
          case "xsg_category":
            $xsg_category = get_option("ace_settings_xsg_category");
            $newvalue = mysql_real_escape_string($_POST[$k]);
          
            update_option("ace_settings_".$k,$newvalue);
            
            /**
             * If the section changed
             */                         
            if ($xsg_category != $newvalue)
            {
              ace_xsg_update("",$newvalue);
            }          
          break;

          case "exclude_method":
          
            $newvalue = mysql_real_escape_string($_POST[$k]);
            update_option("ace_settings_".$k,$newvalue);
                      
          break;
          
          default:
            update_option("ace_settings_".$k,1);          
          break;
        }
      }
      else
      {
        update_option("ace_settings_".$k,'');
      }
    }
  }

  foreach ($ace_settings as $k=>$v)
  {
    $$k = get_option("ace_settings_".$k);
  }
  reset($ace_settings);  

  if (get_option("ace_settings_ec3") != "1" && get_option("ec3_event_category") != "")
  {
    $ec3Category = get_option("ec3_event_category");
  }

?>
<div class="wrap">
  <h2><?php _e('Advanced Category Excluder','ace'); ?></h2>
  <div class="metabox-holder" id="poststuff">
    <div id="ace_main" class="postbox">
      <h3 class="handle">
        <span><?php _e('Settings','ace'); ?></span>
      </h3>
      <div class="inside">
        <form method="post">
        <ul>
      	<?php foreach ($ace_settings as $key=>$val): 
        if (($key == 'ec3' && get_option("ec3_event_category") == "") || $key == 'xsg_category' || $key == 'exclude_method' ) continue;
        /* if the user has no event calendar than we hide that option */ ?>
        <li>	
        	<label>
        	    <input type="checkbox" name="<?php echo $key; ?>" <?php if (${$key} == '1') echo "checked"; ?> />
        	    <?php echo $val; ?><br /> 
        	</label>
        	<?php if (!empty($ace_help[$key])): ?>
          <code><?php echo $ace_help[$key]; ?></code><br/>
          <?php endif; ?>
          <br />
        </li>
      	<?php endforeach; ?>
    	<?php
          $active = get_option("active_plugins");
          if (in_array("google-sitemap-generator/sitemap.php",$active)):
      ?>
      <li>
    	<label>
    	     <?php echo $ace_settings['xsg_category']; ?>
    	    <select name="xsg_category">
    	     <option value=""><?php _e('None','ace'); ?></option>
           <?php foreach($ace_targets as $key=>$val): ?>
           <option value="<?php echo $key; ?>" <?php if($xsg_category == $key): ?>selected<?php endif; ?>><?php echo $val; ?></option>
           <?php endforeach; ?>
    	    </select>
    	    <br />
    	</label>
      <code><?php echo $ace_help["xsg_category"]; ?></code><br /><br />
      </li>
      
      <?php endif; ?>
      
      <li>
    	<label>
    	     <?php echo $ace_settings['exclude_method']; ?>
    	    <select name="exclude_method">
           <?php foreach($ace_methods as $key=>$val): ?>
           <option value="<?php echo $key; ?>" <?php if($exclude_method == $key): ?>selected<?php endif; ?>><?php echo $key; ?></option>
           <?php endforeach; ?>    	    
    	    </select>
    	    <br />
    	</label>
      <code><?php echo $ace_help["exclude_method"]; ?></code><br /><br />
      <?php foreach($ace_methods as $key=>$val): ?>
        <strong><?php _e(sprintf("%s method:",ucfirst($key))); ?></strong><br />
        <code><?php echo $val; ?></code><br /><br />
      <?php endforeach; ?>      
      </li>      
      
      </ul><br/>      
      
    	<input type="hidden" name="settings" value="1">
      <input class="button-primary action" type="submit" name="submit" value="<?php _e('Save','ace'); ?>"/>
      <br class="clear"/>
        </form>
    </div>
  </div>
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