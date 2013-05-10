<?php

require_once('ip2c.php');
set_time_limit(0);
function is_cmd()
{
	return (php_sapi_name() == "cli"); 
}

if (!is_cmd())
{
	die("ip2c test should be executed from the command line (php -f)");
}


function println($msg = "")
{
	if (is_cmd()) echo $msg . "\n";
	else echo $msg . "<br/>";
}

$bin_name = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : "";

if (!$bin_name) $bin_name = "../ip-to-country.bin";

$ips = array();
$len = 100000;
println("Generating $len random IP addresses....");
flush();
for ($i = 0;$i<$len;$i++)
{
	$ips[$i] = mt_rand(0,255) . "." . mt_rand(0,255) . "." . mt_rand(0,255) . "." . mt_rand(0,255);
}

println("Warm up done");
println("Resolving addresses:");
println();
println("Testing bin file : $bin_name");
println("----------- NO CACHING ---------------");
$ip2c = new ip2country($bin_name);
runBenchmark($ip2c, $ips, $len);
println("------------------------------------");
println();
println();
println("----------- CACHING ------------------");
$ip2c = new ip2country("$bin_name", true);
runBenchmark($ip2c, $ips, $len);
println("------------------------------------");

function runBenchmark($ip2c, $ips, $len) {
	$now = microtime_float();
	$progress = $len / 20;

	for ($i = 0; $i < $len; $i++)
	{
		if ($i % $progress == 0 && $i != 0) 
		{
			echo ".";
			flush();
		}
		$ip2c->get_country($ips[$i]);
	}
	$t = microtime_float() - $now;
	println();
	println($t . " ms for $len searches (".($len / $t) ." searches/sec)");
}

function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}
?>
