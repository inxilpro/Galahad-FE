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
 * Provides basic model scaffolding
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Provider_Model extends Galahad_Tool_Project_Provider_Abstract
{
    /**
     * create()
     *
     * @todo Remember to namespace with Default_ if $module is NULL
     * @param string $name
     */
    public function create($name, $configFile = null, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // determine if testing is enabled in the project
        // require_once 'Zend/Tool/Project/Provider/Test.php';
        // $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);

        if (self::hasResource($this->_loadedProfile, $name, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a model named ' . $name);
        }

        try {
            $modelResource = self::createResource($this->_loadedProfile, $name, $module);
            /*
            if ($indexActionIncluded) {
                $indexActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'index', $name, $module);
                $indexActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', $name, $module);
            }
            if ($testingEnabled) {
                $testModelResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $name, 'index', $module);
            }
			*/
        } catch (Exception $e) {
            $response = $this->_registry->getResponse();
            $response->setException($e);
            return;
        }

        $this->_registry->getResponse()->appendContent('Creating a model at ' . $modelResource->getContext()->getPath());
        $modelResource->create();
        
        /*
        if (isset($indexActionResource)) {
            $this->_registry->getResponse()->appendContent('Creating an index action method in model ' . $name);
            $indexActionResource->create();
            $this->_registry->getResponse()->appendContent('Creating a view script for the index action method at ' . $indexActionViewResource->getContext()->getPath());
            $indexActionViewResource->create();
        }
        
        if ($testModelResource) {
            $this->_registry->getResponse()->appendContent('Creating a model test file at ' . $testModelResource->getContext()->getPath());
            $testModelResource->create();
        }
        */
        
        $this->_storeProfile();
    }
    
	/**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $modelName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $modelName, $moduleName = null)
    {
        if (!is_string($modelName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_Model::createResource() expects \"modelName\" is the name of a model resource to create.');
        }

        $modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName);
        if (false == $modelsDirectory) {
            // TODO: Should this be an exception or continue to return false?
            /**
            if ($moduleName) {
                $exceptionMessage = 'A model directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A model directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
            */
            return false;
        }
        return (($modelsDirectory->search(array('modelFile' => array('modelName' => $modelName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }
    
	/**
     * createResource will create the modelFile resource at the appropriate location in the
     * profile.  NOTE: it is your job to execute the create() method on the resource, as well as
     * store the profile when done.
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $modelName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $modelName, $moduleName = null)
    {
        if (!is_string($modelName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Model::createResource() expects \"modelName\" is the name of a model resource to create.');
        }

        if (!($modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName))) {
            if ($moduleName) {
                $exceptionMessage = 'A model directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A model directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newModel = $modelsDirectory->createResource('modelFile', array('modelName' => $modelName)); // TODO: Pass in properties here

        return $newModel;
    }
    
	/**
     * _getModelsDirectoryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getModelsDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'modelsDirectory';

        return $profile->search($profileSearchParams);
    }
}
