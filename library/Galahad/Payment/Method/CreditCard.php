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
 * @see Zend_Validate_CreditCard 
 */
require_once 'Zend/Validate/CreditCard.php';

/**
 * Credit Card Payment Method
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment_Method_CreditCard extends Galahad_Payment_Method
{	
	/**
	 * Credit card types to accept
	 * @var array
	 */
	protected static $_acceptedTypes = array(
		Zend_Validate_CreditCard::ALL,
	);
	
	/**
	 * Credit Card Number
	 * @var string
	 */
	protected $_number = null;
	
	/**
	 * Expiration Date
	 * @var DateTime
	 */
	protected $_expirationDate;
	
	/**
	 * Card Security Code (CSC)
	 * 
	 * AKA Card Verification Value (CVV or CV2), Card Verification Value Code (CVVC), 
	 * Card Verification Code (CVC), Verification Code (V-Code or V Code), or Card Code Verification (CCV)
	 * 
	 * @var string
	 */
	protected $_code = null;
	
	/**
	 * Card Type
	 * 
	 * @var string
	 */
	protected $_type = null;
	
	/**
	 * Detailed errors
	 * @var array
	 */
	protected $_errors = array();
	
	/**
	 * Constructor 
	 * 
	 * @param string $number The Credit Card Number
	 * @param int $expireMonth Expiration Month
	 * @param int $expireYear Expiration Year
	 * @param string $code Card Security Code
	 * @param array $accepted Accepted Card Types (for this instance) 
	 */
	public function __construct($number, $expirationMonth, $expirationYear, $code = null, Array $acceptedTypes = null)
	{
		// Accepted card types
		if (null == $acceptedTypes) {
			$acceptedTypes = self::$_acceptedTypes;
		}
		
		// Clean up credit card number
		$number = preg_replace('/\D/', '', $number);
		
		// Clean up and validate expiration month
		$expirationMonth = (int) $expirationMonth;
		if ($expirationMonth < 1 || $expirationMonth > 12) {
			/** @see Galahad_CreditCard_Exception */
			require_once 'Galahad/Payment/Method/CreditCard/Exception.php';
			throw new Galahad_Payment_Method_CreditCard_Exception("'{$expirationMonth}' is an invalid month.");
		}
		
		// Generate DateTime object and validate expiration
		$expirationYear = (int) $expirationYear;
		$expiration = new DateTime("{$expirationYear}-{$expirationMonth}-1");
		$expiration->modify('+1 month -1 second');
		
		if ($expiration < new DateTime()) {
			/** @see Galahad_CreditCard_Exception */
			require_once 'Galahad/Payment/Method/CreditCard/Exception.php';
			throw new Galahad_Payment_Method_CreditCard_Exception("Card has expired.");
		}
		
		// Validate Card
		$validator = new Zend_Validate_CreditCard();
		$validator->addType($acceptedTypes);
		if (!$validator->isValid($number)) {
			$this->_errors = $validator->getErrors();
			
			/** @see Galahad_CreditCard_Exception */
			require_once 'Galahad/Payment/Method/CreditCard/Exception.php';
			throw new Galahad_Payment_Method_CreditCard_Exception("Invalid credit card number.");
		}
		
		$this->_number = $number;
		$this->_expirationDate = $expiration;
		$this->_code = $code;
		$this->_type = $validator->getType();
	}
	
	/**
	 * Get Credit Card Number
	 * 
	 * @return string
	 */
	public function getNumber()
	{
		return $this->_number;
	}
	
	/**
	 * Get Expiration Date
	 * 
	 * @param string $format
	 * @param bool $raw If true you will receive a raw DateTime object
	 */
	public function getExpirationDate($format = 'mY', $raw = false)
	{
		if (true === $raw) {
			return $this->_expirationDate;
		}
		
		return $this->_expirationDate->format($format);
	}
	
	/**
	 * Get Card Security Code
	 * 
	 * @return string|null
	 */
	public function getCode()
	{
		return $this->_code;
	}
	
	/**
	 * Sets the default accepted credit card types for all transactions
	 *
	 * @param array $accepted
	 */
	public static function setDefaultAcceptedTypes(Array $accepted)
	{
		if (!empty($accepted)) {
			self::$_acceptedTypes = $accepted;
		}
	}
	
	/**
	 * Get credit card validation errors
	 * 
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
}