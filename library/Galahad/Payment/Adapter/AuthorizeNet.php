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
		Galahad_Payment::FEATURE_PRIOR_AUTHORIZATION => true,
		Galahad_Payment::FEATURE_PROCESS => true,
		// TODO: Refund and Void transactions
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
		'x_method' => 'CC', // TODO: Support other methods
	);
	
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
		$params = $this->_buildTransactionParamters($transaction);
		$params['x_type'] = 'AUTH_ONLY';
		return $this->sendAdapterRequest($params);
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
		$params = $this->_buildTransactionParamters($transaction);
		$params['x_type'] = 'AUTH_CAPTURE';		
		return $this->sendAdapterRequest($params);
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
		$params = $this->_buildTransactionParamters($transaction);
		
		$transactionId = $transaction->getTransactionId();
		if (empty($transactionId)) {
			/** @see Galahad_Payment_Adapter_Exception */
			require_once 'Galahad/Payment/Adapter/Exception.php';
			throw new Galahad_Payment_Adapter_Exception('You cannot capture transactions that have not yet been authorized.');
		}
		
		$params['x_type'] = 'PRIOR_AUTH_CAPTURE';
		$params['x_trans_id'] = $transactionId;		
		return $this->sendAdapterRequest($params);
	}
	
	/**
	 * Call the Authorize.net API
	 *
	 * @param array $parameters
	 * @return Galahad_Gateway_AuthorizeNet_Response
	 */
	public function sendAdapterRequest(array $parameters = array())
	{
		// Determine URL
		if ($this->_apiType == self::API_TYPE_TEST) {
			$parameters['x_test_request'] = 'TRUE';
		}
		
		// Login
		$parameters['x_login'] = $this->_apiLoginId;
		$parameters['x_tran_key'] = $this->_apiTransactionKey;
		
		// Build Fields
		$fields = '';
		$parameters = array_merge($this->_defaultParameters, $parameters);
		foreach ($parameters as $key => $value) {
			$fields .= "{$key}=" . urlencode($value) . '&';
		}
		
		// Init cURL
		// TODO: Error check	
		$ch = curl_init($this->_apiUrl);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim($fields, '& '));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // TODO: Optional
		
		// Call API
		$data = curl_exec($ch);
		curl_close($ch);
		
		/** @see Galahad_Payment_Adapter_Response_AuthorizeNet */
		require_once 'Galahad/Payment/Adapter/Response/AuthorizeNet.php';
		return new Galahad_Payment_Adapter_Response_AuthorizeNet($data, $parameters);
	}
	
	protected function _buildTransactionParamters(Galahad_Gateway_Transaction $transaction)
	{
		$params = array();
		
		// Amount
		$this->_setOptionalParameter($params, 'amount', $transaction->getAmount());
		
		// Card
		// TODO: Use setOptionalParameter?
		$creditCard = $transaction->getCreditCard();
		if (false !== $creditCard) {
			$params['x_card_num'] = $creditCard->getNumber();
			$params['x_exp_date'] = $creditCard->getExpirationDate('mY');
			
			if (false !== $creditCard->getCode()) {
				$params['x_card_code'] = $creditCard->getCode();
			}
		}
		
		// Invoice & Description
		$this->_setOptionalParameter($params, 'invoice_num', $transaction->getInvoiceNumber());
		$this->_setOptionalParameter($params, 'description', $transaction->getDescription());
		
		// Customer Information
		$this->_setOptionalParameter($params, 'first_name', $transaction->getCustomerFirstName());
		$this->_setOptionalParameter($params, 'last_name', $transaction->getCustomerLastName());
		$this->_setOptionalParameter($params, 'company', $transaction->getCustomerCompany());
		$this->_setOptionalParameter($params, 'address', $transaction->getCustomerAddress());
		$this->_setOptionalParameter($params, 'city', $transaction->getCustomerCity());
		$this->_setOptionalParameter($params, 'state', $transaction->getCustomerState());
		$this->_setOptionalParameter($params, 'zip', $transaction->getCustomerPostal());
		$this->_setOptionalParameter($params, 'country', $transaction->getCustomerCountry());
		$this->_setOptionalParameter($params, 'phone', $transaction->getCustomerPhone());
		$this->_setOptionalParameter($params, 'fax', $transaction->getCustomerFax());
		$this->_setOptionalParameter($params, 'email', $transaction->getCustomerEmail());
		$this->_setOptionalParameter($params, 'cust_id', $transaction->getCustomerId());
		$this->_setOptionalParameter($params, 'cust_ip', $transaction->getCustomerIpAddress());
		
		// Mailing Information
		$this->_setOptionalParameter($params, 'ship_to_first_name', $transaction->getMailingFirstName());
		$this->_setOptionalParameter($params, 'ship_to_last_name', $transaction->getMailingLastName());
		$this->_setOptionalParameter($params, 'ship_to_company', $transaction->getMailingCompany());
		$this->_setOptionalParameter($params, 'ship_to_address', $transaction->getMailingAddress());
		$this->_setOptionalParameter($params, 'ship_to_city', $transaction->getMailingCity());
		$this->_setOptionalParameter($params, 'ship_to_state', $transaction->getMailingState());
		$this->_setOptionalParameter($params, 'ship_to_zip', $transaction->getMailingPostal());
		$this->_setOptionalParameter($params, 'ship_to_country', $transaction->getMailingCountry());
		
		return $params;
	}
	
	/**
	 * Helper function to build $params arrays
	 *
	 * @param array $params
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	protected function _setOptionalParameter(&$params, $key, $value)
	{
		if (false !== $value) {
			$params['x_' . $key] = $value;
		}
		
		return $params;
	}
}