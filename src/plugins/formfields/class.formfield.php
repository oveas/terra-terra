<?php
/**
 * \file
 * This file defines a formfield element plugin
 * \version $Id: class.formfield.php,v 1.3 2011-04-27 11:50:07 oscar Exp $
 */

/**
 * \ingroup OWL_PLUGINS
 * Abstract base class for Formfield elements plugins
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

abstract class FormFieldPlugin extends BaseElement
{
	/**
	 * Field type
	 * \protected
	 */
	protected $type;

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
		_OWL::init();
		$this->disabled = false;
		$this->readonly = false;
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

//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
Register::setSeverity (OWL_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

Register::setSeverity (OWL_WARNING);
Register::registerCode ('FORMFIELD_IVVAL');
Register::registerCode ('FORMFIELD_IVVALFORMAT');
Register::registerCode ('FORMFIELD_NOVAL');
Register::registerCode ('FORMFIELD_NOSUCHVAL');
Register::registerCode ('FORMFIELD_VALEXISTS');

//Register::setSeverity (OWL_BUG);

//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
