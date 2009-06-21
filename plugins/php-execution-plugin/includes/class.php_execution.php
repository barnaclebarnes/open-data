<?php

define('PHP_EXECUTION_VERSION', '1.0.0');

class php_execution
{
	/**
	 * cache variable for $current_user->has_cap(PHP_EXECUTION_CAPABILITY);
	 */
	var $current_user_can_exec_php = false;

	/**
	 * cache array for $user = new WP_User($id); $user->has_cap(PHP_EXECUTION_CAPABILITY);
	 */	
	var $cap_cache = array();
	
	/**
	 * constructor
	*/
	function php_execution()
	{
		/*
		 * TinyMce hook - load js file 
		 * bug fix -> moved outside "if(WP_ADMIN)" for compatibility with WP version 2.5
		 */
		add_action('mce_external_plugins', array(&$this,'action_mce_external_plugins'));
		
		global $pagenow;
		
		if ( $pagenow && in_array( $pagenow, array('post.php', 'post-new.php', 'page.php', 'page-new.php') ) )
		{
			add_action( 'admin_print_scripts', array(&$this,'action_admin_print_scripts') );
		}
		
		if( defined('WP_ADMIN') )
		{
			// installation
			$pl = plugin_basename( PHP_EXECUTION_ROOT .'/php_execution.php');
			add_action('activate_' . $pl, array(&$this,'action_activate') );
			add_action('deactivate_' . $pl, array(&$this,'action_deactivate') );

			// Administration Area
			add_action('admin_menu', array(&$this,'action_admin_menu'));
			
			// TinyMce hooks
			add_action('the_editor_content', array(&$this,'action_the_editor_content'), 1);
			
			// init
			add_action('init', array(&$this,'action_init'));
		}
		else
		{
			// content hooks
			add_action('the_content', array(&$this,'action_the_content'), 1);
			add_action('the_content_rss', array(&$this, 'action_the_content'), 1);
			
			add_action('the_excerpt', array(&$this, 'action_the_content'), 1);
			add_action('the_excerpt_rss', array(&$this, 'action_the_content'), 1);
		}
	}

	/**
	 * plugin activation
	*/
	function action_activate()
	{
		global $wp_roles;
		
		if( $role = $wp_roles->get_role('administrator') )
		{
			$role->add_cap(PHP_EXECUTION_CAPABILITY);
		}
		
		add_option(PHP_EXECUTION_OPTION, array('observer_active'=>1));
	}

	/**
	 * plugin deactivation
	*/
	function action_deactivate()
	{
		global $wp_roles;
		
		if( $role = $wp_roles->get_role('administrator') )
		{
			$role->remove_cap(PHP_EXECUTION_CAPABILITY);
		}
		
		delete_option(PHP_EXECUTION_OPTION);
	}

	/**
	 * initing object with wp settings
	*/
	function action_init()
	{
		global $current_user;
		
		$this->current_user_can_exec_php = $current_user->has_cap(PHP_EXECUTION_CAPABILITY);
		
		$this->options = get_option(PHP_EXECUTION_OPTION);
		
		if(!$this->current_user_can_exec_php && $this->options['observer_active'])
		{
			// add Post Edit Monitor
			add_filter('user_has_cap', array(&$this,'action_user_has_cap'),10,3);
		}
	}

