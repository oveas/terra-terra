<?php
/**
 * \file
 * This file defines the last testcase for the Hierarchical Dataset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.last.php,v 1.1 2011-09-26 10:50:18 oscar Exp $
 */

/**
 * \ingroup OTK_TESTSETS
 * This testcase dropt a database table that was used in all DbDriver tests
 * \brief DbDriver Testcase
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 23, 2011 -- O van Eijk -- initial version
 */
class OTKDbdriver_Last implements TestCase
{
	// Name of the temporary database table that will be used in this testcase
	private $tablename;

	// Hold details of the testresults
	private $details;

	public function __construct()
	{
		$this->tablename = OTKDbdriver_tableName();
		$this->details = '';
	}

	public function prepareTest ()
	{
		return OTK_RESULT_NONE;
	}

	public function performTest ()
	{
		$returnCodes = array();
		// TODO Use SchemeHandler here (once ported to the DbDriver functionality)
		$db = DbHandler::getInstance();
		$db->setQuery ('DROP TABLE ' . $db->tablename($this->tablename));
		if ($db->write($dummy, __LINE__, __FILE__) <= OWL_SUCCESS) {
			$returnCodes[] = array(OTK_RESULT_SUCCESS, 'Successfully removed the table "' . $this->tablename . '"');
		} else {
			$returnCodes[] = array(OTK_RESULT_FAIL, 'Table "' . $this->tablename . '" could not be removed');
			$db->signal(OWL_WARNING, $msg);
			$this->details .= '<p>Dropping the table failed with error messase<br/><em>' . $msg . '</em></p>';
		}
		return $returnCodes;
	}

	public function cleanupTest ()
	{
		return OTK_RESULT_NONE;
	}

	public function getDetails ()
	{
		return $this->details;
	}

}