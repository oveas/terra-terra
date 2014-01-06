<?php
/**
 * \file
 * This file defines the menu plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

if (!class_exists('ContainerListPlugin') && !TTloader::getClass('container.list', TT_PLUGINS . '/containers')) {
	trigger_error('Error loading the List container plugin - this class is required by the Menu container', E_USER_ERROR);
}

/**
 * \ingroup TT_UI_PLUGINS
 * Class defining menu container plugin, which is basically an unorderd list in a div with
 * some additional methods (like addSubMenu()
 * \brief Menu Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jun 06, 2011 -- O van Eijk -- initial version
 */

class ContainerMenuPlugin extends ContainerListPlugin
{

	/**
	 * Array with pointers to the item objects
	 */
	private $items;

	/**
	 * Array with all lists that have been initialised for JavaScript.
	 */
	static private $knownLists;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'div';
		$this->nested_type = 'ul'; // We want <div [attribs...]><ul> (...) </ul></div> here
		$this->items = array();
		self::$knownLists = array();
	}

	/**
	 * By default, menus are created inside a div. This method removes the div wrapper.
	 * This is mainly useful for submenus
	 */
	public function noWrapper()
	{
		$this->type = 'ul';
		$this->nested_type = null;
	}

	/**
	 * Reimplement the parents option to make sure the type won't change for menu lists; just
	 * ignore everything
	 * \param[in] $_value True of False (ignored)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setOrdered($_value = true)
	{
		;
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
	 * Add a new menuitem
	 * \param[in] $_content Content for the item container
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \param[in] $_type_attribs Array with container type specific arguments
	 * \param[in] $_isSubMenu Boolean which should be true when the menu item is a submenu, ensuring all attributes are
	 * handled by the menu item class.
	 * \return Pointer to the item object
	 * \author Daan Schulpen
	 */
	public function addMenuitem($_content = '', array $_attribs = array(), array $_type_attribs = array(), $_isSubMenu = false)
	{
		if ($_isSubMenu === true) {
			$_item = new Container('menuitem', $_content, array(), array_merge($_attribs, $_type_attribs));
		} else {
			$_item = new Container('menuitem', $_content, $_attribs, $_type_attribs);
		}
		$this->items[] = $_item;
		return $_item;
	}

	/**
	 * Set the menu type. The type must exist in the plugins/menu location of TT-JS
	 * as lowercase&lt;typename&gt;.js; that file is loaded from here.
	 *
	 * At the same time, a JavaScript array is created with the name &lt;type&gt;List that
	 * holds all ID's of the menus of this type. The TT-JS plugin can use this array for handling
	 * and/or initialising the menus.
	 * \param[in] $type The menu type, must exist as an TT-JS menu plugin
	 * \param[in] $menuID ID to set the div element to that holds this menu. This can be used
	 * by the TT-JS plugin
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function menuType ($type, $menuID)
	{
		$doc = TT::factory('Document', 'ui');
		$doc->addJSPlugin('menu', $type);
		$jsListname = $type . 'MenuList';

		if (!in_array($jsListname, self::$knownLists)) {
			$doc->addScript("if (typeof($jsListname) == 'undefined') $jsListname = new Array();");
			self::$knownLists[] = $jsListname;
		}
		$doc->addScript("$jsListname.push('$menuID')");
		$this->setId($menuID);
	}

	/**
	 * Add a submenu to the current menu
	 * \param[in] $_title Title as it will appear in the parent menu
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \param[in] $_type_attribs Array with container type specific arguments
	 * \note The $_attribs and $_type_attribs are used for the menu item pointing to the submenu,
	 * <em>not</em> for the submenu itself. To set attributes like the class for the submenu,
	 * the submenus setAttributes() method must be called after it has been created.
	 * \return Reference to the container object holding the new submenu
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addSubMenu($_title = '', array $_attribs = array(), array $_type_attribs = array())
	{
		$_newMenu = new Container('menu');
		$_newMenu->noWrapper();
		$_subMenu = $this->addMenuitem('', $_attribs, array_merge($_type_attribs, array('title' => $_title)), true);
		$_subMenu->addToContent($_newMenu);
		return $_newMenu;
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
