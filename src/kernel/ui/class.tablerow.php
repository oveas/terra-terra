<?php
/**
 * \file
 * This file defines a table row element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.tablerow.php,v 1.5 2011-05-02 12:56:14 oscar Exp $
 */

if (!OWLloader::getClass('tablecell')) {
	trigger_error('Error loading the Tablecell class', E_USER_ERROR);
}

/**
 * \ingroup OWL_UI_LAYER
 * Class for Table row elements
 * \brief Tablerow
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 9, 2011 -- O van Eijk -- initial version
 */
class Tablerow extends BaseElement
{
	/**
	 * Array with pointers to the tablecell objects
	 */
	private $cells = array();

	/**
	 * Class constructor;
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ()
	{
		_OWL::init();
	}

	/**
	 * Add a new tablecell
	 * \param[in] $_content HTML code that will be placed in the cell
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \return Reference to the cell object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addCell($_content = '&nbsp;', array $_attribs = array())
	{
		$_cell = new Tablecell($_content);
		$_cell->setAttributes($_attribs);
		$this->cells[] = $_cell;
		return $_cell;
	}
	
	/**
	 * Get the HTML code to display the tablerow
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$_htmlCode = '<tr';
		$_htmlCode .= $this->getAttributes();
		$_htmlCode .= ">\n";
		foreach ($this->cells as $_cell) {
			$_htmlCode .= $_cell->showElement();
		}
		$_htmlCode .= "</tr>\n";
		return $_htmlCode;
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('Tablerow');

//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::setSeverity (OWL_WARNING);
//Register::setSeverity (OWL_BUG);
//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
