<?php
/**
 * \file
 * This file defines a tablecell element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.tablecell.php,v 1.7 2011-05-26 12:26:30 oscar Exp $
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
	 */
	private $rowspan = null;

	/**
	 * Colspan
	 */
	private $colspan = null;

	/**
	 * Vertical alignment
	 */
	private $valign = null;
	
	/**
	 * Boolean indicating this cells belongs to a head-row
	 */
	private $isHead;

	/**
	 * Class constructor;
	 * \param[in] $_content HTML that will be placed in the table cell
	 * \param[in] $_head True is this cells belongs to a table head
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($_content = '&nbsp;', $_head = false)
	{
		_OWL::init();
		$this->setContent($_content);
		$this->isHead = $_head;
	}

	/**
	 * Set the rowspan value
	 * \param[in] $_value Rowspan value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setRowspan($_value)
	{
		$this->rowspan = $_value;
	}

	/**
	 * Set the colspan value
	 * \param[in] $_value Rowspan value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setColspan($_value)
	{
		$this->colspan = $_value;
	}

	/**
	 * Set the vertical alignment
	 * \param[in] $_value Vertical alignment
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setValign($_value)
	{
		$this->valign = $_value;
	}

	/**
	 * Make this a header cell
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setHeader ()
	{
		$this->isHead = true;
	}

	/**
	 * Get the HTML code to display the tablecell
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$tag = (($this->isHead===true)?'th':'td');
		$_htmlCode = "\t<$tag";
		if ($this->rowspan !== null) {
			$_htmlCode .= ' rowspan="' . $this->rowspan . '"';
		}
		if ($this->colspan !== null) {
			$_htmlCode .= ' colspan="' . $this->colspan . '"';
		}
		if ($this->valign !== null) {
			$_htmlCode .= ' valign="' . $this->valign . '"';
		}
		$_htmlCode .= $this->getAttributes();
		$_htmlCode .= '>' . $this->getContent() . "</$tag>\n";
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
