<?php

if ( $_REQUEST['ajax'] == "update" ) {
	$edit_addr   = ($_POST['edit_addr']);
	$edit_type   = ($_POST['edit_type']);
	$edit_name   = ($_POST['edit_name']);
	$remove_addr = (isset($_POST['rm']) && is_array($_POST['rm'])) ? $_POST['rm'] : array();

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

