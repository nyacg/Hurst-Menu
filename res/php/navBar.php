<?php 
	$page = $_SERVER['PHP_SELF'];	//get the page that has included this file (i.e. the page that is open)
?>

<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/">Hurst Menu</a>
		</div>

		<!-- navigation bar links -->
		<div class="collapse navbar-collapse" id="navbar">
			<ul class="nav navbar-nav">
				<!-- the php is used to set the currently open page as highlighted on the navigation bar -->
				<li class="<?php echo ($page == "/Hurst Menu/uploadMenu.php") ? "active" : "" ?>"><a href="./uploadMenu.php">Upload Menu</a></li>
				<li class="<?php echo ($page == "/Hurst Menu/viewMenu.php") ? "active" : "" ?>"><a href="./viewMenu.php">View Menu</a></li>
				<li class="<?php echo ($page == "/Hurst Menu/editMenu.php") ? "active" : "" ?>"><a href="./editMenu.php">Edit Menu</a></li>
				<li class="<?php echo ($page == "/Hurst Menu/deleteMenu.php") ? "active" : "" ?>"><a href="./deleteMenu.php">Delete Menu</a></li>
				<li class="<?php echo ($page == "/Hurst Menu/messages.php") ? "active" : "" ?>"><a href="./messages.php">Feedback Messages</a></li>
				<li class="<?php echo ($page == "/Hurst Menu/likes.php") ? "active" : "" ?>"><a href="./likes.php">Likes/Dislikes</a></li>
				<li class="<?php echo ($page == "/Hurst Menu/attendance.php") ? "active" : "" ?>"><a href="./attendance.php">Attendance</a></li>
			</ul>

			<ul class="nav navbar-nav navbar-right">
				<li><a href="./login.php">Logout</a></li>
				<li><a href="./dashboard.php">Dashboard</a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Account<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="./settings.php">Settings</a></li>
						<li><a href="./newAccount.php">Create Account</a></li>
						<li><a href="./manageAccounts.php">Manage Accounts</a></li>
					</ul>
				</li>
				<li><a href="./help.php">Help</a></li>
			</ul>
		</div>
	</div>
</nav>