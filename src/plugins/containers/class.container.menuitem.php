<?php
/**
 * \file
 * This file defines the menu item plugin for containers
 * \author Daan Schulpen
 * \copyright{2011} Daan Schulpen
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

if (!class_exists('ContainerItemPlugin') && !TTloader::getClass('container.item', TT_PLUGINS . '/containers')) {
	trigger_error('Error loading the Item container plugin - this class is required by the Menuitem container', E_USER_ERROR);
}

/**
 * \ingroup TT_UI_PLUGINS
 * Class defining menu item container plugin
 * \brief Menuitem Container
 * \author Daan Schulpen
 * \version Nov 29, 2011 -- D Schulpen -- initial version
 */

class ContainerMenuitemPlugin extends ContainerItemPlugin
{

	/**
	 * String containing href attribute for generated link
	 */
	private $link;

	/**
	 * Container constructor
	 * \author Daan Schulpen
	 */
	public function __construct()
	{
		parent::__construct();
		$this->link = new Container('link');
		$this->setHref('#');
	}

	/**
	 * Set the href attribute on the generated link
	 * \param[in] $_url The hypertext reference
	 * \author Daan Schulpen
	 */
	public function setHref($_url)
	{
		$this->link->setHref($_url);
	}

	/**
	 * Make sure class attributes are passed to the Link element
	 * \param[in] $_value Class name
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setClass($_value)
	{
		$this->link->addClass($_value);
	}

	/**
	* Set a dispatcher as href attribute
	* \note This overwrites the href attribute
	* \param[in] $_dispatcher TT dispatcher as string or array, \see Dispatcher::composeDispatcher()
	* \author Daan Schulpen
	*/
	public function setDispatcher($_dispatcher)
	{
		$_disp = TT::factory('Dispatcher', 'bo');
		$this->setHref(TT_CALLBACK_URL . '?' . TT_DISPATCHER_NAME . '=' . $_disp->composeDispatcher($_dispatcher));
	}

	/**
	 * Add an event to the generated link's events array
	 * \param[in] $_event Javascript event name (onXxx)
	 * \param[in] $_action Javascript function or code
	 * \param[in] $_add Boolean; when true, the action will be added if the event name already exists.
	 * Default is false; overwrite the action for this event.
	 * \author Daan Schulpen
	 */
	public function setEvent($_event, $_action, $_add = false)
	{
		$this->link->setEvent($_event, $_action, $_add);
	}

	/**
	 * Set the title
	 * \param[in] $_title The string to display as title
	 * \author Daan Schulpen
	 */
	public function setTitle($_title)
	{
		$this->link->setContent($_title);
	}

	/**
	 * Show the item specific arguments.
	 * \return string with the HTML code
	 * \author Daan Schulpen
	 */
	public function showElement()
	{
		if ($this->link->getHref() === '#') {
			return ' onClick="return false;"';
		} else {
			return '';
		}
	}

	/**
	 * Retrieve HTML text for generated link
	 * \author Daan Schulpen
	 */
	public function getContent()
	{
		// Make sure we get the nested items as will if this is a submenu!
		return $this->link->showElement() . parent::getContent();
	}
}
