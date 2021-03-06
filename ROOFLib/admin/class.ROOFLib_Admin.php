<?php
class ROOFLib_Admin {

	protected $forms = array();
	protected $mysqli;

	public $results_per_page = 20;
	public $dbtbl_email_addresses = "roofl_email_addresses";
	public $param_prefix = "roofl_";

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
		
		$_PREFIX = $this->param_prefix;
		$_PARAMS = $this->url_params();

		$current_path   = strtok($_SERVER['REQUEST_URI'],'?');
		$current_params = strtok('?');
		if ( !empty($current_params) ) {
			$url_query = array();
			parse_str($current_params,$url_query);
			foreach ( $url_query as $key=>$value ) {
				if ( strpos($key,$this->param_prefix) === 0 ) {
					// remove parameters added to the url by ROOFLib_Admin
					unset($url_query[$key]);
				}
			}
			$current_params = http_build_query($url_query);
		}
		$config['current_page'] = RFTK::href($current_path,$current_params);


		foreach ( $this->forms as $form ) {
			$this->archiveEntries( $form['db'] );
		}

		$inc_prefix = isset($_PARAMS['ajax']) ? "ajax" : "view";
		$inc_page   = (isset($_PARAMS['view']) && $_PARAMS['view'] == "email" ) ? "email_addresses" : "dbforms_list";

		ob_start();
		require( dirname(__FILE__)."/{$inc_prefix}.{$inc_page}.php" );
		return ob_get_clean();
	}
	
	/**
	 * return $_GET/$_POST variables specific to ROOFLib_Admin in an associative array
	 * 
	 * @return Array
	 */
	function url_params() {
		$params   = array();
		$temp_arr = array_merge($_GET,$_POST);
		
		foreach ( $temp_arr as $key=>$value ) {
			if ( strpos($key,$this->param_prefix) === 0 ) {
				$rf_key = substr( $key, strlen($this->param_prefix) );
				$params[$rf_key] = $value;
			}
		}
		
		return $params;
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
