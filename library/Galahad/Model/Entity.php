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
abstract class Galahad_Model_Entity extends Galahad_Model
{
    /**
     * Stores entity's property data
     * @var array
     */
    protected $_data = array();
    
    /**
     * Gets a form object
     * Defaults to a form with the same name as the Entity
     * @param string $name
     * @return Zend_Form
     */
    public function getForm($name = null)
    {
        $namespace = Galahad::getClassNamespace($this);
        if (null == $name) {
            $name = Galahad::getClassType($this);
        } else {
            $name = ucfirst($name);
        }
        
        $className = "{$namespace}_Form_{$name}";
        
        if (!$form = self::getObjectFromCache($className)) {
            $form = new $className();
        }
        
        return $form;
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
}