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
				$departments = $wpdb->get_results("select  p.post_title, p.ID " .
												  "from " . $wpdb->prefix . "posts as p, " . $wpdb->prefix . "term_relationships as ts " .
												  "where p.ID = ts.object_id " . 
												  "and ts.term_taxonomy_id = " . $cat_id .
												  " order by p.post_title ASC;", ARRAY_A); ?>
  			<?php if($departments) : ?>
				<h2 class="department"><?php echo $title ?></h2>
				<ul>
	  				<?php foreach($departments as $department) : ?>
						<li><a href="<?php echo get_permalink($department[ID]) ?>"><?php echo $department[post_title]; ?></a></li>
	  				<?php endforeach;?>
				</ul>
			<? endif ?>
		<?php endforeach; ?>
	</div>

<?php get_footer(); ?>