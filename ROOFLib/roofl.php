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


Form::cfg( 'file_root',   $_SERVER['DOCUMENT_ROOT'] );
Form::cfg( 'web_root',    'http://'.$_SERVER['HTTP_HOST'] );
Form::cfg( 'web_catalog', str_replace($_SERVER['DOCUMENT_ROOT'],"",dirname(str_replace("\\","/",__FILE__)))."/" );


if (! isset($_SESSION)) {
	session_start();
}
