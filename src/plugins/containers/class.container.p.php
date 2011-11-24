<?php
/**
 * \file
 * This file defines the p plugin for containers
 * \author Daan Schulpen
 * \copyright{2011} Daan Schulpen
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
 * Class defining P container plugin
 * \brief PContainer
 * \author Daan Schulpen
 * \version Nov 23, 2011 -- D Schulpen -- initial version
 */

class ContainerPPlugin extends ContainerPlugin
{

	/**
	 * Container constructor
	 * \author Daan Schulpen
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'p';
	}

	/**
	 * The P tag has no specific arguments, but this method is required by syntax
	 * \author Daan Schulpen
	 * \return Empty string
	 */
	public function showElement()
	{
		return '';
	}

}
