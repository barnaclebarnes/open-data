<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header();
?>

	<div id="wide_content" class="widecolumn">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<?php 
			$departments = $wpdb->get_results("select value, sl.label from  " . $wpdb->prefix . "sppl_lookup as sl join  " . $wpdb->prefix . "sppl_fields as sf on sl.field_id = sf.field_id where field_name = 'department';", ARRAY_A);
		 	$str = "SELECT * FROM  " . $wpdb->prefix . "supple_dataset WHERE post_id = " . $post->ID;
			$dataset = $wpdb->get_row($str); 

			$datasets = $wpdb->get_results("select p.post_title, p.ID " .
			"from " . $wpdb->prefix . "posts as p, " . $wpdb->prefix . "supple_dataset as ds, " . $wpdb->prefix . "term_relationships as ts " .
			"where p.id = ds.post_id " . 
			"and p.ID = ts.object_id " . 
			"and ts.term_taxonomy_id = 3 " .
			"and ds.department = " . $dataset->department .
			" order by p.post_title ASC;", ARRAY_A); ?>				
	
		<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
			<h2><?php the_title(); ?></h2>

			<?php the_content(); ?>
			
			<h2>Datasets for <?php echo output_label($dataset->department, $departments); ?></h2>
			<?php 
				if($datasets) {
					echo "<ul>";
					foreach($datasets as $set) {
						echo "<li><a href=" . get_permalink($set[ID]) . " title=\"About\">" . $set[post_title] . "</a></li>";
					}
					echo "</ul>";
				}
				else {
					echo "We don't have any datasets listed for " . output_label($dataset->department, $departments) . 
					". How about <a href=\"" . get_page_link(6) . "\">adding one yourself</a>." ;

				}
			?>
		
			<div class="entry">
				<?php the_tags( '<p>Tags: ', ', ', '</p>'); ?>


				<p class="postmetadata alt">
					<small>
						This entry was posted
						on <?php the_time('l, F jS, Y') ?> at <?php the_time() ?>
						and is filed under <?php the_category(', ') ?>.
						You can follow any responses to this entry through the <?php post_comments_feed_link('RSS 2.0'); ?> feed.

						<?php if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
							// Both Comments and Pings are open ?>
							You can <a href="#respond">leave a response</a>, or <a href="<?php trackback_url(); ?>" rel="trackback">trackback</a> from your own site.

						<?php } elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
							// Only Pings are Open ?>
							Responses are currently closed, but you can <a href="<?php trackback_url(); ?> " rel="trackback">trackback</a> from your own site.

						<?php } elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
							// Comments are open, Pings are not ?>
							You can skip to the end and leave a response. Pinging is currently not allowed.

						<?php } elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
							// Neither Comments, nor Pings are open ?>
							Both comments and pings are currently closed.

						<?php } edit_post_link('Edit this entry','','.'); ?>

					</small>
				</p>

			</div>
		</div>

	<?php comments_template(); ?>

	<?php endwhile; else: ?>

		<p>Sorry, no posts matched your criteria.</p>

<?php endif; ?>

	</div>

<?php get_footer(); ?>
