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
	 * Paginator
	 * 
	 * @var Zend_Paginator_Adapter_Interface
	 */
	protected $_paginator = null;
	
	/**
	 * Fetch all Entities as a Collection
	 * 
	 * @param Galahad_Model_ConstraintInterface $constraint
	 * @return Galahad_Model_Collection
	 */
	public function fetch(Galahad_Model_ConstraintInterface $constraint = null)
	{
        $dao = $this->getDao();
        $data = $dao->fetch($constraint);
        
        $collectionClass = Galahad_Model::getClassSibling($this, Galahad_Model::TYPE_COLLECTION);
        return new $collectionClass($data);
	}
	
	/**
	 * Fetch a single Entity by its ID/Primary Key
	 * 
	 * @param mixed $id Most likely a string, integer, or array
	 */
	public function fetchById($id)
	{
		$dao = $this->getDao();
        $data = $dao->fetchById($id);
                
        if (!$data) {
        	return false;
        }
                
        $entityClass = Galahad_Model::getClassSibling($this, Galahad_Model::TYPE_ENTITY);
        return new $entityClass($data);
	}
	
	/**
	 * Insert an Entity into storage
	 * @param Galahad_Model_Entity $entity
	 * @return mixed ID/Primary Key
	 */
	public function insert(Galahad_Model_Entity $entity)
	{
        $dao = $this->getDao();
        return $dao->insert($entity->toArray());
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
        $dao = $this->getDao();
        return $dao->update($entity->getId(), $entity->toArray());
	}
	
	/**
	 * Delete an Entity from storage
	 * @param Galahad_Model_Entity $entity
	 */
	public function delete(Galahad_Model_Entity $entity)
	{
		if (!$id = $entity->getId()) {
    		// TODO: Test this
    		// TODO: Create a new exception?
    		throw new Galahad_Model_Exception('Cannot delete an entity that does not yet have an ID.');
    	}
    	
        return $this->deleteById($id);
	}
	
	/**
	 * Delete an Entity from storage using its ID/Primary Key
	 * @param mixed $primaryKey Most likely a string, integer, or array
	 */
	public function deleteById($id)
	{
		$dao = $this->getDao();
        return $dao->deleteById($id);
	}
	
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
		return $this->getDao()->getConstraint();
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
			// TODO: Rethink this
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
}



