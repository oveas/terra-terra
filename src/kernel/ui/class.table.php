<?php
/**
 * \file
 * This file defines a table element
	 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.table.php,v 1.5 2011-05-02 12:56:14 oscar Exp $
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
	 */
	private $border = '';

	/**
	 * Array with pointers to the row objects
	 */
	private $rows = array();

	/**
	 * Class constructor;
	 * \param[in] $_attribs Indexed array with the HTML attributes 
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setBorder($_value)
	{
		$this->border = $_value;
	}

	/**
	 * Add a new tablerow
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \return Pointer to the row object
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
Register::registerClass ('Table');

//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::setSeverity (OWL_WARNING);
//Register::setSeverity (OWL_BUG);
//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
