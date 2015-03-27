<?php
	include "connectlocal.php";

	$query = "SELECT shell + remove + fifth + LVI + UVI AS confirmed,  actual_supper AS actual, WEEKDAY(`date`) AS day, DAYNAME(`date`) AS day_name FROM attendance ORDER BY `date` DESC LIMIT 300";
	$result = mysqli_query($con, $query);
	
	//$cols = array(array('label' => "Confirmed Attendance", 'type' => "number"), array('label' => "Actual Attendance", 'type' => "number"));
	//$rows = array();
	$data = array(array(), array(), array(), array(), array(), array(), array());

	while($row = mysqli_fetch_row($result)){
		//$rows[] = array('c' => array(array('v' => $row[0]), array('v' => $row[1])/*, array('v' => $days[$row[2]-1])*/));
		//$rows[] = $row;
		$data[$row[2]][] = array($row[0], $row[1]);
	}
	//var_dump($rows);
	//$json = json_encode(array('cols' => $cols, 'rows' => $rows), JSON_NUMERIC_CHECK);
	//var_dump($data);
	$json = json_encode($data, JSON_NUMERIC_CHECK);
	echo $json;
?>