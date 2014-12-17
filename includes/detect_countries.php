<?php
/*
Detect Country
  Require IP2C - Copyright (C) 2006 Omry Yadan (omry@yadan.net), all rights reserved

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2009 wokamoto http://dogmap.jp/ (email : wokamoto1973@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists('ip2country') )
	require_once( dirname(__FILE__) . '/ip2c/ip2c.php' );

class DetectCountriesController {
	private $ip2c;
	private $ip2c_dir;

	const IP2C_BIN_FILE = 'ip-to-country.bin';
	const IP2C_VER_FILE = 'db.version';

	static $instance;

	/**********************************************************
	* Constructor
	***********************************************************/
	public function __construct() {
		$this->init();
	}

	public function init() {
		// Check ip-to-country.bin file
		$ip2c_bin_file = dirname(__FILE__) . '/ip2c/' .self::IP2C_BIN_FILE;
		if ( !file_exists($ip2c_bin_file) ) {
			$ip2c_bin_file = null;
		}
		$this->ip2c = new ip2country($ip2c_bin_file);
	}

	public function get_info($ip) {
		if ( !isset($this->ip2c) )
			$this->init();

		$res   = $this->ip2c->get_country($ip);
		$ccode = $res != false ? $res['id2'] : null;
		unset($res);

		return array(
			$ccode ? $this->get_country_name($ccode) : null,
			$ccode
			);
	}

	// Get country name
	private function get_country_name($country_code) {
		$res = $this->ip2c->find_country($country_code);
		return (isset($res['name']) ? $res['name'] : '');
	}
}
