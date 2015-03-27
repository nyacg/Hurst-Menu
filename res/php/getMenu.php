<?php 
	include "connectlocal.php"; //connect to the database

	$meal_datetime = strtotime("+".$_GET["d"]."days");
	$meal_date = date('Y-m-d', $meal_datetime);

	//echo $meal_date
	
	$lunch_query = "SELECT `date`, 
	(SELECT item_name FROM item WHERE item_id = soup_id) AS soup, 
	(SELECT item_name FROM item WHERE item_id = main_meat_id) AS main_meat, 
	(SELECT item_name FROM item WHERE item_id = main_fish_id) AS main_fish, 
	(SELECT item_name FROM item WHERE item_id = main_vegetarian_id) AS main_vegetarian, 
	(SELECT item_name FROM item WHERE item_id = potato_id) AS potato, 
	(SELECT item_name FROM item WHERE item_id = veg_1_id) AS veg_1, 
	(SELECT item_name FROM item WHERE item_id = veg_2_id) AS veg_2, 
	(SELECT item_name FROM item WHERE item_id = veg_3_id) AS veg_3, 
	(SELECT item_name FROM item WHERE item_id = alternative_id) AS alternative, 
	(SELECT item_name FROM item WHERE item_id = sauce_1_id) AS sauce_1, 
	(SELECT item_name FROM item WHERE item_id = sauce_2_id) AS sauce_2, 
	(SELECT item_name FROM item WHERE item_id = dessert_id) AS dessert 
	FROM lunch 
	WHERE `date` = '$meal_date'";

	$supper_query = "SELECT `date`, 
	(SELECT item_name FROM item WHERE item_id = soup_id) AS soup, 
	(SELECT item_name FROM item WHERE item_id = main_meat_id) AS main_meat, 
	(SELECT item_name FROM item WHERE item_id = main_fish_id) AS main_fish, 
	(SELECT item_name FROM item WHERE item_id = main_vegetarian_id) AS main_vegetarian, 
	(SELECT item_name FROM item WHERE item_id = staple_id) AS staple, 
	(SELECT item_name FROM item WHERE item_id = veg_1_id) AS veg_1, 
	(SELECT item_name FROM item WHERE item_id = veg_2_id) AS veg_2, 
	(SELECT item_name FROM item WHERE item_id = sauce_1_id) AS sauce_1, 
	(SELECT item_name FROM item WHERE item_id = sauce_2_id) AS sauce_2,
	(SELECT item_name FROM item WHERE item_id = dessert_id) AS dessert
	FROM supper 
	WHERE `date` = '$meal_date'";

	$lunch_result = mysqli_query($con, $lunch_query) or die(mysqli_error($con));
	$supper_result = mysqli_query($con, $supper_query) or die(mysqli_error($con));

	$lunch_array = mysqli_fetch_assoc($lunch_result);
	$supper_array = mysqli_fetch_assoc($supper_result);
	mysqli_close($con); //disconnect from the database

	//for testing purposes
	//var_dump($lunch_array);
	//var_dump($supper_array);

	//output a JSON encoded string which can be processed client side by jQuery and then used by JavaScript
	echo json_encode(array('lunch' => $lunch_array, 'supper' => $supper_array));
?>