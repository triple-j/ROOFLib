<?php
$_TMPL['title'] .= " - Please Login";

$config['admin_user'] = 'admin';
$config['admin_pass'] = 'password';

if(isset($_GET['out'])) {
	unset($_SESSION['formsAdmin']);
	header("Location: {$base_page}?view=login");
	exit;
}

if(isset($_POST['username'])) {
	if($_POST['username'] == $config['admin_user'] && $_POST['password'] == $config['admin_pass']) {

		$_SESSION['formsAdmin'] = $_POST['username'];

		foreach($config['forms'] as $key=>$value) {
			archiveEntries( $value['db'] );
		}

		header("Location: {$base_page}?view=default");
		exit;

	} else {
		$_SESSION['error'] = "There was an error with your username or password.";
		header("Location: {$base_page}?view=login");
		exit;
	}
}
?>
		<h2>Please Login</h2>
		
		<?php if ( isset($_SESSION['error']) ) { echo $_SESSION['error']; unset($_SESSION['error']); } ?>
		
		<form action="" method="post">
			Username:
				<input type="text" name="username" /><br><br>
			Password:
				<input type="password" name="password" /><br><br>

			<input type="submit" value="Login" />
		</form>
