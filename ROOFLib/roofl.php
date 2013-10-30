<?php
require_once(dirname(__FILE__).'/classes/class.form.php');

require_once(dirname(__FILE__).'/classes/class.phpmailer.php');
require_once(dirname(__FILE__).'/classes/class.DatabaseForm.php');
require_once(dirname(__FILE__).'/data/config.php'); // this will become obsolete

foreach (Form::$FORMITEMS as $filename => $description) {
	$class = (($filename == 'FormItem')?'':'FI_').$filename;
	Form::$__fi_strclass[strtolower($filename)] = $class;
	require_once(dirname(__FILE__).'/classes/class.'.strtolower($filename).'.php');
}

if (! isset($_SESSION)) {
	session_start();
}
