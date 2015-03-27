<?php 
	include "./res/php/checkLogin.php";	//ensure user is logged in
	include "./res/php/connectlocal.php";	//connect to db
?>

<!DOCTYPE HTML>

<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Manage Accounts - Hurst Menu</title>
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>

		<div class="page">
			<div class="page-header">
				<h1>Manage Accounts</h1>
			</div>

			<!-- table to hold account details -->
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Email Address</th>
						<th>Receiving Emails</th>
						<th>Delete</th>
					</tr>
				</thead>

				<tbody>
					<?php
						//get the details of all the users
						$users_query = "SELECT * FROM user";
						$user_result = mysqli_query($con, $users_query)or die(mysqli_error($con));

						//for each user add a row to the table with their email address, whether they 
						//are receiving emails and a link to delete the account
						while($row = mysqli_fetch_assoc($user_result)){
							echo "<tr>";
							echo "<td>" . $row['email_address'] . "</td>";
							$emails = $row['receive_suggestions'] ? "Yes" : "No";
							echo "<td>$emails</td>";
							if($row['email_address'] != $_SESSION['in']){
								$user_id = $row['user_id'];
								echo "<td><a href='./res/php/deleteAccount.php?id=$user_id'>delete</a></td>";
							} else {
								//if the user being listed is the one that is logged in then don't put a link
								//to delete the account, instead notify the user that they cannot delete the
								//logged in account
								echo "<td>Cannot delete logged in account</td>";
							}
							echo "</tr>";
						}
						mysqlI_close($con);	//close the connection to the db
					?>
				</tbody>
			</table>

			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>