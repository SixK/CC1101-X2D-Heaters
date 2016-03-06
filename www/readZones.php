<?php
$zone1File="zone1.txt";
$zone2File="zone2.txt";

$state1="Moon";
$state2="Moon";

if (file_exists($zone1File)) 
{
	$fp1=fopen($zone1File,'r');	
	$state1 = fread($fp1,100);
	
	fclose($fp1);
}

if (file_exists($zone2File)) 
{
	$fp2=fopen($zone2File,'r');	
	$state2 = fread($fp2,100);
	fclose($fp2);
}

$zonesArray=array("zone1"=>$state1, "zone2"=>$state2);
$zoneJson=json_encode($zonesArray);

/*
$fp=fopen($zoneFile,'w');	
fwrite($fp, $zoneJson);
fclose($fp);
*/

echo $zoneJson;

?>