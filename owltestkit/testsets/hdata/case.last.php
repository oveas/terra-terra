<?php
/**
 * \file
 * This file defines the last testcase for the Hierarchical Dataset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.last.php,v 1.7 2011-10-28 09:32:48 oscar Exp $
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
 * \ingroup OTK_TESTSETS
 * This testcase dropt a database table that was used in all HData tests
 * \brief HData Testcase
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 23, 2011 -- O van Eijk -- initial version
 */
class OTKHdata_Last implements TestCase
{
	// Name of the temporary database table that will be used in this testcase
	private $tablename;

	// Hold details of the testresults
	private $details;

	public function __construct()
	{
		$this->tablename = OTKHdata_tableName();
		$this->details = '';
	}

	public function prepareTest ()
	{
		return OTK_RESULT_NONE;
	}

	public function performTest ()
	{
		$returnCodes = array();

		// Step 1; remove the complete tree
		$hd = new HDataHandler();
		$hd->setTablename(OTKHdata_tableName());
		$hd->setLeft('lval');
		$hd->setRight('rval');
		if ($hd->removeTree('node', 'Musical instruments') === false) {
			$returnCodes[] = array(OTK_RESULT_FAIL, 'Removing the root node failed: ' . $hd->getLastWarning());
		} else {
			switch (OTKHdata_getData($data)) {
				case null:
					$returnCodes[] = array(OTK_RESULT_SUCCESS, 'Successfully removed the root node "Musical instruments"');
					break;
				case false:
					$returnCodes[] = array(OTK_RESULT_FAIL, 'Failure while retrieving the results: ' . $data);
					break;
				case true:
					$returnCodes[] = array(OTK_RESULT_FAIL, 'The root node and its tree were not removed.');
					$this->details .= '<p>The table should be emptied but still contains the following data:<br/><pre>'
							. print_r($data, 1) . '</pre></p>';
					break;
			}
		}
		return $returnCodes;
	}

	public function cleanupTest ()
	{//return 'Disbled the drop';
		$db = OWL::factory('dbhandler');
		$dbId = $db->getResource();
		if ($db->getDriver()->dbDropTable($dbId, $db->tablename($this->tablename, true))) {
			return OTK_RESULT_SUCCESS;
		} else {
			$db->getDriver()->dbError($dbId,$nr, $msg);
			return "Could not drop the table; database driver returned the error <em>$msg</em>";
		}
	}

	public function getDetails ()
	{
		return $this->details;
	}

}