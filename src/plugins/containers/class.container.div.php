<?php
/**
 * \file
 * This file defines the div plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.div.php,v 1.3 2011-05-02 12:56:14 oscar Exp $
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'div';
	}

	/**
	 * The DIV tag has no specific arguments, but this method is required by syntax
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \return Empty string
	 */
	public function showElement()
	{
		return '';
	}
	
}
