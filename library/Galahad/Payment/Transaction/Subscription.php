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
	const INTERVAL_UNIT_DAYS = 'days';
	const INTERVAL_UNIT_MONTHS = 'months';
	
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
	 * Default interval type is (1) month
	 * 
	 * @var string
	 */
	protected $_intervalUnit = 'months';
	
	/**
	 * Default interval length is 1 (month)
	 * 
	 * @var $_intervalLength integer
	 */
	protected $_intervalLength = 1;
	
	/**
	 * Date to start subscription
	 * 
	 * @var integer
	 */
	protected $_startDate;
	
	/**
	 * Total number of times for the subscription to occur
	 * 
	 * @var integer
	 */
	protected $_totalOccurrences = 1;
	
	/**
	 * Amount to bill during trial period
	 * @var float
	 */
	protected $_trialAmount;
	
	/**
	 * Total number of times to bill trial amount
	 * @var integer
	 */
	protected $_trialOccurrences;
	
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
	
	/**
	 * Set the subscription interval
	 * 
	 * @param integer $length
	 * @param string $unit
	 */
	public function setInterval($length, $unit)
	{
		if (!is_integer($length)) {
			throw new InvalidArgumentException(__CLASS__ . '::' . __METHOD__ . ' expects an integer length.');
		}
		
		if (self::INTERVAL_UNIT_DAYS == $unit) {
			if ($length < 1 || $length > 365) {
				// TODO: Auth.net doesn't allow shorter than 7 days
				throw new Galahad_Payment_Transaction_Exception('Daily intervals must be between 1 and 365.');
			}
		} elseif (self::INTERVAL_UNIT_MONTHS == $unit) {
			if ($length < 1 || $length > 12) {
				// TODO: Auth.net doesn't allow shorter than 7 days
				throw new Galahad_Payment_Transaction_Exception('Monthly intervals must be between 1 and 12.');
			}
		} else {
			throw new Galahad_Payment_Transaction_Exception('Invalid subscription interval type.');
		}
		
		$this->_intervalLength = $length;
		$this->_intervalUnit = $unit;
	}
	
	/**
	 * Get the subscription interval unit (days or months)
	 * 
	 * @return string
	 */
	public function getIntervalUnit()
	{
		return $this->_intervalUnit;
	}
	
	/**
	 * Get the subscription interval length
	 * 
	 * @return integer
	 */
	public function getIntervalLength()
	{
		return $this->_intervalLength;
	}
	
	/**
	 * Set the subscription start date
	 * 
	 * @param integer|Zend_Date $month Either the month or a Zend_Date object for the entire date
	 * @param integer $day
	 * @param integer $year
	 * @return Galahad_Payment_Transaction_Subscription
	 */
	public function setStartDate($month, $day = null, $year = null)
	{
		if ($month instanceof Zend_Date) {
			$this->_startDate = $month;
		} else {
			if (null == $year) {
				$year = date('Y');
			}
			
			if (null == $day) {
				throw new InvalidArgumentException('You must set an expiration day.');
			}
			
			$this->_startDate = new Zend_Date(array(
				'year' => $year,
				'month' => $month,
				'day' => $day,
			));
		}
		return $this;
	}
	
	/**
	 * Get the subscription start date
	 * 
	 * @return Zend_Date
	 */
	public function getStartDate()
	{
		if (null == ($startDate = $this->_startDate)) {
			$startDate = new Zend_Date();
		}
		
		return $startDate;
	}
	
	/**
	 * Set the number of times the subscription should occur
	 * 
	 * @param int|null $totalOccurrences
	 * @return Galahad_Payment_Transaction_Subscription
	 */
	public function setTotalOccurrences($totalOccurrences)
	{
		if (0 === $totalOccurrences) {
			throw new InvalidArgumentException('Total occurrences must be greater than 0, or NULL.');
		}
		
		$this->_totalOccurrences = $totalOccurrences;
		return $this;
	}
	
	/**
	 * Ge the number of times the subscription should occur
	 * 
	 * @return int|null NULL = unlimited
	 */
	public function getTotalOccurrences()
	{
		return $this->_totalOccurrences;
	}
	
	/**
	 * Set the trial amount
	 * 
	 * @param float|null $trialAmount
	 * @return Galahad_Payment_Transaction_Subscription
	 */
	public function setTrialAmount($trialAmount)
	{
		if (0 === $trialAmount) {
			throw new InvalidArgumentException('Trial amount must be greater than 0, or NULL.');
		}
		
		$this->_trialAmount = $trialAmount;
		return $this;
	}
	
	/**
	 * Ge the trial amount
	 * 
	 * @return float|null NULL = no trial
	 */
	public function getTrialAmount()
	{
		return $this->_trialAmount;
	}
	
	/**
	 * Set the number of times the subscription should charge the trial fee
	 * 
	 * @param int|null $trialOccurrences
	 * @return Galahad_Payment_Transaction_Subscription
	 */
	public function setTrialOccurrences($trialOccurrences)
	{
		if (0 === $trialOccurrences) {
			throw new InvalidArgumentException('Trial occurrences must be greater than 0, or NULL.');
		}
		
		$this->_trialOccurrences = $trialOccurrences;
		return $this;
	}
	
	/**
	 * Ge the number of times the subscription should charge the trial fee
	 * 
	 * @return int|null NULL = unlimited
	 */
	public function getTrialOccurrences()
	{
		return $this->_trialOccurrences;
	}
}



