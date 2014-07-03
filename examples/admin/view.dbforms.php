<?php
require_once(dirname(__FILE__).'/../config.php');

#error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ALL);

require_once(dirname(__FILE__).'/../../ROOFLib/admin.php');

$roofl_admin = new ROOFLib_Admin( DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE );
$roofl_admin->addForm( 'contact', "Contact Example", "examples/contact.php" );
$roofl_admin->addForm( 'null', "NULL", "examples/contact.php" );

$content = $roofl_admin->output(); // HACK: must be run before headers are sent or ajax files will not work
?>
		<h2>Databased Forms</h2>
		
		<div style="margin:1em; padding:2em; border:solid 2px black; border-radius:0.67em;">

<?=$content;?>

		</div>
		<div>
			<a href="<?=$base_page;?>">Home</a>
			|
			<a href="<?=$base_page;?>?view=login&out">Logout</a>
		</div>
		
