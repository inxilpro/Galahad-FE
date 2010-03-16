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

/** @see Galahad_Model */
require_once 'Galahad/Model.php';

/** @see Zend_Filter_Word_UnderscoreToCamelCase */
require_once 'Zend/Filter/Word/UnderscoreToCamelCase.php';

/** @see Zend_Filter_Word_SeparatorToSeparator */
require_once 'Zend/Filter/Word/SeparatorToSeparator.php';

/**
 * Provides common model functionality
 * 
 * @category   Galahad
 * @package    Galahad_Model
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Model_Entity
	extends Galahad_Model
	implements Zend_Acl_Resource_Interface
{
    /**
     * Stores entity's property data
     * @var array
     */
    protected $_data = array();
    
    /**
     * ACL
     * 
     * @var Zend_Acl
     */
    protected $_acl = null;
    
    /**
     * Default ACL
     * 
     * @var Zend_Acl
     */
    protected static $_defaultAcl;
    
    /**
     * Resource ID for ACL
     * @var string
     */
    protected $_resourceId = null;
    
    /**
     * Default identity string to use for ACL
     * 
     * @var string
     */
    protected static $_defaultRole = 'guest';
    
    /**
     * Identity of user accessing this model
     * 
     * @var string
     */
    protected $_role = null;
    
    /**
     * Basic constructor functionality
     * 
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if (null !== $data) {
            $this->reset($data);
        }
    }
    
    /**
     * Basic persistence functionality
     * 
     * @return boolean
     */
    public function save()
    {
        $dataMapper = $this->getDataMapper();
        return $dataMapper->save($this);
    }
    
    /**
     * Resets the entity with new data
     * 
     * @param array $data
     * @return Galahad_Model_Entity
     */
    public function reset(array $data)
    {
    	$filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        foreach ($data as $property => $value) {
            $property = $filter->filter($property);
            $method = "set{$property}";
            
            // TODO: Refactor?
            if (!method_exists($this, $method)) {
            	// Where foreign key, set the relationship
            	if ('Id' == substr($method, -2)) {
            		$method = substr($method, 0, -2);
            		if (!method_exists($this, $method)) {
            			throw new BadMethodCallException("No property '$property' exists");
            		}
            	} else {
            		throw new BadMethodCallException("No property '$property' exists");
            	}
            }
            
            $this->$method($value);
        }
        
        return $this;
    }
    
    /**
     * Gets the model's property data
     * 
     * Calls each property's getter so that if you have any
     * custom logic for that property it will be applied
     * 
     * @return array
     */
    public function toArray()
    {
    	$data = array();
    	$filter = new Zend_Filter_Word_UnderscoreToCamelCase();
    	foreach ($this->_data as $key => $value) {
    		$method = 'get' . $filter->filter($key);
    		// TODO: Call toArray on returned object if instanceof Galahad_Model_Entity
    		$data[$key] = $this->$method(); 
    	}
    	
    	return $data;
    }
    
	/**
     * Gets a Data Mapper object
     * 
     * @todo Might want to refactor the get[Object] methods
     * @param string $name
     * @return Galahad_Model_DataMapper
     */
    public function getDataMapper($name = null)
    {
        $namespace = self::getClassNamespace($this);
        if (null == $name) {
            $name = self::getClassType($this);
        } else {
            $name = ucfirst($name);
        }
        
        $className = "{$namespace}_Model_DataMapper_{$name}";
        
        if (!$dataMapper = self::getObjectFromCache($className)) {
            $dataMapper = new $className();
            self::addObjectToCache($dataMapper);
        }
        
        return $dataMapper;
    }
    
    /**
     * Gets a form object
     * Defaults to a form with the same name as the Entity
     * 
     * @todo Might want to refactor the get[Object] methods
     * @param string $name
     * @return Zend_Form
     */
    public function getForm($name = null)
    {
        $namespace = self::getClassNamespace($this);
        if (null == $name) {
            $name = self::getClassType($this);
        } else {
            $name = ucfirst($name);
        }
        
        $className = "{$namespace}_Form_{$name}";
        
        if (!$form = self::getObjectFromCache($className)) {
            $form = new $className();
            self::addObjectToCache($form);
        }
        
        return $form;
    }
    
    /**
     * Use this ACL if none supplied
     * 
     * @param Zend_Acl $acl
     */
    public function setDefaultAcl(Zend_Acl $acl)
    {
    	self::$_defaultAcl = $acl;
    }
    
    /**
     * Get default ACL
     * 
     * @return Zend_Acl
     */
    public function getDefaultAcl()
    {
    	return self::$_defaultAcl;
    }
    
    /**
     * Set the ACL
     * 
     * @param Zend_Acl $acl
     */
    public function setAcl(Zend_Acl $acl)
    {
    	$this->_acl = $acl;
		if (!$this->_acl->has($this->getResourceId())) {
			$this->_acl->add($this);
		}
		$this->_initAcl($this->_acl);
    	return $this;
    }
    
    /**
     * Gets the current ACL or creates a new one
     * 
     * @return Zend_Acl
     */
    public function getAcl()
    {
    	if (null === $this->_acl) {
    		// Lazy Load ACL
    		if (null !== ($defaultAcl = self::getDefaultAcl())) {
    			$this->setAcl($defaultAcl);
    		} else {
    			$this->setAcl(new Galahad_Acl());
    		}
    	}
    	
    	return $this->_acl;
    }
    
    /**
     * Initialize the ACL for your model (subclass this)
     * 
     * Example:
     * <code>
     * <?php
     * protected function _initAcl(Zend_Acl $acl)
     * {
     *      $acl->allow('guest', $this, array('view'));
     *      return $this;
     * }
     * ?>
     * </code>
     * 
     * @return Galahad_Model_Entity
     */
    protected function _initAcl(Zend_Acl $acl)
    {
    	return $this;
    }
    
    /**
     * Set the model's resource ID
     * 
     * @param string $resourceId
     * @return Galahad_Model_Entity
     */
    public function setResourceId($resourceId)
    {
    	// TODO: Verify $resourceId type
    	$this->_resourceId = $resourceId;
    	return $this;
    }
    
    /**
     * Get the model's resource ID
     * 
     * Default resource IDs are in the format:
     * Default_Model_User -> model:default.model.user
     * 
     * @see Zend_Acl_Resource_Interface
     * @see Galahad_Model_Entity::_ensureResource()
     * @return string
     */
    public function getResourceId()
    {
    	if (null === $this->_resourceId) {
    		$filter = new Zend_Filter_Word_SeparatorToSeparator('_', '.');
    		$className = strtolower(get_class($this));
    		$this->setResourceId('model:' . $filter->filter(str_replace('model_', '', $className)));
    	}
    	
    	return $this->_resourceId;
    }
    
    /**
     * Set the default role for all Entities
     * By default this is "guest"
     * 
     * @param mixed $role
     */
    public static function setDefaultRole($role)
    {
    	if (!$role = self::_extractRole($role)) {
    		throw new InvalidArgumentException('Invalid default role'); // TODO: Custom Exception
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
    	if (!$role = self::_extractRole($role)) {
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
            }
            
            $this->setRole(self::getDefaultRole());
        }

        return $this->_role;
    }
    
	/**
     * Determine if a passed role is valid
     * 
     * @link http://weierophinney.net/matthew/archives/201-Applying-ACLs-to-Models.html
     * @param mixed $role
     */
    private static function _extractRole($role)
    {
    	if (is_array($role) && isset($role['role'])) {
    		return $role['role'];
    	} elseif (is_scalar($role) && !is_bool($role)) {
    		return $role;
    	} elseif ($role instanceof Zend_Acl_Role_Interface) {
    		return $role;
    	}
    	
    	return false;
    }
    
    /**
     * Validates data based on a Zend_From object with the Entity's name
     * @param array $data
     * @return boolean
     */
    public function isValid(array $data)
    {
        $form = $this->getForm();
        if ($form->isValid($data)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Sets the value for a given property
     * 
     * @param string $property
     * @param mixed $value
     */
    protected function _setPropertyData($property, $value)
    {
        $this->_data[$property] = $value;
        return $this;
    }
    
    /**
     * Gets the value for a given property
     * Optionally returns a default (null if not set)
     * 
     * @param string $property
     * @param mixed $default
     */
    protected function _getPropertyData($property, $default = null)
    {
    	// TODO: If property = xxxxx_id try to load model "xxxxx" with primary key of property value
    	// That is, if the value is not a model object (or perhaps if it's an integer)
    	
        if (!isset($this->_data[$property])) {
            return $default;
        }
        
        return $this->_data[$property];
    }
}