<?php
/*
Plugin Name: Crazy Bone
Plugin URI: 
Description: Tracks user name, time of login, IP address and browser user agent.
Author: wokamoto
Version: 0.2.0
Author URI: http://dogmap.jp/
Text Domain: user-login-log
Domain Path: /languages/

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2013 (email : wokamoto1973@gmail.com)

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
if (!class_exists('DetectBrowsersController'))
	require_once( dirname(__FILE__) . '/includes/detect_browsers.php' );
if (!class_exists('DetectCountriesController'))
	require_once( dirname(__FILE__) . '/includes/detect_countries.php' );

load_plugin_textdomain(user_login_log::TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/');

class user_login_log {
	const USER_META_KEY = 'user_login_log';
	const TEXT_DOMAIN   = 'user-login-log';
	const LIST_PER_PAGE = 20;
	const DEBUG_MODE    = false;

	const SEC_MINUITE   = 60;
	const SEC_HOUR      = 3600;
	const SEC_DAY       = 86400;
	const SEC_MONTH     = 2592000;
	const SEC_YEAR      = 31536000;

	private $ull_table = 'user_login_log';
	private $admin_action;
	private $plugin_version;

	function __construct(){
		global $wpdb;

		$this->ull_table = $wpdb->prefix.$this->ull_table;
		$this->admin_action = admin_url('profile.php') . '?page=' . plugin_basename(__FILE__);

		$data = get_file_data(__FILE__, array('version' => 'Version'));
		$this->plugin_version = isset($data['version']) ? $data['version'] : '';

		add_action('wp_login', array(&$this, 'user_login_log'), 10, 2);
		add_action('wp_authenticate', array(&$this, 'wp_authenticate_log'), 10, 2);
		add_action('login_form_logout', array(&$this, 'user_logout_log'));

		add_action('admin_enqueue_scripts', array(&$this,'enqueue_scripts'));
		add_action('wp_enqueue_scripts', array(&$this,'enqueue_scripts'));

		add_action('admin_bar_init',  array(&$this, 'admin_bar_init'), 9999);
		add_action('admin_menu', array(&$this,'add_admin_menu'));

		add_action('wp_ajax_ull_info', array(&$this, 'ajax_info'));
		add_action('wp_ajax_nopriv_ull_info', array(&$this, 'ajax_info'));

		add_action('wp_ajax_dismiss-ull-wp-pointer', array(&$this, 'ajax_dismiss'));
		add_action('wp_ajax_nopriv_dismiss-ull-wp-pointer', array(&$this, 'ajax_dismiss'));

		register_activation_hook(__FILE__, array(&$this, 'activate'));
		register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
	}

	public function activate(){
		global $wpdb;

		if ($wpdb->get_var("show tables like '{$this->ull_table}'") != $this->ull_table)
			$this->create_table();
	}

	public function deactivate(){
	}

	private function create_table(){
		global $wpdb;

		if ($wpdb->get_var("show tables like '{$this->ull_table}'") != $this->ull_table) {
			$wpdb->query("
CREATE TABLE `{$this->ull_table}` (
 `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
 `activity_status` varchar(255) NOT NULL,
 `activity_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `activity_agent` varchar(255) NOT NULL,
 `activity_IP` varchar(100) NOT NULL,
 `activity_errors` text NULL,
 `country_name` varchar(100) NULL,
 `country_code` varchar(10) NULL,
 PRIMARY KEY (`ID`),
 KEY `user_id` (`user_id`),
 KEY `activity_status` (`activity_status`),
 KEY `activity_date` (`activity_date`),
 KEY `country_code` (`country_code`)
)");
		}
	}

	public function enqueue_scripts(){
		if (!is_user_logged_in())
			return;

		wp_enqueue_style('wp-pointer');
		wp_enqueue_script('jquery');
		wp_enqueue_script('wp-pointer', array('jquery'));
	}

	public function admin_bar_init() {
		add_action('admin_bar_menu',  array(&$this, 'customize_admin_bar_menu'), 9999);
		wp_enqueue_style('user_login_log', plugins_url('css/user_login_log.css', __FILE__), array(), $this->plugin_version);

		add_action('admin_footer', array(&$this, 'footer_js'));
		add_action('wp_footer',    array(&$this, 'footer_js'));
	}

	public function customize_admin_bar_menu($wp_admin_bar){
		$title = $this->login_info();
		if ($title === false)
			return;

		$wp_admin_bar->add_menu(array(
			'id'     => 'user-login-log',
			'parent' => 'my-account',
			'title'  => $title,
			'meta'   => array(),
			'href'   => $this->admin_action,
		));
	}

	private function last_login_info() {
		$user = wp_get_current_user();
		if (is_wp_error($user))
			return false;

		$login_log = get_user_meta($user->ID, self::USER_META_KEY . '-login', true);
		if (!$login_log)
			$login_log = $this->logging($user->ID, 'login');

		$date = isset($login_log['Date']) ? strtotime($login_log['Date']) : time();
		$ip   = isset($login_log['IP']) ? $login_log['IP'] : '';
		$ua   = isset($login_log['User Agent']) ? $login_log['User Agent'] : '';

		return array('date' => $date, 'ip' => $ip, 'ua' => $ua);
	}

	private function login_info($args = '') {
		$user = wp_get_current_user();
		if (is_wp_error($user))
			return false;
		if (empty($args))
			$args = $this->last_login_info();
		$date = isset($args['date']) ? $args['date'] : $this->time();
		$ip = isset($args['ip']) ? $args['ip'] : $this->ip();
		$ua = isset($args['ua']) ? $args['ua'] : $this->ua();

		list($browser_name, $browser_code, $browser_ver, $os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = $this->detect_browser($ua);
		list($country_name, $country_code) = $this->detect_country($ip);

		$title = trim(sprintf(
			__('Last login: %s%s%s', self::TEXT_DOMAIN),
			$this->nice_time($date),
			!empty($ip) ? '&nbsp;'.$this->get_country_flag($ip) : '',
			!empty($ua) ? '&nbsp;'.$this->get_browser_icon($ua) : ''
			));
		return $title;
	}

	public function user_login_log($user_login, $user) {
		$this->logging($user->ID, 'login');
	}

	function wp_authenticate_log($user_login, $user_password) {
		if (empty($user_login))
			return;
		$user = wp_authenticate($user_login, $user_password);
		if (!is_wp_error($user))
			return;
		$errors = $user->errors;
		if (array_key_exists('invalid_username', $errors)) {
			$user_id = 0;
		} else {
			$user = get_user_by('login', $user_login);
			$user_id = isset($user->ID) ? $user->ID : 0;
		}
		$this->logging($user_id, 'login_error', array(
			'errors' => $errors,
			'user_login' => $user_login,
			'user_password' => $user_password,
			));
	}

	public function user_logout_log() {
		$user = wp_get_current_user();
		if (is_wp_error($user))
			return;
		$this->logging($user->ID, 'logout');
	}

	private function logging($user_id, $status, $args = array()) {
		global $wpdb;

		$activity_date  = $this->time();
		$activity_agent = $this->ua();
		$activity_IP    = $this->ip();
		list($country_name, $country_code) = self::detect_country($activity_IP);
		if ($user_id != 0) {
			$meta_value = array_merge(array(
				'status' => $status,
				'Date' => $activity_date,
				'User Agent' => $activity_agent,
				'IP' => $activity_IP,
				'Country Name' => $country_name,
				'Country Code' => $country_code,
				), (array)$args);
			update_user_meta($user_id, self::USER_META_KEY . "-{$status}", $meta_value);
		}

		if ($status === 'login_error') {
			$sql = $wpdb->prepare(
				"insert into {$this->ull_table}
				 (`user_id`, `activity_status`, `activity_date`, `activity_agent`, `activity_IP`, `activity_errors`, `country_name`, `country_code`)
				 values (%d, %s, %s, %s, %s, %s, %s, %s)",
				$user_id,
				$status,
				$activity_date,
				$activity_agent,
				$activity_IP,
				serialize($args),
				$country_name,
				$country_code
				);
		} else {
			$sql = $wpdb->prepare(
				"insert into {$this->ull_table}
				 (`user_id`, `activity_status`, `activity_date`, `activity_agent`, `activity_IP`, `country_name`, `country_code`)
				 values (%d, %s, %s, %s, %s, %s, %s)",
				$user_id,
				$status,
				$activity_date,
				$activity_agent,
				$activity_IP,
				$country_name,
				$country_code
				);
		}
		$wpdb->query($sql);

		return $meta_value;
	}

	public function ajax_info(){
		if (!is_user_logged_in())
			return;

		$args = $this->last_login_info();
		$content = $this->login_info($args);
		if ($content === false)
			wp_die('Not logged in.');

		$transient_key = 'ull-dismiss-'.md5($this->ip().(isset($args['ip']) ? $args['ip'] : ''));
		$dismiss = get_transient($transient_key);

	    header('Content-Type: application/json; charset='.get_option('blog_charset'));
	    echo json_encode(array(
			'content'       => $content,
			'login_IP'      => isset($args['ip']) ? $args['ip'] : '',
			'login_country' => isset($args['ip']) ? $this->get_country_flag($args['ip']) : '',
			'login_time'    => isset($args['date']) ? $this->nice_time($args['date']) : '',
			'IP'            => $this->ip(),
			'country'       => $this->get_country_flag($this->ip()),
			'dismiss'       => $dismiss,
			));
	    die();
	}

	public function ajax_dismiss(){
		if (!is_user_logged_in())
			return;

		$args = $this->last_login_info();
		$transient_key = 'ull-dismiss-'.md5($this->ip().(isset($args['ip']) ? $args['ip'] : ''));
		set_transient($transient_key, TRUE, 60 * 60);
		die();
	}

	public function footer_js(){
		if (!is_user_logged_in())
			return;

		$args = $this->last_login_info();
		$transient_key = 'ull-dismiss-'.md5($this->ip().(isset($args['ip']) ? $args['ip'] : ''));
		$dismiss = get_transient($transient_key);
		$caution = sprintf(
			"<h3>%s</h3><p>%s (' + res.login_time + ')</p>".
			"<p>".
			"%s' + res.login_country + '<strong>' + res.login_IP + '</strong><br/>".
			"%s' + res.country + '<strong>' + res.IP + '</strong>".
			"</p>",
			__('Caution!', self::TEXT_DOMAIN),
			__('Someone has logged in from another IP.', self::TEXT_DOMAIN),
			__("The someone's IP address :", self::TEXT_DOMAIN),
			__('Your current IP address :', self::TEXT_DOMAIN)
			);
?>
<script type="text/javascript">
function get_ull_info() {
	jQuery.ajax('<?php echo admin_url('admin-ajax.php'); ?>',{
		data: {action: 'ull_info'},
		cache: false,
		dataType: 'json',
		type: 'POST',
		success: function(res){
<?php if (self::DEBUG_MODE) echo "\t\t\tconsole.log(res);\n" ?>
			if (!res.dismiss && res.IP !== res.login_IP) {
				jQuery('#wp-admin-bar-my-account').pointer({
					content: '<?php echo $caution; ?>',
					close: function(){
						jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
							action: 'dismiss-ull-wp-pointer',
						});
						setTimeout('get_ull_info()', 30000);
					}
				}).pointer('open');
			} else {
				setTimeout('get_ull_info()', 30000);
			}
			jQuery('#wp-admin-bar-user-login-log a').html(res.content);
		},
		error: function(){
			setTimeout('get_ull_info()', 10000);
		}
	});
}
jQuery(function(){setTimeout('get_ull_info()', 10000);});
</script>
<?php
	}

	// Add Admin Menu
	public function add_admin_menu() {
		$parent = 'profile.php';
		$page_title = __('Login Log', self::TEXT_DOMAIN);
		$menu_title = $page_title;
		$file = plugin_basename(__FILE__);
		$this->add_submenu_page(
			$parent,
			$page_title,
			array($this,'option_page'),
			'level_0',
			$menu_title,
			$file
			);
	}
	private function add_submenu_page($parent, $page_title, $function, $capability = 'administrator', $menu_title = '', $file = '') {
		if ($menu_title == '')
			$menu_title = $page_title;
		if ($file == '')
			$file = $this->plugin_file;
		add_submenu_page($parent, $page_title, $menu_title, $capability, $file, $function);
	}

	private function ip(){
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
			$x_forwarded_for = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$ip = trim($x_forwarded_for[0]);
		}
		return preg_replace('/[^0-9a-fA-F:., ]/', '', $ip);
	}

	// Detect Country
	public static function detect_country($ip) {
		$detect_countries = new DetectCountriesController();
		list($country_name, $country_code) = $detect_countries->get_info($ip);
		if ( empty($country_code) )
			$country_code = __('UNKNOWN', self::TEXT_DOMAIN);
		return array($country_name, $country_code);
	}

	private function ua(){
		$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : __('Unknown', self::TEXT_DOMAIN);
		return substr($ua, 0, 254);
	}

	// Detect Browser
	public static function detect_browser($ua) {
		$detect_browsers = new DetectBrowsersController();
		list($browser_name, $browser_code, $browser_ver, $os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = $detect_browsers->get_info($ua);
		if (empty($os_code)) {
			$os_name = !empty($pda_code) ? $pda_name : $browser_name;
			$os_code = !empty($pda_code) ? $pda_code : $browser_code;
			$os_ver  = !empty($pda_code) ? $pda_ver  : $browser_ver ;
		}
		if (empty($browser_code)) {
			$browser_name = $os_name;
			$browser_code = $os_code;
			$browser_ver  = $os_ver ;
		}

		if (empty($browser_name))
			$browser_name = $ua;
		if (empty($browser_code))
			$browser_code = 'unknown';

		if (empty($os_name))
			$os_name = $ua;
		if (empty($os_code))
			$os_code = 'unknown';

		return array($browser_name, $browser_code, $browser_ver, $os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver);
	}

	public static function time(){
		$time = current_time('mysql');
		return $time;
	}

	private function nice_time($dest) {
		$dest = intval($dest);
		$sour = intval(func_num_args() == 1 ? strtotime($this->time()) : func_get_arg(1));
		$nicetime = '';

		$tt = $dest - $sour;

		$year = intval($tt / self::SEC_YEAR);
		if ($year < -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . sprintf(__('%d years', self::TEXT_DOMAIN), abs($year));
		} else if ($year == -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . __('one year', self::TEXT_DOMAIN);
		}

		$month = intval($tt / self::SEC_MONTH);
		if ($month < -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . sprintf(__('%d months', self::TEXT_DOMAIN), abs($month));
			$tt = ($dest + abs($year) * self::SEC_MONTH) - $sour;
		} else if ($month == -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . __('one month', self::TEXT_DOMAIN);
			$tt = ($dest + self::SEC_MONTH) - $sour;
		}

		$day = intval($tt / self::SEC_DAY);
		if ($day < -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . sprintf(__('%d days', self::TEXT_DOMAIN), abs($day));
		} else if ($day == -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . __('one day', self::TEXT_DOMAIN);
		}

		$hour = intval($tt / self::SEC_HOUR);
		if ($hour  < -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . sprintf(__('%d hours', self::TEXT_DOMAIN), abs($hour));
			$tt = ($dest + abs($hour) * self::SEC_HOUR) - $sour;
		} else if ($hour == -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . __('one hour', self::TEXT_DOMAIN);
			$tt = ($dest + self::SEC_HOUR) - $sour;
		}

		$minute = intval($tt / self::SEC_MINUITE);
		if ($minute < -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . sprintf(__('%d minutes', self::TEXT_DOMAIN), abs($minute));
		} else if ($minute == -1) {
			$nicetime .= (!empty($nicetime) ? ' ' : '' ) . __('one minute', self::TEXT_DOMAIN);
		}

		return empty($nicetime) ? __('Just now!', self::TEXT_DOMAIN) : sprintf(__('%s ago.', self::TEXT_DOMAIN), $nicetime);
	}

	public static function icon_img_tag($src, $alt, $title, $style = 'width:16px;height:16px;', $class = '') {
		return sprintf(
			__('<img src="%1$s" alt="%2$s" title="%3$s" style="%4$s" %5$s/>', self::TEXT_DOMAIN),
			$src,
			esc_attr($alt),
			esc_attr($title),
			$style,
			!empty($class) ? 'class="'.$class.'" ' : '');
	}

	// Get country flag
	public static function get_country_flag($ip, $class = '') {
		list($country_name, $country_code) = self::detect_country($ip);

		$icon_dir = plugins_url('images/flags/', __FILE__);
		$style    = 'width:16px;height:11px;';

		return self::icon_img_tag($icon_dir.strtolower($country_code).'.png', "{$country_name} ({$ip})", "{$country_name} ({$ip})", $style, $class);
	}

	// Get browser icon
	public static function get_browser_icon($ua, $show_ver = true, $separator = '&nbsp;', $class = '') {
		list($browser_name, $browser_code, $browser_ver, $os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::detect_browser($ua);

		$os_info      = trim($os_name . ( $show_ver ? ' ' . $os_ver : '' ));
		$pda_info     = trim($pda_name . ( $show_ver ? ' ' . $pda_ver : '' ));
		$browser_info = trim($browser_name . ( $show_ver ? ' ' . $browser_ver : '' ));
		$unknown_info = $show_ver ? $ua : __('UNKNOWN', self::TEXT_DOMAIN);

		$icon_dir     = plugins_url('images/browsers/', __FILE__);
		$style        = 'width:16px;height:16px;';

		$browser_icon = '';
		if ( !empty($os_info) )
			$browser_icon .= self::icon_img_tag($icon_dir.$os_code.'.png', $os_info, $os_info, $style, $class) . $separator;
		if ( !empty($pda_info) && $pda_code !== $os_code )
			$browser_icon .= self::icon_img_tag($icon_dir.$pda_code.'.png', $pda_info, $pda_info, $style, $class) . $separator;
		if ( !empty($browser_info) && $browser_code !== $os_code )
			$browser_icon .= self::icon_img_tag($icon_dir.$browser_code.'.png', $browser_info, $browser_info, $style, $class);
		if ( empty($browser_icon) )
			$browser_icon .= self::icon_img_tag($icon_dir.'unknown.png', $unknown_info, $unknown_info, $style, $class);

		return $browser_icon;
	}

	public function option_page() {
		global $wpdb;

		$page = abs(intval(isset($_GET['apage']) ? $_GET['apage'] : 1));
		$per_page = self::LIST_PER_PAGE;
		$start = ($page - 1) * $per_page;

		$user_id = 0;
		if (current_user_can('create_users') && isset($_GET['user_id'])) {
			$user_id = intval($_GET['user_id']);
		} else {
			$user = wp_get_current_user();
			if (is_wp_error($user))
				return;
			$user_id = intval($user->ID);
		}

		$sql = " from `{$this->ull_table}` left join `{$wpdb->users}` on `{$this->ull_table}`.`user_id` = `{$wpdb->users}`.`ID`";
		if ($user_id >= 0)
			$sql .= $wpdb->prepare(" where `user_id` = %d", $user_id);
		$total = intval($wpdb->get_var("select count(`{$this->ull_table}`.`ID`)".$sql));
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'apage', '%#%' ) ,
			'format' => '' ,
			'prev_text' => __('&laquo;') ,
			'next_text' => __('&raquo;') ,
			'total' => ceil($total / $per_page) ,
			'current' => $page
		));

		$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
			number_format_i18n( $start + 1 ),
			number_format_i18n( min( $page * $per_page, $total ) ),
			number_format_i18n( $total ),
			$page_links
		);

		$sql = 'select `user_login`, `activity_date`, `activity_status`, `activity_IP`, `activity_agent`, `activity_errors`'.
			$sql.' order by `activity_date` DESC'.
			' limit '.$start.','.self::LIST_PER_PAGE;

		$ull = $wpdb->get_results($sql);
		$row_num = 0;
?>
<div class="wrap">
<div id="icon-profile" class="icon32"></div>
<h2><?php _e('Login Log', self::TEXT_DOMAIN); ?></h2>

<div class="tablenav">
<?php if (current_user_can('create_users')) { ?>
<div class="alignleft actions">
<form action="" method="get">
<input type="hidden" name="page" value="<?php echo plugin_basename(__FILE__); ?>" />
<select name="user_id">
<option value="-1"<?php if ($user_id == -1) echo ' selected="selected"';?>><?php _e('All Users', self::TEXT_DOMAIN); ?></option>
<option value="0"<?php if ($user_id == 0) echo ' selected="selected"';?>><?php _e('Unknown', self::TEXT_DOMAIN); ?></option>
<?php
		$users = $wpdb->get_results("select ID, user_login from `{$wpdb->users}` order by ID");
		foreach((array)$users as $user) {
			printf("<option value=\"%d\"%s>%s</option>\n", $user->ID, $user->ID == $user_id ? ' selected="selected"' : '', $user->user_login);
		}
?>
</select>
<?php submit_button( __( 'Apply Filters' ), 'action', false, false, array( 'id' => "doaction" ) );?>
</form>
</div>
<?php } ?>
<div class="alignright tablenav-pages">
<?php echo $page_links_text; ?>
</div>
<br class="clear" />
</div>

<div class="clear"></div>

<table class="widefat comments fixed" cellspacing="0">
<thead>
	<tr>
<?php if ($user_id <= 0) { ?>
	<th scope="col" class="manage-column column-username"><?php _e('User Name', self::TEXT_DOMAIN); ?></th>
<?php } ?>
	<th scope="col" class="manage-column column-date"><?php _e('Date', self::TEXT_DOMAIN); ?></th>
	<th scope="col" class="manage-column column-status"><?php _e('Status', self::TEXT_DOMAIN); ?></th>
	<th scope="col" class="manage-column column-ip"><?php _e('IP', self::TEXT_DOMAIN); ?></th>
	<th scope="col" class="manage-column column-agent"><?php _e('User Agent', self::TEXT_DOMAIN); ?></th>
	<th scope="col" class="manage-column column-errors" style=""><?php _e('Errors', self::TEXT_DOMAIN); ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
<?php if ($user_id <= 0) { ?>
	<th scope="col" class="manage-column column-username"><?php _e('User Name', self::TEXT_DOMAIN); ?></th>
<?php } ?>
	<th scope="col" class="manage-column column-date"><?php _e('Date', self::TEXT_DOMAIN); ?></th>
	<th scope="col" class="manage-column column-status"><?php _e('Status', self::TEXT_DOMAIN); ?></th>
	<th scope="col" class="manage-column column-ip"><?php _e('IP', self::TEXT_DOMAIN); ?></th>
	<th scope="col" class="manage-column column-agent"><?php _e('User Agent', self::TEXT_DOMAIN); ?></th>
	<th scope="col" class="manage-column column-errors" style=""><?php _e('Errors', self::TEXT_DOMAIN); ?></th>
	</tr>
</tfoot>

<tbody id="user-login-log">
<?php foreach($ull as $row) {?>
<?php
list($browser_name, $browser_code, $browser_ver, $os_name, $os_code, $os_ver, $pda_name, $pda_code, $pda_ver) = self::detect_browser($row->activity_agent);
$ua  = trim("$os_name $os_ver");
$ua .= $browser_code !== $os_code ? (empty($ua) ? '' : ' / ')."$browser_name $browser_ver" : '';
$ua  = trim($ua);

$errors = unserialize($row->activity_errors);
$user_login =
	(is_array($errors) && isset($errors['user_login']))
	? $errors['user_login']
	: $row->user_login;
$errors = 
	(is_array($errors) && isset($errors['errors']))
	? implode(', ', array_keys($errors['errors']))
	: '';
?>
<tr id="log-<?php echo $row_num ?>">
<?php if ($user_id <= 0) { ?>
<td class="username column-username"><?php echo $user_login; ?></td>
<?php } ?>
<td class="date column-date"><?php echo $row->activity_date; ?></td>
<td class="status column-status"><?php echo $row->activity_status; ?></td>
<td class="ip column-ip"><?php echo trim(self::get_country_flag($row->activity_IP) . '<br>' . $row->activity_IP); ?></td>
<td class="agent column-agent"><?php echo trim(self::get_browser_icon($row->activity_agent) . '<br>' . $ua); ?></td>
<td class="errors column-errors"><?php echo $errors; ?></td>
</tr>
<?php $row_num++; }?>
</tbody>
</table>

<div class="tablenav">
<div class="alignright tablenav-pages">
<?php echo $page_links_text; ?>
</div>
<br class="clear" />
</div>

</div>
<?php
	}
}

new user_login_log();
