<?php
/**
 * \file
 * This file defines the standard container plugin
 * \version $Id: class.container.php,v 1.3 2011-05-27 12:42:20 oscar Exp $
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

/**
 * \ingroup OWL_UI_PLUGINS
 * Abstract class defining container plugins
 * \brief ContainerPlugins
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

abstract class ContainerPlugin extends BaseElement
{

	/**
	 * Type; the container type, which must match the HTML tag.
	 */
	protected $type;

	/**
	 * Class constructor;
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function __construct ()
	{
		_OWL::init();
	}

	/**
	 * Return the container type, which is equal to the HTML tag name
	 * \return container type
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getType()
	{
		return $this->type;
	}
}
