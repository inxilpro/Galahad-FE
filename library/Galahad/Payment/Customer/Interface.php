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
 * Payment Customer Interface
 * 
 * Useful if you want to use your "User" model as a customer object
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
interface Galahad_Payment_Customer_Interface
{
	/**
	 * Get the customer's ID
	 * 
	 * @return string
	 */
	public function getCustomerId();
	
	/**
	 * Get the customer's IP address
	 * 
	 * @return string
	 */
	public function getIpAddress();
	
	/**
	 * Get the customer's first name
	 * 
	 * @return string
	 */
	public function getFirstName();
	
	/**
	 * Get the customer's last name
	 * 
	 * @return string
	 */
	public function getLastName();
	
	/**
	 * Get the customer's company
	 * 
	 * @return string
	 */
	public function getCompany();
	
	/**
	 * Get the customer's address line 1
	 * 
	 * @return string
	 */
	public function getAddressLine1();
	
	/**
	 * Get the customer's address line 2
	 * 
	 * @return string
	 */
	public function getAddressLine2();
	
	/**
	 * Get the customer's city
	 * 
	 * @return string
	 */
	public function getCity();
	
	/**
	 * Get the customer's state
	 * 
	 * @return string
	 */
	public function getState();
	
	/**
	 * Get the customer's postal code
	 * 
	 * @return string
	 */
	public function getPostalCode();
	
	/**
	 * Get the customer's country
	 * 
	 * @return string
	 */
	public function getCountry();
	
	/**
	 * Get the customer's phone number
	 * 
	 * @return string
	 */
	public function getPhoneNumber();
	
	/**
	 * Get the customer's fax number
	 * 
	 * @return string
	 */
	public function getFaxNumber();
	
	/**
	 * Get the customer's email
	 * 
	 * @return string
	 */
	public function getEmail();
}