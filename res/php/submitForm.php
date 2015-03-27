<?php
	include "connectLocal.php"; 	//connect to db
	$formok = true;
	$errors = array();	//initialise an array to hold the errors

	//submission data
	$ipaddress = $_SERVER['REMOTE_ADDR'];
	$date = date('Y-m-d');
	$time = date('H:i:s');

	//form data
	$name = $_POST['name'];
	$email = $_POST['email'];
	$message = mysqli_real_escape_string($con, $_POST['message']);

	//validate form data
	//validate name is not empty
	if(empty($name)){
		$formok = false;
		$errors[] = "You have not entered a name";
	} elseif(strlen($name) > 30){
		$formok = false;
		$errors[] = "Name too long, should be less than 30 characters";
	} else{
		$name = ucwords(strtolower($name)); 	//make name have capital first letters
	}

	//validate email address is not empty
	if(empty($email)){
		$formok = false;
		$errors[] = "You have not entered an email address";

	//validate email address is valid 
	} else {
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$formok = false;
			$errors[] = "You have not entered a valid email address";
		}
		if(strlen($email) > 45){
			$formok = false;
			$errors[] = "Email too long, should be less than 45 characters";
		}
	}


	//validate message is not empty
	if(empty($message)){
		$formok = false;
		$errors[] = "You have not entered a message";
	}

	//validate message is greater than 20 characters
	elseif(strlen($message) < 20){
		$formok = false;
		$errors[] = "Your message must be greater than 20 characters";
	}

	//send email and upload to server if all is ok
	if($formok){
		ini_set("sendmail_from", "info@example.com");  
		$headers = "From: $email" . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$emailbody = "<p>You have received a new message from the suggestions/feedback form on your website.</p>
						><strong>Name: </strong> {$name} </p>
						><strong>Email Address: </strong> {$email} </p>
						><strong>Message: </strong> {$message} </p>
						>This message was sent from the IP Address: {$ipaddress} on {$date} at {$time}</p>";
		
		//get email addresses of recipitants from database
		$query = "SELECT email_address FROM user WHERE receive_suggestions = 1";
		$result = mysqli_query($con, $query);
		$recipitents = mysqli_fetch_array($result);

		//send the message as an email to each user that has requested messages
		if(count($recipitents) > 0) {
			foreach($recipitents as $emailaddr){
				mail($emailaddr, "New Suggestion recieved from the Hurst Menu System", $emailbody, $headers);
			}
		}
		
		//add the message to the database
		$query = "INSERT INTO message (`date`, time, name, email, message, ip_address) VALUES ('$date', '$time', '$name', '$email', '$message', '$ipaddress')";
		$result = mysqli_query($con, $query);	
	}

	if(count($errors) == 0){
		$errors[] = "none";
	}

	//what we need to return back to our form
	$returndata = array(
		'form_ok' => $formok,
		'errors' => $errors
	);
	//return the result as a JSON encoded string that can be processed client side
	echo json_encode($returndata);

	mysqli_close($con);
?>