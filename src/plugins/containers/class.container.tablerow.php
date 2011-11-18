<?php
/**
 * \file
 * This file defines the Tablerow plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
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
