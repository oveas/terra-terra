<?php
/**
 * \file
 * This file defines the Table plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.table.php,v 1.2 2011-10-16 11:11:44 oscar Exp $
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
 * Class defining Table container plugin
 * \brief Table Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 9, 2011 -- O van Eijk -- initial version
 * \version May 27, 2011 -- O van Eijk -- Rewritten the UI object as plugin
 */

class ContainerTablePlugin extends ContainerPlugin
{

	/**
	 * Border
	 */
	private $border;

	/**
	 * Array with pointers to the row objects
	 */
	private $rows;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'table';
		$this->rows = array();
		$this->border = '';
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
	 * \param[in] $_content Content for the row container. Not used here but required by syntax
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \param[in] $_type_attribs Array with container type specific arguments. Here, only header => true/fals
	 * is supported.
	 * \return Pointer to the row object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addRow($_content = '', array $_attribs = array(), array $_type_attribs = array())
	{
		if (!array_key_exists('header', $_type_attribs)) {
			$_type_attribs['header'] = false;
		}
		$_row = new Container('tablerow', '', $_attribs, $_type_attribs);
		$this->rows[] = $_row;
		return $_row;
	}

	/**
	 * Show the TABLE specific arguments.
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$_htmlCode = '';
		if ($this->border !== '') {
			$_htmlCode .= ' border="' . $this->border . '"';
		}
		return $_htmlCode;
	}

	/**
	 * Retrieve all table rows
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getContent()
	{
		$_html = '';
		foreach ($this->rows as $_row) {
			$_html .= $_row->showElement();
		}
		return $_html;
	}
}
