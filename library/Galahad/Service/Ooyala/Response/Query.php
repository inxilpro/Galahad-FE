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
 * Query API response
 *
 * @category   Galahad
 * @package    Galahad_Service
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Service_Ooyala_Response_Query 
    extends Galahad_Service_Ooyala_Response
    implements Iterator, ArrayAccess, SeekableIterator, Countable
{
    protected $_totalResults = 0;
    protected $_limit = 500;
    protected $_currentPage = 0;
    protected $_nextPage = 0;

    /**
     * Iterator for items
     * 
     * @var ArrayIterator
     */
    protected $_iterator;

    public function  __construct($responseData)
    {
	parent::__construct($responseData);

	$this->_totalResults = (int) $this->_data['totalResults'];
	$this->_limit = (int) $this->_data['limit'];
	$this->_currentPage = (int) $this->_data['pageID'];
	$this->_nextPage = (int) $this->_data['nextPageID'];

	$this->_data = $this->_data->xpath('/list/item');
	$this->_iterator = new ArrayIterator($this->_data);
    }

    public function getNextPage()
    {
	if (0 == $this->_nextPage) {
	    return;
	}

	if (!$this->_service instanceof Galahad_Service_Ooyala) {
	    $this->_throwException('Cannot get next page w/o an injected service.');
	}

	if (empty($this->_request)) {
	    $this->_throwException("Cannot get next page w/o an injected request.");
	}

	$params = $this->_request;
	$params['limit'] = $this->_limit;
	$params['pageID'] = $this->_nextPage;

	unset($params['expires']);
	unset($params['signature']);
	unset($params['pcode']);

	return $this->_service->query($params);
    }

    /**#@+
     * Implementation of interfaces
     */
    public function current()
    {
	return $this->_iterator->current();
    }

    public function key()
    {
	return $this->_iterator->key();
    }

    public function next()
    {
	return $this->_iterator->next();
    }

    public function rewind()
    {
	return $this->_iterator->rewind();
    }

    public function valid()
    {
	return $this->_iterator->valid();
    }

    public function offsetExists($offset)
    {
	return $this->_iterator->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
	return $this->_iterator->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
	return $this->_iterator->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
	return $this->_iterator->offsetUnset($offset);
    }

    public function seek($position)
    {
	return $this->_iterator->seek($position);
    }

    public function count()
    {
	return $this->_iterator->count();
    }
    /**#@-*/
}