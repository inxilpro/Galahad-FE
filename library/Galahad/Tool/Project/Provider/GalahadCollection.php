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
 * Generates a collection object
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Provider_GalahadCollection extends Galahad_Tool_Project_Provider_Abstract
{
    /**
     * create()
     *
     * @todo Remember to namespace with Default_ if $module is NULL
     * @param string $name
     */
    public function create($name, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // determine if testing is enabled in the project
        // require_once 'Zend/Tool/Project/Provider/Test.php';
        // $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);

        if (self::hasResource($this->_loadedProfile, $name, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a collection named ' . $name);
        }

        try {
            $collectionResource = self::createResource($this->_loadedProfile, $name, $module);
        } catch (Exception $e) {
            $response = $this->_registry->getResponse();
            $response->setException($e);
            return;
        }

        $this->_registry->getResponse()->appendContent('Creating a collection at ' . $collectionResource->getContext()->getPath());
        $collectionResource->create();
        
        $this->_storeProfile();
    }
    
	/**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $collectionName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $collectionName, $moduleName = null)
    {
        if (!is_string($collectionName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_GalahadCollection::hasResource() expects \"collectionName\" is the name of the collection.');
        }

        $collectionsDirectory = self::_getCollectionsDirectoryResource($profile, $moduleName);
        if (false == $collectionsDirectory) {
            return false;
        }

        return (($collectionsDirectory->search(array('galahadCollectionFile' => array('collectionName' => $collectionName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }
    
	/**
     * createResource will create the collectionFile resource at the appropriate location in the
     * profile.  NOTE: it is your job to execute the create() method on the resource, as well as
     * store the profile when done.
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $collectionName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $collectionName, $moduleName = null)
    {
        if (!is_string($collectionName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Collection::createResource() expects \"collectionName\" is the name of the collection.');
        }

        if (!($collectionsDirectory = self::_getCollectionsDirectoryResource($profile, $moduleName))) {
            if ($moduleName) {
                $exceptionMessage = 'A collection directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A collection directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newCollection = $collectionsDirectory->createResource('galahadCollectionFile', array('collectionName' => $collectionName));
        return $newCollection;
    }
    
	/**
     * _getCollectionsDirectoryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getCollectionsDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'galahadCollectionDirectory';
        $collectionDirectory = $profile->search($profileSearchParams);
        
        if (!$collectionDirectory 
            || (null == $moduleName && $collectionDirectory->getParentResource()->getParentResource()->getContext() instanceof Zend_Tool_Project_Context_Zf_ModuleDirectory)) {
            $modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName);
            $collectionDirectory = $modelsDirectory->createResource('galahadCollectionDirectory', array());
            $collectionDirectory->create();
            
            $projectProfileFile = $profile->search('ProjectProfileFile');
            $projectProfileFile->getContext()->save();
        }
        
        return $collectionDirectory;
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
