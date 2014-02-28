<?php

global $mysqli;
$mysqli = new mysqli( $config['database_host'], $config['database_user'], $config['database_pass'], $config['database_base'] );
if ($mysqli->connect_errno) {
	die( "Failed to connect to MySQL: " . $mysqli->connect_error );
}


function cleanName($name) {
	$name = preg_replace('/_/',' ',$name);

	$name = ucwords($name);

	return $name;
}

function add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NULL" ){
	global $mysqli;
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
	global $mysqli;

	$sql_datediff = "DATEDIFF(NOW(), submit_timestamp) > {$days}";

	$result = $mysqli->query("SELECT * FROM {$table} WHERE {$sql_datediff}");
	if($result) {
		add_column_if_not_exist( $table, '_archived', "TINYINT( 4 ) NOT NULL" );

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
