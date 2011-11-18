<?php
/**
 * \file
 * This file defines a radio formfield element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
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
	 */
	private $selected;

	/**
	 * Array with labels for each option
	 */
	private $label;

	/**
	 * List with options (values)
	 */
	private $options;

	/**
	 * Indexed array with HTML ids for the options
	 */
	private $option_ids;

	/**
	 * Class constructor;
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addOption($_value, $_id = '')
	{
		if (in_array($_value, $this->options)) {
			$this->setStatus (FORMFIELD_VALEXISTS, $_value, $this->name);
		} else {
			$this->options[] = $_value;
			$this->option_ids[$_value] = (($_id === '') ? ('option'.$_value) : $_id);
		}
	}

	/**
	 * Define the selected value
	 * \param[in] $_value Preselected value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setSelected($_value)
	{
		if (!in_array($_value, $this->value)) {
			$this->setStatus (FORMFIELD_NOSUCHVAL, $_value, $this->name);
		} else {
			$this->selected = $_value;
		}
	}

	/**
	 * Set the label for the given option
	 * \param[in] $_val Value for which the label is set
	 * \param[in] $_lbl The label, either as fixed HTML or as a reference to a (label) object
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \return Array with textstrings for each radio button in this this set.
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
