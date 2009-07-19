<?php get_header(); ?>
<?php 
     $posts = query_posts($query_string . '&orderby=title&order=asc&posts_per_page=50');
?>

<?php 
	$cats = array(	85	=> "Office of Parliament",
					87	=> 	"Public Service Department",
					90	=> "State Sector",
					75	=> 	"Autonomous Crown Entity",
					84	=> 	"Non Public Service Department",
					86	=> 	"Other PFA 4th Schedule organisation",
					76	=> 	"City Council",
					80	=> 	"District Council",
					88	=> 	"Regional Council",
					91	=> 	"Territorial Authority",
					81	=> 	"District Health Board",
					82	=> 	"Education",
					77	=> 	"Conservation Sector Organisation",
					78	=> 	"Crown Agent",
					79	=> 	"Crown Research Institute",
					83	=> 	"Independent Crown entity",
					89	=> 	"State Owned Enterprise/Commercial Organisations",
					92	=> 	"Trust",
					93	=> 	"Wananga")
?>

	<div id="wide_content">
		<h1 class="cat_header">List of Departments, Agencies, Crown Research Institutes, Councils and Other Government Organsations</h1>
		<p>Click through to see what datasets we have listed for each entity.</p>
	  	<?php foreach ($cats as $cat_id => $title) :  ?>
	  		<?php
				$sql = 		"select p.ID, p.post_title, sd.department, tr.term_taxonomy_id, ds_count.num_sets" .
											" from (" . $wpdb->prefix . "posts as p, " .
											$wpdb->prefix . "supple_dataset as sd, " .
											$wpdb->prefix . "term_relationships as tr)" .
											" 	left outer join " . 
											" (select 	sd.department, count(sd.department) as num_sets" .
											" from " . $wpdb->prefix . "posts as p, " .
											" " . $wpdb->prefix . "supple_dataset as sd," .
											" " . $wpdb->prefix . "term_relationships as tr" .
											" where p.ID = sd.post_id" .
											" and 	p.ID = tr.object_id" .
											" and	tr.term_taxonomy_id = 3" .
											" group by sd.department) as ds_count " .
											" on sd.department = ds_count.department " . 
											" where p.ID = sd.post_id" .
											" and p.ID = tr.object_id" .
											" and	tr.term_taxonomy_id = " . $cat_id . 
											" order by p.post_title ASC;";
											
				$departments = $wpdb->get_results($sql, ARRAY_A); ?>
  			<?php if($departments) : ?>
				<h2 class="department"><?php echo $title ?></h2>
				<ul>
	  				<?php foreach($departments as $department) : ?>
						<li>
							<a href="<?php echo get_permalink($department[ID]) ?>"><?php echo $department[post_title]; ?></a> 
							<?php if ($department[num_sets]) { echo '(' . $department[num_sets] . ' datasets)'; }; ?>
						</li>
	  				<?php endforeach;?>
				</ul>
			<? endif ?>
		<?php endforeach; ?>
	</div>

<?php get_footer(); ?>