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
 * Provides common functionality used by many classes in Galahad FE
 * 
 * @category   Galahad
 * @package    Galahad
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad
{
    public static function getClassNamespace($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        return substr($className, 0, strpos($className, '_'));
    }
    
    public static function getClassType($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        return substr($className, strrpos($className, _) + 1);
    }
}


