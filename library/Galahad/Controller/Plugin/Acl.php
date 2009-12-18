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
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * Provides a basic wrapper around an array of Entities
 * 
 * @category   Galahad
 * @package    Galahad_Controller
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
	/** @var string */
	private static $_role = 'guest';
	
	/** @var Zend_Acl */
	private $_acl;
	
	private $_authRouteName = null;
	private $_authRouteOptions = array(); // = array('controller' => 'account', 'action' => 'login');
	private $_authUrl = null;
	
	/**
	 * Set the current role
	 * 
	 * @param Zend_Acl_Role_Interface|string $role
	 */
	public static function setRole($role) {
		if (is_string($role)) {
			$role = new Zend_Acl_Role($role);
		}
		
		if (!$role instanceof Zend_Acl_Role_Interface) {
			throw new InvalidArgumentException('Galahad_Controller_Plugin_Acl::setRole() expects a string or an object that implements Zend_Acl_Role_Interface.');
		}
		
		self::$_role = $role;
	}
	
	/**
	 * Constructor
	 * 
	 * @param Zend_Acl $acl
	 */
	public function __construct(Zend_Acl $acl, $role = null)
	{
		$this->_acl = $acl;
		if (null != $role) {
			self::setRole($role);
		}
	}
	
	/**
	 * Sets the URL to redirect to if authentication is required
	 * 
	 * If you set the redirect as a URL, ensure that your ACL doesn't
	 * prevent access to that URL!
	 *
	 * @param string $url
	 * @return Galahad_Controller_Plugin_Acl
	 */
	public function setAuthUrl($url)
	{
		$this->_authUrl = $url;
		return $this;
	}
	
	/**
	 * Set the Route to redirect to if authentication is required
	 *
	 * @param array $urlOptions
	 * @param string $name
	 * @return Galahad_Controller_Plugin_Acl
	 */
	public function setAuthRoute(Array $urlOptions, $name = null)
	{
		$this->_authRouteOptions = $urlOptions;
		$this->_authRouteName = $name;
		return $this;
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
		$this->_ensureResource($resourceName);
		
		if (!$this->_acl->isAllowed(self::$_role, $resourceName, 'view')) {
			$this->redirect();
		}
	}
	
	public function redirect()
	{
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');

		if (!empty($this->_authRouteOptions) || null !== $this->_authRouteName) {
			$redirector->gotoRoute($this->_authRouteOptions, $this->_authRouteName);
		} else if (null !== $this->_authUrl) {
			$redirector->gotoUrl($this->_authUrl);
		} else {
			throw new Exception('No redirect login provided');
		}
	}
	
	protected function _ensureResource($resourceName)
	{
		if (!$this->_acl->has($resourceName)) {
			if (null != ($parentResourceName = $this->_getParentResourceName($resourceName))) {
				$this->_ensureResource($parentResourceName);
			}
			$this->_acl->addResource($resourceName, $parentResourceName);
		}
	}
	
	protected function _getParentResourceName($resourceName)
	{
		if ('mvc' == $resourceName) {
			return null;
		}
		
		$resourceName = substr($resourceName, 0, strrpos($resourceName, '.'));
		if ('' == $resourceName) {
			$resourceName = 'mvc';
		}
		
		return $resourceName;
	}
}