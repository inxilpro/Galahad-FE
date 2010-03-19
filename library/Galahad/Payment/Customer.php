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
 * Generic Customer Class
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment_Customer implements Galahad_Payment_Customer_Interface
{
	/**
	 * Customer's Id
	 * @var string
	 */
	protected $_customerId;
	
	/**
	 * Customer's Ip Address
	 * @var string
	 */
	protected $_ipAddress;
	
	/**
	 * Customer's First Name
	 * @var string
	 */
	protected $_firstName;
	
	/**
	 * Customer's Last Name
	 * @var string
	 */
	protected $_lastName;
	
	/**
	 * Customer's Company
	 * @var string
	 */
	protected $_company;
	
	/**
	 * Customer's Address Line 1
	 * @var string
	 */
	protected $_addressLine1;
	
	/**
	 * Customer's Address Line 2
	 * @var string
	 */
	protected $_addressLine2;
	
	/**
	 * Customer's City
	 * @var string
	 */
	protected $_city;
	
	/**
	 * Customer's State
	 * @var string
	 */
	protected $_state;
	
	/**
	 * Customer's Postal Code
	 * @var string
	 */
	protected $_postalCode;
	
	/**
	 * Customer's Country
	 * @var string
	 */
	protected $_country;
	
	/**
	 * Customer's Phone Number
	 * @var string
	 */
	protected $_phoneNumber;
	
	/**
	 * Customer's Fax Number
	 * @var string
	 */
	protected $_faxNumber;
	
	/**
	 * Customer's Email
	 * @var string
	 */
	protected $_email;
	
	/**
	 * Constructor
	 * 
	 * @param array|Zend_Config $data
	 */
	public function __construct($data = array())
	{
		$this->setData($data);
	}
	
	/**
     * Setup customer from array
     * 
     * @param array|Zend_Config $options
     */
    public function setData($data)
    {
    	if (!is_array($data)) {
            if ($data instanceof Zend_Config) {
                $data = $data->toArray();
            } else {
                /** @see Galahad_Payment_Customer_Exception */
                require_once 'Galahad/Payment/Customer/Exception.php';
                throw new Galahad_Payment_Customer_Exception('Customer data must be an array or a Zend_Config object.');
            }
        }
        
    	foreach ($data as $key => $value) {
            $normalized = ucfirst($key);
            if ('Data' == $normalized) {
                continue;
            }

            $method = 'set' . $normalized;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
	
	/**
	 * Set the customer's ID
	 * 
	 * @param string $customerId
	 * @return Galahad_Payment_Customer
	 */
	public function setCustomerId($customerId)
	{
		$this->_customerId = $customerId;
		return $this;
	}
	
	/**
	 * Get the customer's ID
	 * 
	 * @return string
	 */
	public function getCustomerId()
	{
		return $this->_customerId;
	}
	
	/**
	 * Set the customer's IP address
	 * 
	 * @param string $ipAddress
	 * @return Galahad_Payment_Customer
	 */
	public function setIpAddress($ipAddress)
	{
		$this->_ipAddress = $ipAddress;
		return $this;
	}
	
	/**
	 * Get the customer's IP address
	 * 
	 * @return string
	 */
	public function getIpAddress()
	{
		if (!$this->_ipAddress) {
			if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) { 
				$this->_ipAddress = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
				$this->_ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$this->_ipAddress = $_SERVER['REMOTE_ADDR'];
			}
		}
		
		return $this->_ipAddress;
	}
	
	/**
	 * Set the customer's first name
	 * 
	 * @param string $firstName
	 * @return Galahad_Payment_Customer
	 */
	public function setFirstName($firstName)
	{
		$this->_firstName = $firstName;
		return $this;
	}
	
	/**
	 * Get the customer's first name
	 * 
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->_firstName;
	}
	
	/**
	 * Set the customer's last name
	 * 
	 * @param string $lastName
	 * @return Galahad_Payment_Customer
	 */
	public function setLastName($lastName)
	{
		$this->_lastName = $lastName;
		return $this;
	}
	
	/**
	 * Get the customer's last name
	 * 
	 * @return string
	 */
	public function getLastName()
	{
		return $this->_lastName;
	}
	
	/**
	 * Set the customer's company
	 * 
	 * @param string $company
	 * @return Galahad_Payment_Customer
	 */
	public function setCompany($company)
	{
		$this->_company = $company;
		return $this;
	}
	
	/**
	 * Get the customer's company
	 * 
	 * @return string
	 */
	public function getCompany()
	{
		return $this->_company;
	}
	
	/**
	 * Set the customer's address line 1
	 * 
	 * @param string $addressLine1
	 * @return Galahad_Payment_Customer
	 */
	public function setAddressLine1($addressLine1)
	{
		$this->_addressLine1 = $addressLine1;
		return $this;
	}
	
	/**
	 * Get the customer's address line 1
	 * 
	 * @return string
	 */
	public function getAddressLine1()
	{
		return $this->_addressLine1;
	}
	
	/**
	 * Set the customer's address line 2
	 * 
	 * @param string $addressLine2
	 * @return Galahad_Payment_Customer
	 */
	public function setAddressLine2($addressLine2)
	{
		$this->_addressLine2 = $addressLine2;
		return $this;
	}
	
	/**
	 * Get the customer's address line 2
	 * 
	 * @return string
	 */
	public function getAddressLine2()
	{
		return $this->_addressLine2;
	}
	
	/**
	 * Set the customer's city
	 * 
	 * @param string $city
	 * @return Galahad_Payment_Customer
	 */
	public function setCity($city)
	{
		$this->_city = $city;
		return $this;
	}
	
	/**
	 * Get the customer's city
	 * 
	 * @return string
	 */
	public function getCity()
	{
		return $this->_city;
	}
	
	/**
	 * Set the customer's state
	 * 
	 * @param string $state
	 * @return Galahad_Payment_Customer
	 */
	public function setState($state)
	{
		// TODO: Verify if country is set?
		$this->_state = $state;
		return $this;
	}
	
	/**
	 * Get the customer's state
	 * 
	 * @return string
	 */
	public function getState()
	{
		return $this->_state;
	}
	
	/**
	 * Set the customer's postal code
	 * 
	 * @param string $postalCode
	 * @return Galahad_Payment_Customer
	 */
	public function setPostalCode($postalCode)
	{
		// TODO: Verify format if country is set?
		$this->_postalCode = $postalCode;
		return $this;
	}
	
	/**
	 * Get the customer's postal code
	 * 
	 * @return string
	 */
	public function getPostalCode()
	{
		return $this->_postalCode;
	}
	
	/**
	 * Set the customer's country
	 * 
	 * @param string $country
	 * @return Galahad_Payment_Customer
	 */
	public function setCountry($country)
	{
		$this->_country = $country;
		return $this;
	}
	
	/**
	 * Get the customer's country
	 * 
	 * @return string
	 */
	public function getCountry()
	{
		return $this->_country;
	}
	
	/**
	 * Set the customer's phone number
	 * 
	 * @param string $phoneNumber
	 * @return Galahad_Payment_Customer
	 */
	public function setPhoneNumber($phoneNumber)
	{
		$this->_phoneNumber = $phoneNumber;
		return $this;
	}
	
	/**
	 * Get the customer's phone number
	 * 
	 * @return string
	 */
	public function getPhoneNumber()
	{
		return $this->_phoneNumber;
	}
	
	/**
	 * Set the customer's fax number
	 * 
	 * @param string $faxNumber
	 * @return Galahad_Payment_Customer
	 */
	public function setFaxNumber($faxNumber)
	{
		$this->_faxNumber = $faxNumber;
		return $this;
	}
	
	/**
	 * Get the customer's fax number
	 * 
	 * @return string
	 */
	public function getFaxNumber()
	{
		return $this->_faxNumber;
	}
	
	/**
	 * Set the customer's email
	 * 
	 * @param string $email
	 * @return Galahad_Payment_Customer
	 */
	public function setEmail($email)
	{
		/** @see Zend_Validate_EmailAddress */
		require_once 'Zend/Validate/EmailAddress.php';
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($email)) {
			/** @see Galahad_Payment_Customer_Exception */
			require_once 'Galahad/Payment/Customer/Exception.php';
			throw new Galahad_Payment_Customer_Exception('Invalid customer e-mail address.');
		}
		
		$this->_email = $email;
		return $this;
	}
	
	/**
	 * Get the customer's email
	 * 
	 * @return string
	 */
	public function getEmail()
	{
		return $this->_email;
	}
}