<?php
//Admin Pages for Supple Forms plugin


class SuppleAdmin{
	
	var $options;
	var $message = false;
	var $msgclass = "updated fade";
	var $ungeneratedfields=0;
	var $field_id;
	
	//Constructor
	function SuppleAdmin($_options){		
		global $wpdb;
		
		$this->options = $_options;
		
		if(isset($_REQUEST['sppl_field_id'])){
			$this->field_id = (int)$_REQUEST['sppl_field_id'];
			
		} else { 
			if(isset($_GET['field_id'])){
				$this->field_id = (int)$_GET['field_id'];
			} else { 
				$this->field_id = 0; 
			}
		}
		
		//If form_id is missing and we have a field_id, get form_id from field
		if($this->field_id && !$options['form_id']){				
			$form_id = $wpdb->get_var("SELECT form_id FROM "
				.SUPPLEFIELDSTABLE." WHERE field_id = ".$this->field_id);
		}
		
		//Save options if Options form submitted
		if(isset($_POST['saveSuppleFormSettings'])){
			$this->saveFormOptions($this->options);
		}
		
		//Save fields
		if(isset($_POST['saveSuppleField'])){
			$this->saveFields($this->options);
		}
		
		//Delete field
		if(isset($_POST['deleteSuppleField'])){
			$this->deleteField($this->options);
		}
		
		//Generate Custom Table
		if(isset($_POST['generateSuppleTable'])){
			$this->generateCustomTable($this->options);
		}
	}
	
	
	//	Generate the CUSTOM TABLE
	//	- will update any changes to fields
	//  - does not delete fields that are dropped from field list
	//	- will create a new table if you change the Custom Table Name
	//	- does not drop orphaned tables or delete their contents
	function generateCustomTable($options)
	{
		//Make sure user wants to do custom table
		if($options['use_custom_fields'] == 1){
			$this->message .= "<p>Table not generated.  Custom fields is selected in <a href='admin.php?page=supple-forms.php'>General Settings page</a>.</p>";
			$this->msgclass = 'error';
			return false;
		}
		
		//Make sure a custom table name was specified
		if(!trim($options['custom_tablename'])){
			$this->message .= "<p>Table not generated.  Custom fields is selected in <a href='admin.php?page=supple-forms.php'>General Settings page</a>.</p>";
			$this->msgclass = 'error';
			return false;
		}
		
		//Create or update the Custom Table
		global $wpdb;
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');  // the magic file containing dbDelta()
		
		//Get the field data
		$results = $wpdb->get_results("SELECT * FROM ".SUPPLEFIELDSTABLE." WHERE form_id = ".(int)$options['form_id']." ORDER BY seq;");
		
		//Error out if there are no fields
		if(!$results || $wpdb->num_rows == 0){
			$this->message .= "<p >Table generation failed.  No fields to create.</p>";
			$this->msgclass = 'error';
			return false;
		}
		
		//Here's our Custom Table Name...Supple Forms prepends with $wpdb->prefix + sppl_
		$table_name = SUPPLETABLEPREFIX.$options['custom_tablename'];
		
		/*
		//Drop the post_id index if already exists...else it will keep duplicating it
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
			$sql = "ALTER TABLE ".$table_name." DROP INDEX post_id";
			$wpdb->query($sql);
		}
		*/
		
		//SQL for table creation & updating
		$sql = "CREATE TABLE " . $table_name . " (
			id INT(11) NOT NULL AUTO_INCREMENT,
			post_id INT(11) NOT NULL, 
			supple_status TINYINT(1) NOT NULL default '0' ";
		
		foreach($results as $row)
		{
			if(!$row->multi_val){
				$ret = $this->getFieldSQL($row);
				if($ret){
					$sql .= $ret;
				}
			} else {
				//we're going to display this list so user is aware that he had multi value fields that didn't generate
				$multifields[] = $row->field_name;
			}
		}
		
		if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty($wpdb->charset) )
					$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				if ( ! empty($wpdb->collate) )
					$charset_collate .= " COLLATE $wpdb->collate";
		}	
		
		$sql .= " ,
				PRIMARY KEY  (id)
				)  $charset_collate;";
		
		//Here we go....make the table.....NOW!
		$ret = dbDelta($sql);
		
		$this->message = "<p>Table created: ".$table_name;
		//$this->message .= "<p>$sql</p>";
		if(count($multifields) > 0){
			$m = implode("</b>, <b>",$multifields);
			$this->message .= "<p>Fields set to multiple values (not included in table): <b>".$m."</b></p>";
		}
		
		
		//Set fields to show that they have been generated
		$wpdb->query("UPDATE ".SUPPLEFIELDSTABLE." SET status = 1 WHERE form_id = ".(int)$options['form_id']);
		return true;
	
	}
	
	//return the sql for a field for the table generator
	function getFieldSQL($row){
		switch ($row->type) {
			case 0 :
				if($row->numeric_field == 1){
					$type = "DOUBLE";
				} else {
					$type = "VARCHAR(255)";
				}
				break;
			case 1 :
				$type = "MEDIUMTEXT";
				break;
			case 2 :
				$type = "VARCHAR(255)";
				break;
			case 3 :
				$type = "VARCHAR(255)";
				break;
			case 4 :
				$type = "VARCHAR(255)";
				break;
			case 5 :
				$type = "DATETIME";
				break;
			case 6 :
				$type = "TEXT";
				break;
			
		}
		
		$ret = ",
				".$row->field_name." ".$type." ";
		
		return $ret;
	}
	
	function showMetaBoxSettings()
	{
		//Display the Admin Page form
		$this->printAdminPage($this->options);
	}
	
	function showFieldEditor()
	{
		//Display the Field Editor form
		$this->printEditFieldsPage($this->options, $this->field_id);	
	}
	
	//Returns the Supple Forms - Form Settings
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
	
	function checkDuplicateTableName($tablename)
	{
		global $wpdb;
		
		if($wpdb->get_var($wpdb->prepare('SELECT custom_tablename FROM '.SUPPLEFORMSTABLE
			.' WHERE custom_tablename = %s',$tablename))){
			return false;}
		return true;
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
	
	//Checks to see if we're saving options
	function saveFormOptions(&$options){
	
		global $wpdb;
		
		//This section Saves the overall Supple Forms defaults
			check_admin_referer( 'update-suppleforms');
			
				$data['form_id'] = 1;
				$data['form_title'] = $_POST['sppl_form_title'];
				$data['placement'] = (int)$_POST['sppl_placement'];
				$data['use_custom_fields'] = (int)($_POST['sppl_use_custom_fields']);

				if($data['use_custom_fields'] == 0 ){
					$data['custom_tablename'] = $_POST['sppl_custom_tablename'];
					if(!$this->checkName($data['custom_tablename'])){
						$this->message .= 
							"<p> Update failed. Invalid Custom Table Name: ". $data['custom_tablename']."</p>";
						$this->msgclass = 'error';
						$this->options = $this->getFormSettings($options['form_id']);
						return false;
					}
					if(!$this->checkDuplicateTableName($tablename)){
						$this->message .= "<p>Table name already exists: "
							.$tablename. ". Update failed.</p>";
						$this->msgclass = 'error';
						$this->options = $this->getFormSettings($options['form_id']);
						return false;
					}
				}
				
				$data['post_related'] = isset($_POST['sppl_post_related']) ? 1 : 0;
				$data['write_page'] = isset($_POST['sppl_write_page']) ? 1 : 0;
				$data['hide_wp_customfields'] = isset($_POST['sppl_hide_wp_customfields']) ?
					1 : 0;
								

			//Update the Defaults
			$where = array('form_id' => $options['form_id']);
			$ret = $wpdb->update(SUPPLEFORMSTABLE, $data, $where);
			
			if(!$ret){
				if($wpdb->insert(SUPPLEFORMSTABLE, $data)){
					$options['form_id'] = $wpdb->insert_id;
				} else {
					if(!$wpdb->get_var("SELECT form_id FROM "
						.SUPPLEFORMSTABLE." WHERE form_id = "
						.$options['form_id'])){
						$this->message .= 
							"<p style='margin-bottom: 11px; padding: 0;'>
								Inserting form data failed.</p>";
						$this->msgclass = 'error';
					}
					return false;
				}
			}
			$this->options = $this->getFormSettings($options['form_id']);
			$this->message ="Supple Forms settings updated...";
	}
	
	function deleteField($options)
	{
		global $wpdb;
		
		//This section deletes Field settings
		check_admin_referer( 'update-suppleforms');
		
		$ret = $wpdb->query("DELETE FROM ".SUPPLEFIELDSTABLE." WHERE field_id="
			.(int)$this->field_id." LIMIT 1" );
		if($ret){$this->message = "Field deleted...";}
		
		return;
	}
	
	
	function saveFields($options)
	{
		global $wpdb;
		
		//This section saves Field settings
			check_admin_referer( 'update-suppleforms');
			$field_id = (int)$_POST['sppl_field_id'];
			$d['field_name'] = $_POST['sppl_field_name'];
			
			if(!$this->checkName($d['field_name'])){
				$this->message .= "<p>Invalid field name: <b>" .htmlentities($d['field_name'], ENT_QUOTES)."</b>.  Use only letters, numbers, and underscore (_).</p>";
				$this->msgclass = 'error';
				return false;				
			}
			
			$d['label'] = $_POST['sppl_label'];
			$d['type'] = (int)$_POST['sppl_type'];
			$d['numeric_field'] = isset($_POST['sppl_numeric_field']) ? 1 : 0;
			$d['multi_val'] = isset($_POST['sppl_multi_val']) ? 1 : 0;
			
			//Only allow multiple values for Textboxes, checkboxes, and Date Pickers
			if($d['multi_val'] == 1 ){
				if($d['type'] == 1 || $d['type'] == 2 || $d['type'] == 3 ){
					$d['multi_val'] = 0;
				}
			} else {
				if($d['type'] == 4){$d['multi_val'] = 1;}
			}
			
			if($d['type'] == 0 || $d['type'] == 1){
				$d['html_filter'] = (int)$_POST['sppl_html_filter'];
			} else { $d['html_filter'] = 0; }
			
			$d['default_val'] = $_POST['sppl_default_val'];
			$d['seq'] = (int)(trim($_POST['sppl_seq']));
			$d['form_id'] = $options['form_id'];
			$d['status'] = 0;
			
			
			if($field_id == 0){
				
				$nametest = $wpdb->get_var($wpdb->prepare('SELECT field_name FROM '
					.SUPPLEFIELDSTABLE.' WHERE field_name = %s AND form_id = %d',$d['field_name'], $d['form_id']));
				
				if($nametest){
					$this->message = "<h3 style='color:red;'>Duplicate field name: ".$d['field_name']. " - Field not added.</h3>";
					return false;
				}
				
				
				if($wpdb->insert(SUPPLEFIELDSTABLE,$d)){
					$insert_id = $wpdb->insert_id;
					$this->message =  "<b>Field Added -> </b>".$d['field_name'];
				} else {
					$this->message = "<h3 style='color:red;'>FAILED...field failed to insert: </h3>".$d['field_name'];
				}
			}else{
				$where['field_id'] = $field_id;
				$wpdb->update( SUPPLEFIELDSTABLE, $d, $where);
				$this->message = "<b>Field updated:  ".$d['field_name']."</b>";
				
			}
			
		switch ($d['type']){
				case 0:
					break;
				case 1:
					break;
				case 5:
					break;
				case 6:
					break;
				default :
					if($insert_id){
						$ret = $this->insertListValues($insert_id);
					}else{
						$ret = $this->insertListValues($field_id);
					}
					if($ret){
						$this->message .= " &nbsp;| &nbsp;".$ret." list values added.";
					} else {
						
						$this->message .= "<h5 style='color: red;'>List values missing.  Radio buttons, Checkboxes, and Dropdowns require list values.  Please add.</h5>";
					}
					break;
		}
		
	}
	
	
	//Insert List Values for multiple selection controls:  DropDown List, checkboxes, radio buttons
	function insertListValues($field_id)
	{
		$field_id = (int)$field_id;
		if(!$field_id){ return false; }
				
		//Get the list of values from POST
		$val = trim($_POST['sppl_valuelist']);
		if(!$val){ return false;}
		
		//Replace funky  line breaks to \n
		$val = str_replace("\r\n","\n",$val);
		$val = str_replace("\r","\n",$val);
		
		//Explode into an array of rows based on \n
		$rows = explode("\n", $val);
		
		//Delete pre-existing values for field_id
		global $wpdb;
		$sql = "DELETE FROM " . SUPPLELOOKUPTABLE 
			." WHERE field_id = ".$field_id;
		$wpdb->query($sql);
		
		//Walk through rows and insert values
		foreach($rows as $row)
		{
			if(trim($row)){
				//Get Value and Label...create label from value if not exists...comma separated
				$s = explode(",", $row);
				if(count($s) < 2){
					$s[1] = $s[0];
				}
				
				$data = array( 'field_id' => $field_id,
					'value' => htmlentities(trim($s[0]), ENT_QUOTES),
					'label' => htmlentities(trim($s[1]), ENT_QUOTES),
					'seq' => $icnt++
					);
				$inserts += $wpdb->insert( SUPPLELOOKUPTABLE, $data);
			}
		}
		return $inserts;
	}
	
	//Get the list values for multiple selection controls to disply in the Field Edit screen
	function getListValuesForEditor($field_id)
	{
		global $wpdb;
		;
		$sql = $wpdb->prepare("SELECT value, label FROM ".SUPPLELOOKUPTABLE." WHERE field_id = %d ORDER BY seq", $field_id);
		$query = $wpdb->get_results($sql,ARRAY_A);
		
		if( $query && $wpdb->num_rows > 0){
			foreach($query as $row)
			{
				$ret[] = implode(', ', $row);
			}
			$ret = implode("\n",$ret);
			return $ret;
		}
		return false;
		
	}
	
	
	// ***************************  ADD / EDIT FIELDS   ****************************
	
	//Disply the Add/Edit Fields Page
	function printEditFieldsPage($options, $field_id){
		global $wpdb;
				
		$fieldsDDL = $this->getFieldsDDL($options['form_id'], $field_id);
		if($field_id){
			$fieldOptions = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.SUPPLEFIELDSTABLE.' WHERE field_id = %d',$field_id), ARRAY_A);
			
			$multivalues = trim($this->getListValuesForEditor($field_id));
			
			//Alert the user if they are doing a checkbox, radio, or dropdown and don't have any values saved.
			switch ($fieldOptions['type']){
				case 0: break; case 1: break; case 5: break; case 6: break;
				default :
					if(!$multivalues)
					{
						if(!strstr($this->message,"List values missing")){
							$this->message .= "<h5 style='color: red;'>List values missing.  Radio buttons, Checkboxes, and Dropdowns require list values.  Please add.</h5>";
						}
					}
					break;
			}
		}	
		
		?>
		<div class=wrap>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<?php bwbsppl_nonce_field('update-suppleforms'); ?>
		<h2>Supple Forms -> Add/Edit Fields</h2>
		
		<?php 
			$fieldsTable = $this->getTableOfFields($options['form_id']);
			
			if($this->ungeneratedfields)
			{$this->message .= "<p style='color:red; margin-bottom: 10px;'>Custom table may be out of date.  Generate when finished adding/editing fields.</p>";}
			
			if($this->message){
				echo '<div id="message" class="'.$this->msgclass.'"><p>'.$this->message.'</p></div>';
			}
		?>
		<h3>Add/Edit Fields - <?php echo $options['form_id'] ? "Form: '".$options['form_title']."'" : '<em style="color: red;">No form selected</em>';?></h3>
