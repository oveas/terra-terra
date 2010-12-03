<?php
/**
 * \file
 * This file defines a checkbox formfield element
 * \version $Id: class.formfield.checkbox.php,v 1.1 2010-12-03 12:07:42 oscar Exp $
 */

/**
 * \ingroup OWL_UI_LAYER
 * Formfield Checkbox elements
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldCheckbox extends FormField
{
	/**
	 * Boolean set to true when the box is checked
	 * \private
	 */
	private $checked;

	/**
	 * Class constructor; 
	 * \public
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->type = 'checkbox';
	}

	/**
	 * Set the Checked boolean
	 * \param[in] $_value Value indicating true (default) or false
	 * \public
	 */
	public function setChecked($_value = true)
	{
		$this->checked = toStrictBoolean($_value, array('yes', 'y', 'true', '1', 'checked', 'selected'));
	}

	/**
	 * Reimplement; value defaults to 1 for checkboxes
	 * \param[in] $_value Field value
	 * \public
	 */
	public function setValue($_value)
	{
		if ($_value == '') {
			$this->value = 1;
		} else {
			$this->value = $_value;
		}
	}

	/**
	 * Return the HTML code to display the form element
	 * \public
	 * \return Textstring with the complete code for the form element
	 */
	public function getFieldCode ()
	{
		$_htmlCode = "<input type='$this->type'";
		if ($this->checked === true) {
			$_htmlCode .= " checked='$this->checked'";
		}
		$_htmlCode .= $this->getGenericFieldAttributes() . '/>';
		return $_htmlCode;
	}
}


//Register::set_severity (OWL_DEBUG);

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
//Register::register_code ('FORM_RETVALUE');

//Register::set_severity (OWL_WARNING);

//Register::set_severity (OWL_BUG);

//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
