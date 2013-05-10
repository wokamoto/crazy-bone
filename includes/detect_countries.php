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

class DetectCountriesController {
	var $_ip2c;
	var $_ip2c_dir;
	var $_ip2c_country_cache;

	const IP2C_DB_VER_CHECK_URL = 'http://files.firestats.cc/ip2c/ip-to-country.latest';
	const IP2C_BIN_FILE = 'ip-to-country.bin';
	const IP2C_VER_FILE = 'db.version';


	/**********************************************************
	* Constructor
	***********************************************************/
	function DetectCountriesController() {
		$this->__construct();
	}
	function __construct() {
		$this->_ip2c_dir  = dirname( __FILE__ ) . '/ip2c/';

		// Require Class ip2country
		if ( !class_exists('ip2country') )
			require( $this->_ip2c_dir . 'ip2c.php' );

		// Check ip-to-country.bin file
		$ip2c_bin_file = $this->_ip2c_dir . self::IP2C_BIN_FILE;
		if ( !file_exists($ip2c_bin_file) ) {
			$this->ip2c_db_ver_check(false);
			if ( !file_exists($ip2c_bin_file) ) $ip2c_bin_file = null;
		}
		$this->_ip2c = new ip2country($ip2c_bin_file);
		$this->_ip2c_country_cache = array();
	}

	function get_info($ip) {
		$ip2c_res = $this->_ip2c->get_country($ip);
		$ccode = ( $ip2c_res != false ? $ip2c_res['id2'] : null );
		unset ($ip2c_res);

		return array(
			  ( $ccode != null ? $this->_get_country_name($ccode) : null )
			, $ccode
			);
	}

	// Get country name
	function _get_country_name($country_code) {
		$res = isset($this->_ip2c_country_cache[$country_code])
			? $this->_ip2c_country_cache[$country_code]
			: false;

		if (!$res) {
			$res = $this->_ip2c->find_country($country_code);
			$this->_ip2c_country_cache[$country_code] = $res;
		}

		return (isset($res['name']) ? $res['name'] : '');
	}

	// function ip2c_db_ver_check
	function ip2c_db_ver_check($ver_check = true) {
		$ip2c_ver_file = $this->_ip2c_dir . self::IP2C_VER_FILE;
		$ip2c_bin_file = $this->_ip2c_dir . self::IP2C_BIN_FILE;

		$ip2c_ver = ( file_exists($ip2c_ver_file)
			? file_get_contents($ip2c_ver_file)
			: ''
			);
		$response = $this->_request( self::IP2C_DB_VER_CHECK_URL );
		if ( preg_match_all('/^(version|bin_url|zip_url)=(.*)$/im', $response, $matches, PREG_SET_ORDER) ) {
			foreach ((array) $matches as $match) {
				switch (strtolower($match[1])) {
				case 'version':
					$ip2c_ver_current = trim($match[2]);
					break;
				case 'bin_url':
					$ip2c_bin_url = trim($match[2]);
					break;
				case 'zip_url':
					$ip2c_zip_url = trim($match[2]);
					break;
				}
			}
			unset($match);
		}
		unset($matches);

		if ( !$ver_check || ($ver_check && version_compare($ip2c_ver, $ip2c_ver_current) === 1) ) {
			// Clean up working directory
			$working_dir = trailingslashit( defined('WP_CONTENT_DIR')
				? WP_CONTENT_DIR
				: trailingslashit(ABSPATH) . 'wp-content'
				) . 'upgrade/ip2c/';
			if ( file_exists($working_dir) ) {
				if ( file_exists($working_dir . self::IP2C_VER_FILE) ) @unlink ($working_dir . self::IP2C_VER_FILE);
				if ( file_exists($working_dir . self::IP2C_BIN_FILE) ) @unlink ($working_dir . self::IP2C_BIN_FILE);
				@rmdir ($working_dir);
			}

			// Download the package
			$download_file = $this->_download_url( $ip2c_zip_url );
			if ( is_wp_error($download_file) )
				return new WP_Error('download_failed', __('Download failed.'), $download_file->get_error_message());

			// Unzip package to working directory
			$result = $this->_unzip_file( $download_file, $working_dir );

			// Once extracted, delete the package
			unlink( $download_file );

			if ( is_wp_error($result) ) {
				$wp_filesystem->delete( $working_dir, true );
				return $result;
			}

			if ( file_exists($ip2c_ver_file . '.bak') ) @unlink ($ip2c_ver_file . '.bak');
			if ( file_exists($ip2c_bin_file . '.bak') ) @unlink ($ip2c_bin_file . '.bak');
			@copy ($working_dir . self::IP2C_VER_FILE, $ip2c_ver_file);
			@copy ($working_dir . self::IP2C_BIN_FILE, $ip2c_bin_file);
			@unlink ($working_dir . self::IP2C_VER_FILE);
			@unlink ($working_dir . self::IP2C_BIN_FILE);
			@rmdir ($working_dir);
		}
	}

