<?php
//Database Viewer for Supple Forms plugin


class SuppleSnipEditor{
	
	var $options;
	var $message = false;
	var $msgclass= "updated fade";
	var $snip_id;
	
	//Constructor
	function SuppleSnipEditor(){
				
		if(isset($_REQUEST['sppl_snip_id'])){
			$this->snip_id = (int)$_REQUEST['sppl_snip_id'];
		} else { $this->snip_id = 0; }
		
		//Save Snip
		if(isset($_POST['saveSuppleSnip'])){
			$ret = $this->saveSnip();
			if($ret){
				$this->snip_id = $ret;
			}
		}
		
		//Load up Snip defaults
		
		$this->showSnipsForm();
		
		$this->showFieldsTable();
	}
	
	function saveSnip(){
		global $wpdb;
		check_admin_referer( 'update-supplesnips');
		$d['snip_name'] = $_POST['sppl_snip_name'];
			
		if(!$this->checkName($d['snip_name'])){
			$this->message = 'Invalid snip name...use numbers, letters, and underscore only.';
			$this->msgclass= 'error';
			return false;
		}
		$d['auto_add'] = (int)$_POST['sppl_auto_add'];
		$d['snip'] = $_POST['sppl_snip'];
		$d['css'] = $_POST['sppl_css'];
		$d['lists'] = $_POST['sppl_lists'];
		
		if(get_magic_quotes_gpc()){
			$d['snip'] = stripslashes($d['snip']);
			$d['css'] = stripslashes($d['css']);
			$d['lists'] = stripslashes($d['lists']);
		}			
		
		
		if($this->snip_id == 0){
				
				$nametest = $wpdb->get_var($wpdb->prepare('SELECT snip_name FROM '
					.SUPPLESNIPSTABLE.' WHERE snip_name = %s',$d['snip_name']));
				
				if($nametest){
					$this->message = "<h3 style='color:red;'>Duplicate Snip name: ".$d['snip_name']. " - Snip not added.</h3>";
					return false;
				}
				
				
				if($wpdb->insert(SUPPLESNIPSTABLE,$d)){
					$insert_id = $wpdb->insert_id;
					$this->message =  "<b>Snip Added -> </b>".$d['snip_name'];
					return $insert_id;
				} else {
					$this->message = "<h3 style='color:red;'>FAILED...field failed to insert: </h3>".$d['field_name'];
				}
			}else{
				$where['snip_id'] = $this->snip_id;
				$wpdb->update( SUPPLESNIPSTABLE, $d, $where);
				$this->message = "<b>Snip updated:  ".$d['snip_name']."</b>";
			}
				
	}
	
	function checkName($text)
	{
		$regex = "/^([A-Za-z0-9_]+)$/";
		if (preg_match($regex, $text)) {
			return TRUE;
		} 
		else {
			return FALSE;
		}
	}
	
	
	/**
	 * Displays the Add/Edit form for HTML Snips
	 *
	 * Snips let you build complex HTML templates for 
	 * displaying your field data within a post
	 * 
	 */
	function showSnipsForm(){
		global $wpdb;
				
		$snipsDDL = $this->getSnipsDDL($this->snip_id);
		if($this->snip_id){
			$snipOptions = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.SUPPLESNIPSTABLE.' WHERE snip_id = %d',$this->snip_id), ARRAY_A);
			
			if($snipOptions){
				if(get_magic_quotes_gpc()){
					$snipOptions['snip'] = stripslashes($snipOptions['snip']);
					$snipOptions['css'] = stripslashes($snipOptions['css']);
					$snipOptions['lists'] = stripslashes($snipOptions['lists']);
				}			
			}
			
		}	
		
		?>
		<div class=wrap>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<?php bwbsppl_nonce_field('update-supplesnips'); ?>
		<h2>Supple Forms -> HTML Snips Editor</h2>
		
		<?php
			if($this->message){
				echo '<div id="message" class="'.$this->msgclass.'"><p>'.$this->message.'</p></div>';
			}
		?>
		
		<h3>Add/Edit HTML Snips</h3>
<table class="form-table"><tr>
<tr>
<th><input type="submit" name="saveSuppleSnip" class="button-primary" tabindex="20" value="<?php _e('Save Snip', 'suppleLang') ?>" /></th>
<td>&nbsp;</td>
</tr>

<th>Select Snip to edit:</th><td><?php echo $snipsDDL;?>&nbsp;<input type="submit" name="showSnipSettings" tabindex="100" value="<?php _e('Edit', 'suppleLang') ?>" /></td></tr>

<tr>
	<th>Snip name:</th>
	<td>
		<input type='text' name="sppl_snip_name" value='<?php echo $snipOptions['snip_name'];?>'/>
		<ol><li>Used in shortcodes to display snip: <span style='color:red;'>[supple snip='<?php if($snipOptions['snip_name']){echo $snipOptions['snip_name']; } else {
		echo 'my_snip';} ?>']</span></li>
		<li>Use letters, numbers, and underscore ( _ ) only</li></ol>
	</td>
</tr>
<tr>
	<th>Auto-add to posts:<br/><span style='font-size: 9px;'>Not implemented at this time.</span></th>
	<td>
		<input type="radio" name="sppl_auto_add" value="0" <?php if($snipOptions['auto_add'] == 0) echo 'checked'; ?> disabled> No auto-add<br/>
		<input type="radio" name="sppl_auto_add" value="1" <?php if($snipOptions['auto_add'] == 1) echo 'checked'; ?> disabled> Add before content<br/>
		<input type="radio" name="sppl_auto_add" value="2" <?php if($snipOptions['auto_add'] == 1) echo 'checked'; ?> disabled> Add to end of content<br/>
		Not implemented at this time...
	</td>
