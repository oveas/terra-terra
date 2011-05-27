<?php
/**
 * \file
 * This file defines the list plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.list.php,v 1.1 2011-05-27 12:42:20 oscar Exp $
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
