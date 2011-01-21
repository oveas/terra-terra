<?php
/**
 * \file
 * This file defines a table element
 * \version $Id: class.table.php,v 1.3 2011-01-21 16:28:15 oscar Exp $
 */

if (!OWLloader::getClass('tablerow')) {
	trigger_error('Error loading the Tablerow class', E_USER_ERROR);
}

/**
 * \ingroup OWL_UI_LAYER
 * Class for Table elements
 * \brief Table 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 9, 2011 -- O van Eijk -- initial version
 */

class Table extends BaseElement
{
	/**
	 * Border
	 * \private
	 */
	private $border = '';

	/**
	 * Array with pointers to the row objects
	 * \private
	 */
	private $rows = array();

	/**
	 * Class constructor;
	 * \param[in] $_attribs Indexed array with the HTML attributes 
	 * \public
	 */
	public function __construct (array $_attribs = array())
	{
		_OWL::init();
		if (count($_attribs) > 0) {
			parent::setAttributes($_attribs);
		}
	}

	/**
	 * Set the table border
	 * \param[in] $_value Border value
	 * \public
	 */
	public function setBorder($_value)
	{
		$this->border = $_value;
	}

	/**
	 * Add a new tablerow
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \return Pointer to the row object
	 * \public
	 */
	public function addRow(array $_attribs = array())
	{
		$_row = new Tablerow($_attribs);
		$_row->setAttributes($_attribs);
		$this->rows[] = $_row;
		return $_row;
	}

	/**
	 * Get the HTML code to display the table
	 * \public
	 * \return string with the HTML code
	 */
	public function showElement()
	{
		$_htmlCode = '<table';
		if ($this->border !== '') {
			$_htmlCode .= ' border="' . $this->border . '"';
		}
		$_htmlCode .= $this->getAttributes();
		$_htmlCode .= ">\n";
		foreach ($this->rows as $_row) {
			$_htmlCode .= $_row->showElement();
		}
		$_htmlCode .= "</table>\n";
		return $_htmlCode;
	}
}

/*
 * Register this class and all status codes
 */
Register::register_class ('Table');

//Register::set_severity (OWL_DEBUG);

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
//Register::set_severity (OWL_WARNING);
//Register::set_severity (OWL_BUG);
//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
