<?php

class php_execution_admin
{

	/**
	 * php execution options
	 */		
	var $options = array();

	/**
	 * constructor
	 */	
	function php_execution_admin()
	{
		$this->options = get_option(PHP_EXECUTION_OPTION);
	}

	/**
	 * checks wether an item (user or role) has a cap set
	 */
	function item_has_cap(&$obj, $cap_name)
	{
		return $obj->has_cap($cap_name);
	}

	/**
	 * checks wether an item (user or role) has
	 * at least one cap of a list of cap_names set
	 */
	function item_has_either_caps(&$obj, $cap_names)
	{
		foreach($cap_names as $cap_name)
		{
			if($this->item_has_cap($obj,$cap_name))
				return true;
		}
		return false;
	}

	/**
	 * checks wether an item (user or role) has
	 * all caps in a list of cap_names set
	 */
	function item_has_all_caps(&$obj, $cap_names)
	{
		foreach($cap_names as $cap_name)
		{
			if(!$this->item_has_cap($obj,$cap_name))
				return false;
		}
		return true;	
	}

	/**
	 * removes a list of caps from an item
	 * cap_names = an array of caps
	 * obj = either an user or a role object
	 */
	function remove_item_caps(&$obj, $cap_names)
	{
		foreach($cap_names as $cap_name)
		{
			/*
			 * if($obj->caps) => obj = WP_User
			 * Bypasses a bug in WP_User->remove_cap
			 * WP_User->remove_cap only removes cap when it has a value set or a value != 0
			 * 
			 * WP BUG: capabilities.php [668]
			 *   if ( empty( $this->caps[$cap] ) ) return; 
			 * should be
			 *   if ( !isset( $user->caps[$cap_name] ) ) return;
			 */
			if($obj->caps) $obj->caps[$cap_name] = 1;

			$obj->remove_cap($cap_name);
		}
	}

	/**
	 * adds a list of caps with the value true
	 * cap_names = an array of caps
	 * obj = either an user or a role object
	 */
	function add_item_caps(&$obj, $cap_names)
	{
		foreach($cap_names as $cap_name)
		{
			$obj->add_cap($cap_name);
		}
	}

	/**
	 * sets a list of caps to a specific value
	 * cap_names = an array of caps
	 * obj = either an user or a role object
	 */
	function set_item_caps(&$obj, $cap_names, $value)
	{
		foreach($cap_names as $cap_name)
		{
			$obj->add_cap($cap_name, $value);
		}
	}

	/**
	 * checks wether one of the roles of a user has a defined cap set
	 */
	function user_has_role_cap(&$user, $cap_name)
	{
		global $wp_roles;
		
		$role_names = $user->roles;
		foreach($role_names as $role_name)
		{
			$role = $wp_roles->get_role($role_name);
			if($this->item_has_cap($role,$cap_name))
				return true;
		}
		return false;
	}

	/**
	 * checks wether at least one of a list of caps is set in the roles of a user
	 * cap_names = array of cap names
	 */
	function user_has_either_role_caps(&$user, $cap_names)
	{
		global $wp_roles;
		
		$role_names = $user->roles;
		foreach($role_names as $role_name)
		{
			$role = $wp_roles->get_role($role_name);
			if($this->item_has_either_caps($role,$cap_names))
				return true;
		}
		return false;
	}

	/**
	 * checks wether a list of caps is set in the roles of a user
	 * cap_names = array of cap names
	 */
	function user_has_all_role_caps(&$user, $cap_names)
	{
		foreach($cap_names as $cap_name)
		{
			if(!$this->user_has_role_cap($user,$cap_name))
				return false;
		}
		return true;
	}
	 
	/**
	 * toggle an array of capabilties (cap_names)
	 */
	function toggle_caps(&$user, $cap_names, $enable=true)
	{
		$user_has_role_cap = $this->user_has_either_role_caps($user,$cap_names);
		$user_has_cap = $this->item_has_either_caps($user,$cap_names);
		
		// enable
		if($enable && !$user_has_cap)
		{
			// permission is explicitly denied => we return to role permission as it is currently granted
			if($user_has_role_cap)
				$this->remove_item_caps($user,$cap_names);
			// permission is denied by role => we explicitly grant permission for specific user
			else
				$this->add_item_caps($user,$cap_names);
		
			return true; // caps changed
		}
		// disable
		else if(!$enable && $user_has_cap)
		{
			// permission is granted by role => we explicitly deny permission for specific user
			if($user_has_role_cap)
				$this->set_item_caps($user, $cap_names, 0);
			// permission is explicitly granted => we return to role permission as it is currently denied
			else
				$this->remove_item_caps($user,$cap_names);
	
			return true; // caps changed
		}
		return false;
	}
	
	/**
	 * checks wether a capability is inherited from a role or set explicitly for a user
	 */
	function is_explicit_cap(&$user, $cap_name)
	{
		return ($this->user_has_role_cap($user, $cap_name) != $this->item_has_cap($user, $cap_name));
	}

}

?>