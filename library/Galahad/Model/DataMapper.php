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
 * @copyright Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/** @see Galahad_Model */
require_once 'Galahad/Model.php';

/**
 * Provides base functionality for data mappers
 * 
 * @category   Galahad
 * @package    Galahad_Model
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
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
	 * Fetch all Entities as a Collection
	 */
	abstract public function fetchAll();
	
	/**
	 * Fetch a single Entity by its Primary Key
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
	 * Manually set the DAO's class name
	 * @param string $className
	 */
	public function setDaoClass($className)
	{
		$this->_daoClass = $className;
	}
	
	/**
	 * Get the default class name for our DAO
	 * @return Galahad_Model_DaoInterface 
	 */
    protected function _getDaoClass()
	{
		if (null == $this->_daoClass) {
		    $namespace = Galahad::getClassNamespace($this);
		    $modelName = Galahad::getClassType($this);
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
			$namespace = Galahad::getClassNamespace($this);
		    $modelName = Galahad::getClassType($this);
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
	
		
	protected function _getCollectionClass()
	{
		if (null == $this->_collectionClass) {
			$namespace = Galahad::getClassNamespace($this);
		    $modelName = Galahad::getClassType($this);
			$this->_collectionClass =  "{$namespace}_{$modelName}Collection"; // TODO: Naming?	
		}
		
		return $this->_collectionClass;
	}
}



