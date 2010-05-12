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

/** @see Zend_Db_Table */
require_once 'Zend/Db/Table.php';

/**
 * Provides base functionality for data mappers
 * 
 * @category   Galahad
 * @package    Galahad_Model
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Model_DbTable 
	extends Zend_Db_Table
	implements Galahad_Model_DaoInterface
{
	/**
	 * Fetch all rows matching constraint
	 * 
	 * @param Galahad_Model_DbTable_Constraint $constraint
	 * @return array
	 */
	public function fetch(Galahad_Model_ConstraintInterface $constraint = null)
	{
		if (null !== $constraint && !$constraint instanceof Galahad_Model_DbTable_Constraint) {
			throw new InvalidArgumentException("Galahad_Model_DbTable::fetch() expects its constraint to be of type 'Galahad_Model_DbTable_Constraint'");
		}
		
		$result = parent::fetchAll($constraint);
		return $result->toArray();
	}
	
	/**
	 * Fetches a row by primary key(s)
	 * 
	 * @param mixed|array $primaryKey
	 * @return array|false
	 */
	public function fetchById($id)
	{
		$results = call_user_func_array(array($this, 'find'), (array) $id);

        if (1 != count($results)) {
        	return false;
        }
        
        $data = $results->current()->toArray();
        return $data;
	}
	
	/**
	 * Inserts a new entity into the database
	 * 
	 * @param array $data
	 * @return mixed The entity's primary key data
	 */
	public function insert(array $data)
	{
		$data = $this->_prepData($data);
		return parent::insert($data);
	}
	
	/**
	 * Update an existing entity in the database
	 * 
	 * @param mixed $id
	 * @param array $data
	 * @return boolean
	 */
	public function update($id, array $data)
	{
		$where = $this->_buildPrimaryKeyWhere($id);
		$data = $this->_prepData($data);
		return (1 == parent::update($data, $where));
	}
	
	/**
	 * Saves data to the table
	 * 
	 * Sorts out whether it's an insert or update based on primary key(s)
	 * 
	 * @param array $data
	 * @return mixed
	 */
	/*
	public function save(Galahad_Model_Entity $entity)
	{
		$data = $entity->toArray();
		$this->_setupPrimaryKey();
		
        $keyCount = 0;
        $primary = $this->_primary;
        foreach ($primary as $column) {
        	if (isset($data[$column])) {
        		$keyCount++;
        	}
        }
        if ($keyCount > 0 && $keyCount != count($primary)) {
        	// TODO: Should this throw an exception?  Are there cases where one portion of the key would be set?
        	throw new LengthException(get_class($this) . ' expects ' . count($primary) . ' column(s) to be set for the primary key');
        }
        
        if ($keyCount) {
        	$where = array();
        	foreach ($primary as $column) {
        		$where[] = $this->getAdapter()->quoteInto("{$column} = ?", $data[$column]);
        	}
        	
        	return $this->update($data, $where);
        }
        
        return $this->insert($data);
	}
	*/
	
	/**
	 * Deletes an entity from storage based on its primary key
	 * 
	 * @param mixed|array $primaryKey
	 * @return boolean
	 */
	public function deleteById($id)
	{
		$where = $this->_buildPrimaryKeyWhere($id);
		return (1 == $this->delete($where));
	}
	
	/**
	 * Gets a count of models matching constraint
	 * 
	 * @param Galahad_Model_DbTable_Constraint $constraint
	 */
	public function count(Galahad_Model_ConstraintInterface $constraint = null)
	{
		if (!$constraint instanceof Galahad_Model_DbTable_Constraint) {
			throw new InvalidArgumentException("Galahad_Model_DbTable::count() expects its constraint to be of type 'Galahad_Model_DbTable_Constraint'");
		}
		
		if (null == $constraint) {
			$constraint = $this->select();
		}
		
		$constraint = clone $constraint;
		$constraint->from($this, array('c' => 'COUNT(*)'));
		$r = $this->fetchAll($constraint)->current()->toArray();
		return $r['c'];
	}
	
	/**
	 * Fetch a DbTable constraint object (essentially a Zend_Db_Table_Select object)
	 * 
	 * @return Galahad_Model_DbTable_Constraint
	 */
	public function getConstraint()
	{
		return new Galahad_Model_DbTable_Constraint($this);
	}
	
	/**
	 * Prepare data for insert/update
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function _prepData(array $data)
	{
		return $data;
	}
	
	/**
	 * Ensures that ID is in col => value format
	 * 
	 * @param mixed $id
	 * @return array
	 */
	protected function _normalizeId($id)
	{
		$this->_setupPrimaryKey();
		
		if (!is_array($id)) {
			$id = array($this->_primary[0] => $id);
		}
		if (count($id) != count($this->_primary)) {
			throw new Galahad_Model_Exception('Primary key is an invalid length');
		}
		
		return $id;
	}
	
	protected function _buildPrimaryKeyWhere($id)
	{
		$id = $this->_normalizeId($id);
		$where = array();
		foreach ($this->_primary as $column) {
			if (!isset($id[$column])) {
				throw new Exception("Column '{$column}' must be set for this operation.");
			}
			$where[] = $this->getAdapter()->quoteInto("{$column} = ?", $id[$column]);
		}
		
		return $where;
	}
	
	/**
	 * @todo  Get rid of thisÑseems messy
	 * @param array $data
	 * @param string $indexFrom
	 * @param string $indexTo
	 * @param string $className
	 * @param string $method
	 */
	protected function _convertModelToId(array &$data, $indexFrom, $indexTo, $className, $method = 'getId')
	{
		if (isset($data[$indexFrom])) {
	        if ($data[$indexFrom] instanceof $className) {
	    		$data[$indexTo] = $data[$indexFrom]->{$method}();
	    	} elseif (is_int($data[$indexFrom])) {
	    		$data[$indexTo] = $data[$indexFrom];
	    	} else {
	    		$data[$indexTo] = null;
	    	}
	    	unset($data[$indexFrom]);
        }
        
        return $data;
	}
}