<table class="form-table"><tr>

<tr style='display:none;'><th>Select form:</th>
	<td>
		<?php echo $this->getFormsDDL($options['form_id']);?>&nbsp;<input type="submit" name="showFieldSettings" tabindex="100" value="<?php _e('Choose', 'suppleLang') ?>" />
	</td>
</tr>

<tr>
<th><input type="submit" name="saveSuppleField" class="button-primary" tabindex="20" value="<?php _e('Save Field', 'suppleLang') ?>" /></th>
<td>
	<?php if($this->options['use_custom_fields'] == 0){
		?>
	<input type="submit" onclick='return confirmGenerateSpplTable();' name="generateSuppleTable" class="button-primary" tabindex="30" value="<?php _e('Generate Table', 'suppleLang') ?>" />
	<em>After all fields are added/changed, generate custom table.</em>
	<?php } else { echo "&nbsp;";} ?>
</td>
</tr>
<tr>
<th>Select field to edit:</th><td><?php echo $fieldsDDL;?>&nbsp;<input type="submit" name="showFieldSettings" tabindex="100" value="<?php _e('Edit', 'suppleLang') ?>" />
<input type="submit" name="deleteSuppleField" onclick='return suppleConfirmDeleteField();' value="<?php _e('Delete', 'suppleLang') ?>" />

