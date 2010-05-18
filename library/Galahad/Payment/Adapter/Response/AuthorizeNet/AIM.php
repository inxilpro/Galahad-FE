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
class Galahad_Payment_Adapter_Response_AuthorizeNet_AIM extends Galahad_Payment_Adapter_Response_AuthorizeNet
{
	/**#@+
	 * Authorize.net "response" codes
	 * @var int
	 */
	const RESPONSE_APPROVED					=   1;  // *
	const RESPONSE_DECLINED					=   2;  // *
	const RESPONSE_ERROR					=   3;  // *
	const RESPONSE_REVIEW					=   4;  // *
	/**#@-*/

	/**#@+
	 * Authorize.net "reason" codes
	 * @var int
	 */
	const REASON_APPROVED					=   1;  // *
	const REASON_DECLINED					=   2;  // *
	const REASON_DECLINED_2					=   3;  // *
	const REASON_DECLINED_3					=   4;  // *	Card needs to be picked up
	const REASON_INVALID_AMOUNT 			=   5;  // *
	const REASON_INVALID_NUMBER 			=   6;  // *	Invalid card #
	const REASON_INVALID_EXPIRATION			=   7;  // *
	const REASON_EXPIRED					=   8;  // *
	const REASON_INVALID_ABA				=   9;  //		Invalid financial institution
	const REASON_INVALID_ACCOUNT			=  10;  // 		Account number is invalid
	const REASON_DUPLICATE					=  11;  // *	Duplicate transaction;
	const REASON_NO_AUTH_CODE				=  12;  // 		Requires an x_auth_code parameter
	const REASON_INVALID_LOGIN				=  13;  // 		API login ID is invalid or inactive
	const REASON_INVALID_RESPONSE_URL		=  14;  // 		Relay response or referrer URL doesn't match configured
	const REASON_INVALID_TRANSACTION		=  15;
	const REASON_NO_TRANSACTION				=  16;  // 		No transaction found w/ that ID
	const REASON_CARD_TYPE					=  17;  // * 		Merchant doesn't accept this card type
	const REASON_ACH_NOT_ACCEPTED			=  18;  // 		Merchant doesn't accept ACH transactions
	const REASON_5_MINUTES					=  19;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_2				=  20;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_3				=  21;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_4				=  22;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_5				=  23;  // *	Unknown error, try back in 5 minutes
	const REASON_NOVA_NUMBER				=  24;  // 		Nova Bank number or terminal ID incorrect
	const REASON_5_MINUTES_6				=  25;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_7				=  26;  // *	Unknown error, try back in 5 minutes
	const REASON_AVS_MISMATCH				=  27;  // *	Address verification mismatch
	const REASON_CARD_TYPE_2				=  28;  // *	Merchant doesn't accept this card type
	const REASON_PAYMENTECH_ID				=  29;  // 		The Paymentech ID numbers are incorrect
	const REASON_INVALID_PROCESSOR_CONFIG	=  30;  // 		Invalid configuration at processor
	const REASON_FDC_ID						=  31;  // 		The FDC merchant ID or terminal ID is incorrect
	const REASON_NO_REASON					=  32;  // 		Not used
	const REASON_BLANK_FIELD				=  33;  // *	A required field was left blank
	const REASON_VITAL_ID					=  34;  // 		The VITAL ID nubmers are incorrect
	const REASON_PROCESSING_ERROR			=  35;  // 		Invalid configuration at processor
	const REASON_SETTLEMENT_FAILED			=  36;  // 		Auth approved, but settlement failed
	const REASON_INVALID_NUMBER_2			=  37;
	const REASON_GPS_ID						=  38;  // 		The Global Payment System ID numbers are incorrect
	const REASON_NO_REASON_2				=  39;  // 		Not used
	const REASON_NOT_ENCRYPTED				=  40;  // 		Transaction must be encrypted
	const REASON_DECLINED_4					=  41;  // 		FraudScreen.Net decline/high fraud score
	const REASON_NO_REASON_3				=  42;  // 		Not used
	const REASON_INVALID_CONFIG_2			=  43;  // 		Invalid configuration at processor
	const REASON_DECLINED_5					=  44;  // *	Card code mismatch
	const REASON_DECLINED_6					=  45;  // *	AVS/card code mismatch
	const REASON_SESSION_EXPIRED			=  46;  // 		Need to log in
	const REASON_AMOUNT_MISMATCH			=  47;  // 		Settlement amount must be <= auth amount
	const REASON_PARTIAL_REVERSAL			=  48;  // 		Settlemet cannot be less than auth amount
	const REASON_MAX_AMOUNT					=  49;  // *	Exceeds maximum amount allowed
	const REASON_CANNOT_REFUND				=  50;  // 		Transaction awaiting settlement cannot be refunded
	const REASON_CREDIT_EXCEEDS_AMOUNT		=  51;  // 		Credit amount exceeds amount of transactions
	const REASON_CLIENT_NOT_NOTIFIED		=  52;  //		Transaction was authorized but client cannot be notified
	const REASON_INVALID_FOR_ACH			=  53;  //		Transaction type invalid for ACH transactions
	const REASON_NO_CRITERIA_CREDIT			=  54;  //		The referenced transaction does not meet the criteria for issuing a credit.
	const REASON_SUM_EXCEEDS_DEBIT			=  55;  //		The sum of credits against the referenced transaction would exceed the original debit amount
	const REASON_ACH_ONLY					=  56;  //		Merchant accepts ACH only (no CC)
	const REASON_5_MINUTES_8				=  57;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_9				=  58;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_10				=  59;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_11				=  60;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_12				=  61;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_13				=  62;  // *	Unknown error, try back in 5 minutes
	const REASON_5_MINUTES_14				=  63;  // *	Unknown error, try back in 5 minutes
	const REASON_NO_REASON_4				=  64;  // 		Not used
	const REASON_CARD_CODE_MISMATCH			=  65;  // * 	Declined because of card code mismatch
	const REASON_GATEWAY_SECURITY		   	=  66;  // * 	Transaction does not meet gateway security guidelines
	const REASON_NO_REASON_5				=  67;  // 		Not used
	const REASON_INVALID_VERSION_PARAM	  	=  68;  // * 	Version parameter invalid
	const REASON_INVALID_TRANS_TYPE		  	=  69;  // * 	x_type invalid
	const REASON_INVALID_METHOD				=  70;  // * 	x_method invalid
	const REASON_INVALID_BANK_TYPE		  	=  71;  //		The bank account type is invalid.
	const REASON_INVALID_AUTH_CODE		  	=  72;  //		The authorization code is invalid.
	const REASON_INVALID_BIRTH_DATE		  	=  73;  //		The drivers license date of birth is invalid.
	const REASON_INVALID_DUTY_AMOUNT	   	=  74;  // 		The duty amount is invalid.
	const REASON_INVALID_FREIGHT_AMOUNT		=  75;  //		The freight amount is invalid.
	const REASON_INVALID_TAX_AMOUNT			=  76;  //			The tax amount is invalid.
	const REASON_INVALID_TAX_ID				=  77;  // 		The SSN or tax ID is invalid.
	const REASON_INVALID_CARD_CODE		  	=  78;  //		The Card Code (CVV2/CVC2/CID) is invalid.
	const REASON_INVALID_DL_NUMBER			=  79;  //		The drivers license number is invalid.
	const REASON_INVALID_DL_STATE			=  80;  //		The drivers license state is invalid.
	const REASON_INVALID_FORM_TYPE			=  81;  //		The requested form type is invalid
	const REASON__VERSION_25_ONLY			=  82;  //		Scripts are only supported in version 2.5.
	const REASON_INVALID_REQUESTED_SCRIPT	=  83;  //		The requested script is either invalid or no longer supported.
	const REASON_NO_REASON_6				=  84;  //		This reason code is reserved or not applicable to this API.
	const REASON_NO_REASON_7				=  85;  //		This reason code is reserved or not applicable to this API.
	const REASON_NO_REASON_8				=  86;  //		This reason code is reserved or not applicable to this API.
	const REASON_NO_REASON_9				=  87;  //		This reason code is reserved or not applicable to this API.
	const REASON_NO_REASON_10				=  88;  //		This reason code is reserved or not applicable to this API.
	const REASON_NO_REASON_11				=  89;  //		This reason code is reserved or not applicable to this API.
	const REASON_NO_REASON_12				=  90;  //		This reason code is reserved or not applicable to this API.
	const REASON_25_NOT_SUPPORTED			=  91;  //		Version 2.5 is no longer supported.
	const REASON_METHOD_NOT_SUPPORTED		=  92;  //		The gateway no longer supports the requested method of integration.
	const REASON_NO_REASON_13				=  93;  //
	const REASON_NO_REASON_14				=  94;  //
	const REASON_NO_REASON_15				=  95;  //
	const REASON_NO_REASON_16				=  96;  //
	const REASON_FINGERPRINT_EXPIRED		=  97;  //		This transaction cannot be accepted.
	const REASON_FINGERPRINT_USED			=  98;  //		This transaction cannot be accepted.
	const REASON_FINGERPRINT_NOT_MATCHED	=  99;  //		This transaction cannot be accepted.
	const REASON_INVALID_ECHECK_TYPE		=  100; //		The eCheck.Net type is invalid.
	const REASON_ECHECK_NAME_MISMATCH		=  101; //		The given name on the account and/or the account type does not match the actual account.
	const REASON_WEBLINK_PASSWORD			=  102; //		This request cannot be accepted.
	const REASON_WEBLINK_CREDS_REQUIRED		=  103; //		This transaction cannot be accepted.
	const REASON_UNDER_REVIEW				=  104; //		This transaction is currently under review.
	const REASON_UNDER_REVIEW_2				=  105; //		This transaction is currently under review.
	const REASON_UNDER_REVIEW_3				=  106; //		This transaction is currently under review.
	const REASON_UNDER_REVIEW_4				=  107; //		This transaction is currently under review.
	const REASON_UNDER_REVIEW_5				=  108; //		This transaction is currently under review.
	const REASON_UNDER_REVIEW_6				=  109; //		This transaction is currently under review.
	const REASON_UNDER_REVIEW_7				=  110; //		This transaction is currently under review.
	const REASON_INVALID_AUTH_IND			=  116; //		The authentication indicator is invalid.  
	const REASON_INVALID_CARD_AUTH			=  117; //		The cardholder authentication value is invalid.
	const REASON_INVALID_CARD_AUTH_IND		=  118; //		The combination of authentication indicator and cardholder authentication value is invalid
	const REASON_CARD_AUTH_RECURRING		=  119; //		Transactions having cardholder authentication values cannot be marked as recurring.
	const REASON_PROCESSING_ERROR_2			=  120; //		An error occurred during processing. Please try again.
	const REASON_PROCESSING_ERROR_3			=  121; //		An error occurred during processing. Please try again.
	const REASON_PROCESSING_ERROR_4			=  122; //		An error occurred during processing. Please try again.
	const REASON_ACCOUNT_PERMISSIONS		=  123; //		This account has not been given the permission(s) required for this request.
	const REASON_AVS_MISMATCH_2				=  127; // * 	The transaction resulted in an AVS mismatch. The address provided does not match billing address of cardholder
	const REASON_CANNOT_PROCESS				=  128; //		This transaction cannot be processed.
	const REASON_GATEWAY_ACCOUNT_CLOSED		=  130; // * 	This payment gateway account has been closed.
	const REASON_TRANSACT_NO_ACCEPT			=  131; //		This transaction cannot be accepted at this time
	const REASON_TRANSACT_NO_ACCEPT_2		=  132; //		This transaction cannot be accepted at this time
	const REASON_DECLINED_7					=  141; //		This transaction has been declined.
	const REASON_DECLINED_8					=  145; //		This transaction has been declined.
	const REASON_TRANSACT_AUTHORIZE			=  152; //		The transaction was authorized, but the client could not be notified; the transaction will not be settled.
	const REASON_DECLINED_9					=  165; //		This transaction has been declined.
	const REASON_PROCESSING_ERROR_5			=  170; //		An error occurred during processing. Please contact the merchant.
	const REASON_PROCESSING_ERROR_6			=  171; //		An error occurred during processing. Please contact the merchant.
	const REASON_PROCESSING_ERROR_7			=  172; //		An error occurred during processing. Please contact the merchant.
	const REASON_PROCESSING_ERROR_8			=  173; //		An error occurred during processing. Please contact the merchant.
	const REASON_INVALID_TRANSACT			=  174; //		The transaction type is invalid. Please contact the merchant.
	const REASON_CREDIT_VOID				=  175; //		The processor does not allow voiding of credits.
	const REASON_PROCESSING_ERROR_9			=  180; //		An error occurred during processing. Please try again.
	const REASON_PROCESSING_ERROR_10		=  181; //		An error occurred during processing. Please try again.
	const REASON_CODE_RESERVED				=  185; //		This reason code is reserved or not applicable to this API.
	const REASON_UNDER_REVIEW_8				=  193; //		The transaction is currently under review
	const REASON_DECLINED_10				=  200; //		This transaction has been declined.
	const REASON_DECLINED_11				=  201; //		This transaction has been declined.
	const REASON_DECLINED_12				=  202; //		This transaction has been declined.
	const REASON_DECLINED_13				=  203; //		This transaction has been declined.
	const REASON_DECLINED_14				=  204; //		This transaction has been declined.
	const REASON_DECLINED_15				=  205; //		This transaction has been declined.
	const REASON_DECLINED_16				=  206; //		This transaction has been declined.
	const REASON_DECLINED_17				=  207; //		This transaction has been declined.
	const REASON_DECLINED_18				=  208; //		This transaction has been declined.
	const REASON_DECLINED_19				=  209; //		This transaction has been declined.
	const REASON_DECLINED_20				=  210; //		This transaction has been declined.
	const REASON_DECLINED_21				=  211; //		This transaction has been declined.
	const REASON_DECLINED_22				=  212; //		This transaction has been declined.
	const REASON_DECLINED_23				=  213; //		This transaction has been declined.
	const REASON_DECLINED_24				=  214; //		This transaction has been declined.
	const REASON_DECLINED_25				=  215; //		This transaction has been declined.
	const REASON_DECLINED_26				=  216; //		This transaction has been declined.
	const REASON_DECLINED_27				=  217; //		This transaction has been declined.
	const REASON_DECLINED_28				=  218; //		This transaction has been declined.
	const REASON_DECLINED_29				=  219; //		This transaction has been declined.
	const REASON_DECLINED_30				=  220; //		This transaction has been declined.
	const REASON_DECLINED_31				=  221; //		This transaction has been declined.
	const REASON_DECLINED_32				=  222; //		This transaction has been declined.
	const REASON_DECLINED_33				=  223; //		This transaction has been declined.
	const REASON_DECLINED_34				=  224; //		This transaction has been declined.
	const REASON_DECLINE_RECURR_BILL		=  243; //		Recurring billing is not allowed for this eCheck.Net type.
	const REASON_DECLINE_ECHECK				=  244; //		This eCheck.Net type is not allowed for this Bank Account Type.
	const REASON_DECLINE_ECHECK_2			=  245; //		This eCheck.Net type is not allowed when using the payment gateway hosted payment form.
	const REASON_DECLINE_ECHECK_3			=  246; //		This eCheck.Net type is not allowed.
	const REASON_DECLINE_ECHECK_4			=  247; //		This eCheck.Net type is not allowed.
	const REASON_INVALID_CHECKNO			=  248; //		The check number is invalid.
	const REASON_DECLINED_35				=  250; //		This transaction has been declined.
	const REASON_DECLINED_36				=  251; //		This transaction has been declined.
	const REASON_ORDER_RECEIVED				=  252; //		Your order has been received. Thank you for your business!
	const REASON_ORDER_RECEIVED_2			=  253; //		Your order has been received. Thank you for your business!
	const REASON_DECLINED_37				=  254; //		Your transaction has been declined.
	const REASON_ERROR_OCCUR_10				=  261; //		An error occurred during processing. Please try again.
	const REASON_INVALID_ITEMLINE			=  270; //		The line item [item number] is invalid.
	const REASON_DECLINE_LINEITEMS_30		=  271; //		The number of line items submitted is not allowed. A maximum of 30 line items can be submitted.
	const REASON_INVALID_CREDITCARD			=  315; //		The credit card number is invalid.
	const REASON_INVALID_CREDITCARD_DATE	=  316; //		The credit card expiration date is invalid.
	const REASON_CREDITCARD_EXP				=  317; //		The credit card has expired.
	const REASON_DUPLICATE_TRANSACTION		=  318; //		A duplicate transaction has been submitted.
	const REASON_TRANSACTION_NOT_FOUND		=  319; //		The transaction cannot be found.
	/**#@-*/
	
