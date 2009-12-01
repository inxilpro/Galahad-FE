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
class Galahad_Tool_Project_Provider_DbTable extends Galahad_Tool_Project_Provider_Abstract
{
    /**
     * create()
     *
     * @todo Remember to namespace with Default_ if $module is NULL
     * @param string $name
     */
    public function create($tableName, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // determine if testing is enabled in the project
        // require_once 'Zend/Tool/Project/Provider/Test.php';
        // $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);

        if (self::hasResource($this->_loadedProfile, $tableName, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a db table named ' . $tableName);
        }

        try {
            $dbTableResource = self::createResource($this->_loadedProfile, $tableName, $module);
        } catch (Exception $e) {
            $response = $this->_registry->getResponse();
            $response->setException($e);
            return;
        }

        $this->_registry->getResponse()->appendContent('Creating a dbTable at ' . $dbTableResource->getContext()->getPath());
        $dbTableResource->create();
        
        $this->_storeProfile();
    }
    
	/**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $dbTableName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $tableName, $moduleName = null)
    {
        if (!is_string($tableName)) {
            throw new Zend_Tool_Project_Provider_Exception('Galahad_Tool_Project_Provider_DbTable::createResource() expects \"parentModel\" is the name of the dbTable\'s parent.');
        }

        $dbTablesDirectory = self::_getDbTablesDirectoryResource($profile, $moduleName);
        if (false == $dbTablesDirectory) {
            return false;
        }

        return (($dbTablesDirectory->search(array('dbTableFile' => array('tableName' => $tableName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }
    
	/**
     * createResource will create the dbTableFile resource at the appropriate location in the
     * profile.  NOTE: it is your job to execute the create() method on the resource, as well as
     * store the profile when done.
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $dbTableName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $tableName, $moduleName = null)
    {
        if (!is_string($tableName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_DbTable::createResource() expects \"tableName\" is the name of the table.');
        }

        if (!($dbTablesDirectory = self::_getDbTablesDirectoryResource($profile, $moduleName))) {
            if ($moduleName) {
                $exceptionMessage = 'A dbTable directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A dbTable directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newDbTable = $dbTablesDirectory->createResource('dbTableFile', array('tableName' => $tableName));
        return $newDbTable;
    }
    
	/**
     * _getDbTablesDirectoryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getDbTablesDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'dbTableDirectory';
        $dbTablesDirectory = $profile->search($profileSearchParams);
        
        if (!$dbTablesDirectory 
            || (null == $moduleName && $dbTablesDirectory->getParentResource()->getParentResource()->getContext() instanceof Zend_Tool_Project_Context_Zf_ModuleDirectory)) {
            $modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName);
            $dbTablesDirectory = $modelsDirectory->createResource('dbTableDirectory', array());
            $dbTablesDirectory->create();
            
            $projectProfileFile = $profile->search('ProjectProfileFile');
            $projectProfileFile->getContext()->save();
        }
        
        return $dbTablesDirectory;
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
