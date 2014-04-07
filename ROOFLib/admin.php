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

class ROOFLib_Admin {

	protected $forms = array();
	protected $database_host;
	protected $database_user;
	protected $database_pass;
	protected $database_base;
	protected $results_per_page = 20;
	
	function __construct( $host=null, $user=null, $pass=null, $db=null ) {
		if ( $host && $user && $pass && $db ) {
			$this->setDatabaseCreds( $host, $user, $pass, $db );
		}
	}
	
	function config_array() {
		return array(
			"forms"            => $this->forms,
			"database_host"    => $this->database_host,
			"database_user"    => $this->database_user,
			"database_pass"    => $this->database_pass,
			"database_base"    => $this->database_base,
			"results_per_page" => $this->results_per_page
		);
	}
	
	function addForm( $table, $name, $file=null ) {
		$this->forms[ $table ] = array( 'db'=>$table, 'name'=>$name, 'file'=>$file );
	}

	function setDatabaseCreds( $host, $user, $pass, $db ) {
		$this->database_host = $host;
		$this->database_user = $user;
		$this->database_pass = $pass;
		$this->database_base = $db;
	}
	
	function output() {
		$config = $this->config_array();
		$config['current_page'] = strtok($_SERVER['REQUEST_URI'],'?');
	
		require( dirname(__FILE__)."/admin/includes/init.php" );
		
		foreach ( $this->forms as $form ) {
			archiveEntries( $form['db'] );
		}
	
		ob_start();
		if ( isset($_REQUEST['ajax']) ) {
			require( dirname(__FILE__)."/admin/ajax.php" );
		} else {
			require( dirname(__FILE__)."/admin/output.php" );
		}
		return ob_get_clean();
	}

}