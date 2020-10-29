<?php
/**
 * \file
 * This file defines a  selectlist formfield element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup TT_PLUGINS
 * Formfield selectlist elements
 * \brief Formfield
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldSelectPlugin extends FormFieldPlugin
{

	/**
	 * Number of visible options
	 */
	private $size;

	/**
	 * Boolean indicating a multiple select
	 */
	private $multiple;

	/**
	 * Array with options for the select list
	 */
	private $options;

	const DefaultOptionGroup = '__TT_OptGroup__';
	/**
	 * Class constructor;
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->type = 'select';
		$this->options = array(self::DefaultOptionGroup => array());
	}

	/**
	 * Define the options
	 * \param[in] $_value 2-Dimensional array with option data. The first level contains indexed arrays
	 * with data for each option in the format:
	 * \code
	 * array (
	 *     'value'    => string  // Required, value that will be submitted
	 *    ,'text'     => string  // Optional, text that will be displayed, defaults to 'value'
	 *    ,'selected' => boolean // Optional, true when this option is selected, defauls to false
	 *    ,'class'    => string  // Optional, class name
	 *    ,'group'    => string  // Optional, label of the optgroup the option belongs to
	 * )
	 * \endcode
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setValue($_value)
	{
		if ($_value == '') {
			// Possible during instantation - can be ignored
			return;
		}
		if (!is_array($_value)) {
			$this->setStatus(__FILE__, __LINE__, FORMFIELD_IVVALFORMAT, array($this->name));
			return;
		}

		foreach ($_value as $_option) {
			if (!is_array($_option)) {
				$this->setStatus(__FILE__, __LINE__, FORMFIELD_IVVALFORMAT, array($this->name));
				return;
			}
			if (!array_key_exists('value', $_option)) {
				$this->setStatus(__FILE__, __LINE__, FORMFIELD_NOVAL, array($this->name));
				return;
			}

			if (array_key_exists('group', $_option)) {
				if (!array_key_exists($_option['group'], $this->options)) {
					$this->options[$_option['group']] = array();
				}
				$_valueArray =& $this->options[$_option['group']];
			} else {
				$_valueArray =& $this->options[self::DefaultOptionGroup];
			}
			if (in_array($_option['value'], $_valueArray)) { // TODO This will only check the current optgroup
				$this->setStatus (__FILE__, __LINE__, FORMFIELD_VALEXISTS, $_option['value'], $this->name);
				return;
			}
			$_nextOption = array();
			$_nextOption['value'] = $_option['value'];
			$_nextOption['text'] = (array_key_exists('text', $_option) ? $_option['text'] : $_option['value']);
			if (array_key_exists('selected', $_option)) {
				$_nextOption['selected'] = toBool($_option['selected'],array('yes', 'y', 'true', '1', 'checked', 'selected'));
			} else {
				$_nextOption['selected'] = false;
			}
			$_nextOption['class'] = (array_key_exists('class', $_option) ? $_option['class'] : '');
			$_valueArray[] = $_nextOption;
		}
	}

	/**
	 * Set the Size attribute
	 * \param[in] $size integer
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setSize ($size)
	{
		if (is_int($size)) {
			$this->size = $size;
		} else {
			$this->setStatus(__FILE__, __LINE__, FORMFIELD_IVVAL, array($size, 'size'));
		}
	}

	/**
	 * Set the Multiple boolean
	 * \param[in] $_value Value indicating true (default) or false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setMultiple($_value = true)
	{
		$this->multiple = toBool($_value);
	}

	/**
	 * Return the HTML code to display the form elements
	 * \return String with the element formcode
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement ()
	{
		if ($this->multiple && !preg_match('/\[\]$/', $this->name)) {
			$this->name .= '[]';
		}
		$_htmlCode = '<select ' . $this->getGenericFieldAttributes(array('value'));
		if (!empty($this->size)) {
			$_htmlCode .= " size='$this->size'";
		}
		if ($this->multiple) {
			$_htmlCode .= " multiple='multiple'";
		}
		$_htmlCode .= '>';

		foreach ($this->options as $_group => $_options) {
			if ($_group != self::DefaultOptionGroup) {
				$_htmlCode .= "<optgroup label='$_group'>";
			}
			foreach ($_options as $_opt) {
				$_htmlCode .= "<option value='" . $_opt['value'] . "'";
				if (!empty($_opt['class'])) {
					$_htmlCode .= " class='" . $_opt['class'] . "'";
				}
				if ($_opt['selected'] === true) {
					$_htmlCode .= " selected='selected'";
				}
				$_htmlCode .= ">" . $_opt['text'] . '</option>';
			}
			if ($_group != self::DefaultOptionGroup) {
				$_htmlCode .= "</optgroup>";
			}
		}
		$_htmlCode .= '</select>';
		return $_htmlCode;
	}
}


//Register::setSeverity (TT_DEBUG);

//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

//Register::setSeverity (TT_WARNING);

//Register::setSeverity (TT_BUG);

//Register::setSeverity (TT_ERROR);
//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
