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
 * Dummy Payment Adapter
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment_Adapter_Dummy extends Galahad_Payment_Adapter_Abstract
{
	/**
	 * Features the adapter supports
	 * @var array
	 */
	protected static $_features = array(
		Galahad_Payment::FEATURE_PROCESS => true,
		Galahad_Payment::FEATURE_PRIOR_AUTHORIZATION => false, // FIXME
		Galahad_Payment::FEATURE_REFUND => false, // FIXME
		Galahad_Payment::FEATURE_VOID => false, // FIXME
	);
	
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
	{
		// FIXME
	}
	
	/**
	 * Send a raw request via the adapter
	 * 
	 * Each adapter should implement this as its method for sending a raw
	 * request to its gateway.  This method provides access to features
	 * that aren't available in all adapters.
	 * 
	 * @param array $parameters
	 */
	public function sendAdapterRequest(array $parameters = array())
	{
		// FIXME
	}
}