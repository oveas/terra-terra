<?php
/**
 * \file
 * This file defines a formfield element
 * \version $Id: class.formfield.php,v 1.1 2010-12-03 12:07:43 oscar Exp $
 */

/**
 * \ingroup OWL_UI_LAYER
 * Abstract base class for Formfield elements
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormField extends BaseElement
{
	/**
	 * Field type
	 * \protected
	 */
	protected $type;

	/**
	 * Name of the formfield
	 * \protected
	 */
	protected $name;

	/**
	 * Field value
	 * \protected
	 */
	protected $value;

	/**
	 * Boolean indicating a disabled field when true
	 * \protected
	 */
	protected $disabled;

	/**
	 * Boolean indicating a readonly field when true
	 * \protected
	 */
	protected $readonly;

	/**
	 * Class constructor; 
	 * \public
	 */
	public function __construct ()
	{
		$this->disabled = false;
		$this->readonly = false;
	}

	/**
	 * Set the field name
	 * \param[in] $_value Field name
	 * \public
	 */
	public function setName($_value)
	{
		$this->name = $_value;
	}

	/**
	 * Set the field value
	 * \param[in] $_value Field value
	 * \public
	 */
	public function setValue($_value)
	{
		$this->value = $_value;
	}

	/**
	 * Set the Disabled boolean
	 * \param[in] $_value Value indicating true (default) or false
	 * \public
	 */
	public function setDisabled($_value = true)
	{
		$this->disabled = toStrictBoolean($_value, array('yes', 'y', 'true', '1', 'disabled'));
	}

	/**
	 * Set the Readonly boolean
	 * \param[in] $_value Value indicating true (default) or false
	 * \public
	 */
	public function setReadonly($_value = true)
	{
		$this->readonly = toStrictBoolean($_value, array('yes', 'y', 'true', '1', 'readonly'));
	}

	/**
	 * Give the field type
	 * \return Field type
	 * \public
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Return the attributes for an HTML formfield in the " attrib='value' [...]" format
	 * \protected
	 * \param[in] $_ignore Array with attributes names that should be ignored, e.g. for a textarea, the value
	 * is not returned as an attribute.
	 * \return Textstring with the HTML code
	 */
	protected function getGenericFieldAttributes($_ignore = array())
	{
		$_htmlCode = '';
		if (!in_array('id', $_ignore) && !empty($this->id)) {
			$_htmlCode .= " id='$this->id'";
		}
		if (!in_array('class', $_ignore) && !empty($this->class)) {
			$_htmlCode .= " class='$this->class'";
		}
		if (!in_array('style', $_ignore) && !empty($this->style)) {
			$_htmlCode .= " style='$this->style'";
		}
		if (!in_array('name', $_ignore) && !empty($this->name)) {
			$_htmlCode .= " name='$this->name'";
		}
		if (!in_array('value', $_ignore)) {
			$_htmlCode .= " value='$this->value'";
		}
		if (!in_array('disabled', $_ignore) && ($this->disabled === true)) {
			$_htmlCode .= " disabled='disabled'";
		}
		if (!in_array('readonly', $_ignore) && ($this->readonly === true)) {
			$_htmlCode .= " readonly='readonly'";
		}
		$_htmlCode .= $this->getEvents();
		return $_htmlCode;
	}

}

/*
 * Register this class and all status codes
 */
Register::register_class ('FormField');

//Register::set_severity (OWL_DEBUG);

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
Register::set_severity (OWL_SUCCESS);
//Register::register_code ('FORM_RETVALUE');

Register::set_severity (OWL_WARNING);
Register::register_code ('FORMFIELD_IVVAL');
Register::register_code ('FORMFIELD_IVVALFORMAT');
Register::register_code ('FORMFIELD_NOVAL');
Register::register_code ('FORMFIELD_NOSUCHVAL');
Register::register_code ('FORMFIELD_VALEXISTS');

//Register::set_severity (OWL_BUG);

//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
