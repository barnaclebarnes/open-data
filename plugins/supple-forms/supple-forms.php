<?php
/*
Plugin Name: Supple Forms
Plugin URI: http://www.whypad.com/posts/supple-forms-a-wordpress-cms-plugin/566/
Description: A CMS plugin to create custom write panels whose data can be stored in custom fields or a custom table.  Provides powerful shortcode support for displaying data and HTML snippets in Posts/Pages.  Provides an Object containing form data that can be used in code for manipulating form data.
Version: 0.1.62
Author: Byron Bennett
Author URI: http://www.whypad.com/
*/

/** LICENSE: GPL
 *
 * This work is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation; either version 
 * 2 of the License, or any later version.
 *
 * This work is distributed in the hope that it will be useful, 
 * but without any warranty; without even the implied warranty 
 * of merchantability or fitness for a particular purpose. See 
 * Version 2 and version 3 of the GNU General Public License for
 * more details. You should have received a copy of the GNU General 
 * Public License along with this program; if not, write to the 
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, 
 * Boston, MA 02110-1301 USA
 */


define("SUPPLEFIELDSTABLE", $wpdb->prefix."sppl_fields");
define("SUPPLESNIPSTABLE", $wpdb->prefix."sppl_snips");
define("SUPPLEFORMSTABLE", $wpdb->prefix."sppl_forms");
define("SUPPLELOOKUPTABLE", $wpdb->prefix."sppl_lookup");
define("SUPPLETABLEPREFIX", $wpdb->prefix."supple_");


class SuppleForms{
	var $options;	
	var $suppleWrite;
	var $form_id;
	
	/**
	 * Constructor:	
	 * 
	 * Pre-loads the Form Settings
	 * @versions 0.1.X - multiple forms not implemented...always uses form_id == 1
	 */
	function SuppleForms(){
		
		
		/*
		 * Until multiple forms is implemented, set up default FORM ID
		 *
		if(isset($_REQUEST['sppl_form_id'])){
			$this->form_id = (int)$_REQUEST['sppl_form_id'];
		} else { $this->form_id = 0;}
		*/
		
		$this->form_id = 1;  //undo this when multiple forms is implemented

		$this->options = $this->getFormSettings($this->form_id);
	}
	
	/**
	 * Retrieve Form settings
	 * 
	 * @param int $form_id The form ID
	 */
	function getFormSettings($form_id)
	{
		global $wpdb;
		
		$form_id = (int)$form_id;
		$options = $wpdb->get_row("SELECT * FROM ".SUPPLEFORMSTABLE
			." WHERE form_id = ".$form_id, ARRAY_A);
			
		if(!$options)
		{
			$options = $this->getFormSettingsDefaults();
		}
		return $options;
	}
	
	
	function getFormSettingsDefaults()
	{
		//defaults if nothing is in the database
		return array(
				'form_id' => 0,
				'form_title' => '',
				'placement' => 'normal-sortables',
				'use_custom_fields' => 1,
				'custom_tablename' => '',
				'post_related' => 1,
				'write_page' => 1,
				'hide_wp_customfields' => 1,
				'seq' => 0,
				'status' => 1
		);
				
	}

	
	
	//Enqueue JS libraries
	function enqueueJS(){		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		//enqueue jQuery DatePicker
		wp_register_script('jquery_datepicker', get_bloginfo('wpurl') . '/wp-content/plugins/supple-forms/js/ui.datepicker.js', array('jquery'), '1.0');
		wp_enqueue_script('jquery_datepicker');
				
		//enqueue SuppleForms Javascript
		wp_register_script('supple_js', get_bloginfo('wpurl') . '/wp-content/plugins/supple-forms/js/supple-admin.js', array('jquery'), '1.0');
		wp_enqueue_script('supple_js');
	}
	
	//Add style sheets for the Date Picker
	function addDatePickerCSS()
	{
		echo '<link rel="stylesheet" type="text/css" href="'
			.get_bloginfo('wpurl').'/wp-content/plugins/supple-forms/css/ui.core.css" />
			';
		echo '<link rel="stylesheet" type="text/css" href="'
			.get_bloginfo('wpurl').'/wp-content/plugins/supple-forms/css/ui.datepicker.css" />
			';
	
	}
		
