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

/** @see Zend_Form_Decorator_File */
require_once 'Zend/Form/Decorator/File.php';

/**
 * Provides a basic wrapper around an array of Entities
 * 
 * @category   Galahad
 * @package    Galahad_Form
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Form_Decorator_ImageFile extends Zend_Form_Decorator_File
{	
	/**
     * Default placement: append
     * @var string
     */
    protected $_placement = 'PREPEND';
	
	/**
     * Render a form image file
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $content = parent::render($content);
        
        $element = $this->getElement();
    	$value = $element->getValue();
        
        if (null == $value) {
        	return $content;
        }
        
        $view = $element->getView();
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        
        // TODO: Maybe change these to instanceof tests
        if (method_exists($element, 'getBaseUrl')) {
        	$value = $element->getBaseUrl() . $value;
        }
        
        $width = '';
    	if (method_exists($element, 'getWidth')) {
        	$width = 'width="' . $element->getWidth() . '" ';
        }
        
        $height = '';
		if (method_exists($element, 'getHeight')) {
        	$height = 'height="' . $element->getHeight() . '" ';
        }
        
        $markup = '<img ' . $width . $height . 'src="' . $value . '" alt="' . 
        		htmlentities(rtrim($element->getLabel(), ':')) . '"'
        		. ($view->doctype()->isXhtml() ? ' /><br />' : '><br>'); // FIXME
        
        switch ($placement) {
            case self::APPEND:
                return $content . $separator . $markup;
			case self::PREPEND:
			default:
                return $markup . $separator . $content;
        }
    }
}