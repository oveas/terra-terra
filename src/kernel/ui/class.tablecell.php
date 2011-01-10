<?php
/**
 * \file
 * This file defines a tablecell element
 * \version $Id: class.tablecell.php,v 1.1 2011-01-10 18:45:59 oscar Exp $
 */

/**
 * \ingroup OWL_UI_LAYER
 * Class for Table cell elements
 * \brief Tablecell
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 9, 2011 -- O van Eijk -- initial version
 */

class Tablecell extends BaseElement
{
	/**
	 * Rowspan
	 * \private
	 */
	private $rowspan = '';

	/**
	 * Colspan
	 * \private
	 */
	private $colspan = '';
	
	/**
	 * HTML content
	 * \private
	 */
	private $content;

	/**
	 * Class constructor;
	 * \param[in] $_content HTML that will be placed in the table cell
	 * \public
	 */
	public function __construct ($_content = '&nbsp;')
	{
		_OWL::init();
		$this->content = $_content;
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
	 * Set the rowspan value
	 * \param[in] $_value Rowspan value
	 * \public
	 */
	public function setRowspan($_value)
	{
		$this->rowspan = $_value;
	}

	/**
	 * Set the colspan value
	 * \param[in] $_value Rowspan value
	 * \public
	 */
	public function setColspan($_value)
	{
		$this->colspan = $_value;
	}
	
	/**
	 * Get the HTML code to display the tablecell
	 * \public
	 * \return string with the HTML code
	 */
	public function getTablecell()
	{
		$_htmlCode = "\t<td";
		if (!empty($this->rowspan)) {
			$_htmlCode .= ' rowspan="' . $this->rowspan . '"';
		}
		if (!empty($this->colspan)) {
			$_htmlCode .= ' colspan="' . $this->colspan . '"';
		}
		$_htmlCode .= $this->getAttributes();
		$_htmlCode .= '>' . $this->content . "</td>\n";
		return $_htmlCode;
	}
}

/*
 * Register this class and all status codes
 */
Register::register_class ('Tablecell');

//Register::set_severity (OWL_DEBUG);

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
//Register::set_severity (OWL_WARNING);
//Register::set_severity (OWL_BUG);
//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
