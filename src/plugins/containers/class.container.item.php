<?php
/**
 * \file
 * This file defines the list item plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.item.php,v 1.1 2011-05-27 12:42:20 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Class defining List item container plugin
 * \brief Listitem Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 27, 2011 -- O van Eijk -- initial version
 */

class ContainerItemPlugin extends ContainerPlugin
{

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'li';
	}

	/**
	 * Show the item specific arguments.
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		return '';
	}
}
