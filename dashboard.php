<?php 
	include "./res/php/checkLogin.php";		//ensure user is logged in 
	include "./res/php/connectlocal.php";	//connect to db
 ?>

<!DOCTYPE HTML>

<html lang='en'>
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Dashboard - Hurst Menu</title>
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>

		<div class="page">
	
			<div class="page-header">
				<h1>Hurst Menu <small>Dashboard</small></h1>
			</div>

			<?php
				//get the number of days of menu remaining
				$days_remaining_query = "SELECT DATEDIFF(MAX(`date`), CURDATE()) as days_remaining FROM lunch";
				$days_remaining_result = mysqli_query($con, $days_remaining_query) or die(mysqli_error($con));
				$days_remaining = mysqli_fetch_assoc($days_remaining_result);
				$days_remaining = $days_remaining['days_remaining'];
				
				//output messages to the user of different levels of significance depending on how much menu data is remaining
				//include a link to the Upload Menu page to upload more menu
				if($days_remaining < 0){
					echo "<div class='alert alert-danger'><strong>Danger!</strong> No more menu uploaded. To upload more days of menu go to the <a href='./uploadMenu.php' class='text-danger'>Upload Menu</a> page.</div>";
				} elseif ($days_remaining < 7) {
					echo "<div class='alert alert-warning'><strong>Warning!</strong> Less than a week of menu remaining. To upload more days of menu go to the <a href='./uploadMenu.php' class='text-warning'>Upload Menu</a> page.</div>";
				}
			?>

			<h3>Attendance</h3>
			<!-- Bootstrap table component to hold the attendance data -->
			<table class="table">
				<?php
					$date_string = date('Y-m-d');	//get todays date

					//get the confirmed attendance for each year group, the total confirmed attendance and the attendance for each meal today
					$attendance_query = "SELECT shell, remove, fifth, LVI, UVI, shell+remove+fifth+LVI+UVI as total_confirmed_supper_attendance, actual_breakfast, actual_lunch, actual_supper FROM attendance WHERE `date` = '$date_string'";
					$attendance_result = mysqli_query($con, $attendance_query) or die(mysqli_error($con));
					
					//if there is data for today
					if(mysqli_num_rows($attendance_result)){
						//get the data and output the data as a table
						$attendance_row = mysqli_fetch_assoc($attendance_result);
						echo "<thead><tr>";
						foreach($attendance_row as $key => $value){
							$title = ucwords(str_replace("_", " ", $key));
							echo "<th>$title</th>";
						}
						echo "</tr></thead><tbody><tr>";
						foreach($attendance_row as $key => $value){
							echo "<td>$value</tds>";
						}
						echo "</tr></tbody>";
					} else {
						//otherwise say there is no more data left
						echo "<h4>No attendance data yet for today</h4>";
					}
					
				?>
			</table>
			<a href="./attendance.php">Analyse attendance in detail</a>

			<h3>Menu</h3>
			<div class="panel panel-default menu-panel">
				<div class="panel-heading">Lunch Menu</div>
				<div class="menu-panel-content">
					<dl>
						<?php
							//query to get the lunch menu for today
							$lunch_menu_query = "SELECT
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
							WHERE `date` = '$date_string'";

							//query to get the likes and dislikes for the lunch today
							$lunch_votes_query = "SELECT
							(SELECT likes FROM vote WHERE item_id = soup_id AND `date` = '$date_string') AS soup_likes, 
							(SELECT likes FROM vote WHERE item_id = main_meat_id AND `date` = '$date_string') AS main_meat_likes, 
							(SELECT likes FROM vote WHERE item_id = main_fish_id AND `date` = '$date_string') AS main_fish_likes, 
							(SELECT likes FROM vote WHERE item_id = main_vegetarian_id AND `date` = '$date_string') AS main_vegetarian_likes, 
							(SELECT likes FROM vote WHERE item_id = potato_id AND `date` = '$date_string') AS potato_likes, 
							(SELECT likes FROM vote WHERE item_id = veg_1_id AND `date` = '$date_string') AS veg_1_likes, 
							(SELECT likes FROM vote WHERE item_id = veg_2_id AND `date` = '$date_string') AS veg_2_likes, 
							(SELECT likes FROM vote WHERE item_id = veg_3_id AND `date` = '$date_string') AS veg_3_likes, 
							(SELECT likes FROM vote WHERE item_id = alternative_id AND `date` = '$date_string') AS alternative_likes, 
							(SELECT likes FROM vote WHERE item_id = sauce_1_id AND `date` = '$date_string') AS sauce_1_likes, 
							(SELECT likes FROM vote WHERE item_id = sauce_2_id AND `date` = '$date_string') AS sauce_2_likes, 
							(SELECT likes FROM vote WHERE item_id = dessert_id AND `date` = '$date_string') AS dessert_likes,

							(SELECT dislikes FROM vote WHERE item_id = soup_id AND `date` = '$date_string') AS soup_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = main_meat_id AND `date` = '$date_string') AS main_meat_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = main_fish_id AND `date` = '$date_string') AS main_fish_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = main_vegetarian_id AND `date` = '$date_string') AS main_vegetarian_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = potato_id AND `date` = '$date_string') AS potato_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = veg_1_id AND `date` = '$date_string') AS veg_1_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = veg_2_id AND `date` = '$date_string') AS veg_2_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = veg_3_id AND `date` = '$date_string') AS veg_3_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = alternative_id AND `date` = '$date_string') AS alternative_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = sauce_1_id AND `date` = '$date_string') AS sauce_1_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = sauce_2_id AND `date` = '$date_string') AS sauce_2_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = dessert_id AND `date` = '$date_string') AS dessert_dislikes

							FROM lunch 
							WHERE `date` = '$date_string'";

							//execute queries
							$lunch_menu_result = mysqli_query($con, $lunch_menu_query) or die(mysqli_error($con));
							$lunch_votes_result = mysqli_query($con, $lunch_votes_query) or die(mysqli_error($con));

							//get data
							$lunch_menu = mysqli_fetch_assoc($lunch_menu_result);
							$lunch_votes = mysqli_fetch_assoc($lunch_votes_result);
							
							//if there was some data
							if(count($lunch_menu) > 0){
								//for each item, output a descriptive list with a title and description taken from the data
								foreach ($lunch_menu as $key => $value) {
									$item_name = ucwords(str_replace("_", " ", $key));
									$item_likes = $lunch_votes[$key."_likes"];
									$item_dislikes = $lunch_votes[$key."_dislikes"];
									//title of item as item type with labels for the likes and dislikes as taken from the votes result
									echo "<dt>$item_name <span class='label label-success'>$item_likes</span> <span class='label label-danger'>$item_dislikes</span></dt>";
									echo "<dd>$value</dd>";
								}
								echo "</dl><a href='./editMenu.php?date=$date_string'>Edit</a>";	//link to edit the menu
							} else {
								//if there is no data, notify the user
								echo "<div class='alert alert-info' style='margin: 10px; max-width: 600px'><strong>Heads up!</strong> No lunch data today.</div>";
							}
						?>
				</div>
			</div>

			<div class="panel panel-default menu-panel">
				<div class="panel-heading">Supper Menu</div>
				<div class="menu-panel-content">
					<dl>
						<?php
							//query to get the lunch menu for today
							$supper_menu_query = "SELECT 
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
							WHERE `date` = '$date_string'";

							//query to get the likes and dislikes for the lunch today
							$supper_votes_query = "SELECT 
							(SELECT likes FROM vote WHERE item_id = soup_id AND `date` = '$date_string') AS soup_likes, 
							(SELECT likes FROM vote WHERE item_id = main_meat_id AND `date` = '$date_string') AS main_meat_likes, 
							(SELECT likes FROM vote WHERE item_id = main_fish_id AND `date` = '$date_string') AS main_fish_likes, 
							(SELECT likes FROM vote WHERE item_id = main_vegetarian_id AND `date` = '$date_string') AS main_vegetarian_likes, 
							(SELECT likes FROM vote WHERE item_id = staple_id AND `date` = '$date_string') AS staple_likes, 
							(SELECT likes FROM vote WHERE item_id = veg_1_id AND `date` = '$date_string') AS veg_1_likes, 
							(SELECT likes FROM vote WHERE item_id = veg_2_id AND `date` = '$date_string') AS veg_2_likes, 
							(SELECT likes FROM vote WHERE item_id = sauce_1_id AND `date` = '$date_string') AS sauce_1_likes, 
							(SELECT likes FROM vote WHERE item_id = sauce_2_id AND `date` = '$date_string') AS sauce_2_likes,
							(SELECT likes FROM vote WHERE item_id = dessert_id AND `date` = '$date_string') AS dessert_likes,

							(SELECT dislikes FROM vote WHERE item_id = soup_id AND `date` = '$date_string') AS soup_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = main_meat_id AND `date` = '$date_string') AS main_meat_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = main_fish_id AND `date` = '$date_string') AS main_fish_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = main_vegetarian_id AND `date` = '$date_string') AS main_vegetarian_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = staple_id AND `date` = '$date_string') AS staple_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = veg_1_id AND `date` = '$date_string') AS veg_1_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = veg_2_id AND `date` = '$date_string') AS veg_2_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = sauce_1_id AND `date` = '$date_string') AS sauce_1_dislikes, 
							(SELECT dislikes FROM vote WHERE item_id = sauce_2_id AND `date` = '$date_string') AS sauce_2_dislikes,
							(SELECT dislikes FROM vote WHERE item_id = dessert_id AND `date` = '$date_string') AS dessert_dislikes
							FROM supper 
							WHERE `date` = '$date_string'";

							//echo $supper_votes_query;

							//execute queries
							$supper_result = mysqli_query($con, $supper_menu_query) or die(mysqli_error($con));
							$supper_votes_result = mysqli_query($con, $supper_votes_query) or die(mysqli_error($con));

							//get data
							$supper_menu = mysqli_fetch_assoc($supper_result);
							$supper_votes = mysqli_fetch_assoc($supper_votes_result);

							//if there was some data
							if(count($supper_menu) > 0){
								//for each item, output a descriptive list with a title and description taken from the data
								foreach ($supper_menu as $key => $value) {
									$item_name = ucwords(str_replace("_", " ", $key));
									$item_likes = $supper_votes[$key."_likes"];
									$item_dislikes = $supper_votes[$key."_dislikes"];
									//title of item as item type with labels for the likes and dislikes as taken from the votes result
									echo "<dt>$item_name <span class='label label-success'>$item_likes</span> <span class='label label-danger'>$item_dislikes</span></dt>";
									echo "<dd>$value</dd>";
								}
								echo "</dl><a href='./editMenu.php?date=$date_string'>Edit</a>";	//link to edit the menu
							} else {
								//if there is no data, notify the user
								echo "<div class='alert alert-info' style='margin: 10px; max-width: 600px'><strong>Heads up!</strong> No supper data today.</div>";
							}
							mysqli_close($con);	//close connection
						?>
				</div>
			</div>

			<!-- link to view the menu for the whole week -->
			<a href="./viewMenu.php" style="clear: both; float: left; margin-left: 5px;">View whole week menu</a>			

			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>