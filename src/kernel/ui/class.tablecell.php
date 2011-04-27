<?php
/**
 * \file
 * This file defines a tablecell element
 * \version $Id: class.tablecell.php,v 1.4 2011-04-27 11:50:07 oscar Exp $
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
	 * Class constructor;
	 * \param[in] $_content HTML that will be placed in the table cell
	 * \public
	 */
	public function __construct ($_content = '&nbsp;')
	{
		_OWL::init();
		$this->setContent($_content);
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
	public function showElement()
	{
		$_htmlCode = "\t<td";
		if (!empty($this->rowspan)) {
			$_htmlCode .= ' rowspan="' . $this->rowspan . '"';
		}
		if (!empty($this->colspan)) {
			$_htmlCode .= ' colspan="' . $this->colspan . '"';
		}
		$_htmlCode .= $this->getAttributes();
		$_htmlCode .= '>' . $this->getContent() . "</td>\n";
		return $_htmlCode;
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('Tablecell');

//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::setSeverity (OWL_WARNING);
//Register::setSeverity (OWL_BUG);
//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
