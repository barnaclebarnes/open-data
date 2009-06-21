<?php
/*
Plugin Name: PHP Execution
Plugin URI: http://www.zehnet.de/
Description: The PHP Execution Plugin allows users to write php code inside their posts and pages. Embedded php code will be executed when the post is shown. In doing so, the plugin offers the possibility to utilize dynamic content inside of posts. <strong>This plugin does not require to turn off the visual editor</strong>.
Version: 1.0.0
Author: Nicolas Zeh
Author URI: http://www.zehnet.de/
*/

/**

   	** LICENSE **
	
	Copyright © 2009 Nicolas Zeh <http://www.zehnet.de/>
   
    This plugin is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation (version 2.0).
	For more information see: http://creativecommons.org/licenses/GPL/2.0/

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
	
	
	** ACKNOWLEDGEMENTS **
	
	This plugin makes use of the Silk Icon Set 1.3 by
	Mark James <http://www.famfamfam.com/lab/icons/silk/>
	licensed under Creative Commons Attribution 2.5 License.
	
*/

/*
 * the name of the capability PHP execution uses to check wether permission is granted or denied
 */
define('PHP_EXECUTION_CAPABILITY', 'execute_php');

/*
 * the name of the option PHP execution uses to store its settings
 */
define('PHP_EXECUTION_OPTION', 'php_execution');

/*
 * root directory of the plugin
 */
define('PHP_EXECUTION_ROOT', dirname(__FILE__));

/*
 * base url of the plugin
 */
define('PHP_EXECUTION_BASE_URL', WP_PLUGIN_URL . '/' . basename(PHP_EXECUTION_ROOT) );


require(PHP_EXECUTION_ROOT.'/includes/class.php_execution.php'); 

$php_execution = new php_execution();

?>