<?php

global $current_user, $wp_roles;

include_once(PHP_EXECUTION_ROOT.'/includes/class.php_execution_admin.php');

$php_exec_admin = new php_execution_admin();

$current_user_is_admin = ($current_user->roles[0] == 'administrator' || current_user_can(10) );

// hook for wether or not to show administration panel
// in the end it could also be used to intervene $_POST submissions
$current_user_is_admin = apply_filters('php_execution_show_admin_panel',$current_user_is_admin);

// the caps to check for post editing permissions
$EDIT_CAPS = array('edit_others_posts', 'edit_others_pages');

?>
<script language="javascript" type="text/javascript">
jQuery(document).ready(function()
{
	jQuery(".helptext").css('display','none');
	
	jQuery("a.helptoggle").click(function(event)
	{
		event.preventDefault();
		var a = jQuery(this);
		var p = jQuery(this).parent().next('.helptext');
		p.slideToggle('fast',function()
		{
			if(p.css('display')=='none')
			{
				a.html('more help &raquo;');
			}
			else
			{
				a.html('less help &raquo;');
			}		
		});
	});
});
</script>
<div class="icon32" style="background: transparent url(<?php echo PHP_EXECUTION_BASE_URL ?>/assets/icon.png) no-repeat"><br></div>
<div class="wrap">
<form method="post">
	<h2>PHP Execution</h2>
<?php

