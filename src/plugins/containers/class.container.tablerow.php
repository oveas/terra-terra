<?php
/**
 * \file
 * This file defines the Tablerow plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.tablerow.php,v 1.1 2011-05-27 12:42:20 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Class defining Tablerow container plugin
 * \brief Tablerow Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 9, 2011 -- O van Eijk -- initial version
 * \version May 27, 2011 -- O van Eijk -- Rewritten the UI object as plugin
 */

class ContainerTablerowPlugin extends ContainerPlugin
{

	/**
	 * Array with pointers to the tablecell objects
	 */
	private $cells;

	/**
	 * Boolean indicating this is a head-row
	 */
	private $header;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'tr';
		$this->cells = array();
	}

	/**
	 * Make this row a header row.
	 * \param[in] $_value True or false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setHeader ($_value = true)
	{
		$this->header = $_value;
		if (count($this->cells) > 0) {
			foreach ($this->cells as $_cell) {
				$_cell->setAttributes(array('header' => $_value));
			}
		}
	}

	/**
	 * Add a new tablecell
	 * \param[in] $_content HTML code that will be placed in the cell
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \param[in] $_type_attribs Array with container type specific arguments. Here, only header => true/fals
	 * is supported.
	 * \return Reference to the cell object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addCell($_content = '&nbsp;', array $_attribs = array(), array $_type_attribs = array())
	{
		$_cell = new Container('tablecell', $_content, $_attribs, $_type_attribs);
		$this->cells[] = $_cell;
		return $_cell;
	}

	/**
	 * Show the tablerow specific arguments.
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		return '';
	}

	/**
	 * Retrieve all table cells
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getContent()
	{
		$_html = '';
		foreach ($this->cells as $_cell) {
			$_html .= $_cell->showElement();
		}
		return $_html;
	}
}