	/**
	 * Called when plugin is Activated
	 * 
	 * Triggers the functions that build the Supple Forms tables
	 */
	function init(){
		require_once("supple-init.php");
		 echo SUPPLEFORMSTABLE;
		$suppleinit = new SuppleFormsInit();
		
	}
	
	function checkTablesInstalled()
	{
		if(get_option('supple_install_error') == 1){ return; }
		global $wpdb;
		if($wpdb->get_var("SHOW TABLES LIKE '".SUPPLEFIELDSTABLE."'") != SUPPLEFIELDSTABLE) {
			$table[] = SUPPLEFIELDSTABLE;
		}
		if($wpdb->get_var("SHOW TABLES LIKE '".SUPPLESNIPSTABLE."'") != SUPPLESNIPSTABLE) {
			$table[] = SUPPLESNIPSTABLE;
		}
		if($wpdb->get_var("SHOW TABLES LIKE '".SUPPLEFORMSTABLE."'") != SUPPLEFORMSTABLE) {
			$table[] = SUPPLEFORMSTABLE;
		}
		if($wpdb->get_var("SHOW TABLES LIKE '".SUPPLELOOKUPTABLE."'") != SUPPLELOOKUPTABLE) {
			$table[] = SUPPLELOOKUPTABLE;
		}
		delete_option('supple_install_error');
		add_option('supple_install_error',1);
		if(is_array($table)){	
			$tables = implode(", ",$table);
			echo "<div class='error'><b>Supple Forms not installed properly.</b><br/>Tables not installed properly: ".$tables."</div>";
		}
	}
	
	
	/**
	 * Adds the Supple Forms menu items	to Admin
	 * 
	 */
	function suppleFormsOptionsPage()
	{
		global $suppleForms;
		if (!isset($suppleForms)) {
			return;
		}
		if (function_exists('add_menu_page')) {
			
			add_menu_page('Supple Forms', 'Supple Forms', 9, basename(__FILE__), array(&$suppleForms, 'loadAdminPage'));
			
			add_submenu_page(basename(__FILE__), __('Form Settings'), __('Form Settings'), 9,  basename(__FILE__), array(&$suppleForms, 'loadAdminPage'));
			
			add_submenu_page(basename(__FILE__), __('Add/Edit Fields'), __('Add/Edit Fields'), 9,  
			'editSuppleFormFields', array(&$suppleForms, 'loadFieldEditor'));
			
			add_submenu_page(basename(__FILE__), __('HTML Snips Editor'), __('HTML Snips Editor'), 9,  
			'editHTMLSnips', array(&$suppleForms, 'loadSnipsEditor'));
			
			add_submenu_page(basename(__FILE__), __('Database Viewer'), __('Database Viewer'), 9,  
			'viewSuppleFormTables', array(&$suppleForms, 'loadDatabaseViewer'));
		}
		
	}
	
	/**
	 * Load the Admin Page - General Settings
	 * 
	 */
	//  
	function loadAdminPage()
	{
		require_once("supple-admin.php");
		$suppleinit = new SuppleAdmin($this->options);
		$suppleinit->showMetaBoxSettings();
	}
	
	/**
	 * Load the Field Editor page
	 * 
	 */
	//  
	function loadFieldEditor()
	{
		require_once("supple-admin.php");
		$suppleinit = new SuppleAdmin($this->options);
		$suppleinit->showFieldEditor();
	}
	
	/**
	 * Displays the Database view page when menu item is selected
	 * 
	 */
	function loadDatabaseViewer()
	{
		require_once("supple-dbviewer.php");
		$suppleinit = new SuppleDBViewer($this->options);
	}
	
	/**
	 * Displays the Database view page when menu item is selected
	 * 
	 */
	function loadSnipsEditor()
	{
		require_once("supple-snips.php");
		$supplesnips = new SuppleSnipEditor();
	}
	
