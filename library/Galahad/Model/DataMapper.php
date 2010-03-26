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

/** @see Galahad_Model */
require_once 'Galahad/Model.php';

/**
 * Provides base functionality for data mappers
 * 
 * @todo Should the classes have the option to be set statically?
 * 
 * @category   Galahad
 * @package    Galahad_Model
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Model_DataMapper extends Galahad_Model 
{
    /**
     * Data Access Object used by mapper
     * @var Galahad_Model_Dao_Interface
     */
	protected $_dao;
	
	/**
	 * Class name for DAO
	 * @var string
	 */
	protected $_daoClass;
	
	/**
	 * Class name for generated entities
	 * @var string
	 */
	protected $_entityClass;
	
	/**
	 * Class name for generated collections
	 * @var string
	 */
	protected $_collectionClass;
	
	/**
	 * Class name for generated collections
	 * @var string
	 */
	protected $_constraintClass = 'Galahad_Model_DbTable_Constraint';
	
	/**
	 * Paginator
	 * 
	 * @var Zend_Paginator_Adapter_Interface
	 */
	protected $_paginator = null;
	
	/**
	 * Fetch all Entities as a Collection
	 * 
	 * @todo Maybe make a non-abstract version of this
	 * @param Galahad_Model_ConstraintInterface $constraint
	 * @return Galahad_Model_Collection
	 */
	abstract public function fetchAll(Galahad_Model_ConstraintInterface $constraint = null);
	
	/**
	 * Fetch a single Entity by its Primary Key
	 * 
	 * @todo Maybe make a non-abstract version of this
	 * @param mixed $primaryKey Most likely a string, integer, or array
	 */
	abstract public function fetchByPrimary($primaryKey);
	
	/**
	 * Save a Entity in storage
	 * @param Galahad_Model_Entity $entity
	 * @return boolean
	 */
	abstract public function save(Galahad_Model_Entity $entity);
	
	/**
	 * Delete an Entity from storage
	 * @param Galahad_Model_Entity $entity
	 */
	abstract public function delete(Galahad_Model_Entity $entity);
	
	/**
	 * Delete an Entity from storage using its Primary Key
	 * @param mixed $primaryKey Most likely a string, integer, or array
	 */
	abstract public function deleteByPrimary($primaryKey);
	
	/**
	 * Get a count of Entities in storage
	 * 
	 * @param Galahad_Model_ConstraintInterface $constraint
	 * @return integer
	 */
	public function count(Galahad_Model_ConstraintInterface $constraint = null)
	{
		return $this->getDao()->count($constraint);
	}
	
	/**
	 * Get a new Constraint object
	 * @return Galahad_Model_ConstraintInterface
	 * @todo Should this be 'getConstraint' or is the fluid interface good here?
	 */
	public function constraint()
	{
		$className = $this->_getConstraintClass();
		return new $className($this->getDao()); // TODO: How to handle this dependency?
	}
	
	/**
	 * Get a new Paginator object
	 * 
	 * @return Galahad_Paginator_Adapter_Model
	 */
	public function paginator(Galahad_Model_ConstraintInterface $constraint = null, $itemCountPerPage = 10)
	{
		$paginator = new Zend_Paginator(new Galahad_Paginator_Adapter_Model($this, $constraint));
		$paginator->setItemCountPerPage($itemCountPerPage);
		return $paginator;
	}
	
	/**
	 * Manually set the DAO's class name
	 * @param string $className
	 */
	public function setDaoClass($className)
	{
		$this->_daoClass = $className;
	}
	
	/**
	 * Get the default class name for our DAO
	 * @return string 
	 */
    protected function _getDaoClass()
	{
		if (null == $this->_daoClass) {
		    $namespace = self::getClassNamespace($this);
		    $modelName = self::getClassType($this);
			$this->_daoClass =  "{$namespace}_Model_DbTable_{$modelName}";
		}
		
		return $this->_daoClass;
	}
	
	/**
	 * Manually set the DAO object
	 * @param Galahad_Model_DaoInterface $dao
	 */
    public function setDao(Galahad_Model_DaoInterface $dao)
	{
		$this->_dao = $dao;
	}
	
	/**
	 * Get the DAO
	 * @return Galahad_Model_DaoInterface
	 */
    public function getDao()
	{
		if (!isset($this->_dao)) {
			$className = $this->_getDaoClass();
			$this->_dao = new $className();
		}
		
		return $this->_dao;
	}
	
	/**
	 * Manually set the Entity class to use
	 * @param string $className
	 */	
	public function setEntityClass($className)
	{
		$this->_entityClass = $className;
	}
	
	/**
	 * Get the class name for generated Entities
	 * @return string
	 */
    protected function _getEntityClass()
	{
		if (null == $this->_entityClass) {
			$namespace = self::getClassNamespace($this);
		    $modelName = self::getClassType($this);
			$this->_entityClass =  "{$namespace}_Model_{$modelName}";	
		}
		
		return $this->_entityClass;
	}
	
	/**
	 * Manually set the Collection class to use
	 * @param string $className
	 */
	public function setCollectionClass($className)
	{
		$this->_collectionClass = $className;
	}
	
	/**
	 * Get the collection class
	 * @return string
	 */	
	protected function _getCollectionClass()
	{
		if (null == $this->_collectionClass) {
			$namespace = self::getClassNamespace($this);
		    $modelName = self::getClassType($this);
			$this->_collectionClass =  "{$namespace}_Model_Collection_{$modelName}"; // TODO: Naming?	
		}
		
		return $this->_collectionClass;
	}
	
	/**
	 * Manually set the Constraint class to use
	 * @param string $className
	 */
	public function setConstraintClass($className)
	{
		$this->_constraintClass = $className;
	}
	
	/**
	 * Get the constraint class
	 * @return string
	 */
	protected function _getConstraintClass()
	{
		return $this->_constraintClass;
	}
	
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



