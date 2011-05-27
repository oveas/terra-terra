<?php
/**
 * \file
 * This file defines the legend plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.legend.php,v 1.1 2011-05-27 12:42:20 oscar Exp $
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Class defining Legend container plugin for fieldsets
 * \brief Legend Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

class ContainerLegendPlugin extends ContainerPlugin
{

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'legend';
	}

	/**
	 * The LEGEND tag has no specific arguments, but this method is required by syntax
	 * \return Empty string
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		return '';
	}
}
