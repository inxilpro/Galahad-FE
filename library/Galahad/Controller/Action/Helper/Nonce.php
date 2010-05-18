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
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * Provides basic access to your ACL in your controllers
 * 
 * @category   Galahad
 * @package    Galahad_Controller
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Controller_Action_Helper_Nonce extends Zend_Controller_Action_Helper_Abstract
{
	/** @var Zend_Session_Namesapce */
	protected $_session;
	
	/**
	 * Constructor
	 * 
	 * @param Zend_Session_Abstract $session
	 */
	public function __construct(Zend_Session_Abstract $session = null)
	{
		if (null !== $session) {
			$this->_session = $session;
		} else {
			$this->_session = new Zend_Session_Namespace(__CLASS__);
		}
	}
	
	/**
	 * Get the current nonce or create one
	 * 
	 * @return integer
	 */
	public function getNonce()
	{
		if (!isset($this->_session->nonce)) {
			$this->_createNonce();
		}
		
		return $this->_session->nonce;
	}
	
	/**
	 * Check passed nonce against stored nonce
	 * 
	 * @param integer $nonce
	 * @return boolean
	 */
	public function checkNonce($nonce)
	{
		$currentNonce = $this->getNonce();
		$this->_createNonce();
		return ($currentNonce == $nonce);
	}
	
	/**
	 * Create a new nonce
	 * 
	 * @return integer
	 */
	protected function _createNonce()
	{
		$this->_session->nonce = rand(0, PHP_INT_MAX);
	}
}