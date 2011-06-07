<?php
/**
 * \file
 * This file defines the standard container plugin
 * \version $Id: class.container.php,v 1.4 2011-06-07 14:06:55 oscar Exp $
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
	 * Optional nested type
	 */
	protected $nested_type;

	/**
	 * Class constructor;
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function __construct ()
	{
		_OWL::init();
		$this->nested_type = null;
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

	/**
	 * Return the complete HTML tag for a nested type, or an empty string if the current
	 * container doesn't have one
	 * \param[in] $close Boolean that's set to true when the closing tag must be returned
	 * \return Full HTML tag
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getNestedType ($close = false)
	{
		if ($this->nested_type === null) {
			return '';
		}
		return ('<' . ($close === true ? '/' : '') . $this->nested_type . '>');
	}
}
