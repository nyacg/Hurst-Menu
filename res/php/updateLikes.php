<?php
	include "connectlocal.php"; 	//connect to the database

	//get variables from script call
	$meal = $_POST["m"];
	$item = $_POST["i"];
	$likes_change = $_POST["lc"];
	$dislikes_change = $_POST["dc"];
	$meal_datetime = strtotime("+".$_POST["d"]."days");		//convert displacement to time stamp

	$meal_date = date('Y-m-d', $meal_datetime);		//convert displacement time stamp to date 

	echo $change . " " . $meal . " " . $item . "<br>";		//for alpha testing

	$meal_day = date('N', $meal_datetime); 		//get the numeric day of the week

	if($meal_day == 7){
		$item_type = "sunday";
	} elseif(strpos($item, "veg") !== false){
		//if it is veg_1, veg_2 or veg_3, just have veg as the item type
		$item_type = "veg";
	} elseif(strpos($item, "sauce") !== false){
		//if it is sauce_1 or sauce_2, just have sauce as the item type
		$item_type = "sauce";
	} else {
		$item_type = $item;
	}

	$query = "UPDATE vote 
				SET likes = likes + $likes_change, dislikes =  dislikes + $dislikes_change, item_type = '$item_type'
				WHERE `date` = '$meal_date' 
				AND `item_id` = (SELECT `" . $item . "_id` FROM $meal WHERE `date` = '$meal_date')";

	$result = mysqli_query($con, $query) or die(mysqli_error($con)); 	//execute the query
	
	//if the update query does not affect any rows, the item needs to be inserted into the database first
	if(mysqli_affected_rows($con) == 0) {
		$insert_query = "INSERT INTO vote (`date`, item_id, likes, dislikes, item_type) 
						VALUES ('$meal_date', (SELECT " . $item . "_id FROM $meal WHERE `date` = '$meal_date'), $likes_change, $dislikes_change, '$item_type')";
		echo $insert_query; 	//for alpha testing
		$insert_result = mysqli_query($con, $insert_query) or die(mysqli_error($con));	//execute insert query
	}
	mysqli_close($con);		//close connection to database
?>