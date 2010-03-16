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
 * @package   Galahad_Acl
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * Provides additional ACL functionality
 * 
 * @category   Galahad
 * @package    Galahad_Acl
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Acl extends Zend_Acl
{
	/**
     * Recursively adds resources when in the format "namespace:parent.child"
     * 
     * For example, if passed "model:default.model.user" this function will ensure
     * that all of the following resources exist (will create them and add
     * them to ACL if they don't):
     * 
     *  - model:default.model.user
     *  - model:default.model
     *  - model:default
     *  - model:
     *  
     * This is particularly useful for allowing or denying access to
     * whole classes of models.  For example, if you wanted to deny access
     * to ALL models in the "Admin" module, you could set up your ACL with:
     * 
     * <code>
     * <?php
     * $acl->add('model:admin', 'model:');
     * $acl->deny('guest', 'model:admin');
     * ?>
     * </code>
     * 
     * Galahad_Acl-aware classes will take care of setting up their own ACL trees.
     * For example, Galahad_Model_Entity will take care of setting the "model:admin" resource
     * as a parent of "model:admin.model.topsecret" so that the rules cascade 
     * appropriately.
     * 
     * Please note that because most Galahad_Acl-aware classes modify the ACL at
     * query-time you must add a resource to the ACL before setting permissions
     * for that resource (the $acl-add() method in the above code example is necessary
     * even though within your implementation of Galahad_Model_Entity it is not necessary).
     * 
     * Also note that all namespaces should end with a colon (:).
     * 
     * @param string|Zend_Acl_Resource_Interface $resource
     * @param string|Zend_Acl_Resource_Interface $parent
     * @return Galahad_Acl
     */
	public function addResource($resource, $parent = null)
    {
    	// Build resource chain if necessary
    	if (null == $parent) {
	        if (is_string($resource)) {
	            $resource = new Zend_Acl_Resource($resource);
	        }
	
	        if (!$resource instanceof Zend_Acl_Resource_Interface) {
	            require_once 'Zend/Acl/Exception.php';
	            throw new Zend_Acl_Exception('addResource() expects $resource to be a string or of type Zend_Acl_Resource_Interface');
	        }
	
	        $resourceId = $resource->getResourceId();
	        
	        if (preg_match('/^(\w+):(\w+(?:\.\w+)*)$/i', $resourceId, $matches)) {
	        	$parent = $this->_getParentResourceName($matches[1], $matches[2]);
	        	if (!$this->has($parent)) {
	        		$this->addResource($parent);
	        	}
	        }
    	}
    	
    	return parent::addResource($resource, $parent);
    }
	
	/**
	 * Determines the parent resource name for an ACL resource
	 * 
	 * @param string $resourceName
	 * @return string|null
	 */
	private function _getParentResourceName($namespace, $resourceName)
	{
		$resourceName = substr($resourceName, 0, strrpos($resourceName, '.'));		
		return "{$namespace}:{$resourceName}";
	}
}