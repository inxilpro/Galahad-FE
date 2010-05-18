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
 * Subscription Payment Transaction
 * 
 * @category   Galahad
 * @package    Galahad_Payment
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Payment_Transaction_Subscription extends Galahad_Payment_Transaction
{
	/**
	 * The subscription ID
	 * 
	 * This is in whatever format the gateway sends subscription IDs
	 * 
	 * @var string
	 */
	protected $_subscriptionId = null;
	
	/**
	 * The subscription name
	 * 
	 * @var string
	 */
	protected $_subscriptionName = null;
	
	/**
	 * Set the subscription ID
	 * 
	 * @param mixed $subscriptionId
	 */
	public function setSubscriptionId($subscriptionId)
	{
		$this->_subscriptionId = (string) $subscriptionId;
		return $this;
	}
	
	/**
	 * Get the subscription ID
	 * 
	 * @return string
	 */
	public function getSubscriptionId()
	{
		return $this->_subscriptionId;
	}
	
	/**
	 * Set the subscription name
	 * 
	 * @param mixed $subscriptionName
	 */
	public function setSubscriptionName($subscriptionName)
	{
		$this->_subscriptionName = (string) $subscriptionName;
		return $this;
	}
	
	/**
	 * Get the subscription Name
	 * 
	 * @return string
	 */
	public function getSubscriptionName()
	{
		return $this->_subscriptionName;
	}
}