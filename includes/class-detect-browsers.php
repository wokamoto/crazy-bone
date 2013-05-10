<?php
/*
Detect browser
  based on a wordpress plugin by Iman Nurchyo (http://priyadi.net/)
  which is available at http://priyadi.net/archives/2005/03/29/wordpress-browser-detection-plugin/

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2009 - 2011 wokamoto http://dogmap.jp/ (email : wokamoto1973@gmail.com)

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

class DetectBrowsers {
	/**********************************************************
	* is PC ?
	***********************************************************/
	static public function is_pc($ua = '') {
		$result = FALSE;

		if (!is_array($ua))
			$ua = self::get_info($ua);

		if (isset($ua['os'])) {
			switch($ua['os']['code']) {
			case 'windows':
			case 'macos':
			case 'linux':
			case 'debian':
			case 'mandrake':
			case 'suse':
			case 'novell':
			case 'ubuntu':
			case 'redhat':
			case 'gentoo':
			case 'fedora':
			case 'slackware':
			case 'freebsd':
			case 'netbsd':
			case 'openbsd':
			case 'sgi':
			case 'sun':
			case 'unix':
				$result = TRUE;
				break;
			default:
				$result = FALSE;
				break;
			}
		}

		return $result;
	}

	/**********************************************************
	* is smartphone ?
	***********************************************************/
	static public function is_smartphone($ua = '') {
		$result = FALSE;

		if (!is_array($ua))
			$ua = self::get_info($ua);

		if (isset($ua['pda'])) {
			switch($ua['pda']['code']) {
			case 'iphone':
			case 'android':
			case 'blackberry':
			case 'windows_phone':
			case 'ipod':
				$result = ! self::is_pc($ua);
				break;
			default:
				$result = FALSE;
				break;
			}
		}

		return $result;
	}

	/**********************************************************
	* is ktai ?
	***********************************************************/
	static public function is_ktai($ua = '') {
		$result = FALSE;

		if (!is_array($ua))
			$ua = self::get_info($ua);

		if (isset($ua['pda'])) {
			switch($ua['pda']['code']) {
			case 'docomo':
			case 'au':
			case 'softbank':
			case 'willcom':
				$result = ! self::is_smartphone($ua);
				break;
			default:
				$result = ( $ua['os']['code'] === 'symbian' ? TRUE : FALSE );
				break;
			}
		}

		return $result;
	}

	/**********************************************************
	* Get Information from user agent
	***********************************************************/
	static public function get_info($ua = '') {
		if ( empty($ua) && isset($_SERVER['HTTP_USER_AGENT']) )
			$ua = $_SERVER['HTTP_USER_AGENT'];

		$ua = preg_replace("/FunWebProducts/i", "", $ua);

		$browser_name = $browser_code = $browser_ver = $os_name = $os_code = $os_ver = $pda_name = $pda_code = $pda_ver = null;

		if ( empty($browser_name) ) {
			if (preg_match('/(movabletype|wordpress|typepad|livedoortrackback|fc2|blogserver|hatena diary trackback agent|gooblog|lovelogtrackback|bitwave\.trackbackping|libwww-perl|jakarta commons-httpclient|java|xoops news\+trackback ver)[ \/]([a-z0-9\.]+)/i', $ua, $matches)) {
				$browser_name = $matches[1];
				$browser_code = strtolower(trim($browser_name));
				$browser_ver  = $matches[2];
				$os_name = FALSE;
				$pda_name = FALSE;
				switch (strtolower($browser_code)) {
				case 'movabletype':
					$browser_name = 'MovableType';
					$browser_code = 'mt';
					break;
				case 'wordpress':
					$browser_name = 'WordPress';
					$browser_code = 'wp';
					break;
				case 'typepad':
					$browser_name = 'TypePad';
					$browser_code = 'typepad';
					break;
				case 'livedoortrackback':
					$browser_name = 'livedoor blog';
					$browser_code = 'livedoor';
					break;
				case 'fc2':
					$browser_name = 'FC2 blog';
					$browser_code = 'fc2';
					break;
				case 'lovelogtrackback':
					$browser_name = 'LOVELOG';
					$browser_code = 'lovelog';
					break;
				case 'blogserver':
					$browser_name = 'Seesaa blog';
					$browser_code = 'seesaa';
					break;
				case 'hatena diary trackback agent':
					$browser_name = 'Hatena Diary';
					$browser_code = 'hatena';
					break;
				case 'gooblog':
					$browser_name = 'goo blog';
					$browser_code = 'gooblog';
					break;
				case 'bitwave.trackbackping':
					$browser_name = 'Bitwave.jp';
					$browser_code = 'bitwave';
					break;
				case 'libwww-perl':
					$browser_name = 'libwww-perl';
					$browser_code = 'libwww-perl';
					break;
				case 'jakarta commons-httpclient':
					$browser_name = 'Jakarta Commons-HttpClient';
					$browser_code = 'jakarta-apache';
					break;
				case 'java':
					$browser_name = 'Java';
					$browser_code = 'java';
					break;
				case 'xoops news+trackback ver':
					$browser_name = 'XOOPS News+TrackBack';
					$browser_code = 'xoops';
					break;
				}
			} else if (preg_match('/(drupal|habari|pukiwiki|seesaa|jugem|avantbrowser.com|danger hiptop|anonymouse|pear http_request|coreblog2?)/i', $ua, $matches)) {
				$browser_name = $matches[1];
				$browser_code = strtolower(trim($browser_name));
				$os_name = FALSE;
				$pda_name = FALSE;
				switch (strtolower($browser_code)) {
				case 'drupal':
					$browser_name = 'Drupal';
					$browser_code = 'drupal';
					break;
				case 'habari':
					$browser_name = 'Habari';
					$browser_code = 'habari';
					break;
				case 'pukiwiki':
					$browser_name = 'PukiWiki';
					$browser_code = 'pukiwiki';
					break;
				case 'seesaa':
					$browser_name = 'Seesaa blog';
					$browser_code = 'seesaa';
					break;
				case 'jugem':
					$browser_name = 'JUGEM blog';
					$browser_code = 'jugem';
					break;
				case 'avantbrowser.com':
					$browser_name = 'Avant Browser';
					$browser_code = 'avantbrowser';
					break;
				case 'danger hiptop':
					$browser_name = 'Danger HipTop';
					$browser_code = 'danger';
					break;
				case 'anonymouse':
					$browser_name = 'Anonymouse';
					$browser_code = 'anonymouse';
					break;
				case 'pear http_request':
					$browser_name = 'PEAR HTTP_Request';
					$browser_code = 'pear';
					break;
				case 'coreblog':
					$browser_name = 'COREBlog (Blog on Zope)';
					$browser_code = 'zope';
					break;
				case 'coreblog2':
					$browser_name = 'COREBlog2 (Blog on Plone)';
					$browser_code = 'plone';
					break;
				}
			} else if (preg_match('/trackback/i', $ua)) {
				$browser_name = 'Trackback';
				$browser_name = '';
				$os_name = FALSE;
				$pda_name = FALSE;
			} else if (preg_match('/symbianos\/([a-z0-9\.]+)/i', $ua, $matches)) {
				$os_name = 'SymbianOS';
				$os_code = 'symbian';
				$os_ver  = $matches[1];
				$pda_name = FALSE;
			} else if (preg_match('/(docomo|up\.browser|j-phone|vodafone|softbank)/i', $ua, $matches)) {
				$os_name = FALSE;
				list($pda_name, $pda_code, $pda_ver) = self::_pda($ua);
			} else if (preg_match('/wp\-(iphone|android|blackberry)/i', $ua, $matches)) {
				$os_name = FALSE;
				list($pda_name, $pda_code, $pda_ver) = self::_pda($ua);
				$browser_name = 'WordPress';
				switch ($pda_code) {
				case 'iphone':
					$browser_name .= ' for iPhone';
					break;
				case 'android':
					$browser_name .= ' for Android';
					break;
				case 'blackberry':
					$browser_name .= ' for BlackBerry';
					break;
				}
				$browser_code = 'wp';
				$browser_ver  = $pda_ver;
			} else if (preg_match('/(blackberry[0-9]+|\(ip(od|ad|hone);|ddipocket|willcom)/i', $ua, $matches)) {
				$os_name = FALSE;
				list($pda_name, $pda_code, $pda_ver) = self::_pda($ua);
				switch ($pda_code) {
				case 'blackberry':
					if (preg_match('/(j2me|midp)[ \/\-]?([a-z0-9\.]+)?/i', $ua, $matches)) {
						$browser_name = "J2ME/MIDP Browser";
						$browser_code = "j2me";
						$browser_ver  = $matches[2];
					}
					break;
				case 'ipod':
				case 'ipad':
				case 'iphone':
					if (preg_match('/Version\/([a-z0-9\.]+)/', $ua, $matches))
						$pda_ver  = $matches[1];
					if (preg_match('/Safari\/([a-z0-9\.]+)/i', $ua, $matches)) {
						$browser_name = 'Safari';
						$browser_code = 'safari';
						$browser_ver  = $matches[1];
					}
					break;
				case 'willcom':
					if (preg_match('/(netfront|opera)[ \/]([a-z0-9\.]+)/i', $ua, $matches)) {
						$browser_name = $matches[1];
						$browser_code = strtolower(trim($browser_name));
						$browser_ver  = $matches[2];
						switch ($browser_code) {
						case 'netfront':
							$browser_name = 'Netfront';
							$browser_code = 'netfront';
							break;
						case 'opera':
							$browser_name = 'Opera';
							$browser_code = 'opera';
							break;
						}
					}
					break;
				}
			} else if (preg_match('/ibisbrowser/i', $ua)) {
				$browser_name = 'ibisBrowser';
				$browser_code = 'ibisbrowser';
				$os_name = FALSE;
			} else if (preg_match('/jig browser ?([a-z0-9\.]+)?/i', $ua, $matches)) {
				$browser_name = 'jig browser';
				$browser_code = 'jig';
				$browser_ver  = $matches[1];
				$os_name = FALSE;
			} else if (preg_match('/(chrome|opera mini|camino|chimera|shiira|lunascape|sleipnir|konqueror|netnewswire|dillo|epiphany|seamonkey|kazehakase|flock|minimo|multizilla|galeon|icab|k-meleon|lynx|elinks|netpositive|omniweb|webpro|netfront|xiino|kunststofftraumte|w3m)[ \/]([a-z0-9\.]+)/i', $ua, $matches)) {
				$browser_name = $matches[1];
				$browser_code = strtolower(trim($browser_name));
				$browser_ver  = $matches[2];
				$pda_name = FALSE;
				switch ($browser_code) {
				case 'chrome':
					$browser_name = 'Google Chrome';
					$browser_code = 'chrome';
					break;
				case 'opera mini':
					$browser_name = 'Opera Mini';
					$browser_code = 'opera';
					if (preg_match('/Opera\/([a-z0-9\.]+)/i', $ua, $matches))
						$browser_ver  = $matches[1];
					list($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::_os_pda($ua);
					break;
					break;
				case 'camino':
				case 'chimera':
					$browser_name = 'Camino';
					$browser_code = 'camino';
					$os_name = "Mac OS";
					$os_code = "macos";
					$os_ver  = "X";
					break;
				case 'shiira':
					$browser_name = 'Shiira';
					$browser_code = 'shiira';
					$os_name = "Mac OS";
					$os_code = "macos";
					$os_ver  = "X";
					break;
				case 'lunascape':
					$browser_name = 'Lunascape';
					$browser_code = 'lunascape';
					break;
				case 'sleipnir':
					$browser_name = 'Sleipnir';
					$browser_code = 'sleipnir';
					break;
				case 'konqueror':
					$browser_name = 'Konqueror';
					$browser_code = 'konqueror';
					list($os_name, $os_code, $os_ver) = self::_os_unix($ua);
					if (!$os_name)
						list($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::_os_pda($ua);
					break;
				case 'netnewswire':
					$browser_name = 'NetNewsWire';
					$browser_code = 'netnewswire';
					$os_name = "Mac OS";
					$os_code = "macos";
					$os_ver  = "X";
					break;
				case 'dillo':
					$browser_name = 'Dillo';
					$browser_code = 'dillo';
					$os_name = FALSE;
					break;
				case 'epiphany':
					$browser_name = 'Epiphany';
					$browser_code = 'epiphany';
					list($os_name, $os_code, $os_ver) = self::_os_unix($ua);
					break;
				case 'seamonkey':
					$browser_name = 'Mozilla SeaMonkey';
					$browser_code = 'seamonkey';
					break;
				case 'kazehakase':
					$browser_name = 'Kazehakase';
					$browser_code = 'kazehakase';
					break;
				case 'flock':
					$browser_name = 'Flock';
					$browser_code = 'flock';
					break;
				case 'minimo':
					$browser_name = 'Minimo';
					$browser_code = 'mozilla';
					break;
				case 'multizilla':
					$browser_name = 'MultiZilla';
					$browser_code = 'mozilla';
					break;
				case 'galeon':
					$browser_name = 'Galeon';
					$browser_code = 'galeon';
					list($os_name, $os_code, $os_ver) = self::_os_unix($ua);
					break;
				case 'icab':
					$browser_name = 'iCab';
					$browser_code = 'icab';
					list($os_name, $os_code, $os_ver) = self::_os_mac($ua);
					break;
				case 'k-meleon':
					$browser_name = 'K-Meleon';
					$browser_code = 'kmeleon';
					break;
				case 'lynx':
					$browser_name = 'Lynx';
					$browser_code = 'lynx';
					list($os_name, $os_code, $os_ver) = self::_os_unix($ua);
					break;
				case 'elinks':
					$browser_name = 'ELinks';
					$browser_code = 'lynx';
					list($os_name, $os_code, $os_ver) = self::_os_unix($ua);
					break;
				case 'netpositive':
					$browser_name = 'NetPositive';
					$browser_code = 'netpositive';
					$os_name = "BeOS";
					$os_code = "beos";
					break;
				case 'omniweb':
					$browser_name = 'OmniWeb';
					$browser_code = 'omniweb';
					$os_name = "Mac OS";
					$os_code = "macos";
					$os_ver  = "X";
					break;
				case 'webpro':
					$browser_name = 'WebPro';
					$browser_code = 'webpro';
					$browser_ver  = $matches[1];
					$os_name = "PalmOS";
					$os_code = "palmos";
					break;
				case 'netfront':
					$browser_name = 'Netfront';
					$browser_code = 'netfront';
					list($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::_os_pda($ua);
					break;
				case 'xiino':
					$browser_name = 'Xiino';
					$browser_code = 'xiino';
					$os_name = FALSE;
					break;
				case 'kunststofftraumte':
					$browser_name = 'Kunststoff Traumte';
					$browser_code = '';
					$os_name = FALSE;
					break;
				case 'w3m':
					$browser_name = 'W3M';
					$browser_code = 'w3m';
					list($os_name, $os_code, $os_ver) = self::_os_unix($ua);
					break;
				}
			} else if (preg_match('/(webpro|blazer|j2me|midp)[ \/\-]?([a-z0-9\.]+)?/i', $ua, $matches)) {
				$browser_name = $matches[1];
				$browser_code = strtolower(trim($browser_name));
				$browser_ver  = $matches[2];
				$os_name = "PalmOS";
				$os_code = "palmos";
				switch ($browser_code) {
				case 'webpro':
					$browser_name = 'WebPro';
					$browser_code = 'webpro';
					$pda_name = FALSE;
					break;
				case 'blazer':
					$browser_name = "Blazer";
					$browser_code = "blazer";
					$pda_name = FALSE;
					break;
				case 'j2me':
				case 'midp':
					$browser_name = "J2ME/MIDP Browser";
					$browser_code = "j2me";
					$os_name = FALSE;
					$os_code = null;
					break;
				}
			} else if (preg_match('/(opera|safari|firefox|shiretoko|firebird|phoenix|bonecho|granparadiso|minefield|iceweasel)[ \/]([a-z0-9\.]+)/i', $ua, $matches)) {
				$browser_name = $matches[1];
				$browser_code = strtolower(trim($browser_name));
				$browser_ver  = $matches[2];
				$pda_name = FALSE;
				switch ($browser_code) {
				case 'safari':
					$browser_name = 'Safari';
					$browser_code = 'safari';
					list($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::_os($ua);
					break;
				case 'firefox':
				case 'shiretoko':
				case 'firebird':
				case 'phoenix':
				case 'bonecho':
				case 'granparadiso':
				case 'minefield':
				case 'iceweasel':
					$browser_name = 'Mozilla Firefox';
					$browser_code = 'firefox';
					list($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::_os($ua);
					$pda_name = FALSE;
					break;
				case 'opera':
					$browser_name = 'Opera';
					$browser_code = 'opera';
					$browser_ver = $matches[1];
					list($os_name, $os_code, $os_ver) = self::_os_win($ua);
					if (!$os_name)
						list($os_name, $os_code, $os_ver) = self::_os_unix($ua);
					if (!$os_name)
						list($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::_os_pda($ua);
					if (!$os_name) {
						if ( preg_match('/Wii/i', $ua) ) {
							$os_name = "Nintendo Wii";
							$os_code = "nintendo-wii";
						} else if ( preg_match('/Nitro/i', $ua) ) {
							$os_name = "Nintendo DS";
							$os_code = "nintendo-ds";
						}
					}
					// Windows CE
					if ( $os_code == "windows" && $os_ver == "CE" )
						list($pda_name, $pda_code, $pda_ver) = self::_pda($ua);
					break;
				}
			} else if (preg_match('/(e?links \(|php\/)([a-z0-9\.]+)/i', $ua, $matches)) {
				$browser_name = $matches[1];
				$browser_code = strtolower(trim($browser_name));
				$browser_ver  = $matches[2];
				$pda_name = FALSE;
				switch ($browser_code) {
				case 'links (':
					$browser_name = 'Links';
					$browser_code = 'lynx';
					break;
				case 'elinks (':
					$browser_name = 'ELinks';
					$browser_code = 'lynx';
					break;
				case 'php/':
					$browser_name = 'PHP';
					$browser_code = 'php';
					break;
				}
				list($os_name, $os_code, $os_ver) = self::_os_unix($ua);
			} else if (preg_match('/(nintendo gameboy)/i', $ua, $matches)) {
				$os_name = FALSE;
				$pda_name = $matches[1];
				$pda_code = strtolower(trim($pda_name));
				switch ($pda_code) {
				case 'nintendo gameboy':
					$pda_name = "Nintendo GameBoy";
					$pda_code = "nintendo-gb";
					if (preg_match('/Mech.Mozilla\/([a-z0-9\.]+)/i', $ua, $matches))
						$pda_ver  = $matches[1];
					break;
				}
			} else if (preg_match('/(psp \(playstation portable\)\; |ps2; playstation bb navigator |playstation 3; |spv |nokia ?|sonyericsson ?|lge-|mot-|sie-|sec-|samsung-)([a-z0-9\.\-]+)/i', $ua, $matches)) {
				$os_name = FALSE;
				$pda_name = $matches[1];
				$pda_code = strtolower(trim($pda_name));
				$pda_ver  = $matches[2];
				switch ($pda_code) {
				case 'psp (playstation portable);':
					$pda_name = "Sony PSP";
					$pda_code = "sony-psp";
					break;
				case 'ps2; playstation bb navigator':
					$pda_name = "Sony PLAYSTATION 2";
					$pda_code = "sony-ps";
					break;
				case 'playstation 3;':
					$pda_name = "Sony PLAYSTATION 3";
					$pda_code = "sony-ps";
					break;
				case 'spv':
					$pda_name = "Orange SPV";
					$pda_code = "orange";
					break;
				case 'nokia':
					$pda_name = "Nokia";
					$pda_code = "nokia";
					break;
				case 'sonyericsson':
					$pda_name = "SonyEricsson";
					$pda_code = "sonyericsson";
					break;
				case 'lge-':
					$pda_name = "LG";
					$pda_code = "lg";
					break;
				case 'mot-':
					$pda_name = "Motorola";
					$pda_code = "motorola";
					break;
				case 'sie-':
					$pda_name = "Siemens";
					$pda_code = "siemens";
					break;
				case 'sec-':
				case 'samsung-':
					$pda_name = "Samsung";
					$pda_code = "samsung";
					break;
				}
			} else if (preg_match('/IEMobile[ \/]([0-9\.]+):/', $ua, $matches)) {
				$browser_name = 'Internet Explorer Mobile';
				$browser_code = 'iemobile';
				$browser_ver  = $matches[1];
			} else if (preg_match('/MSIE ([a-z0-9\.]+)/', $ua, $matches)) {
				$browser_name = 'Internet Explorer';
				$browser_code = 'ie';
				$browser_ver  = $matches[1];
				if ( preg_match('/win64/i', $ua) )
					$browser_ver  .= ' (64bit)';
			} else if (preg_match('/universe\/([0-9\.]+)/i', $ua, $matches)) {
				$browser_name = 'Universe';
				$browser_code = 'universe';
				$browser_ver  = $matches[1];
				list($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::_os_pda($ua);
			}else if (preg_match('/netscape[0-9]?\/([a-z0-9\.]+)/i', $ua, $matches)) {
				$browser_name = 'Netscape';
				$browser_code = 'netscape';
				$browser_ver  = $matches[1];
			} else if (preg_match('/^Mozilla\/5\.0/', $ua) && preg_match('#rv:([a-z0-9\.]+)#i', $ua, $matches)) {
				$browser_name = 'Mozilla';
				$browser_code = 'mozilla';
				$browser_ver  = $matches[1];
			} else if (preg_match('/^Mozilla\/([a-z0-9\.]+)/', $ua, $matches)) {
				$browser_name = 'Netscape Navigator';
				$browser_code = 'netscape';
				$browser_ver  = $matches[1];
			}
		}

		// Get OS Information
		if ( empty($os_name) && $os_name !== FALSE )
			list($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::_os($ua);
		if ( $os_name === FALSE )
			$os_name = $os_code = $os_ver = null;

		// Get PDA Plathome
		if ( empty($pda_name) && $pda_name !== FALSE )
			list($pda_name, $pda_code, $pda_ver) = self::_pda($ua);
		if ( $pda_name === FALSE )
			$pda_name = $pda_code = $pda_ver = null;

		return array(
			'browser' => array(
				'name'    => $browser_name ,
				'code'    => $browser_code ,
				'version' => $browser_ver ,
				),
			'os' => array(
				'name'    => $os_name ,
				'code'    => $os_code ,
				'version' => $os_ver ,
				),
			'pda' => array(
				'name'    => $pda_name ,
				'code'    => $pda_code ,
				'version' => $pda_ver ,
				),
			);
	}

	static private function _os($ua) {
		$os_name = $os_code = $os_ver = $pda_name = $pda_code = $pda_ver = null;

		list($os_name, $os_code, $os_ver) = (preg_match('/win(dows)?/i', $ua)
			? self::_os_win($ua)
			: self::_os_unix($ua)
			);

		// Windows CE
		if ( $os_code == "windows" && ( $os_ver == "CE" || empty($os_ver) ) )
			list($pda_name, $pda_code, $pda_ver) = self::_pda($ua);

		// Windows Phone
		if ( $os_code == "windows_phone" )
			list($pda_name, $pda_code, $pda_ver) = self::_pda($ua);

		return array($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver);
	}

	static private function _os_win($ua) {
		$os_name = $os_code = $os_ver = null;

		if (preg_match('/mac_powerpc/i', $ua)) {
			$os_name = "Mac OS";
			$os_code = "macos";
		} else if (preg_match('/win(dows)?[ \.]?(9[58]|9x 4\.90|[cm]e|2000|nt ?[456]\.0|nt ?5\.[12])/i', $ua, $matches)) {
			$os_name = "Windows";
			$os_code = "windows";
			$os_ver  = strtoupper(trim($matches[2]));
			switch ($os_ver) {
			case '95':
				$os_ver  = "95";
				break;
			case '9X 4.90';
			case 'ME';
				$os_ver  = "ME";
				break;
			case 'NT4.0';
			case 'NT 4.0';
				$os_ver  = "NT 4.0";
				break;
			case '2000':
			case 'NT 5.0':
				$os_ver  = "2000";
				break;
			case 'NT 5.1':
				$os_ver  = "XP";
				break;
			case 'NT 5.2':
				$os_ver  = ( preg_match('/(win|wow)64/i', $ua) ? "XP (64bit)" : "Server 2003" );
				break;
			case 'NT 6.0':
				$os_ver  = "Vista" . ( preg_match('/(win|wow)64/i', $ua) ? " (64bit)" : "" );
				break;
			case 'NT 6.1':
				$os_ver  = "7" . ( preg_match('/(win|wow)64/i', $ua) ? " (64bit)" : "" );
				break;
			case 'CE';
				$os_name = "Windows";
				$os_code = "windows";
				$os_ver  = "CE";
				if (preg_match('/ppc/i', $ua)) {
					$os_name = "Microsoft PocketPC";
					$os_code = "windows";
					$os_ver  = '';
				}
				if (preg_match('/smartphone/i', $ua)) {
					$os_name = "Microsoft Smartphone";
					$os_code = "windows";
					$os_ver  = '';
				}
				break;
			}
		} else if (preg_match('/win(dows)? phone os ([0-9\.]+)/i', $ua, $matches)) {
			$os_name = "Windows Phone";
			$os_code = "windows_phone";
			$os_ver  = strtoupper(trim($matches[2]));
		} else if (preg_match('/win(dows )?nt/i', $ua)) {
			$os_name = "Windows";
			$os_code = "windows";
			$os_ver  = "NT";
		}

		return array($os_name, $os_code, $os_ver);
	}

	static private function _os_unix($ua) {
		$os_name = $os_code = $os_ver = null;

		if (preg_match('/linux/i', $ua)) {
			$os_name = "Linux";
			$os_code = "linux";
			if (preg_match('/(android|debian|mandrake|suse|novell|ubuntu|red ?hat|gentoo|fedora|mepis|knoppix|slackware|xandros|kanotix)/i', $ua, $matches)) {
				$os_code = strtolower(trim($matches[1]));
				switch ($os_code) {
				case 'android':
					$os_name = "Android";
					$os_code = "android";
					break;
				case 'debian':
					$os_name = "Debian GNU/Linux";
					$os_code = "debian";
					break;
				case 'mandrake':
					$os_name = "Mandrake Linux";
					$os_code = "mandrake";
					break;
				case 'suse':
					$os_name = "SuSE Linux";
					$os_code = "suse";
					break;
				case 'novell':
					$os_name = "Novell Linux";
					$os_code = "novell";
					break;
				case 'ubuntu':
					$os_name = "Ubuntu Linux";
					$os_code = "ubuntu";
					break;
				case 'redhat':
				case 'red hat':
					$os_name = "RedHat Linux";
					$os_code = "redhat";
					break;
				case 'gentoo':
					$os_name = "Gentoo Linux";
					$os_code = "gentoo";
					break;
				case 'fedora':
					$os_name = "Fedora Linux";
					$os_code = "fedora";
					break;
				case 'mepis':
					$os_name = "MEPIS Linux";
					$os_code = "linux";
					break;
				case 'knoppix':
					$os_name = "Knoppix Linux";
					$os_code = "linux";
					break;
				case 'slackware':
					$os_name = "Slackware Linux";
					$os_code = "slackware";
					break;
				case 'xandros':
					$os_name = "Xandros Linux";
					$os_code = "linux";
					break;
				case 'kanotix':
					$os_name = "Kanotix Linux";
					$os_code = "linux";
					break;
				}
			} 
		} else if (preg_match('/((free|net|open)bsd|irix|sunos|mac( os x|intosh|_powerpc))/i', $ua, $matches)) {
			$os_name = trim($matches[1]);
			$os_code = strtolower($os_name);
			switch ($os_code) {
			case 'freebsd':
				$os_name = "FreeBSD";
				$os_code = "freebsd";
				break;
			case 'netbsd':
				$os_name = "NetBSD";
				$os_code = "netbsd";
				break;
			case 'openbsd':
				$os_name = "OpenBSD";
				$os_code = "openbsd";
				break;
			case 'irix':
				$os_name = "SGI IRIX";
				$os_code = "sgi";
				break;
			case 'sunos':
				$os_name = "Solaris";
				$os_code = "sun";
				break;
			case 'mac os x':
				$os_name = "Mac OS";
				$os_code = "macos";
				$os_ver  = "X";
				break;
			case 'macintosh':
			case 'mac_powerpc':
				$os_name = "Mac OS";
				$os_code = "macos";
				break;
			}
		} else if (preg_match('/unix/i', $ua)) {
			$os_name = "UNIX";
			$os_code = "unix";
		} 

		return array($os_name, $os_code, $os_ver);
	}

	static private function _os_mac($ua) {
		$os_name = $os_code = $os_ver = null;

		if (preg_match('/(mac( os x|intosh|_powerpc))/i', $ua, $matches)) {
			$os_name = trim($matches[1]);
			$os_code = strtolower($os_name);
			switch ($os_code) {
			case 'mac os x':
				$os_name = "Mac OS";
				$os_code = "macos";
				$os_ver  = "X";
				break;
			case 'macintosh':
			case 'mac_powerpc':
				$os_name = "Mac OS";
				$os_code = "macos";
				break;
			}
		} 

		return array($os_name, $os_code, $os_ver);
	}

	static private function _os_pda($ua) {
		$os_name = $os_code = $os_ver = $pda_name = $pda_code = $pda_ver = null;

		if (preg_match('/(palmos|windows ce|windows phone|qtembedded|zaurus|symbian)/i', $ua, $matches)) {
			$os_name = $matches[1];
			$os_code = strtolower($os_name);
			switch ($os_code) {
			case 'palmos':
				$os_name = "Palm OS";
				$os_code = "palm";
				break;
			case 'windows ce':
				$os_name = "Windows CE";
				$os_code = "windows";
				break;
			case 'windows phone':
				$os_name = "Windows Phone";
				$os_code = "windows";
				break;
			case 'qtembedded':
				$os_name = "Qtopia";
				$os_code = "linux";
				break;
			case 'zaurus':
				$os_name = "Zaurus";
				$os_code = "linux";
				break;
			case 'symbian':
				$os_name = "Symbian OS";
				$os_code = "symbian";
				break;
			}
		}
		list($pda_name, $pda_code, $pda_ver) = self::_pda($ua);

		return array($os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver);
	}

	static private function _pda($ua) {
		$pda_name = $pda_code = $pda_ver = null;

		if (preg_match('/docomo\/([a-z0-9\.]+)/i', $ua, $matches)) {
			$pda_name = 'DoCoMo';
			$pda_code = 'docomo';
			$pda_ver  = $matches[1];
			if ($pda_ver  == '1.0' && preg_match('/docomo\/([a-z0-9\.]+)\/([a-z0-9\.]+)/i', $ua, $matches)) {
				$pda_ver  = $matches[2];
			} else if ($pda_ver  == '2.0' && preg_match('/docomo\/([a-z0-9\.]+) ([a-z0-9\.]+)/i', $ua, $matches)) {
				$pda_ver  = $matches[2];
			}
		} else if (preg_match('/up\.browser\/[a-z0-9\.]+/i', $ua)) {
			$pda_name = 'au';
			$pda_code = 'au';
			if (preg_match('/kddi-([a-z0-9\.]+) up\.browser\/[a-z0-9\.]+/i', $ua, $matches)) {
				$pda_ver = $matches[1];
			} else if (preg_match('/up\.browser\/([a-z0-9\.]+)-([a-z0-9\.]+)/i', $ua, $matches)) {
				$pda_ver  = $matches[2];
			}
		} else if (preg_match('/^mozilla\/5\.0 \(([a-z0-9\.\-]+);softbank/i', $ua, $matches)) {
			$pda_name = 'SoftBank';
			$pda_code = 'softbank';
			$pda_ver  = $matches[1];
		} else if (preg_match('/(j-phone|vodafone|softbank)\/[a-z0-9\.]+\/([a-z0-9\.\-]+)/i', $ua, $matches)) {
			$pda_name = 'SoftBank';
			$pda_code = 'softbank';
			$pda_ver  = $matches[2];
		} else if (preg_match('/(ddipocket|willcom);[a-z0-9\.]+\/([a-z0-9\.\-]+)/i', $ua, $matches)) {
			$os_name = FALSE;
			$pda_name = 'WILLCOM';
			$pda_code = 'willcom';
			$pda_ver  = $matches[2];
		} else if (preg_match('/palmos\/sony\/model/i', $ua)) {
			$pda_name = "Sony Clie";
			$pda_code = "sony";
		} else if (preg_match('/wp\-(iphone|android|blackberry)\/([0-9\.]+)/i', $ua, $matches)) {
			$pda_name = trim($matches[1]);
			$pda_code = strtolower($pda_name);
			$pda_ver  = $matches[2];
			switch ($pda_code) {
			case 'iphone':
				$pda_name = "WordPress for iPhone";
				$pda_code = "iphone";
				$pda_ver  = $matches[2];
				break;
			case 'android':
				$pda_name = "WordPress for Android";
				$pda_code = "android";
				$pda_ver  = $matches[2];
				break;
			case 'blackberry':
				$pda_name = "WordPress for BlackBerry";
				$pda_code = "blackberry";
				$pda_ver  = $matches[2];
				break;
			}
		} else if (preg_match('/(blackberry|\(ip(od|ad|hone);|series |nokia |windows phone os )([0-9\.]+)?/i', $ua, $matches)) {
			$pda_name = trim($matches[1]);
			$pda_code = strtolower($pda_name);
			$pda_ver  = $matches[2];
			switch ($pda_code) {
			case 'blackberry':
				$pda_name = trim("BlackBerry " . $matches[2]);
				$pda_code = "blackberry";
				$pda_ver  = null;
				break;
			case '(ipod;':
				$pda_name = "iPod touch";
				$pda_code = "ipod";
				if (preg_match('/Version\/([a-z0-9\.]+)/', $ua, $matches))
					$pda_ver  = $matches[1];
				break;
			case '(ipad;':
				$pda_name = "iPad";
				$pda_code = "ipad";
				if (preg_match('/Version\/([a-z0-9\.]+)/', $ua, $matches))
					$pda_ver  = $matches[1];
				break;
			case '(iphone;':
				$pda_name = "iPhone";
				$pda_code = "iphone";
				if (preg_match('/Version\/([a-z0-9\.]+)/', $ua, $matches))
					$pda_ver  = $matches[1];
				break;
			case 'series':
				$pda_name = "Series";
				$pda_code = "nokia";
				break;
			case 'nokia':
				$pda_name = "Nokia";
				$pda_code = "nokia";
				break;
			case 'windows phone os':
				$pda_name = "Windows Phone";
				$pda_code = "windows_phone";
				break;
			}
		} else if (preg_match('/(zaurus|sie-|dopod|o2 xda |sec-|sonyericsson ?)([a-z0-9\.]+)?/i', $ua, $matches)) {
			$pda_name = trim($matches[1]);
			$pda_code = strtolower($pda_name);
			$pda_ver  = $matches[1];
			switch ($pda_code) {
			case 'zaurus':
				$pda_name = "Sharp Zaurus " . $matches[1];
				$pda_code = "zaurus";
				$pda_ver  = null;
				break;
			case 'sie-':
				$pda_name = "Siemens";
				$pda_code = "siemens";
				break;
			case 'dopod':
				$pda_name = "Dopod";
				$pda_code = "dopod";
				break;
			case 'o2 xda':
				$pda_name = "O2 XDA";
				$pda_code = "o2";
				break;
			case 'sec-':
				$pda_name = "Samsung";
				$pda_code = "samsung";
				break;
			case 'sonyericsson':
				$pda_name = "SonyEricsson";
				$pda_code = "sonyericsson";
				break;
			}
		}

		return array($pda_name, $pda_code, $pda_ver);
	}
}
