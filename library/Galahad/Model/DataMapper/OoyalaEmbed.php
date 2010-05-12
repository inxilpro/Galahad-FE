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
 * @package   Galahad_Service
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/** @see Galahad_Service_Ooyala */
require_once 'Galahad/Service/Ooyala.php';

/** @see Galahad_Model_DataMapper_OoyalaEmbed_Constraint */
require_once 'Galahad/Model/DataMapper/OoyalaEmbed/Constraint.php';

/**
 * Provides access to the Ooyala APIs
 *
 * @category   Galahad
 * @package    Galahad_Service
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Model_DataMapper_OoyalaEmbed extends Galahad_Model_DataMapper
{
	/**
	 * Ooyala API Service
	 * @var Galahad_Service_Ooyala
	 */
	protected $_service = null;
	
	/**
	 * Default API partner code
	 * @var string
	 */
	protected static $_defaultPartnerCode;
	
	/**
	 * Default API secret code
	 * @var string
	 */
	protected static $_defaultSecretCode;
	
	public static function setDefaultPartnerCode($partnerCode)
	{
		self::$_defaultPartnerCode = $partnerCode;
	}
	
	public static function setDefaultSecretCode($secretCode)
	{
		self::$_defaultSecretCode = $secretCode;
	}
	
	/**
	 * Set the Ooyala service
	 * @param Galahad_Service_Ooyala $service
	 * @return Galahad_Model_DataMapper_OoyalaEmbed
	 */
	public function setService(Galahad_Service_Ooyala $service)
	{
		$this->_service = $service;
		return $this;
	}
	
	/**
	 * Get Ooyala service
	 */
	public function getService()
	{
		if (null == $this->_service) {
			if (!self::$_defaultPartnerCode || !self::$_defaultSecretCode) {
				throw new Galahad_Service_Ooyala_Exception('No Ooyala & no defaults set.');
			}
			
			$this->_service = new Galahad_Service_Ooyala(self::$_defaultPartnerCode, self::$_defaultSecretCode);
		}
		
		return $this->_service;
	}
	
	/**
	 * Fetch all data matching constaint
	 * 
	 * @param Galahad_Model_DataMapper_ConstraintInterface $constraint
	 * @return array
	 */
	public function _fetch(Galahad_Model_DataMapper_ConstraintInterface $constraint = null)
	{
		if (null !== $constraint) {
			if (!$constraint instanceof Galahad_Model_DataMapper_OoyalaEmbed_Constraint) {
				// TODO: Can this be done in the method declaration now?
				throw new Galahad_Model_Exception(__CLASS__ . '::' . __METHOD__ . ' expects a constraint of type Galahad_Model_DataMapper_OoyalaEmbed_Constraint');
			}
			$constraint = $constraint->toArray();
		}
		
		$results = $this->getService()->query($constraint);
		return $results->toArray();
	}
	
	/**
	 * Fetch single "row" of data by id/primary key
	 * 
	 * @param mixed $id
	 * @return array
	 */
	public function _fetchById($id)
	{
		$results = $this->getService()->query(array('embedCode' => $id));
		$results = $results->toArray();
		return $results[0];
	}
	
	/**
	 * Insert an Entity into storage
	 * @param Galahad_Model_Entity $entity
	 * @return mixed ID/Primary Key
	 */
	public function insert(Galahad_Model_Entity $entity)
	{
		// FIXME
	}
	
	/**
	 * Update an Entity in storage
	 * 
	 * @param mixed $id
	 * @param Galahad_Model_Entity $entity
	 * @return boolean
	 */
	public function update(Galahad_Model_Entity $entity)
	{
		// FIXME
	}
	
	/**
	 * Delete an Entity from storage using its ID/Primary Key
	 * @param mixed $primaryKey Most likely a string, integer, or array
	 */
	public function deleteById($id)
	{
		// FIXME		
	}
	
	/**
	 * Get a count of Entities in storage
	 * 
	 * @param Galahad_Model_DataMapper_ConstraintInterface $constraint
	 * @return integer
	 */
	public function count(Galahad_Model_DataMapper_ConstraintInterface $constraint = null)
	{
		// FIXME
	}
	
	/**
	 * Get a new constraint object for this DataMapper
	 * 
	 * @return Galahad_Model_DataMapper_OoyalaEmbed_Constraint
	 */
	public function getConstraint()
	{
		return new Galahad_Model_DataMapper_OoyalaEmbed_Constraint();
	}
}