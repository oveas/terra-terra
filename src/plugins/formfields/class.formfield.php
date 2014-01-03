<?php
/**
 * \file
 * This file defines a formfield element plugin
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
 * Abstract base class for Formfield elements plugins
 * \brief Formfield
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

abstract class FormFieldPlugin extends BaseElement
{
	/**
	 * Field type
	 */
	protected $type;

	/**
	 * Field value
	 */
	protected $value;

	/**
	 * Boolean indicating a disabled field when true
	 */
	protected $disabled;

	/**
	 * Boolean indicating a readonly field when true
	 */
	protected $readonly;

	/**
	 * Class constructor;
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		_TT::init(__FILE__, __LINE__);
		$this->disabled = false;
		$this->readonly = false;
	}

	/**
	 * Set the field value
	 * \param[in] $_value Field value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setValue($_value)
	{
		$this->value = $_value;
	}

	/**
	 * Set the Disabled boolean
	 * \param[in] $_value Value indicating true (default) or false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setDisabled($_value = true)
	{
		$this->disabled = toBool($_value, array('yes', 'y', 'true', '1', 'disabled'));
	}

	/**
	 * Set the Readonly boolean
	 * \param[in] $_value Value indicating true (default) or false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setReadonly($_value = true)
	{
		$this->readonly = toBool($_value, array('yes', 'y', 'true', '1', 'readonly'));
	}

	/**
	 * Give the field type
	 * \return Field type
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Return the attributes for an HTML formfield in the " attrib='value' [...]" format
	 * \param[in] $_ignore Array with attributes names that should be ignored, e.g. for a textarea, the value
	 * is not returned as an attribute.
	 * \return Textstring with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function getGenericFieldAttributes($_ignore = array())
	{
		$_htmlCode = parent::getAttributes($_ignore);
		if (!in_array('value', $_ignore)) {
			$_htmlCode .= " value='$this->value'";
		}
		if (!in_array('disabled', $_ignore) && ($this->disabled === true)) {
			$_htmlCode .= " disabled='disabled'";
		}
		if (!in_array('readonly', $_ignore) && ($this->readonly === true)) {
			$_htmlCode .= " readonly='readonly'";
		}
		return $_htmlCode;
	}
	/**
	 * This is a dummy implementation for the showElement() method, since it will be reimplemented
	 * by the fieldtype specific classes.
	 * \see BaseElement::showElement()
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		return '';
	}

}

/*
 * Register this class and all status codes
 */
Register::registerClass ('FormField');

//Register::setSeverity (TT_DEBUG);

//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
Register::setSeverity (TT_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

Register::setSeverity (TT_WARNING);
Register::registerCode ('FORMFIELD_IVVAL');
Register::registerCode ('FORMFIELD_IVVALFORMAT');
Register::registerCode ('FORMFIELD_NOVAL');
Register::registerCode ('FORMFIELD_NOSUCHVAL');
Register::registerCode ('FORMFIELD_VALEXISTS');

//Register::setSeverity (TT_BUG);

//Register::setSeverity (TT_ERROR);
//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
