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
 * Authorize.net Payment Adapter
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment_Adapter_AuthorizeNet extends Galahad_Payment_Adapter_Abstract
{
	/**
	 * Adapter features
	 * 
	 * @var array
	 */
	protected static $_features = array(
		Galahad_Payment::FEATURE_PROCESS,
		Galahad_Payment::FEATURE_PRIOR_AUTHORIZATION,
		// TODO: Refund and Void transactions
		Galahad_Payment::FEATURE_RECURRING,
	);
	
	/**
	 * Authorize.net API Login ID
	 * 
	 * @var string
	 */
	protected $_apiLoginId = null;
	
	/**
	 * Authorize.net API Transaction Key
	 * 
	 * @var string
	 */
	protected $_apiTransactionKey = null;
	
	/**
	 * Authorize.net API Endpoint
	 * 
	 * @var string
	 */
	protected $_apiUrl = 'https://secure.authorize.net/gateway/transact.dll';
	
	/**
	 * Default parameters to send to Authorize.net
	 * 
	 * @var array
	 */
	protected $_defaultParameters = array(
		'x_version' => '3.1',
		'x_delim_char' => '|',
		'x_delim_data' => 'TRUE',
		'x_relay_response' => 'FALSE',
	);
	
	/**
	 * Client for SOAP-based commands
	 * 
	 * @var Zend_Soap_Client
	 */
	protected $_soapClient = null;
	
	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	function __construct($options = null)
	{
		if (!extension_loaded('curl')) {
			/** @see Galahad_Payment_Adapter_ExtensionException */
			require_once 'Galahad/Payment/Adapter/ExtensionException.php';
			throw new Galahad_Payment_Adapter_ExtensionException("cURL is required for Authorize.net transactions.");
		}
		if (!extension_loaded('openssl')) {
			/** @see Galahad_Payment_Adapter_ExtensionException */
			require_once 'Galahad/Payment/Adapter/ExtensionException.php';
			throw new Galahad_Payment_Adapter_ExtensionException("OpenSSL is required for Authorize.net transactions.");
		}
		
		parent::setOptions($options);
	}
	
	/**
	 * Sets the Authorize.net Login ID
	 * 
	 * @param string $loginId
	 */
	public function setLoginId($loginId)
	{
		$this->_apiLoginId = $loginId;
	}
	
	/**
	 * Sets the Authorize.net Transaction Key
	 * 
	 * @param string $transactionKey
	 */
	public function setTransactionKey($transactionKey)
	{
		$this->_apiTransactionKey = $transactionKey;
	}
	
	/**
	 * Set the API URL
	 * 
	 * @param string $url
	 */
	public function setApiUrl($url)
	{
		require_once 'Zend/Uri.php';
		if (!Zend_Uri::check($url)) {
			throw new InvalidArgumentException('Invalid URL endpoint');
		}
		
		$this->_apiUrl = $url;
	}
	
	/**
	 * Authorize a card for a specific amount
	 *
	 * @param Galahad_Gateway_Transaction $transaction
	 * @return Galahad_Gateway_AuthorizeNet_Response
	 */
	public function authorize(Galahad_Gateway_Transaction $transaction)
	{
		$parameters = array('x_type' => 'AUTH_ONLY');
		$parameters = $this->_buildParameters($transaction, $parameters);
		return $this->sendAdapterRequest($parameters);
	}
	
	/**
     * Process a transaction
     * 
     * Authorizes and captures a transaction in one call
     * 
     * @param Galahad_Payment_Transaction $transaction
     * @return Galahad_Payment_Adapter_Response
     */
	public function process(Galahad_Payment_Transaction $transaction) 
	{
		$parameters = array('x_type' => 'AUTH_CAPTURE');	
		$parameters = $this->_buildParameters($transaction, $parameters);
		return $this->sendAdapterRequest($parameters);
	}
	
	/**
	 * Capture a transaction (by default auth + capture)
	 *
	 * @param Galahad_Gateway_Transaction $transaction
	 * @param bool $authorize
	 * @return Galahad_Gateway_AuthorizeNet_Response
	 */
	public function capture(Galahad_Gateway_Transaction $transaction)
	{
		$transactionId = $transaction->getTransactionId();
		if (empty($transactionId)) {
			/** @see Galahad_Payment_Adapter_Exception */
			require_once 'Galahad/Payment/Adapter/Exception.php';
			throw new Galahad_Payment_Adapter_Exception('You cannot capture transactions that have not yet been authorized.');
		}
		
		$parameters = array(
			'x_type' => 'PRIOR_AUTH_CAPTURE',
			'x_trans_id' => $transactionId,
		);
		
		$parameters = $this->_buildParameters($transaction, $parameters);
		return $this->sendAdapterRequest($parameters);
	}
	
	/**
	 * Create a subscription
	 * 
	 * @param Galahad_Payment_Transaction_Subscription $transaction
	 * @return Galahad_Payment_Adapter_Response_AuthorizeNet_ARB
	 */
	public function subscribe(Galahad_Payment_Transaction_Subscription $transaction)
	{
		$client = $this->getSoapClient();
		$parameters = array();
		
		// Authentication
		$parameters['merchantAuthentication'] = array(
			'name' => $this->_apiLoginId, 
			'transactionKey' => $this->_apiTransactionKey,
		);
		
		// Subscription
		$intervalLength = $transaction->getIntervalLength();
		$intervalUnit = $transaction->getIntervalUnit();
		if (Galahad_Payment_Transaction_Subscription::INTERVAL_UNIT_DAYS == $intervalUnit && $intervalLength < 7) {
			throw new Galahad_Payment_Adapter_Exception('Daily intervals must be more than 7 days.');
		}
		
		$startDate = $transaction->getStartDate();
		$startDate->setTimezone('America/Denver');
		$startDate = $startDate->toString('YYYY-MM-dd');
		
		$totalOccurrences = $transaction->getTotalOccurrences();
		if (null == $totalOccurrences) {
			$totalOccurrences = 9999;
		}
		
		$parameters['subscription'] = array(
			'paymentSchedule' => array(
				'interval' => array(
					'length' => $intervalLength,
					'unit' => $intervalUnit,
				),
				'startDate' => $startDate,
				'totalOccurrences' => $totalOccurrences,
			),
			'amount' => $transaction->getAmount(),
		);
		
		if (null !== ($subscriptionName = $transaction->getSubscriptionName())) {
			$parameters['subscription']['name'] = $subscriptionName;
		}
		
		// Payment
		$method = $transaction->getPaymentMethod();
		if ($method instanceof Galahad_Payment_Method_CreditCard) {
			$parameters['subscription']['payment'] = array(
				'creditCard' => array(
					'cardNumber' => $method->getNumber(),
					'expirationDate' => $method->getExpirationDate('Y-m'),
				),
			);
			if (null !== ($code = $method->getCode())) {
				$parameters['subscription']['payment']['creditCard']['code'] = $code;
			}
		} else {
			/** @see Galahad_Payment_Adapter_Exception */
			require_once 'Galahad/Payment/Adapter/Exception.php';
			throw new Galahad_Payment_Adapter_Exception('Only credit card payments are supported at this time.');
		}
		
		// Order
		$invoiceNumber = $transaction->getInvoiceNumber();
		$description = $transaction->getComments();
		if ($invoiceNumber || $description) {
			$parameters['subscription']['order'] = array();
			if ($invoiceNumber) {
				$parameters['subscription']['order']['invoiceNumber'] = $invoiceNumber;
			}
			if ($description) {
				$parameters['subscription']['order']['description'] = $description;
			}
		}
		
		// Customer
		$customer = $transaction->getBillingCustomer();
		$parameters['subscription']['customer'] = array();
		if ($customerId = $customer->getCustomerId()) {
			$parameters['subscription']['customer']['id'] = $customerId;
		}
		if ($customerEmail = $customer->getEmail()) {
			$parameters['subscription']['customer']['email'] = $customerEmail;
		}
		if ($customerPhone = $customer->getPhoneNumber()) {
			$parameters['subscription']['customer']['phone'] = $customerPhone;
		}
		if ($customerFax = $customer->getFaxNumber()) {
			$parameters['subscription']['customer']['fax'] = $customerFax;
		}
		
		$parameters['subscription']['billTo'] = array(
			'firstName' => $customer->getFirstName(),
			'lastName' => $customer->getLastName(),
			'company' => $customer->getCompany(),
			'address' => $customer->getAddressLine1() . ' ' . $customer->getAddressLine2(),
			'city' => $customer->getCity(),
			'state' => $customer->getState(),
			'zip' => $customer->getPostalCode(),
			'country' => $customer->getCountry(),
		);
		
		if ($shippingRecipient = $transaction->getShippingCustomer()) {
			$parameters['subscription']['shipTo'] = array(
				'firstName' => $shippingRecipient->getFirstName(),
				'lastName' => $shippingRecipient->getLastName(),
				'company' => $shippingRecipient->getCompany(),
				'address' => $shippingRecipient->getAddressLine1() . ' ' . $shippingRecipient->getAddressLine2(),
				'city' => $shippingRecipient->getCity(),
				'state' => $shippingRecipient->getState(),
				'zip' => $shippingRecipient->getPostalCode(),
				'country' => $shippingRecipient->getCountry(),
			);
		}
		
		$result = $client->ARBCreateSubscription($parameters);
		$response = new Galahad_Payment_Adapter_Response_AuthorizeNet_ARB($result->ARBCreateSubscriptionResult, $parameters);
		
		if ($subscriptionId = $response->getSubscriptionId()) {
			$transaction->setSubscriptionId($subscriptionId);
		}
		
		return $response;
	}
	
	/**
	 * Cancel a subscription
	 * 
	 * @param Galahad_Payment_Transaction_Subscription $transaction
	 * @return Galahad_Payment_Adapter_Response
	 */
	public function unsubscribe(Galahad_Payment_Transaction_Subscription $transaction)
	{
		$client = $this->getSoapClient();
		$parameters = array();
		
		// Authentication
		$parameters['merchantAuthentication'] = array(
			'name' => $this->_apiLoginId, 
			'transactionKey' => $this->_apiTransactionKey,
		);
		
		// Subscription ID
		$parameters['subscriptionId'] = $transaction->getSubscriptionId();
		
		$result = $client->ARBCancelSubscription($parameters);
		$response = new Galahad_Payment_Adapter_Response_AuthorizeNet_ARB($result->ARBCancelSubscriptionResult, $parameters);
		
		return $response;
	}
	
	/**
	 * Change a subscription
	 * 
	 * @param Galahad_Payment_Transaction_Subscription $transaction
	 * @return Galahad_Payment_Adapter_Response
	 */
	public function changeSubscription(Galahad_Payment_Transaction_Subscription $transaction)
	{
		
	}
	
	/**
	 * Call the Authorize.net API
	 *
	 * @param array $parameters
	 * @return Galahad_Gateway_AuthorizeNet_Response
	 */
	public function sendAdapterRequest(array $parameters = array())
	{
		// Build Fields
		$fields = http_build_query($parameters);
		
		// Init cURL
		// TODO: Error check	
		$ch = curl_init($this->_apiUrl);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // TODO: Optional
		
		// Call API
		$data = curl_exec($ch);
		curl_close($ch);
		
		/** @see Galahad_Payment_Adapter_Response_AuthorizeNet_AIM */
		require_once 'Galahad/Payment/Adapter/Response/AuthorizeNet/AIM.php';
		return new Galahad_Payment_Adapter_Response_AuthorizeNet_AIM($data, $parameters);
	}
	
	/**
	 * Set the SOAP client for SOAP-based commands
	 * 
	 * @param Zend_Soap_Client $client
	 * @return Galahad_Payment_Adapter_AuthorizeNet
	 */
	public function setSoapClient(Zend_Soap_Client $client)
	{
		$this->_soapClient = $client;
		return $this;
	}
	
	/**
	 * Get the SOAP client for SOAP-based commands
	 * 
	 * @return Zend_Soap_Client
	 */
	public function getSoapClient()
	{
		if (null == $this->_soapClient) {
			$this->_soapClient = new Zend_Soap_Client("https://apitest.authorize.net/soap/v1/Service.asmx?WSDL", array(
				'soap_version' => SOAP_1_1,
			));
		}
		
		return $this->_soapClient;
	}
	
	/**
	 * Build transaction parameters
	 * 
	 * @param Galahad_Payment_Transaction $transaction
	 * @param array $customParamters
	 * @return array
	 */
	protected function _buildParameters(Galahad_Payment_Transaction $transaction, array $customParamters = array())
	{
		$parameters = array_merge($this->_defaultParameters, $customParamters);
		
		// Enable test mode if necessary
		if (self::MODE_TEST == $this->getMode()) {
			$parameters['x_test_request'] = 'TRUE';
		}
		
		// Add API credentials
		$parameters['x_login'] = $this->_apiLoginId;
		$parameters['x_tran_key'] = $this->_apiTransactionKey;
		
		// Build parameters depending on the API function
		if ($parameters['x_type'] != 'PRIOR_AUTH_CAPTURE') {
			$parameters = $this->_buildAuthorizeParameters($transaction, $parameters);
		}
		
		return $parameters;
	}
	
	/**
	 * Build transaction parameters for "process" or "authorize" transactions
	 * 
	 * @param Galahad_Payment_Transaction $transaction
	 * @param array $paramters
	 * @return array
	 */
	protected function _buildAuthorizeParameters(Galahad_Payment_Transaction $transaction, array $parameters)
	{
		// Amount
		$amount = $transaction->getAmount();
		$parameters['x_amount'] = $amount;
		
		// Payment Method
		$method = $transaction->getPaymentMethod();
		if ($method instanceof Galahad_Payment_Method_CreditCard) {
			$parameters['x_method'] = 'CC';
			$parameters['x_card_num'] = $method->getNumber();
			$parameters['x_exp_date'] = $method->getExpirationDate('mY');
			if (null !== ($code = $method->getCode())) {
				$parameters['x_card_code'] = $code;
			}
		} else {
			/** @see Galahad_Payment_Adapter_Exception */
			require_once 'Galahad/Payment/Adapter/Exception.php';
			throw new Galahad_Payment_Adapter_Exception('Only credit card payments are supported at this time.');
		}
		
		// Invoice & Description
		$this->_setOptionalParameter($parameters, 'x_invoice_num', $transaction->getInvoiceNumber());
		$this->_setOptionalParameter($parameters, 'x_description', $transaction->getComments());
		
		// Billing Customer Information
		if ($billingCustomer = $transaction->getBillingCustomer()) {
			$this->_setOptionalParameter($parameters, 'x_first_name', $billingCustomer->getFirstName());
			$this->_setOptionalParameter($parameters, 'x_last_name', $billingCustomer->getLastName());
			$this->_setOptionalParameter($parameters, 'x_company', $billingCustomer->getCompany());
			
			$address1 = $billingCustomer->getAddressLine1();
			$address2 = $billingCustomer->getAddressLine2();
			$address = $address1 . (empty($address2) ? '' : " {$address2}");
			$this->_setOptionalParameter($parameters, 'x_address', $address);
			
			$this->_setOptionalParameter($parameters, 'x_city', $billingCustomer->getCity());
			$this->_setOptionalParameter($parameters, 'x_state', $billingCustomer->getState());
			$this->_setOptionalParameter($parameters, 'x_zip', $billingCustomer->getPostalCode());
			$this->_setOptionalParameter($parameters, 'x_country', $billingCustomer->getCountry());
			$this->_setOptionalParameter($parameters, 'x_phone', $billingCustomer->getPhoneNumber());
			$this->_setOptionalParameter($parameters, 'x_fax', $billingCustomer->getFaxNumber());
			$this->_setOptionalParameter($parameters, 'x_email', $billingCustomer->getEmail());
			$this->_setOptionalParameter($parameters, 'x_cust_id', $billingCustomer->getCustomerId());
			$this->_setOptionalParameter($parameters, 'x_cust_ip', $billingCustomer->getIpAddress());
		}
		
		// Shipping Customer Information
		if ($shippingCustomer = $transaction->getShippingCustomer()) {
			$this->_setOptionalParameter($parameters, 'x_ship_to_first_name', $shippingCustomer->getFirstName());
			$this->_setOptionalParameter($parameters, 'x_ship_to_last_name', $shippingCustomer->getLastName());
			$this->_setOptionalParameter($parameters, 'x_ship_to_company', $shippingCustomer->getCompany());
			
			$address1 = $shippingCustomer->getAddressLine1();
			$address2 = $shippingCustomer->getAddressLine2();
			$address = $address1 . (empty($address2) ? '' : " {$address2}");
			$this->_setOptionalParameter($parameters, 'x_ship_to_address', $address);
			
			$this->_setOptionalParameter($parameters, 'x_ship_to_city', $shippingCustomer->getCity());
			$this->_setOptionalParameter($parameters, 'x_ship_to_state', $shippingCustomer->getState());
			$this->_setOptionalParameter($parameters, 'x_ship_to_zip', $shippingCustomer->getPostalCode());
			$this->_setOptionalParameter($parameters, 'x_ship_to_country', $shippingCustomer->getCountry());
		}
		
		return $parameters;
	}
	
	/**
	 * Helper function to build $parameters arrays
	 *
	 * @param array $parameters
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	protected function _setOptionalParameter(&$parameters, $key, $value)
	{
		if (!empty($value)) {
			$parameters[$key] = $value;
		}
		
		return $parameters;
	}
}