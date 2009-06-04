<?php
/*
Plugin Name: Sobek`s Posts in Category
Plugin URI: http://wordpress.sobek.pl/sobeks-post-in-category-plugin/
Version: 1.9
Description: Displays a list of the posts in one or more categories in virtually any way you want. Please read <a href="http://wordpress.sobek.pl/sobeks-post-in-category-plugin/">the plugin page</a> for installation and usage.
Author: Lukasz Sobek
Author URI: http://sobek.pl/
*/ 

function sobeks_posts_in_category() {

	// Definitions
	global $wpdb;
	$wp_tp = $wpdb->prefix;
	$num_of_args = func_num_args();
	$list_of_args = func_get_args();
	$disp_post_count = 0; $disp_comment_count = 0; $disp_date = 0; $disp_author = 0;
	$text_before = '<li>'; $text_after = '</li>'; $text_container_before = ''; $text_container_after = '';
	$limit_text = ''; $order_meta_key = '';
	$order_posts_by = "{$wp_tp}posts.post_title ASC";
	$cstm_err_msg = '<b style="color:#f00;">Error:</b> Please enter the ID of at least one category';
	// there are arguments
	if ($num_of_args != 0) {
		// there are "display" arguments
		if(!is_bool(array_search('postcount',$list_of_args))) { $disp_post_count = 1; }
		if(!is_bool(array_search('commentcount',$list_of_args))) { $disp_comment_count = 1; }
		if(!is_bool(array_search('date',$list_of_args))) { $disp_date = 1; }
		if(!is_bool(array_search('author',$list_of_args))) { $disp_author = 1; }
		//------------------------------
		$list_of_args = implode(",",$list_of_args);
		if(!is_bool(stripos($list_of_args,'sort:'))) {
			preg_match('/(sort:)(comments|date|meta:)([\w\-]*)/',$list_of_args, $matches);
			if($matches[2] == 'comments'){$order_posts_by = "{$wp_tp}posts.comment_count DESC";}
			if($matches[2] == 'date'){$order_posts_by = "{$wp_tp}posts.post_date DESC";}
			if($matches[2] == 'meta:'){
				$order_meta_key = "{$wp_tp}postmeta.meta_key = '" . $matches[3] . "' AND ";
				$order_posts_by = "{$wp_tp}postmeta.meta_value ASC"; }
			$list_of_args = str_replace($matches[0], '', $list_of_args);
		}
		if(!is_bool(stripos($list_of_args,'style:'))) {
			preg_match('/(style:)(order|unorder|comma)/',$list_of_args, $matches);
			if($matches[2] == 'order') {
				$text_container_before = '<ol class="sobeks_pic">';	$text_container_after = '</ol>'; }
			if($matches[2] == 'unorder') {
				$text_container_before = '<ul class="sobeks_pic">';	$text_container_after = '</ul>'; }
			if($matches[2] == 'comma') {
				$text_before = ''; $text_after = ' <font style="font-size: 120%"><b>&#149;</b></font> '; }
			$list_of_args = str_replace($matches[0], '', $list_of_args);
		}
		if(!is_bool(stripos($list_of_args,'limit:'))) {
			preg_match('/(limit:)([0-9]*)/',$list_of_args, $matches);
			$limit_text = ' LIMIT 0, ' . $matches[2];
			$list_of_args = str_replace($matches[0], '', $list_of_args); }
		$list_of_args = explode(",",$list_of_args);
		$list_of_args = array_diff($list_of_args, array('', 'postcount', 'commentcount', 'date', 'author'));
		//------------------------------
		$num_of_args = func_num_args($list_of_args);
		// after having dealt with the "display arguments" there are some arguments left
		if ($num_of_args != 0) {
			// there is more than one category
			if($num_of_args != 1) {
				$the_cat_result = "AND ( ";
				for ($i = 0; $i < $num_of_args; $i++) {
					if($i != 0) { $the_cat_result .= " OR "; }
					$the_cat_result .= "{$wp_tp}term_taxonomy.term_id = '" . $list_of_args[$i] . "'"; }
				$the_cat_result .= " )";
			// there is one category
			} else { $the_cat_result = "AND {$wp_tp}term_taxonomy.term_id = '" . $list_of_args[0] ."'"; }
		//------------------------------
		// after having dealt with the "display arguments" there are no arguments left
		} else { echo $cstm_err_msg; exit; }
	// there are no arguments
	} else { echo $cstm_err_msg; exit; }
	// to execute after having done the checking
	$posts_in_term = $wpdb->get_results("SELECT {$wp_tp}posts.ID, {$wp_tp}posts.post_date, {$wp_tp}posts.post_title, {$wp_tp}posts.post_status, {$wp_tp}posts.comment_count, {$wp_tp}term_relationships.object_id, {$wp_tp}term_relationships.term_taxonomy_id, {$wp_tp}users.display_name FROM {$wp_tp}posts, {$wp_tp}term_relationships, {$wp_tp}term_taxonomy, {$wp_tp}users WHERE {$wp_tp}posts.ID = {$wp_tp}term_relationships.object_id AND " . $order_meta_key . "{$wp_tp}term_relationships.term_taxonomy_id = {$wp_tp}term_taxonomy.term_taxonomy_id AND {$wp_tp}term_taxonomy.taxonomy = 'category' AND {$wp_tp}users.ID = {$wp_tp}posts.post_author AND {$wp_tp}posts.post_status = 'publish' " . $the_cat_result . " AND {$wp_tp}posts.post_date < NOW( ) ORDER BY " . $order_posts_by . $limit_text);
	$posts_in_term = array_values($posts_in_term);
	$num_of_posts = count($posts_in_term);
	$i = 1;
	if ($disp_post_count != 0) {
		if($num_of_posts != 1) { $post_count_statement = 'are ' . $num_of_posts . ' posts';
		} else { $post_count_statement = 'is one post'; }
		echo 'There ' . $post_count_statement . ' in this category<br />'; } 
	echo $text_container_before;
	foreach ($posts_in_term as $posts) {
		echo $text_before;
		if($disp_date != 0){ echo date('j-M-y',strtotime($posts->post_date)) . " - "; }
		echo '<a href="' . get_permalink($posts->ID) . '">' . $posts->post_title . '</a>';
		if($disp_author != 0){ echo ' by ' . $posts->display_name; }
		if($disp_comment_count != 0) {
			if($posts->comment_count != 1) { echo ', ' . $posts->comment_count . ' comments';
			} else { echo ', ' . $posts->comment_count . ' comment'; }
		}
	if($i == $num_of_posts && $text_after == ' <font style="font-size: 120%"><b>&#149;</b></font> ') { $text_after = ''; }
	$i++;
	echo $text_after; }	
	echo $text_container_after;
}
?>