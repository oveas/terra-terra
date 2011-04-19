<?php
/**
 * \file
 * This file defines the link plugin for containers
 * \version $Id: class.container.link.php,v 1.2 2011-04-19 13:00:03 oscar Exp $
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
	 * \private
	 */
	private $href;
	
	/**
	 * Link target
	 * \private
	 */
	private $target;

	/**
	 * Container constructor
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
	 */
	public function setHref($_url)
	{
		$this->href = $_url;
	}

	/**
	 * Set the target attribute
	 * \param[in] $_window Browser window identification
	 */
	public function setTarget($_window)
	{
		$this->target = $_window;
	}

	/**
	 * Set a dispatcher as href attribute. NOTE! This overwrites the href attribute!
	 * \param[in] $_dispatcher OWL dispatcher as string or array, \see Dispatcher::composeDispatcher()
	 */
	public function setDispatcher($_dispatcher)
	{
		$_disp = OWL::factory('Dispatcher', 'bo');
		$this->href = $_SERVER['PHP_SELF'] . '?' . OWL_DISPATCHER_NAME . '=' . $_disp->composeDispatcher($_dispatcher);
	}

	/**
	 * Show the A specific arguments. 
	 * \return HTML code for use in the A tag
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
