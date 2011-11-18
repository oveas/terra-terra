<?php
/**
 * \file
 * \ingroup OWL_LIBRARY
 * This file loads contributed plugins that have been enabled. It contains
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
 * Scan the CONTRIB/enabled directory for php files and load them.
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function loadContribPlugins ()
{
	$contribDir = OWL_CONTRIB . '/enabled/';
	if ($dH = opendir($contribDir)) {
		while (($fName = readdir($dH)) !== false) {
			if (is_file($contribDir . $fName)) {
				$fElements = explode('.', $fName);
				if (array_pop($fElements) == 'php') {
					require $contribDir . $fName;
				}
			}
		}
	}
	closedir($dH);
}

loadContribPlugins();