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
 * @see Zend_Tool_Project_Context_Interface
 */
require_once 'Zend/Tool/Project/Context/Interface.php';

/**
 * @see Zend_CodeGenerator_Php_Docblock
 */
require_once 'Zend/CodeGenerator/Php/Docblock.php';

/**
 * @see Zend_CodeGenerator_Php_Docblock_Tag_Param
 */
require_once 'Zend/CodeGenerator/Php/Docblock/Tag/Param.php';

/**
 * @see Zend_CodeGenerator_Php_Docblock_Tag_Return
 */
require_once 'Zend/CodeGenerator/Php/Docblock/Tag/Return.php';

/**
 * @see Zend_Reflection_File
 */
require_once 'Zend/Reflection/File.php';

/**
 * @see Zend_Filter_Word_DashToCamelCase
 */
require_once 'Zend/Filter/Word/UnderscoreToCamelCase.php';

/**
 * @see Zend_Filter_Word_DashToCamelCase
 */
require_once 'Galahad/Model/Entity.php';

/**
 * Context for creating a Galahad-style model
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Context_GalahadModelPropertyMethods implements Zend_Tool_Project_Context_Interface
{
    /**
     * @var Zend_Tool_Project_Profile_Resource
     */
    protected $_resource = null;
    
    /**
     * @var Zend_Tool_Project_Profile_Resource
     */
    protected $_formResource = null;
    
    /**
     * @var string
     */
    protected $_formPath = '';

    /**
     * @var string
     */
    protected $_element
    = null;
    
    /**
     * init()
     *
     * @return Galahad_Tool_Project_Context_GalahadModelPropertyMethods
     */
    public function init()
    {
        $this->_propertyName = $this->_resource->getAttribute('propertyName');
        
        $this->_resource->setAppendable(false);
        $this->_modelResource = $this->_resource->getParentResource();
        if (!$this->_modelResource->getContext() instanceof Galahad_Tool_Project_Context_GalahadModelFile) {
            require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception('GalahadModelPropertyMethods must be a sub resource of a ModelFile');
        }
        
        $this->_modelPath = $this->_modelResource->getContext()->getPath();
        
        // make the ModelFile node appendable so we can tack on the property methods.
        $this->_resource->getParentResource()->setAppendable(true);
        
        /*
         * This code block is now commented, its doing to much for init()
         *
        if ($this->_modelPath != '' && self::hasModelPropertyMethods($this->_modelPath, $this->_propertyName)) {
            require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception('An property named ' . $this->_propertyName . 'Property already exists in this model');
        }
        */
        
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
			'propertyName' => $this->getPropertyName()
        );
    }
    
    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'GalahadModelPropertyMethods';
    }
    
    /**
     * setResource()
     *
     * @param Zend_Tool_Project_Profile_Resource $resource
     * @return Galahad_Tool_Project_Context_GalahadModelPropertyMethods
     */
    public function setResource(Zend_Tool_Project_Profile_Resource $resource)
    {
        $this->_resource = $resource;
        return $this;
    }
    
    /**
     * setPropertyName()
     *
     * @param string $propertyName
     * @return Galahad_Tool_Project_Context_GalahadModelPropertyMethods
     */
    public function setPropertyName($propertyName)
    {
        $this->_propertyName = $propertyName;
        return $this;
    }
    
    /**
     * getPropertyName()
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->_propertyName;
    }
    
    /**
     * create()
     *
     * @return Galahad_Tool_Project_Context_GalahadModelPropertyMethods
     */
    public function create()
    {
        if (self::createPropertyMethods($this->_modelPath, $this->_propertyName) === false) {
            require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception(
                'Could not create property within model ' . $this->_modelPath 
                . ' with property name ' . $this->_propertyName);
        }
        return $this;
    }
    
    /**
     * delete()
     *
     * @return Galahad_Tool_Project_Context_GalahadModelPropertyMethods
     */
    public function delete()
    {
        // @todo do this
        return $this;
    }
    
    /**
     * createPropertyMethod()
     *
     * @param string $modelPath
     * @param string $propertyName
     * @param string $body
     * @return true
     */
    public static function createPropertyMethods($modelPath, $propertyName)
    {
        if (!file_exists($modelPath)) {
            return false;
        }
        
        $modelCodeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($modelPath, true, true);
        
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $methodName = $filter->filter($propertyName);
                
        $modelCodeGenFile->getClass()->setMethod(array(
            'name' => 'get' . $methodName,
            'body' => "return \$this->_getPropertyData('{$propertyName}');",
            'docblock' => array(
                'shortDescription' => "Gets the '{$propertyName}' property",
                'tags' => array(
                    'return' => new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'mixed',
                    )),
                ),
            ),
        ));
        
        $param = strtolower($methodName{0}) . substr($methodName, 1);
        $modelCodeGenFile->getClass()->setMethod(array(
            'name' => 'set' . $methodName,
            'parameters' => array(
                array('name' => $param),
            ),
            'body' => "return \$this->_setPropertyData('$propertyName', \${$param});",
            'docblock' => array(
                'shortDescription' => "Sets the '{$propertyName}' property",
                'tags' => array(
                    'param' => new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramname' => $param,
                        'datatype' => 'mixed',
                    )),
                    'return' => new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => $modelCodeGenFile->getClass()->getName(),
                    )),
                ),
            ),
        ));
        
        file_put_contents($modelPath, $modelCodeGenFile->generate());
        return true;
    }
    
    /**
     * hasModelPropertyMethods()
     *
     * @param string $modelPath
     * @param string $propertyName
     * @return bool
     */
    public static function hasPropertyMethods($modelPath, $propertyName)
    {
        if (!file_exists($modelPath)) {
            return false;
        }
        
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $methodName = $filter->filter($propertyName);
        
        $modelCodeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($modelPath, true, true);
        $class = $modelCodeGenFile->getClass();
        return ($class->hasMethod('get' . $methodName) && $class->hasMethod('set' . $methodName));
    }
}