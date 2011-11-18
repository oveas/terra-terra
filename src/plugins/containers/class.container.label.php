<?php
/**
 * \file
 * This file defines the label plugin for containers
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
 * Class defining Label container plugin
 * \brief LabelContainer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 12, 2011 -- O van Eijk -- initial version
 */

class ContainerLabelPlugin extends ContainerPlugin
{

	/**
	 * Reference to the formfield object
	 */
	private $for;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'label';
		$this->for = null;
	}

	/**
	 * Set the for attribute, which identifies the formfield for which this is a label
	 * \param[in] $_for Reference to the formfield object or a string with the ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setFor($_for)
	{
		$this->for = $_for;
	}

	/**
	 * Show the LABEL specific arguments.
	 * \return HTML code for use in the LABEL tag
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$_htmlCode = '';
		if ($this->for !== null) {
			$_htmlCode .= ' for="'
				. ((is_object($this->for)) ? $this->for->getId() : $this->for)
				. '"';
		}
		return $_htmlCode;
	}
}
