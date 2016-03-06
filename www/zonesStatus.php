<?php
$zoneFile='zones.json';

$state1="Moon";
$state2="Moon";

if (file_exists($zoneFile)) 
{
	$json = file_get_contents($zoneFile);
	$zoneArray = json_decode($json);
	
	$state1=$zoneArray->zone1;
	$state2=$zoneArray->zone2;
}

if(isset($_GET['zone1']))
{
	$state1=$_GET['zone1'];
}

if(isset($_GET['zone2']))
{
	$state2=$_GET['zone2'];
}

$zonesArray=array("zone1"=>$state1, "zone2"=>$state2);
$zoneJson=json_encode($zonesArray);

$fp=fopen($zoneFile,'w');	
fwrite($fp, $zoneJson);
fclose($fp);

echo $zoneJson;

?>