<?php
/**
 * \file
 * This file defines the Tablecell plugin for containers
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.container.tablecell.php,v 1.2 2011-10-16 11:11:44 oscar Exp $
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
 * Class defining Tablecell container plugin
 * \brief Tablecell Container
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jan 9, 2011 -- O van Eijk -- initial version
 * \version May 27, 2011 -- O van Eijk -- Rewritten the UI object as plugin
 */

class ContainerTablecellPlugin extends ContainerPlugin
{

	/**
	 * Rowspan
	 */
	private $rowspan = null;

	/**
	 * Colspan
	 */
	private $colspan = null;

	/**
	 * Vertical alignment
	 */
	private $valign = null;

	/**
	 * Container constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'td';
	}

	/**
	 * Make this cell from row a header by chabging the type
	 * \param[in] $isheader True or false
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setHeader ($isheader = true)
	{
		$this->type = (($isheader === true) ? 'th' : 'td');
	}

	/**
	 * Set the rowspan value
	 * \param[in] $_value Rowspan value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setRowspan($_value)
	{
		$this->rowspan = $_value;
	}

	/**
	 * Set the colspan value
	 * \param[in] $_value Rowspan value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setColspan($_value)
	{
		$this->colspan = $_value;
	}

	/**
	 * Set the vertical alignment
	 * \param[in] $_value Vertical alignment
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function setValign($_value)
	{
		$this->valign = $_value;
	}

	/**
	 * Show the tablecell specific arguments.
	 * \return string with the HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function showElement()
	{
		$_htmlCode = '';
		if ($this->rowspan !== null) {
			$_htmlCode .= ' rowspan="' . $this->rowspan . '"';
		}
		if ($this->colspan !== null) {
			$_htmlCode .= ' colspan="' . $this->colspan . '"';
		}
		if ($this->valign !== null) {
			$_htmlCode .= ' valign="' . $this->valign . '"';
		}
		return $_htmlCode;
	}
}
