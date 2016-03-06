<?php
$modeFile='mode.csv';

if(isset($_GET['mode']))
{
	$mode=$_GET['mode'];
	$fp=fopen($modeFile,'w');
	
	fwrite($fp, $mode);
	
	fclose($fp);
	
}else
{
	$mode='';
	if (file_exists($modeFile)) 
	{
		$fp=fopen($modeFile,'r');
		$mode=fread($fp, filesize($modeFile));
		fclose($fp);
	}
	
	$modeArray=array('mode'=>$mode);
	
	echo json_encode($modeArray);
}

?>