	// function _request
	function _request($url, $timeout = 5) {
		// Require Class Snoopy
		if ( !class_exists('Snoopy') )
			require_once( dirname( __FILE__ ) . '/class-snoopy.php');

		$snoopy = new Snoopy;
		$snoopy->read_timeout = $timeout;
		$snoopy->timed_out = true;
		$snoopy->fetch($url);
		$response  = $snoopy->results;
		$http_code = $snoopy->response_code;
		unset($snoopy);

		return (strpos($http_code, '200') !== FALSE ? $response : false);
	}

	// function _download_url -- Based on wp-admin/includes/file.php
	/**
	 * Downloads a url to a local file using the Snoopy HTTP Class.
	 *
	 * @since unknown
	 * @todo Transition over to using the new HTTP Request API (jacob).
	 *
	 * @param string $url the URL of the file to download
	 * @return mixed WP_Error on failure, string Filename on success.
	 */
	function _download_url( $url ) {
		//WARNING: The file is not automatically deleted, The script must unlink() the file.
		if ( ! $url )
			return new WP_Error('http_no_url', __('Invalid URL Provided'));

		$tmpfname = trailingslashit( defined('WP_CONTENT_DIR')
			? WP_CONTENT_DIR
			: trailingslashit(ABSPATH) . 'wp-content'
			) . 'upgrade/' . basename($url);
		if ( file_exists($tmpfname) )
			@unlink($tmpfname);

		if ( ! $tmpfname )
			return new WP_Error('http_no_file', __('Could not create Temporary file'));

		$handle = @fopen($tmpfname, 'wb');
		if ( ! $handle )
			return new WP_Error('http_no_file', __('Could not create Temporary file'));

		$response = wp_remote_get($url, array('timeout' => 30));

		if ( is_wp_error($response) ) {
			fclose($handle);
			unlink($tmpfname);
			return $response;
		}

		if ( $response['response']['code'] != '200' ){
			fclose($handle);
			unlink($tmpfname);
			return new WP_Error('http_404', trim($response['response']['message']));
		}

		fwrite($handle, $response['body']);
		fclose($handle);

		return $tmpfname;
	}

	// function _unzip_file -- Based on wp-admin/includes/file.php
	/**
	 * {@internal Missing Short Description}}
	 *
	 * @since unknown
	 *
	 * @param unknown_type $file
	 * @param unknown_type $to
	 * @return unknown
	 */
	function _unzip_file($file, $to) {
		$to = trailingslashit($to);
		if ( !file_exists($to) ) @mkdir($to);

		// Unzip uses a lot of memory
		@ini_set('memory_limit', '256M');

		// Require Class PclZip
		if ( !class_exists('PclZip') ) {
			if ( file_exists(ABSPATH . 'wp-admin/includes/class-pclzip.php') )
				require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
			else
				require_once( dirname( __FILE__ ) . '/class-pclzip.php' );
		}

		// Is the archive valid?
		$archive = new PclZip($file);
		if ( false == ($archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING)) )
			return new WP_Error('incompatible_archive', __('Incompatible archive'), $archive->errorInfo(true));
		if ( 0 == count($archive_files) )
			return new WP_Error('empty_archive', __('Empty archive'));

		foreach ((array) $archive_files as $archive_file) {
			$path = $archive_file['folder'] ? $archive_file['filename'] : dirname($archive_file['filename']);

			$path = explode('/', $path);
			for ( $i = count($path); $i >= 0; $i-- ) { //>=0 as the first element contains data
				if ( empty($path[$i]) )
					continue;
				$tmppath = $to . implode('/', array_slice($path, 0, $i) );
				if ( !file_exists($tmppath) ) {//Found the highest folder that exists, Create from here
					for ( $i = $i + 1; $i <= count($path); $i++ ) { //< count() no file component please.
						$tmppath = $to . implode('/', array_slice($path, 0, $i) );
						if ( !file_exists($tmppath) && ! mkdir($tmppath) )
							return new WP_Error('mkdir_failed', __('Could not create directory'), $tmppath);
					}
					break; //Exit main for loop
				}
			}

			// We've made sure the folders are there, so let's extract the file now:
			if ( ! $archive_file['folder'] ) {
				$handle = @fopen($to . $archive_file['filename'], 'wb');
				if ( ! $handle )
					return new WP_Error('http_no_file', __('Could not create Temporary file'));
				fwrite($handle, $archive_file['content']);
				fclose($handle);
			}
		}
		unset($archive_files); unset($archive_file);

		return true;
	}
}
