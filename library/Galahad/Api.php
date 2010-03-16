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

/**
 * @see Galahad_Api_ExpiredException
 */
require_once 'Galahad/Api/ExpiredException.php';

/**
 * @see Galahad_Api_BadAlgorithmException
 */
require_once 'Galahad/Api/BadAlgorithmException.php';

/**
 * @see Galahad_Api_BadSignatureException
 */
require_once 'Galahad/Api/BadSignatureException.php';

/**
 * Provides base functionality to build APIs on top of
 * 
 * @category   Galahad
 * @package    Galahad_Api
 * @throws     Galahad_Api_BadAlgorithmException if the hashing algorithm used by the client is unavailable
 * @throws     Galahad_Api_ExpiredException if the signature used has expired
 * @throws     Galahad_Api_BadSignatureException if the signature provided by the client is invalid
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Api
{
	/**
	 * Constructor 
	 * 
	 * @param string $apiKey
	 * @param integer $expires
	 * @param string $signature
	 * @param string $algorithm
	 */
	public function __construct($apiKey, $expires, $signature, $algorithm = 'sha1')
	{
		// Ensure that algorithm is available
		if (!in_array(hash_algos(), $algorithm)) {
			throw new Galahad_Api_BadAlgorithmException();
		}
		
		// Check expiry
		if (time() > $expires) {
			throw new InterNACHI_Api_ExpiredException();
		}
		
		// Check signature
		if ($signature !== $this->_generateSignature($key, $expires, $algorithm)) {
			throw new InterNACHI_Api_BadSignatureException();
		}
	}
	
	protected function getSecret($key)
	{
		// FIXME
		return '123';
	}
	
	/**
	 * Generate a signature for a given key/expiration pair
	 * 
	 * @param string $key
	 * @param integer $expires
	 * @param string $algorithm
	 */
	protected function _generateSignature($key, $expires, $algorithm)
	{
		return base64_encode(hash_hmac($algorithm, "{$key}\n{$expires}", $this->getSecret($key), true));
	}
}