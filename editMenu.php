<?php 
	include "./res/php/checkLogin.php";
	include "./res/php/connectlocal.php";	//connect to db
 ?>
<!DOCTYPE HTML>

<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Edit Menu - Hurst Menu</title>
		
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>

		<div class="page">
			<div class="page-header">
				<h1>Edit Menu</h1>
			</div>
			<?php
				//get the date from the URL as the database Date format
				if(isset($_GET['date'])){
					$date_string = $_GET['date'];
					if($date_string == ""){
						//if it has not been specified, get the date as todays date
						$date_string = date("Y-m-d");
					}
				} else {
					//if it has not been specified, get the date as todays date
					$date_string = date("Y-m-d");
				}
				$date = strtotime($date_string);	//get date as time stamp
				//get the nicely formatted readable date e.g. 20th October 2014
				$menu_date_nice = date("l jS F Y", $date);
				//get the standard dates for the next and previous days
				$previous_day_date_string = date("Y-m-d", strtotime("$date_string -1 day"));
				$next_day_date_string = date("Y-m-d", strtotime("$date_string +1 day"));
				$today = strtotime("today");	//get time stamp for today

				//work out the displacement in weeks from today
				$day_of_date = date("N", $date);	//day of week today
				//date of the monday of the week that the menu appears in
				$monday_date = strtotime("$date_string +" . (1 - $day_of_date) . " days");
				$date_difference = $monday_date - $today;	//time stamp difference in dates
				//convert time stamp (in seconds) to week displacement from today
				$week_difference = ceil($date_difference / (7*24*60*60));
				//echo $week_difference;

				//decide which error messages to show and reset the session so they don't appear again until the 
				//form is submitted again
				if(isset($_SESSION['edit_okay'])){
					if($_SESSION['edit_okay'] == true){
 					echo "<div class='alert alert-success'><strong>Success!</strong> Menu changes completed successfully.</div>";
					} else {
						echo "<div class='alert alert-danger'><strong>Error!</strong> Something went wrong processing the update, please try again. If the problem persists contact administrator via the help section.</div>";

					}
					unset($_SESSION['edit_okay']);
				}
			?>

			<h3>Menu for <?php echo $menu_date_nice; ?></h3>	<!-- show the nicely formatted date as a heading -->
			<!-- provide a link to view the whole menu using the week difference calculated as the URL variable -->
			<a href="./viewMenu.php?displacement=<?php echo $week_difference ?>">View whole week menu</a>
			
			<!-- form to hold the items as input boxes, submit to processMenuUpdate.php -->
			<form action="./res/php/processMenuUpdate.php" method="post" id="edit-menu-form">
				<div class="change-week-buttons">
					<!-- left and right buttons to change the day of menu shown to the previous and next days respectively -->
					<button type="button" id="previous-week" class="btn btn-default glyphicon glyphicon-chevron-left"  onclick="location.href='./editMenu.php?date=<?php echo $previous_day_date_string; ?>'"></button>
					Change Day
					<button type="button" id="next-week" class="btn btn-default glyphicon glyphicon-chevron-right" onclick="location.href='./editMenu.php?date=<?php echo $next_day_date_string; ?>'"></button>
					<button type="submit" id="save-menu-changes" class="btn btn-default">Save Changes</button>
				</div>

				<!-- Bootstap's panel components are used to hold the lunch and supper menus side by side -->
				<div class="panel panel-default menu-panel">
					<div class="panel-heading">Lunch Menu</div>
					<div class="menu-panel-content">
						<!-- hold the date in the form so it sent when the form is submitted but there is no 
						need to show it to the user as they can see the nicely formatted date -->
						<input type="hidden" name="date" value="<?php echo $date_string; ?>" />
						<?php
							//query to get the lunch menu data from the db (similar to getMenu.php)
							$lunch_query = "SELECT
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

							//echo $lunch_query;

							//execute query and get result
							$lunch_result = mysqli_query($con, $lunch_query) or die(mysqli_error($con));
							$lunch_menu = mysqli_fetch_assoc($lunch_result);
							//var_dump($lunch_menu);

							//if there was data in the database for that date
							if(count($lunch_menu) > 0){
								//loop through result array outputting a new label and input box for each item
								foreach ($lunch_menu as $key => $value) {
									$item = str_replace(" ", "_", $value);	//so it can be used as a name attribute
									$item_name = ucwords(str_replace("_", " ", $key));
									echo "<label for='".$key."_lunch_$item'>$item_name</label>";
									echo "<input name='".$key."_lunch_$item' 'type='text' value='$value' class='form-control'></input><br>";
								}
							} else {
								//otherwise display the message that there is no menu data for the selected date
								echo "<div class='alert alert-info' style='margin: 10px; max-width: 600px'><strong>Heads up!</strong> No lunch data for selected day.</div>";
							}
						?>
					</div>
				</div>

				<div class="panel panel-default menu-panel">
					<div class="panel-heading">Supper Menu</div>
					<div class="menu-panel-content">
						<?php
							//query to get the supper menu data from the db (similar to getMenu.php)
							$supper_query = "SELECT 
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

							//echo $supper_query;

							//execute query and get result
							$supper_result = mysqli_query($con, $supper_query) or die(mysqli_error($con));
							$supper_menu = mysqli_fetch_assoc($supper_result);
							//var_dump($supper_menu);

							//if there was data in the database for that date
							if(count($supper_menu) > 0){
								//loop through result array outputting a new label and input box for each item
								foreach ($supper_menu as $key => $value) {
									$item = str_replace(" ", "_", $value);
									$item_name = ucwords(str_replace("_", " ", $key));
									echo "<label for='".$key."_supper_$item'>$item_name</label>";
									echo "<input name='".$key."_supper_$item' 'type='text' value='$value' class='form-control'></input><br>";
								}
							} else {
								//otherwise display the message that there is no menu data for the selected date
								echo "<div class='alert alert-info' style='margin: 10px; max-width: 600px'><strong>Heads up!</strong> No supper data for selected day.</div>";
							}
							mysqli_close($con);	//close connection to db
						?>
					</div>
				</div>
			</form>
			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>