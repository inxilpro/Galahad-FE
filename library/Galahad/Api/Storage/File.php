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
 * @see Galahad_Api_Storage
 */
require_once 'Galahad/Api/Storage.php';

/**
 * Used to store API secret keys in the filesystem
 * 
 * @category    Galahad
 * @package	    Galahad_Api
 * @copyright   Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license	GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Api_Storage_File extends Galahad_Api_Storage
{

	/**
	 * Directory name to store data files in
	 *
	 * @var string $_dir
	 */
	private $_dir;

	/**
	 * Constructs storage object and creates storage directory
	 *
	 * @param string $dir directory name to store data files in
	 * @throws Galahad_Api_Storage_Exception
	 */
	public function __construct($dir = null)
	{
		if ($dir === null) {
			$tmp = getenv('TMP');
			if (empty($tmp)) {
				$tmp = getenv('TEMP');
				if (empty($tmp)) {
					$tmp = "/tmp";
				}
			}
			$user = get_current_user();
			if (is_string($user) && !empty($user)) {
				$tmp .= '/' . $user;
			}
			$dir = $tmp . '/galahad/api';
		}
		$this->_dir = $dir;
		if (!is_dir($this->_dir)) {
			if (!@mkdir($this->_dir, 0700, 1)) {
				throw new Galahad_Api_Storage_Exception("{$dir} does not exist and cannot be created.");
			}
		}
	}

	/**
	 * Saves the secret key to storage
	 * 
	 * @param string $apiKey
	 * @param string $secretKey
	 */
	public function setSecret($apiKey, $secretKey)
	{
		$name = $this->_dir . '/secret_' . md5($apiKey);
		if (false === ($f = @fopen($name, 'w'))) {
			return false;
		}
		fwrite($f, $secretKey);
		fclose($f);
		
		return true;
	}

	/**
	 * Retreives the secret key from storage
	 * 
	 * @param string $apiKey
	 */
	public function getSecret($apiKey)
	{
		$name = $this->_dir . '/secret_' . md5($apiKey);
		if (false === ($f = @fopen($name, 'r'))) {
			return false;
		}
		$data = stream_get_contents($f);
		fclose($f);
		
		if (empty($data)) {
			return false;
		}
		
		return $data;
	}
}
