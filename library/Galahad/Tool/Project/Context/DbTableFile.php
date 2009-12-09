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
 * @see Zend_Tool_Project_Context_Filesystem_File
 */
require_once 'Zend/Tool/Project/Context/Filesystem/File.php';

/**
 * @see Zend_CodeGenerator_Php_File
 */
require_once 'Zend/CodeGenerator/Php/File.php';

/**
 * @see Zend_Filter_Word_UnderscoreToCamelCase
 */
require_once 'Zend/Filter/Word/UnderscoreToCamelCase.php';

/**
 * @see Zend_Filter_Word_CamelCaseToUnderscore
 */
require_once 'Zend/Filter/Word/CamelCaseToUnderscore.php';

/**
 * Context for creating a DbTable file
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Context_DbTableFile extends Zend_Tool_Project_Context_Filesystem_File 
{
    /**
     * @var string
     */
    protected $_filesystemName = 'DbTableName';
    
    /** @var string */
    protected $_tableName = 'tableName';
    
    /**
     * init()
     *
     * @return Galahad_Tool_Project_Context_DbTableFile
     */
    public function init()
    {
        $this->_tableName = $this->_resource->getAttribute('tableName');
        
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $this->_filesystemName = $filter->filter($this->_tableName) . '.php';
        
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
			'tableName' => $this->getTableName()
        );
    }
    
    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'DbTableFile';
    }
    
    /**
     * getTableName()
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_tableName;
    }
  
    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {
        $moduleName = 'Default';
        $parent = $this->_resource->getParentResource()->getParentResource()->getParentResource()->getContext();
        if ($parent instanceof Zend_Tool_Project_Context_Zf_ModuleDirectory) {
            $moduleName = ucfirst($parent->getModuleName());
            // $className = ($this->_moduleName ? ucfirst($this->_moduleName) : 'Default');
        }
        
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $className = $moduleName . '_Model_DbTable_' . $filter->filter($this->_tableName);
        
        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'fileName' => $this->getPath(),
            'classes' => array(
                new Zend_CodeGenerator_Php_Class(array(
                    'name' => $className,
                    'extendedClass' => 'Galahad_Model_DbTable',
                    'properties' => array(
                        array(
                            'name' => '_name',
                            'visibility' => 'protected',
                            'defaultValue' => $this->_tableName,
                        ),
                    ),
                )),
            ),
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
