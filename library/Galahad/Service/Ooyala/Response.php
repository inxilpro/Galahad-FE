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
 * @package   Galahad_Service
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * Provides access to the Ooyala APIs
 *
 * @category   Galahad
 * @package    Galahad_Service
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Service_Ooyala_Response
{
	/**
	 * Raw XML Data
	 * 
	 * @var string
	 */
	protected $_raw;
	
	/**
	 * Parsed XML Data
	 *
	 * @var SimpleXMLElement
	 */
	protected $_data;
	
	/**
	 * Status of the response
	 *
	 * True if the response is valid, false if not
	 *
	 * @var boolean
	 */
	protected $_status = null;
	
	/**
	 * Error message sent from server
	 *
	 * @var string
	 */
	protected $_errorMessage;
	
	/**
	 * Original request
	 *
	 * @var array
	 */
	protected $_request = array();
	
	/**
	 * The service that created this response
	 *
	 * @var Galahad_Service_Ooyala
	 */
	protected $_service;

	/**
	 * Constructor
	 *
	 * @param string $responseData
	 */
	public function __construct($responseData)
	{
		if ($responseData instanceof Zend_Http_Response) {
			if (! $responseData->isSuccessful()) {
				$this->_status = false;
				$this->_errorMessage = 'HTTP Error: ' . $responseData->getMessage();
			}
			$responseData = $responseData->getBody();
		}
		
		if (! is_string($responseData)) {
			$this->_throwException(
				'Galahad_Ooyala_Response::__construct() accepts a Zend_Http_Response object or a string.');
		}
		
		$this->_raw = trim($responseData);
		unset($responseData);
		
		$this->_status = $this->_process();
	}

	protected function _process()
	{
		return $this->_processXml();
	}

	protected function _processXml()
	{
		$this->_data = @simplexml_load_string($this->_raw);
		
		if (! $this->_data instanceof SimpleXMLElement) {
			$this->_errorMessage = $this->_raw;
			return false;
		}
		
		if ('result' == $this->_data->getName() && 'failure' == $this->_data['code']) {
			$this->_errorMessage = (string) $this->_data;
			return false;
		}
		
		return true;
	}

	protected function _processSimple()
	{
		if ("OK" !== $this->_raw) {
			$this->_errorMessage = $this->_raw;
			return false;
		}
		
		return true;
	}
	
	public function toArray($node = null, &$array = array())
	{
		if (null == $node) {
			$node = $this->_data;
		}
		
		if ($node instanceof SimpleXMLElement && !count($node)) {
			return (string) $node;
		}

		foreach ($node as $i => $item) {
			if (isset($array[$i])) {
				if (!is_array($array[$i])) {
					$array[$i] = array($array[$i]);
				}
				$array[$i][] = $this->toArray($item, $array[$i]);
			} else {
				$array[$i] = $this->toArray($item);
			}
		}
		
		return $array;
	}

	/**
	 * Get the status of the response
	 *
	 * @return boolean
	 */
	public function getStatus()
	{
		return $this->_status;
	}

	/**
	 * Get the error message if the response is an error
	 *
	 * @return string
	 */
	public function getErrorMessage()
	{
		return $this->_errorMessage;
	}

	/**
	 * Get Raw XML
	 *
	 * @return string
	 */
	public function getRawResponse()
	{
		return $this->_raw;
	}

	/**
	 * Get Simple XML Element
	 *
	 * @return SimpleXMLElement
	 */
	public function getSimpleXML()
	{
		return $this->_data;
	}

	/**
	 * Set the request data used to create this response
	 *
	 * @param array $request
	 */
	public function setRequest(array $request)
	{
		$this->_request = $request;
	}

	/**
	 * Set the service used to create this response
	 *
	 * @param Galahad_Service_Ooyala $service
	 */
	public function setService(Galahad_Service_Ooyala $service)
	{
		$this->_service = $service;
	}

	/**
	 * Pass everything else to SimpleXMLElement
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->_data->$name;
	}

	/**
	 * Pass everything else to SimpleXMLElement
	 * 
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->_data, $name), $arguments);
	}

	/**
	 * @param string $message
	 */
	protected function _throwException($message)
	{
		/** @see Galahad_Service_Ooyala_Exception */
		require_once 'Galahad/Service/Ooyala/Exception.php';
		throw new Galahad_Service_Ooyala_Exception($message);
	}
}