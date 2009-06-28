<?php get_header(); ?>
<?php 
     $posts = query_posts($query_string . '&orderby=title&order=asc&posts_per_page=50');
?>
	<div id="wide_content">
	<?php if (have_posts()) : ?>
			<?php $departments = $wpdb->get_results("select value, sl.label from " . $wpdb->prefix . "sppl_lookup as sl join  " . $wpdb->prefix . "sppl_fields as sf on sl.field_id = sf.field_id where field_name = 'department';", ARRAY_A); ?>
			<?php $licenses = $wpdb->get_results("select value, sl.label  from  " . $wpdb->prefix . "sppl_lookup as sl join  " . $wpdb->prefix . "sppl_fields as sf on sl.field_id = sf.field_id where field_name = 'license';", ARRAY_A); ?>
		
		
		<table summary="Results for Dataset section" width="100%" border="0" cellspacing="0" cellpadding="0" id="tblDataset"> 
			<caption> 
				<div class="catalog-header">DATA CATALOG</div> 
			</caption> 
			<thead> 
				<tr> 
					<th scope="col" id="c1" abbr="name" class="catname"> 
						<p class="nomargin white">Dataset Name</p>
					</th>
					<th scope="col" id="c2" abbr="instant access?"  class="center"> 
						<p class="nomargin white">Instant Access</p>
					</th>
					<th scope="col" id="c3" abbr="license" class="center"> 
						<p class="nomargin white">License</p>
					</th>
					<th scope="col" id="c4" abbr="Chargable?" class="center"> 
						<p class="nomargin white">Price</p>
					</th>
					<th scope="col" id="c5" abbr="xls" align="left" valign="middle" class="set_link"> 
						<p class="nomargin white">XLS</p>
					</th>
					<th scope="col" id="c6" abbr="csv" align="left" valign="middle" class="set_link"> 
						<p class="nomargin white">CSV</p>
					</th>
					<th scope="col" id="c7" abbr="kml" align="left" valign="middle" class="set_link"> 
						<p class="nomargin white">KML</p>
					</th>
					<th scope="col" id="c8" abbr="geo" align="left" valign="middle" class="set_link"> 
						<p class="nomargin white">Geo</p>
					</th>
					<th scope="col" id="c8" abbr="api" align="left" valign="middle" class="set_link"> 
						<p class="nomargin white">API</p>
					</th>
					<th scope="col" id="c9" abbr="other" align="left" valign="middle" class="set_link"> 
						<p class="nomargin white">Other</p>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php while (have_posts()) : the_post(); ?>
					<?php 
					 	$str = "SELECT * FROM  " . $wpdb->prefix . "supple_dataset WHERE post_id = " . $post->ID;
						$dataset = $wpdb->get_row($str); 
					?>
					<tr>
						<td class="name_col">
							<h2 class="tbl_ds_name"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
							<h3><?php echo output_label($dataset->department, $departments); ?></h3>
							<p><?php  echo strip_tags(get_the_excerpt(),'<br />'); ?></p>
						</td>
						<td  class="access_col">
							<?php echo output_image_if_match($dataset->instant_access, 1, "ok.png"); ?>
						</td>
						<td class="license_col">
							<?php echo output_label($dataset->license, $licenses); ?>
						</td>
						<td class="price_col">
							<?php echo output_image_if_match($dataset->free, 2, "dollar.png"); ?>
						</td>
						<td class="dl_col">
							<?php echo output_url($dataset->xls_url, "download-xls.jpg"); ?>
						</td>
						<td class="dl_col">
							<?php echo output_url($dataset->csv_url, "download-csv.gif"); ?>
						</td>
						<td class="dl_col">
							<?php echo output_url($dataset->kml_url, "download-kml.jpg"); ?>
						</td>
						<td class="dl_col">
							<?php echo output_url($dataset->geo_url, "download-geo.jpg"); ?>
						</td>
						<td class="dl_col">
							<?php echo output_url($dataset->api_url, "download-api.gif"); ?>
						</td>
						<td class="dl_col">
							<?php echo output_url($dataset->other_url, "download-other.jpg"); ?>
						</td>
					</tr>
			<?php endwhile; ?>
		</tbody>
	</table>
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