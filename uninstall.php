<?php

if( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
	exit();


global $wpdb;
$wpdb->query("delete from {$wpdb->usermeta} where meta_key like 'user_login_log%';");
delete_option( 'user-login-log' );

$ull_table = $wpdb->prefix.'user_login_log';
if ($wpdb->get_var("show tables like '{$ull_table}'") != $ull_table)
	$wpdb->query("DROP TABLE `{$ull_table}`");