	/**
	 * Attaches the action to show the Meta Box...ends up calling showMetaBox()
	 * 
	 */
	function attachMetaBox() {
	  //Note:  Supple Forms requires WP 2.5 or greater
	  if( function_exists( 'add_meta_box' )) {
		add_meta_box( 'suppleforms_metabox'
			, __( $this->options['form_title'], 'suppleforms_textdomain' )
			, array(&$this,'showMetaBox'), 'post', 'normal', 'core' );
	   
		add_meta_box( 'suppleforms_metabox'
			, __( $this->options['form_title'], 'suppleforms_textdomain' )
			, array(&$this,'showMetaBox'), 'page', 'normal', 'core' );
	   }
	}
	
	/**
	 * Gets called when the Write Post/Page page is shown...Triggers the functions
	 *		that display the Supple Form on the Write Post page	
	 * 
	 */
	function showMetaBox()
	{
		//Add SuppleWrite object if not set
		if(!isset($this->suppleWrite))
		{
			require_once("supple-write.php");
	  		$this->suppleWrite = new SuppleWrite($this->options);
		}
		$this->suppleWrite->showMetaBox();
	}
	
	/**
	 * Gets called when a post is saved...Triggers the functions that save
	 *		the Supple Form data	
	 * 
	 * @param int $post_id The post ID
	 * 
	 */
	function saveMetaData($post_id)
	{
		//Add SuppleWrite object if not set
		if(!isset($this->suppleWrite))
		{
			require_once("supple-write.php");
	  		$this->suppleWrite = new SuppleWrite($this->options);
		}
		$this->suppleWrite->saveMetaData($post_id);
	}
	
	/**
	 * The shortcode callback function: this takes the shortcode and its attributes and computes something the display in the post
	 * 
	 * @param string $atts the attributes that will be parsed
	 *
	 * @return string The string that will replace the shortcode in the post
	 */
	function shortCode($atts, $content=null){
		extract(shortcode_atts(array(
			'snip' => false,
			'field' => false,
			'field_tag' => false,
			'field_class' => '',
			'field_style' => '',
			'label' => false,
			'label_tag' => false,
			'label_class' => '',
			'label_style' => '',
			'link' => false,
			'list' => ', ',
			'list_class' => false,
			'separator' => ', ',
			'date_format' => 'm/d/Y'
		),$atts));
		
		if($snip){
			return $this->showSnip($snip);
		}
		
		switch ($separator){
			case 'br':
				$separator = '<br/>';
				break;
			case 'ol' :
				$separator = '</li><li>';
				$sepopentag = "<ol><li>";
				$sepclosetag = "</li></ol>";
				break;
			case 'ul' :
				$separator = '</li><li>';
				$sepopentag = "<ul><li>";
				$sepclosetag = "</li></ul>";
				break;
			case 'div' :
				$separator = '</div><div>';
				$sepopentag = "<div>";
				$sepclosetag = "</div>";
				break;
			case 'p' :
				$separator = '</p><p>';
				$sepopentag = "<p>";
				$sepclosetag = "</p>";
				break;
			case 'span' :
				$separator = '</span><span>';
				$sepopentag = "<span>";
				$sepclosetag = "</span>";
				break;
			default:
				break;		
		}
		
		//Get the form data associated with the Post
		if($field){
			global $post;
			$fields = $this->getFields($this->form_id);
			$f = $this->getPostData($post->ID, $fields);
			
			//get list of labels
			if($label){
				//$label = str_replace("&lt;","<",$label);
				//$label = str_replace("&gt;",">",$label);
				
				if($label == 'false'){
					$label = false;
				}else {
					if($label == 'true'){
						$mylabel = "";
					} else {
						$mylabel = $label;
					}
				
					foreach($fields as $myfield)
					{
						$fieldArray[$myfield->field_name] = $myfield;
					}
				}
			}
			
			if($label_tag){
				if($label_class){
					$label_class = " class='$label_class'";
				}
				if($label_style){
					$label_style = " style='$label_style'";
				}
				
				$lblopentag = "<".$label_tag.$label_class.$label_style.">";
				$lblclosetag = "</".$label_tag.">";				
			}
			
		} else { return ''; }
		
		$field = str_replace(";",",",$field);
		$field = str_replace(" ", "", $field);
		
		$flist = explode(",", $field);
		
		//Fix up the field tag
		if($field_tag){
			if($field_class){
				$field_class = " class='".$field_class."'";
			}
			if($field_style){
				$field_style = " style='".$field_style."'";
			}
			
			$openfield = "<".$field_tag.$field_class.$field_style.">";
			
			$closefield = "</".$field_tag.">";
		}
		
		$glue = $closefield."</li><li>".$openfield;
		switch ($list){
			case ('ul') :
				$opentag = "<ul><li>".$openfield;
				$closetag = $closefield."</li></ul>";
				break;
			case ('ol') :
				$opentag = "<ol><li>".$openfield;
				$closetag = $closefield."</li></ol>";
				break;
			default :
				$opentag = $openfield;
				$closetag = $closefield;
				$glue = $list;
			break;
		}
		
		foreach($flist as $fld){
			
			//Fix up the Label
			if($label){
				$lbl = $fieldArray[$fld]->label. $mylabel;
			} else { $lbl = ''; }
			
			if($fieldArray[$fld]->type == 5){
				if(is_array($f[$fld])){
					$fldvalcount = count($f[$fld]);
					for($cnt = 0; $cnt < $fldvalcount; $cnt++){
						$f[$fld][$cnt] = date($date_format,strtotime ($f[$fld][$cnt]));
					}
				} else {
					$f[$fld] = date($date_format,strtotime ($f[$fld]));
				}
			}
			
			if(is_array($f[$fld])){
				$temp = implode($glue, $f[$fld]);
				$ret[] = $lblopentag.$lbl.$lblclosetag. $opentag. $temp . $closetag;
			} else {
				$ret[] = $lblopentag.$lbl.$lblclosetag. $openfield.$f[$fld].$closefield;
			}
		}
		$ret = $sepopentag. implode($separator, $ret). $sepclosetag;
		$ret = nl2br($ret);
		return $ret;
	}
	
