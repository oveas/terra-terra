<?php
/**
 * \file
 * This file defines the testcase that does all table alterings
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.alter.php,v 1.1 2011-09-26 10:50:18 oscar Exp $
 */

/**
 * \ingroup OTK_TESTSETS
 * This testcase does some node handling, like inserts, move and remove
 * \brief DbDriver ALTER TABLE Testcase
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Sep 26, 2011 -- O van Eijk -- initial version
 */
class OTKDbdriver_Alter implements TestCase
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
		$_scheme = OWL::factory('schemehandler');

		$_scheme->createScheme($this->tablename);
		$_scheme->tableDescription($this->tablename, $data);
		$data['columns']['lastname']['length'] = 48;
		$_scheme->defineScheme($data['columns']);
		$_scheme->defineIndex($data['indexes']);
		// Step 1: alter the table
		if ($_scheme->scheme() <= OWL_SUCCESS) {
			$_scheme->reset();
			$returnCodes[] = array(OTK_RESULT_SUCCESS, 'Successfully altered the table');
		} else {
			$_scheme->signal(OWL_WARNING, $msg);
			$_scheme->reset();
			$returnCodes[] = array(OTK_RESULT_FAIL, 'Altering the table failed in step 1');
			$this->details .= "<p>Schemehandler returned an error while altyering the table:<blockquote>$msg</blockquote>";
		}

		// Step 2: Check the results
		$_expectedResult = $this->getExpected();
		if ($data == $_expectedResult) { // Just 2 '=' signs since not all datatypes (int vs string) might match
			$returnCodes[] = array(OTK_RESULT_SUCCESS, 'Table comparison succeeded');
		} else {
			$returnCodes[] = array(OTK_RESULT_FAIL, 'Table comparison failed');

			$this->details .= "<p>The table definition differed from the exected structure: "
				. OTKHelpers::compareTable($_expectedResult, $data);
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

	private function getExpected ()
	{
		return array(
			 'columns' => array(
				 'id' => array(
					  'type' => 'int'
					, 'length' => '11'
					, 'unsigned' => ''
					, 'zerofill' => ''
					, 'null' => ''
					, 'auto_inc' => '1'
					, 'default' => ''
					, 'comment' => ''
				)
				,'firstname' => array(
					  'type' => 'varchar'
					, 'length' => '24'
					, 'unsigned' => ''
					, 'zerofill' => ''
					, 'null' => ''
					, 'auto_inc' => '0'
					, 'default' => ''
					, 'comment' => ''
				)
				,'lastname' => array(
					  'type' => 'varchar'
					, 'length' => '48'
					, 'unsigned' => ''
					, 'zerofill' => ''
					, 'null' => ''
					, 'auto_inc' => '0'
					, 'default' => ''
					, 'comment' => ''
				)
				,'address' => array(
					  'type' => 'text'
					, 'null' => ''
					, 'auto_inc' => '0'
					, 'default' => ''
					, 'comment' => ''
				)
				,'phone' => array(
					  'type' => 'varchar'
					, 'length' => '16'
					, 'unsigned' => ''
					, 'zerofill' => ''
					, 'null' => '1'
					, 'auto_inc' => '0'
					, 'default' => ''
					, 'comment' => ''
				)
				,'country' => array(
					 'type' => 'enum'
					, 'null' => ''
					, 'auto_inc' => '0'
					, 'options' => array(
						  '0' => '\'NL\''
						, '1' => '\'BE\''
						, '2' => '\'DE\''
						, '3' => '\'FR\''
						, '4' => '\'ES\''
					)
					, 'default' => 'ES'
					, 'comment' => ''
				)
			)
			,'indexes' => array(
				  'PRIMARY' => array(
					  'columns' => array(
						  '1' => 'id'
					)
					, 'unique' => '1'
					, 'type' => 'BTREE'
					, 'comment' => ''
				)
				, 'name' => array(
					  'columns' => array(
						  '1' => 'firstname'
						, '2' => 'lastname'
						)
					, 'unique' => ''
					, 'type' => 'BTREE'
					, 'comment' => ''
				)
				, 'address' => array(
					  'columns' => array(
						  '1' => 'address'
					)
					, 'unique' => ''
					, 'type' => 'FULLTEXT'
					, 'comment' => ''
				)

			)
		);
	}
}