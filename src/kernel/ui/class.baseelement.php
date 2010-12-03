<?php
/**
 * \file
 * This file defines the top-level BaseElement class
 * \version $Id: class.baseelement.php,v 1.1 2010-12-03 12:07:43 oscar Exp $
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
	 * Set the element Class
	 * \param[in] $_value Class name
	 * \public
	 */
	public function setClass($_value)
	{
		$this->class = $_value;
	}

	protected function getEvents()
	{
		// TODO Implement events
	}

	/**
	 * Return the general element attributes that are set
	 * \return string attributes in HTML format (' fld="value"...)
	 * \protected
	 */
	protected function getAttributes()
	{
		$_attrib = '';
		if ($this->id != '') {
			$_attrib .= ' id="' . $this->id . '"';
		}
		if ($this->class != '') {
			$_attrib .= ' class="' . $this->class . '"';
		}
		if ($this->style != '') {
			$_attrib .= ' style="' . $this->style . '"';
		}
		return $_attrib;
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

//Register::set_severity (OWL_WARNING);

//Register::set_severity (OWL_BUG);

//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
