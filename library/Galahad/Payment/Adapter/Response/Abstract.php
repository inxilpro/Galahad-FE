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
abstract class Galahad_Payment_Adapter_Response_Abstract
{
	/**#@+
	 * @var string
	 */
	
	/** Signifies that the transaction was approved at the gateway */
	const CODE_APPROVED = 'approved';
	
	/** Signifies that the transaction was denied at the gateway */
	const CODE_DECLINED = 'declined';
	
	/** Signifies that the transaction needs to be reviewed before an decision is made */
	const CODE_REVIEW = 'review';
	
	/** Signifies that there was an error at the gateway */
	const CODE_SERVER_ERROR = 'server-error';
	
	/** Signifies that there was an error with the data sent to the gateway */
	const CODE_APPLICATION_ERROR = 'application-error';
	
	/** Signifies that an unknown error occurred with the transaction */
	const CODE_UNKNOWN_ERROR = 'unknown-error';
	
	/** Response message */
	protected $_message = '';
	
	/** Response code */
	protected $_code = self::CODE_UNKNOWN_ERROR;
	/**#@-*/
	
	/**
	 * Determine whether the transaction was approved
	 * 
	 * @return bool
	 */
	abstract public function isApproved();
	
	/**
	 * Get the response message from the adapter
	 * 
	 * @return string
	 */
	public function getMessage()
	{
		return $this->_message;
	}
	
	/**
	 * Get the response code from the adapter
	 * For example, Galahad_Payment_Adapter_Response_Abstract::CODE_SERVER_ERROR
	 * 
	 * @return string
	 */
	public function getCode()
	{
		return $this->_code;
	}
	
	/**
	 * If the adapter supports it, get a friendly message
	 * 
	 * This is a message that the adapter considers safe to show the
	 * end-user (something like "This transaction has been declined.")
	 * 
	 * Implementing this method is optional
	 * 
	 * @return string
	 */
	public function getFriendlyMessage()
	{
		return null;
	}
	
	/**
	 * Get the message set from the gateway
	 * 
	 * This is the message sent by the adapter's gateway, which may be different from
	 * the message that the adapter itself responds with
	 * 
	 * Implementing this method is optional
	 * 
	 * @return string
	 */
	public function getAdapterMessage()
	{
		return null;
	}
	
	/**
	 * Get the code sent by the gateway
	 * 
	 * This is the code sent by the adapter's gateway, which will be in whatever format
	 * specified by that gateway's API.  Some adapters may provide constants associated with
	 * some or all gateway codes.
	 * 
	 * Implementing this method is optional
	 * 
	 * @return string|int
	 */
	public function getAdapterCode()
	{
		return null;
	}
	
	/**
	 * Get the response/transaction ID
	 * 
	 * A unique ID that identifies the transaction at the gateway.
	 * 
	 * Implementing this method is optional
	 * 
	 * @todo This may become required, and may be renamed
	 * @return string|int
	 */
	public function getResponseId()
	{
		return null;
	}
}