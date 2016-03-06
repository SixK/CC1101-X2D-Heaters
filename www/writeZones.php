<?php
$zone1File="zone1.txt";
$zone2File="zone2.txt";

$state1="Moon";
$state2="Moon";

if(isset($_GET['zone1']))
{
	$state1=$_GET['zone1'];
	$fp1=fopen($zone1File,'w');	
	fwrite($fp1, $state1);
	fclose($fp1);
}

if(isset($_GET['zone2']))
{
	$state2=$_GET['zone2'];
	$fp2=fopen($zone2File,'w');	
	fwrite($fp2, $state2);
	fclose($fp2);
}

?>