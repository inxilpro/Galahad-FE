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
 * @package   Galahad_Tool
 * @copyright Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * @see Zend_Tool_Project_Provider_Abstract
 */
require_once 'Galahad/Tool/Project/Provider/Abstract.php';

/**
 * Provider for model properties
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Provider_ModelProperty extends Galahad_Tool_Project_Provider_Abstract
{
    /**
     * createResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $propertyName
     * @param string $modelName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $propertyName, $modelName, $moduleName = null)
    {
        if (!is_string($propertyName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_ModelProperty::createResource() expects \"propertyName\" is the name of a property resource to create.');
        }

        if (!is_string($modelName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_ModelProperty::createResource() expects \"modelName\" is the name of a model resource to create.');
        }

        $modelFile = self::_getModelFileResource($profile, $modelName, $moduleName);   
        $propertyMethod = $modelFile->createResource('ModelPropertyMethods', array('propertyName' => $propertyName));
        return $propertyMethod;
    }

    /**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $propertyName
     * @param string $modelName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $propertyName, $modelName, $moduleName = null)
    {
        if (!is_string($propertyName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_ModelProperty::hasResource() expects \"propertyName\" is the name of a property resource to create.');
        }
        
        if (!is_string($modelName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_ModelProperty::hasResource() expects \"modelName\" is the name of a model resource to create.');
        }
        
        $modelFile = self::_getModelFileResource($profile, $modelName, $moduleName);
        return (($modelFile->search(array('modelPropertyMethods' => array('propertyName' => $propertyName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }

    /**
     * _getModelFileResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $modelName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getModelFileResource(Zend_Tool_Project_Profile $profile, $modelName, $moduleName = null)
    {
        $profileSearchParams = array();

        if (null != $moduleName && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'modelsDirectory';
        $profileSearchParams['modelFile'] = array('modelName' => $modelName);

        return $profile->search($profileSearchParams);
    }

    /**
     * create()
     *
     * @param string $name
     * @param string $modelName
     * @param string $module
     */
    public function create($name, $parentModel, $addToForm = true, $module = null)
    {
        $this->_loadProfile();
        
        if (self::hasResource($this->_loadedProfile, $name, $parentModel, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This model (' . $parentModel . ') already has an property named (' . $name . ')');
        }
        
        $propertyMethod = self::createResource($this->_loadedProfile, $name, $parentModel, $module);
        
        $this->_registry->getResponse()->appendContent(
            'Creating a property named ' . $name .
            ' inside model at ' . $propertyMethod->getParentResource()->getContext()->getPath());
        $propertyMethod->create();
        
        if ($addToForm) {
        	$formElementResource = Galahad_Tool_Project_Provider_FormElement::createResource($this->_loadedProfile, $name, $parentModel, 'text', false, $module);
			$this->_registry->getResponse()->appendContent('Adding element to associated form for property ' . $name);
			$formElementResource->create();
        }
        
        $this->_storeProfile();
    }
}


