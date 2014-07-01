<?php
#include('includes/application_top.php');

#error_reporting(E_ALL);

// make sure the email addresses table exists
$table_exists_sql = "SHOW TABLES LIKE '{$config['table_email_addresses']}'";
$table_exists_qry = $mysqli->query($table_exists_sql);
if ( $table_exists_qry->num_rows == 0 ) {
	$create_email_addresss_sql = "
		CREATE TABLE `{$config['table_email_addresses']}` (
			`id`            int(11)      NOT NULL AUTO_INCREMENT,
			`form_db`       varchar(255) DEFAULT NULL,
			`email_type`    varchar(3)   DEFAULT NULL,
			`email_address` varchar(255) DEFAULT NULL,
			`email_name`    varchar(255) DEFAULT NULL,
			PRIMARY KEY (`id`)
		);
	";
	$mysqli->query($create_email_addresss_sql);
}

// set the default form table
if (isset($_REQUEST['table']) && isset($config['forms'][$_REQUEST['table']])) {
	$tkey  = $_REQUEST['table'];
	$table = $config['forms'][$_REQUEST['table']]['db'];
} else {
	reset($config['forms']);
	$tkey  = key($config['forms']);
	$table = $config['forms'][$tkey]['db'];
}
$form_name = $config['forms'][$tkey]['name'];

// run ajax and exit
#if (isset($_GET['ajax-file'])) {
#	require('databased_forms_emails_ajax.php');
#	exit;
#}

// add email address
if (isset($_POST['email_address']) && $_POST['email_address'] != '') {
	$qry = "
		INSERT IGNORE INTO `{$config['table_email_addresses']}`
			(email_type, email_address, email_name, form_db)
		VALUES(
			'{$_POST['email_type']}',
			'{$_POST['email_address']}',
			'{$_POST['email_name']}',
			'{$table}'
		)
	";
	$mysqli->query($qry) or die($mysqli->error);
}

// get email addresses for current form
$email_addrs_sql = "
	SELECT
		email_type    AS `type`,
		email_address AS `address`,
		email_name    AS `name`,
		id            AS `id`
	FROM
		{$config['table_email_addresses']}
	WHERE
		form_db = '{$table}';
";
$email_addrs_qry = $mysqli->query($email_addrs_sql);
$email_data = array();
while ($row = $email_addrs_qry->fetch_array()) {
	$email_data []= $row;
}

#error_reporting(E_ALL ^ E_NOTICE);

?>


<form id="db_form_select" action="<?=$config['current_page'];?>" name="form_select">
	<input type="hidden" name="rf_page" value="email" />
	Select Form:
	<select onchange="this.form.submit()" name="table">
		<?php
		foreach($config['forms'] as $key=>$val) {
			echo '<option ' . ($table==$val['db'] ? 'selected="selected"' : '') . ' value="' . $key . '" >' . $val['name'] . '</option>';
		}
		?>
	</select>
</form>

<div id="rooflib_email_address_admin">

<h1>Databased Forms Email Recipients &mdash; <?=$form_name;?></h1>

<p>If the email type '<em>To</em>' has not been set, the form will use the address set in <em>Configuration-&gt;Contact Info</em>.</p>

<form action="<?=$config['current_page'];?>?ajax=update&rf_page=email" name="update_values" method="post">
<fieldset>
	<legend>Update existing emails for form: <em><?=$form_name?></em></legend>

<?php
		if (count($email_data)) {
			foreach ($email_data as $email) {
				$id            = $email['id'];
				$type          = $email['type'];
				$name          = $email['name'];
				$email_address = $email['address'];
?>
	<div id="row_<?=$id;?>">
		<select name="edit_type[<?=$id;?>]">
			<option <?=($type =='to'  ? 'selected' : '' );?> value="to">To</option>
			<option <?=($type =='cc'  ? 'selected' : '' );?> value="cc">CC</option>
			<option <?=($type =='bcc' ? 'selected' : '' );?> value="bcc">BCC</option>
		</select>
		<input type="text" name="edit_name[<?=$id;?>]" value="<?=$name;?>" />
		<input type="text" name="edit_addr[<?=$id;?>]" value="<?=$email_address;?>" />
		Delete:<input type="checkbox" name="rm[<?=$id;?>]">
	</div>
<?php
			}
			echo  '<input onclick="updateForm(event)" type="button" value="submit" />';
		} else {
			echo '<strong>this form has no exisiting email addresses</strong>';
		}
?>
</fieldset>
</form>

<form class="new_email" action="<?=$config['current_page'];?>?rf_page=email" name="insert_values" method="post">
<fieldset>
	<legend>Insert new email address to form: <em><?=$form_name?></em></legend>

	<label for="email_type">Type</label>
	<select name="email_type" >
		<option value="to">To</option>
		<option value="cc">CC</option>
		<option value="bcc">BCC</option>
	</select><br />

	<label>Label / Full Name:</label>
	<input name="email_name" type="text"><br />

	<label>Email Address:</label>
	<input name="email_address" type="text"><br />

	<input type ="hidden" value="<?=$_REQUEST['table']?>" name="table"/>
	<input type="submit" value="submit" />
</fieldset>
</form>

</div><!-- /rooflib_email_address_admin -->

<script type="text/javascript">
	function updateForm(evt) {
		var $update_form = $('form[name=update_values]');
		$.post( $update_form.attr('action'), $update_form.serialize(), function(data){ }, "JSON" );
		$('[name^="rm"]').each(function(){
			if($(this).prop('checked') == true){
				$(this).parent().hide();
			}
		});
		evt.preventDefault();
	}
</script>

<style>
	#rooflib_email_address_admin form {
		display: block;
	}

	#rooflib_email_address_admin fieldset {
		border: 1px solid #444;
		margin: 1em 0.25em;
		width: 48em;
	}

	#rooflib_email_address_admin .new_email label {
		display: inline-block;
		position: relative;
		width: 12em;
	}

	#rooflib_email_address_admin input,
	#rooflib_email_address_admin select {
		margin:5px;
	}

	#rooflib_email_address_admin [id^=row_] {
		margin-bottom: 1em;
	}
</style>
