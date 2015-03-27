<?php
	include "connectlocal.php";	//connect to db
	$average = null;	//initalise the array to hold the average data
	
	//get the average data as an array from the JSON encoded string that is passed into the script call
	if(isset($_GET['average'])){
		if($_GET['average'] != ""){
			//get the array of average attendance for each meal each day
			$average = (array) json_decode($_GET['average']);
		}
	} 
	//var_dump($average);

	//get the integer value of the displacement if it is set, otherwise set it to 0
	$displacement = isset($_GET['displacement']) ? intval($_GET['displacement']) : 0;

	//get the timestamp for the date of a day in the week that is beeing looked at
	$date = strtotime("+".$displacement." weeks");	
	$date_string = date("Y-m-d", $date);	//get that date in standard readable form
	$day_of_week = date("N", $date); 	//get the number for the position of that day in the week
	//echo $day_of_week;
	$dates_of_week = array();	//initalise array

	//for each day of the week, get the time stamp for that date (monday to sunday)
	for($i = 1; $i <= 7; $i++){
		$dates_of_week[] = strtotime("$date_string +" . ($i - $day_of_week) . " days");
	}
	//var_dump($dates_of_week);

	$query = "SELECT ";	//initialse query
	//for each time stamp
	foreach($dates_of_week as $day_date){
		$string_date_of_day = date("Y-m-d", $day_date);	//get the database readable date
		$day = date("l", $day_date);	//get name of day e.g. Monday
		//add to the query the queries to get the data for each meal that day
		$query .= "(SELECT actual_breakfast FROM attendance WHERE `date` = '$string_date_of_day') AS breakfast_$day, 
				(SELECT actual_lunch FROM attendance WHERE `date` = '$string_date_of_day') AS lunch_$day, 
				(SELECT actual_supper FROM attendance WHERE `date` = '$string_date_of_day') AS supper_$day, ";
	}

	$query = rtrim($query, ", ");	//remove the trailing comma
	//echo $query;
	$result = mysqli_query($con, $query) or die(mysqli_error($con));	//execute the query
	$data = mysqli_fetch_assoc($result);	//get the data as an associative array
	$neat_data = array();	//initalise array
	//var_dump($data);

	//for each row in the data
	foreach ($data as $key => $value) {
		//echo $key . " " . $value . "<br>";
		$split_key = explode("_", $key);	//split the $key (e.g. supper_monday)
		$meal = $split_key[0];	//get the meal
		$day = $split_key[1];	//get the day
		//add the raw actual attendance data for the meal on the day to the array in the correct position 
		$neat_data[$day][$meal]["attendance"] = $value;
		$key = strtolower($key);	//make the key lowercase (as that is the key from the average data)
		$neat_data[$day][$meal]["average"] = $average[$key];	//add the average data for the day and meal to the array
	}
	//var_dump($neat_data);

	//define the columns for the Data Table required by the API
	$cols = array(array('label' => "Day", 'type' => "string"), array('label' => "Breakfast", 'type' => "number"),  array('role'=> "interval", 'type'=> "number"), array('role'=> "interval", 'type'=> "number"), array('label' => "Lunch", 'type' => "number"),  array('role'=> "interval", 'type'=> "number"), array('role'=> "interval", 'type'=> "number"), array('label' => "Supper", 'type' => "number"), array('role' => "interval", 'type' => "number"), array('role' => "interval", 'type' => "number"));
	$rows = array();	//initalise the array if rows
	
	//for each day in the neat data array
	foreach ($neat_data as $key => $value) {
		//echo $key;
		//add an array to the rows array which contains arrays which are the cells and hold the values defined by the columns
		$rows[] = array('c' => array( array('v' => $key), array('v' => $value['breakfast']['attendance']), array('v' => $value['breakfast']['average']), array('v' => $value['breakfast']['average']), array('v' => $value['lunch']['attendance']), array('v' => $value['lunch']['average']), array('v' => $value['lunch']['average']), array('v' => $value['supper']['attendance']), array('v' => $value['supper']['average']), array('v' => $value['supper']['average'])));
	}
	//var_dump($rows);	
	//group the nicely formatted date (much easier to do in PHP than JS) and the array of columns and rows into one array
	$table_data = array('date' => date("l jS F Y", $dates_of_week[0]), 'tableData' => array('cols' => $cols, 'rows' => $rows));

	//enocde the data as a JSON string with the numeric values preserved as numbers
	$weeks_attendance_data = json_encode($table_data,  JSON_NUMERIC_CHECK);
	echo $weeks_attendance_data;	//output the data to the AJAX request
?>