<?php 
	session_start();	//connect to users session on the server

	//check if the logged in session is not set
	if(!isset($_SESSION['in'])){
		//if it is not then redirect to the login page with the
		//page that was trying to be accessed and the URL variables
		//as the page URL variable
		header("location: login.php?page=".$_SERVER['REQUEST_URI']);
	} 
?>