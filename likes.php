<?php 
	include "./res/php/checkLogin.php";		//ensure user is logged in 
	include "./res/php/connectlocal.php";	//connect to db
?>
<!DOCTYPE HTML>

<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Likes - Hurst Menu</title>
		
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>

		<div class="page">

			<div class="page-header">
				<h1>Likes</h1>
			</div>

			<?php
				$date_query_addition = "";	//initalise variable
				//get form data on dates
				if(isset($_POST['from-date'])){
					$from_date = $_POST['from-date'];
				} else {
					$from_date = "";
				}

				if(isset($_POST['to-date'])){
					$to_date = $_POST['to-date'];
				} else {
					$to_date = "";
				}

				//decide in the query addition based on the values of from and to dates
				if($from_date != "" or $to_date != ""){
					if($from_date != "" and $to_date != ""){
						$date_query_addition = "`date` BETWEEN '$from_date' AND '$to_date'";
					} elseif($from_date != ""){
						$date_query_addition = "`date` >= '$from_date'";
					} else{
						$date_query_addition = "`date` <= '$to_date'";
					}
				}

				//get form data on item
				if(isset($_POST['item'])){
					$item = $_POST['item'];
				} else {
					$item = "all";
				}

				$and = "";	//initalise variable

				//decide if the AND is required in the query
				if($date_query_addition != ""){
					$and = "AND ";
				}

				//get form data for day
				if(isset($_POST['day'])){
					$day = $_POST['day'];
				} else {
					$day = "all";
				}
				//decide on the query part for he day
				if($day == "weekday"){
					$day_query_addition = $and . "WEEKDAY(`date`) < 5";
				} elseif($day == "weekend"){
					$day_query_addition = $and . "WEEKDAY(`date`) > 4";
				} elseif(is_numeric($day)) {
					$day_int = intval($day);
					$day_query_addition = $and . "WEEKDAY(`date`) = $day_int";
				} else {
					$day_query_addition = "";
				}

				//if at least one of the date or day query additions are not blank the the 'AND' will be required in the query
				if($date_query_addition != "" || $day_query_addition != ""){
					$and = "AND ";
				}

				//create the item query addtion 
				$item_query_addition = $and . "item_type = '$item'";
				if($item == "all"){
					$item_query_addition = "";
				}

				//get form data for sort
				if(isset($_POST['sort'])){
					$sort = $_POST['sort'];
				} else {
					$sort = "DESC";
				}

				//creare the query addition to sort the data
				$sort_query_addition = " ORDER BY score $sort";
			?>

			<!-- Form used to constrain the data, it uses Bootstrap's 'form-inline' class to have the form spread 
			horizontally across the page. It also uses php to set the data in the form to the data just submitted 
			so the user can see what they just submitted and make modifications to one field without having to set
			every single field to do so. -->
			<form action="./likes.php" method="post" class="form-inline" id="likes-form" role="form">
				<label for="from-date">From:</label>
				<div class="form-group">
					<div class="input-group likes-input">
						<span class="input-group-addon glyphicon glyphicon-calendar"></span>
						<input type="date" class="form-control glyph-input" name="from-date" value="<?php echo $from_date ?>"/>
					</div>
				</div>

				<label for="to-date">To:</label>
				<div class="form-group">
					<div class="input-group likes-input">
						<span class="input-group-addon glyphicon glyphicon-calendar"></span>
						<input type="date" class="form-control glyph-input" name="to-date" value="<?php echo $to_date ?>"/>
					</div>
				</div>

				<div class="form-group">
					<label for="item">Item:</label>
					<select name="item" class="form-control likes-input">
						<option value="all" <?php echo ($item == "all" ? "selected" : "") ?>>All Item Types</option>
						<option value="soup" <?php echo ($item == "soup" ? "selected" : "") ?>>Soup</option>
						<option value="main_meat" <?php echo ($item == "main_meat" ? "selected" : "") ?>>Main Meat Choices</option>
						<option value="main_fish" <?php echo ($item == "main_fish" ? "selected" : "") ?>>Main Fish Choices</option>
						<option value="main_vegetarian" <?php echo ($item == "main_vegetarian" ? "selected" : "") ?>>Main Vegetarian</option>
						<option value="potato" <?php echo ($item == "potato" ? "selected" : "") ?>>Potato</option>
						<option value="staple" <?php echo ($item == "staple" ? "selected" : "") ?>>Staple</option>
						<option value="veg" <?php echo ($item == "veg" ? "selected" : "") ?>>Vegetables</option>
						<option value="sauce" <?php echo ($item == "sauce" ? "selected" : "") ?>>Alternative Option or Pasta Sauce</option>
						<option value="dessert" <?php echo ($item == "dessert" ? "selected" : "") ?>>Dessert</option>
						<option value="sunday" <?php echo ($item == "sunday" ? "selected" : "") ?>>Sunday Items</option>
					</select>
				</div>

				<div class="form-group">
					<label for="day">Day:</label>
					<select name="day" class="form-control likes-input">
						<option value="all" <?php echo ($day == "all" ? "selected" : "") ?>>Every Day</option>
						<option value="weekday" <?php echo ($day == "weekday" ? "selected" : "") ?>>Weekdays</option>
						<option value="weekend" <?php echo ($day == "weekend" ? "selected" : "") ?>>Weekends</option>
						<option value="0" <?php echo ($day == "0" ? "selected" : "") ?>>Mondays</option>
						<option value="1" <?php echo ($day == "1" ? "selected" : "") ?>>Tuesday</option>
						<option value="2" <?php echo ($day == "2" ? "selected" : "") ?>>Wednesday</option>
						<option value="3" <?php echo ($day == "3" ? "selected" : "") ?>>Thursday</option>
						<option value="4" <?php echo ($day == "4" ? "selected" : "") ?>>Friday</option>
						<option value="5" <?php echo ($day == "5" ? "selected" : "") ?>>Saturday</option>
						<option value="6" <?php echo ($day == "6" ? "selected" : "") ?>>Sunday</option>
					</select>
				</div>

				<div class="form-group">
					<label for="sort">Sort:</label>
					<select name="sort" class="form-control likes-input">
						<option value="DESC" <?php echo ($sort == "DESC" ? "selected" : "") ?>>Highest Score</option>
						<option value="ASC" <?php echo ($sort == "ASC" ? "selected" : "") ?>>Lowest Score</option>
					</select>
				</div>

				<div class="form-group">
					<button type="submit" class="btn btn-primary" id="submit_likes_parameters" name="submit">Go</button>
				</div>
			</form>

			<div id="likes-result">
				<?php 
					//php code to make table
					//joining all the query additions together
					$query_additon = "$date_query_addition $day_query_addition $item_query_addition";
					$query_additon = trim($query_additon, " AND"); //remove preceding and trailing ANDs
					//prepare entire query for voting data (using MySQL to do maths with 'likes-dislikes AS score') and join to get item name from the item ID supplied
					$likes_query = "SELECT (SELECT item_name FROM item WHERE item_id = vote.item_id) AS item_name, SUM(likes) as likes, SUM(dislikes) as dislikes, SUM(likes-dislikes) AS score
					FROM vote 
					WHERE $query_additon";
					$likes_query = rtrim($likes_query, "WHERE ");	//removing trailing WHERE (would  cause error if there was an empty query addition)
					//add GROUP BY so items are linked together and finally add the sort query addition
					$likes_query .= " GROUP BY item_name" . $sort_query_addition;
					//echo $likes_query;
					$likes_result = mysqli_query($con, $likes_query);	//execute the query for voting data

					//if the query was sucessfully executed
					if($likes_result){
						//if there was at least one row returned
						if(mysqli_num_rows($likes_result)){
							//output start of table (using Bootstrap's table component)
							echo "<table class='table table-striped'>
								<thead>
									<tr>
										<th>Item Name</th>
										<th>Likes</th>
										<th>Dislikes</th>
										<th>Score</th>
									</tr>
								</thead>
								<tbody>";
							//for each row in query result
							while($row = mysqli_fetch_assoc($likes_result)){
								//output a row in the table
								echo "<tr>";
								echo "<td>".$row['item_name']."</td>";
								echo "<td>".$row['likes']."</td>";
								echo "<td>".$row['dislikes']."</td>";
								echo "<td>".$row['score']."</td>";
								echo "</tr>";
							}
							//finish off the table
							echo "</tbody></table>";
						} else {
							//if 0 rows then output a message notifying user of no menu data
							echo "<div class='alert alert-info'><strong>Heads up!</strong> No voting data for selected parameters.</div>";
						} 
					} else {
						//otherwise if the query failed, warn the user of this
						echo "<div class='alert alert-warning'><strong>Heads up!</strong> Getting likes data failed, please try again. If problem persists contact administrator for help.</div>";
					}
					mysqli_close($con);		//close connection to db
				?>
			</div>

			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>