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
 * Authorize.Net ARB Response
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment_Adapter_Response_AuthorizeNet_ARB extends Galahad_Payment_Adapter_Response_AuthorizeNet
{
	/**#@+
	 * Authorize.net ARB Error Codes
	 * @var string
	 */
	const ERROR_UNEXPECTED 		= 'E00001';	// An error occurred during processing. Please try again.
											// An unexpected system error occurred while processing this request.
	const ERROR_CONTENT_TYPE	= 'E00002';	// The content-type specified is not supported.
											// The only supported content-types are text/xml and application/xml.
	const ERROR_PARSING_XML		= 'E00003';	// An error occurred while parsing the XML request.
											// This is the result of an XML parser error.
	const ERROR_NO_API_METHOD	= 'E00004';	// The name of the requested API method is invalid.
											// The name of the root node of the XML request is the API method being called. It is not valid.
	const ERROR_TRANS_KEY		= 'E00005';	// The merchantAuthentication.transactionKey is invalid or not present.
											// Merchant authentication requires a valid value for transaction key.
	const ERROR_LOGIN_ID		= 'E00006';	// The merchantAuthentication.name is invalid or not present.
											// Merchant authentication requires a valid value for name.
	const ERROR_AUTHENTICATION	= 'E00007';	// User authentication failed due to invalid authentication values.
											// The name/and or transaction key is invalid.
	const ERROR_INACTIVE		= 'E00008';	// User authentication failed. The payment gateway account or user is inactive.
											// The payment gateway or user account is not currently active.
	const ERROR_TEST_MODE		= 'E00009';	// The payment gateway account is in Test Mode. The request cannot be processed.
											// The requested API method cannot be executed while the payment gateway account is in Test Mode.
	const ERROR_NO_PERMISSION	= 'E00010';	// User authentication failed. You do not have the appropriate permissions.
											// The user does not have permission to call the API.
	const ERROR_ACCESS_DENIED	= 'E00011';	// Access denied. You do not have the appropriate permissions.
											// The user does not have permission to call the API method.
	const ERROR_DUPLICATE		= 'E00012';	// A duplicate subscription already exists.
											// A duplicate of the subscription was already submitted. The duplicate check looks at several fields including payment information, billing information and, specifically for subscriptions, Start Date, Interval and Unit.
	const ERROR_INVALID_FIELD	= 'E00013';	// The field is invalid.
											// One of the field values is not valid.
	const ERROR_MISSING_FIELD	= 'E00014';	// A required field is not present.
											// One of the required fields was not present.
	const ERROR_FIELD_LENGTH	= 'E00015';	// The field length is invalid.
											// One of the fields has an invalid length.
	const ERROR_FIELD_TYPE		= 'E00016';	// The field type is invalid.
											// The field type is not valid.
	const ERROR_START_IN_PAST	= 'E00017';	// The startDate cannot occur in the past.
											// The subscription start date cannot occur before the subscription submission date.  Note: Validation is performed against local server time, which is Mountain Time.
	const ERROR_CC_WILL_EXPIRE	= 'E00018';	// The credit card expires before the subscription startDate.
											// The credit card is not valid as of the start date of the subscription.
	const ERROR_TAXID_REQUIRED	= 'E00019';	// The customer taxId or driversLicense information is required.
											// The customer tax ID or driverÕs license information (driverÕs license number, driverÕs license state, driverÕs license DOB) is required for the subscription.
	const ERROR_NO_ECHECK		= 'E00020';	// The payment gateway account is not enabled for eCheck.Net subscriptions.
											// This payment gateway account is not set up to process eCheck.Net subscriptions.
	const ERROR_NO_CC			= 'E00021';	// The payment gateway account is not enabled for credit card subscriptions.
											// This payment gateway account is not set up to process credit card subscriptions.
	const ERROR_BAD_LENGTH		= 'E00022';	// The interval length cannot exceed 365 days or 12 months.
											// The interval length must be 7 to 365 days or 1 to 12 months.
	const ERROR_TRIAL_OCCUR		= 'E00024';	// The trialOccurrences is required when trialAmount is specified.
											// The number of trial occurrences cannot be zero if a valid trial amount is submitted.
	const ERROR_NO_ARB			= 'E00025';	// Automated Recurring Billing is not enabled.
											// The payment gateway account is not enabled for Automated Recurring Billing.
	const ERROR_TRIAL_REQUIRED	= 'E00026';	// Both trialAmount and trialOccurrences are required.
											// If either a trial amount or number of trial occurrences is specified then values for both must be submitted.
	const ERROR_BAD_TEST		= 'E00027';	// The test transaction was unsuccessful.
											// An approval was not returned for the test transaction.
	const ERROR_TRIAL_TOTAL		= 'E00028';	// The trialOccurrences must be less than totalOccurrences.
											// The number of trial occurrences specified must be less than the number of total occurrences specified.
	const ERROR_NO_PAYMENT		= 'E00029';	// Payment information is required.
											// Payment information is required when creating a subscription.
	const ERROR_NO_SCHEDULE		= 'E00030';	// A paymentSchedule is required.
											// A payment schedule is required when creating a subscription.
	const ERROR_NO_AMOUNT		= 'E00031';	// The amount is required.
											// The subscription amount is required when creating a subscription.
	const ERROR_NO_START		= 'E00032';	// The startDate is required.
											// The subscription start date is required to create a subscription.
	const ERROR_CHANGE_START	= 'E00033';	// The subscription Start Date cannot be changed.
											// Once a subscription is created the Start Date cannot be changed.
	const ERROR_CHANGE_INTERVAL	= 'E00034';	// The interval information cannot be changed.
											// Once a subscription is created the subscription interval cannot be changed.
	const ERROR_NO_SUBSCRIPTION	= 'E00035';	// The subscription cannot be found.
											// The subscription ID for this request is not valid for this merchant.
	const ERROR_CHANGE_TYPE		= 'E00036';	// The payment type cannot be changed.
											// Changing the subscription payment type between credit card and eCheck.Net is not currently supported.
	const ERROR_CANT_CHANGE		= 'E00037';	// The subscription cannot be updated.
											// Subscriptions that are expired, canceled or terminated cannot be updated.
	const ERROR_CANT_CANCEL		= 'E00038';	// The subscription cannot be canceled.
											// Subscriptions that are expired or terminated cannot be canceled.
	const ERROR_XML_NAMESPACE	= 'E00045';	// The root node does not reference a valid XML namespace.
											// An error exists in the XML namespace. This error is similar to E00003.
	/**#@-*/

	/**
	 * Subscription ID
	 * 
	 * @var integer
	 */
	protected $_subscriptionId = null;
	
	/**
	 * Parse raw data returned by the gateway
	 * 
	 * @param stdClass $data
	 * @return array
	 */
	protected function _parseData($data)
	{
		if ('OK' == strtoupper($data->resultCode)) {
			$this->_code = self::CODE_APPROVED;
		}
		
		$this->_responseCode = $data->messages->MessagesTypeMessage->code;
		$this->_adapterMessage = $data->messages->MessagesTypeMessage->text;
		$this->_message = $this->_adapterMessage;
		
		if ($this->isApproved() && isset($data->subscriptionId)) {
			$this->_subscriptionId = $data->subscriptionId;
		}
		
		// TODO: Set messages on some of these?
		switch ($this->_responseCode) {
			case self::ERROR_UNEXPECTED:
			case self::ERROR_BAD_TEST:
				$this->_code = self::CODE_SERVER_ERROR;
				break;
			case self::ERROR_CONTENT_TYPE:
			case self::ERROR_PARSING_XML:
			case self::ERROR_NO_API_METHOD:
			case self::ERROR_TRANS_KEY:
			case self::ERROR_LOGIN_ID:
			case self::ERROR_AUTHENTICATION:
			case self::ERROR_INACTIVE:
			case self::ERROR_TEST_MODE:
			case self::ERROR_NO_PERMISSION:
			case self::ERROR_ACCESS_DENIED:
			case self::ERROR_DUPLICATE:
			case self::ERROR_INVALID_FIELD:
			case self::ERROR_MISSING_FIELD:
			case self::ERROR_FIELD_LENGTH:
			case self::ERROR_FIELD_TYPE:
			case self::ERROR_START_IN_PAST:
			case self::ERROR_CC_WILL_EXPIRE:
			case self::ERROR_TAXID_REQUIRED:
			case self::ERROR_NO_ECHECK:
			case self::ERROR_NO_CC:
			case self::ERROR_BAD_LENGTH:
			case self::ERROR_TRIAL_OCCUR:
			case self::ERROR_NO_ARB:
			case self::ERROR_TRIAL_REQUIRED:
			case self::ERROR_TRIAL_TOTAL:
			case self::ERROR_NO_PAYMENT:
			case self::ERROR_NO_SCHEDULE:
			case self::ERROR_NO_AMOUNT:
			case self::ERROR_NO_START:
			case self::ERROR_CHANGE_START:
			case self::ERROR_CHANGE_INTERVAL:
			case self::ERROR_NO_SUBSCRIPTION:
			case self::ERROR_CHANGE_TYPE:
			case self::ERROR_CANT_CHANGE:
			case self::ERROR_CANT_CANCEL:
			case self::ERROR_XML_NAMESPACE:
				$this->_code = self::CODE_APPLICATION_ERROR;
				break;
		}
	}
	
	/**
	 * Get the ARB Subscription ID
	 * 
	 * @return integer
	 */
	public function getSubscriptionId()
	{
		return $this->_subscriptionId;
	}
}