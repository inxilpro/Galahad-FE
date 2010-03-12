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
 * @package   Galahad_Validate
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @category  Galahad
 * @package   Galahad_Validate
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Validate_Uri extends Zend_Validate_Abstract
{
	const INVALID = 'uriInvalid';
	const INVALID_FORMAT = 'uriInvalidFormat';
 
	/**
	 * @var array
	 */
    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given, value should be a string",
        self::INVALID_FORMAT => "'%value%' is no valid URI in the basic format http://domain.tld",
    );
	
    /**
     * Validates a URI
     * 
     * Returns true if value passes Zend_Uri::check()
     * 
     * @see Zend_Uri::check()
     * @param string $value
     */
	public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }
        
        $this->_setValue($value);
        
        if (Zend_Uri::check($value)) {
        	return true;
        }
        
        $this->_error(self::INVALID_FORMAT);
        return false;
    }
}



