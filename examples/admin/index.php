<?php
session_start();
if(!isset($_SESSION['formsAdmin'])) header("Location: login.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Pages</title>
	</head>
	<body>

		<h2>Pages</h2>
		<ul>
			<li><a href="dbforms.php">Databased Forms</a></li>
			<li><a href="dbforms.php?rf_page=email">Email Addresses</a></li>
		</ul>

	</body>
</html>