</td></tr>

<tr>
	<th>Database field name:</th>
	<td>
		<input type='text' name="sppl_field_name" value='<?php echo $fieldOptions['field_name'];?>'/>
		<br/>Use letters, numbers, and underscore ( _ ) only
	</td>
</tr>
<tr>
	<th>Label:</th>
	<td>
		<input type='text' name="sppl_label" value='<?php echo $fieldOptions['label'];?>'/>
	</td>
</tr>
<tr>
<th>Order:</th>
	<td>
			<?php 
				$nextseq = $this->getNextSeq($options['form_id']);
				if( $fieldOptions['seq'] === null)
				{$seq = $nextseq;}else{$seq = (int)$fieldOptions['seq'];}
			
			?>
			<input type='text' name="sppl_seq" value='<?php echo $seq; ?>'/>
			Next sequence #: <?php echo $nextseq; ?>
	</td>
</tr>
<tr>
	<th>Type:</th>
	<td>
			<input type="radio" name="sppl_type" value="0" <?php if($fieldOptions['type'] == 0) echo 'checked'; ?>>Textbox &nbsp;-&gt;&nbsp; Numeric? <input type="checkbox" name="sppl_numeric_field" <?php if($fieldOptions['numeric_field'] == 1) echo 'checked'; ?>><br/>
			<input type="radio" name="sppl_type" value="1" <?php if($fieldOptions['type'] == 1) echo 'checked'; ?>>Multi-line Textbox<br/>
			<input type="radio" name="sppl_type" value="2" <?php if($fieldOptions['type'] == 2) echo 'checked'; ?>>Dropdown List<br/>
			<input type="radio" name="sppl_type" value="3" <?php if($fieldOptions['type'] == 3) echo 'checked'; ?>>Radio Buttons<br/>
			<input type="radio" name="sppl_type" value="4" <?php if($fieldOptions['type'] == 4) echo 'checked'; ?>>Checkboxes<br/>
			<input type="radio" name="sppl_type" value="5" <?php if($fieldOptions['type'] == 5) echo 'checked'; ?>>Date Picker  (uses <a target='_blank' href='http://docs.jquery.com/UI/Datepicker'>jQuery UI DatePicker</a>)<br/>
		<?php 
		//Hidden fields not implemented
		/*	<input type="radio" name="sppl_type" value="6" <?php if($fieldOptions['type'] == 6) echo 'checked'; ?>>Hidden<br/> */ 		
		?>
	</td>
