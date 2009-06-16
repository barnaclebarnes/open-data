<?php get_header(); ?>
<?php 
     $posts = query_posts($query_string . '&orderby=title&order=asc&posts_per_page=50');
?>
	<div id="wide_content">
		<h1 class="cat_header">List of Departments, Agencies, Crown Research Institutes and Councils</h1>
		<p>Click through to see what datasets we have listed for each entity.</p>
	<?php if (have_posts()) : ?>
			<?php $departments = $wpdb->get_results("select value, sl.label from " . $wpdb->prefix . "sppl_lookup as sl join  " . $wpdb->prefix . "sppl_fields as sf on sl.field_id = sf.field_id where field_name = 'department';", ARRAY_A); ?>
			
			<?php while (have_posts()) : the_post(); ?>
				<?php 
				 	$str = "SELECT * FROM  " . $wpdb->prefix . "supple_dataset WHERE post_id = " . $post->ID;
					$dataset = $wpdb->get_row($str); 
				?>
				<h2><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
			<?php endwhile; ?>
			
		
		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>

	<?php else : ?>

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>

	<?php endif; ?>

	</div>

<?php get_footer(); ?>