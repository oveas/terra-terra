<?php
/**
 * \file
 * This file defines the Image plugin for containers
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
 * \ingroup TT_UI_PLUGINS
 * Class defining Img container plugin. Actually, this is rather a link than a container
 * \brief ImageContainer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 14, 2020 -- O van Eijk -- initial version
 */

class ContainerImgPlugin extends ContainerPlugin
{
	/**
	 * Image source.
	 */
	private $src;

	/**
	 * Alternative text
	 */
	private $alt;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'img';
		$this->alt = '';
		$this->setSelfClosing(true);
	}

	/**
	 * Set the image source attribute
	 * \param[in] $_src Full URL of the image
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setSrc($_src)
	{
		$this->src = $_src;
	}

	/**
	 * Set the alternative text
	 * \param[in] $_alt Alternative text
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setAlt($_alt = '')
	{
		$this->alt = $_alt;
	}

	/**
	 * Show the IMG specific arguments.
	 * \return HTML code for use in the IMG tag
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$_htmlCode = ' src="' . $this->src . '"';
		if ($this->alt !== '') {
			$_htmlCode .= ' alt="' . $this->alt . '"';
		}
		return $_htmlCode;
	}
}
