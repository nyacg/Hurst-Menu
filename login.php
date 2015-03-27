<?php 
	session_start();	//connect to users session on the server

	$page = "/Hurst%20Menu/dashboard.php";	//initalise the page to the default
	//if another page has been
	if(isset($_REQUEST['page'])){
		$page = $_REQUEST['page'];
	}
?>

<!DOCTYPE HTML>

<html lang='en'>
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Login - Hurst Menu</title>
	</head>

	<body>
		<div class="page">
			<div class="page-header">
				<h1>Hurst Menu <small>Login</small></h1>
			</div>

			<br>

			<!-- form for username and password -->
			<form action='./res/php/processLogin.php' method='post'>
				<!-- hidden input to hold the redirect page -->
				<input type="hidden" name="page" class="form-control login-input" value="<?php echo $page ?>"/>
				<div class="input-group" style="max-width: 400px">
						<span class="input-group-addon glyphicon glyphicon-envelope"></span>
						<input type="text" name="email" class="form-control glyph-input" placeholder="Email"/>
				</div>
				<br>
				<div class="input-group" style="max-width: 400px">
						<span class="input-group-addon glyphicon glyphicon-asterisk"></span>
						<input type="password" name="password" class="form-control glyph-input" placeholder="Password"/>
						<span class="input-group-btn">
							<button class="btn btn-default" id="login-button" type="submit">Login</button>
						</span>
				</div>
			</form>

			<?php
				//if the login failed
				if(isset($_SESSION['login_failed'])){
					unset($_SESSION['login_failed']);	//remove the session
					//notify the user in the form of an error message
					echo "<div class='alert alert-danger login-alert' id='login-failed'><strong>Login failed!</strong> Check email and password then try again.</div>";
				} elseif(isset($_SESSION['in'])){
					//if the login session is set
					unset($_SESSION['in']);	//remove the session (logout the user)
					//notify the user that they were logged out successfully
					echo "<div class='alert alert-info login-alert'>Logout successful.</div>";
				} 
			?>

			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>