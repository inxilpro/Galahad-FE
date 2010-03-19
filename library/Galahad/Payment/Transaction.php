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
 * Payment Transaction
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment_Transaction
{
	/**
	 * The transaction ID
	 * 
	 * This is in whatever format the gateway sends transaction IDs
	 * 
	 * @var string
	 */
	protected $_transactionId = null;
	
	/**
	 * Transaction Amount
	 * @var float
	 */
	protected $_amount = 0.00;
	
	/**
	 * Transaction invoice number
	 * @var mixed
	 */
	protected $_invoiceNumber;
	
	/**
	 * Transaction comments or description
	 * @var string
	 */
	protected $_comments;
	
	/**
	 * Payment Method
	 * @var Galahad_Payment_Method
	 */
	protected $_paymentMethod;
	
	/**
	 * Billing Customer
	 * @var Galahad_Payment_Customer_Interface
	 */
	protected $_billingCustomer;
	
	/**
	 * Shipping Customer
	 * @var Galahad_Payment_Customer_Interface
	 */
	protected $_shippingCustomer;
	
	/**
	 * Application-specific properties
	 * 
	 * These are transaction properties that either aren't available
	 * in all gateways or are for application-use only.
	 * 
	 * @var array
	 */
	protected $_properties = array();
	
	/**
	 * Constructor
	 * 
	 * @param array|Zend_Config $data
	 */
	public function __construct($options = array())
	{
		$this->setOptions($options);
	}
	
	/**
     * Setup customer from array
     * 
     * @param array|Zend_Config $options
     */
    public function setOptions($options)
    {
    	if (!is_array($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            } else {
                /** @see Galahad_Payment_Transaction_Exception */
                require_once 'Galahad/Payment/Transaction/Exception.php';
                throw new Galahad_Payment_Transaction_Exception('Transaction options must be an array or a Zend_Config object.');
            }
        }
        
    	foreach ($options as $key => $value) {
            $normalized = ucfirst($key);
            if ('Options' == $normalized) {
                continue;
            }

            $method = 'set' . $normalized;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
            
            // TODO: throw exception on unknown option?
        }
    }
	
	/**
	 * Set the transaction ID
	 * 
	 * @param mixed $transactionId
	 */
	public function setTransactionId($transactionId)
	{
		$this->_transactionId = (string) $transactionId;
		return $this;
	}
	
	/**
	 * Get the transaction ID
	 * 
	 * @return string
	 */
	public function getTransactionId()
	{
		return $this->_transactionId;
	}
	
	/**
	 * Sets the transaction amount
	 * 
	 * @param int|float $amount
	 * @return Galahad_Payment_Transaction
	 */
	public function setAmount($amount)
	{
		if (!is_int($amount) && !is_float($amount)) {
			/** @see Galahad_Payment_Transaction_Exception */
			require_once 'Galahad/Payment/Transaction/Exception.php';
			throw new Galahad_Payment_Transaction_Exception('Transaction amount must be an integer or a float.');
		}
		
		$this->_amount = (float) $amount;
		return $this;
	}
	
	/**
	 * Get the transaction amount
	 * 
	 * @return float
	 */
	public function getAmount()
	{
		return $this->_amount;
	}
	
	/**
	 * Set the transaction invoice number
	 * 
	 * @param mixed $invoiceNumber
	 */
	public function setInvoiceNumber($invoiceNumber)
	{
		$this->_invoiceNumber = (string) $invoiceNumber;
		return $this;
	}
	
	/**
	 * Get the transaction invoice number
	 * 
	 * @return string
	 */
	public function getInvoiceNumber()
	{
		return $this->_invoiceNumber;
	}
	
	/**
	 * Set the transaction comments
	 * 
	 * @param mixed $comments
	 */
	public function setComments($comments)
	{
		$this->_comments = (string) $comments;
		return $this;
	}
	
	/**
	 * Get the transaction comments
	 * 
	 * @return string
	 */
	public function getComments()
	{
		return $this->_comments;
	}
	
	/**
	 * Set the transaction's payment method (credit card, e-check, etc)
	 * 
	 * @param Galahad_Payment_Method $method
	 * @return Galahad_Payment_Transaction
	 */
	public function setPaymentMethod(Galahad_Payment_Method $method)
	{
		$this->_paymentMethod = $method;
		return $this;
	}
	
	/**
	 * Get the transaction's payment method
	 * 
	 * @return Galahad_Payment_Method
	 */
	public function getPaymentMethod()
	{
		return $this->_paymentMethod;
	}
	
	/**
	 * Set the transaction's billing customer (who gets billed)
	 * 
	 * @param Galahad_Payment_Customer_Interface $customer
	 * @return Galahad_Payment_Transaction
	 */
	public function setBillingCustomer(Galahad_Payment_Customer_Interface $customer)
	{
		$this->_billingCustomer = $customer;
		return $this;
	}
	
	/**
	 * Get the transaction's billing customer (who gets billed)
	 * 
	 * @return Galahad_Payment_Customer_Interface
	 */
	public function getBillingCustomer()
	{
		return $this->_billingCustomer;
	}
	
	/**
	 * Set the transaction's shipping customer (who receives items ordered)
	 * 
	 * @param Galahad_Payment_Customer_Interface $customer
	 * @return Galahad_Payment_Transaction
	 */
	public function setShippingCustomer(Galahad_Payment_Customer_Interface $customer)
	{
		$this->_shippingCustomer = $customer;
		return $this;
	}
	
	/**
	 * Get the transaction's shipping customer (who receives items ordered)
	 * 
	 * @return Galahad_Payment_Customer_Interface
	 */
	public function getShippingCustomer()
	{
		return $this->_shippingCustomer;
	}
	
	/**
	 * Captures all setters and getters and stores them as transaction properties
	 * 
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments)
	{
		if (preg_match('/^(set|get)([A-Z])(\w+)$/', $name, $matches)) {
			$action = $matches[1];
			$propertyName = strtolower($matches[2]) . $matches[3];
			
			if ('set' == $action) {
				$this->_properties[$propertyName] = $arguments[0];
				return $this;
			} else {
				if (!isset($this->_properties[$propertyName])) {
					return null;
				}
				return $this->_properties[$propertyName];
			}
		}
		
		/** @see Galahad_Payment_Transaction_Exception */
		require_once 'Galahad/Payment/Transaction/Exception.php';
		throw new Galahad_Payment_Transaction_Exception("'{$name}' is not a valid transaction method.");
	}
	
	/**
	 * Set all undeclared properties as transaction properties
	 *  
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->_properties[$name] = $value;
	}
	
	/**
	 * Get all undeclared properties as transaction properties
	 * 
	 * @param string $name
	 * @return mixex
	 */
	public function __get($name)
	{
		if (!isset($this->_properties[$name])) {
			return null;
		}
		return $this->_properties[$name];
	}
	
	/**
	 * Overload isset
	 * @param string $name
	 */
	public function __isset($name)
	{
		return isset($this->_properties[$name]);
	}
	
	/**
	 * Overload unset
	 * @param string $name
	 */
	public function __unset($name)
	{
		unset($this->_properties[$name]);
	}
}