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
 * @package   Galahad_Payment
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * Abstract Response Class
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Payment_Adapter_Response_AuthorizeNet extends Galahad_Payment_Adapter_Response_Abstract
{
	/**
	 * Parameters sent to Authorize.net to produce this response
	 * 
	 * @var array
	 */
	protected $_apiParameters = array();
	
	/**
	 * Raw data returned by gateway
	 * 
	 * @var string
	 */
	protected $_rawData = null;
	
	/**
	 * Current response code
	 * 
	 * @var string
	 */
	protected $_responseCode = null;
	
	/**
	 * Friendly message
	 * 
	 * @var string
	 */
	protected $_friendlyMessage = null;
	
	/**
	 * Adapter message
	 * @var string
	 */
	protected $_adapterMessage = null;
	
	/**
	 * Constructor
	 * 
	 * @param array $data
	 * @param string $rawData
	 */
	public function __construct($rawData, $parameters = array())
	{
		// Construct from data passed by Authorize.net
		$this->_rawData = $rawData;
		$this->_apiParameters = $parameters;
		
		$this->_parseData($rawData);
	}
	
	/**
	 * Parse raw data returned by the gateway
	 * 
	 * @param string $data
	 * @return array
	 */
	abstract protected function _parseData($data);
	
	/**
	 * Get the parameters that were sent to Authorize.net to produce this response
	 * 
	 * @return array
	 */
	public function getParameters()
	{
		return $this->_apiParameters;
	}
	
	/**
	 * Is the transaction approved?
	 * 
	 * @return bool
	 */
	public function isApproved()
	{
		return ($this->_code == self::CODE_APPROVED);
	}
	
	/**
	 * Get a friendly message
	 * 
	 * This is a message that the adapter considers safe to show the
	 * end-user (something like "This transaction has been declined.")
	 * 
	 * @return string
	 */
	public function getFriendlyMessage()
	{
		return $this->_friendlyMessage;
	}
	
	/**
	 * Get the message set from the gateway
	 * 
	 * This is the message sent by the authorize.net gateway
	 * 
	 * @return string
	 */
	public function getAdapterCode()
	{
		return $this->getResponseCode();
	}
	
	/**
	 * Get the Authorize.net Response Code sent by the gateway
	 * 
	 * @link http://www.authorize.net/support/merchant/Transaction_Response/Response_Code_Details.htm
	 * @return int
	 */
	public function getResponseCode()
	{
		return $this->_responseCode;
	}
}