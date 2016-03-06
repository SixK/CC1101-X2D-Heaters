<?php

class HandlePlanning
{
	public function __construct() {
		$this->_horaireLine = 2;
		$this->_horairePos = 3;
		$this->_zonePos=2;
		$this->_dayPos=2;
		$this->_zoneStateArray=array();		
		$this->_horaireArray=array();
	}

	public function SetData($data)
	{
		$this->_data=$data;
		return $this;
	}
	
	public function getHoraires() 
	{
		$this->_horaireArray=array_slice($this->_data[$this->_horaireLine-1], $this->_horairePos-1 );
		return $this->_horaireArray;
	}
	
	public function getZone($zoneName){
		$x=0;
		$zoneFound=0;
		foreach ($this->_data as $row) 
		{
			if($row[$this->_zonePos-1] == $zoneName) {
				// echo "Zone : ".$zoneName. " found at pos : ".$this->_zonePos.",".$x;
				$zoneFound=1;
			}else{
				// echo "Data : ".$row[$this->_zonePos-1];
			}
			$x+=1;
			
			if ($zoneFound)
			{
				$this->_zoneStateArray[]=$row;
				
				if (strtolower($row[$this->_zonePos-1]) == "dimanche"){
					// print ("last day found !");
					$this->_zoneStateArray[]=$row;
					break;
				}
			}
		}
	}
	
	public function getDays()
	{
		echo "getDays";
	}
	
	public function getTimeHoraire($time)
	{
		// var_dump($this->_horaireArray);
		$x=0;
		foreach (array_slice($this->_horaireArray,0, -1) as $horaire) 
		{
			if ((strtotime($horaire) <= strtotime($time)) && (strtotime($this->_horaireArray[$x+1]) > strtotime($time)))
			{
				return $x;
				break;
			}
			$x+=1;
		}
	}
		
	public function getStatus($day, $time)
	{
		$status=0;
		// echo "getStatus";
		$hPos=$this->getTimeHoraire($time);
		
		foreach ($this->_zoneStateArray as $line) 
		{
			if (strtolower($line[$this->_dayPos-1]) == $day)
			{
				$status = $line[$this->_dayPos + $hPos];
				if ($status == '')
					$status = 0;					
				if ($status == '1')
					$status = 1;

				// echo $status;	
				break;
			}
		}		
		return $status;
	}
}
?>