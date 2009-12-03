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
 * Generates a Zend_Form object
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Provider_Form extends Galahad_Tool_Project_Provider_Abstract
{
    /**
     * create()
     *
     * @todo Remember to namespace with Default_ if $module is NULL
     * @param string $name
     */
    public function create($formName, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // determine if testing is enabled in the project
        // require_once 'Zend/Tool/Project/Provider/Test.php';
        // $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);

        if (self::hasResource($this->_loadedProfile, $formName, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a form named ' . $formName);
        }

        try {
            $formResource = self::createResource($this->_loadedProfile, $formName, $module);
        } catch (Exception $e) {
            $response = $this->_registry->getResponse();
            $response->setException($e);
            return;
        }

        $this->_registry->getResponse()->appendContent('Creating a form at ' . $formResource->getContext()->getPath());
        $formResource->create();
        
        $this->_storeProfile();
    }
    
	/**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $formName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $formName, $moduleName = null)
    {
        if (!is_string($formName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_Form::createResource() expects \"formName\" is the name of the form\'s parent.');
        }

        $formsDirectory = self::_getApplicationDirectoryResource($profile, $moduleName);
        if (false == $formsDirectory) {
            return false;
        }

        return (($formsDirectory->search(array('formFile' => array('formName' => $formName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }
    
	/**
     * createResource will create the formFile resource at the appropriate location in the
     * profile.  NOTE: it is your job to execute the create() method on the resource, as well as
     * store the profile when done.
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $formName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $formName, $moduleName = null)
    {
        if (!is_string($formName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Form::createResource() expects \"parentModel\" is the name of the table.');
        }

        if (!($formsDirectory = self::_getApplicationDirectoryResource($profile, $moduleName))) {
            if ($moduleName) {
                $exceptionMessage = 'A form directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A form directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newForm = $formsDirectory->createResource('formFile', array('formName' => $formName));
        return $newForm;
    }
    
	/**
     * _getFormsDirectoryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getApplicationDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'formsDirectory';
        $formsDirectory = $profile->search($profileSearchParams);
        
        if (!$formsDirectory 
            || (null == $moduleName && $formsDirectory->getParentResource()->getParentResource()->getContext() instanceof Zend_Tool_Project_Context_Zf_ModuleDirectory)) {
            $applicationDirectory = $profile->search(array('applicationDirectory'));
            $formsDirectory = $applicationDirectory->createResource('formsDirectory', array());
            $formsDirectory->create();
            
            $projectProfileFile = $profile->search('ProjectProfileFile');
            $projectProfileFile->getContext()->save();
        }
        
        return $formsDirectory;
    }
}
