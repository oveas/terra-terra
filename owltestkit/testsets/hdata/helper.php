<?php
/**
 * \ingroup OTK_TESTSETS
 * \file
 * This file defines some helper functions for the HData testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: helper.php,v 1.6 2011-10-16 11:11:45 oscar Exp $
 * \copyright{2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OTK.
 *
 * OTK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OTK is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OTK. If not, see http://www.gnu.org/licenses/.
 */

/**
 * Get the name of the database table used by all testcases. It's hardcoded here, but can
 * be used by more classes this way.
 * \return Table name without prefix
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OTKHdata_tableName ()
{
	return 'hdata';
}

/**
 * Get the data from the testtable. The primary key will be excluded from the resulting dataset
 * and all integer values will be casted to actual integer.
 * \param[out] $data Table contents or an error message when an error occured
 * \param[in] $rootNode Optional toplevel to select from
 * \return True on success, false on error, null when no data data was found
 * \author Oscar van Eijk, Oveas Functionality Provider
 */
function OTKHdata_getData (&$data, $rootNode = null)
{
	$db = DbHandler::getInstance();
	$_q = 'SELECT * FROM ' . $db->tablename(OTKHdata_tableName());
	if ($rootNode !== null) {
		$_q .= ' WHERE ' . $db->getDriver()->dbQuote('lval') . ' >= (SELECT ' . $db->getDriver()->dbQuote('lval') . ' FROM ' . $db->tablename(OTKHdata_tableName()) . ' WHERE ' . $db->getDriver()->dbQuote('node') . " = '$rootNode')"
			. ' AND  ' . $db->getDriver()->dbQuote('rval') . ' <= (SELECT ' . $db->getDriver()->dbQuote('rval') . ' FROM ' . $db->tablename(OTKHdata_tableName()) . ' WHERE ' . $db->getDriver()->dbQuote('node') . " = '$rootNode')";
	}
	$_q .= ' ORDER BY ' . $db->getDriver()->dbQuote('lval');
	$db->read(DBHANDLE_DATA, $_data, $_q, __LINE__, __FILE__);
	$data = null;
	if ($db->getStatus () === DBHANDLE_NODATA) {
		return null;
	} elseif ($db->succeeded() === false) {
		$data = $db->getLastWarning();
		return false;
	} else {
		$data = array();
		foreach ($_data as $record) {
			$data[] = array(
				 'lval' => (int) $record['lval']
				,'rval' => (int) $record['rval']
				,'node' => $record['node']
				,'xlink' => ($record['xlink'] === null ? null : (int) $record['xlink'])
			);
		}
		return (true);
	}

}