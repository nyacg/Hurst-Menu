<?php
	include "checkLogin.php";	//check user is logged in
	include "connectlocal.php";	//connect to db

	$user_id = isset($_GET['id']) ? $_GET['id'] : false;	//get user id from URL variable

	if($user_id){
		//delete user from db
		$delete_user_query = "DELETE FROM user WHERE user_id = $user_id";
		$delete_user_result = mysqli_query($con, $delete_user_query) or die(mysqli_error($con));
	}

	mysqli_close($con);	//close connection to db
	header("location: /Hurst Menu/manageAccounts.php"); 	//return user to manage acccounts page