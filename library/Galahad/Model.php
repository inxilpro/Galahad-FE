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

/**
 * Provides common model functionality
 * 
 * @category   Galahad
 * @package    Galahad_Model
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Model
{
	/**
     * Stores common objects like Forms and DAOs
     * @var array
     */
    protected static $_objectCache = array();
    
    /**
     * Gets namespace of class (Default_ or Admin_ for example)
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



