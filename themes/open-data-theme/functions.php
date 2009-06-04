<?php
add_filter('single_template', create_function('$t', 'foreach( (array) get_the_category() as $cat ) { if ( file_exists(TEMPLATEPATH . "/single-{$cat->term_id}.php") ) return TEMPLATEPATH . "/single-{$cat->term_id}.php"; } return $t;' ));

if ( function_exists('register_sidebar') )
    register_sidebar(array(
		'name' => 'Sidebar',
        'before_widget' => '<div class="block %1$s %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ));
	
if ( function_exists('register_sidebar') )
    register_sidebar(array(
		'name' => 'Blurb',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => '',
    ));
	
if ( function_exists('register_sidebar') )
register_sidebar(array(
	'name' => 'Top Navigation',
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '',
	'after_title' => '',
));

function output_image_if_match($field, $match_value, $image_name) {
	if ($field == $match_value)
		$str = "<img src='". get_bloginfo('template_directory') . "/images/" . $image_name . "' />";
	else
		$str = "";

	return $str;
}

function output_url($field, $image_name) {
	if (strlen($field) > 7)
		$str = "<a href='". $field . "' /><img src='" . get_bloginfo('template_directory') . "/images/" . $image_name ."' /></a>";
	else
		$str = "";

	return $str;
}

function output_text_or_dash($field) {
	if (strlen($field) > 0)
		echo $field;
	else
		echo "-";
}

function output_url_or_dash($field) {
	if (strlen($field) > 7)
		echo "<a href='". $field . "' />" . $field . "</a>";
	else
		echo  "-";
}

function output_email_or_dash($field) {
	if (strlen($field) > 1)
		echo "<a href='mailto:". $field . "' />" . $field . "</a>";
	else
		echo  "-";
}


function output_label($value, $lookup) {
	//TODO: Must be an easier way to search nexted array's in PHP?
	$str = "";
	foreach ($lookup as $item) {
		if ($item['value'] == $value)
			$str = $item['label'];
	}

	return $str;
}


$licenses
?>