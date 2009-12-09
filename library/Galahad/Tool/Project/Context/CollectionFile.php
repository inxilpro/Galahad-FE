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
require_once 'Zend/Filter/Word/DashToCamelCase.php';

/**
 * Context for creating a Collection file
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Context_CollectionFile extends Zend_Tool_Project_Context_Filesystem_File 
{
    /**
     * @var string
     */
    protected $_filesystemName = 'CollectionName';
    
    /** @var string */
    protected $_collectionName = 'collectionName';
    
    /**
     * init()
     *
     * @return Galahad_Tool_Project_Context_CollectionFile
     */
    public function init()
    {
        $this->_collectionName = $this->_resource->getAttribute('collectionName');
        
        $filter = new Zend_Filter_Word_DashToCamelCase();
        $this->_filesystemName = $filter->filter($this->_collectionName) . '.php';
        
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
			'collectionName' => $this->getCollectionName()
        );
    }
    
    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'CollectionFile';
    }
    
    /**
     * getCollectionName()
     *
     * @return string
     */
    public function getCollectionName()
    {
        return $this->_collectionName;
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
        $className = $moduleName . '_Model_Collection_' . $filter->filter($this->_collectionName);
        
        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'fileName' => $this->getPath(),
            'classes' => array(
                new Zend_CodeGenerator_Php_Class(array(
                    'name' => $className,
                    'extendedClass' => 'Galahad_Model_Collection',
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
