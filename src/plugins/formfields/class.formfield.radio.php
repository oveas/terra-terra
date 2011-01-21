<?php
/**
 * \file
 * This file defines a radio formfield element
 * \version $Id: class.formfield.radio.php,v 1.3 2011-01-21 10:18:28 oscar Exp $
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
	 * Array with labels for each option
	 * \private
	 */
	private $label;

	/**
	 * List with options (values)
	 * \private
	 */
	private $options;

	/**
	 * Indexed array with HTML ids for the options
	 * \private
	 */
	private $option_ids;

	/**
	 * Class constructor; 
	 * \public
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->type = 'radio';
		$this->options = array();
		$this->optionids = array();
		$this->label = array();
	}

	/**
	 * Add an option to the options array
	 * \param[in] $_value Field value, defaults to 'option&lt;value&gt;'. This argument
	 * is required in combination with a label container.
	 * \param[in] $_id HTML ID for this option
	 * \public
	 */
	public function addOption($_value, $_id = '')
	{
		if (in_array($_value, $this->options)) {
			$this->set_status (FORMFIELD_VALEXISTS, $_value, $this->name);
		} else {
			$this->options[] = $_value;
			$this->option_ids[$_value] = (($_id === '') ? ('option'.$_value) : $_id);
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
	 * Set the label for the given option
	 * \param[in] $_val Value for which the label is set
	 * \param[in] $_lbl The label, either as fixed HTML or as a reference to a (label) object
	 */
	public function setLabel($_val, $_lbl)
	{
		if (is_object($_lbl)) {
			$this->label[$_val] = $_lbl->showElement();
		} else {
			$this->label[$_val] = $_lbl;
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
		foreach ($this->options as $_val) {
			$_htmlCode = "<input type='$this->type' value='$_val'"
				. " id='" . $this->option_ids[$_val] . "'";
			if ($this->value == $_val) {
				$_htmlCode .= " checked";
			}
			$_htmlCode .= $this->getGenericFieldAttributes(array('value', 'id')) . '/>';
			if (array_key_exists($_val, $this->label)) {
				$_htmlCode .= $this->label[$_val];
			}
			$_retCode[] = $_htmlCode;
		}
		return $_retCode;
	}
}
