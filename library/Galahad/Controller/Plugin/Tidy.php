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
 * @package   Galahad_Controller
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * Tidy Up HTML Output
 * 
 * Probably don't want to use this in a production environment, but
 * can make debugging easier.
 *  * 
 * @category   Galahad
 * @package    Galahad_Controller
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Controller_Plugin_Tidy extends Zend_Controller_Plugin_Abstract
{
	const MODE_BEAUTIFY = 'beautify';
	const MODE_MINIFY = 'minify';
	const MODE_DEFAULT = self::MODE_BEAUTIFY;
	
	/**
	 * @var Tidy
	 */
	protected $_tidy = null;
	
	/**
	 * @var array
	 */
	protected $_config = array(
		'tidy-mark' => true,
		'indent' => true,
		'wrap' => 0,
		'break-before-br' => true,
		'vertical-space' => true,
	);
	
	protected $_minifyConfig = array(
		'clean' => true,
		'bare' => true,
		'hide-comments' => true,
		'wrap' => 0,
		'indent' => false,
		'break-before-br' => false,
	);
	
	/**
	 * Constructor
	 * 
	 * @param tidy|array $options
	 */
	public function __construct(array $config = null, $mode = self::MODE_DEFAULT)
	{
		if (!null == $config) { 
			$this->_config = $config;
		} else if ($mode == self::MODE_MINIFY) {
			$this->_config = $this->_minifyConfig; 
		}
	}
	
	public function getTidy()
	{
		if (null == $this->_tidy) {
			$this->_tidy = new tidy;
		}
		
		return $this->_tidy;
	}
	
	public function dispatchLoopShutdown()
	{
		$response = $this->getResponse();
		$tidy = $this->getTidy(); 
		$tidy->parseString($response->getBody(), $this->_config);
		$response->setBody((string) $tidy);
	}
}