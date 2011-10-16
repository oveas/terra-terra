<?php
/**
 * \file
 * This file defines the standard container plugin
 * \version $Id: class.container.php,v 1.5 2011-10-16 11:11:44 oscar Exp $
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
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
