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
 * @see Galahad_Tool_Project_Provider_Model
 */
require_once 'Galahad/Tool/Project/Provider/Model.php';

/**
 * Provides basic model scaffolding
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Provider_Models extends Galahad_Tool_Project_Provider_Abstract 
{
    public function create($configPath)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $config = $this->_loadConfig($configPath);
        
        foreach ($config as $modelName => $section) {
	        $modelName = ucfirst($modelName);
	        
	        /*	        
	        $this->out('Creating model ', false)
	             ->out($modelName, true, 'cyan');
	             
            foreach ($section['properties'] as $propertyName => $propertyOptions) {
                $propertyName = strtolower($propertyName);
                $this->out(" - with property ", false)
	             ->out($propertyName, false, 'red')
	             ->out(' of type ', false)
	             ->out($propertyOptions['type'], true, 'red');
            }
            
            $this->_createEntity($modelName, $path, $section);
            $this->_createService($modelName, $path, $section);
            */
	    }
    }
    
    /**
     * Loads a Zend_Config implementation
     * @param string $configPath
     */
    private function _loadConfig($configPath)
	{
	    if (!file_exists($configPath)) {
	        throw new Zend_Tool_Project_Provider_Exception("The file '{$configPath}' does not exist.");
	    }
	    
	    $extension = substr($configPath, -3);
	    
	    if ('ini' == $extension) {
	        require_once 'Zend/Config/Ini.php';
	        $config = new Zend_Config_ini($configPath);
	    } else if ('xml' == $extension) {
            require_once 'Zend/Config/Xml.php';
	        $config = new Zend_Config_Xml($configPath);
	    } else {
	        throw new Zend_Tool_Project_Provider_Exception('Your models configuration file must be either a .xml or .ini file.');
	    }
	    
	    return $config;
	}
}