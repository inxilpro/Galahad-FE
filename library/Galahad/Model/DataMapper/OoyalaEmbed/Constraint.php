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

/**
 * Model Constraint for Ooyala query API
 *
 * @category   Galahad
 * @package    Galahad_Service
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Model_DataMapper_OoyalaEmbed_Constraint implements Galahad_Model_DataMapper_ConstraintInterface
{
	/**
	 * Parameters for query API
	 * 
	 * @var array
	 */
	private $_params = array();
	
	/**
	 * Internal count of how many labels are being queried
	 * 
	 * @var $labelCount integer
	 */
	private $labelCount = 0;
	
	/**
	 * Get this constaint as an array
	 * 
	 * @return array
	 */
	public function toArray()
	{
		return $this->_params;
	}
	
	/**
	 * Add query conditions
	 * 
	 * Only supports equality conditions.  For example:
	 * 
	 * status = live
	 * 
	 * @see http://www.ooyala.com/support/docs/backlot_api#query
	 * @param string $cond
	 * @param mixed $value
	 * @param mixed $type Not used
	 */
	public function where($cond, $value = null, $type = null)
	{
		if (!preg_match('/^([a-z]+)\s+(=|>|<)\s+(.*)$/i', $cond, $matches)) {
			throw new InvalidArgumentException("Malformed condition: '{$cond}'");
		}
		
		array_shift($matches);
		list($column, $operator, $condValue) = $matches;
		
		if ('=' != $operator) {
			throw new InvalidArgumentException(__CLASS__ . '::' . __METHOD__ . ' only accepts equality operators (no greater than or less than).');
		}
		
		if ('?' == $condValue) {
			$condValue = $value;
		}
		
		$this->_params[$column] = $condValue;		
		return $this;
	}
	
	/**
	 * Add query conditions and set mode from AND to OR
	 * 
	 * Note that this sets the entire mode to OR, not just this condition
	 * 
	 * @param string $cond
	 * @param mixed $value
	 * @param mixed $type Not used
	 */
	public function orWhere($cond, $value = null, $type = null)
	{
		$this->_params['queryMode'] = 'OR';
		return $this->where($cond, $value, $type);
	}
	
	/**
	 * Order the query results
	 * 
	 * Only supports one ordering column
	 * Supported colums are: uploadedAt and updatedAt (as of 5/11/2010)
	 * 
	 * @param string $spec
	 */
	public function order($spec)
	{
		if (is_array($spec)) {
			if (count($spec) > 1) {
				trigger_error(__CLASS__ . '::' . __METHOD__ . ' does not support multi-column ordering.', E_USER_NOTICE);
			}
			$spec = $spec[0];
		}
		
		$spec = explode(' ', strtolower($spec));
		if (1 == count($spec)) {
			$spec[1] = 'asc';
		} elseif (count($spec) > 2) {
			throw new InvalidArgumentException('Ordering spec must be in the format "column direction"');
		}
		
		list($column, $direction) = $spec;
		$this->_params['orderBy'] = "{$column}, {$direction}";
		
		return $this;
	}
	
	/**
	 * Limit the number of results
	 * 
	 * For multi-page limits, limitPage() is highly recommended over setting an offset
	 * 
	 * @param integer $count
	 * @param integer $offset
	 */
	public function limit($count = null, $offset = null)
	{
		$page = round($offset / $count) - 1;
		if ($page < 1) {
			$page = 1;
		}
		return $this->limitPage($page, $count);
	}
	
	/**
	 * Limit the number of results
	 * 
	 * @param integer $page
	 * @param integer $rowCount
	 */
	public function limitPage($page, $rowCount)
	{
		$this->_params['pageID'] = $page;
		$this->_params['limit'] = $rowCount;
		return $this;
	}
}