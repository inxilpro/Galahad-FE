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

/**
 * Paginator for Galahad_Model_Entity objects via a Galahad_Model_DataMapper
 * 
 * @category   Galahad
 * @package    Galahad_Paginator
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
 
class Galahad_Paginator_Adapter_Model implements Zend_Paginator_Adapter_Interface
{
	/**
	 * Data mapper
	 * 
	 * @var Galahad_Model_DataMapper
	 */
	private $_mapper;
	
	/**
	 * Any constraints to apply
	 * 
	 * @var Galahad_Model_ConstraintInterface
	 */
	private $_constraint;
	
	/**
	 * The number of models counted
	 * 
	 * @var integer
	 */
	private $_count = null;
	
	/**
	 * Constructor
	 * 
	 * @todo  Might want to reset any limit set on constraint, or throw an exception...
	 * @param Galahad_Model_DataMapper $mapper
	 * @param Galahad_Model_ConstraintInterface $constraint
	 */
	public function __construct(Galahad_Model_DataMapper $mapper, Galahad_Model_ConstraintInterface $constraint = null)
	{
		$this->_mapper = $mapper;
		
		if (null == $constraint) {
			$constraint = $mapper->constraint();
		}
		$this->_constraint = $constraint;
	}
	
	/**
     * Returns the total number of rows in the collection.
     *
     * @return integer
     */
	public function count()
	{
		if (null == $this->_count) {
			$this->_count = $this->_mapper->count($this->_constraint);
		}
		
		return $this->_count;
	}
    
    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
    	// echo "<p>Offset: '{$offset}' - Item Count Per Page: '{$itemCountPerPage}'</p>";
    	// public function limit($count = null, $offset = null);
    	$this->_constraint->limit($itemCountPerPage, $offset);
    	return $this->_mapper->fetchAll($this->_constraint);
    }
}