</tr>
<tr>
<th id='sppl_snip'>List of values:</th>
	<td>
		HTML snippet<br/>
		<textarea name="sppl_snip" cols="45" rows="6"><?php echo htmlentities($snipOptions['snip']);?></textarea>
		<br/>1) Use standard HTML to format your display<br/>2) Show field values with tags like: <span style='color:red;'>[my_field]</span>
	</td>
</tr>

<th id='sppl_css'>CSS:</th>
	<td>
		Enter as normal CSS for use with classes/IDs in your snips:<br/>
		<textarea name="sppl_css" cols="45" rows="4"><?php echo htmlentities($snipOptions['css']);?></textarea>
	</td>
</tr>
<tr>
	<th>Multi-value fields formats:</th>
	<td>
		<textarea name="sppl_lists" cols="15" rows="3"><?php echo htmlentities($snipOptions['lists']);?></textarea>
		<br/>Format multi-value fields as lists by entering formats from below. Place each format on a new line, and enter formats in order for each multi-value field used in snip. <br/>Enter only one format to use same for all multi-value fields.<h3>Formats:</h3><ul><li><b>ol</b> - will turn values for a field into an ordered list</li>
		<li><b>ul</b> - unordered list</li>
		<li><b>br</b> - new line</li>
		<li><b>", "</b> (without the quotation marks) - will separate items with comma and space </li>
		<li><b>any other delimiter</b> - only 'ol', 'ul', and 'br' will be transformed into tags</li>
		</ul>
		
	</td>
</tr>

<tr>
<th><input type="submit" name="saveSuppleSnip" class="button-primary" tabindex="20" value="<?php _e('Save Snip', 'suppleLang') ?>" /></th>
<td>&nbsp;
</td>
</tr>
</table>
</form>
<br/>
<?php
}
	
	function getSnipsDDL($selected_snip){
		
 		global $wpdb;
 		 
		$ret = "<option value='0'>&lt;new&gt;</value>";
		
		$query = $wpdb->get_results("SELECT snip_id, snip_name FROM "
			.SUPPLESNIPSTABLE." ORDER BY snip_name;");
		
		if($query){
			foreach($query as $row){
		
				if($selected_snip == $row->snip_id){$sel = "selected='selected'";}else{$sel = "";}
				$ret .= "<option value='".$row->snip_id."' ".$sel.">".$row->snip_name."</option>";
		
			}
		}
		$ret ="<select name='sppl_snip_id'>".$ret."</select>";
		return $ret;

	
	}
	
	function showFieldsTable(){
		$fldTable = $this->getTableOfFields();
		
		echo "<h2>Table of your fields for reference:</h2>Field values can be inserted into your snippet with a short code like [field_name].  Example:<p>&lt;div&gt;I live in [my_city].&lt;/div&gt;</p>Renders:  I live in St. Louis.";
		echo $fldTable;
	}
	
	//Get a table of the created fields
	function getTableOfFields()
	{
		global $wpdb;
		$sql = "SELECT * FROM ".SUPPLEFIELDSTABLE." ORDER BY form_id, seq";
		$query = $wpdb->get_results($wpdb->prepare($sql, $form_id));
		
		if($this->options['use_custom_fields'] == 0 ){
			$ct = '<th scope="col" >Generated</th>';
			$b = true;
		}
		
		if($query){
			foreach($query as $row)
			{
				if($b){
					$gen = $row->status == 1 ? "<span style='color:green;'>generated</span>" : "<span style='color:red;'>not generated</span>";
					$gen = "<td>".$gen."</td>";
					if(!$row->status){$this->ungeneratedfields++;}
				}
				$multi = $row->multi_val == 0 ? 'No' : 'Yes';
				$nbr = $row->numeric_field == 0 ? 'No' : 'Yes';
				$def = $row->default_val ? $row->default_val : '&nbsp;';
				$ret .= "<tr><td>".$row->seq
					." - <a href='admin.php?page=editSuppleFormFields&field_id="
					.$row->field_id."'>"
					.$row->field_name."</a></td>"
					."<td>".$row->label."</td>"
					."<td>".$this->getControlType($row->type)."</td>"
					."<td>".$nbr."</td>"
					."<td>".$multi."</td>"
					."<td>".$def."</td>"
					.$gen."
					</tr>";
			}
		
		}
		
		
		
		return '<table class="widefat" cellspacing="0" id="supple-fields-table">
		<thead>
		<tr>
			<th scope="col">Field name</th>
			<th scope="col">Label</th>
			<th scope="col">Type</th>
			<th scope="col">Nbr</th>
			<th scope="col">Multi-value</th>
			<th scope="col" >Default value</th>
			'.$ct.'
		</tr>
		</thead>'.$ret.'</table>';

	}
	
	function getControlType($type)
	{
		switch ($type) {
			case 0:
				return "Textbox";
				break;
			case 1:
				return "Multi-line";
				break;
			case 2:
				return "Dropdown List";
				break;
			case 3:
				return "Radio buttons";
				break;
			case 4:
				return "Checkboxes";
				break;
			case 5:
				return "Date Picker";
				break;
			case 6:
				return "Hidden";
				break;
		}
	}
	
	
}

if ( !function_exists('wp_nonce_field') ) {
        function bwbsppl_nonce_field($action = -1) { return; }
        $bwbsppl_plugin_nonce = -1;
} else {
        function bwbsppl_nonce_field($action = -1) { return wp_nonce_field($action); }
}
?>