// Parse submissions
if($current_user_is_admin)
{

	$search = new WP_User_Search();
	
	// the roles to offered to add php execution permissions
	$role_names = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

	// ATTENTION: roles are ordered hierarchical: administrator => editor => author
	// if e.g. the role author gets granted php execution rights, all higher roles are also: i.e. administrator & editor	
	$role_names = apply_filters('php_execution_hierarchical_roles',$role_names);
	
	$error = '';
	$info = '';
		
	if(count($_POST)>0)
	{
	
		// setting role permissions
		if($_POST['save_role_perms'])
		{
			// if role_perms is corrupt it will intval to 0 which means resetting to default only admin execution
			$new_role = intval($_POST['role_perms']);
			
			$granted = array();
	
			for($i=0, $n=count($role_names); $i<$n; $i++)
			{
				$rolename = $role_names[$i];
				
				if( $role = $wp_roles->get_role($rolename) )
				{
					if($i<=$new_role)
					{	
						$role->add_cap(PHP_EXECUTION_CAPABILITY);
						
						array_unshift($granted, ucfirst($rolename).'s');
					}
					else
					{
						$role->remove_cap(PHP_EXECUTION_CAPABILITY);
					}
				}
				else
				{
					$error = 'Could not update role permissions to <strong>'.$role_names[$new_role].'</strong>.';
					
				}
			}
			if(!$error) 
			{
				$info = '<em>Php execution</em> permission was granted to <strong>'. implode(', ',$granted) .'</strong>.';
			}
			
			
		}
		
		// setting user php execution permissions
		else if(count($_POST['userPHP'])>0)
		{
			list($id,$action) = each($_POST['userPHP']);
			
			$user = new WP_User(intval($id));
			
			if( $php_exec_admin->toggle_caps($user, array(PHP_EXECUTION_CAPABILITY), ($action == 'enable')) )
			{
				$info = ($action == 'enable')
						? '<em>Php execution</em> permission was granted to user <strong>'.$user->user_login.'</strong>.'
						: '<em>Php execution</em> permission was denied for user <strong>'.$user->user_login.'</strong>.';
			}
		}
		
		// setting user others post editing permissions
		else if(count($_POST['userEdit'])>0)
		{
			list($id,$action) = each($_POST['userEdit']);
			
			$user = new WP_User(intval($id));
			
			if( $php_exec_admin->toggle_caps($user, $EDIT_CAPS, ($action == 'enable')) )
			{
				$info = ($action == 'enable')
						? '<em>Edit others\' posts</em> permission was granted to user <strong>'.$user->user_login.'</strong>.'
						: '<em>Edit others\' posts</em> permission was denied for user <strong>'.$user->user_login.'</strong>.';
			}
		}
		// setting user permissions
		else if($_POST['reset_explicit_php'])
		{
			foreach($search->get_results() as $id)
			{
				$user = new WP_User($id);
				$php_exec_admin->remove_item_caps($user, array(PHP_EXECUTION_CAPABILITY) );
			}
			$info = 'All <strong>explicit</strong> <em>php execution</em> permissions were removed.';
		}
		
		// setting user permissions
		else if($_POST['reset_explicit_edit'])
		{
			foreach($search->get_results() as $id)
			{
				$user = new WP_User($id);
				$php_exec_admin->remove_item_caps($user, $EDIT_CAPS );
			}
			$info = 'All <strong>explicit</strong> <em>edit others\' posts</em> permissions were removed.';
		}
		
		// deactivate edit post observer
		else if($_POST['deactivate_observer'] && $php_exec_admin->options['observer_active'])
		{
			$php_exec_admin->options['observer_active'] = 0;
			update_option(PHP_EXECUTION_OPTION,$php_exec_admin->options);
			$info = 'The Post Edit Observer was <strong>deactivated</strong>.';
		}	
		
		// activate edit post observer
		else if($_POST['activate_observer'] && !$php_exec_admin->options['observer_active'])
		{
			$php_exec_admin->options['observer_active'] = 1;
			update_option(PHP_EXECUTION_OPTION,$php_exec_admin->options);
			$info = 'The Post Edit Observer was <strong>activated</strong>.';
		}		
		
		
	} // endif $_POST
	
	if($error) $info = 'ERROR: '.$error;
	if($info)
	{
		echo '<div id="message" style="margin-top:5px 0 10px 0" class="updated fade"><p>'.$info.'</p></div>';
	}
?>
<div class="error" style="border: 1px solid #666; margin-top:10px">
  <p><strong>Attention</strong>: Executing php code in your posts can lead to severe security risks 
concerning your blog. Therefore you should grant permissions  only to people you really trust.</p>
  </div>
<p>This administration area gives you the possibility to grant php execution permissions
to different users or user groups (roles).</p>

<h3 class="title">Roles &amp; Permissions</h3>

<p>Below you can specify which role a user must at least possess in order to be allowed to execute php code.
<br/>
By <em>default</em> only Administrators (i.e. the administrator role) possess the permission to execute php code. 
<a href="#" class="helptoggle">more help &raquo;</a></p>
<p class="helptext">Wordpress in general offers the following five <strong>roles</strong> which can be attached to Wordpress users: <em>Administrator, Editor, Author, Contributor, Subscriber</em>.<br/>
For more information see <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">Wordpress Codex: Roles &amp; Capabilities</a>.</p>
<p>
<?php
	
	for($n=count($role_names), $i=$n-1, $sel=array(), $granted=false; $i>=0; $i--)
	{
		if( $role = $wp_roles->get_role($role_names[$i]) )
		{	
			$sel[$i] = '';
			if( $role->has_cap(PHP_EXECUTION_CAPABILITY) && $granted == false)
			{ 
				$sel[$i] = ' selected="selected"';
				$granted = true;
			}
			$sel[$i] .= $granted ? ' style="background-color:#ACFF9D"' : ' style="background-color:#FFAA9D"';
		}
	}
?>
    <select name="role_perms">
      <option value="0"<?php echo $sel[0]; ?>>Administrators</option>
      <option value="1"<?php echo $sel[1]; ?>>Editors</option>
      <option value="2"<?php echo $sel[2]; ?>>Authors</option>
      <option value="3"<?php echo $sel[3]; ?>>Contributors</option>
      <option value="4"<?php echo $sel[4]; ?>>Subscribers</option>
    </select>
    <input type="submit" name="save_role_perms" value="Save Role Permissions" class="button-secondary"/>
</p>

<h3 class="title">Users &amp; Permissions</h3>

<p>Below is a list of all users of this blog. It is possible to edit a user's permision to <em>execute php code</em> and his permission to <em>edit other users' posts</em>.
	<a href="#" class="helptoggle">more help &raquo;</a></p>
<div class="helptext">
<p>The permissions shown are either inherited from the user's <strong>role</strong> profile (Administrator, Editor, ...) or they are 
  <strong>explicitly</strong> set for a specific user.</p>
<ul style="list-style:outside;margin-left: 15px">
  <li>With the &quot;enable/disable&quot; buttons you can explicitly 
set permissions for a specific user and override the role settings.</li>
  <li>With the buttons &quot;reset explicit&quot; you can remove all explicit permission settings, so that settings return to role permissions.</li>
  <li>The permissions to <em>edit other users' posts</em> are  relevant for this plugin as  they could mean a security risk: Even if you deny a user the permission to <em>execute php code</em>, he still can do so if he has the permission to <em>edit other users' posts</em>. This is the case when he is allowed to edit posts of users with the permission to <em>execute php code</em>. Php code added in their posts still gets executed.</li>
  <li>The table shows a user's permission to <em>edit other users' posts</em> next his permission to execute php code. If a user has no permission to <em>execute php code</em>, but the permission to <em>edit other users' posts</em>, the security risks arises. A <img src="<?php echo PHP_EXECUTION_BASE_URL; ?>/assets/error.png" style="vertical-align:middle" /> sign indicates if that risk occurs.</li>
  <li>If that risk occurs, there are two possibilities:
    <ul style="list-style:outside;margin-left: 15px">
      <li>a) You can disable the user's permission to <em>edit other users' posts</em> as well.</li>
      <li>b) You can enable the <strong>post edit observer</strong> below. It is enabled by <em>default</em>. The post edit observer automatically takes care of editing permissions. For further information see explanations below.</li>
    </ul>
  </li>
  </ul>
