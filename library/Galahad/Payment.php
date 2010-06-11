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
 * Class for performing payment operations
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment
{
	const FEATURE_PROCESS = 'process';
	const FEATURE_PRIOR_AUTHORIZATION = 'prior-authorization';
	const FEATURE_VOID = 'void';
	const FEATURE_REFUND = 'refund';
	const FEATURE_RECURRING = 'recurring';
	
	/**
	 * Possible features an adapter can support
	 * 
	 * @var array
	 */
	public static $_features = array(
		self::FEATURE_PROCESS,
		self::FEATURE_PRIOR_AUTHORIZATION,
		self::FEATURE_VOID,
		self::FEATURE_REFUND,
		self::FEATURE_RECURRING,
	);
	
	/**
	 * Generates the appropriate adapter
	 * 
	 * @see Zend_Db Borrows heavily from Zend_Db implementation
	 * @param string $adapter
	 * @param array|Zend_Config $config
	 * @return Galahad_Payment_Adapter_Abstract
	 */
    public static function factory($adapter, $config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        /*
         * Convert Zend_Config argument to plain string
         * adapter name and separate config object.
         */
        if ($adapter instanceof Zend_Config) {
            if (isset($adapter->params)) {
                $config = $adapter->params->toArray();
            }
            if (isset($adapter->adapter)) {
                $adapter = (string) $adapter->adapter;
            } else {
                $adapter = null;
            }
        }

        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($config)) {
            /**
             * @see Galahad_Payment_Exception
             */
            require_once 'Galahad/Payment/Exception.php';
            throw new Galahad_Payment_Exception('Adapter parameters must be in an array or a Zend_Config object');
        }

        /*
         * Verify that an adapter name has been specified.
         */
        if (!is_string($adapter) || empty($adapter)) {
            /**
             * @see Galahad_Payment_Exception
             */
            require_once 'Galahad/Payment/Exception.php';
            throw new Galahad_Payment_Exception('Adapter name must be specified in a string');
        }

        /*
         * Form full adapter class name
         */
        $adapterNamespace = 'Galahad_Payment_Adapter';
        if (isset($config['adapterNamespace'])) {
            if ($config['adapterNamespace'] != '') {
                $adapterNamespace = $config['adapterNamespace'];
            }
            unset($config['adapterNamespace']);
        }

        // TODO: Use a filter here
        $adapterName = $adapterNamespace . '_';
        $adapterName .= str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapter))));

        /*
         * Load the adapter class.  This throws an exception
         * if the specified class cannot be loaded.
         */
        if (!class_exists($adapterName)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($adapterName);
        }

        /*
         * Create an instance of the adapter class.
         * Pass the config to the adapter class constructor.
         */
        $paymentAdapter = new $adapterName($config);

        /*
         * Verify that the object created is a descendent of the abstract adapter type.
         */
        if (!$paymentAdapter instanceof Galahad_Payment_Adapter_Abstract) {
            /**
             * @see Galahad_Payment_Exception
             */
            require_once 'Galahad/Payment/Exception.php';
            throw new Galahad_Payment_Exception("Adapter class '$adapterName' does not extend Galahad_Payment_Adapter_Abstract");
        }

        return $paymentAdapter;
    }
}