	function showSnip($snip_name){
		global $post;
		$fields = $this->getFields($this->form_id);
		$f = $this->getPostData($post->ID, $fields);
		
		//Get the custom Snip HTML
		$snip = $this->getSnipHTML($snip_name);
		if(!$snip){return "Invalid Supple Forms HTML Snip name: ".$snip_name;}
		$ret = $snip->snip;
		
		
		//Determine how to handle lists...either use the default...use a single
		//list format for all lists, or use a matched array of formats -> lists
		$listCount = 0;
		
		if($snip->lists){
			$snip->lists = str_replace("\r\n","\n",$snip->lists);
			$snip->lists = str_replace("\r","\n",$snip->lists);
			$listformats = explode("\n",$snip->lists);		
		} else { $listformat[0] = ", ";}
		
		$formatCount = count($listformats);
		
		foreach($fields as $fld){
			if(is_array($f[$fld->field_name])){
				unset($mval);
				foreach($f[$fld->field_name] as $fldval){
					$mval[] = $fldval;
				}
				
				//Format the list tags
				if($listCount > $formatCount)
				{ 
					$format = $listformats[0];
				} else {
					$format = $listformats[$listCount];
				}
				
				switch ($format){
				case ('ul') :
					$opentag = "<ul><li>";
					$closetag = "</li></ul>";
					$glue = "</li><li>";
					break;
				case ('ol') :
					$opentag = "<ol><li>";
					$closetag = "</li></ol>";
					$glue = "</li><li>";
					break;
				case ('br') :
					$opentag = "";
					$closetag = "";
					$glue = "<br/>";
					break;

				default :
					$opentag = "";
					$closetag = "";
					$glue = $format;
				
				}
				
				$val = $opentag.implode($glue,$mval).$closetag;
				
				$listCount++;
				
			} else {
				$val = $f[$fld->field_name];
			}
				$ret = str_replace("[".$fld->field_name."]", $val, $ret);
		}
		
		if($snip->css){
			$ret = "<style type='text/css'>
				<!--
				".$snip->css."
				-->
				</style>".$ret;
		}
		
		if(get_magic_quotes_gpc()){
				$ret = stripslashes($ret);
		}
		
		return $ret;
	}
	
