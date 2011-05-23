<?php 
/**
 * \file
 * This file defines the first testcase for the Hierarchical Dataset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.first.php,v 1.1 2011-05-23 17:56:18 oscar Exp $
 */

/**
 * \ingroup OTK_TESTSETS
 * This testcase creates a database table that will be used in all HData tests
 * \brief HData Testcase
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 23, 2011 -- O van Eijk -- initial version
 */
class OTKHdata_First
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
		$db = DbHandler::getInstance();
		$db->setQuery ('CREATE  TABLE ' . $db->tablename($this->tablename) . ' ('
			. ' `id` INT UNSIGNED NOT NULL AUTO_INCREMENT '
			. ',`lval` INT NOT NULL '
			. ',`rval` INT NOT NULL '
			. ',`field` VARCHAR(45) NOT NULL '
			. ',`xlink` INT UNSIGNED NULL '
			. ',PRIMARY KEY (`id`) '
			. ',INDEX (`lval`) '
			. ',INDEX (`rval`) '
			. ',UNIQUE INDEX (`field`) '
			. ',INDEX (`xlink`) '
			. ')');
		if ($db->write($dummy, __LINE__, __FILE__) <= OWL_OK) {
			return OTK_RESULT_SUCCESS;
		} else {
			$db->signal(OWL_WARNING, $msg);
			return $msg;
		}
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
		return OTK_RESULT_NONE;
	}

	public function getDetails ()
	{
		return null;
	}
	
}