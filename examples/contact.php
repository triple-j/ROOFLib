<?php

require_once(dirname(__FILE__).'/../ROOFLib/roofl.php');
require_once(dirname(__FILE__).'/config.php');

Form::cfg( 'db_server', DB_SERVER );
Form::cfg( 'db_user',   DB_USERNAME );
Form::cfg( 'db_pass',   DB_PASSWORD );
Form::cfg( 'database',  DB_DATABASE );

$form_name     = "contact";
$upload_dir_ws = dirname(Form::cfg('web_catalog')) . "/examples/uploads/";

function isnamedray($formitem, &$errors, &$warnings) {
	$value = $formitem->value();
	if (strtolower(trim($value)) == 'ray') {
		$errors []= "Your name is ray. YOu are not allowed to submit this form";
	} else {
		$warnings[]= "Your name is not ray, but I don't like you.";
	}
	return true;
}

/* START: Create Form */
$form = Form::create($form_name)
	->setSuccessMessage('Success!')
	->set('required_attr', false)

	->addSeparator('Contact Information', array('separator'=>'', 'help'=>'<strong>Your information is safe with us!</strong><p>We will not distribute any personal information we receive from you.</p>'))
	->requireText('firstname', 'First Name', Array('validators' => Array('isnamedray')))
	->requireText('lastname', 'Last Name')
	->addText('company', 'Company')
	->requireEmail('email', 'Email')
	->requirePhone('phone', 'Telephone Number')
	->addPhone('fax', 'Fax Number')
	->addDate('birthday', 'Birthday')

	->addSeparator('Location', array('separator'=>''))
	->addText('address1', 'Street Address')
	->addText('address2', 'Address 2', array('hide_label'=>true))
	->addText('city', 'City')
	->requireSelect('stateprovince', 'State / Province', array('options' => Form::getData('statesprovinces'), 'other' => "Other&hellip;"))
	->addText('postal', 'Zip / Postal Code')
	->requireSelect('country', 'Country', array('options'=>Form::getData('countries')))

	->addSeparator('Message', Array('separator'=>''))
	->addTextarea('message', 'Comments, questions, or details')

	->addFile('file', 'Upload a document', Array('maxFiles' => 5, 'allowMultiple' => true, 'uploadDir'=>$upload_dir_ws))
	->requireCaptcha('Are you human?')
	->setButtons(Form::BU('Send', 'send'));
/* END: Create Form */



	$email = new ROOFLib_Email($form_name);
	#var_dump($email->get_addresses());



/* START: Form Action */
if ($form->action() && $form->validate()) {

	// store in database forms table
	$form->storeEntry();

	// send email
	$value = $form->value();
	$email = new ROOFLib_Email($form_name);
	$email_params = array(
		'subject'     => STORE_NAME . ' Contact Form',
		'fromAddress' => $value['email'],
		'fromName'    => $value['firstname'] . ' ' . $value['lastname'],
		'header'      => '<h1>' . STORE_NAME . ' Contact Form</h1>',

		'to'  => array( STORE_NAME . ' Contact' => STORE_OWNER_EMAIL_ADDRESS )  // default address if not overwritten in the database
	);
	if ( !defined('DEBUG_LVL') || DEBUG_LVL < 1 ) {
		$email_recipients = $email->get_addresses();
		$email_params     = array_merge( $email_params, $email_recipients );  // overwrite 'to', 'cc', 'bcc' email addresses (if set)
	}
	$form->sendEmail( $email_params );

	// show success page
	header('Location: ?success');
	exit();

} else if (! $form->action() ) {
	// default values get added here
	$form->value(array(
		'message' => 'Default Message / Auto text goes here'
	));
}
/* END: Form Action */



?>

