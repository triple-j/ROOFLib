<?php
require_once(dirname(__FILE__).'/../config.php');

error_reporting(E_ALL ^ E_NOTICE);

session_start();
if(!isset($_SESSION['formsAdmin'])) header("Location: login.php");

require_once(dirname(__FILE__).'/../../ROOFLib/admin.php');

$roofl_admin = new ROOFLib_Admin( DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE );
$roofl_admin->addForm( 'contact', "Contact Example", "examples/contact.php" );
$roofl_admin->addForm( 'null', "NULL", "examples/contact.php" );

$content = $roofl_admin->output();
?>
<!DOCTYPE html>
<html>
	<head>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.8/jquery-ui.min.js"></script>

		<link rel="stylesheet" type="text/css" href="../../ROOFLib/admin/css/admin.css" media="all">
		<link rel="stylesheet" type="text/css" href="../../ROOFLib/admin/css/admin_print.css" media="print">

		<link rel="stylesheet" type="text/css" href="../../ROOFLib/resources/ui-simple-theme/style.css" media="all">
	</head>
	<body>

<?=$content;?>

		<div><a href="login.php?out">Logout</a></div>
	</body>
</html>
