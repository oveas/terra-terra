<?php
/**
 * \file
 * This file defines a table row element
 * \version $Id: class.tablerow.php,v 1.1 2011-01-10 18:45:59 oscar Exp $
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
	 * \private
	 */
	private $cells = array();

	/**
	 * Class constructor;
	 * \public
	 */
	public function __construct ()
	{
		_OWL::init();
	}

	/**
	 * Set the HTML attributes for this row. Calls the parents' protected function
	 * \param[in] $_attribs Indexed array with the HTML attributes
	 * \public
	 */
	public function setAttributes($_attribs = array())
	{
		if (count($_attribs) > 0) {
			parent::setAttributes($_attribs);
		}
	}
	
	/**
	 * Add a new tablecell
	 * \param[in] $_content HTML code that will be placed in the cell
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \public
	 */
	public function addCell($_content, $_attribs = array())
	{
		$_cell = new Tablecell($_content);
		$_cell->setAttributes($_attribs);
		$this->cells[] = $_cell;
	}
	
	/**
	 * Get the HTML code to display the tablerow
	 * \public
	 * \return string with the HTML code
	 */
	public function getTablerow()
	{
		$_htmlCode = '<tr';
		$_htmlCode .= $this->getAttributes();
		$_htmlCode .= ">\n";
		foreach ($this->cells as $_cell) {
			$_htmlCode .= $_cell->getTablecell();
		}
		$_htmlCode .= "<tr>\n";
		return $_htmlCode;
	}
}

/*
 * Register this class and all status codes
 */
Register::register_class ('Tablerow');

//Register::set_severity (OWL_DEBUG);

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
//Register::set_severity (OWL_WARNING);
//Register::set_severity (OWL_BUG);
//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
