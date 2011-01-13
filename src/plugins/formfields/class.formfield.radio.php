<?php
/**
 * \file
 * This file defines a radio formfield element
 * \version $Id: class.formfield.radio.php,v 1.1 2011-01-13 11:05:34 oscar Exp $
 */

/**
 * \ingroup OWL_PLUGINS
 * Formfield radio elements
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldRadioPlugin extends FormFieldPlugin
{
	/**
	 * Holds the value that must be preselected
	 * \private
	 */
	private $selected;

	/**
	 * Class constructor; 
	 * \public
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->type = 'radio';
		$this->value = array();
	}

	/**
	 * Reimplement, multiple values are supported
	 * \param[in] $_value Field value
	 * \public
	 */
	public function setValue($_value)
	{
		if (in_array($_value, $this->value)) {
			$this->set_status (FORMFIELD_VALEXISTS, $_value, $this->name);
		} else {
			$this->value[] = $_value;
		}
	}

	/**
	 * Define the selected value
	 * \param[in] $_value Preselected value
	 * \public
	 */
	public function setSelected($_value)
	{
		if (!in_array($_value, $this->value)) {
			$this->set_status (FORMFIELD_NOSUCHVAL, $_value, $this->name);
		} else {
			$this->selected = $_value;
		}
	}

	/**
	 * Return the HTML code to display the form elements
	 * \public
	 * \return Array with textstrings for each radio button in this this set.
	 */
	public function showElement ()
	{
		$_retCode = array();
		foreach ($this->value as $_val) {
			$_htmlCode = "<input type='$this->type' value='$_val'";
			if (!empty($this->selected) && ($this->selected == $_val)) {
				$_htmlCode .= " checked='$this->checked'";
			}
			$_htmlCode .= $this->getGenericFieldAttributes(array('value')) . '/>';
			$_retCode[] = $_htmlCode;
		}
		return $_retCode;
	}
}
