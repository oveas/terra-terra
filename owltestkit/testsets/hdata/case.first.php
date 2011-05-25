<?php 
/**
 * \file
 * This file defines the first testcase for the Hierarchical Dataset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.first.php,v 1.3 2011-05-25 12:04:30 oscar Exp $
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
		$db = DbHandler::getInstance();
		$db->setQuery ('CREATE  TABLE ' . $db->tablename($this->tablename) . ' ('
			. ' `id` INT UNSIGNED NOT NULL AUTO_INCREMENT '
			. ',`lval` INT NOT NULL '
			. ',`rval` INT NOT NULL '
			. ',`node` VARCHAR(45) NOT NULL '
			. ',`xlink` INT UNSIGNED NULL '
			. ',PRIMARY KEY (`id`) '
			. ',INDEX (`lval`) '
			. ',INDEX (`rval`) '
			. ',UNIQUE INDEX (`node`) '
			. ',INDEX (`xlink`) '
			. ')');
		if ($db->write($dummy, __LINE__, __FILE__) <= OWL_SUCCESS) {
			return OTK_RESULT_SUCCESS;
		} else {
			$db->signal(OWL_WARNING, $msg);
			return $msg;
		}
	}

	public function performTest ()
	{
		$returnCodes = array();

		// Step 1; create a rootnode
		$hd = new HDataHandler();
		$hd->setTablename(OTKHdata_tableName());
		$hd->setLeft('lval');
		$hd->setRight('rval');
		if ($hd->insertNode(array('node' => 'Musical instruments'), array('field' => 'node')) === false) {
			$returnCodes[] = array(OTK_RESULT_FAIL, 'Inserting root node failed: ' . $hd->getLastWarning());
		} else {
			switch (OTKHdata_getData($data)) {
				case null:
					$returnCodes[] = array(OTK_RESULT_FAIL, 'No root node inserted - table empty');
					break;
				case false:
					$returnCodes[] = array(OTK_RESULT_FAIL, 'Failure while retrieving the results: ' . $data);
					break;
				case true:
					$_expectedResult = array(
											array(
												 'lval' => 1
												,'rval' => 2
												,'node' => 'Musical instruments'
												,'xlink' => null
											)
										);
					if ($data === $_expectedResult) {
						$returnCodes[] = array(OTK_RESULT_SUCCESS, 'Successfully inserted root node "Musical instruments"');
					} else {
						$returnCodes[] = array(OTK_RESULT_FAIL, 'Inserted root node was not as expected');
						$this->details .= '<p>The values of the root node differed from the exected result. '
							. 'The expected result was: <br/><pre>' . print_r($_expectedResult, 1) . '</pre><br/>'
							. 'The actual result was: <br/><pre>' . print_r($data, 1) . '</pre></p>';
					}
					break;
			}
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