<style type="text/css">

	#fi_popUp { width:400px; min-height:150px; z-index:100000; position:fixed; left:50%; margin-left:-200px; top:50%; margin-top:-100px; box-shadow: 0px 4px 10px #000; border:1px solid #333; background-color:#fff; font-family:Arial, sans-serif; background-image: -webkit-gradient(
		linear,
		left bottom,
		left top,
		color-stop(0.33, rgb(224,224,224)),
		color-stop(0.84, rgb(255,255,255))
	);
	background-image: -moz-linear-gradient(
		center bottom,
		rgb(224,224,224) 33%,
		rgb(255,255,255) 84%
	); }
	#fi_popUp .header { font-weight:bold; color:#fff; background-color:#333; font-size:14px; padding:3px; }
	#fi_popUp .message { padding:5px; }
	#fi_popUpModal {
		width:100%; height:100%; /*background:url('../resources/pinstripe.png') repeat; z-index:99999; */
		background: -moz-radial-gradient(50% 50%, ellipse closest-side, #666 0%,#111 100%);
		background: -o-radial-gradient(50% 50%, ellipse closest-side, #666 0%,#111 100%);
		background: -ms-radial-gradient(50% 50%, ellipse closest-side, #666 0%,#111 100%);
		background: -webkit-gradient(radial, 50% 50%, 0, 40% 40%, 60 from (#666), to (#111));
		background: radial-gradient(50% 50%, ellipse closest-side, #666 0%,#111 100%);
		opacity:0.8; position:fixed; top:0px; left:0px;

	}



	.fi_icon { float:left; padding:2px; }
	.fi_close { float:right; padding:2px; }

	.rf_form, .rf_note, .rf_welcome, .rf_success, .rf_error, .rf_warning { font-family:Arial, sans-serif; font-size:12px;}

	h1, .sepLabel { border-bottom:1px solid #ccc; color:#17345C; font-family:Arial, sans-serif; }

	.sepLabel {  font-weight:bold; padding-top:20px; font-size:14px; text-transform:uppercase; }
	.rf_name, .rff_name { font-weight:bold; padding-top:5px; margin-top:10px; vertical-align:top; border-bottom:1px solid #ccc; }
	.rf_value { padding-top:5px; vertical-align:top; }


	#rfi_stateprovince .rf_value { width:200px; }
	#rfi_stateprovince select  { width:100%; }
	#rfi_stateprovince select.rfa_other { width:32%; margin-right:2px; }
	#rfi_stateprovince input { width:65%; }
	#rfi_postal input { width: 140px;   }
	.rf_fbu  {clear:both; padding-top:10px; }

	textarea { width: 350px; height:150px; }


	#rfi_captcha {
		margin-top:10px;
	}
	#rfi_captcha .rf_name {
		padding-top:0px;
		border-bottom:1px solid #ccc;
		margin-bottom:5px;
	}

	.rf_req { color:#c00; font-weight:bold;  }
	.rf_note { font-style:italic; font-weight:normal; }

	.rf_value .rf_desc { font-style:italic; font-weight:normal;  }
	.rf_name .rf_desc { font-style:italic; font-weight:normal; }
	.rf_success { background-color:#D6EBFF; padding:25px; border:1px solid #99CCFF; color:#000; font-weight:bold; }
	.rf_error { background-color:#FFCCCC; padding:5px; border:1px solid #FF0000; color:#c00; font-weight:bold; }
	.rf_error ul, .rf_warning ul { margin:5px 0px; color:#000; }
	.rf_error li, .rf_warning li { font-weight:normal; }
	.rf_warning { background-color:#FFFFCC; padding:5px; border:1px solid #CC9900; color:#000; font-weight:bold; }

	/* Round */
	#rfi_captcha, .rf_warning, .rf_error, .rf_success {
		border-radius:5px;
		-webkit-border-radius:5px;
		-moz-border-radius:5px;
		-o-border-radius:5px;
	}

	/* Clear lefts */
	#rfi_country, .fbu, #rfi_email, .sepLabel, #rfi_birthday, #rfi_company { clear: left; }

	/* Float lefts */
	#rfi_postal, #rfi_stateprovince, #rfi_firstname, #rfi_lastname, #rfi_phone, #rfi_fax { float:left;  }
	/* left elements */
	#rfi_stateprovince, #rfi_firstname, #rfi_phone { margin-right:10px;  }

	/* Full width inputs */
	#rfi_email input, #rfi_birthday input, #rfi_address1 input, #rfi_address2 input, #rfi_country select, #rfi_city input, #rfi_company input { width:350px; }
	#rfi_email, #rfi_country, #rfi_captcha #rfi_birthday { width:350px; }

	/* half widths */
	#rfi_firstname input, #rfi_lastname input, #rfi_phone input, #rfi_fax input { width:170px; }
	.rfc_fi_flip_inc:active { background:#333; opacity:0.3;}
	.rfc_fi_flip_dec:active {  background:#333; opacity:0.3; }

	#rfi_year .rfc_fi_flip_outer { background:url('../resources/flipleft.png') left no-repeat; height:50px; width:100px; position:relative;  }
	#rfi_year .rfc_fi_flip_inc { padding:0px; margin:0px; height:25px; margin-top:-50px; width:100px; position:relative; z-index:2; }
	#rfi_year .rfc_fi_flip_dec { padding:0px; margin:0px; height:25px; width:100px; position:relative; z-index:2; }

	#rfi_year #rfi_text { background:url('../resources/flipright.png') right no-repeat; padding:15px; margin-right:-5px; line-height:20px; font-size:21px; font-family:Helvetica, Arial, sans-serif; color:#17345C;}

</style>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script>
</script>
<h1>Contact</h1>

<?php echo $form->printForm(true); ?>
