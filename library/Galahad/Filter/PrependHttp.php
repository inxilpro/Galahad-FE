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
 * @package   Galahad_Filter
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';

/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @category  Galahad
 * @package   Galahad_Filter
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Filter_PrependHttp implements Zend_Filter_Interface
{
	/**
     * Allowed schemes
     * 
     * This is pretty restrictive by default, but you could potentially
     * allow all sorts of schemes like ftp, nntp, rtsp, itms, etc. 
     *
     * @var array
     */
    protected $_allowedSchemes = array(
    	'http://',
    	'https://',
    	'mailto:', 
    );
    
    /**
     * Only prepend to otherwise valid URI's
     * 
     * @var bool
     */
    protected $_checkUri = true;

    /**
     * Constructor
     *
     * @param string|array $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp = array();
            switch (count($options)) {
            	case 2:
            		$temp['checkUri'] = $options[1];
            	case 1:
            		$temp['allowedSchemes'] = $options[0];
            }
            $options = $temp;
        }

        if (array_key_exists('allowedSchemes', $options)) {
            $this->setAllowedSchemes($options['allowedSchemes']);
        }
    	if (array_key_exists('checkUri', $options)) {
            $this->setCheckUri($options['checkUri']);
        }
    }
	
    /**
     * Gets the currently set allowed schemes
     * 
     * @return array
     */
    public function getAllowedSchemes()
    {
    	return $this->_allowedSchemes;
    }
    
	/**
     * Set the allowed schemes
     *
     * @param array $schemes
     * @return Galahad_Filter_PrependHttp
     */
    public function setAllowedSchemes(array $schemes)
    {
        if (empty($schemes)) {
        	$schemes = array('http://');
        }

        $this->_allowedSchemes;
        return $this;
    }
    
    /**
     * Get the checkUri option
     * 
     * @return bool
     */
    public function getCheckUri()
    {
    	return $this->_checkUri;
    }
    
    /**
     * Set the checkUri option
     * 
     * @param bool $checkUri
     * @return Galahad_Filter_PrependHttp
     * @throws Zend_Filter_Exception
     */
    public function setCheckUri($checkUri)
    {
    	if (!is_bool($checkUri)) {
    		require_once 'Zend/Filter/Exception.php';
    		throw new Zend_Filter_Exception('Galahad_Filter_PrependHttp::setCheckUri() expects a boolean parameter.');
    	}
    	
    	$this->_checkUri = $checkUri;
    	return $this;
    }
	
    /**
     * Filters a URI
     * 
     * @param string $value
     */
	public function filter($value)
	{
		$schemes = $this->_allowedSchemes;
		array_walk($schemes, array($this, '_escapeScheme'));
		$schemes = implode('|', $schemes);

		$pattern = "#^({$schemes})#i";
		if (!preg_match($pattern, $value)) {
			$temp = 'http://' . $value;
			if ($this->_checkUri) {
				if (!Zend_Uri::check($temp)) {
					$temp = $value;
				}
			}
			$value = $temp;
		}
		
		return $value;
	}
	
	/**
	 * Escapes all shchemes for preg 
	 * 
	 * @param string $scheme
	 */
	private function _escapeScheme($scheme)
	{
		return preg_quote($scheme, '#');
	}
}

