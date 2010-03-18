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
 * Abstract Payment Adapter
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Payment_Adapter_Abstract
{
	/**
	 * "Normal" mode Ñ transactions are performed as normal
	 * 
	 * @var string
	 */
	const MODE_NORMAL = 'normal';
	
	/**
	 * "Test" mode Ñ transactions aren't actually completed
	 * 
	 * @var string
	 */
	const MODE_TEST = 'test';
	
	/**
	 * Adapter Mode
	 * 
	 * @var string
	 */
	protected $_mode = self::MODE_NORMAL;
	
	/**
	 * Features the adapter supports (each adapter should set this)
	 * 
	 * @var array
	 */
	protected static $_features = array();
	
	/**
	 * Options passed to adapter
	 * 
	 * @var array
	 */
	protected $_options = array();
	
	/**
	 * Determin whether this adapter supports a given feature
	 * 
	 * Example:
	 * 
	 * <code>
	 * if (Galahad_Payment_Adapter_AuthorizeNet::supports(Galahad_Payment::FEATURE_VOID) {
	 *      // Can VOID transactions
	 * }
	 * </code>
	 * 
	 * Or:
	 * 
	 * <code>
	 * if ($adapter->supports(Galahad_Payment::FEATURE_VOID) {
	 *      // Can VOID transactions
	 * }
	 * </code>
	 * 
	 * @param string $feature
	 */
	public static final function supports($feature)
	{
		if (!in_array($feature, Galahad_Payment::$_features)) {
			/** @see Galahad_Payment_Adapter_Exception */
			require_once 'Galahad/Payment/Adapter/Exception.php';
			throw new Galahad_Payment_Adapter_Exception("The feature <b>{$feature}</b> does not exist.");
		}
		
		if (!isset(self::$_features[$feature])) {
			return false;
		}
		
		return self::$_features[$feature];
	}
	
	/**
	 * Constructor
	 * @param array|Zend_Config $options
	 */
	public function __construct($options = null)
    {
        if (null !== $options) {
        	$this->setOptions($options);
        }
    }
    
    /**
     * Set adapter options
     * 
     * @param array|Zend_Config $options
     */
    public function setOptions($options)
    {
    	if (!is_array($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            } else {
                /** @see Galahad_Payment_Adapter_Exception */
                require_once 'Galahad/Payment/Adapter/Exception.php';
                throw new Galahad_Payment_Adapter_Exception('Adapter options must be in an array or a Zend_Config object');
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
            } else {
            	$this->_options[$key] = $value;
            }
        }
    }
    
    /**
     * Set the adapter mode
     * 
     * @param string $mode
     */
    public function setMode($mode)
    {
    	if ($mode !== self::MODE_NORMAL && $mode !== self::MODE_TEST) {
    		/** @see Galahad_Payment_Adapter_Exception */
    		require_once 'Galahad/Payment/Adapter/Exception.php';
    		throw new Galahad_Payment_Adapter_Exception('Invalid payment mode.');
    	}
    	
    	$this->_mode = $mode;
    }
    
    /**
     * Get the adapter mode
     * 
     * @return string
     */
    public function getMode()
    {
    	return $this->_mode;
    }
    
    /**
     * Process a transaction
     * 
     * Available when Galahad_Payment::FEATURE_PROCESS is available
     * Also known as a "Sale" transaction or authorize+capture
     * 
     * @param Galahad_Payment_Transaction $transaction
     * @return Galahad_Payment_Adapter_Response
     */
	public function process(Galahad_Payment_Transaction $transaction) 
	{}
	
	/**
     * Authorize a transaction
     * 
     * Available when Galahad_Payment::FEATURE_PRIOR_AUTHORIZATION is available
     * This is useful for authorizing a transaction and then capturing it at a
     * later date (say, when the product ships)
     * 
     * @param Galahad_Payment_Transaction $transaction
     * @return Galahad_Payment_Adapter_Response
     */
	public function authorize(Galahad_Payment_Transaction $transaction) 
	{}
	
	/**
     * Capture a transaction
     * 
     * Available when Galahad_Payment::FEATURE_PRIOR_AUTHORIZATION is available
     * Only available on transactions that were previously authorized
     * 
     * @param Galahad_Payment_Transaction $transaction
     * @return Galahad_Payment_Adapter_Response
     */
	public function capture(Galahad_Payment_Transaction $transaction) 
	{}
	
	/**
     * Refund a transaction
     * 
     * Available when Galahad_Payment::FEATURE_REFUND is available
     * Only available on transactions that were previously authorized
     * 
     * @param Galahad_Payment_Transaction $transaction
     * @return Galahad_Payment_Adapter_Response
     */
	public function refund(Galahad_Payment_Transaction $transaction) 
	{}
	
	/**
     * Void a transaction
     * 
     * Available when Galahad_Payment::FEATURE_VOID is available
     * 
     * @param Galahad_Payment_Transaction $transaction
     * @return Galahad_Payment_Adapter_Response
     */
	public function void(Galahad_Payment_Transaction $transaction) 
	{}
	
	/**
	 * Send a raw request via the adapter
	 * 
	 * Each adapter should implement this as its method for sending a raw
	 * request to its gateway.  This method provides access to features
	 * that aren't available in all adapters.
	 * 
	 * @param array $parameters
	 */
	abstract public function sendAdapterRequest(array $parameters = array());
}