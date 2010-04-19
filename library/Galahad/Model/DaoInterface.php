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
 * @package   Galahad
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * Interface for Data Access Objects
 * 
 * @category   Galahad
 * @package    Galahad_Model
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
interface Galahad_Model_DaoInterface
{
	/**
	 * Fetch all data matching constaint
	 * 
	 * @param Galahad_Model_ConstraintInterface $constraint
	 * @return array
	 */
	public function fetchAll(Galahad_Model_ConstraintInterface $constraint);
	
	/**
	 * Fetch single "row" of data by primary key
	 * 
	 * @param mixed $primaryKey
	 * @return array
	 */
	public function fetchByPrimary($primaryKey);
	
	/**
	 * Save an entity into persistent storage
	 * 
	 * @param Galahad_Model_Entity $entity
	 * @return boolean
	 */
	public function save(Galahad_Model_Entity $entity);
	
	/**
	 * Delete an entity from persistent storage
	 * 
	 * @param Galahad_Model_Entity $entity
	 * @return boolean
	 */
	public function delete(Galahad_Model_Entity $entity);
	
	/**
	 * Delete an entity by its primary key from persistent storage
	 * 
	 * @param mixed $primaryKey
	 * @return boolean
	 */
	public function deleteByPrimary($primaryKey);
	
	/**
	 * Count the data matching a constraint (or all)
	 * 
	 * @param Galahad_Model_ConstraintInterface $constraint
	 * @return integer
	 */
	public function count(Galahad_Model_ConstraintInterface $constraint = null);
	
	/**
	 * Get a new constraint object for this DAO
	 * 
	 * @return Galahad_Model_ConstraintInterface
	 */
	public function getConstraint();
}