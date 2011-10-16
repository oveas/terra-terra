<?php
/**
 * \file
 * This file defines the list plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.list.php,v 1.2 2011-10-16 11:11:44 oscar Exp $
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
 * Class defining list container plugin, ordered or unordered
 * \brief List Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 27, 2011 -- O van Eijk -- initial version
 */

class ContainerListPlugin extends ContainerPlugin
{

	/**
	 * Array with pointers to the item objects
	 */
	private $items;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'ul'; // Defaults to <ul>
		$this->items = array();
	}

	/**
	 * Set the listtype by changing the type property
	 * \param[in] $_value True of False
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setOrdered($_value = true)
	{
		$this->type = (($_value === true) ? 'ol' : 'ul');
	}

	/**
	 * Add a new list item
	 * \param[in] $_content Content for the item container
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \param[in] $_type_attribs Array with container type specific arguments
	 * \return Pointer to the item object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addItem($_content = '', array $_attribs = array(), array $_type_attribs = array())
	{
		$_item = new Container('item', $_content, $_attribs, $_type_attribs);
		$this->items[] = $_item;
		return $_item;
	}

	/**
	 * Show the list specific arguments.
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		return '';
	}

	/**
	 * Retrieve all list items
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getContent()
	{
		$_html = '';
		foreach ($this->items as $_item) {
			$_html .= $_item->showElement();
		}
		return $_html;
	}
}
