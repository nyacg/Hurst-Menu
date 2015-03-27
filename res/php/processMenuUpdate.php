<?php
	include "checkLogin.php";	//ensure user is authenticated
	include "connectlocal.php";	//connect to db

	//assume edit failed so error message is shown unless it completed successfully
	$_SESSION['edit_okay'] = false;

	//get the date of the meal from the form
	$meal_date = $_POST['date'];
	//remove this from the form data array so it is not included when we loop through 
	unset($_POST['date']);

	//queries to get the ids of each item from lunch and supper for the current date
	$lunch_id_query = "SELECT * FROM lunch WHERE `date` = '$meal_date' LIMIT 1";
	$supper_id_query = "SELECT * FROM supper WHERE `date` = '$meal_date' LIMIT 1";

	//executing the queries
	$lunch_id_result = mysqli_query($con, $lunch_id_query) or handleError();
	$supper_id_result = mysqli_query($con, $supper_id_query) or handleError();

	//getting the ids for lunch and supper as an associative array
	$lunch_ids = mysqli_fetch_assoc($lunch_id_result);
	$supper_ids = mysqli_fetch_assoc($supper_id_result);
	$ids = array('lunch' => $lunch_ids, 'supper' => $supper_ids);

	//for testing
	//var_dump($lunch_ids);
	//var_dump($supper_ids);

	//loop through all the fields from the form
	foreach ($_POST as $key => $value){
		//echo "Field $key is $value <br>";

		$split_key = "";	//initalise variable

		//get the meal from the name of the field
		if(strpos($key, '_lunch_') !== false){
			$meal = "lunch";
			$split_key = explode("_lunch_", $key);
		} elseif (strpos($key, '_supper_') !== false) {
			$meal = "supper";
			$split_key = explode("_supper_", $key);
		} else {
			die("Invalid data");
		}

		//get the type of item from the name of the field and add '_id' to the end to make it a feild name from the lunch or supper table
		$item_identifier = $split_key[0]."_id";
		//get the old name of the item from the name of the field and make the underscores spaces again
		$item_old_name = str_replace("_", " ", $split_key[1]);
		$item_new_name = $value;	//the new name of the item is the text in the field
		$item_new_clean_name = preg_replace('/\s{2,}/', ' ', $item_new_name);	//remove excess spaces
		$item_new_clean_name = mysqli_real_escape_string($con, $item_new_clean_name);	//esxape the value (make it db insert safe)

		//echo "Item $item_identifier used to be <br> $item_old_name is now <br> $item_new_name <br><br>";

		//if the value has changed
		if($item_new_clean_name != $item_old_name) {
			//get the id of the origional item from the ids array which we populated at the beginning 
			$item_id = $ids[$meal][$item_identifier];
			//echo "$item_id <br>";

			//query to get the number of times the item has appeared on the menu
			$item_count_query = "SELECT 
			(SELECT COUNT(soup_id) FROM lunch WHERE soup_id=$item_id) + (SELECT COUNT(soup_id) FROM supper WHERE soup_id=$item_id) + 
			(SELECT COUNT(main_meat_id) FROM lunch WHERE main_meat_id=$item_id) + (SELECT COUNT(main_meat_id) FROM supper WHERE main_meat_id=$item_id) + 
			(SELECT COUNT(main_fish_id) FROM lunch WHERE main_fish_id=$item_id) + (SELECT COUNT(main_fish_id) FROM supper WHERE main_fish_id=$item_id) + 
			(SELECT COUNT(main_vegetarian_id) FROM lunch WHERE main_vegetarian_id=$item_id) + (SELECT COUNT(main_vegetarian_id) FROM supper WHERE main_vegetarian_id=$item_id) + 
			(SELECT COUNT(potato_id) FROM lunch WHERE potato_id=$item_id) + (SELECT COUNT(staple_id) FROM supper WHERE staple_id=$item_id) + 
			(SELECT COUNT(veg_1_id) FROM lunch WHERE veg_1_id=$item_id) + (SELECT COUNT(veg_1_id) FROM supper WHERE veg_1_id=$item_id) + 
			(SELECT COUNT(veg_2_id) FROM lunch WHERE veg_2_id=$item_id) + (SELECT COUNT(veg_2_id) FROM supper WHERE veg_2_id=$item_id) + 
			(SELECT COUNT(veg_3_id) FROM lunch WHERE veg_3_id=$item_id) + 
			(SELECT COUNT(alternative_id) FROM lunch WHERE alternative_id=$item_id) +
			(SELECT COUNT(sauce_1_id) FROM lunch WHERE sauce_1_id=$item_id) + (SELECT COUNT(sauce_1_id) FROM supper WHERE sauce_1_id=$item_id) + 
			(SELECT COUNT(sauce_2_id) FROM lunch WHERE sauce_2_id=$item_id) + (SELECT COUNT(sauce_2_id) FROM supper WHERE sauce_2_id=$item_id) + 
			(SELECT COUNT(dessert_id) FROM lunch WHERE dessert_id=$item_id) + (SELECT COUNT(dessert_id) FROM supper WHERE dessert_id=$item_id) 
			AS count";
			//echo $item_count_query;

			$item_count_result = mysqli_query($con, $item_count_query) or handleError();	//execute query
			$item_count_row = mysqli_fetch_row($item_count_result);	//get result
			$item_count = $item_count_row[0];	//get the count
			//echo $item_count."<br>";

			//insery the new item into the item table
			//INSERT IGNORE means the item will only be inserted if it doesn't already exist
			$insert_item_query = "INSERT IGNORE INTO item (item_name) VALUES ('$item_new_clean_name')";
			$insert_item_result = mysqli_query($con, $insert_item_query) or handleError();	//execute query

			//get the id of the item just inserted 
			if(mysqli_insert_id($con)){
				$item_new_id = mysqli_insert_id($con);
			} else {
				//if the new item alredy exisited in the array (it wasn't just inserted)
				//get the id of the new item from the item table
				$item_new_id_result = mysqli_query($con, "SELECT item_id FROM item WHERE item_name='$item_new_clean_name'") or handleError();
				$item_new_id_row = mysqli_fetch_assoc($item_new_id_result);
				$item_new_id = $item_new_id_row['item_id'];
			}

			//query to update the meal with the new item id
			$update_meal_query = "UPDATE $meal SET $item_identifier = $item_new_id WHERE `date` = '$meal_date'";
			$update_meal_result = mysqli_query($con, $update_meal_query) or handleError();	//execute query

			//if the origional item only appared once in the menus
			if($item_count == 1) {
				//delete it from the item table as it is no longer required 
				$remove_query = "DELETE FROM item WHERE item_id = $item_id; DELETE FROM vote WHERE item_id = $item_id;";
				$remove_result = mysqli_multi_query($con, $remove_query) or handleError();
			}
		}
	}
	mysqli_close($con);	//close connection to db
	$_SESSION['edit_okay'] = true;	//set the edit okay session to true
	//return to edit menu page
	header("location: /Hurst%20Menu/editMenu.php?date=$meal_date");

	//if something goes wrong then the user should be shown the edit menu page 
	//and the script should stop
	function handleError(){
		header("location: /Hurst%20Menu/editMenu.php?date=$meal_date");
		die();
	}
?>