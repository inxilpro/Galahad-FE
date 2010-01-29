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
 * @package   Galahad
 * @copyright Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Form/Element/File.php';

/**
 * A form element that presents a file upload and an image preview if
 * a value exists for that element
 * 
 * @category   Galahad
 * @package    Galahad_Form
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Form_Element_ImageFile extends Zend_Form_Element_File
{    
    private $_baseUrl = '';
	private $_width = null;
	private $_height = null;
    
    public function init()
    {
    	$this->addPrefixPath('Galahad_Form_Decorator', 'Galahad/Form/Decorator', self::DECORATOR);
    	
    	$this->addValidators(array(
    		new Zend_Validate_File_Count(1),
        	// new Zend_Validate_File_IsImage(), // FIXME
    	));
    }
    
	/**
     * Load default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ImageFile')
                 ->addDecorator('Errors')
                 ->addDecorator('Description')
                 ->addDecorator('HtmlTag', array('tag' => 'dd'))
                 ->addDecorator('Label', array('tag' => 'dt'));
        }
    }
    
	/**
     * Set element value
     *
     * @param  mixed $value
     * @return Zend_Form_Element
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }
    
    /**
     * Set the base URL to prepend to images
     * 
     * @example setBaseUrl('/images/');
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
    	$this->_baseUrl = $baseUrl;
    }
    
    /**
     * Get the current base URL
     * @param string $baseUrl
     */
    public function getBaseUrl()
    {
    	return $this->_baseUrl;
    }
    
    public function setWidth($width)
    {
    	$this->_width = $width;
    }
    
    public function getWidth()
    {
    	return $this->_width;
    }
    
    public function setHeight($height)
    {
    	$this->_height = $height;
    }
    
    public function getHeight()
    {
    	return $this->_height;
    }
}