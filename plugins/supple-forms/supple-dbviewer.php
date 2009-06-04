<?php
//Database Viewer for Supple Forms plugin


class SuppleDBViewer{
	
	var $options;
	var $message = false;
	
	//Constructor
	function SuppleDBViewer($_options){
		
		$this->options = $_options;
		echo "<div class=wrap><h2>Supple Forms -> Database Viewer</h2></div>";
		
		if($this->options['use_custom_fields'] ==1){
			$ret = "<h3>Custom Tables are not turned on in <a href='admin.php?page=supple-forms.php'>Form Settings</h3>";
		}else {
			echo "Viewing custom table: ".SUPPLETABLEPREFIX.$this->options['custom_tablename'];
			$ret = $this->getSuppleData(SUPPLETABLEPREFIX.$this->options['custom_tablename']);
		}
		echo $ret;
	
	}
	
	//Get a table of the created fields
	function getSuppleData($table)
	{
		global $wpdb;
		
		$sql = "SELECT ".$wpdb->posts.".post_title, "
			.$table.".* FROM ".$table." LEFT OUTER JOIN "
			.$wpdb->posts." ON ".$wpdb->posts.".ID = "
			.$table.".post_id ORDER BY id";
			
		$query = $wpdb->get_results($sql, ARRAY_A);
			
		if($query){
			$cnt = 0;
			foreach($wpdb->get_col_info("name") as $name){
				$cnt++;
				$rethd .= "<th scope='col'>".$name."</th>";			
				if($name == 'post_id'){
					$pnum = $cnt;
				}
			}
		
			foreach($query as $row)
			{
				$ret .= "<tr>";
				$cnt = 0;
				foreach($row as $fld){
					$cnt++;
					if($cnt == $pnum || $cnt == 1){
						$fld = "<a href='post.php?action=edit&post=".(int)$row['post_id']."'>".$fld."</a>";
					}
					$ret .= "<td>".$fld."</td>";
				}
				$ret .= "</tr>";
			}
			
			
			$ret = "<table class='widefat' cellspacing='0' id='supple-forms-data'>
			<thead>
				<tr>".$rethd."</tr>
			</thead>
			".$ret."
			</table>";
			
			
		}else {
			$ret = "<h3>Table either does not exist, or contains no fields.</h3>";
		}
		
		return $ret;			
		
		
	}
	
}
?>
