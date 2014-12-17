<?php
/*
Detect browser
  based on a wordpress plugin by Iman Nurchyo (http://priyadi.net/)
  which is available at http://priyadi.net/archives/2005/03/29/wordpress-browser-detection-plugin/

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2009 - 2010 wokamoto http://dogmap.jp/ (email : wokamoto1973@gmail.com)

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

if (!class_exists('DetectBrowsers'))
	require_once 'class-detect-browsers.php';

class DetectBrowsersController {
	private $_ua_cache = array();

	/**********************************************************
	* Constructor
	***********************************************************/
	public function __construct() {
	}

	/**********************************************************
	* Get Information from user agent
	***********************************************************/
	public function get_info($ua) {
		if ( isset($this->_ua_cache[$ua]) )
			return $this->_ua_cache[$ua];

		$info = DetectBrowsers::get_info($ua);
		$this->_ua_cache[$ua] = array(
			$info['browser']['name'] ,
			$info['browser']['code'] ,
			$info['browser']['version'] ,
			$info['os']['name'] ,
			$info['os']['code'] ,
			$info['os']['version'] ,
			$info['pda']['name'] ,
			$info['pda']['code'] ,
			$info['pda']['version'] ,
			);

		return $this->_ua_cache[$ua];
	}
}
