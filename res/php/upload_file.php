<?php
	include "connectlocal.php"; //connect to database
	include "checkLogin.php"; //ensure user is logged in

	//added to stop the script timing out when there is a large number of menus to be processed
	ini_set('max_execution_time', 300);

	//collect variables from form
	$start_date = strtotime($_POST["start_date"]);
	$end_date = strtotime($_POST["end_date"]);
	$start_week = (int) $_POST["start_week"];
	
	//set the length of menu as a variable so if it changes th
	$length_of_menu = 3;

	//assume the upload fails so the error message will be shown unless it completes successfully
	$_SESSION["upload_failed"] = true;

	//if no errors and the start date is before the end date and the file is an Excel document
	if (!empty($_FILES['file']['name']) and $start_date <= $end_date and strpos($_FILES["file"]["type"],'excel') !== false) {

		if ($_FILES["file"]["error"] > 0) {
			echo "Error: " . $_FILES["file"]["error"] . "<br>";
		} else {
			//used for alpha testing when just testing the file had uploaded
			/*echo "Upload: " . $_FILES["file"]["name"] . "<br>";
			echo "Type: " . $_FILES["file"]["type"] . "<br>";
			echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
			echo "Stored in: " . $_FILES["file"]["tmp_name"] . " <br>";
			echo "Start Date: " . date("d-m-Y",	$start_date);*/
		}



		$file_path = $_FILES["file"]["tmp_name"]; //set the file path as variable

			//turn Excel file into an array (taken from PHPExcel documentation)

			require_once './PHPExcel/Classes/PHPExcel/IOFactory.php';
			$objPHPExcel = PHPExcel_IOFactory::load($file_path);

			//initialise array for the lunches and suppers
			$lunches = array();
			$suppers = array();
			
			//for each sheet (all 3 lunches and all 3 suppers)
			for($week = 0; $week < $length_of_menu*2; $week++){

				//convert current sheet to an array (taken from PHPExcel documentation)
				$rows = $objPHPExcel->setActiveSheetIndex($week)->toArray(null, true, true, true);

				// get the column names
				$xls_fields = isset($rows[1]) ? $rows[1] : array();
				if (! empty($xls_fields)) {
					unset($rows[1]);
				}

				//create a tidy array with just the menu items required
				$clean_array = array();
				$i = 0;

				//used for alpha testing to view all data in the array so I could work out which indexes of the array contained the data for the items I required
				//var_dump($rows); 
				foreach ($rows as $row) {
					//remove keys again
					$data = array();
					foreach ($row as $key => $value) {
						$data[] = $value;
					}

					if($week < $length_of_menu){
						//for lunch menus remove data not required
						if($i!=0 && $i!=2 && $i!=10 && $i!=14 && $i<16){
							$clean_array[] = $data;
							}
						} else {
							//for supper menus remove data not required
							if($i!=0 && $i!=2 && $i!=9 && $i!=10 && $i!=13 && $i<15){
							$clean_array[] = $data;
						}
						}
					$i++;
				}

				//used for alpha testing to view all data in the array so I could confirm I had the right data
				//var_dump($clean_array);

				//insert each day's menu into the database
				for($day=0; $day<7; $day++) {
				//	$meal_date = date('Y-m-d', strtotime("+".$day." days",	$date));
					$name_array = array();
					$id_array = array();
					for($item_number=0; $item_number<count($clean_array); $item_number++){
						//remove pointless details from menu items (excess spaces, details repeated on every day,) and make '&' look nicer
						$clean_item = trim(str_replace(array("Freshly Made", "and Bread"), "", str_replace("&", "and", preg_replace('/\s{2,}/', ' ', $clean_array[$item_number][$day]))));
						//ensure the item can be inseted into the database without issues
						$clean_item = mysqli_real_escape_string($con, $clean_item);

						//add the item to the end of the array of items
						$name_array[] = $clean_item;

						//update the items table using the function defined below
						update_all_main_items($clean_item, $con);

						//get the id of item
						$result = mysqli_fetch_array(mysqli_query($con, "SELECT item_id FROM item WHERE item_name='$clean_item'"));
						if($week<$length_of_menu){
							//if it is a lunch menu then add the item id to the end of the array of lunches 
							//at the week and day that is currently being processes
							$lunches[$week][$day][] = (int) $result['item_id'];
						} else {
							//if it is a supper menu then add the item id to the end of the array of suppers
							//at the week and day that is currently being processes
							$suppers[$week-$length_of_menu][$day][] = (int) $result['item_id'];
						}
					}
			 	}
			
			 	//release the arrays from memory so they are clear for the next week
				unset($rows);
				unset($clean_array);
				unset($id_array);
				unset($name_array);
			}
			unset($objPHPExcel);


	 	//loop to insert menu a number of times from start date to end date
		$datetime = $start_date;
		$week_number = $start_week-1;
		while($datetime <= $end_date) {

			$day = date("N", $datetime)-1;
			$date = date("Y-m-d", $datetime);
			$lunch_today = $lunches[$week_number][$day];
			$supper_today = $suppers[$week_number][$day];

			//insert the lunch and supper menu for each day into the database
			//INSERT IGNORE so the script does not fail if you are attempting to overwrite menu data already in the database
			$insert_lunch_query = "INSERT IGNORE INTO lunch (`date`, soup_id, main_meat_id, main_fish_id, main_vegetarian_id, potato_id, veg_1_id, veg_2_id, veg_3_id, alternative_id, sauce_1_id, sauce_2_id, dessert_id) VALUES ('$date', '$lunch_today[0]', '$lunch_today[1]', '$lunch_today[2]', '$lunch_today[3]', '$lunch_today[4]', '$lunch_today[5]', '$lunch_today[6]', '$lunch_today[7]', '$lunch_today[8]', '$lunch_today[9]', '$lunch_today[10]', '$lunch_today[11]')";
			$insert_supper_query = "INSERT IGNORE INTO supper (`date`, soup_id, main_meat_id, main_fish_id, main_vegetarian_id, staple_id, veg_1_id, veg_2_id, sauce_1_id, sauce_2_id, dessert_id) VALUES ('$date', '$supper_today[0]', '$supper_today[1]', '$supper_today[2]', '$supper_today[3]', '$supper_today[4]', '$supper_today[5]', '$supper_today[6]', '$supper_today[7]', '$supper_today[8]', '$supper_today[9]')";
			
			mysqli_query($con, $insert_supper_query) or die(mysqli_error($con));	
			mysqli_query($con, $insert_lunch_query) or die(mysqli_error($con));

			$datetime = strtotime("+1 day", $datetime);
			
			if($day == 6){
				$week_number++;
				if($week_number >= $length_of_menu){
					$week_number = 0;
				}
			}
		}	


		//get the date of monday of the first week that was just uploaded
		$day_of_date = date("N", $start_date);
		$date_string = date("Y-m-d", $start_date);
		$monday_date = strtotime("$date_string +" . (1 - $day_of_date) . " days");
		//convert that date into a number that is the displacement of that date to today in weeks
		$date_difference = $monday_date - strtotime("now");
		$week_difference = ceil($date_difference / (7*24*60*60));
		//used for alpha testing:
		//echo $date_difference . "<br>";
		//echo $week_difference;

		//everthing has gone successfully so the fail session can be removed
		unset($_SESSION['upload_failed']);

		//go to the View Menu page, showing the first week of menu just uploaded
		header("location: /Hurst%20Menu/ViewMenu.php?displacement=$week_difference");	
	} else {
		//invalid details were uploaded so return to Upload Menu page without removing fail session
		header("location: /Hurst%20Menu/uploadMenu.php");
	}

		
	function update_all_main_items($fitem, $fconnection){
		//INSERT IGNORE means the item will only be inserted if it doesn't already exist
		$mainItemsQuery = "INSERT IGNORE INTO item (item_name) VALUES ('$fitem')";
		mysqli_query($fconnection, $mainItemsQuery);
	}
?>