<?php include "./res/php/checklogin.php" ?>
<!DOCTYPE HTML>

<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Messages - Hurst Menu</title>		
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>

		<div class="page">
			<div class="page-header">
				<h1>Messages</h1>
			</div>

			<div class="list-group">
				<a class="list-group-item active">
					<h4 class="list-group-item-heading">Messages</h4>
					<p class='list-group-item-text'>Click on a message to reply by email</p>
				</a>
				<?php 
					include "./res/php/connectlocal.php"; 	//connect to db
					//ternary operator to check if limit URL variable is set, if it is then get the integer value of the limit, otherwise set it to 5
					$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
					//query to get the messages in chronological order witht the most recent ones at the top
					$messages_query = "SELECT * FROM message ORDER BY `date` DESC LIMIT $limit";
					$result = mysqli_query($con, $messages_query);	//excecute the query
					
					//loop through all the messages, displaying them as links in the list group, Bootstrap handles the styling
					while($row = mysqli_fetch_assoc($result)){
						$date = strtotime($row["date"]);
						$now = strtotime("now");
						$nice_date = date("l jS \of F Y", $date);	//get the nicely formatted date (e.g. 20th October 2013)
						//get the mailto link in the form email_address?subject=subject&body=email_message

						//the body is made up of 'Dear Name,' and their origional message
						//the %0D%0A is used to add in a carriage return each time (a URL encoded carriage return)
						$mailto = rawurlencode($row["email"])."?subject=Reply%20to%20your%20message%20on%20Hurst%20Menu&body=Dear ".
									rawurlencode($row["name"]).",%0D%0A%0D%0AOriginal%20Message:%0D%0A".rawurlencode($row["message"]);

						//output message to the page, ternary operator used to decide whether the message was
						//recieved in the past 7 days, if it was then a 'recent' label is placed beside the message
						echo "
							<a href='mailto:$mailto' class='list-group-item'>
							<h4 class='list-group-item-heading'>Message from ".$row["name"]." on ".$nice_date.
							" at ".$row["time"].($now - $date < 604800 ? " <span class='label label-default'>Recent</span>" : "")."</h4>
							<p class='list-group-item-text'>".$row["message"]."</p>
							</a>
						";
					}
				?>

				<!-- a link to load the next 5 items, the #bottom specifies the loaded page to scroll to this position so the 
				newly loaded messages can be seen -->
				<a name='bottom' <?php echo "href='./messages.php?limit=".strval($limit + 5)."#bottom'" ?> >Load More</a>
			</div>

			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>