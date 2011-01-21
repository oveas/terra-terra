<?php
/**
 * \file
 * This file defines the div plugin for containers
 * \version $Id: class.container.div.php,v 1.2 2011-01-21 10:18:27 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Class defining Div container plugin
 * \brief DivContainer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

class ContainerDivPlugin extends ContainerPlugin
{

	/**
	 * Container constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'div';
	}

	/**
	 * The DIV tag has no specific arguments, but this method is required by syntax
	 * \return Empty string
	 */
	public function showElement()
	{
		return '';
	}
	
}
