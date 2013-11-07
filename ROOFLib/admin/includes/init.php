<?php

mysql_connect($config['database_host'], $config['database_user'], $config['database_pass']) or die(mysql_error());
mysql_select_db($config['database_base']) or die(mysql_error());


function cleanName($name) {

	$name = preg_replace('/_/',' ',$name);

	$name = ucwords($name);

	return $name;

}

function add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NULL" ){
	$exists = false;
	$columns = mysql_query("SHOW COLUMNS FROM $db");
	while($c = mysql_fetch_assoc($columns)){
		if($c['Field'] == $column){
			$exists = true;
			break;
		}
	}
	if(!$exists){
		mysql_query("ALTER TABLE `$db` ADD `$column`  $column_attr");
	}
}

function manipulateFields($array) {
	//$array = array_slice($array,0,10);
	//array_push($array, 'submit_timestamp');
	return $array;
}

function archiveEntries( $table, $days=90 ) {

	$sql_datediff = "DATEDIFF(NOW(), submit_timestamp) > {$days}";

	$result = mysql_query("SELECT * FROM {$table} WHERE {$sql_datediff}");
	if($result) {
		add_column_if_not_exist( $table, '_archived', "TINYINT( 4 ) NOT NULL" );
		
		// delete files
		while($row = mysql_fetch_object($result)) {
			foreach($row as $key2=>$value2) {
				if(preg_match('/^FILE:(.*)$/i',$value2,$file)) {
					@unlink('../'.$file[1]);
				}
			}
		}
		
		mysql_query("UPDATE {$table} SET _archived=1 WHERE {$sql_datediff}");
	}

}
