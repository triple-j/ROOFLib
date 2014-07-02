<?php
if (! isset($_SESSION)) {
	session_start();
}

//counteract magic quotes if they are enabled
if (get_magic_quotes_gpc()) {
	 function undoMagicQuotes($array, $topLevel=true) {
		  $newArray = array();
		  foreach($array as $key => $value) {
				if (!$topLevel) {
					 $key = stripslashes($key);
				}
				if (is_array($value)) {
					 $newArray[$key] = undoMagicQuotes($value, false);
				}
				else {
					 $newArray[$key] = stripslashes($value);
				}
		  }
		  return $newArray;
	 }
	 $_GET = undoMagicQuotes($_GET);
	 $_POST = undoMagicQuotes($_POST);
	 $_COOKIE = undoMagicQuotes($_COOKIE);
	 $_REQUEST = undoMagicQuotes($_REQUEST);
}

require_once(dirname(__FILE__).'/classes/toolkit.php');
require_once(dirname(__FILE__).'/admin/class.ROOFLib_Admin.php');
