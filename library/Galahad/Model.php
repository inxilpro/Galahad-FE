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

/**
 * Provides common model functionality
 * 
 * @category   Galahad
 * @package    Galahad_Model
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Model
{
	const TYPE_ENTITY = 'entity';
	const TYPE_COLLECTION = 'collection';
	const TYPE_DBTABLE = 'dbtable';
	const TYPE_DATAMAPPER = 'datamapper';
	
	/**
     * Stores common objects like Forms and DAOs
     * @var array
     */
    protected static $_objectCache = array();
    
    /**
     * Gets namespace of class (Application_ or Admin_ for example)
     * 
     * @param string $className
     * @return string
     */
	public static function getClassNamespace($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        return substr($className, 0, strpos($className, '_'));
    }
    
    /**
     * Gets the type of model class (User or Entry for example)
     * 
     * @param string $className
     * @return string
     */
    public static function getClassType($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        return substr($className, strrpos($className, '_') + 1);
    }
    
	/**
     * Gets the name of a sibling class
     * 
     * For example, if supplied with Admin_Model_Collection_User and asked
     * for the Data Mapper, would return Admin_Model_Mapper_User
     * 
     * @param string $className
     * @param string siblingType
     * @return string
     */
    public static function getClassSibling($className, $siblingType)
    {
        $namespace = self::getClassNamespace($className);
        $type = self::getClassType($className);
        
        switch ($siblingType) {
        	case self::TYPE_COLLECTION:
        		return "{$namespace}_Model_Collection_{$type}";
        	case self::TYPE_DATAMAPPER:
        		return "{$namespace}_Model_Mapper_{$type}";
        	case self::TYPE_DBTABLE:
        		return "{$namespace}_Model_DbTable_{$type}";
        	case self::TYPE_ENTITY:
        		return "{$namespace}_Model_{$type}";
        }
        
        throw new Galahad_Model_Exception("No such model type: '{$siblingType}'");
    }
    
    /**
     * Retreives cached object
     * 
     * @param string $objectName
     * @return object|boolean Returns the object if it exists, else false
     */
    protected static function getObjectFromCache($objectName)
    {
        if (isset(self::$_objectCache[$objectName])) {
            return self::$_objectCache[$objectName];
        }
        
        return false;
    }
    
    /**
     * Adds an object to cache
     * 
     * @param object $object
     */
    protected static function addObjectToCache($object)
    {
        self::$_objectCache[get_class($object)] = $object;
    }
}



