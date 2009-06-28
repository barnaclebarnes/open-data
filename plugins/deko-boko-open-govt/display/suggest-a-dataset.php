<?php
// Use this file as a template for making your own custom contact form
?>

<?php
	function selected_attribute ($value, $tag) {
		if ($value == $tag) return "selected='selected' "; 
	}
	function output_option_group ($options, $selected_value) {
		foreach($options as $option)
			echo "<option value='" . $option . "'" . selected_attribute($selected_value, $option) . ">" . $option . "</option>";
	}

	
	function output_optional_text_field($field_name, $label, $size) {
		echo "<li>";
		echo "<label for='dekoboko_'" . $field_name . "'>" . $label . "</label><br />";
		echo  "<input type='text' name='dekoboko_optional[" . $field_name . "]' id='dekoboko_" . $field_name . "' value='" . $dekoboko_optional[$field_name] . "' size= '" . $size . "'/>\n";
		echo "</li>";
	}

?>
<form action="<?php echo get_permalink(); ?>" method="post" id="dekoboko_form">
<?php wp_nonce_field('dekoboko_nonce', 'dekoboko_nonce'); ?>

<?php
    // you can comment out or delete this "if" block if you don't want to
    // display the welcome message that's in your Deko Boko Settings.
    if ($dekoboko_options['welcome']) {
        echo "<p>" . $dekoboko_options['welcome'] . "</p>";
    }
?>

<p>
	If you know of another dataset that we can add to the catalogue then please fill out this form and let us know. We will add it to the database as soon as we can. If you work for a Government Department and would like to upload a bulk listing of datasets then please email glen [at] opengovt [dot] org [dot] nz.
</p>
	
<p>Fields marked with <span class="dekoboko_required">*</span> are required.</p>

<fieldset>
<ol>
<li>
    <label for="dekoboko_name">Your Name<span class="dekoboko_required">*</span></label><br />
    <input type="text" name="dekoboko_required[name]" id="dekoboko_name" value="<?php echo $dekoboko_required['name']; ?>" size="30" />
</li>

<li>
    <label for="dekoboko_email">Your Email<span class="dekoboko_required">*</span></label><br />
    <input type="text" name="dekoboko_required[email]" id="dekoboko_email" value="<?php echo $dekoboko_required['email']; ?>" size="30" />
</li>

<li>
    <label for="dekoboko_subject">Dataset Name*</label><br />
    <input type="text" name="dekoboko_optional[subject]" id="dekoboko_subject" value="<?php echo $dekoboko_optional['subject']; ?>" size="80" />
</li>

<li>
    <label for="dekoboko_department">Department/CRI/Local Body*</label><br />
    <input type="text" name="dekoboko_required[department]" id="dekoboko_department" value="<?php echo $dekoboko_required['department']; ?>" size="80" />
</li>
	
<li>
    <label for="dekoboko_message">Description of the dataset<span class="dekoboko_required">*</span></label><br />
    <textarea name="dekoboko_required[message]" cols="80" rows="10" id="dekoboko_message"><?php echo $dekoboko_required['message']; ?></textarea>
</li>

<li>
    <label for="dekoboko_update_frequency">How often is the dataset updated (daily/weekly/monthly/custom)?</label><br />
    <textarea name="dekoboko_optional[update_frequency]" cols="80" rows="3" id="dekoboko_update_optional"><?php echo $dekoboko_optional['update_frequency']; ?></textarea>
</li>


<li>
    <label for="dekoboko_free">Is the dataset free?*</label><br />
	<select name="dekoboko_required[free]" id="dekoboko_free">
		<?php 
			$options = array("Yes", "No");
			output_option_group($options, $dekoboko_required['free']);
		?>
	</select>
</li>

<li>
    <label for="dekoboko_instant_access">Can the dataset be downloaded instantly (i.e. No manual intervention)?*</label><br />
	<select name="dekoboko_required[instant_access]" id="dekoboko_instant_access">
		<?php 
			$options = array("Yes", "No");
			output_option_group($options, $dekoboko_required['instant_access']);
		?>
	</select>
</li>

<li>
    <label for="dekoboko_license">What license does the dataset have?*</label><br />
	<select name="dekoboko_required[license]" id="dekoboko_license">
		<?php 
			$options = array("Unknown", "No license", "Crown Copyright", "Creative Commons", "Restrictive");
			output_option_group($options, $dekoboko_required['license']);
		?>
	</select>
</li>

<?php output_optional_text_field("xls_url","Link to Excel File (or webpage containing the download/info)", 80) ?>
<?php output_optional_text_field("csv_url","Link to CSV File (or webpage containing the download/info)", 80) ?>
<?php output_optional_text_field("kml_url","Link to KML File (or webpage containing the download/info)", 80) ?>
<?php output_optional_text_field("geo_url","Link to file that can be read by GIS software (or webpage containing the download/info)", 80) ?>
<?php output_optional_text_field("api_url","Link to information on the API", 80) ?>
<?php output_optional_text_field("other_url","Link to other format file (or webpage containing the download/info)", 80) ?>

<?php output_optional_text_field("web_address","Website URL for more information", 80) ?>
<?php output_optional_text_field("contact_name","Who can users contact to find out more information?", 40) ?>
<?php output_optional_text_field("contact_phone","Phone", 20) ?>
<?php output_optional_text_field("contact_email","Email", 40) ?>

<li>
    <label for="dekoboko_cc_me">CC Me</label><br />
    <input type="checkbox" name="dekoboko_optional[cc_me]" id="dekoboko_cc_me" value="Y"<?php if ($dekoboko_optional['cc_me'] == 'Y') echo ' checked="checked"'; ?> />
    <span style="font-size: x-small;">Check this box to send a copy of your message to yourself.</span>
</li>

<li><label for="recaptcha_challenge_field">Are You Human?<span class="dekoboko_required">*</span><br />
    	<span style="font-size: x-small;">
			<a href="http://recaptcha.net/popuphelp/" onclick="window.open('http://recaptcha.net/popuphelp/','name','height=600,width=500'); return false;" title="Help">What's this?</a>
		</span>
	</label><br />
    <?php echo $recaptcha_html ?>
</li>
</ol>
</fieldset>

<fieldset id="dekoboko_end">
    <input type="submit" name="dekoboko_submit" id="dekoboko_submit" value="Send Message" />
</fieldset>
</form>

