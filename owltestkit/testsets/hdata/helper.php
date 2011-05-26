<?php
/**
 * \ingroup OTK_TESTSETS
 * \file
 * This file defines some helper functions for the HData testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: helper.php,v 1.3 2011-05-26 12:26:30 oscar Exp $
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
		$_q .= ' WHERE `lval` >= (SELECT `lval` FROM ' . $db->tablename(OTKHdata_tableName()) . " WHERE `node` = '$rootNode')"
			. ' AND  `rval` <= (SELECT `rval` FROM ' . $db->tablename(OTKHdata_tableName()) . " WHERE `node` = '$rootNode')";
	}
	$_q .= ' ORDER BY `lval`';
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