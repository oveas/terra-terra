<?php
/**
 * \file
 * This file defines the link plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.link.php,v 1.4 2011-06-12 11:03:38 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Class defining Div container plugin
 * \brief LinkContainer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

class ContainerLinkPlugin extends ContainerPlugin
{
	/**
	 * Hypertext reference, defaults to '#'
	 */
	private $href;

	/**
	 * Link target
	 */
	private $target;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'a';
		$this->href = '#';
		$this->target = '';
	}

	/**
	 * Set the href attribute
	 * \param[in] $_url The hypertext reference
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setHref($_url)
	{
		$this->href = $_url;
	}

	/**
	 * Set the target attribute
	 * \param[in] $_window Browser window identification
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setTarget($_window)
	{
		$this->target = $_window;
	}

	/**
	 * Set a dispatcher as href attribute
	 * \note This overwrites the href attribute
	 * \param[in] $_dispatcher OWL dispatcher as string or array, \see Dispatcher::composeDispatcher()
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setDispatcher($_dispatcher)
	{
		$_disp = OWL::factory('Dispatcher', 'bo');
		$this->href = OWL_CALLBACK_URL . '?' . OWL_DISPATCHER_NAME . '=' . $_disp->composeDispatcher($_dispatcher);
	}

	/**
	 * Show the A specific arguments.
	 * \return HTML code for use in the A tag
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$_htmlCode = ' href="' . $this->href . '"';
		if ($this->target !== '') {
			$_htmlCode .= ' target="' . $this->target . '"';
		}
		return $_htmlCode;
	}
}
