<?php
/**
 * \file
 * This file defines default methods for the Mail drivers
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
 * \ingroup TT_DRIVERS
 * Abstract class that defines some default methods and properties for the mail drivers.
 * \brief Mail driver defauls
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 11, 2011 -- O van Eijk -- initial version
 */
abstract class MailsendDefaults
{

	/**
	 * Class constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __constructor()
	{
	}

	/**
	 * Add one or more headers to this mail. Headers that already exist will be overwritten.
	 * \param[out] $headers Indexed array with the header info in the format (header => value) to which the headers will be added
	 * \param[in] $headerType Identification of the headertype, see \ref MAILDRIVER_HeaderTypes
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function addHeader (array &$headers, $headerType)
	{
		switch ($headerType) {
			case MAILDRIVER_HEADERTYPE_HTML:
				$headers[] = 'MIME-Version: 1.0';
				$headers[] = 'Content-type: text/html; charset=iso-8859-1';
				break;
		}
	}
}
