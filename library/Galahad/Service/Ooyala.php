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

/** @see Zend_Service_Abstract */
require_once 'Zend/Service/Abstract.php';

/** @see Galahad_Ooyala_Service_Response */
require_once 'Galahad/Service/Ooyala/Response.php';

/**
 * Provides access to the Ooyala APIs
 *
 * @category   Galahad
 * @package    Galahad_Service
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Service_Ooyala extends Zend_Service_Abstract
{
    /**
     * Partner Code
     * @var string
     */
    protected $_partnerCode;

    /**
     * Secret Code
     * @var string
     */
    protected $_secretCode;

    /**
     * API End-Point
     * @var string
     */
    protected $_url = 'http://api.ooyala.com/partner/';

    /**
     * Constructor
     * 
     * @param string $partnerCode
     * @param string $secretCode
     */
    public function  __construct($partnerCode, $secretCode)
    {
	if (!is_string($partnerCode) || !is_string($secretCode)) {
	    throw new InvalidArgumentException('Ooyala partner code and secret codes both must be strings');
	}

	$this->_partnerCode = $partnerCode;
	$this->_secretCode = $secretCode;
    }

    /**
     * Query API
     *
     * @param array $params
     * @return Galahad_Service_Ooyala_Response
     */
    public function query(array $params = null)
    {
	return $this->_request('query', $params);
    }

    /**
     * Get thumbnails for a given Embed Code
     *
     * @param string $embedCode
     * @param string $range Thumbnail indices to return, starting from 0, eg. "0-4" for first 5
     * @param string $resolution In the format WIDTHxHEIGHT, eg. 480x360
     * @return Galahad_Service_Ooyala_Response
     */
    public function thumbnailQuery($embedCode, $range = '0', $resolution = '800x600')
    {
	return $this->_request('thumbnails', array(
	    'embedCode' => $embedCode,
	    'range' => $range,
	    'resolution' => $resolution,
	));
    }

    /**
     * Update embed code attributes
     *
     * @param string $embedCode
     * @param array $params
     * @return Galahad_Service_Ooyala_Response
     */
    public function edit($embedCode, array $params)
    {
	$params['embedCode'] = $embedCode;
	return $this->_request('edit', $params);
    }

    /**
     * Update embed code custom meta data
     *
     * @param string $embedCode
     * @param array $metadata
     * @return Galahad_Service_Ooyala_Response
     */
    public function updateMetadata($embedCode, array $metadata)
    {
	if (count($metadata) > 100) {
	    $this->_throwException("Assets can't have more than 100 custom metadata pairs.");
	}

	foreach ($metadata as $key => $value) {
	    if ($key == 'delete') {
		$this->_throwException("'Delete' is a reserved keyword, and cannot be used as a metadata key.");
	    }

	    if (!is_scalar($value)) {
		$this->_throwException('Metadata can only be scalar values (no arrays, objects, etc).');
	    }

	    if (strlen($key) > 128) {
		$this->_throwException('Metadata keys cannot be longer than 128 characters.');
	    }

	    if (strlen($value) > 2048) {
		$this->_throwException('Metadata values cannot be longer than 2048 characters');
	    }
	}

	$metadata['embedCode'] = $embedCode;
	return $this->_request('set_metadata', $metadata);
    }

    /**
     * Delete custom meta data for embed code
     *
     * @param string $embedCode
     * @param array $keys
     * @return Galahad_Service_Ooyala_Response
     */
    public function deleteMetadata($embedCode, array $keys)
    {
	$keys = implode('%00', $keys);
	return $this->_request('set_metadata', array(
	    'embedCode' => $embedCode,
	    'delete' => $keys,
	));
    }

    /**
     * Get all labels associated with account
     *
     * @return Galahad_Service_Ooyala_Response
     */
    public function getLabels()
    {
	return $this->_request('labels', array('mode' => 'listLabels'));
    }

    /**
     * Get all sub-labels for a given label
     *
     * @param string $label
     * @return Galahad_Service_Ooyala_Response
     */
    public function getSubLabels($label)
    {
	// TODO: Prepend "/" if not found?
	return $this->_request('labels', array(
	    'mode' => 'listSubLabels',
	    'label' => $label,
	));
    }

    /**
     * Create a new label
     *
     * Entire path will be created, even if parent labels do not exist.
     *
     * @param string $labels
     * @return Galahad_Service_Ooyala_Response
     */
    public function createLabels($labels)
    {
	return $this->_request('labels', array(
	    'mode' => 'createLabels',
	    'labels' => $this->_stringOrList($labels),
	));
    }

    /**
     * Rename/move a label
     *
     * @param string $label
     * @param string $newLabel
     * @return Galahad_Service_Ooyala_Response
     */
    public function renameLabel($label, $newLabel)
    {
	return $this->_request('labels', array(
	    'mode' => 'renameLabels',
	    'oldlabel' => $label,
	    'newlabel' => $newLabel,
	));
    }

    /**
     * Delete one or more labels
     *
     * @param string|array $labels
     * @return Galahad_Service_Ooyala_Response
     */
    public function deleteLabels($labels)
    {
	return $this->_request('labels', array(
	    'mode' => 'deleteLabels',
	    'labels' => $this->_stringOrList($labels),
	));
    }

    /**
     * Assigns one or more labels to one or more embed codes
     *
     * @param string|array $embedCodes
     * @param string|array $labels
     * @param boolean $create If label does not exist, create it
     * @return Galahad_Service_Ooyala_Response
     */
    public function assignLabels($embedCodes, $labels, $create = false)
    {
	$params = array(
	    'mode' => 'assignLabels',
	    'embedCodes' => $this->_stringOrList($embedCodes),
	    'labels' => $this->_stringOrList($labels),
	);

	if (true == $create) {
	    $params['createLabels'] = 'true';
	}

	return $this->_request('labels', $params);
    }

    /**
     * Unassign one or more labels from one or more embed codes
     *
     * @param string|array $embedCodes
     * @param string|array $labels
     * @param boolean $includeSubLabels Also unassign any sub-labels (/parent also unassigns /parent/child)
     * @param boolean $ignoreNotFound Do not return an error if any given label is not found
     * @return Galahad_Service_Ooyala_Response
     */
    public function unassignLabels($embedCodes, $labels, $includeSubLabels = false, $ignoreNotFound = false)
    {
	$params = array(
	    'mode' => 'unassignLabels',
	    'embedCodes' => $this->_stringOrList($embedCodes),
	    'labels' => $this->_stringOrList($labels),
	);

	if (true == $includeSubLabels) {
	    $params['includeSublabels'] = 'true';
	}

	if (true == $ignoreNotFound) {
	    $params['ignoreNotFound'] = 'true';
	}

	return $this->_request('labels', $params);
    }

    /**
     * Unassign ALL labels from one or more embed codes
     *
     * @param string|array $embedCodes
     * @return Galahad_Service_Ooyala_Response
     */
    public function clearLabels($embedCodes)
    {
	return $this->_request('labels', array(
	    'mode' => 'clearLabels',
	    'embedCodes' => $this->_stringOrList($embedCodes),
	));
    }

    /**
     * List all players in account
     *
     * @return Galahad_Service_Ooyala_Response
     */
    public function listPlayers()
    {
	return $this->_request('players', array('mode' => 'list'));
    }

    /**
     * Assign player to one or more embed codes
     *
     * @param string|array $embedCodes
     * @param string $playerId
     * @return Galahad_Service_Ooyala_Response
     */
    public function assignPlayers($embedCodes, $playerId)
    {
	return $this->_request('players', array(
	    'mode' => 'assign',
	    'embedCodes' => $this->_stringOrList($embedCodes),
	    'pid' => $playerId,
	), false);
    }

    /**
     * List all the items in a channel lineup
     *
     * @param string $embedCode
     * @return Galahad_Service_Ooyala_Response
     */
    public function listChannelItems($embedCode)
    {
	return $this->_request('channels', array(
	    'mode' => 'list',
	    'channelEmbedCode' => $embedCode,
	));
    }

    /**
     * Assign a new lineup to a channel (will overwrite existing lineup)
     *
     * @param string $channelEmbedCode
     * @param string|array $itemEmbedCodes
     * @return Galahad_Service_Ooyala_Response
     */
    public function assignChannelItems($channelEmbedCode, $itemEmbedCodes)
    {
	return $this->_request('channels', array(
	    'mode' => 'assign',
	    'channelEmbedCode' => $channelEmbedCode,
	    'embedCodes' => $this->_stringOrList($itemEmbedCodes),
	), false);
    }

    /**
     * Sign a set of parameters
     *
     * @param array $params
     * @return string
     */
    protected function _sign(array $params)
    {
	$signed = $this->_secretCode;

	ksort($params);
	foreach ($params as $key => $value) {
	    $signed .= "{$key}={$value}";
	}

	$signed = base64_encode(hash('sha256', $signed, true));
	$signed = trim($signed, ' =');

	return $signed;
    }

    /**
     * Send an API request
     *
     * @link http://www.ooyala.com/support/docs/backlot_api
     * @param string $method
     * @param array $params
     * @return Galahad_Service_Ooyala_Response
     */
    protected function _request($method, array $params = null, $xml = true)
    {
	$url = $this->_url . $method;

	if (!isset($params['expires'])) {
	    $params['expires'] = time() + 900;
	}
	$params['signature'] = $this->_sign($params);
	$params['pcode'] = $this->_partnerCode;

	$client = $this->getHttpClient();
	$client->setUri($url);
	$client->setParameterGet($params);
	
	return new Galahad_Service_Ooyala_Response($client->request());
    }

    /**
     * Takes a string or array and returns a comma-separated list
     *
     * @param string|array $items
     * @return string
     */
    private function _stringOrList($items)
    {
	if (!is_array($items)) {
	    $items = array($items);
	}

	return implode(',', $items);
    }

    /**
     * @param string $message
     */
    private function _throwException($message)
    {
	/** @see Galahad_Service_Ooyala_Exception */
	require_once 'Galahad/Service/Ooyala/Exception.php';
	throw new Galahad_Service_Ooyala_Exception($message);
    }
}