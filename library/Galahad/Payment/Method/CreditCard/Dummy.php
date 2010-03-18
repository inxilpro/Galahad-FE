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
 * "Dummy" Credit Card Payment Method
 * 
 * Doesn't validate card
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment_Method_CreditCard_Dummy extends Galahad_Payment_Method_CreditCard
{
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
		// Still have to clean up expiration some to create a valid DateTime obj
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
		
		$this->_number = $number;
		$this->_expirationDate = $expiration;
		$this->_code = $code;
	}
}