	/**
	 * Map of fields returned by gateway
	 * 
	 * In the format:
	 * offset => array(variable name, pretty name)
	 * 
	 * @var array
	 */
	protected $_responseFields = array(
		 1 => array('responseCode', 'Response Code'),
		 2 => array('responseSubCode', 'Response Subcode'),
		 3 => array('responseReasonCode', 'Response Reason Code'),
		 4 => array('responseReasonText', 'Response Reason Text'),
		 5 => array('approvalCode', 'Approval Code'),
		 6 => array('avsResultCode', 'AVS Result Code'),
		 7 => array('transactionId', 'Transaction ID'),
		 8 => array('invoiceNumber', 'Invoice Number'),
		 9 => array('description', 'Description'),
		10 => array('amount', 'Amount'),
		11 => array('method', 'Method'),
		12 => array('transactionType', 'Transaction Type'),
		13 => array('customerId', 'Customer ID'),
		14 => array('firstName', 'First Name'),
		15 => array('lastName', 'Last Name'),
		16 => array('company', 'Company'),
		17 => array('billingAddress', 'Billing Address'),
		18 => array('city', 'City'),
		19 => array('state', 'State'),
		20 => array('zip', 'Zip/Postal Code'),
		21 => array('country', 'Country'),
		22 => array('phone', 'Phone Number'),
		23 => array('fax', 'Fax Number'),
		24 => array('email', 'E-Mail Address'),
		25 => array('shipToFirstName', 'Shipping First Name'),
		26 => array('shipToLastName', 'Shipping Last Name'),
		27 => array('shipToCompany', 'Shipping Company'),
		28 => array('shipToAddress', 'Shipping Address'),
		29 => array('shipToCity', 'Shipping City'),
		30 => array('shipToState', 'Shipping State'),
		31 => array('shipToZip', 'Shipping Zip/Postal Code'),
		32 => array('shipToCountry', 'Shipping Country'),
		33 => array('taxAmount', 'Tax Amount'),
		34 => array('dutyAmount', 'Duty Amount'),
		35 => array('freightAmount', 'Freight Amount'),
		36 => array('taxExemptFlag', 'Tax Exempt'),
		37 => array('poNumber', 'Purchase Order Number'),
		38 => array('md5Hash', 'MD5 Hash'),
		39 => array('cvv2ResponseCode', 'Card Code Response'),
		40 => array('cavvResponseCode', 'Cardholder Authentication Verification Response'),
	);
	
