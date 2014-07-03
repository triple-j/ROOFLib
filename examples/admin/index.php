<?php
session_start();

$_TMPL = array();
$_TMPL['title']   = "Example ROOFLib Admin";
$_TMPL['heading'] = $_TMPL['title'];

$base_page = basename(__FILE__);

$current_view = "view.default.php";
if( !isset($_SESSION['formsAdmin']) || ( isset($_GET['view']) && $_GET['view'] == "login" ) ) {
	$current_view = "view.login.php";
} elseif ( isset($_GET['view']) && $_GET['view'] == "databasedForms" ) {
	$current_view = "view.dbforms.php";
}

ob_start();
include( $current_view );
$content = ob_get_clean();
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?=$_TMPL['title'];?></title>
		
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.8/jquery-ui.min.js"></script>

		<link rel="stylesheet" type="text/css" href="../../ROOFLib/admin/css/admin.css" media="all">
		<link rel="stylesheet" type="text/css" href="../../ROOFLib/admin/css/admin_print.css" media="print">

		<link rel="stylesheet" type="text/css" href="../../ROOFLib/resources/ui-simple-theme/style.css" media="all">
	</head>
	<body>
		
		<h1><?=$_TMPL['heading'];?></h1>

<?php echo $content.PHP_EOL; ?>

	</body>
</html>
