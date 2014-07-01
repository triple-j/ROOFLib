<?php

class ROOFLib_Email {

	protected $mysqli;
	protected $table;

	public $dbtbl_email_addresses = "roofl_email_addresses";


	/**
	 * Creates a new ROOFLib_Email
	 *
	 * @param String  $table    The name of the database table.
	 */
	function __construct( $table ) {
		$this->table  = $table;
		$this->mysqli = new mysqli( Form::cfg('db_server'), Form::cfg('db_user'), Form::cfg('db_pass'), Form::cfg('database') );
		if ($this->mysqli->connect_errno) {
			die( "Failed to connect to MySQL: " . $this->mysqli->connect_error );
		}
	}


	/**
	 * get the email addresses set for a given form
	 *
	 * @return array  an associative array containing arrays for 'to', 'cc', and 'bcc'
	 */
	function get_addresses() {

		$mysqli          = $this->mysqli;
		$allowed_types   = array( 'to', 'cc', 'bcc' );
		$email_addresses = array();

		$email_sql = "
			SELECT
				email_type    AS type,
				email_address AS address,
				email_name    AS name
			FROM
				`{$this->dbtbl_email_addresses}`
			WHERE
				form_db = '{$this->table}';
		";
		$email_qry = $this->mysqli->query($email_sql) or die($this->mysqli->error);
		while ( $email = $email_qry->fetch_array() ) {

			$type          = strtolower($email['type']);
			$email_address = $email['address'];
			$name          = (trim($email['name'])=="") ? $email['address'] : $email['name'];

			if ( in_array( $type, $allowed_types ) ) {
				if ( !isset($email_addresses[ $type ]) ) {
					$email_addresses[ $type ] = array();
				}

				$email_addresses[ $type ][ $name ] = $email_address;
			}

		}

		return $email_addresses;
	}

}