	/**
	 * Parsed data from gateway
	 * 
	 * @var string
	 */
	protected $_data = array();
	
	/**
	 * Current reason code
	 * 
	 * @var string
	 */
	protected $_responseReasonCode = null;
	
	/**
	 * Parse raw data returned by the gateway
	 * 
	 * @param string $data
	 * @return array
	 */
	protected function _parseData($data)
	{
		$data = explode('|', $data);
		foreach ($data as $key => $value) {
			$key++;
			if (isset($this->_responseFields[$key])) {
				$this->_data[$this->_responseFields[$key][0]] = $value;
			}
		}
		
		$this->_responseCode = (int) $this->_data['responseCode'];
		$this->_responseReasonCode = (int) $this->_data['responseReasonCode'];
		$this->_adapterMessage = (string) $this->_data['responseReasonText'];
		$this->_message = (string) $this->_data['responseReasonText'];
		
		// Set standardized codes
		switch ($this->_responseCode)
		{
			case self::RESPONSE_APPROVED:
				$this->_code = self::CODE_APPROVED;
				break;
			case self::RESPONSE_DECLINED:
				$this->_code = self::CODE_DECLINED;
				break;
			case self::RESPONSE_REVIEW:
				$this->_code = self::CODE_REVIEW;
				break;
			default:
				$this->_code = self::CODE_UNKNOWN_ERROR;	
		}
		
		// Interpret codes
		// TODO: Flush this out
		switch ($this->_responseReasonCode) {
			case self::REASON_DECLINED_3:
				$this->_message = 'This transaction had been declined; the bank has indicated that the card needs to be picked up.';
				$this->_friendlyMessage = 'Your bank has indicated that the card needs to be picked up.';
				break;
			case self::REASON_INVALID_AMOUNT:
				$this->_code = self::CODE_APPLICATION_ERROR;
				break;
			case self::REASON_INVALID_NUMBER:
			case self::REASON_INVALID_EXPIRATION:
			case self::REASON_EXPIRED:
				$this->_code = self::CODE_USER_ERROR;
				break;
		}
		
		return $this->_data;
	}
	
	/**
	 * Get the parsed response data
	 * 
	 * @return array
	 */
	public function getData()
	{
		if (empty($this->_data)) {
			$this->_parseData($this->_rawDataData);
		}
		
		return $this->_data;
	}
	
	/**
	 * Get the transaction ID
	 *
	 * @return string|int
	 */
	public function getResponseId()
	{
		return $this->_data['transactionId'];
	}
	
	/**
	 * Get the Authorize.net Response Reason Code sent by the gateway
	 * 
	 * @link http://www.authorize.net/support/merchant/Transaction_Response/Response_Reason_Codes_and_Response_Reason_Text.htm
	 * @return int
	 */
	public function getResponseReasonCode()
	{
		return $this->_responseReasonCode;
	}
}