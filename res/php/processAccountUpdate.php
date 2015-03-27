<?php
	include "checkLogin.php";	//check user is logged in
	include "connectlocal.php";	//connect to db

	$email = $_SESSION['in'];	//get the current users current email address from their session
	$new_email = $_POST['email'];	//get the new email from the form

	//work out if the user wishes to recieve emails and get this as a boolean
	$emails = isset($_POST['emails']) ? $_POST['emails'] : "off";
	$emails = $emails == "on" ? 1 : 0;
	//echo $emails;
	
	//get the password and the repeated password from the form
	$pass = $_POST['pass'];
	$verify_pass = $_POST['verify-pass'];

	//initalise the errors session
	$_SESSION['account_update_errors'] = "";

	//initalise the query additions 
	$pass_query_addition = "";
	$email_query_addition = "";

	//validate password is at least 5 characters 
	if(strlen($pass) >= 5){
		//validate passwords match
		if($pass == $verify_pass){
			//escape password for db entry
			$safe_pass = mysqli_real_escape_string($con, $pass);
			$pass_query_addition = ", password = '$safe_pass'";
		} else {
			//add password not match error to the set of errors
			$_SESSION['account_update_errors'] .= "New passwords do not match, ";
		}
	} else {
		//add password length error to list of errors
		$_SESSION['account_update_errors'] .= "Password must be at least 5 characters, ";
	}

	//if the email address in the form differs from the users current email
	if($new_email != $email){
		//validate new email addres
		if(filter_var($new_email, FILTER_VALIDATE_EMAIL)){
			//check if email address is already in db
			$check_email_existance_query = "SELECT COUNT(*) as count FROM user WHERE email_address = '$new_email'";
			$check_email_existance_result = mysqli_query($con, $check_email_existance_query) or die(mysqli_error($con));
			$new_email_existace_rows = mysqli_fetch_assoc($check_email_existance_result);
			
			//if it is not currently in db
			if($new_email_existace_rows['count'] != 0){
				//error message as already exists
				$_SESSION['account_update_errors'] .= "Email address already registered, ";
			} else {
				$email_query_addition = ", email_address = '$new_email' ";
			}
		} else {
			//add invalid email address error to list of errors
			$_SESSION['account_update_errors'] .= "Invalid new email address, ";
		}
	}

	//if there were no errors
	if($_SESSION['account_update_errors'] == ""){
		//update the user on the database
		$update_user_query = "UPDATE user SET receive_suggestions = $emails$email_query_addition$pass_query_addition WHERE email_address = '$email'";
		echo $update_user_query;
		$update_user_result = mysqli_query($con, $update_user_query) or die(mysqli_error($con));
		unset($_SESSION['account_update_errors']);
		$_SESSION['account_update_success'] = true;
		$_SESSION['in'] = $new_email;	//update the logged in session with the new email address
	}

	mysqli_close($con);	//close db connection
	header("location: /Hurst Menu/settings.php");	//take user back to settings page
?>