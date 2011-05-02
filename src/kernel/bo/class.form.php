<?php
/**
 * \file
 * This file defines the HTML Form class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.form.php,v 1.10 2011-05-02 12:56:14 oscar Exp $
 */

OWLloader::getClass('formfield', OWL_PLUGINS . '/formfields');

/**
 * \ingroup OWL_BO_LAYER
 * Define an HTML Form.
 * \brief Form Element class 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 29, 2008 -- O van Eijk -- initial version
 */
class Form extends BaseElement
{

	/**
	 * Array holding all field objects
	 */
	private $fields;

	/**
	 * String holding dispatch info.
	 */
	private $dispatcher;

	/**
	 * Method; post (default) or get
	 */
	private $method;

	/**
	 * Form encoding; application/x-www-form-urlencoded (defauls) or multipart/form-data
	 */
	private $enctype;

	/**
	 * Class constructor
	 * \param[in] $_dispatcher OWL dispatcher as string or array, \see Dispatcher::composeDispatcher()
	 * \param[in] $_attribs Indexed array with the HTML attributes 
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($_dispatcher, $_attribs = array())
	{
		_OWL::init();
		$this->fields = array();
		$this->method = 'POST';
		$this->enctype = 'application/x-www-form-urlencoded';

		$_disp = OWL::factory('Dispatcher', 'bo');
		$this->dispatcher = $_disp->composeDispatcher($_dispatcher);

		if (count($_attribs) > 0) {
			parent::setAttributes($_attribs);
		}
	}

	/**
	 * Set the form method
	 * \param[in] $method The method, GET and POST are supported
	 * \return Severity level of the object status
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setMethod ($method)
	{
		if ($method != 'GET' && $method != 'POST') {
			$this->setStatus (FORM_IVMETHOD, $method);
			return ($this->severity);
		}
		$this->method = $method;
		return ($this->severity);
	}

	/**
	 * Set the form encoding
	 * \param[in] $enctype The encoding type, multipart/form-data and application/x-www-form-urlencoded
	 * are supported
	 * \return Severity level of the object status
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setEncoding ($enctype)
	{
		$enctype = strtolower($enctype);
		if ($enctype != 'multipart/form-data' && $enctype != 'application/x-www-form-urlencoded') {
			$this->setStatus (FORM_IVENCODING, $enctype);
			return ($this->severity);
		}
		$this->enctype = $enctype;
		return ($this->severity);
	}
	
	/**
	 * Add a formfield to the formelement
	 * \param[in] $type Field type
	 * \param[in] $name Field name
	 * \param[in] $value Optional field value. For a selectlist type this must be an array, see FormFieldSelect::setValue()
	 * \param[in] $attributes Indexed array with additional values in the format, where the key must be a supported attributed
	 * for the given type.
	 * \return Reference to the field object, or the severity in case of errors
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addField($type, $name, $value = '', $attributes = array())
	{
		if (in_array($name, $this->fields)) {
			// TODO This must be an addValue method; trigger an error here
//			// Object already exists, so it must be a multivalue (radio or select).
//			if (method_exists($this->fields[$name], 'addValue')) {
//				if (in_array(''))
//				$this->fields[$name]->addValue($value);
//			} else {
//				$this->setStatus (FORM_NOMULTIVAL, array($name, $this->fields[$name]->getType()));
//				return $this->severity;
//			}
		} else {
			// Add a new object to the fieldlist
			$_subtype = '';

			if ($type == 'text' || $type == 'hidden' || $type == 'password') {
				$_subtype = $type;
				$type = 'text';
			}
			if ($type == 'button' || $type == 'image' || $type == 'submit' || $type == 'reset') {
				$_subtype = $type;
				$type = 'button';
			}

			if (!OWLloader::getClass('formfield.'.$type, OWL_PLUGINS . '/formfields')) {
				$this->setStatus (FORM_NOCLASS, $type);
				return ($this->severity);
			}
			$_className = 'FormField' . ucfirst($type) . 'Plugin';

			if (!($this->fields[$name] = new $_className($_subtype))) {
				$this->setStatus (FORM_IVCLASSNAME, array($type, $_className));
				return ($this->severity);
			}

			$this->fields[$name]->setName($name);
			$this->fields[$name]->setValue($value);
		}

		if (count($attributes) > 0) {
			$this->setFieldAttributes($name, $attributes);
		}
		return $this->fields[$name];
	}

	/**
	 * Set one or more formfield attributes
	 * \param[in] $index Index of the fieldobject
	 * \param[in] $attributes array with object name and values in the format ('attrib' => 'value')
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setFieldAttributes($index, $attributes)
	{
		foreach ($attributes as $_k => $_v) {
			$_method = 'set' . ucfirst($_k);
			if (method_exists($this->fields[$index], $_method)) {
				$this->fields[$index]->$_method($_v);
			} else {
				$this->setStatus (FORM_NOATTRIB, array($_k, $this->fields[$index]->getType()));
			}
		}
	}

	/**
	 * Set one or more formfield events
	 * \param[in] $index Index of the fieldobject
	 * \param[in] $events array with eventnames and JavaScript code in the format ('event' => 'action')
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setFieldEvents($index, $events)
	{
		foreach ($events as $_e => $_a) {
			$this->fields[$index]->setEvent($_e, $_a);
		}
	}

	/**
	 * Get the HTML code for a given field
	 * \param[in] $name Fieldname
	 * \return HTML code defining the field
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showField($name)
	{
		if (in_array($name, $this->fields)) {
			$this->setStatus (FORM_NOSUCHFIELD, array($name));
			return null;
		}
		return $this->fields[$name]->showElement();
	}

	/**
	 * Return the form code to open the form
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function openForm()
	{
		return '<form action="'.$_SERVER['PHP_SELF'].'" '
			. parent::getAttributes()
			. ' enctype="'.$this->enctype.'"'
			. ' method="'.$this->method.'">'."\n";
	}

	/**
	 * Close the form and set a hidden field defining the dispatcher
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function closeForm()
	{
		$this->addField('hidden', OWL_DISPATCHER_NAME, $this->dispatcher);
		return $this->showField(OWL_DISPATCHER_NAME) . '</form>'."\n";
	}

	/**
	 * Display the form
	 * \see BaseElement::showElement()
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		return $this->openForm() . $this->getContent() . $this->closeForm();
	}
}
/**
 * \example exa.form.php
 * This example shows how to create a form and display it. The example code in the comments
 * can be used in the mainpage, e.g. index.php.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

/*
 * Register this class and all status codes
 */
Register::registerClass ('Form');

//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
Register::setSeverity (OWL_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

Register::setSeverity (OWL_WARNING);
Register::registerCode ('FORM_NOMULTIVAL');
Register::registerCode ('FORM_IVMETHOD');
Register::registerCode ('FORM_IVENCODING');

Register::setSeverity (OWL_BUG);
Register::registerCode ('FORM_IVCLASSNAME');

Register::setSeverity (OWL_ERROR);
Register::registerCode ('FORM_NOCLASS');
Register::registerCode ('FORM_NOATTRIB');
Register::registerCode ('FORM_NOSUCHFIELD');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
