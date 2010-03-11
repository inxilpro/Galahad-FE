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
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * @see Zend_Tool_Project_Provider_Abstract
 */
require_once 'Galahad/Tool/Project/Provider/Abstract.php';

/**
 * Provider for form properties
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Provider_GalahadFormElement extends Galahad_Tool_Project_Provider_Abstract
{
    /**
     * createResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $elementName
     * @param string $formName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $elementName, $formName, $type = 'text', $required = false, $moduleName = null)
    {
        if (!is_string($elementName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_GalahadFormElement::createResource() expects \"elementName\" is the name of a element resource to create.');
        }

        if (!is_string($formName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_GalahadFormElement::createResource() expects \"formName\" is the name of a form resource to create.');
        }

        $formFile = self::_getFormFileResource($profile, $formName, $moduleName);   
        $elementMethod = $formFile->createResource('galahadFormElement', array('elementName' => $elementName, 'elementType' => $type, 'required' => $required));
        return $elementMethod;
    }

    /**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $elementName
     * @param string $formName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $elementName, $formName, $moduleName = null)
    {
        if (!is_string($elementName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_GalahadFormElement::hasResource() expects \"elementName\" is the name of a element resource to create.');
        }
        
        if (!is_string($formName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_GalahadFormElement::hasResource() expects \"formName\" is the name of a form resource to create.');
        }
        
        $formFile = self::_getFormFileResource($profile, $formName, $moduleName);
        return (($formFile->search(array('galahadFormElement' => array('elementName' => $elementName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }

    /**
     * _getFormFileResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $formName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getFormFileResource(Zend_Tool_Project_Profile $profile, $formName, $moduleName = null)
    {
        $profileSearchParams = array();

        if (null != $moduleName && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'formsDirectory';
        $profileSearchParams['formFile'] = array('formName' => $formName);

        return $profile->search($profileSearchParams);
    }

    /**
     * create()
     *
     * @param string $name
     * @param string $formName
     * @param string $module
     */
    public function create($name, $formName, $type = 'text', $required = false, $module = null)
    {
        $this->_loadProfile();
        
        if (self::hasResource($this->_loadedProfile, $name, $formName, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This form (' . $formName . ') already has an element named (' . $name . ')');
        }
        
        $element = self::createResource($this->_loadedProfile, $name, $formName, $type, $required, $module);
        
        $this->_registry->getResponse()->appendContent(
            'Creating a element named ' . $name .
            ' inside form at ' . $element->getParentResource()->getContext()->getPath());
        $element->create();
        
        $this->_storeProfile();
    }
}


