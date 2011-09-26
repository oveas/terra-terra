<?php
/**
 * \file
 * This file defines the first testcase for the Hierarchical Dataset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.first.php,v 1.5 2011-09-26 10:50:19 oscar Exp $
 */

/**
 * \ingroup OTK_TESTSETS
 * This testcase creates a database table that will be used in all HData tests
 * \brief HData Testcase
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 23, 2011 -- O van Eijk -- initial version
 */
class OTKHdata_First implements TestCase
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
		$_scheme = OWL::factory('schemehandler');
		$_table = array(
			 'id' => array (
				 'type' => 'INT'
				,'auto_inc' => true
				,'null' => false
				,'unsigned' => true
			)
			,'lval' => array (
				 'type' => 'INT'
				,'null' => false
			)
			,'rval' => array (
				 'type' => 'INT'
				,'null' => false
			)
			,'node' => array (
				 'type' => 'VARCHAR'
				,'length' => 45
				,'null' => false
			)
			,'xlink' => array (
				 'type' => 'INT'
				,'null' => true
				,'unsigned' => true
			)
		);
		$_index = array (
			 'lval' => array(
				 'columns' => array ('lval')
			)
			,'rval' => array(
				 'columns' => array ('rval')
			)
			,'node' => array(
				 'columns' => array ('node')
				,'unique' => true
			)
			,'xlink' => array(
				 'columns' => array ('xlink')
			)
		);


		$_scheme->createScheme($this->tablename);
		$_scheme->defineScheme($_table);
		$_scheme->defineIndex($_index);
		$_scheme->scheme();
		if ($_scheme->scheme() <= OWL_SUCCESS) {
			$_scheme->reset();
			return OTK_RESULT_SUCCESS;
		} else {
			$_scheme->signal(OWL_WARNING, $msg);
			$_scheme->reset();
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