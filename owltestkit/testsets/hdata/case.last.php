<?php 
/**
 * \file
 * This file defines the last testcase for the Hierarchical Dataset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.last.php,v 1.1 2011-05-23 18:21:31 oscar Exp $
 */

/**
 * \ingroup OTK_TESTSETS
 * This testcase dropt a database table that was used in all HData tests
 * \brief HData Testcase
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 23, 2011 -- O van Eijk -- initial version
 */
class OTKHdata_Last
{
	/**
	 * Name of the temporary database table that will be used in this testcase
	 */
	private $tablename;

	public function __construct()
	{
		$this->tablename = OTKHdata_Tablename();
	}

	public function prepareTest ()
	{
		return OTK_RESULT_NONE;
	}

	/**
	 * Perform the test(s) for this testcase. Multiple tests can be performed, each writing a single
	 * message in an internal array explaining what the result was ('step X completed succefully', an
	 * error message in case of failures etc).
	 * Additional (private) methods can be created for each step.
	 * \return A 2 dimensional array with messages for all steps that were performed in this testcase.
	 * Each step in the testcase has exactly 1 element in the array, being an array with 2 elements: first
	 * is an human readable message, the second is true if the step succeeded, false if it failed and
	 * null when the step was skipped, e.g.
	 * \code
	 * 	array(
	 * 		 array('Step 1 completed successfully', true)
	 * 		,array('Step 2 completed successfully', true)
	 * 		,array('Step 3 failed with code XX', false)
	 * 		,array('Step 4 was skipped', null)
	 * 	)
	 * \endcode
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function performTest ()
	{
		$db = DbHandler::getInstance();
	}

	public function cleanupTest ()
	{
		$db = DbHandler::getInstance();
		$db->setQuery ('DROP TABLE ' . $db->tablename($this->tablename));
		if ($db->write($dummy, __LINE__, __FILE__) <= OWL_SUCCESS) {
			return OTK_RESULT_SUCCESS;
		} else {
			$db->signal(OWL_WARNING, $msg);
			return $msg;
		}
	}

	public function getDetails ()
	{
		return null;
	}
	
}