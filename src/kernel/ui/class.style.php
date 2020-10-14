<?php
/**
 * \file
 * This file defines default Style class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2020} Oscar van Eijk, Oveas Functionality Provider
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
 * \ingroup TT_UI_LAYOUT
 * Class that defines the CSS Style object
 * \brief Style class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 13, 2020 -- O van Eijk -- initial version
 */
class Style extends _TT
{
	/**
	 * An array with all CSS attributes and their values
	 */
	private $attributes;

	/**
	 * Constructor.
	 */
	protected function __construct()
	{
		$this->attributes = array();
	}
	
	/**
	 * Add CSS style elements to the array with attributes
	 * \param[in] $_attributes CSS elements as an array in the format element => value
	 */
	public function setAttributes(array $_attributes)
	{
		$this->attributes = array_merge($this->attributes, $_attributes);
	}

	/**
	 * Return the style
	 * \return The CSS style element in HTML format
	 */
	public function getStyleElement()
	{
		if (empty($this->attributes)) {
			return '';
		}
		$_style = ' style="';
		foreach ($_attributes as $_k => $_v) {
			$_style .= "$_k: $_v;";
		}
		$_style = '"';
		return $_style;
	}
}

Register::registerClass('Style', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);
//Register::setSeverity (TT_INFO);
//Register::setSeverity (TT_OK);
//Register::setSeverity (TT_SUCCESS);

//Register::setSeverity (TT_WARNING);

//Register::setSeverity (TT_BUG);

//Register::setSeverity (TT_ERROR);

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
