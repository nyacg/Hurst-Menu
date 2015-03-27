<?php 
	session_start();	//connect to users session on the server
	include "connectlocal.php";	//connect to db

	//get form data
	$email = $_REQUEST['email'];
	$pass = $_REQUEST['password'];

	//get correct page to redirect to
	$page = "/Hurst%20Menu/dashboard.php";
	if(isset($_REQUEST['page'])){
		$page = $_REQUEST['page'];
	}

	//ensure it is a valid email address to avoid SQL injection
	if(filter_var($email, FILTER_VALIDATE_EMAIL)){
		//query to get the users that match the email and password
		$query = "SELECT * FROM user WHERE email_address='$email' AND password='$pass'";
		$result = mysqli_query($con, $query);	//execute query

		//get the number of matches 
		$count = mysqli_num_rows($result);

		//if correct details then there should only be one match
		if($count == 1){
			//set the 'in' session as the users email and destroy any fail sessions
			$_SESSION["in"] = strtolower($email);
			if(isset($_SESSION['login_failed'])) {
				unset($_SESSION['login_failed']);
			}
			//redirect the user to the page specified
			header("location: " . $page);
		} else {
			//otherwise set the fail session and redirect back to the login page
			$_SESSION["login_failed"] = true;
			header("location: /Hurst Menu/login.php");
		}
	} else {
		//otherwise set the fail session and redirect back to the login page
		$_SESSION["login_failed"] = true;
		header("location: /Hurst Menu/login.php");
	}
	mysqli_close($con);	//close connection to db
	
?>