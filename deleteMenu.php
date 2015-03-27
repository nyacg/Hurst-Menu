<?php 
	include "./res/php/checkLogin.php";		//check user is logged in
	include "./res/php/connectlocal.php";	//connect to db
 ?>
<!DOCTYPE HTML>

<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Delete Menu - Hurst Menu</title>
		
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>

		<?php
			//get data from form
			if(isset($_POST['start_date'])){
				$start_date = $_POST['start_date'];
			} else {
				$start_date = "";
			}

			if(isset($_POST['end_date'])){
				$end_date = $_POST['end_date'];
			} else {
				$end_date = "";
			}

			//initalise variable
			$delete_query_addition = "";
			$delete_success_addition = "";

			//decide what the query shoould do
			if($start_date != "" or $end_date != ""){
				if($start_date != "" and $end_date != ""){
					//if both the first and last date is set then delete the menu between these dates (inclusive)
					$delete_query_addition = "`date` BETWEEN '$start_date' AND '$end_date'";
					$delete_success_addition = "from $start_date to $end_date";
				} elseif($start_date != ""){
					//otherwise if just the first date is set the delete the menu starting from this date
					$delete_query_addition = "`date` >= '$start_date'";
					$delete_success_addition = "from $start_date";
				} else {
					//otherwise if just the last date is set the delete the menu upto this date
					$delete_query_addition = "`date` <= '$end_date'";
					$delete_success_addition = "up to $end_date";
				}
			} 

		?>

		<div class="page">
			<div class="page-header">
				<h1>Delete Menu</h1>
			</div>
		
			<!-- form for user to submit the dates -->
			<form class="left left-form" action="./deleteMenu.php" method="post">
				<label for="start_date">First Day of Menu to Delete:</label>
				<div class="input-group menu-delete-input">
					<span class="input-group-addon glyphicon glyphicon-calendar"></span>
					<input type="date" class="form-control glyph-input" name="start_date"/>
				</div>

				<label for="end_date">Last Day of Menu to Delete:</label>
				<div class="input-group menu-delete-input">
					<span class="input-group-addon glyphicon glyphicon-calendar"></span>
					<input type="date" class="form-control glyph-input" name="end_date"/>
				</div>

				<br>

				<!-- When the button is clicked, show a confirmation popup to the user to check that they want to delete the data -->
				<button type="submit" class="btn btn-lg btn-primary" onclick="return confirm('Are you sure you want to delete the menu between the selected dates?')" name="submit">Delete</button>
			</form>

			<!-- on screen help -->
			<div class="left instructions-panel">
				<div class="panel panel-primary" >
					<div class="panel-heading">Instructions</div>
					<div class="panel-body">
						<p>You can select either a first day of menu to delete, a last day of menu to delete or both.</p>
						<p>Selecting just a first day will delete all data that appears after that date.</p>
						<p>Selecting just a last day will delete all data that appears before that date.</p>
						<p>Selecting both a first day and a last day will delete all data that appears between the two dates (inclusive).</p>
						<p>No data will be deleted if the last date is before the first date.<p>
					</div>
				</div>
			</div>

			<?php
				//if the form has been submitted
				if($delete_query_addition != ""){
					//build query
					$delete_query = "DELETE FROM lunch WHERE $delete_query_addition; DELETE FROM supper WHERE $delete_query_addition;";
					//execute query or output a Bootstrap alert component with the error message and stop the execution
					$delete_result = mysqli_multi_query($con, $delete_query) or die("<div class='alert clear alert-danger delete-alert'><strong>Heads up!</strong> Delete failed, please try again. If problem persists contact the administrator.</div>");
					//output a success message
					echo "<div class='alert alert-success clear delete-alert'><strong>Success!</strong> Menu successfully deleted for dates $delete_success_addition.</div>";
				} elseif(isset($_POST['start_date']) or isset($_POST['end_date'])) {
					//if the form was submitted blank output a message to the user
					echo "<div class='alert clear alert-warning delete-alert'><strong>Heads up!</strong> Please select a first day and/or a last day of menu to delete.</div>";
				}
			?>

			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>