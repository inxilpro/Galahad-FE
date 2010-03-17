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
 * @package   Galahad_Controller
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * MVC Access Control Plugin
 * 
 * Handles enforcing ACL restrictions on your standard MVC application.  All
 * MVC resources in the ACL should be in the form of:
 * 
 * mvc:
 *  - mvc:module (child of "mvc:")
 *    - mvc:module.controller (child of "mvc:module")
 *      - mvc:module.controller.action (child of "mvc:module.controller")
 *      
 * This allows for various levels of access controls on your MVC application.
 * For example, you can allow all access to "mvc:admin" to your staff, but disallow
 * access to "mvc:admin.users.delete" to everyone but administrators.
 * 
 * If you're using Galahad_Acl most MVC resources should be automatically generated
 * for you--you just need to add and set permissions for the ones you actually
 * use.
 * 
 * Please note that for granular permissions it is better to use the ACL
 * implementation in Galahad_Model_Entity.  The MVC ACL implementation is best
 * for broad permissions (like denying access to an entire module or controller).
 * If your models are used in different ways (within multiple controllers, or both
 * via the web and via an API/CLI) permissions within your domain logic will save
 * you from duplicating the same permissions at different access points.
 * 
 * Please note that most of the benefits of the plugin are made possible by
 * Galahad_Acl.  If you are using Zend_Acl you will have to manually generate all
 * the appropriate resource chains yourself.
 * 
 * MVC access control is also useful for controlling permissions on MVC resources
 * that may initiate costly logic within your domain before querying the ACL.
 * 
 * @category   Galahad
 * @package    Galahad_Controller
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
	/**
	 * ACL
	 *  
	 * @var Zend_Acl
	 */
	private $_acl;
	
	/**
     * Default role to use for ACL
     * 
     * @var string
     */
    protected static $_defaultRole = 'guest';
    
    /**
     * Role of user to query ACL for
     * 
     * @var string
     */
    protected $_role = null;
	
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
	 * Checks the current action against the ACL
	 * 
	 * Action resources should be named 'mvc:module.controller.action' with a parent
	 * resource of 'mvc:module.controller' (etc).  The default module (or a moduleless
	 * system) should use 'default' as the module.
	 * 
	 * @param Zend_Controller_Request_Abstract $request
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$module = $request->getModuleName();
		if (null == $module || '' == $module) {
			$module = 'default';
		}
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		
		$resourceName = "mvc:{$module}.{$controller}.{$action}";
		if (!$this->_acl->has($resourceName)) {
			$this->_acl->addResource($resourceName);
		}
		
		if (!$this->_acl->isAllowed($this->getRole(), $resourceName, 'dispatch')) {
			throw new Galahad_Acl_Exception('You are not authorized to access this action.');
		}
	}
	
	/**
     * Set the default role for all Entities
     * By default this is "guest"
     * 
     * @param mixed $role
     */
    public static function setDefaultRole($role)
    {
    	if (!$role = Galahad_Acl::extractRoleId($role)) {
    		throw new InvalidArgumentException('Invalid default role');
    	}
    	
    	self::$_defaultRole = $role;
    }
    
    /**
     * Get the current default role
     * 
     * @return mixed
     */
    public static function getDefaultRole()
    {
    	return self::$_defaultRole;
    }
    
    /**
     * Set the accessing user's role
     * 
     * @param mixed $role
     */
    public function setRole($role)
    {
    	// TODO: Should this just throw an exception?
    	if (!$role = Galahad_Acl::extractRoleId($role)) {
			$role = self::getDefaultRole();
    	}
    	
    	$this->_role = $role;
    }
    
    /**
     * Get the accessing user's role (and lazy load if necessary)
     * 
     * @return mixed
     */
    public function getRole()
    {
		if (null === $this->_role) {
			$auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
				$this->setRole($auth->getIdentity());
            } else {
            	$this->setRole(self::getDefaultRole());
            }
        }

        return $this->_role;
    }
}