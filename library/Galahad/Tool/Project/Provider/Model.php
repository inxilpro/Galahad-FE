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
 * @see Zend_Filter_Word_DashToUnderscore
 */
require_once 'Zend/Filter/Word/DashToUnderscore.php';

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
    public function create($name, $collectionIncluded = true, $dataMapperIncluded = true, $formIncluded = true, $tableIncluded = true, $module = null)
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
            if ($tableIncluded) {
            	// TODO: Do this in db table resource 
                $filter = new Zend_Filter_Word_DashToUnderscore();
                $tableName = $filter->filter($name);
                $dbTableResource = Galahad_Tool_Project_Provider_DbTable::createResource($this->_loadedProfile, $tableName, $module);
            }
        	if ($formIncluded) {
                $formResource = Galahad_Tool_Project_Provider_Form::createResource($this->_loadedProfile, $name, $module);
            }
        	if ($dataMapperIncluded) {
                $dataMapperResource = Galahad_Tool_Project_Provider_DataMapper::createResource($this->_loadedProfile, $name, $module);
            }
        	if ($collectionIncluded) {
                $collectionResource = Galahad_Tool_Project_Provider_Collection::createResource($this->_loadedProfile, $name, $module);
            }
            
            // TODO Add Properties via Zend_Tool_Project_Provider_ModelProperty
            // TODO Also add elements to form based on properties
            /*
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
        
        if (isset($dbTableResource)) {
            $this->_registry->getResponse()->appendContent('Creating DbTable for model ' . $name
                . ' at ' . $dbTableResource->getContext()->getPath());
            $dbTableResource->create();
        }
    	if (isset($formResource)) {
            $this->_registry->getResponse()->appendContent('Creating form for model ' . $name
                . ' at ' . $formResource->getContext()->getPath());
            $formResource->create();
        }
    	if (isset($dataMapperResource)) {
            $this->_registry->getResponse()->appendContent('Creating data mapper for model ' . $name
                . ' at ' . $dataMapperResource->getContext()->getPath());
            $dataMapperResource->create();
        }
    	if (isset($collectionResource)) {
            $this->_registry->getResponse()->appendContent('Creating collection for model ' . $name
                . ' at ' . $collectionResource->getContext()->getPath());
            $collectionResource->create();
        }
        
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
            // TODO: Generate models directory?  See DbTable...
            if ($moduleName) {
                $exceptionMessage = 'A model directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A model directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newModel = $modelsDirectory->createResource('modelFile', array(
        	'modelName' => $modelName, 
        	'moduleName' => $moduleName
        ));

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
