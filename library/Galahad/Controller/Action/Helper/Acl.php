<?php
/**
 * This file is part of the Galahad Framework Extension.
 * 
 * The Galahad Framework Extension is free software: you can redistribute 
 * it and/or modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation, either version 3 of the 
 * License, or (at your option) any later version.
 * 
 * The Galahad Framework Extension is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU 
 * General Public License for more details.
 * 
 * @category  Galahad
 * @package   Galahad
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * Provides basic access to your ACL in your controllers
 * 
 * @category   Galahad
 * @package    Galahad_Controller
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{
	/** @var Zend_Acl */
	private $_acl;
	
	/**
	 * Constructor
	 * 
	 * @param Zend_Acl $acl
	 */
	public function __construct(Zend_Acl $acl)
	{
		$this->_acl = $acl;
	}
	
	/**
	 * Check against ACL (proxy to isAllowed)
	 * 
	 * @param string|Zend_Acl_Role $role
	 * @param string|Zend_Acl_Resource $resource
	 * @param string $privilege
	 */
	public function direct($role = null, $resource = null, $privilege = null)
	{
		return $this->_acl->isAllowed($role, $resource, $privilege);
	}
	
	/**
	 * Check if currently authenticated user is allowed
	 * 
	 * @see Galahad_Model_Entity::_extractRole() Maybe this should use this?
	 * @param string|Zend_Acl_Resource_Interface $resource
	 * @param string $privilege
	 */
	public function authIdentityIsAllowed($resource = null, $privilege = null)
	{
		$role = null;
		
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			if (is_string($identity) || $identity instanceof Zend_Acl_Role_Interface) {
				$role = $identity;
			}
		}
		
		return $this->_acl->isAllowed($role, $resource, $privilege);
	}
	
	/**
	 * Pass anything on to the ACL that's not defined
	 * 
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments)
	{
		return call_user_method_array($name, $this->_acl, $arguments);
	}
}