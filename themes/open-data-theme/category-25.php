<?php get_header(); ?>
<?php 
     $posts = query_posts($query_string . '&orderby=title&order=asc&posts_per_page=50');
?>

<?php 
	// $cats = array(  
	// 				85	=> "Office of Parliament",
	// 				// 87	=> 	"Public Service Department",
	// 				// 90	=> "State Sector",
	// 				// 75	=> 	"Autonomous Crown Entity",
	// 				84	=> 	"Non Public Service Department",
	// 				86	=> 	"Other PFA 4th Schedule organisation",
					// 76	=> 	"City Council",
					// 80	=> 	"District Council",
					// 88	=> 	"Regional Council",
					// 91	=> 	"Territorial Authority",
					// 81	=> 	"District Health Board",
					// 82	=> 	"Education",
					// 77	=> 	"Conservation Sector Organisation",
					// 78	=> 	"Crown Agent",
					// 79	=> 	"Crown Research Institute",
					// 83	=> 	"Independent Crown entity",
					// 89	=> 	"State Owned Enterprise/Commercial Organisations",
					// 92	=> 	"Trust",
					// 93	=> 	"Wananga")
					
					// Central Government
					// >Public Service Department 
					// >Crown Agents, Autonomous Crown Entities, Crown Entitity Companies, Trusts (suggest merge these categories into one alpha list) 
					// >DHBs 
					// >Crown Research Institutes 
					// >Reserve Bank of New Zealand 
					// >Non Public Service Departments 
					// >Office of Parliament 
					// >Education Institutions and Wananga (suggest merge these categories) 
					// >State-Owned Enterprises
					// *Local Government*
					// >City and District Councils 
					// > Regional Councils and Territorial Authorities
?>

	<div id="wide_content">
		<h1 class="cat_header">List of Departments, Agencies, Crown Research Institutes, Councils and Other Government Organsations</h1>
		<p>Click through to see what datasets we have listed for each entity.</p>
		<h2 class="department">Central Government</h2>
		<h3 class="department">Public Service Department</h3>
		<?php output_department(87, $wpdb); ?>
		<h3 class="department">Crown Agents, Autonomous Crown Entities, Crown Entitity Companies, Trusts</h3>
		<?php output_department(78, $wpdb); ?>
		<?php output_department(75, $wpdb); ?>
		<?php output_department(83, $wpdb); ?>
		<?php output_department(92, $wpdb); ?>
		<h3 class="department">DHBs </h3>
		<?php output_department(81, $wpdb); ?>
		<h3 class="department">Crown Research Institutes </h3>
		<?php output_department(79, $wpdb); ?>
		<h3 class="department">Reserve Bank of New Zealand </h3>
		<?php output_department(90, $wpdb); ?>
		<h3 class="department">Non Public Service Departments</h3>
		<?php output_department(84, $wpdb); ?>
		<h3 class="department">Office of Parliament </h3>
		<?php output_department(85, $wpdb); ?>
		<h3 class="department">Education Institutions and Wananga </h3>
		<?php output_department(82, $wpdb); ?>
		<?php output_department(93, $wpdb); ?>
		<h3 class="department">State-Owned Enterprises</h3>
		<?php output_department(89, $wpdb); ?>
		<h2 class="department">Local Government</h2>
		<h3 class="department">City and District Councils</h3>
		<?php output_department(76, $wpdb); ?>
		<?php output_department(80, $wpdb); ?>
		<h3 class="department">Regional Councils and Territorial Authorities</h3>
		<?php output_department(88, $wpdb); ?>
		<?php output_department(91, $wpdb); ?>
		
	</div>

<?php get_footer(); ?>