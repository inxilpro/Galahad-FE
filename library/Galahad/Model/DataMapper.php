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
	 * @param Galahad_Model_DataMapper_ConstraintInterface $constraint
	 * @return Galahad_Model_Collection
	 */
	public function fetch(Galahad_Model_DataMapper_ConstraintInterface $constraint = null)
	{
        $data = $this->_fetch($constraint);
        
        $collectionClass = $this->_getCollectionClass();
        return new $collectionClass($data);
	}
	
	/**
	 * Fetch all Entities as an array
	 * 
	 * @param Galahad_Model_DataMapper_ConstraintInterface $constraint
	 * @return array
	 */
	abstract protected function _fetch(Galahad_Model_DataMapper_ConstraintInterface $constraint = null);
	
	/**
	 * Fetch a single Entity by its ID/Primary Key
	 * 
	 * @param mixed $id Most likely a string, integer, or array
	 */
	public function fetchById($id)
	{
		$data = $this->_fetchById($id);
        if (!$data) {
        	return false;
        }
                
        $entityClass = $this->_getEntityClass();
        return new $entityClass($data);
	}
	
	/**
	 * Fetch a single entity as an array
	 * @param mixed $id
	 * @return array
	 */
	abstract protected function _fetchById($id);
	
	/**
	 * Prepare data for insert/update
	 * 
	 * This method is meant to be subclassed by concrete data mappers.
	 * It's useful when you need to prepare data before insert/update.
	 * 
	 * @todo This should be _prepData($entity) but that might break BC
	 * @param array $data
	 * @return array
	 */
	protected function _prepData(array $data, Galahad_Model_Entity $entity)
	{
		return $data;
	}
	
	/**
	 * Act on data once it's been saved
	 * 
	 * This method is meant to be subclassed by concrete data mappers.
	 * It's useful when you need to do something once the entity has been saved,
	 * particularly if you need its ID.
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function _postSave(Galahad_Model_Entity $entity)
	{
		
	}
	
	/**
	 * Insert an Entity into storage
	 * 
	 * @todo Should be a concrete implementation that calls _prepData and _postSave
	 * @param Galahad_Model_Entity $entity
	 * @return mixed ID/Primary Key
	 */
	abstract public function insert(Galahad_Model_Entity $entity);
	
	/**
	 * Update an Entity in storage
	 * 
	 * @todo Should be a concrete implementation that calls _prepData and _postSave
	 * @param mixed $id
	 * @param Galahad_Model_Entity $entity
	 * @return boolean
	 */
	abstract public function update(Galahad_Model_Entity $entity);
	
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
	abstract public function deleteById($id);
	
	/**
	 * Get a count of Entities in storage
	 * 
	 * @param Galahad_Model_DataMapper_ConstraintInterface $constraint
	 * @return integer
	 */
	abstract public function count(Galahad_Model_DataMapper_ConstraintInterface $constraint = null);
	
	/**
	 * Get a new Constraint object (fluent)
	 * 
	 * @return Galahad_Model_DataMapper_ConstraintInterface
	 */
	public function constraint()
	{
		return $this->getConstraint();
	}
	
	/**
	 * Get a new Constraint object
	 * 
	 * @return Galahad_Model_DataMapper_ConstraintInterface
	 */
	abstract public function getConstraint();
	
	/**
	 * Get a new Paginator object
	 * 
	 * @return Galahad_Paginator_Adapter_Model
	 */
	public function paginator(Galahad_Model_DataMapper_ConstraintInterface $constraint = null, $itemCountPerPage = 10)
	{
		// FIXME: Confirm this works w/ new DAO-less system
		$paginator = new Zend_Paginator(new Galahad_Paginator_Adapter_Model($this, $constraint));
		$paginator->setItemCountPerPage($itemCountPerPage);
		return $paginator;
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
			$this->_entityClass = Galahad_Model::getClassSibling($this, Galahad_Model::TYPE_ENTITY);	
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
			$this->_collectionClass = Galahad_Model::getClassSibling($this, Galahad_Model::TYPE_COLLECTION);	
		}
		
		return $this->_collectionClass;
	}
}



