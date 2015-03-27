<?php
	include "connectlocal.php";		//connect to db
	
	//get the variables from the script call
	$change = intval($_POST["change"]);
	$yeargroup = $_POST["yeargroup"];
	$meal_datetime = strtotime("+".$_POST["d"]."days");	//displacement to time stamp
	$meal_date = date('Y-m-d', $meal_datetime);	//time stamp to fromatted date

	echo $change . " " . $yeargroup . " " . $meal_date . "<br>";	//for testing

	//update query
	$query = "UPDATE attendance SET $yeargroup = $yeargroup + $change WHERE `date` = '$meal_date'";
	echo $query;	//for testing
	$result = mysqli_query($con, $query) or die(mysqli_error($con));	//execute update query
	
	//if update didn't make any channges then insert the row
	if(mysqli_affected_rows($con) == 0) {
		$insert_query = "INSERT INTO attendance (`date`, shell, remove, fifth, LVI, UVI, actual_breakfast, actual_lunch, actual_supper) 
						VALUES ('$meal_date', 0, 0, 0, 0, 0, 0, 0, 0)";
		echo $insert_query;
		$insert_result = mysqli_query($con, $insert_query) or die(mysqli_error($con));	//execute the insert query

		$result = mysqli_query($con, $query) or die(mysqli_error($con));	//execute the update query
	}
	mysqli_close($con);	//close db connection
?>