</div>
<?php 

if($search->get_results()) 
{
?>
<table class="widefat fixed" cellspacing="0">
    <thead>
        <tr class="thead">
            <th scope="col" id="username" class="manage-column column-username" style="">Username</th>
            <th scope="col" id="name" class="manage-column column-name" style="">Name</th>
            <th scope="col" id="email" class="manage-column column-email" style="">E-mail</th>
            <th scope="col" id="role" class="manage-column column-role" style="">Role</th>
            <th scope="col" id="edit" class="manage-column column-role" style="">Can edit others posts</th>
            <th scope="col" id="php" class="manage-column column-role" style="">PHP Execution</th>
        </tr>
    </thead>
    
    <tfoot>
        <tr class="thead">
            <th scope="col" class="manage-column column-username" style="">Username</th>
            <th scope="col" class="manage-column column-name" style="">Name</th>
            <th scope="col" class="manage-column column-email" style="">E-mail</th>
            <th scope="col" class="manage-column column-role" style="">Role</th>
            <th scope="col" class="manage-column column-role" style=""><input type="submit" name="reset_explicit_edit" value="reset explicit" class="button-secondary" style="padding: 3px 5px"/></th>
            <th scope="col" class="manage-column column-role" style=""><input type="submit" name="reset_explicit_php" value="reset explicit" class="button-secondary" style="padding: 3px 5px"/></th>
        </tr>
    </tfoot>
    
    <tbody id="users" class="list:user user-list">

<?php

	$editOtherConflict = false;

	foreach($search->get_results() as $id)
	{
		
		$user = new WP_User($id);
		
		$role = $user->roles[0];
		$roleName = isset($wp_roles->role_names[$role]) ? translate_with_context($wp_roles->role_names[$role]) : __('None');
		
		$user_has_php_cap 	 = $php_exec_admin->item_has_cap($user, PHP_EXECUTION_CAPABILITY);
		$php_is_explicit_cap = $php_exec_admin->is_explicit_cap($user, PHP_EXECUTION_CAPABILITY);

		
		$user_has_edit_cap 	 = $php_exec_admin->item_has_either_caps($user, $EDIT_CAPS);
		$edit_is_explicit_cap = $php_exec_admin->user_has_either_role_caps($user, $EDIT_CAPS);
		$edit_is_explicit_cap = ($user_has_edit_cap != $edit_is_explicit_cap);
		
		switch(true)
		{
			// no permission inherited from role
			case(!$php_is_explicit_cap && !$user_has_php_cap):
				$permission = '<img src="'. PHP_EXECUTION_BASE_URL .'/assets/cross.png" /> <input type="submit" name="userPHP['.$id.']" value="enable" class="button-secondary"/> ';
				break;
			
			// no permission explicitly set
			case($php_is_explicit_cap && !$user_has_php_cap):
				$permission = '<img src="'. PHP_EXECUTION_BASE_URL .'/assets/delete.png" /> <input type="submit" name="userPHP['.$id.']" value="enable" class="button-secondary"/> ';
				break;
			
			// granted permission inherited from role
			case(!$php_is_explicit_cap && $user_has_php_cap):
				$permission = '<img src="'. PHP_EXECUTION_BASE_URL .'/assets/tick.png" /> <input type="submit" name="userPHP['.$id.']" value="disable" class="button-secondary"/>';
				break;
			
			// granted permission explicitly set
			case($php_is_explicit_cap && $user_has_php_cap):
				$permission = '<img src="'. PHP_EXECUTION_BASE_URL .'/assets/accept.png" /> <input type="submit" name="userPHP['.$id.']" value="disable" class="button-secondary"/>';
				break;
			
		}

		switch(true)
		{
			// no permission inherited from role
			case(!$edit_is_explicit_cap && !$user_has_edit_cap):
				$othersPosts = '<img src="'. PHP_EXECUTION_BASE_URL .'/assets/cross.png" /> <input type="submit" name="userEdit['.$id.']" value="enable" class="button-secondary"/> ';
				break;
			
			// no permission explicitly set
			case($edit_is_explicit_cap && !$user_has_edit_cap):
				$othersPosts = '<img src="'. PHP_EXECUTION_BASE_URL .'/assets/delete.png" /> <input type="submit" name="userEdit['.$id.']" value="enable" class="button-secondary"/> ';
				break;
			
			// granted permission inherited from role
			case(!$edit_is_explicit_cap && $user_has_edit_cap):
				$othersPosts = '<img src="'. PHP_EXECUTION_BASE_URL .'/assets/tick.png" /> <input type="submit" name="userEdit['.$id.']" value="disable" class="button-secondary"/>';
				break;
			
			// granted permission explicitly set
			case($edit_is_explicit_cap && $user_has_edit_cap):
				$othersPosts = '<img src="'. PHP_EXECUTION_BASE_URL .'/assets/accept.png" /> <input type="submit" name="userEdit['.$id.']" value="disable" class="button-secondary"/>';
				break;
			
		}
		
		if(!$user_has_php_cap && $user_has_edit_cap)
		{
			$editOtherConflict = true;
			$othersPosts .= ' <img src="'. PHP_EXECUTION_BASE_URL .'/assets/error.png" />';
		}
		
 ?>
        <tr id="user-<?php echo $id ?>" class="alternate">
            <td class="username column-username"><?php echo get_avatar( $user->user_email, 32 ); ?> <strong><?php echo $user->user_login ?></strong></td>
            <td class="name column-name"><?php echo $user->first_name . ' ' . $user->last_name ?></td>
            <td class="email column-email"><a href="mailto:<?php echo $user->user_email; ?>" title="e-mail: <?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a></td>
            <td class="role column-role"><?php echo $roleName ?></td>
            <td class="role column-role"><?php echo $othersPosts ?></td>
          <td class="posts column-role"><?php echo $permission ?></td>
        </tr>
<?php

	} // end foreach
	
?>
    </tbody>
</table>

<div style="clear:both; margin-top:10px;">
<table style="display:inline">
	<tbody>
    	<tr>
			<td style="font-size:11px; padding-right: 10px"><img src="<?php echo PHP_EXECUTION_BASE_URL; ?>/assets/tick.png" style="vertical-align:middle" /> permission granted (inherited from role)</td>
			<td style="font-size:11px; padding-right: 10px"><img src="<?php echo PHP_EXECUTION_BASE_URL; ?>/assets/accept.png" style="vertical-align:middle" /> permission granted (explicitly set for specific user)</td>
		</tr>
       	<tr>
			<td style="font-size:11px; padding-right: 10px"><img src="<?php echo PHP_EXECUTION_BASE_URL; ?>/assets/cross.png" style="vertical-align:middle" /> permission denied (inherited from role)</td>
			<td style="font-size:11px; padding-right: 10px"><img src="<?php echo PHP_EXECUTION_BASE_URL; ?>/assets/delete.png" style="vertical-align:middle" /> permission denied (explicitly set for specific user)</td>
		</tr>
       	<tr>
       	  <td style="font-size:11px; color:#FF3300; padding-right: 10px"><img src="<?php echo PHP_EXECUTION_BASE_URL; ?>/assets/error.png" style="vertical-align:middle" /> indicates a possible security risk</td>
       	  <td style="font-size:11px; padding-right: 10px">&nbsp;</td>
     	  </tr>
    </tbody>
</table>
</div>
<?php

	} // end if search->get_results
	
