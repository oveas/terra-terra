<?php
/**
 * \file
 * This file defines the menu plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.menu.php,v 1.3 2011-06-13 13:54:56 oscar Exp $
 */

if (!class_exists('ContainerListPlugin') && !OWLloader::getClass('container.list', OWL_PLUGINS . '/containers')) {
	trigger_error('Error loading the List container plugin - this class is required by the Menu container', E_USER_ERROR);
}

/**
 * \ingroup OWL_UI_PLUGINS
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
	 * Set the menu type. The type must exist in the plugins/menu location of OWL-JS
	 * as lowercase&lt;typename&gt;.js; that file is loaded from here.
	 *
	 * At the same time, a JavaScript array is created with the name &lt;type&gt;List that
	 * holds all ID's of the menus of this type. The OWL-JS plugin can use this array for handling
	 * and/or initialising the menus.
	 * \param[in] $type The menu type, must exist as an OWL-JS menu plugin
	 * \param[in] $menuID ID to set the div element to that holds this menu. This can be used
	 * by the OWL-JS plugin
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function menuType ($type, $menuID)
	{
		$doc = OWL::factory('Document', OWL_UI_INC);
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
		$_lnk = new Container('link', $_title);
		$_lnk->setHref('#');
		$_lnk->setEvent('onClick', 'return false;');
		$_newMenu = new Container('menu');
		$_subMenu = $this->addItem($_lnk, $_attribs, $_type_attribs);
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
