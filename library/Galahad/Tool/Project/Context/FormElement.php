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
 * Context for creating form elements
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Context_FormElement implements Zend_Tool_Project_Context_Interface
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
    protected $_elementName = null;
    
    /**
     * init()
     *
     * @return Galahad_Tool_Project_Context_FormElement
     */
    public function init()
    {
        $this->_elementName = $this->_resource->getAttribute('elementName');
        
        $this->_resource->setAppendable(false);
        $this->_formResource = $this->_resource->getParentResource();
        if (!$this->_formResource->getContext() instanceof Galahad_Tool_Project_Context_FormFile) {
            require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception('FormElement must be a sub resource of a FormFile');
        }
        
        $this->_formPath = $this->_formResource->getContext()->getPath();
        
        // make the ModelFile node appendable so we can tack on the element methods.
        $this->_resource->getParentResource()->setAppendable(true);
        
        /*
         * This code block is now commented, its doing to much for init()
         *
        if ($this->_modelPath != '' && self::hasFormElement($this->_modelPath, $this->_elementName)) {
            require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception('An element named ' . $this->_elementName . 'Element already exists in this model');
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
			'elementName' => $this->getElementName()
        );
    }
    
    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'FormElement';
    }
    
    /**
     * setResource()
     *
     * @param Zend_Tool_Project_Profile_Resource $resource
     * @return Galahad_Tool_Project_Context_FormElement
     */
    public function setResource(Zend_Tool_Project_Profile_Resource $resource)
    {
        $this->_resource = $resource;
        return $this;
    }
    
    /**
     * setElementName()
     *
     * @param string $elementName
     * @return Galahad_Tool_Project_Context_FormElement
     */
    public function setElementName($elementName)
    {
        $this->_elementName = $elementName;
        return $this;
    }
    
    /**
     * getElementName()
     *
     * @return string
     */
    public function getElementName()
    {
        return $this->_elementName;
    }
    
    /**
     * create()
     *
     * @return Galahad_Tool_Project_Context_FormElement
     */
    public function create()
    {
        if (self::createElementCode($this->_formPath, $this->_elementName) === false) {
            require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception(
                'Could not create element within form ' . $this->_formPath 
                . ' with element name ' . $this->_elementName);
        }
        return $this;
    }
    
    /**
     * delete()
     *
     * @return Galahad_Tool_Project_Context_FormElement
     */
    public function delete()
    {
        // @todo do this
        return $this;
    }
    
    /**
     * createElementMethod()
     *
     * @param string $modelPath
     * @param string $elementName
     * @param string $body
     * @return true
     */
    public static function createElementCode($formPath, $elementName)
    {
        if (!file_exists($formPath)) {
            return false;
        }
        
        $formCodeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($formPath, true, true);
        $initMethod = $formCodeGenFile->getClass()->getMethod('init');
        
        echo "\n";
        var_export($initMethod);
        die("\n\n");
        
        /*
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $methodName = $filter->filter($elementName);
                
        $formCodeGenFile->getClass()->setMethod(array(
            'name' => 'get' . $methodName,
            'body' => "\t\treturn \$this->_getElementData('{$elementName}');",
            'docblock' => array(
                'shortDescription' => "Gets the '{$elementName}' element",
                'tags' => array(
                    'return' => new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'mixed',
                    )),
                ),
            ),
        ));
        
        $param = strtolower($methodName{0}) . substr($methodName, 1);
        $formCodeGenFile->getClass()->setMethod(array(
            'name' => 'set' . $methodName,
            'parameters' => array(
                array('name' => $param),
            ),
            'body' => "\t\t\$this->_setElementData('$elementName', \${$param});"
                    . "\nreturn \$this;",
            'docblock' => array(
                'shortDescription' => "Sets the '{$elementName}' element",
                'tags' => array(
                    'param' => new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                        'paramname' => $param,
                        'datatype' => 'mixed',
                    )),
                    'return' => new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                        'datatype' => 'Galahad_Entity',
                    )),
                ),
            ),
        ));
        */
        
        file_put_contents($formPath, $formCodeGenFile->generate());
        return true;
    }
    
    /**
     * hasFormElement()
     *
     * @param string $modelPath
     * @param string $elementName
     * @return bool
     */
    public static function hasElement($modelPath, $elementName)
    {
        if (!file_exists($modelPath)) {
            return false;
        }
        
        // FIXME Ñ Might need some fancy logic here.
        
        /*        
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $methodName = $filter->filter($elementName);
        
        $modelCodeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($modelPath, true, true);
        $class = $modelCodeGenFile->getClass();
        return ($class->hasMethod('get' . $methodName) && $class->hasMethod('set' . $methodName));
        */
    }
}