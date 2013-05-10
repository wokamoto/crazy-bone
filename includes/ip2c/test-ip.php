<?php
error_reporting(E_ALL | (defined('E_STRICT')? E_STRICT : 0));
require_once('ip2c.php');

$ip = htmlentities(isset($_GET['ip']) ? $_GET['ip'] : $_SERVER['REMOTE_ADDR']);
$ip2c = new ip2country("../ip-to-country.bin");
var_dump($ip2c->find_country_impl(16981,0, $ip2c->m_numCountries));
return;
$res = $ip2c->get_country($ip);
if ($res == false)
  echo "$ip => not found";
else
{
  $o2c = $res['id2'];
  $o3c = $res['id3'];
  $oname = $res['name'];
  echo "$ip => $o2c $o3c $oname";
}

?>
