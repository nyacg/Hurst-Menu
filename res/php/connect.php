<?php
	$con = mysqli_connect("menu.hppc.co.uk","hurstmenu","menupassword", "menu");
	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
?>