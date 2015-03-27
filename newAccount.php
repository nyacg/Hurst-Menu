<?php include "./res/php/checkLogin.php"; ?>

<!DOCTYPE HTML>

<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Create Account - Hurst Menu</title>
		
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>
		<div class="page">

			<div class="page-header">
				<h1>New Account</h1>
			</div>

			<?php
				//if there are any errors
				if(isset($_SESSION['new_account_errors'])){
					//get the error messages without the last ', ' as a variable
					$error_message = $_SESSION['new_account_errors'];
					$error_message = rtrim($error_message, ", ");
					//show these error messages in a message box
					echo "<div class='alert alert-warning'><strong>Heads up!</strong> Form completed incorrectly: $error_message.</div>";
					unset($_SESSION['new_account_errors']);	//unset the session so message won't appear when the page is refreshed
				} elseif (isset($_SESSION['new_account_success'])){
					//if the account was created successfully show a success message with the account details
					$details = $_SESSION['new_account_success'];
					echo "<div class='alert alert-success'><strong>Success!</strong> Account created completed successfully. $details</div>";
					unset($_SESSION['new_account_success']);	//unset the session so message won't appear when the page is refreshed
				}
			?>

			<!-- for to input the account details -->
			<form action="./res/php/processNewAccount.php" method="post" autocomplete="off">
				<label for="email">Email</label>
				<div class="input-group menu-delete-input">
					<span class="input-group-addon glyphicon glyphicon-envelope"></span>
					<input type="text" class="form-control glyph-input" name="email" />
				</div>

				<label for="pass">Password</label>
				<div class="input-group menu-delete-input">
					<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
					<input type="text" class="form-control glyph-input" name="pass" />
				</div>

				<label for="verify-pass">Repeat Password</label>
				<div class="input-group menu-delete-input">
					<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
					<input type="text" class="form-control glyph-input" name="verify-pass" />
				</div>

				<!-- a Bootstrap-Switch switch component -->
				<label for="emails" id="emails-label">Receive emails </label><input type="checkbox" name="emails" class="switch" checked />

				<br>

				<button type="submit" class="btn btn-primary" name="submit">Create Account</button>
			</form>

			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>