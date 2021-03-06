<?php

function ROOFL_Config() {
	$default = Array(
		'file_root'           => '', //$_SERVER['DOCUMENT_ROOT'],
		'web_root'            => '', //'http://'.$_SERVER['HTTP_HOST'],
		'web_catalog'         => '', //'/_base_apps/forms_rm/',
		'web_formroot'        => '', //'ROOFLib/',

		'debug'               => false,

		'dir_cache'           => 'cache/',
		'dir_resources'       => 'resources/',
		'dir_data'            => 'data/',
		'dir_js'              => 'js/',

		'prefix_id'           => 'rfi_',
		'prefix_form'         => 'rff_',
		'prefix_class'        => 'rfc_',

		'class_form'          => 'rf_form',
		'class_formitem'      => 'rf_fi',
		'class_required'      => 'rf_cr',
		'class_buttongroup'   => 'rf_fbu',
		'class_not_required'  => 'rf_cn',
		'class_post'          => 'rf_post',
		'class_pre'           => 'rf_pre',
		'class_warning'       => 'rf_warning',
		'class_inline'        => 'rf_inline',
		'class_error'         => 'rf_error',
		'class_success'       => 'rf_succes',
		'class_fieldname'     => 'rf_name',
		'class_fieldvalue'    => 'rf_value',
		'class_fieldmessages' => 'rf_messages',
		'class_description'   => 'rf_desc',
		'class_welcome'       => 'rf_welcome',
		'class_note'          => 'rf_note',

		'text_warning'        => '',
		'text_success'        => '',
		'text_note'           => 'Required Fields <span class="rf_req">*</span>',
		'text_required'       => '<span class="rf_req">*</span>',
		'text_error_head'     => 'Please enter a value for field: <em>%s</em>',
		'text_error_inline'   => 'This field is required',

		'ico_error'           => 'error.gif',
		'ico_warning'         => 'warning.gif',
		'ico_help'            => 'help.png',
		'ico_close'           => 'close.png',

		'form_method'         => 'post',
		'cache'               => true,

		'attr_required'       => false,
		'file_captcha'        => 'validation_png.php'
	);

	return $default;
}
