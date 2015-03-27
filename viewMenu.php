<?php 
	include "./res/php/checkLogin.php";		//ensure user is logged in
	include "./res/php/connectlocal.php";	//connect to db
?>

<!DOCTYPE HTML>

<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>View Menu - Hurst Menu</title>
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>

		<div class="page">
			<div class="page-header">
				<h1>View Menu</h1>
			</div>
			<?php
				$displacement = 0;	//initalise displacement variable
				//get displacement in weeks from URL
				if(isset($_GET['displacement'])){
					$displacement = $_GET['displacement'];
				}
				//convert displacement as a time stamp
				$date_of_menu = strtotime("+$displacement weeks");
				$date_string = date("Y-m-d", $date_of_menu);	//get date as a string formatted for db
				
				//get date of the monday and friday of the week 
				$day_of_date = date("N", $date_of_menu);
				$monday_date = date("Y-m-d", strtotime("$date_string +" . (1 - $day_of_date) . " days"));
				$sunday_date = date("Y-m-d", strtotime("$date_string +" . (7 - $day_of_date) . " days"));
				$week_start = date("l jS F Y", strtotime($monday_date));	//get nicely formatted date
			?>
			<!-- show a heading with the nicely formatted date -->
			<h3>Menus for week starting <?php echo $week_start; ?></h3>
			<!-- left and right buttons to change the menu being shown to the previous and next week respectively -->
			<div class="change-week-buttons">
				<button type="button" id="previous-week" class="btn btn-default glyphicon glyphicon-chevron-left"  onclick="location.href='./viewMenu.php?displacement=<?php echo $displacement -1; ?>'"></button>
				Change Week
				<button type="button" id="next-week" class="btn btn-default glyphicon glyphicon-chevron-right" onclick="location.href='./viewMenu.php?displacement=<?php echo $displacement +1; ?>'"></button>
			</div>

			<!-- Bootstrap's panel and table components to hold the menus for lunch and supper -->
			<div class="panel panel-default">
				<div class="panel-heading">Lunch Menu</div>
				<table class="table table-striped">
					<?php
						//query to get the lunch menu for each day of the week
						$lunch_query = "SELECT `date`, DAYNAME(`date`) AS day, 
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
						WHERE `date` BETWEEN '$monday_date' AND '$sunday_date'";

						//echo $lunch_query;

						$lunch_result = mysqli_query($con, $lunch_query) or die(mysqli_error($con));	//execute query

						//output the data as a table
						outputTable($lunch_result);
					?>
				</tbody>
				</table>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading">Supper Menu</div>
				<table class="table table-striped">
					<?php
						//query to get the supper menu for each day of the week
						$supper_query = "SELECT `date`, DAYNAME(`date`) AS day, 
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
						WHERE `date` BETWEEN '$monday_date' AND '$sunday_date'";

						$supper_result = mysqli_query($con, $supper_query) or die(mysqli_error($con));	//execute query
						
						//ouptut the data as a table
						outputTable($supper_result);

					?>
				</tbody>
				</table>
			</div>

			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>

<?php
	//a function to output the result of the query passed into it as a table
	function outputTable($result){
		$rows = array();	//initalise array
		
		//loop through each day
		while($row = mysqli_fetch_assoc($result)){
			//loop through each item for that day
			foreach ($row as $key => $value) {
				//add item to array, grouped by item type (column)
				$rows[$key][] = $value;
			}
		}
		//var_dump($rows);

		//if there was data for some of the days in the week
		if(count($rows) > 0){
			$dates = $rows['date'];	//get date as variable
			unset($rows['date']);		//remove date from array
			echo "<thead><tr><th></th>";	//start the table

			//loop through the days outputting a heading of the name of the day
			foreach ($rows['day'] as $day) {
				echo "<th>$day</th>";
			}

			echo "</tr><thead>";	//close heading of the table
			unset($rows['day']);	//remove the days array from the array
			echo "<tbody>";	//open the body of the table

			//for each item type output a new table row with a heading of the item type
			foreach ($rows as $key => $value) {
				$item_split = explode("_", $key);
				$item_name = ucwords(str_replace("_", " ", $key));
				echo "<tr>";
				echo "<th>$item_name</th>";
				//for each item, add a new cell to the table with the item name
				foreach ($value as $item) {
					echo "<td>$item</td>";
				}
				echo "</tr>";
			}

			echo "<tr><td></td>";	
			//for each day, provide a link to edit the menu for that day
			foreach ($dates as $date) {
				echo "<td><a href='./editMenu.php?date=$date'>Edit</a></td>";
			}
			echo "</tr>";
		} else {
			//if there is no data, inform the user
			echo "<div class='alert alert-info' style='margin: 10px; max-width: 600px'><strong>Heads up!</strong> No data for selected week</div>";
		}
	}

?>