	/**
	 * As some users are allowed to edit other users' posts
	 * it would be a security risk if a user with no rights
	 * to execute php code can edit posts of a user who can.
	 *
	 * The aim of this callback is to deny users with no rights
	 * to execute php code to edit posts of users who can.
	*/
	function action_user_has_cap($allcaps, $caps, $args)
	{
		global $current_user;
		// allcaps = all capabilities of the user
		// caps = capabilities to check along with args[0]
		// args[0] = current capability to check; args[1] = userID; args[2] = postID;
		#echo '<pre>' . print_r($caps,1) .'</pre>';
		#echo '<pre>' . print_r($args,1) .'</pre>';
		if($args[1] == $current_user->ID)
		{
			switch(true)
			{
				case($args[0] == 'edit_post' && in_array('edit_others_posts',$caps)):
				case($args[0] == 'edit_page' && in_array('edit_others_pages',$caps)):
					
					$author_can_exec_php = true; // till better info we expect the author to have rights to execute php code
					
					if( $args[2] && ($post = get_post($args[2])) ) // if postID is specified => check post->authors php execution rights
					{
						$id = $post->post_author;
						
						if(isset($this->cap_cache[$id]))
						{
							$author_can_exec_php = $this->cap_cache[$id];
						}
						else
						{
							$author = new WP_User($id);
							$author_can_exec_php = $author->has_cap(PHP_EXECUTION_CAPABILITY);
							$this->cap_cache[$id] = $author_can_exec_php;
						}
					}
					
					if( !$this->current_user_can_exec_php && $author_can_exec_php )
					{
						$allcaps['edit_others_posts'] = false;
						$allcaps['edit_others_pages'] = false;
					}
					break;
				
				// The following 2 cases are if checked always checked together with the above ones !?!?
				// I've checked this for editing posts and pages in the general dialog.
				// As !!very wisely!! no authorID or postID is provided with a cap check for
				// edit_others_posts & edit_others_pages, one cannot tell who "the others" are
				// and thus we cant decide if they are allowed to execute php code or not.
				#case($args[0] == 'edit_others_posts'):
				#case($args[0] == 'edit_others_pages'):
					#break;
			
			}
		}
		return $allcaps;
	}

	/**
	 * add PHP Execution options page to the admin section
	*/
	function action_admin_menu()
	{
		add_options_page('PHP Execution', 'PHP Execution', 'manage_options', PHP_EXECUTION_ROOT . '/php_execution_admin.php');
	}

	/**
	 * admin_print_scripts action. 
	 * Used to add the baseURL for the plugin.
	 * Bug fix for cases where the WP_PLUGIN directory does not reside at its usual place.
	 *
	 * see: admin-header.php [71] - do_action('admin_print_scripts');
	*/
	function action_admin_print_scripts()
	{
		echo '<script type="text/javascript">/* <![CDATA[ */ phpExecutionBaseUrl="'. PHP_EXECUTION_BASE_URL .'"; /* ]]> */</script>';
	}

	/**
	 * load editor plugin javascript
	*/
	function action_mce_external_plugins($content)
	{
		$content['phpExecution'] = PHP_EXECUTION_BASE_URL . '/assets/editor_plugin.js';
		return $content;
	}
	
	/**
	 * prepare html data for editor
	*/
	function action_the_editor_content($content)
	{
		// if rich editing is on => wordpress adds autop which destroys our code
		// so we need to be there prior than WP.
		// See wp-includes/general-template.php [1500+] for more info
		// and "wp_htmledit_pre" vs. "wp_richedit_pre"
		// the filter functions added are:
		// a) wp_richedit_pre (wp-includes/formatting.php [1627]) = when tinymce is ACTIVE
		// b) wp_htmledit_pre (wp-includes/formatting.php [1649]) = when tinymce is INACTIVE
		if(wp_default_editor() != 'html')
		{
			$content = preg_replace(
				'#<\?php([\s\S]*?)\?>#ie', 
				'\'<img src="\' . PHP_EXECUTION_BASE_URL . \'/assets/trans.gif" class="mceWpPHP mceItemNoResize" title="php" alt="\' . base64_encode(stripslashes(\'\\0\')) . \'" />\'',
				$content
			);
		}
		
		return $content;
	}

	/**
	 * depending if code execution is allowed
	 * will execute or remove php code in/from post
	*/
	function action_the_content($content)
	{	
		return (strpos($content,'<?php') === false) // fast(er) precheck if post contains php
		?	$content
		: 	($this->php_execution_allowed())
			? $this->execute_php($content)
			: preg_replace('#<\?php([\s\S]*?)\?>#','',$content);
	}
	
	/**
	 * check if execution of php code is allowed
	*/
	function php_execution_allowed()
	{
		global $post;
		
		if (isset($post) && isset($post->post_author))
		{
			$id = $post->post_author;
			
			if(isset($this->cap_cache[$id])) return $this->cap_cache[$id];
			
			$user = new WP_User($id);
			$perm = $user->has_cap(PHP_EXECUTION_CAPABILITY);
			
			// wp hook integrated here
			$perm = apply_filters('php_execution_allowed',$perm,$user);
			
			$this->cap_cache[$id] = $perm;
			
			return $perm;
		}
		return false;
	}

	/**
	 * execute php inside post
	*/
	function execute_php($content)
	{
		ob_start();
		eval("?>$content<?php ");
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

}

?>