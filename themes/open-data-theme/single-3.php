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
			$licenses = $wpdb->get_results("select value, sl.label  from  " . $wpdb->prefix . "sppl_lookup as sl join  " . $wpdb->prefix . "sppl_fields as sf on sl.field_id = sf.field_id where field_name = 'license';", ARRAY_A); 
			$instant_access = $wpdb->get_results("select value, sl.label  from  " . $wpdb->prefix . "sppl_lookup as sl join  " . $wpdb->prefix . "sppl_fields as sf on sl.field_id = sf.field_id where field_name = 'instant_access';", ARRAY_A); 
			$free = $wpdb->get_results("select value, sl.label  from  " . $wpdb->prefix . "sppl_lookup as sl join  " . $wpdb->prefix . "sppl_fields as sf on sl.field_id = sf.field_id where field_name = 'free';", ARRAY_A); 
		 	$str = "SELECT * FROM  " . $wpdb->prefix . "supple_dataset WHERE post_id = " . $post->ID;
			$dataset = $wpdb->get_row($str); 
		?>
	
		<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
			<h2><?php the_title(); ?></h2>
			<p><strong>Updated Frequency:</strong> <?php output_text_or_dash($dataset->update_frequency); ?></p>
			<p>
				<strong>Website Address:</strong> <?php output_url_or_dash($dataset->web_address); ?><br />
				<small>Location for more information about this dataset.</small>
			</p>
			<p><strong>Contact Name:</strong> <?php output_text_or_dash($dataset->contact_name); ?></p>
			<p><strong>Contact Phone:</strong> <?php output_text_or_dash($dataset->contact_phone); ?></p>
			<p><strong>Contact Email Address:</strong> <?php output_email_or_dash($dataset->contact_email);	?></p>
			<p>
				<strong>Instant Access:</strong> <?php echo output_label($dataset->instant_access, $instant_access); ?><br />
				<small>Is there an approval process or time delay to download this dataset?</small>
			</p>
			<p><strong>License:</strong> <?php echo output_label($dataset->license, $licenses); ?><br />
				<small>What license does this dataset fall under? Note: check the exact terms when using!</small>
			
			</p>
			<p><strong>Free:</strong> <?php echo output_label($dataset->free, $free); ?><br />
				<small>Is there a charge to use/download/access this dataset?</small>
			
			</p>
			<p><strong>Download Here:</strong>
				<?php echo output_url($dataset->xls_url, "download-xls.jpg"); ?>
				<?php echo output_url($dataset->csv_url, "download-csv.gif"); ?>
				<?php echo output_url($dataset->kml_url, "download-kml.jpg"); ?>
				<?php echo output_url($dataset->geo_url, "download-geo.jpg"); ?>
				<?php echo output_url($dataset->api_url, "download-api.gif"); ?>
				<?php echo output_url($dataset->other_url, "download-other.jpg"); ?>
			</p>


				<?php the_content(); ?>

				<p><strong>Last Editied:</strong> <?php echo $post->post_modified; ?></p>

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
