<?php
require_once('ip2c.php');
define ('UNIT_TEST',true);
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

$csv_name = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : "";
if (!$csv_name) $csv_name = "../ip-to-country.csv";
$bin_name = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : "";
if (!$bin_name) $bin_name = "../ip-to-country.bin";
println();
println("Unit testing csv $csv_name against bin $bin_name");
println();
println();

println("----------- NO CACHING ------------------");
$ip2c = new ip2country($bin_name);
run_test($ip2c,$csv_name);
// this one caused problems before
$ip2c->get_country('10.0.0.1');
$ip2c->get_country('192.116.192.9');
println("------------------------------------");
println();
println();

println("----------- CACHING ------------------");
$ip2c = new ip2country($bin_name, true);
run_test($ip2c,$csv_name);
// this one caused problems before
$ip2c->get_country('10.0.0.1');
$ip2c->get_country('192.116.192.9');
println("------------------------------------");

function run_test($ip2c, $csvFile) 
{
	global $num;
	global $total;
	$num = 0;$total = 0;
	
	$csv = fopen(dirname(__FILE__)."/$csvFile", "r");
	if (!$csv)
	{
		ip2c_die("Error opening $csvfile");
	}
	
	$row = 0;
	$count = 0;
	
	while (($expected = fgetcsv($csv, 1000, ",")) !== FALSE) 
	{
		if (isset($expected[0][0]) && $expected[0][0] == '#') continue; // skip comments
		$row++;
		if ($row % 10 != 0) continue; // only test every 10th row.
		$count++;
		$start = $expected[0];
		$end  = $expected[1];
		
		test($ip2c, $expected, $start);
		if ($end - $start > 1)
			test($ip2c, $expected, $start+1);
		if ($end < IP2C_MAX_INT * 2)
			test($ip2c, $expected, $end);
		if ($end - $start > 1 && ($end-1) < IP2C_MAX_INT * 2)
			test($ip2c, $expected, $end-1);
		test($ip2c, $expected, ($start+$end)/2);
		if ($count % 1000 == 0) println("Tested $count ranges");
		flush();
	}
	$t2 = $total * 1000;
	println("Test passed");
	
	fclose($csv);
}


function test($ip2c, $expected, $ip)
{
	$ips = long2ip($ip);
	$country = $ip2c->get_country($ips);

	if ($expected == false && $country == false) return;
	if ($expected == false && $country != false) ip2c_die("Expected " . var_export($expected, true) . ", got " . var_export($country, true) . " ||| $ip $ips");
	if ($expected != false && $country == false) ip2c_die("IP ($ip $ips) Not found, Expected :\n" . var_export($expected, true));
	if (count($expected) == 5) // webhosting
	{
		$id2c = $expected[2];
		$id3c = $expected[3];
		$name = $expected[4];
	}
	else 
	if (count($expected) == 7) // software77
	{
		$id2c = $expected[4];
		$id3c = $expected[5];
		$name = $expected[6];
	}
	
	$o2c = $country['id2'];
	$o3c = trim($country['id3']); // there is at least one case of a 3c code which has only 2 chars: EU. to avoid stupid errors from the unit test, trim.
	$oname = $country['name'];
	
	if (strcmp($id2c,$o2c) != 0 || 
		strcmp($id3c,$o3c) != 0 || 
		strcmp($name,$oname) != 0)
	{
		println("in  3c ".strlen($id3c));
		println("out 3c ".strlen($o3c));
		ip2c_die("Expected :\n2c = '$id2c', 3c = '$id3c' , name = '$name'\ngot:\n2c = '$o2c', 3c = '$o3c' , name = '$oname'\nIP address: $ip\n");
	}
}

function ip2c_die($msg)
{
	println($msg);
	exit(1);
}
?>
