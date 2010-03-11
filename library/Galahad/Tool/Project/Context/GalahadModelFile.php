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
 * @see Zend_Tool_Project_Context_Filesystem_File
 */
require_once 'Zend/Tool/Project/Context/Filesystem/File.php';

/**
 * @see Zend_CodeGenerator_Php_File
 */
require_once 'Zend/CodeGenerator/Php/File.php';

/**
 * @see Zend_Filter_Word_DashToCamelCase
 */
require_once 'Zend/Filter/Word/DashToCamelCase.php';

/**
 * Context for creating a Galahad-style model
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Context_GalahadModelFile extends Zend_Tool_Project_Context_Filesystem_File 
{
    /**
     * @var string
     */
    protected $_filesystemName = 'modelName';
    
    /** @var string */
    protected $_tableName = 'modelName';
    
    /** @var string */
    protected $_moduleName = 'Default';
    
    /**
     * init()
     *
     * @return Galahad_Tool_Project_Context_GalahadModelFile
     */
    public function init()
    {
        $this->_moduleName = $this->_resource->getAttribute('moduleName');
        $this->_modelName = $this->_resource->getAttribute('modelName');
        $this->_filesystemName = ucfirst($this->_modelName) . '.php';
        parent::init();
        return $this;
    }
    
    /**
     * getPersistentAttributes
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
			'modelName' => $this->getModelName()
        );
    }
    
    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'GalahadModelFile';
    }
    
    /**
     * getModelName()
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->_modelName;
    }
  
    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {

        $filter = new Zend_Filter_Word_DashToCamelCase();
        
        $className = ($this->_moduleName ? ucfirst($this->_moduleName) : 'Default');
        $className .= '_Model_' . $filter->filter($this->_modelName);
        
        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'fileName' => $this->getPath(),
            'classes' => array(
                new Zend_CodeGenerator_Php_Class(array(
                    'name' => $className,
                    'extendedClass' => 'Galahad_Model_Entity',
                    /*
                    'methods' => array(
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'fixMe',
                            'body' => '// FIXME',
                        ))
                    )
                    */
                ))
            )
        ));
        
        // store the generator into the registry so that the addProperty command can use the same object later
        Zend_CodeGenerator_Php_File::registerFileCodeGenerator($codeGenFile); // REQUIRES filename to be set
        return $codeGenFile->generate();
    }
    
    /**
     * getCodeGenerator()
     *
     * @return Zend_CodeGenerator_Php_Class
     */
    public function getCodeGenerator()
    {
        $codeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($this->getPath());
        $codeGenFileClasses = $codeGenFile->getClasses();
        $class = array_shift($codeGenFileClasses);
        return $class;
    }
    
}
