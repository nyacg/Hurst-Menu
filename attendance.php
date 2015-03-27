<?php include "./res/php/checkLogin.php" ?>

<!DOCTYPE HTML>
<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Attendance - Hurst Menu</title>
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>
		<div class="page">
			<div class="page-header">
				<h1>Attendance <small>
				</small></h1>
			</div>			

			<h3>Dates to analyse data for (from, to)</h3>
			<!-- A small form to allow the user to set the dates over which to get the data, the data is sent 
			(by GET) to this page. The date fields are filled with the dates that have been submitted so the 
			user can see the parameters that they have set -->
			<form action='./attendance.php' id="date-form" method="get">
				<?php
					$date_query_addition = "";	//initalise variable
					
					//get form data on dates
					if(isset($_GET['from-date'])){
						$from_date = $_GET['from-date'];
					} else {
						$from_date = "";
					}

					if(isset($_GET['to-date'])){
						$to_date = $_GET['to-date'];
					} else {
						$to_date = "";
					}

					//decide on the query addition based on the values of from and to dates
					if($from_date != "" or $to_date != ""){
						if($from_date != "" and $to_date != ""){
							$date_query_addition = "WHERE `date` BETWEEN '$from_date' AND '$to_date'";
						} elseif($from_date != ""){
							$date_query_addition = "WHERE `date` >= '$from_date'";
						} else{
							$date_query_addition = "WHERE `date` <= '$to_date'";
						}
					}
				?>
				<div class="input-group left" style="max-width: 259px; margin-right: 20px;">
						<span class="input-group-addon glyphicon glyphicon-calendar"></span>
						<input type="date" name="from-date" class="form-control login-input" value=<?php echo "'".$from_date."'" ?>/>
				</div>
				<div class="input-group left" style="max-width: 250px"> 
						<span class="input-group-addon glyphicon glyphicon-calendar"></span>
						<input type="date" name="to-date" class="form-control login-input" value=<?php echo "'".$to_date."'" ?>/>
						<span class="input-group-btn">
							<button class="btn btn-default" id="login-button" type="submit">Set</button>
						</span>
				</div>
			</form>

			<br>
			
			<h3>Confirmed Attendance vs. Actual Attendance for Supper Analysis</h3>
			<!-- Using Bootstrap's panel and button group components and the Bootstrap switch component to make 
			a pane where the data shown on the scatter graph can be limited by each day of the week -->
			<div class="panel panel-primary left" id="days-panel">
				<div class="panel-heading">Days To Include</div>
				<div id="days">
					<label for="monday">Monday</label><input type="checkbox" name="monday" checked class="switch"></input><br>
					<label for="tuesday">Tuesday</label><input type="checkbox" name="tuesday" checked class="switch"></input><br>
					<label for="wednesday">Wednesday</label><input type="checkbox" name="wednesday" checked class="switch"></input><br>
					<label for="thursday">Thursday</label><input type="checkbox" name="thursday" checked class="switch"></input><br>
					<label for="friday">Friday</label><input type="checkbox" name="friday" checked class="switch"></input><br>
					<label for="saturday">Saturday</label><input type="checkbox" name="saturday" class="switch"></input><br>
					<label for="sunday">Sunday</label><input type="checkbox" name="sunday" class="switch"></input><br>

					<div class="btn-group">
						<button type="button" id="weekdays" class="btn btn-default quick-set-graph">Weekdays</button>
						<button type="button" id="weekends" class="btn btn-default quick-set-graph">Weekends</button>
						<div class="btn-group">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									Day
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" id="days-dropdown">
									<li><a class="quick-set-graph">Monday</a></li>
									<li><a class="quick-set-graph">Tuesday</a></li>
									<li><a class="quick-set-graph">Wednesday</a></li>
									<li><a class="quick-set-graph">Thursday</a></li>
									<li><a class="quick-set-graph">Friday</a></li>
									<li><a class="quick-set-graph">Saturday</a></li>
									<li><a class="quick-set-graph">Sunday</a></li>
								</ul>
							</div>
					</div>
				</div>
			</div>

			<!-- placehoder div for the scatter chart, built in styling for width and height required by Google Visulization API -->
			<div id="confirmed-attendance-chart" class="left" style="width: 800px; height: 400px;"></div>

			<br>
			<h3>Actual Attendance Analysis by Week against Average</h3>
			<!-- buttons to change the week of menu data being shown on the bar graph -->
			<div id="change-week-buttons" class="change-week-buttons">
				<button type="button" id="previous-week" class="btn btn-default glyphicon glyphicon-chevron-left"></button>
				Change Week
				<button type="button" id="next-week" class="btn btn-default glyphicon glyphicon-chevron-right"></button>
			</div>
			<!-- placehoder div for the bar graph, built in styling for width and height required by Google Visulization API -->
			<div id="weeks-attendance-chart" class="left" style="width: 1000px; height: 400px;"></div>

			<?php include "./res/php/footer.php" ?>
			<?php 
				include "./res/php/connectlocal.php";	//connect to db

				//query to get the data for the scatter grah
				$confirmedAttendanceQuery = "SELECT shell + remove + fifth + LVI + UVI AS confirmed,  actual_supper AS actual, WEEKDAY(`date`) AS day, `date` 
											FROM attendance $date_query_addition ORDER BY `date` DESC LIMIT 300";
				//echo $confirmedAttendanceQuery; //for testing
				$confirmedAttendanceResult = mysqli_query($con, $confirmedAttendanceQuery) or die(mysqli_error($con));	//execute scatter graph query
				
				$confirmedAttendanceData = array(array(), array(), array(), array(), array(), array(), array());	//initialise data

				//for each row of scatter graph data query
				$dateString = date("Y-m-d");	//more efficient
				while($row = mysqli_fetch_row($confirmedAttendanceResult)){
					//decide if the data is for today
					$isToday = $row[3] == $dateString ? "Today's Confirmed Attendance" : null;
					//prepare the data in the format required by the Google Visualization API
					$confirmedAttendanceData[$row[2]][] = array($row[0], $row[1], $isToday);
				}

				//create the JSON object of the data; JSON_NUMERIC_CHECK ensures the numbers are stored as numbers, not strings
				$confirmedAttendanceJSON = json_encode($confirmedAttendanceData, JSON_NUMERIC_CHECK);
				//make the object a JavaScript varialbe so it can be used in the attendance.js script
				echo "<script type='text/javascript'> var confirmedAttendanceJSON = $confirmedAttendanceJSON </script>";

				//check if the date query addition is not blank so AND is added and WHERE 
				//is removed so that it can be added to the WHERE parameters 
				$date_query_addition = $date_query_addition != "" ? "AND ".ltrim($date_query_addition, "WHERE") : "";
				//query to get the average attendance for each meal on each day of the week between the selected dates
				$averageAttendanceQuery = "SELECT 
				(SELECT AVG(actual_breakfast) FROM attendance WHERE WEEKDAY(`date`) = 0 $date_query_addition) AS breakfast_monday, 
				(SELECT AVG(actual_lunch) FROM attendance WHERE WEEKDAY(`date`) = 0 $date_query_addition) AS lunch_monday, 
				(SELECT AVG(actual_supper) FROM attendance WHERE WEEKDAY(`date`) = 0 $date_query_addition) AS supper_monday,

				(SELECT AVG(actual_breakfast) FROM attendance WHERE WEEKDAY(`date`) = 1 $date_query_addition) AS breakfast_tuesday, 
				(SELECT AVG(actual_lunch) FROM attendance WHERE WEEKDAY(`date`) = 1 $date_query_addition) AS lunch_tuesday, 
				(SELECT AVG(actual_supper) FROM attendance WHERE WEEKDAY(`date`) = 1 $date_query_addition) AS supper_tuesday,

				(SELECT AVG(actual_breakfast) FROM attendance WHERE WEEKDAY(`date`) = 2 $date_query_addition) AS breakfast_wednesday, 
				(SELECT AVG(actual_lunch) FROM attendance WHERE WEEKDAY(`date`) = 2 $date_query_addition) AS lunch_wednesday, 
				(SELECT AVG(actual_supper) FROM attendance WHERE WEEKDAY(`date`) = 2 $date_query_addition) AS supper_wednesday,

				(SELECT AVG(actual_breakfast) FROM attendance WHERE WEEKDAY(`date`) = 3 $date_query_addition) AS breakfast_thursday, 
				(SELECT AVG(actual_lunch) FROM attendance WHERE WEEKDAY(`date`) = 3 $date_query_addition) AS lunch_thursday, 
				(SELECT AVG(actual_supper) FROM attendance WHERE WEEKDAY(`date`) = 3 $date_query_addition) AS supper_thursday,

				(SELECT AVG(actual_breakfast) FROM attendance WHERE WEEKDAY(`date`) = 4 $date_query_addition) AS breakfast_friday, 
				(SELECT AVG(actual_lunch) FROM attendance WHERE WEEKDAY(`date`) = 4 $date_query_addition) AS lunch_friday, 
				(SELECT AVG(actual_supper) FROM attendance WHERE WEEKDAY(`date`) = 4 $date_query_addition) AS supper_friday,

				(SELECT AVG(actual_breakfast) FROM attendance WHERE WEEKDAY(`date`) = 5 $date_query_addition) AS breakfast_saturday, 
				(SELECT AVG(actual_lunch) FROM attendance WHERE WEEKDAY(`date`) = 5 $date_query_addition) AS lunch_saturday, 
				(SELECT AVG(actual_supper) FROM attendance WHERE WEEKDAY(`date`) = 5 $date_query_addition) AS supper_saturday,

				(SELECT AVG(actual_breakfast) FROM attendance WHERE WEEKDAY(`date`) = 6 $date_query_addition) AS breakfast_sunday, 
				(SELECT AVG(actual_lunch) FROM attendance WHERE WEEKDAY(`date`) = 6 $date_query_addition) AS lunch_sunday, 
				(SELECT AVG(actual_supper) FROM attendance WHERE WEEKDAY(`date`) = 6 $date_query_addition) AS supper_sunday
				";
				//echo $averageAttendanceQuery;

				$averageAttendanceResult = mysqli_query($con, $averageAttendanceQuery) or die(mysqli_error($con));	//execute query
				$averageAttendanceData = mysqli_fetch_assoc($averageAttendanceResult);	//get the data returned by the query
				//convert the data into a JavaScript object, numbers are kept as numbers
				$averageAttendanceJSON = json_encode($averageAttendanceData,  JSON_NUMERIC_CHECK);	
				//output the data as a JavaScript variable so it can be proceesed and graphed
				echo "<script type='text/javascript'> var averageAttendanceJSON = $averageAttendanceJSON </script>";
			?>
			<!-- include the JavaScript files for the Google API Loader and the script to control the page -->
			<script type="text/javascript" src="http://www.google.com/jsapi"></script>
			<script type="text/javascript" src="./res/js/attendance.js"></script>
		</div>
	</body>
</html>