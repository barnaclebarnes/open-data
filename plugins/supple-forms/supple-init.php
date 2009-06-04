<?php
//Called from init

class SuppleFormsInit{
	function SuppleFormsInit()
	{
		delete_option('supple_install_error');
		
		//Create the Tables if not exists
		global $wpdb;
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty($wpdb->charset) )
					$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				if ( ! empty($wpdb->collate) )
					$charset_collate .= " COLLATE $wpdb->collate";
			}		
				
			//Create the Fields table
			$sql = "CREATE TABLE " . $wpdb->prefix."sppl_fields (
				field_id INT(11) NOT NULL AUTO_INCREMENT,
				form_id INT(4) NOT NULL default '0',
				field_name VARCHAR(50) ,
				label VARCHAR(255) ,
				type INT(4) ,
				numeric_field TINYINT(1) NOT NULL default '0',
				multi_val TINYINT(1) NOT NULL,
				default_val varchar(255),
				html_filter TINYINT(1),
				seq INT(4) ,
				status TINYINT(1) NOT NULL ,
				PRIMARY KEY  (field_id)
				)  $charset_collate;";
			
			dbDelta($sql);
			
			//Create the Supple Lookup Table
			$sql = "CREATE TABLE " . $wpdb->prefix."sppl_lookup (
				id INT(11) NOT NULL AUTO_INCREMENT,
				field_id INT(4) ,
				value VARCHAR(255) ,
				label VARCHAR(255) ,
				seq INT(4) ,
				PRIMARY KEY   (id)
				)  $charset_collate;";
			dbDelta($sql);
			
			/* Create the Supple HTML Snips table
			* HTML snips are a form template that lets you
			* create a predefined HTML snippet with shortcodes to display
			* fields as a group...an example would be an address box
			* that displays address, city, state, and zip in an address format
			*/
			$sql = "CREATE TABLE " . $wpdb->prefix."sppl_snips (
				snip_id INT(11) NOT NULL AUTO_INCREMENT,
				snip_name VARCHAR(30) ,
				auto_add TINYINT NOT NULL default '0',
				snip TEXT ,
				css TEXT ,
				lists VARCHAR(255) ,
				PRIMARY KEY   (snip_id)
				)  $charset_collate;";
			dbDelta($sql);
			
			//Create the Forms table
			$sql = "CREATE TABLE " . $wpdb->prefix."sppl_forms (
				form_id INT(4) NOT NULL AUTO_INCREMENT,
				form_title VARCHAR(250) ,
				placement TINYINT(1) NOT NULL default '0' ,
				use_custom_fields TINYINT(1) NOT NULL default '1',
				custom_tablename VARCHAR(30) ,
				post_related TINYINT(1) NOT NULL default '1',
				write_page INT(4) NOT NULL default '1',
				hide_wp_customfields TINYINT(1) NOT NULL default '1',
				seq INT(4) ,
				status TINYINT(1) NOT NULL default '1',
				PRIMARY KEY  (form_id)
				)  $charset_collate;";
			
			dbDelta($sql);
	}
}
?>