<?php
/**
 * \file
 * This file defines a checkbox formfield element
 * \version $Id: class.formfield.checkbox.php,v 1.2 2011-04-27 11:50:08 oscar Exp $
 */

/**
 * \ingroup OWL_PLUGINS
 * Formfield Checkbox elements
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldCheckboxPlugin extends FormFieldPlugin
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
	public function showElement ()
	{
		$_htmlCode = "<input type='$this->type'";
		if ($this->checked === true) {
			$_htmlCode .= " checked='$this->checked'";
		}
		$_htmlCode .= $this->getGenericFieldAttributes() . '/>';
		return $_htmlCode;
	}
}


//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

//Register::setSeverity (OWL_WARNING);

//Register::setSeverity (OWL_BUG);

//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
