<?php include "./res/php/checkLogin.php" ?>
<!DOCTYPE HTML>

<html lang="en">
	<head>
		<?php include "./res/php/header.php" ?>
		<title>Upload Menu - Hurst Menu</title>	
	</head>

	<body>
		<?php include "./res/php/navBar.php" ?>
		
		<div class="page">
			<div class="page-header">
				<h1>Upload Menu</h1>
			</div>
			<?php
				if(isset($_SESSION["upload_failed"])){	
	 				echo "<div class='alert alert-warning'><strong>Warning!</strong> Menu upload failed, check dates and file then try again.</div>";
					unset($_SESSION["upload_failed"]);	
				}
			?>
			<div class="left left-form">
				<form action="./res/php/upload_file.php" method="post" enctype="multipart/form-data">
					<label for="start_date">First Day of Menu:</label>
					<div class="input-group menu-upload-input">
						<span class="input-group-addon glyphicon glyphicon-calendar"></span>
						<input type="date" class="form-control glyph-input" required="required" name="start_date"/>
					</div>

					<label for="end_date">Last Day of Menu:</label>
					<div class="input-group menu-upload-input">
						<span class="input-group-addon glyphicon glyphicon-calendar"></span>
						<input type="date" class="form-control glyph-input"  required="required" name="end_date"/>
					</div>

					<label for="start_week">First Week Number:</label>
					<div class="input-group menu-upload-input">
						<span class="input-group-addon glyphicon">1,2,3</span>
						<input type="number" class="form-control glyph-input" required="required" value="1" min="1" max="3" name="start_week"/>
					</div>

					<label for="file">Filename:</label>
					<div class="input-group menu-upload-input">
						<span class="input-group-addon glyphicon glyphicon-folder-open"></span>
						<input type="file" class="form-control glyph-input" required="required" name="file" id="file"/>
					</div>
					<br>

					<button type="submit" class="btn btn-lg btn-primary" name="submit" value="Submit">Submit</button>
				</form>
			</div>

			<div class="left instructions-panel">
				<div class="panel panel-primary" >
					<div class="panel-heading">Instructions</div>
					<div class="panel-body">
						<p>1. Set the start and end date of the duration of the menu. If there is a school holiday in between these dates then you will need to upload the menu in separate parts for the time the menu is active before the holiday and the time the menu is active after it.</p>
						<p>2. Set the week number of the menu that the three week cycle should start on.</p>
						<p>3. Choose the Excel document of the menu that you wish to upload. This should be of the standard menu format otherwise it will not be read correctly.</p>
						<p>For additional menu uploading guidance (including an example standard format Excel document) please see the <a href="./help.php">help</a> page.</p>
						<p>You cannot overwrite exsisting menu unless you delete it first</p>
					</div>
				</div>
			</div>

			<?php include "./res/php/footer.php" ?>
		</div>
	</body>
</html>