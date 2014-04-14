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

require_once('class.text.php');

class FI_TextArea extends FI_Text {

	protected $wysiwyg;


/**
 * Creates a new FI_Textarea
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);
		$defaultValues = Array(
			'wysiwyg'=>false,
			'wysiwygInputClass'=>'ckeditor'
		);

		$this->merge($options, $defaultValues);
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Textarea";
 */
	public static function getType() {
		return "TextArea";
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Bool $wysiwyg Whether or not to use a WYSIWYG editor; Default:false
 * @param String $wysiwygInputClass The class to append to the textarea to identify it as a WYSIWYG; Default:'ckeditor'
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'wysiwyg'=>self::DE('bool', 'Whether or not to use a WYSIWYG editor', 'false'),
			'wysiwygInputClass'=>self::DE('string', 'The class to append to the textarea to identify it as a WYSIWYG', '\'ckeditor\'')
		);
	}


/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		return '<textarea '.(($this->wysiwyg && $this->wysiwygInputClass)?('class="'.$this->wysiwygInputClass.'" '):'').'id="'.$this->name().'_w" cols="40" rows="4" '.(($this->required() && ($this->required_attr || $this->form->required_attr))?'required="required" ':'') .'name="'.$this->name().'">'.htmlentities($this->value()).'</textarea>'.$this->printDescription();
	}



/**
 * Adds the form info to the DatabaseForm object()
 *
 * @param DatabaseForm $dbForm The DatabaseForm to add fields to
 */
	public function addToDB(&$dbForm) {
		$dbForm->addItem($dbForm->dbName($this->label), $this->value(), 'text');
	}

	/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail() {
		return nl2br($this->value());
	}
}