</tr>

<tr>
	<th>Allow multiple values:</th>
	<td>
		<input type="checkbox" name="sppl_multi_val" <?php if($fieldOptions['multi_val'] == 1) echo 'checked'; ?>>
		<br/><span style='font-size: 9px; line-height: 12px;'>Fields allowing multiple values will use <b>WP Custom Fields</b><br/>even if Custom Table is selected in 'Where to store data'
		<br/>in the <a href='admin.php?page=supple-forms.php'>General Settings</a> page.  Other fields will use
		<br/><b>Custom Table as directed.<br/><br/>Only available for: Textboxes, Checkboxes, & Dates</b></span>
	</td>
</tr>

<tr>
	<th>HTML filtering:</th>
	<td>
		<input type="radio" name="sppl_html_filter" value="0" <?php if($fieldOptions['html_filter'] == 0) echo 'checked'; ?>>Filter all html<br/>
		<input type="radio" name="sppl_html_filter" value="1" <?php if($fieldOptions['html_filter'] == 1) echo 'checked'; ?>>Allow formatting tags (b, strong, em, code)<br/>
		<input type="radio" name="sppl_html_filter" value="2" <?php if($fieldOptions['html_filter'] == 2) echo 'checked'; ?>>Allow formatting & links & lists<br/>
		<input type="radio" name="sppl_html_filter" value="3" <?php if($fieldOptions['html_filter'] == 3) echo 'checked'; ?>>No filtering<br/>
		<span>Uses WordPress HTML filtering functionality (wp_kses)</span>
	</td>