	function getSnipHTML($snip_name)
	{
		global $wpdb;
		return $wpdb->get_row($wpdb->prepare("SELECT * FROM ".SUPPLESNIPSTABLE.
			" WHERE snip_name = %s",$snip_name));
	}
	
	/**
	 * Retrieve form and custom field data for a Post	
	 * 
	 * @param int $post_id The post ID
	 * @return $data - an associative array with field names as keys
	 * 
	 * Multiple value fields with have their values as an array
	 */
	function getPostData($post_id, $fields)
	{
		
		if(!$post_id){
			return false;
		}

		global $wpdb;
		
		// Get list of Multi Value and Checkbox fields for 
		// retrieving data from WP Custom Fields
		foreach($fields as $f){
			//Include Checkboxes and Multi Value fields
			if($f->type == 4 || $f->multi_val ==1){
				$metakeys[] = str_replace("'","",$f->field_name);
			}
		}
		
		if(is_array($metakeys)){
			$mkeys = "('". implode("','", $metakeys) . "')";
		}
		
		if($post_id){
			$sql = "SELECT * FROM ".$wpdb->postmeta
				." WHERE post_id = ".$post_id." AND meta_key IN ".$mkeys." ORDER BY meta_id";
		
			$custfields = $wpdb->get_results($sql);
					
			//Put Custom Fields into an Array of Arrays	
			if($custfields){
				foreach($custfields as $row)
				{
					$cf[$row->meta_key][] = $row->meta_value;
					
					//Get list of Meta-Keys to hide in write form
					if($this->options['hide_wp_customfields']){
						if(in_array($row->meta_key, $metakeys)){
							$this->hide_id[] = $row->meta_id;
						}				
					}
				}
			}
						
			//If Custom Tables is on...get Custom Table Data
			if(!$this->options['use_custom_fields'])
			{
				if($this->options['custom_tablename']){
					$custtable = $wpdb->get_row("SELECT * FROM "
						.SUPPLETABLEPREFIX.$this->options['custom_tablename']
						." WHERE post_id = ". $post_id, ARRAY_A);
					
					if(!$custtable){
						//populate with default values
						foreach($fields as $f){
							$custtable[$f->field_name] = $f->default_val;
						}
					}
					
				}
			}
			$bCT = !$this->options['use_custom_fields'] ? true : false;
			
			//Go through each field and get the appropriate data values
			foreach($fields as $f){
				if($bCT && !$f->multi_val){
					$data[$f->field_name] = $custtable[$f->field_name];
				} else {
					if($f->multi_val || $f->type == 4){
						$data[$f->field_name] = $cf[$f->field_name];
					} else {
						$data[$f->field_name] = $cf[$f->field_name][0];
					}				
				}
			}
			return $data;	
		}
	
	}
	
	/**
	 * Returns an associative array of the deined fields & their defined params	
	 * 
	 * @param int $form_id the form ID for which you want to get fields for
	 *
	 * Note:  multiple forms not implemented in 0.1.X versions...form_id should == 1
	 *
	 */
	function getFields($form_id){
		global $wpdb;
		return $wpdb->get_results("SELECT * FROM "
			.SUPPLEFIELDSTABLE." WHERE form_id = ".(int)$form_id);
	}
	

} //End of SuppleForms Class


$suppleForms = new SuppleForms();

add_shortcode('supple', array(&$suppleForms, 'shortCode'));

//Load the Javascript file to the Admin pages only
add_action( "admin_print_scripts", array(&$suppleForms, 'enqueueJS'));

//Call the Function that will Add the Options Page
add_action('admin_menu', array(&$suppleForms, 'suppleFormsOptionsPage'));


//Call the INIT function whenever the Plugin is activated
add_action('activate_supple-forms/supple-forms.php',
array(&$suppleForms, 'init'));

add_action('admin_notices', array(&$suppleForms, 'checkTablesInstalled'));


/* Use the admin_menu action to define the custom boxes */
add_action('admin_menu', array(&$suppleForms, 'attachMetaBox'));


/* Use the save_post action to do something with the data entered */
add_action('save_post', array(&$suppleForms, 'saveMetaData'));

/* Add jQuery DatePicker Style sheet */
add_action('admin_head', array(&$suppleForms, 'addDatePickerCSS'));

?>