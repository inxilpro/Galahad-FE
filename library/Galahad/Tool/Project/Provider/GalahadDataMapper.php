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
 * Generates a data mapper object
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Provider_GalahadDataMapper extends Galahad_Tool_Project_Provider_Abstract
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
            throw new Zend_Tool_Project_Provider_Exception('This project already has a data mapper named ' . $name);
        }

        try {
            $dataMapperResource = self::createResource($this->_loadedProfile, $name, $module);
        } catch (Exception $e) {
            $response = $this->_registry->getResponse();
            $response->setException($e);
            return;
        }

        $this->_registry->getResponse()->appendContent('Creating a data mapper at ' . $dataMapperResource->getContext()->getPath());
        $dataMapperResource->create();
        
        $this->_storeProfile();
    }
    
	/**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $dataMapperName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $dataMapperName, $moduleName = null)
    {
        if (!is_string($dataMapperName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_GalahadDataMapper::createResource() expects \"dataMapperName\" is the name of the data mapper.');
        }

        $dataMappersDirectory = self::_getDataMappersDirectoryResource($profile, $moduleName);
        if (false == $dataMappersDirectory) {
            return false;
        }

        return (($dataMappersDirectory->search(array('galahadDataMapperFile' => array('dataMapperName' => $dataMapperName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }
    
	/**
     * createResource will create the dataMapperFile resource at the appropriate location in the
     * profile.  NOTE: it is your job to execute the create() method on the resource, as well as
     * store the profile when done.
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $dataMapperName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $dataMapperName, $moduleName = null)
    {
        if (!is_string($dataMapperName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_GalahadDataMapper::createResource() expects \"dataMapperName\" is the name of the dataMapper.');
        }

        if (!($dataMappersDirectory = self::_getDataMappersDirectoryResource($profile, $moduleName))) {
            if ($moduleName) {
                $exceptionMessage = 'A dataMapper directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A dataMapper directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newDataMapper = $dataMappersDirectory->createResource('galahadDataMapperFile', array('dataMapperName' => $dataMapperName));
        return $newDataMapper;
    }
    
	/**
     * _getDataMappersDirectoryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getDataMappersDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'dataMapperDirectory';
        $dataMapperDirectory = $profile->search($profileSearchParams);
        
        if (!$dataMapperDirectory 
            || (null == $moduleName && $dataMapperDirectory->getParentResource()->getParentResource()->getContext() instanceof Zend_Tool_Project_Context_Zf_ModuleDirectory)) {
            $modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName);
            $dataMapperDirectory = $modelsDirectory->createResource('dataMapperDirectory', array());
            $dataMapperDirectory->create();
            
            $projectProfileFile = $profile->search('ProjectProfileFile');
            $projectProfileFile->getContext()->save();
        }
        
        return $dataMapperDirectory;
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
