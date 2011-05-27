<?php
/**
 * \file
 * This file defines the Tablecell plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.tablecell.php,v 1.1 2011-05-27 12:42:20 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Class defining Tablecell container plugin
 * \brief Tablecell Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 9, 2011 -- O van Eijk -- initial version
 * \version May 27, 2011 -- O van Eijk -- Rewritten the UI object as plugin
 */

class ContainerTablecellPlugin extends ContainerPlugin
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
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'td';
	}

	/**
	 * Make this cell from row a header by chabging the type
	 * \param[in] $isheader True or false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setHeader ($isheader = true)
	{
		$this->type = (($isheader === true) ? 'th' : 'td');
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
	 * Show the tablecell specific arguments.
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$_htmlCode = '';
		if ($this->rowspan !== null) {
			$_htmlCode .= ' rowspan="' . $this->rowspan . '"';
		}
		if ($this->colspan !== null) {
			$_htmlCode .= ' colspan="' . $this->colspan . '"';
		}
		if ($this->valign !== null) {
			$_htmlCode .= ' valign="' . $this->valign . '"';
		}
		return $_htmlCode;
	}
}
