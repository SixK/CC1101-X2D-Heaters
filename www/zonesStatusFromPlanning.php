<?php

require_once "HandlePlanning.php";

// date_default_timezone_set('UTC');

$dayNames=['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
$stateNames=['Moon','Sun'];

$row = 1;
if (($handle = fopen("chauffage.csv", "r")) !== FALSE) {
    while (($line = fgetcsv($handle, 1000, ";", '"')) !== FALSE) {
		$data[]=$line;
    }
    fclose($handle);
}

$zone1=new HandlePlanning;
$zone2=new HandlePlanning;

$zone1->setData($data);
$zone2->setData($data);

$horaires=$zone1->getHoraires();
$zone2->getHoraires();

$zone1->getZone("Zone 1");
$zone2->getZone("Zone 2");

$dayName=$dayNames[date("N")-1];
$timeStr=date("H:i:s");

// $timeStr="07:32:00";

$stat1=$stateNames[$zone1->getStatus($dayName, $timeStr)];
$stat2=$stateNames[$zone2->getStatus($dayName, $timeStr)];
// print $stat1;
// print $stat2;
$zones=array("zone1"=>$stat1,"zone2"=>$stat2);

// $zones=array("zone1"=>"Sun","zone2"=>"Moon");

//$ret=array($zones);
echo json_encode($zones);

?>