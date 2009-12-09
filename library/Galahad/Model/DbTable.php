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

/** @see Zend_Db_Table */
require_once 'Zend/Db/Table.php';

/**
 * Provides base functionality for data mappers
 * 
 * @category   Galahad
 * @package    Galahad_Model
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Model_DbTable extends Zend_Db_Table 
{
	/**
	 * Saves data to the table
	 * 
	 * Sorts out whether it's an insert or update based on primary key(s)
	 * 
	 * @param array $data
	 * @return mixed
	 */
	public function save(array $data)
	{
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
	
	/**
	 * Fetches a row by primary key(s)
	 * 
	 * @param mixed|array $primaryKey
	 * @return array|false
	 */
	public function fetchByPrimary($primaryKey)
	{
		$results = call_user_func_array(array($this, 'find'), (array) $primaryKey);

        if (1 != count($results)) {
        	return false;
        }
        
        $data = $results->current()->toArray();
        return $data;
	}
	
	/**
	 * Deletes an entity from storage based on its primary key
	 * 
	 * @param mixed|array $primaryKey
	 * @return boolean
	 */
	public function deleteByPrimary($primaryKey)
	{
		$this->_setupPrimaryKey();
		
		$keyColumns = $this->_primary;
		$primaryKey = (array) $primaryKey;
		
		$where = array();
		foreach ($keyColumns as $i => $column) {
			$where[] = $this->getAdapter()->quoteInto("{$column} = ?", $primaryKey[$i - 1]);
		}
		
		return (1 == $this->delete($where));
	}
}