</tr>
<tr>
<th>Default value:</th>
	<td>
			<input type='text' name="sppl_default_val" value='<?php echo $fieldOptions['default_val'];?>'/>
	</td>
</tr>
<tr>
<th id='sppl_valuelist'>List of values:</th>
	<td>
		Enter as:  <em>value</em><b>,</b> <em>display label</em> [new line (\n)]<br/>
		<textarea name="sppl_valuelist" cols="35" rows="4"><?php echo htmlentities($multivalues);?></textarea>
		<br/>Required for: checkboxes, radio buttons, and dropdown lists
	</td>
</tr>
<tr>
<th><input type="submit" name="saveSuppleField" class="button-primary" tabindex="20" value="<?php _e('Save Field', 'suppleLang') ?>" /></th>
<td>
	<?php if($this->options['use_custom_fields'] == 0){
		?>
	<input type="submit" onclick='return confirmGenerateSpplTable();' name="generateSuppleTable" class="button-primary" tabindex="30" value="<?php _e('Generate Table', 'suppleLang') ?>" />
	<em>After all fields are added/changed, generate custom table.</em>
	<?php } else { echo "&nbsp;";} ?>
</td>
</tr>
</table>
</form>
<br/>
<?php

if($fieldsTable){			
	echo $fieldsTable;
} else {
	echo "<h3>No fields added yet...</h3>";
}

