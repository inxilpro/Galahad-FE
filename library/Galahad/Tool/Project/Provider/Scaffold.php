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
 * @see Zend_Tool_Framework_Provider_Abstract
 */
require_once 'Zend/Tool/Framework/Provider/Abstract.php';

/**
 * Provides basic model scaffolding
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Provider_Scaffold extends Zend_Tool_Framework_Provider_Abstract 
{
    /**
     * @var array Array
     */
    private $_noHintTypes = array(
        'boolean',
        'integer',
        'float',
        'string',
        'null',
        'mixed',
    );
    
	public function create($config, $generateSql = false)
	{
	    $path = rtrim(getcwd(), DIRECTORY_SEPARATOR);
	    if (!file_exists($path . '/.zfproject.xml')) {
	        $this->error("No project found at '{$path}'");
	    }
	    
	    $config = $this->_getConfig($config);
	    $config = $config->toArray();
	    
	    $this->_ensureDirectoryStructure($path);
	    
	    foreach ($config as $modelName => $section) {
	        $modelName = ucfirst($modelName);
	        
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
	    }
	    
	    /*
	    while ("" != $property = $this->in('Enter property name (blank for done):')) {
	        while ("" == $type = $this->in('Enter property type:')) {}
	        
	        $this->out("Create property ", false);
	        $this->out($property, false, 'cyan');
	        $this->out(' of type ', false);
	        $this->out($type, true, 'cyan');
	        $this->out("");
	    }
	    */
	}
	
	private function _createService($modelName, $path, $options = array())
	{
	    $filename = "{$path}/application/services/{$modelName}.php";
        if ($this->_createFile($filename, $this->_generateServiceCode($modelName, $options))) {
            $this->out('Service file created: ', false)
                 ->out($filename, true, 'blue');
        }
	}
	
    private function _generateServiceCode($modelName, array $options)
	{
	    $code = "<?php \n\nclass Default_Service_{$modelName} extends Galahad_Service_Abstract\n{\n";
	    $code .= "}\n\n\n\n";
	    return $code;
	}
	
	private function _createEntity($modelName, $path, $options = array())
	{
	    $filename = "{$path}/application/models/{$modelName}.php";
        if ($this->_createFile($filename, $this->_generateEntityCode($modelName, $options))) {
            $this->out('Entity file created: ', false)
                 ->out($filename, true, 'blue');
        }
	}
	
    private function _generateEntityCode($modelName, array $options)
	{
	    $code = "<?php \n\nclass {$modelName} extends Galahad_Model_Entity\n{\n";
	    $code .= "\t/**\n\t * Array containing object's properties\n\t * @var array\n\t */\n\tprotected \$_data = array();\n";
	    $code .= $this->_generateEntityAccessors($options['properties']);
	    $code .= "}\n\n\n\n";
	    return $code;
	}
	
	private function _generateEntityAccessors(array $properties)
	{
	    $accessors = '';
	    foreach ($properties as $property => $propertyOptions) {
	        $type = $propertyOptions['type'];
    	    $prettyProperty = preg_replace_callback('/_([a-z])/', create_function('$matches', 'return strtoupper($matches[1]);'), $property);
    	    
    	    $typeHint = '';
    	    if (false === strpos($type, '|')) {
    	        if (!in_array($type, $this->_noHintTypes)) {
    	            $typeHint = "{$type} ";
    	        }
    	    }
    	    
    	    $accessors .= "\n\t/**\n\t * Sets the '{$property}' property\n\t * \n\t * @param {$type} \${$prettyProperty}\n\t */"
    	                . "\n\tpublic function set" . ucfirst($prettyProperty) . "({$typeHint}\${$prettyProperty})\n\t{"
                        . "\n\t\t\$this->_data['{$property}'] = \${$prettyProperty};\n\t}\n";
    	    $accessors .= "\n\t/**\n\t * Gets the '{$property}' property\n\t * \n\t * @return {$type}\n\t */"
    	                . "\n\tpublic function get" . ucfirst($prettyProperty) . "()\n\t{"
                        . "\n\t\treturn \$this->_data['{$property}'];\n\t}\n";
	    }
	    
	    return $accessors;
	}
	
	private function _getConfig($configPath)
	{
	    if (!file_exists($configPath)) {
	        $this->error("The file '{$configPath}' does not exist.");
	    }
	    
	    $extension = substr($configPath, -3);
	    
	    if ('ini' == $extension) {
	        require_once 'Zend/Config/Ini.php';
	        $config = new Zend_Config_ini($configPath);
	    } else if ('xml' == $extension) {
            require_once 'Zend/Config/Xml.php';
	        $config = new Zend_Config_Xml($configPath);
	    } else {
	        $this->error('Your configuration file must be either a .xml or .ini file.');
	    }
	    
	    return $config;
	}
	
	private function _ensureDirectoryStructure($path = '.')
	{
	    $directories = array(
	        '/application/',
	        '/application/forms/',
	        '/application/models/',
	        '/application/services/',
	    );
	    
	    foreach ($directories as $directory) {
	        if (!is_dir($path . $directory)) {
	            if (!mkdir($path . $directory, 0755, true)) {
	                $this->error("Unable to make directory '{$path}{$directory}'");
	            }
	        }
	    }
	    
	    return $this;
	}
	
	private function _createFile($filename, $contents)
	{
	    if (file_exists($filename)) {
            // $this->error("'{$filename}' already exists.  For security reasons this script does not overwrite files.  Please delete this file and try again.");
            if ('y' != strtolower($this->in("'{$filename}' already exists. Overwrite? [y/N]"))) {
                return;
            }
        }
        
        if (!$handle = fopen($filename, 'w')) {
            $this->error("Cannot open '{$filename}'.  Please check permissions and try again.");
        }
        
	    if (false === fwrite($handle, $contents)) {
            $this->error("Cannot write to '{$filename}'.  Please check permissions and try again.");
        }
        
        fclose($handle);
        return $this;
	}
	
	private function out($message, $separator = true, $color = null) 
	{
	    $opts = array('separator' => $separator);
	    if (null !== $color) {
	        $opts['color'] = $color;
	    }
	    
	    $this->_registry->getResponse()->appendContent($message, $opts);
	    return $this;
	}
	
	private function in($message)
	{
	    return $this->_registry->getClient()->promptInteractiveInput($message)->getContent();
	}
	
	private function error($message, $exception = null)
	{
	    require_once 'Zend/Tool/Framework/Client/Console/HelpSystem.php';
        $helpSystem = new Zend_Tool_Framework_Client_Console_HelpSystem();
        $helpSystem->setRegistry($this->_registry);
        $helpSystem->respondWithErrorMessage($message, $exception);
        exit(1);
	}
}
