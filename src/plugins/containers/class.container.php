<?php
/**
 * \file
 * This file defines the standard container plugin
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
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

/**
 * \ingroup TT_UI_PLUGINS
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
	 * True for self-closing containers that have no actual content. Defaults to false
	 */
	protected $self_closing;

	/**
	 * CSS style object
	 */
	protected $style;

	/**
	 * Class constructor;
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function __construct ()
	{
		_TT::init(__FILE__, __LINE__);
		$this->nested_type = null;
		$this->style = new Style();
		$this->self_closing = false;
	}

	/**
	 * Specify it this container is self closing.
	 * \param[in] $value True (default) or false.
	 */
	protected function setSelfClosing($value = true)
	{
		$this->self_closing = $value;
	}

	/**
	 * Return the self_closing property. This must be set to true for containers that have no actual content (as in:
	 * &lt;tag&gtContent&lt;/tag&gt), like images (img), linebreaks (br), horizontal rules (hr) etc.
	 * These containers will be rendered as &lt;tag/&gt
	 * @return boolean|string
	 */
	public function isSelfClosing()
	{
		return $this->self_closing;
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
	 * Add CSS style elements
	 * \param[in] $_attributes CSS elements as an array in the format element => value
	 */
	public function addStyleAttributes(array $_attributes)
	{
		$this->style->setAttributes($_attributes);
	}

	/**
	 * Return the style element
	 * \return CSS style element in HTML format
	 */
	public function getStyleElement()
	{
		return $this->style->getStyleElement();
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
