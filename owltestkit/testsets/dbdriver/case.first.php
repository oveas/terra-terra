<?php
/**
 * \file
 * This file defines the first testcase for the Hierarchical Dataset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.first.php,v 1.2 2011-10-16 11:11:44 oscar Exp $
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
 * This testcase creates a database table that will be used in all Dbdriver tests
 * \brief DbDriver testcase
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Sep 26, 2011 -- O van Eijk -- initial version
 */
class OTKDbdriver_First implements TestCase
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
		$_scheme = OWL::factory('schemehandler');

		$_table = array(
			 'id' => array (
				 'type' => 'INT'
				,'length' => 11
				,'auto_inc' => true
				,'null' => false
			)
			,'firstname' => array (
					 'type' => 'varchar'
					,'length' => 24
					,'auto_inc' => false
					,'null' => false
			)
			,'lastname' => array (
					 'type' => 'varchar'
					,'length' => 24
					,'auto_inc' => false
					,'null' => false
			)
			,'address' => array (
					 'type' => 'text'
					,'length' => 0
					,'null' => false
			)
			,'phone' => array (
					 'type' => 'varchar'
					,'length' => 16
					,'null' => true
			)
			,'country' => array (
					 'type' => 'enum'
					,'length' => 0
					,'auto_inc' => false
					,'options' => array('NL', 'BE', 'DE', 'FR', 'ES')
					,'default' => 'ES'
					,'null' => false
			)
		);
		$_index = array (
			 'name' => array(
					 'columns' => array ('firstname', 'lastname')
					,'primary' => false
					,'unique' => false
					,'type' => null
			)
			,'address' => array(
					 'columns' => array ('address')
					,'primary' => false
					,'unique' => false
					,'type' => 'FULLTEXT'
			)
		);
		$_scheme->createScheme($this->tablename);
		$_scheme->defineScheme($_table);
		$_scheme->defineIndex($_index);
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

		$_scheme = OWL::factory('schemehandler');
		$_scheme->tableDescription($this->tablename, $data);
//OutputHandler::outputPhpArray($data);

		$_expectedResult = $this->getExpected();
		if ($data == $_expectedResult) { // Just 2 '=' signs since not all datatypes (int vs string) might match
			$returnCodes[] = array(OTK_RESULT_SUCCESS, 'Table comparison succeeded');
		} else {
			$returnCodes[] = array(OTK_RESULT_WARNING, 'Table comparison failed');

			$this->details .= "<p>The table definition differed from the exected structure: "
				. OTKHelpers::compareTable($_expectedResult, $data);
		}

		return $returnCodes;
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
					, 'length' => '24'
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

	public function cleanupTest ()
	{
		return OTK_RESULT_NONE;
	}

	public function getDetails ()
	{
		return $this->details;
	}

}