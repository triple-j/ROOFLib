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

require_once('class.formitemdb.php');

class FI_Select extends FormItemDB {

	protected $options;
	protected $selected;
	protected $_value;



/**
 * Creates a new FI_Script
 *
 * @param String $name The name of the form item. Must be unique within the group or form.
 * @param String $label The label of the form item as printed to the page.
 * @param Array $options An array of parameters and their values. See description()
 */
	public function __construct($name, $label, $options = Array()) {
		parent::__construct($name, $label, $options);

		$defaultValues = Array(
			'options' => Array(),
			'size'=>1,
			'default'=>'-1',
			'default_string'=>' - Please Choose - ',
			'show_default'=>true,
			'onchange'=>null,   
			'other'=>null,
			'other_val'=>'OTHER',
		);

		$this->merge($options, $defaultValues);

		$this->_update_selected();
	}


/**
 * Gets a description of the Form Items additional parameters
 *
 * @param Array $options The list of available options; Default:Array()
 * @param String $default The index to be counted as the default value. In required select boxes, this value would not pass validation; Default:-1
 * @param String $default_string The prompt to display when no option is selected; Default:' - Please Choose - '
 * @param Bool $show_default Whether or not to display a select prompt; Default:true;
 *
 * @return Array The optional parameters which describe this class.
 */
	public static function description () {
		return Array(
			'options'=>self::DE('array', 'The list of available options', 'Array()'),
			'default'=>self::DE('string', 'The index to be counted as the default value. In required select boxes, this value would not pass validation', '-1'),
			'default_string'=>self::DE('string', 'The prompt to display when no option is selected', '\' - Please Choose - \''),
			'show_default'=>self::DE('bool', 'Whether or not to display a select prompt', 'true'),
		);
	}



/**
 * Prints the Javascript required to allow for advanced manipulation
 */
	public function print_js() {
		global $BASE_SCRIPT_ADDED;
		$this->form->js_files []= 'select.js';
		$script = '<script type="text/javascript">$(\'input[name='.$this->name().'_other]\').css(\'display\', \'none\');</script>';
		return $script;
	}


/**
 * Updates the internal representation of the value according to the $_POST values.
 */
	protected function _update_selected() {
		if (! $this->selected) {
			$this->selected = ($this->show_default?$this->default:key($this->options));
		}
		if (isset($_POST[$this->name()])) {
			$this->selected = $_POST[$this->name()];
			if ($this->other != null) { 
				$this->_value = $_POST[$this->name()."_other"];
			}
		}
	}


/**
 * Gets the type of the FormItem
 *
 * @return String "Select";
 */
	public static function getType() {
		return "Select";
	}


/**
 * Gets or Sets the value of the FormItem
 *
 * @param String $input Providing an input indicates that the FormItem should be printed with that default.
 *
 * @return String If using this function as a Getter, gets the value of the item.
 */
	public function value($input = NULL){
		if ($input !== NULL) {
			$this->selected = $input;
		} else {
			$this->_update_selected();
			return $this->selected;
		}
	}


/**
 * Gets the human readable value of the FormItem
 *
 * @return String The option label/human readable value
 */
	public function optionValue(){
		if ($this->other != null && $this->value() == $this->other_val) {
			return $this->_value;
		} else {
			return $this->options[$this->value()];
		}
	}


/**
 * Performs native validation within the FormItem.
 *
 * @param Array $errors An array in which to place errors
 * @param Array $warnings An array in which to place warnings
 * @param Bool $continue A Bool to indicate whether or not the containing FI_Group or Form should break upon completion
 */
	public function check(&$errors, &$warnings, &$continue) {
		$value = $this->value();
		
		if ($this->other != null && $value == $this->other_val && strlen(trim($this->_value)) < 1) {
			$value = $this->default;  // pretend the user didn't select anything
		}
		
		if ($this->default == $value && $this->required) {
			$errors []= Form::ME('error', 'Please enter a value for field: <em>'.$this->label().'</em>', $this, 'This field is required');
		}
		parent::check($errors, $warnings, $continue);
	}


/**
 * Adds the form item to the database.
 *
 * @param DatabaseForm $form The DatabaseForm
 */
	public function addToDB(&$dbForm) {
 		parent::addToDB($dbForm, $this->optionValue());
	}


 /**
 * Recursively renders a tree of optgroups and options for the select
 *
 * @param Array $tree The tree of options, when the value is an array, render that array as an optgroup with the key being the label, otherwise render as an option.
 * @param String $selected_value The value ($key|$value) of the selected option.
 *
 * @return String The HTML for the options.
 */
	private function printGroup($tree, $selected_value = '') {
		$html = '';
		foreach ($tree as $key => $value) {
			if (is_array($value)) {
				$html .= "\t".'<optgroup label="'.htmlentities($key).'">'.$this->printGroup($value, $selected_value).'</optgroup>'."\n";
			} else {
				$is_selected = ((string)$selected_value === (string)$value || (string)$selected_value === (string)$key);
				$html .= "\t".'<option '.(($is_selected)?' selected="selected"':'').' value="'.$key.'">'.$value.'</option>'."\n";
			}
		}
		return $html;
	}

/**
 * Prints the FormItem for the Form
 *
 * @return String The HTML to be printed as a form.
 */
	public function printForm() {
		$html  = '';
		$selected_value = $this->value();
		if ($this->other != null) {
			$this->onchange = 'rf_toggleOther(this, \''.$this->other_val.'\');'.$this->onchange; 
		}
		if ($this->onchange != null) $jschange = 'onchange="'.$this->onchange.'"';                                             
		$html .= $this->printPre().'<select name="'.$this->name().'" size="'.$this->size.'" '.$jschange.'>'."\n";
		if ($this->show_default) {
			$id = $this->name().'_'.$this->default;
			$html .= "\t".'<option '.(((string)$selected_value === (string)$this->default)?' selected="yes"':'').' value="'.$this->default.'">'.$this->default_string.'</option>'."\n";
		}
		
		$html .= $this->printGroup($this->options, $selected_value);
		if ($this->other != null) {
			$html .= "\t".'<option  value="'.$this->other_val.'">'.$this->other.'</option>'."\n";
		}
		$html .= '</select>'.$this->printPost();
		
		if ($this->other != null) {
			$html .= '<input type="text" name="'.$this->name().'_other" value="" />';
			$html .= $this->print_js(); 
		}
		
		if ($this->description) {
			$html .= '<div class="descr">'.$this->description.'</div>';
		}

		return $html;
	}


/**
 * Prints the FormItem for Email
 *
 * @return String The HTML to be printed as an email.
 */
	public function printEmail() {
		$html = $this->optionValue();
		return $html;
	}


}


?>