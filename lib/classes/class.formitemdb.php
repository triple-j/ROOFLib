<?php
/**
 * ROOFLib
 * Version 0.7
 * MIT License
 * Ray Minge
 * the@rayminge.com
 *
 * @package ROOFLib 0.7
 */


require_once('class.formitem.php');

class FormItemDB extends FormItem {

/**
 * Creates a new FormItemDB
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'use_as_column_name' => $this->cfg('use_as_column_name'),
		);

		$this->merge($options, $defaultValues);
	}

/**
 * Adds the form item to the database.
 *
 * @param DatabaseForm $form The DatabaseForm
 */
	public function addToDB(&$dbForm, $value = null, $type='varchar') {
		$db_name  = ($this->use_as_column_name == "name") ? $this->name : $this->label;
		$db_value = ($value === null) ? $this->value() : $value;
		$dbForm->addItem($dbForm->dbName($db_name), $db_value, $type);
	}
}