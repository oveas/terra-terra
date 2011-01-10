<?php
/**
 * \file
 * This file defines the top-level BaseElement class
 * \version $Id: class.baseelement.php,v 1.2 2011-01-10 18:45:59 oscar Exp $
 */

/**
 * \ingroup OWL_UI_LAYER
 * Abstract base class for all DOM elements
 * \brief DOM Element base class 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 29, 2008 -- O van Eijk -- initial version
 */
abstract class BaseElement extends _OWL
{
	/**
	 * Class specification
	 * \protected
	 */
	protected $class = '';

	/**
	 * Element style
	 * \protected
	 */
	protected $style = '';

	/**
	 * Array with javascript events
	 * \protected
	 */
	protected $events = array();

	/**
	 * Name of the element
	 * \protected
	 */
	protected $name = '';

	/**
	 * Element ID
	 * \protected
	 */
	protected $id = '';
	
	/**
	 * Set the element ID
	 * \param[in] $_value Identification
	 * \public
	 */
	public function setId($_value)
	{
		$this->id = $_value;
	}

	/**
	 * Set the element name
	 * \param[in] $_value Element name
	 * \public
	 */
	public function setName($_value)
	{
		$this->name = $_value;
	}

	/**
	 * Set the element style
	 * \param[in] $_value Element style
	 * \public
	 */
	public function setStyle($_value)
	{
		$this->style = $_value;
	}

	/**
	 * Set the element Class
	 * \param[in] $_value Class name
	 * \public
	 */
	public function setClass($_value)
	{
		$this->class = $_value;
	}

	/**
	 * Add an event to the events array
	 * \param[in] $_event Javascript event name (onXxx)
	 * \param[in] $_action Javascript function or code
	 * \param[in] $_add Boolean; when true, the action will be added if the event name already exists.
	 * Default is false; overwrite the action for this event.
	 */
	public function setEvent($_event, $_action, $_add = false)
	{
		// TODO Validate the event and action
		if (!preg_match('/;$/', $_action)) {
			$_action .= ';';
		}

		if (array_key_exists($_event, $this->events) && $_add === true) {
			$this->events[$_event] .= $_action;
		} else {
			$this->events[$_event] = $_action;
		}
	}

	/**
	 * Return the HTML attribute list for events that where set for this element.
	 * \return string, HTML code
	 * \private
	 */
	private function getEvents()
	{
		$_htmlCode = '';
		foreach ($this->events as $_e => $_a) {
			$_htmlCode .= " $_e='$_a'";
		}
		return $_htmlCode;
	}

	/**
	 * Set the attributes of the DOM element by calling the 'set&lt;Attrib&gt;' method for this class.
	 * \param[in] $_attribs Array with attributes in the format attrib=>value
	 * \return Severity level. The status increased when a set&lt;Attrib&gt method does not exist.
	 */
	protected function setAttributes($_attribs)
	{
		foreach ($_attribs as $_k => $_v) {
			$_method = 'set' . ucfirst($_k);
			if (method_exists($this, $_method)) {
				$this->$_method($_v);
			} else {
				$this->set_status (DOM_IVATTRIB, array($_k));
				return $this->severity;
			}
		}
		return $this->severity;
	}

	/**
	 * Return the general element attributes that are set
	 * \param[in] $_ignore Array with attributes names that should be ignored
	 * \return string attributes in HTML format (' fld="value"...)
	 * \protected
	 */
	protected function getAttributes($_ignore = array())
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
		if (!in_array('events', $_ignore)) {
			$_htmlCode .= self::getEvents();
		}
		return $_htmlCode;
	}
}

/*
 * Register this class and all status codes
 */
Register::register_class ('DOMElement');

//Register::set_severity (OWL_DEBUG);

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
Register::set_severity (OWL_SUCCESS);
//Register::register_code ('FORM_RETVALUE');

Register::set_severity (OWL_WARNING);
Register::register_code('DOM_IVATTRIB');

//Register::set_severity (OWL_BUG);

//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