?>
<h3 class="title">Post Edit Observer</h3>

<?php

	if(!$php_exec_admin->options['observer_active'] && $editOtherConflict)
	{
	
?>
<p style="color:#f30; font-weight:bold">
 <img src="<?php echo PHP_EXECUTION_BASE_URL; ?>/assets/error.png" style="vertical-align:middle" /> You really should
 activate the Post Edit Observer as a security risk occurs with your php execution permission settings.
</p>
<?php

	}
	
?>

<p>The <strong>post edit observer</strong> monitors the users' permissions to edit posts of other users. 
It prevents users  lacking in permissions to execute php code to add code to posts of 
users with the permission to execute php code. <a href="#" class="helptoggle">more help &raquo;</a>
</p>
<p class="helptext">
Even if you deny a user the permission to execute php code, he still can do so if he has the permission to 
edit other users' posts and one of these  users is allowed to execute php code. 
To prevent this risk, the <strong>post edit observer</strong> monitors the editing permissions of users 
and denies users with <strong>no</strong> permission to execute php code to edit posts of users 
<strong>with</strong> the permission to execute php code, even if the user is  allowed to edit 
other users' posts in general. With the observer activated you can ignore any security risk indicators 
in the table above.
</p>
<p>It is activated by <em>default</em> and strongly recommended to keep it activated. </p>
<p>
<?php

	if($php_exec_admin->options['observer_active'])
	{
	
?>
	<strong><img src="<?php echo PHP_EXECUTION_BASE_URL; ?>/assets/tick.png" style="vertical-align:middle" /> Post Edit Observer is <span style="color: #090">active</span></strong>
	<input type="submit" name="deactivate_observer" value="Deactivate Observer" class="button-secondary"/>
<?php

	}
	else
	{
	
?>
	<strong><img src="<?php echo PHP_EXECUTION_BASE_URL; ?>/assets/cross.png" style="vertical-align:middle" /> Post Edit Observer is <span style="color: #f30">deactivated</span></strong>
	<input type="submit" name="activate_observer" value="Activate Observer" class="button-primary"/>
<?php

	}
	
?>
</p>
<?php


} // end if($current_user_is_admin)
else
{
	if($current_user && $current_user->has_cap(PHP_EXECUTION_CAPABILITY))
		echo '<p><img src="'. PHP_EXECUTION_BASE_URL .'/assets/tick.png" style="vertical-align:middle" /> You have permission to embed php code into your posts and pages.</p>';
	else
		echo '<p><img src="'. PHP_EXECUTION_BASE_URL .'/assets/cross.png" style="vertical-align:middle" /> You currently have no permission to embed php code into your posts and pages.</p>';
}
?>
</form>
<p style="font-size:11px;margin-top:20px">(PHP Execution Version <?php echo PHP_EXECUTION_VERSION ?>)</p>
</div>