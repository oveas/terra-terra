<?php
/**
 * \file
 * This file defines the header plugin for containers
 * \author Daan Schulpen
 * \copyright{2011} Daan Schulpen
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
 * Class defining header (e.g. h1) container plugin
 * \brief Header Container
 * \author Daan Schulpen
 * \version Nov 23, 2011 -- D Schulpen -- initial version
 */

class ContainerHeaderPlugin extends ContainerPlugin
{

	/**
	 * Container constructor
	 * \author Daan Schulpen
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type = 'h1';
	}
	
	/**
	 * Set the header level by changing the type
	 * \param[in] $_level The desired header level (1 - 6)
	 * \author Daan Schulpen
	 */
	public function setLevel($_level) {
		if (!is_numeric($_level) || $_level < 1 || $_level > 6) {
			$this->setStatus(HEADER_LVLOORANGE, array($_level));
			return $this->severity;
		}
		
		$this->type = 'h' . $_level;
	}

	/**
	 * The header tags have no specific arguments, but this method is required by syntax
	 * \author Daan Schulpen
	 * \return Empty string
	 */
	public function showElement()
	{
		return '';
	}

}

Register::registerClass('ContainerHeaderPlugin', TT_APPNAME);

Register::setSeverity(TT_BUG);
Register::registerCode('HEADER_LVLOORANGE');
