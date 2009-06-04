<?php
//Write Page for Supple Forms plugin
class SuppleWrite{
	
	var $options;
	var $message = false;
	var $msgclass = "updated fade";
	var $hide_id;
	var $tabindex = 50;
		
	//Constructor
	function SuppleWrite($_options){
		$this->options = $_options;
	}
	
	  
	// $supple_data is the Array that will collect form data
	var $supple_data = false;
	
	/* When the post is saved, saves our custom data */
	function saveMetaData( $post_id ) {
		global $wpdb;
		global $supple_data;
		;
	  if(!$post_id){
	  	return false; //we don't know how you got here, but go away
	  }
	  
	  // verify this came from our screen and with proper authorization, because save_post can be triggered at other times
	  
	  //Don't want to create a record for meta data for a Revision...Only save for actual posts
	  if($wpdb->get_var("SELECT post_type FROM ".$wpdb->prefix."posts WHERE ID = ".
		(int)$post_id) == 'revision' ){ return;}
	  
	  //Ah...the nonce in the corner
	  if ( !wp_verify_nonce( $_POST['SuppleForms_noncename'], 'SuppleForms_plugin' )) {
		return $post_id;
	  }
	  
	  //Does user have mojo?
	  if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ))
		  return $post_id;
	  } else {
		if ( !current_user_can( 'edit_post', $post_id ))
		  return $post_id;
	  }
	
	  // OK, we're authenticated: we need to find and save the data
		
	  //Get the Field List from our Database
		$sql = "SELECT * FROM ".SUPPLEFIELDSTABLE." WHERE form_id = ".$this->options['form_id'];
		$fields = $wpdb->get_results($sql);
		  
	  if($this->options['use_custom_fields']){
	  	$this->saveCustomFields($fields, $post_id);
	  } else {
	  	$this->saveCustomTable($fields, $post_id);
	  }
	
	}
			 
	// ********************************************
	// *	Save Custom FIELDS
	// ********************************************	
	function saveCustomFields($fields, $post_id)
	{
		$tags = $this->getFilterArrays();
		foreach($fields as $f){
			$this->saveCustField($f,$post_id, $tags);	
		}
	}
	
	function saveCustField($f,$post_id, $tags)
	{
		global $wpdb;
		$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->postmeta." WHERE post_id = "
			.(int)$post_id." AND meta_key = %s", $f->field_name));
		
		if(!isset($_POST['sppl_'.$f->field_id.$f->field_name])) { return false; }
		
		if($f->multi_val && is_array($_POST['sppl_'.$f->field_id.$f->field_name])){
			foreach($_POST['sppl_'.$f->field_id.$f->field_name] as $val){
				$val = $this->formatSaveVal($f->html_filter,$val,$tags);
				$data = array(
					'post_id' => $post_id,
					'meta_key' => $f->field_name,
					'meta_value' => $val
				);
				
				$wpdb->insert($wpdb->postmeta,$data);
			} 
		}else {
			if(is_array($_POST['sppl_'.$f->field_id.$f->field_name])){
				$val = $_POST['sppl_'.$f->field_id.$f->field_name][0];
			} else {
				$val = $_POST['sppl_'.$f->field_id.$f->field_name];
			}
			$val = $this->formatSaveVal($f->html_filter,$val,$tags);
			$data = array(
				'post_id' => $post_id,
				'meta_key' => $f->field_name,
				'meta_value' => $val
			);	
			$wpdb->insert($wpdb->postmeta,$data);
		}
	}
	
	// ********************************************
	// *	Save Custom Table
	// ********************************************
	function saveCustomTable($fields,$post_id)
	{
		global $wpdb;
		
		//Try to update record in Custom Table
		if(!$this->options['custom_tablename']){
			$this->message = "Missing custom table name."; 
			return false;
		}
		
		//Get allowable HTML for filtering
		$tags = $this->getFilterArrays();
		
		foreach($fields as $f){
			if($f->multi_val ==1){
				//All Multi-val enabled fields are stored as WP custom fields
				$this->saveCustField($f,$post_id, $tags);
			} else {
				$val = null;
				
				if(isset($_POST['sppl_'.$f->field_id.$f->field_name])){
					$val = $_POST['sppl_'.$f->field_id.$f->field_name]; 
				}
				
				$val = $this->formatSaveVal($f, $val, $tags);
				$data[$f->field_name] = $val;
			}
		}
		$data['supple_status'] = 0;
		
		$tablename = SUPPLETABLEPREFIX.$this->options['custom_tablename'];
		$where = array('post_id' => (int)$post_id );
		
		$res = $wpdb->update($tablename, $data, $where);
		if(!$res){
			//Insert the data if no record was updated.
			$data['post_id'] = (int)$post_id;
			
			$reccnt = $wpdb->get_var("SELECT COUNT(post_id) as cnt FROM "
				. " $tablename WHERE post_id = ".$data['post_id']);
			
			if( $reccnt <> 1){
				//delete existing records based on POST ID...must change when go to 
				//forms that don't rely on POST ID
				$wpdb->query("DELETE FROM $tablename WHERE post_id = $post_id");
				$wpdb->insert($tablename, $data);
				$this->message = "Supple data inserted.";
			} else {
				$this->message = "Supple data updated";
			}
			
		}else {
			$this->message = "Supple data updated.";
		}
	}
	
	
	function formatSaveVal($field, $val,$tags){
		//If it's a numeric field type
		if($field->numeric_field == 1 && $field->type == 0)
		{	
			$val = (double)(trim($val));	
		} else{
			if(get_magic_quotes_gpc()){
				$val = stripslashes($val);
			}
			$i = (int)$field->html_filter;
			
			if($field->html_filter < 3){
				$val = wp_kses($val,$tags[(int)$field->html_filter]);
			}
		}
		//Format for date:
		if($field->type == 5){
				$val = date('Y-m-d',strtotime ($val));
		}
		
		return $val;
	}
	
	
	function getFilterArrays(){
	
		//Allowable tag arrays for use with wp_kses
	 	//Allow formatting
	 	 $tags[0] = array('b' => array());
		 $tags[1] = array(
			'abbr' => array(
				'title' => array()
				),
			'acronym' => array(
				'title' => array()
				),
			'code' => array(),
			'em' => array(),
			'strong' => array(),
			'b' => array()
		);
		
		//Allow links and lists + formatting
	  	$tags[2] = array(
			'a' => array( 
				'href' => array(), 
				'title' => array(), 
				'rel' => array()
				),
			'ul' => array(
				'id' => array(),
				'class' => array()
				), 
			'ol' => array(
				'id' => array(),
				'class' => array()
				),
			'li' => array(), 
			'abbr' => array(
				'title' => array()
				),
			'acronym' => array(
				'title' => array()
				),
			'code' => array(),
			'em' => array(),
			'strong' => array(),
			'b' => array()
		);
		return $tags;
	}
	
	/* Prints the inner fields for the custom post/page section */
	function showMetaBox() {
		global $wpdb;
		
		$post_id = (int)$_REQUEST['post'];
		
		//Get the Field List
		$fields = $wpdb->get_results("SELECT * FROM "
			.SUPPLEFIELDSTABLE." WHERE form_id = ".$this->options['form_id']." ORDER BY seq");
		
		if(!$fields){ 
			echo "<h3>Supple Forms WARNING:</h3>No custom fields have been added.";
			return false;
		}
		
		$data = $this->getDataStructure($post_id, $fields);
		
		
	  // Use nonce for verification
	  echo '<input type="hidden" name="SuppleForms_noncename" id="SuppleForms_noncename" value="' . 
		wp_create_nonce( 'SuppleForms_plugin' ) . '" />';
	
	  // The actual fields for data entry
	  
	  ?>
	<table class="form-table" style="width: 100%;" cellspacing="2" cellpadding="5">
	
	<?php
		if($this->message){
				echo '<tr><th>Message:</th><td><div id="message" class="'.$this->msgclass.'"><p>'.$this->message.'</p></div></td></tr>';
		}
		$cnt = 0;
		foreach($fields as $f)
		{
			
			if($f->type == 1 || $f->type == 2 || $f->type == 3)
			{ $f->multi_val = 0; //These types cannot be multi-value 
			}
			
			if(is_array($data[$f->field_name]) && $f->type <> 4 && $f->type <> 0)
			{
				//We do this for anything that somehow gets an array of values...then figure out if it should be an array or not
							
				if($f->multi_val == 1){
					$maxcnt = count($data[$f->field_name]);
				} else { 
					$maxcnt = 1; 
				}
				
				$multcnt = 0;
				foreach($data[$f->field_name] as $d)
				{
					$multcnt++;
					if($multcnt > $maxcnt){break;}
					if(!$post_id){$d = $f->default_val;}
					echo $this->buildFormField($f, $d, $multcnt);
				}
			} else {
				if(!$post_id){$data[$f->field_name] = $f->default_val;}
				echo $this->buildFormField($f, $data[$f->field_name], false);
			}
			
		}
	
	?>
	</table>
	  
	<script type="text/javascript">
		var suppleFormsPlacement = <?php echo $this->options['placement']; ?>;
		<?php
			//This little bit of Code provides Javascript array for hiding WP Custom Fields 
			//that are used in Supple Forms...other custom fields are left alone.
			if(is_array($this->hide_id)){
				echo "var suppleHideMetaID = new Array();
				";
				$i=0;
				foreach($this->hide_id as $hideid){
					
					echo " suppleHideMetaID[$i] = ".$hideid.";
					";
					$i++;
				}
			}else{
				echo "var suppleHideMetaID = false;";
			}
		?>
	jQuery(document).ready(function() { 
		suppleHideWPCustomFields();
			
			//Place the meta box in the desired location
		if(jQuery('#suppleforms_metabox').length > 0){
			switch (suppleFormsPlacement)
			{
			case 1:
				jQuery('#suppleforms_metabox').insertAfter("#titlediv");
				break;
			case 2:
				jQuery('#suppleforms_metabox').appendTo("#normal-sortables");
				break;
			default:
				jQuery('#suppleforms_metabox').prependTo("#normal-sortables");
			}
		}
	});
	</script>
	<?php
	}
	
	
	//BUILD THE FORM FIELD
	function buildFormField($f, $val, $multcnt){
		
		//Name is field name + sppl (prepended)
		$name = "sppl_".$f->field_id.$f->field_name;
		
		//Doesn't work for multi value items...build their IDs ad hoc
		$id = " id='sppl_".$f->field_name."'";
		
		$f->name = $name;
		
		//adjust the id and name for Multi Value
		if($f->multi_val == 1 || $f->type == 4){
			$name .= "[]";
		}
		
		//Element name....works for about everything
		$ele_name = " name='$name'";
		
		
		$opts['field_type'] = $f->type;
		switch ($f->type){
			case 0 :	//text box
			
				if($f->multi_val ==1){
				
					//Multi Value Text Box
					if(!is_array($val)){ $val[] = $val;}
					$eleCnt = 0;
					foreach($val as $d){
						$f->ele_id = $f->name."_".$eleCnt;
						$ret .= "<input tabindex='".$this->tabindex++."' id='"
							.$f->ele_id
							."' ".$ele_name
							." value='".htmlentities($d, ENT_QUOTES)
							."' type='text' size='255' />";
					
						if($eleCnt){
							//Do not put a duplicator on the first value
							$ret .= $this->getDuplicator($f, false);			
						}
						$eleCnt++;
					}
					
					$ret .= $this->getDuplicator($f, true);
				
				} else {
				
					//Single Value Text Box
					$ret = "<input tabindex='".$this->tabindex++."' ".$id
						." ".$ele_name
						." value='".htmlentities($val, ENT_QUOTES)
						."' type='text' size='255' />";
					
						if($f->multi_val == 1){
							$ret .= $this->getDuplicator($f);
						}
				}
					
				break;
			case 1 :	//textarea
				$ret = "<textarea tabindex='".$this->tabindex++."' ".$id
					." ".$ele_name
					." rows=4 cols=40 />"
					.htmlentities($val, ENT_QUOTES)."</textarea>
					";
				break;
			case 2 :	//option (ddl)
				$ret = "<select tabindex='".$this->tabindex++."' ".$id
					." ".$ele_name.">";
				$ret .= "<option value=''>--Select--</option>";
				
				$opts['opentag'] = "option";	//opening tag
				$opts['closetag'] = "</option>";	//closing tag
				$opts['selected'] = 'selected';  // indicator for selected value
				$opts['defval'] = $val;
				$opts['type'] = "";	// input type (e.g. type='text')
				$opts['name'] = "";  //form field name (radioboxes need this)
				
				$ret .= $this->getFieldValueOptions($f->field_id, $opts);
				
				$ret .= "</select>";
				break;
			case 3 :	//radio
			
				$opts['opentag'] = "input ";	//opening tag
				$opts['closetag'] = "<br/>\n";	//closing tag
				$opts['selected'] = "checked='checked'";  // indicator for selected value
				$opts['defval'] = $val;
				$opts['type'] = "type='radio'";	// input type (e.g. type='text')
				$opts['style'] = "style='width:auto;'";
				$opts['name'] = "name='"."sppl_".$f->field_id.$f->field_name."'";  //form field name
							//radioboxes need name defined
				
				$ret = "<div>".$this->getFieldValueOptions($f->field_id, $opts)."</div>";
				
				break;
			case 4 :	//checkboxes
				$opts['opentag'] = "input tabindex=".$this->tabindex++ ;	//opening tag
				$opts['closetag'] = "<br/>";	//closing tag
				$opts['selected'] = 'checked="checked"';  // indicator for selected value
				$opts['defval'] = $val;
				$opts['type'] = "type='checkbox'";	// input type (e.g. type='text')
				$opts['multi_select'] = true;
				$opts['style'] = "style='width:auto;'";
				$opts['name'] = "name='"."sppl_".$f->field_id.$f->field_name."[]'";  //form field name
				
				$ret = $this->getFieldValueOptions($f->field_id, $opts);
				$ret .= "<input type='hidden' name='subtle_nonsense'/>";
				break;
			case 5 :	//date picker
				if($val){
					$val = date('m/d/Y',strtotime ($val));
				}
				$ret = "<input tabindex='".$this->tabindex++."' ".$id
					." ".$ele_name
					." value='".htmlentities($val, ENT_QUOTES)
					."' type='text' style='width:130px;' />";
					
				$ret .= "
				<script type='text/javascript'>
					jQuery(document).ready(function(){
						jQuery('#sppl_".$f->field_name."').datepicker();
					});
				</script>
				";
				break;
			case 6 :	//hidden
				//Not implemented
				break;
			default :
					
				break;
		}
		
		if($f->status < 1 && $this->options['use_custom_fields'] == 0){
			$warn = "<br/><span style='color: red; font-size: 10px;'>Out of date.  <a href='admin.php?page=editSuppleFormFields'>Regenerate table</a>.</span>";
		}
		
		$ret = "
		<tr class='form-field'>
			<th scope='row'><label for='"."sppl_".$f->field_id.$f->field_name."'>".$f->label.$warn."</label>
			</th>
			<td>
				".$ret."
			</td>
		</tr>
		";
		
		return $ret;
	}
	
	
	//Build the HTML INPUT elements for the Form
  function getFieldValueOptions($field_id, $opts)
  {
  	global $wpdb;
  	//Get the Value Options  	
  	$sql = "SELECT * FROM ".SUPPLELOOKUPTABLE." WHERE field_id = ".(int)$field_id. " ORDER BY seq";
  	$query = $wpdb->get_results($sql);
  	if(!$query ||$wpdb->num_rows == 0){return "";}
  	  	
  	//Walk through our data set and create HMTL entities for each option
  	foreach($query as $row){
  		
  		$ret .= "<".$opts['opentag']." "
  			.$opts['name']." "
  			.$opts['type']." ".$opts['style']." "
  			."value='".htmlentities($row->value, ENT_QUOTES)."'";
		$sel = "";
  		if(is_array($opts['defval'])){
			if($opts['multi_select']){
				foreach($opts['defval'] as $v){
					if($v == $row->value){
						$sel .=" ".$opts['selected'];
					}
				}
			} else {
				if($opts['defval'][0] == $row->value){
						$sel .=" ".$opts['selected'];
				}
			}
		} else {
			
			if($opts['defval'] == $row->value){
				$ret .=" ".$opts['selected'];
			} else {$ret .= ""; }
		}
  		$ret .= $sel." "
  			.">".$row->label.$opts['closetag'];
  	}
  	return $ret;
  }

	//Retrieve existing data to pre-populate Form with	
	function getDataStructure($post_id, $fields)
	{
		if(!$post_id){
		 	//load up defaults
		 	foreach($fields as $f){
		 		if($f->type == 4 || $f->multi_val == 1){
		 			$data[$f->field_name][] = $f->default_val;
		 		} else {
		 			$data[$f->field_name] = $f->default_val;
		 		}
			}
			
			return $data;
		}
		global $wpdb;
		
		//Get list of Multi Value and Checkbox fields for retrieving data from WP Custom Fields
		foreach($fields as $f){
			//Include Checkboxes and Multi Value fields
			if($f->type == 4 || $f->multi_val ==1 || $this->options['use_custom_fields'] == 1){
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
	
	function getDuplicator($f, $final){
		if($f->field_id && $f->multi_val == 1){			
			if($final){
				//On final round, add the Add button
				$ret .= "<p  id='add_"
					.$f->name."_0'><a href='javascript:void(0);' onclick='suppleDuplicator(\""
					.$f->name."_0\"); return false;' title='Add another'>add</a></p>";
			} else {
				$ret = "<a href='javascript: void(0);' id='"
					.$f->ele_id."_remover' onclick='suppleRemover(\""
					.$f->ele_id."\"); return false;'>X</a>";

			}
		}
		return $ret;
	}
	
  	
}


?>