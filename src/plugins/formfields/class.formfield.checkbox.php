<?php
/**
 * \file
 * This file defines a checkbox formfield element
	 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.formfield.checkbox.php,v 1.3 2011-05-02 12:56:13 oscar Exp $
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
	 */
	private $checked;

	/**
	 * Class constructor; 
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->type = 'checkbox';
	}

	/**
	 * Set the Checked boolean
	 * \param[in] $_value Value indicating true (default) or false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setChecked($_value = true)
	{
		$this->checked = toStrictBoolean($_value, array('yes', 'y', 'true', '1', 'checked', 'selected'));
	}

	/**
	 * Reimplement; value defaults to 1 for checkboxes
	 * \param[in] $_value Field value
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \return Textstring with the complete code for the form element
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
