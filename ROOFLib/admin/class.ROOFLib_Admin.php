<?php
class ROOFLib_Admin {

	protected $forms = array();
	protected $mysqli;

	public $results_per_page = 20;
	public $dbtbl_email_addresses = "roofl_email_addresses";

	function __construct( $host=null, $user=null, $pass=null, $db=null ) {
		$this->mysqli = new mysqli( $host, $user, $pass, $db );
		if ($this->mysqli->connect_errno) {
			die( "Failed to connect to MySQL: " . $this->mysqli->connect_error );
		}
	}

	function config_array() {
		return array(
			"forms"                 => $this->forms,
			"results_per_page"      => $this->results_per_page,
			"table_email_addresses" => $this->dbtbl_email_addresses
		);
	}

	function addForm( $table, $name, $file=null ) {
		$this->forms[ $table ] = array( 'db'=>$table, 'name'=>$name, 'file'=>$file );
	}

	function output() {
		$mysqli = $this->mysqli;
		$admin  = $this;
		$config = $this->config_array();

		$config['current_page'] = strtok($_SERVER['REQUEST_URI'],'?');

		foreach ( $this->forms as $form ) {
			$this->archiveEntries( $form['db'] );
		}

		$prefix  = isset($_REQUEST['ajax']) ? "ajax" : "view";
		$rf_page = (isset($_REQUEST['rf_page']) && $_REQUEST['rf_page'] == "email" ) ? "email_addresses" : "dbforms_list";

		ob_start();
		require( dirname(__FILE__)."/{$prefix}.{$rf_page}.php" );
		return ob_get_clean();
	}


//------------
	function cleanName($name) {
		$name = preg_replace('/_/',' ',$name);

		$name = ucwords($name);

		return $name;
	}

	function add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NULL" ){
		$mysqli = $this->mysqli;
		$exists = false;
		$columns = $mysqli->query("SHOW COLUMNS FROM $db");
		while($c =$columns->fetch_assoc()){
			if($c['Field'] == $column){
				$exists = true;
				break;
			}
		}
		if(!$exists){
			$mysqli->query("ALTER TABLE `$db` ADD `$column`  $column_attr");
		}
	}

	function manipulateFields($array) {
		//$array = array_slice($array,0,10);
		//array_push($array, 'submit_timestamp');
		return $array;
	}

	function archiveEntries( $table, $days=90 ) {
		$mysqli = $this->mysqli;

		$sql_datediff = "DATEDIFF(NOW(), submit_timestamp) > {$days}";

		$result = $mysqli->query("SELECT * FROM {$table} WHERE {$sql_datediff}");
		if($result) {
			$this->add_column_if_not_exist( $table, '_archived', "TINYINT( 4 ) NOT NULL" );

			// delete files
			while($row = $result->fetch_object()) {
				foreach($row as $key2=>$value2) {
					if(preg_match('/^FILE:(.*)$/i',$value2,$file)) {
						@unlink('../'.$file[1]);
					}
				}
			}

			$mysqli->query("UPDATE {$table} SET _archived=1 WHERE {$sql_datediff}");
		}
	}

}
