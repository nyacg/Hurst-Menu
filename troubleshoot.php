<?php
	echo "PHP Working!<br>";

	echo "PHP Version: " . phpversion() . "<br>";
	
	include "./res/php/connectlocal.php";

	echo "SQL Active Connections: ";
	$sql = mysqli_get_connection_stats($con);
	echo $sql['active_connections'];
	echo " If this number is greater than 0 then you are connected to the SQL and the database is working fine!";

	unset($sql);
	mysqli_close($con);
?>