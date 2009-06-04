
	<div id="footer">	
	<p>&copy; Copyright <?php echo date("Y") ?> | <a href="<?php echo get_option('home'); ?>"><?php bloginfo('name'); ?></a> <?php if ( "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] == get_option('home')."/" || "http://www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] == get_option('home')."/" || $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] == get_option('home')."/" || "www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] == get_option('home')."/" ) : ?>
	| Theme by <a href="http://midmodesign.com/">Columbia, MO Web Design</a>
	<?php endif; ?>| All Rights Reserved</p>
	<p><?php wp_footer() ?></p>
	</div>

</div>

<!-- Can put web stats code here -->

</body>

</html>