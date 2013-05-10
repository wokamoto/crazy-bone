<?php

if( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
	exit();


global $wpdb;
$wpdb->query("delete from {$wpdb->usermeta} where meta_key like 'user_login_logging%';");

$user_login_logging_table = $wpdb->prefix.'user_login_logging';
if ($wpdb->get_var("show tables like '{$user_login_logging_table}'") != $user_login_logging_table)
	$wpdb->query("DROP TABLE `{$user_login_logging_table}`");
