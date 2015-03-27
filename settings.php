<?php
	include "./res/php/checkLogin.php";	//check user is logged in
	include "./res/php/connectlocal.php";	//connect to db
?>
<!DOCTYPE HTML>

<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Account Settings - Hurst Menu</title>
	</head>

	<?php 
		$email = $_SESSION['in'];	//get email from logged in session

		//get user details
		$user_query = "SELECT * FROM user WHERE email_address='$email'";
		$user_result = mysqli_query($con, $user_query);
		$array = mysqli_fetch_assoc($user_result);

		mysqli_close($con); //close db connection
	?>

	<body>
		<?php include "./res/php/navBar.php" ?>
		<div class="page">
			<div class="page-header">
				<!-- page title with the users email included -->
				<h1>Account Settings <small><?php echo $email; ?></small></h1>
			</div>

			<?php
				//show the success and error messages as required
				if(isset($_SESSION['account_update_errors'])){
					$error_message = $_SESSION['account_update_errors'];
					$error_message = rtrim($error_message, ", ");
					echo "<div class='alert alert-warning'><strong>Heads up!</strong> Form completed incorrectly: $error_message.</div>";
					unset($_SESSION['account_update_errors']);
				} elseif (isset($_SESSION['account_update_success'])){
					echo "<div class='alert alert-success'><strong>Success!</strong> Update completed successfully.</div>";
					unset($_SESSION['account_update_success']);
				}
			?>
			<!-- form to hold account details, pre populated with current details -->
			<form action="./res/php/processAccountUpdate.php" method="post" autocomplete="off">
				<label for="email">Email</label>
				<div class="input-group menu-delete-input">
					<span class="input-group-addon glyphicon glyphicon-envelope"></span>
					<input type="text" class="form-control glyph-input" name="email" value="<?php echo $email; ?>"/>
				</div>

				<label for="pass">New Password</label>
				<div class="input-group menu-delete-input">
					<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
					<input type="text" class="form-control glyph-input" name="pass" value="<?php echo $array['password'] ?>"/>
				</div>

				<label for="verify-pass">Repeat New Password</label>
				<div class="input-group menu-delete-input">
					<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
					<input type="text" class="form-control glyph-input" name="verify-pass" value="<?php echo $array['password'] ?>"/>
				</div>

				<label for="emails" id="emails-label">Receive emails </label><input type="checkbox" name="emails" class="switch" <?php echo $array['receive_suggestions'] == 1 ? "checked" : "" ?>></input>

				<br>

				<button type="submit" class="btn btn-primary" name="submit">Save Changes</button>
			</form>
		</div>
		<?php include "./res/php/footer.php" ?>
	</body>
</html>