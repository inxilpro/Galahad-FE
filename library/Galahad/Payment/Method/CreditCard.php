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
 * Credit Card Payment Method
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment_Method_CreditCard extends Galahad_Payment_Method
{
	const TYPE_MASTERCARD 	= 'MasterCard';
	const TYPE_VISA 		= 'Visa';
	const TYPE_AMEX 		= 'American Express';
	const TYPE_DINERSCLUB 	= 'Diners Club';
	const TYPE_DISCOVER 	= 'Discover';
	const TYPE_ENROUTE 		= 'Enroute';
	const TYPE_JCB 			= 'JCB';
	
	private static $_acceptedTypes = array(
		self::TYPE_VISA,
		self::TYPE_MASTERCARD,
		self::TYPE_DISCOVER,
		self::TYPE_AMEX,
	);
	
	private $_number = null;
	private $_expireMonth = 0;
	private $_expireYear = 0;
	private $_code = null;
	private $_type = null;
	
	public function __construct($number, $expireMonth, $expireYear, $code = null, Array $accepted = array())
	{
		// Accepted card types
		if (empty($accepted)) {
			$accepted = self::$_acceptedTypes;
		}
		
		// Clean data
		$number = preg_replace('/\D/', '', $number);
		$expireMonth = (int) $expireMonth;
		$expireYear = (int) $expireYear;
		if ($expireYear < 1000) {
			$expireYear += 2000;
		}
		
		// Error check
		if ($expireMonth < 1 || $expireMonth > 12) {
			require_once 'Galahad/CreditCard/Exception.php';
			throw new Galahad_CreditCard_Exception("<b>{$expireMonth}</b> is an invalid month.");
		}
		
		if ($expireYear < (int) date('Y') || ($expireYear == (int) date('Y') && $expireMonth < (int) date('n'))) {
			require_once 'Galahad/CreditCard/ExpiredException.php';
			throw new Galahad_CreditCard_ExpiredException();
		}
		
		if (!self::checkMod10($number)) {
			require_once 'Galahad/CreditCard/Mod10Exception.php';
			throw new Galahad_CreditCard_Mod10Exception();
		}
		
		$type = self::getCardType($number);
		if (!in_array($type, $accepted)) {
			require_once 'Galahad/CreditCard/CardTypeException.php';
			throw new Galahad_CreditCard_CardTypeException("We do not accept <b>{$type}</b>");
		}
		
		$this->_number = $number;
		$this->_expireMonth = $expireMonth;
		$this->_expireYear = $expireYear;
		$this->_code = $code;
		$this->_type = $type;	
	}
	
	public function getNumber()
	{
		return $this->_number;
	}
	
	public function getExpirationDate($format = 'mY')
	{
		$date = gmmktime(0, 0, 0, $this->_expireMonth, 1, $this->_expireYear);
		return gmdate($format, $date);
	}
	
	public function getCode()
	{
		if (null == $this->_code) {
			return false;
		}
		
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
	 * Checks whether a credit card number passes the MOD 10 check
	 *
	 * @param string $number
	 * @return bool
	 */
	public static function checkMod10($number)
	{
		$number = preg_replace('/\D/', '', $number);
		$length = strlen($number);
		$parity = $length % 2;
		
		if ($length < 13) {
			return false;
		}
		
		$sum = 0;
		for ($i = 0; $i < $length; $i++) {
			$digit = $number[$i];
			if ($i % 2 == $parity) {
				$digit = $digit * 2;
			}
			if ($digit > 9) {
				$digit = $digit - 9;
			}
			$sum = $sum + $digit;
		}
		
		$valid = ($sum % 10 == 0);
		return $valid;
	}
	
	/**
	 * Gets the type of card based on its number
	 *
	 * @param string $number
	 * @return string
	 */
	public static function getCardType($number)
	{
		$number = preg_replace('/\D/', '', $number);
		$length = strlen($number);
		if ($length < 13) {
			return false;
		}
		
		$d1 = intval(substr($number, 0, 1));
		$d2 = intval(substr($number, 0, 2));
		$d3 = intval(substr($number, 0, 3));
		$d4 = intval(substr($number, 0, 4));
		
		if ($d2 >= 51 && $d2 <= 55) {
			return self::TYPE_MASTERCARD;
		} elseif ($d1 == 4) {
			return self::TYPE_VISA;
		} elseif ($d2 >= 34 && $d2 <= 37) {
			return self::TYPE_AMEX;
		} elseif ($d3 >= 300 && $d3 <= 305) {
			return self::TYPE_DINERSCLUB;
		} elseif ($d2 == 36 || $d2 == 38) {
			return self::TYPE_DINERSCLUB;
		} elseif ($d4 == 6011) {
			return self::TYPE_DISCOVER;
		} elseif ($d4 == 2014 || $d4 == 2149) {
			return self::TYPE_ENROUTE;
		} elseif ($d1 == 3 || $d4 == 2131 || $d4 == 1800) {
			return self::TYPE_JCB;
		}
		
		return false;
	}
}