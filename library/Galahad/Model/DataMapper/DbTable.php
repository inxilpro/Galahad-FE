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

/** @see Galahad_Model_DataMapper */
require_once 'Galahad/Model/DataMapper.php';

/** @see Zend_Db_Table */
require_once 'Zend/Db/Table.php';

/**
 * Basic Zend_Db_Table implementation of a data mapper
 * 
 * @category   Galahad
 * @package    Galahad_Model
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Model_DataMapper_DbTable extends Galahad_Model_DataMapper
{
	/**
	 * @var string
	 */
	protected $_dbTableClass = null;
	
	/**
	 * @var Zend_Db_Table
	 */
	protected $_dbTable = null;
	
	public function getDbTable()
	{
		if (null == $this->_dbTable) {
			if (!$class = $this->_dbTableClass) {
				$class = Galahad_Model::getClassSibling($this, Galahad_Model::TYPE_DBTABLE);
			}
			$this->_dbTable = new $class;
		}
		
		return $this->_dbTable;
	}
	
	/**
	 * Fetch all Entities as an array
	 * 
	 * @param Galahad_Model_DataMapper_ConstraintInterface $constraint
	 * @return array
	 */
	protected function _fetch(Galahad_Model_DataMapper_ConstraintInterface $constraint = null)
	{
		if (null !== $constraint && !$constraint instanceof Galahad_Model_DataMapper_DbTable_Constraint) {
			throw new InvalidArgumentException(__CLASS__ . '::' . __METHOD__ . " expects its constraint to be of type 'Galahad_Model_DataMapper_DbTable_Constraint'");
		}
		
		$result = $this->getDbTable()->fetchAll($constraint);
		return $result->toArray();
	}
	
	/**
	 * Fetch a single entity as an array
	 * 
	 * @param mixed $id
	 * @return array
	 */
	protected function _fetchById($id)
	{
		$results = $this->getDbTable()->find($id);
        if (1 !== count($results)) {
        	return false;
        }
        return $results->current()->toArray();
	}
	
	/**
	 * Insert an Entity into storage
	 * @param Galahad_Model_Entity $entity
	 * @return mixed ID/Primary Key
	 */
	public function insert(Galahad_Model_Entity $entity)
	{
		$data = $this->_prepData($data->toArray());
		return $this->getDbTable()->insert($data);
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
		$id = $entity->getId();
		$where = $this->_buildPrimaryKeyWhere($id);
		$data = $this->_prepData($entity->toArray());
		return (1 == $this->getDbTable()->update($data, $where));
	}
	
	/**
	 * Delete an Entity from storage using its ID/Primary Key
	 * @param mixed $primaryKey Most likely a string, integer, or array
	 */
	public function deleteById($id)
	{
		$where = $this->_buildPrimaryKeyWhere($id);
		return (1 == $this->delete($where));
	}
	
	/**
	 * Get a count of Entities in storage
	 * 
	 * @param Galahad_Model_DataMapper_ConstraintInterface $constraint
	 * @return integer
	 */
	public function count(Galahad_Model_DataMapper_ConstraintInterface $constraint = null)
	{
		if (!$constraint instanceof Galahad_Model_DataMapper_DbTable_Constraint) {
			throw new InvalidArgumentException("Galahad_Model_DbTable::count() expects its constraint to be of type 'Galahad_Model_DataMapper_DbTable_Constraint'");
		}
		
		if (null == $constraint) {
			$constraint = $this->getDbTable()->select();
		}
		
		$constraint = clone $constraint;
		$constraint->from($this->getDbTable(), array('c' => 'COUNT(*)'));
		$r = $this->getDbTable()->fetchAll($constraint)->current()->toArray();
		return $r['c'];
	}
	
	/**
	 * Fetch a DbTable constraint object (essentially a Zend_Db_Table_Select object)
	 * 
	 * @return Galahad_Model_DataMapper_DbTable_Constraint
	 */
	public function getConstraint()
	{
		return new Galahad_Model_DataMapper_DbTable_Constraint($this->getDbTable());
	}
	
	/**
	 * Builds a where statement for the primary key
	 * 
	 * @param mixed $id
	 * @return array
	 */
	protected function _buildPrimaryKeyWhere($id)
	{
		$id = $this->_normalizeId($id);
		$where = array();
		foreach ($id as $column => $value) {
			$where[] = $this->getAdapter()->quoteInto("{$column} = ?", $value);
		}
		
		return $where;
	}
	
	/**
	 * Ensures that ID is in col => value format
	 * 
	 * @param mixed $id
	 * @return array
	 */
	protected function _normalizeId($id)
	{
		// TODO: Is there a better way to get this?
		$primary = $this->getDbTable()->info(Zend_Db_Table::PRIMARY);
		
		if (!is_array($id)) {
			$id = array($primary[0] => $id);
		}
		if (count($id) != count($primary)) {
			throw new Galahad_Model_Exception('Primary key is an invalid length');
		}
		
		return $id;
	}
}