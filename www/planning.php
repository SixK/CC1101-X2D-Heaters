<?php
require_once './PHPExcel-1.8/Classes/PHPExcel.php';

date_default_timezone_set('UTC');


$inputFileType = 'ODS';
$inputFileName = 'chauffage.ods';

$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
$objWriter->setDelimiter(';')->setEnclosure('"')->save('chauffage.csv');


$synopsis = $objPHPExcel->getSheetByName("Feuille1");
$objPHPExcel->setActiveSheetIndex(0);
$column = $synopsis->getHighestColumn();
$row = $synopsis->getHighestRow();
// echo $row." ".$column;

$data = array();
for($i=1;$i<=$row;$i++)
{
	for($j='A';$j!=$column;$j++)
	{
		$data[$i][$j]['rotate']=0;
		$data[$i][$j]['value'] = $synopsis->getCell($j.$i)->getFormattedValue();
		$data[$i][$j]['color'] = $synopsis->getStyle($j.$i)->getFill()->getStartColor()->getARGB();
		$data[$i][$j]['color'] = substr($data[$i][$j]['color'],strlen($data[$i][$j]['color'])-6,strlen($data[$i][$j]['color']));   
		$data[$i][$j]['color'] = $data[$i][$j]['color']=="000000" ? '' : $data[$i][$j]['color'];  /*black should be renamed to white*/  
		
		if (substr_count($data[$i][$j]['value'],':') == 2  ) $data[$i][$j]['rotate']=-90;
		
	}
}


$table_html =  '<table cellpadding="0" border="1" style="border-collapse:collapse">';
foreach ($data as $row) {
	$table_html .= "<tr>";
		foreach ($row as $value)
		{
			if($value['rotate'] == -90)  $table_html .= '<td><div style="background-color:#'.$value['color'].';transform: translate(28px, 25px) rotate('.$value['rotate'].'deg) scaleY(0.8);white-space: nowrap;height:90px;max-width:20px;">'.(is_numeric($value['value']) ? number_format($value['value'],0) : $value['value']).'</div></td>';
			else  $table_html .= '<td style="background-color:#'.$value['color'].'">'.(is_numeric($value['value']) ? number_format($value['value'],0) : $value['value']).'</td>';
		}
	$table_html .= "</tr>";
}
$table_html .= "</table>";

?>