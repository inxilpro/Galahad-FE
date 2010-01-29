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
 * @copyright Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
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
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{
	/** @var Zend_Acl */
	private $_acl;
	
	/** @var Galahad_Controller_Plugin_Acl */
	private $_plugin = null;
	
	/**
	 * Constructor
	 * 
	 * @param Zend_Acl $acl
	 * @param Galahad_Controller_Plugin_Acl $plugin
	 */
	public function __construct(Zend_Acl $acl, Galahad_Controller_Plugin_Acl $plugin = null)
	{
		$this->_acl = $acl;
		$this->_plugin = $plugin;
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
	 * Redirect to the plugin's auth route/URL
	 */
	public function redirect()
	{
		$this->getPlugin()->redirect();
	}
	
	/**
	 * Gets an instance of Galahad_Controller_Plugin_Acl
	 * 
	 * @return Galahad_Controller_Plugin_Acl
	 */
	public function getPlugin()
	{
		if (null == $this->_plugin) {
			$this->_plugin = $this->getFrontController()->getPlugin('Galahad_Controller_Plugin_Acl');
		}
		
		return $this->_plugin;
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