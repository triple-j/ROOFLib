<?php
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
if (isset($_PARAMS['table']) && isset($config['forms'][$_PARAMS['table']])) {
	$tkey  = $_PARAMS['table'];
	$table = $config['forms'][$_PARAMS['table']]['db'];
} else {
	reset($config['forms']);
	$tkey  = key($config['forms']);
	$table = $config['forms'][$tkey]['db'];
}
$form_name = $config['forms'][$tkey]['name'];

// add email address
if (isset($_PARAMS['email_address']) && $_PARAMS['email_address'] != '') {
	$qry = "
		INSERT IGNORE INTO `{$config['table_email_addresses']}`
			(email_type, email_address, email_name, form_db)
		VALUES(
			'{$_PARAMS['email_type']}',
			'{$_PARAMS['email_address']}',
			'{$_PARAMS['email_name']}',
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


<form id="db_form_select" action="<?=RFTK::href($config['current_page']);?>" name="form_select" method="get">
	<input type="hidden" name="<?=$_PREFIX;?>view" value="email" />
	Select Form:
	<select onchange="this.form.submit()" name="<?=$_PREFIX;?>table">
		<?php
		foreach($config['forms'] as $key=>$val) {
			echo '<option ' . ($table==$val['db'] ? 'selected="selected"' : '') . ' value="' . $key . '" >' . $val['name'] . '</option>';
		}
		echo PHP_EOL;
		?>
	</select>
	
<?php 
foreach ( $_GET as $key=>$value ) { 
	if ( strpos($key,$_PREFIX) !== 0 ) {
?>
	<input type="hidden" name="<?=$key;?>" value="<?=$value;?>" />
<?php 
	}
} 
?>
</form>

<div id="rooflib_email_address_admin">

<h1>Databased Forms Email Recipients &mdash; <?=$form_name;?></h1>

<p>If the email type '<em>To</em>' has not been set, the form will use the address set in <em>Configuration-&gt;Contact Info</em>.</p>

<form action="<?=RFTK::href($config['current_page'],"{$_PREFIX}ajax=update&{$_PREFIX}view=email");?>" name="update_values" method="post">
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
	<div class="row id_<?=$id;?>">
		<select name="<?=$_PREFIX;?>edit_type[<?=$id;?>]">
			<option <?=($type =='to'  ? 'selected' : '' );?> value="to">To</option>
			<option <?=($type =='cc'  ? 'selected' : '' );?> value="cc">CC</option>
			<option <?=($type =='bcc' ? 'selected' : '' );?> value="bcc">BCC</option>
		</select>
		<input type="text" name="<?=$_PREFIX;?>edit_name[<?=$id;?>]" value="<?=$name;?>" />
		<input type="email" name="<?=$_PREFIX;?>edit_addr[<?=$id;?>]" value="<?=$email_address;?>" />
		Delete:<input type="checkbox" name="<?=$_PREFIX;?>rm[<?=$id;?>]">
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

<form class="new_email" action="<?=RFTK::href($config['current_page'],"{$_PREFIX}view=email");?>" name="insert_values" method="post">
<fieldset>
	<legend>Insert new email address to form: <em><?=$form_name?></em></legend>

	<label>Type</label>
	<select name="<?=$_PREFIX;?>email_type" >
		<option value="to">To</option>
		<option value="cc">CC</option>
		<option value="bcc">BCC</option>
	</select><br />

	<label>Label / Full Name:</label>
	<input name="<?=$_PREFIX;?>email_name" type="text"><br />

	<label>Email Address:</label>
	<input name="<?=$_PREFIX;?>email_address" type="email"><br />

	<input type ="hidden" value="<?=$table;?>" name="<?=$_PREFIX;?>table"/>
	<input type="submit" value="submit" />
</fieldset>
</form>

</div><!-- /rooflib_email_address_admin -->

<script type="text/javascript">
	function updateForm(evt) {
		var $update_form = $('form[name=update_values]');
		$.post( $update_form.attr('action'), $update_form.serialize(), function(data){ }, "JSON" );
		$('input[type=checkbox][name^="<?=$_PREFIX;?>rm"]').each(function(){
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

	#rooflib_email_address_admin .row {
		margin-bottom: 1em;
	}
</style>
