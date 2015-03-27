<?php
	include "checkLogin.php";	//ensure user is logged in
	include "connectlocal.php";	//connect to db
	
	//get email address for new user
	$new_email = $_POST['email'];

	//work out whether the user should be recieving emails and get is at a boolean 
	$emails = isset($_POST['emails']) ? $_POST['emails'] : "off";
	$emails = $emails == "on" ? 1 : 0;

	//get the password and repeated password as variables
	$pass = $_POST['pass'];
	$verify_pass = $_POST['verify-pass'];

	//initalise the errors session and query addition variables
	$_SESSION['new_account_errors'] = "";
	$pass_query_addition = "";
	$email_query_addition = "";

	//validate password is at least 5 characters
	if(strlen($pass) >= 5){
		//validate passwords match
		if($pass == $verify_pass){
			//escape the password so it can be inserted into the db
			$safe_pass = mysqli_real_escape_string($con, $pass);
			$pass_query_addition = ", password = '$safe_pass'";
		} else {
			//if passwords do not match then add this error to the errors session
			$_SESSION['new_account_errors'] .= "New passwords do not match, ";
		}
	} else {
		//if the passwords are less than 5 characters then add this to the errors session
		$_SESSION['new_account_errors'] .= "Password must be at least 5 characters, ";
	}

	//check the email is a valid email
	if(filter_var($new_email, FILTER_VALIDATE_EMAIL) && $new_email != ""){
		//see if the email address already exists in the db
		$check_email_existance_query = "SELECT COUNT(*) as count FROM user WHERE email_address = '$new_email'";
		$check_email_existance_result = mysqli_query($con, $check_email_existance_query) or die(mysqli_error($con));
		$new_email_existace_rows = mysqli_fetch_assoc($check_email_existance_result);
		if($new_email_existace_rows['count'] != 0){
			//error message as already exists
			$_SESSION['new_account_errors'] .= "Email address already registered, ";
		} 
	} else {
		//add the invalid email error to the list of errors
		$_SESSION['new_account_errors'] .= "Invalid new email address, ";
	}

	//if there were no errors
	if($_SESSION['new_account_errors'] == ""){
		//add the user to the db
		$new_user_query = "INSERT INTO user (email_address, password, receive_suggestions) VALUES ('$new_email', '$pass', $emails)";
		//echo $new_user_query;
		$new_user_result = mysqli_query($con, $new_user_query) or die(mysqli_error($con));
		unset($_SESSION['new_account_errors']);	//unset the errors session

		//add the account details to the success session
		$_SESSION['new_account_success'] = "Email: $new_email, Password: $pass, " . ($emails ? "R" : "Not r") . "ecieving emails.";
	}

	mysqli_close($con);	//close connection to db
	header("location: /Hurst Menu/newAccount.php");	//take the user back to the create account page
?>