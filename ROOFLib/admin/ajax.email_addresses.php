<?php

if ( $_PARAMS['ajax'] == "update" ) {
	$edit_addr   = ($_PARAMS['edit_addr']);
	$edit_type   = ($_PARAMS['edit_type']);
	$edit_name   = ($_PARAMS['edit_name']);
	$remove_addr = (isset($_PARAMS['rm']) && is_array($_PARAMS['rm'])) ? $_PARAMS['rm'] : array();

	foreach ($edit_addr as $id => $val) {
		if ( !isset($remove_addr[$id]) ) {
			$sql = "
				UPDATE
					`{$config['table_email_addresses']}`
				SET
					email_type    = '{$edit_type[$id]}',
					email_address = '{$val}',
					email_name    = '{$edit_name[$id]}'
				WHERE
					id = '{$id}';
			";
			//dump($sql);
		} else {
			$sql = "DELETE FROM `{$config['table_email_addresses']}` WHERE id = '{$id}'";
		}
		$mysqli->query($sql) or die($mysqli->error);
	}
}

exit;

