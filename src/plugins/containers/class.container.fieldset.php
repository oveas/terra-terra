<?php
/**
 * \file
 * This file defines the label plugin for containers
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
 * Class defining Fieldset container plugin
 * \brief FieldsetContainer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

class ContainerFieldsetPlugin extends ContainerPlugin
{

	/**
	 * Reference to the Legend container object
	 */
	private $legend;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		$this->type = 'fieldset';
		$this->legend = null;
		parent::__construct();
	}

	/**
	 * Add a legend container to the fieldset
	 * \param[in] $_content Texstring
	 * \param[in] $_attribs An optional array with HTML attributes
	 * \param[in] $_type_attribs Array with container type specific arguments; not used here
	 * \return Reference to the legend object
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addLegend($_content = '&nbsp;', array $_attribs = array(), array $_type_attribs = array())
	{
		$this->legend = new Container('legend', $_attribs, $_type_attribs);
		$this->legend->setContent($_content);
		return $this->legend;
	}

	/**
	 * The FIELDSET tag has no specific arguments, but this method is required by syntax
	 * \return Empty string
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		return '';
	}

	/**
	 * If a legend is set, return the content
	 * \return HTML code or an empty string
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getContent()
	{
		$_content = parent::getContent();
		if ($this->legend !== null) {
			return $this->legend->showElement() . $_content;
		} else {
			return $_content;
		}
	}
}