?>
 </div>
					<?php	
	}	//End the function for printing out the Field Editor Form

	//Get a table of the created fields
	function getTableOfFields($form_id)
	{
		global $wpdb;
		$sql = "SELECT * FROM ".SUPPLEFIELDSTABLE." WHERE form_id = %d ORDER BY seq";
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
	
	

	//Disply the Admin Options Page
	function printAdminPage($options){
		global $wpdb;
				
		?>
		<div class=wrap>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<?php bwbsppl_nonce_field('update-suppleforms'); ?>
		<h2>Supple Forms -> Form Settings</h2>
		<?php 
			if($this->message){
				echo '<div id="message" class="'.$this->msgclass.'"><p>'.$this->message.'</p></div>';
			}
		?>
		<h3>Form Settings</h3>
		<table class="form-table">
			<tr><th><input type="submit" name="saveSuppleFormSettings" class="button-primary" value="<?php _e('Save Form', 'suppleLang') ?>" /></th><td><?php 
				if($options['form_id']){echo "<a href='admin.php?page=editSuppleFormFields&sppl_form_id="
				.$options['form_id']."'>Add/Edit Fields</a>";} else { echo '&nbsp;';}?></td></tr>
			<tr style='display:none;'>
				<th>Select form to edit:</th>
				<td>
					<?php echo $this->getFormsDDL($options['form_id']);?>&nbsp;<input type="submit" name="showFieldSettings" tabindex="100" value="<?php _e('Edit', 'suppleLang') ?>" />
				</td>
			</tr>
			<tr>
				<th>Form Title:</th>
				<td>
					<input type='text' name="sppl_form_title" value='<?php echo $options['form_title'];?>'/>
				</td>
			</tr>
			<tr style='display:none;'>
				<th>Post related form:</th>
				<td>
					<input type="checkbox" name="sppl_post_related" <?php if($options['post_related'] == 1) echo 'checked'; ?>> (If selected, post IDs will be stored with records)
				</td>
			</tr>
			<tr style='display:none;'>
				<th>Show on Write Post page:</th>
				<td>
					<input type="checkbox" name="sppl_write_page" <?php if($options['write_page'] == 1) echo 'checked'; ?>> (Allows editable forms in blog pages, not just Write Post)
				</td>
			</tr>
			<tr>
				<th>Hide WP custom fields:</th>
				<td>
					<input type="checkbox" name="sppl_hide_wp_customfields" <?php if($options['hide_wp_customfields'] == 1) echo 'checked'; ?>> (Remove Supple Forms custom fields from the WP custom fields edit box)
				</td>
			</tr>			
			<tr>
				<th>Placement on Write Post page:</th>
				<td>
					<select name="sppl_placement">
						<option value="0" <?php if($options['placement'] == 0) echo 'selected=selected'; ?>>After Post Editor</option>
						<option value="1" <?php if($options['placement'] == 1) echo 'selected=selected'; ?>>After Post Title</option>
						<option value="2" <?php if($options['placement'] == 2) echo 'selected=selected'; ?>>At Bottom</option>
					</select>
				</td>
			</tr>
			<tr>
				<th>Where to Store Data:<br/><span style='font-size: 9px;'>Custom Fields or <b>Custom Table<b/></span></th>
				<td>
					<input type="radio" name="sppl_use_custom_fields" value="1" <?php if($options['use_custom_fields'] == 1) echo 'checked'; ?>> WP Custom Fields<br/>
					<input type="radio" name="sppl_use_custom_fields" value="0" <?php if($options['use_custom_fields'] == 0) echo 'checked'; ?>> Custom Table<br/>
					WP Custom Fields will always be used for<br/>
					for fields that allow multiple entries.
				</td>
			</tr>
			<tr>
				<th>Custom Table Name:</th>
				<td>
					<input type='text' name="sppl_custom_tablename" value='<?php echo $options['custom_tablename'];?>'/>
					<br/>- Only used if 'Custom Table' is selected above.<br/>
					- Supple Forms will prepend '<?php echo $wpdb->prefix;?>supple_' to table name.
				<?php
				if($options['use_custom_fields'] == 1){
					echo "<br/>- <span style='color:red;'>Custom Table not selected.</span>  Will not be used.";
				}else {
				if($options['custom_tablename']){ ?>
					<br/><br/>
					Custom table name (use for sql calls in your code):
					<br/> <span style='color: red;'><?php echo $wpdb->prefix . 'supple_'.$options['custom_tablename'];?></span>
				<?php } else {
						echo "<h3><span style='color:red;'>No custom table name given.</span></h3>";
					}
				}
				 ?>
				</td>
			</tr>
			<tr>
				<th>
			<input type="submit" name="saveSuppleFormSettings" class="button-primary" value="<?php 
				_e('Save Form', 'suppleLang') ?>" /></th>
			<td><?php 
				if($options['form_id']){echo "<a href='admin.php?page=editSuppleFormFields&sppl_form_id="
				.$options['form_id']."'>Add/Edit Fields</a>";} else { echo '&nbsp;';}?></td>
			</tr>
		</table>
</form>
</div>
<?php

}
	

	function getNextSeq($form_id)
	{
		global $wpdb;
		$sql = "SELECT MAX(seq) FROM ".SUPPLEFIELDSTABLE." WHERE form_id = ".(int)$form_id;
		$var = $wpdb->get_var($sql);
		$var++;
		return $var;
	}

	
	//Returns markup for a DropDown List of existing fields
	function getFieldsDDL($form_id, $selectedField = 0)
 	{
 		global $wpdb;
 		 
		$ret = "<option value='0'>&lt;new&gt;</value>";
		
		$query = $wpdb->get_results("SELECT field_id, field_name, seq FROM "
			.SUPPLEFIELDSTABLE." WHERE status > -1 AND form_id = ".(int)$form_id." ORDER BY seq;");
		
		if($query){
			foreach($query as $row){
		
				if($selectedField == $row->field_id){$sel = "selected='selected'";}else{$sel = "";}
				$ret .= "<option value='".$row->field_id."' ".$sel.">".$row->field_name." (".$row->seq.")</option>";
		
			}
		}
		$ret ="<select id='supple_fieldDropDown' name='sppl_field_id'>".$ret."</select>";
		return $ret;
	}
	
	function getFormsDDL($selectedForm = 0)
	{
		global $wpdb;
 		 
		$ret = "<option value='0'>&lt;new&gt;</value>";
		
		$query = $wpdb->get_results("SELECT form_id, form_title, seq FROM "
			.SUPPLEFORMSTABLE." WHERE status > -1 ORDER BY seq;");
		
		if($query){
			foreach($query as $row){
		
				if($selectedForm == $row->form_id){$sel = "selected='selected'";}else{$sel = "";}
				$ret .= "<option value='".$row->form_id."' ".$sel.">".$row->form_title."</option>";
		
			}
		}
		$ret ="<select name='sppl_form_id'>".$ret."</select>";
		return $ret;
		
	}
	
}  //closes out the class

if ( !function_exists('wp_nonce_field') ) {
        function bwbsppl_nonce_field($action = -1) { return; }
        $bwbsppl_plugin_nonce = -1;
} else {
        function bwbsppl_nonce_field($action = -1) { return wp_nonce_field($action); }
}


?>