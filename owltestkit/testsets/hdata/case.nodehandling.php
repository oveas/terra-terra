<?php
/**
 * \file
 * This file defines the testcase that does all node handling
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.nodehandling.php,v 1.2 2011-09-26 10:50:19 oscar Exp $
 */

/**
 * \ingroup OTK_TESTSETS
 * This testcase does some node handling, like inserts, move and remove
 * \brief HData Node Testcase
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 26, 2011 -- O van Eijk -- initial version
 */
class OTKHdata_Nodehandling implements TestCase
{
	// Hierarchical data handler
	private $hdatahandler;

	// Hold details of the testresults
	private $details;

	// Array with return values
	private $returnCodes;

	public function __construct()
	{
		$this->details = '';
		$this->hdatahandler = new HDataHandler();
		$this->hdatahandler->setTablename(OTKHdata_tableName());
		$this->hdatahandler->setLeft('lval');
		$this->hdatahandler->setRight('rval');
	}

	public function prepareTest ()
	{
		// Insert some childnodes to wotk with during this test
		$_stat = $this->insertNodes('Wind', 'Musical instruments');
		if ($_stat === true) { $_stat = $this->insertNodes('Strings', 'Musical instruments'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Percussion', 'Musical instruments');}
		if ($_stat === true) { $_stat = $this->insertNodes('Wood', 'Wind'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Brass', 'Wind'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Plucked', 'Strings'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Bowed', 'Strings'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Clarinet', 'Wood'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Saxophone', 'Wood'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Oboe', 'Wood'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Bassoon', 'Wood'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Flute', 'Wood'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Trumpet', 'Brass'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Horn', 'Brass'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Violin', 'Bowed'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Cello', 'Bowed'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Guitar', 'Plucked'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Chapman stick', 'Plucked'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Piano', 'Strings'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Cymbal', 'Percussion'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Tuned', 'Percussion'); }
		if ($_stat === true) { $_stat = $this->insertNodes('Timpani', 'Tuned'); }

		if ($_stat === false) {
			return $this->hdatahandler->getLastWarning();
		}
		return OTK_RESULT_SUCCESS;
	}

	public function performTest ()
	{
		$this->returnCodes = array();

		// Step 1; check the prepared values and move some nodes there
		$retVal = OTKHdata_getData($data);
		$this->checkResult ($retVal, $data, 1, 'Inserting the "Musical instruments" tree');

		// Step 2; add some subcategories
		$this->insertNodes('Single reed', 'Wood');
		$this->insertNodes('Double reed', 'Wood');
		$this->hdatahandler->moveNode('node', 'Clarinet', array('field' => 'node', 'value' => 'Single reed'));
		$this->hdatahandler->moveNode('node', 'Saxophone', array('field' => 'node', 'value' => 'Single reed'));
		$this->hdatahandler->moveNode('node', 'Oboe', array('field' => 'node', 'value' => 'Double reed'));
		$this->hdatahandler->moveNode('node', 'Bassoon', array('field' => 'node', 'value' => 'Double reed'));
		$retVal = OTKHdata_getData($data);
		$this->checkResult ($retVal, $data, 2, 'Inserting new categories and move nodes');

		// Step 3; remove a node, moving the childs upwards
		$this->hdatahandler->removeNode('node', 'Tuned');
		$retVal = OTKHdata_getData($data);
		$this->checkResult ($retVal, $data, 3, 'Removing a node, moving the children up');

		// Step 4; remove a tree
		$this->hdatahandler->removeTree('node', 'Wind');
		$retVal = OTKHdata_getData($data);
		$this->checkResult ($retVal, $data, 4, 'Removing a complete tree');

		// Step 5; crosslinking
		$this->insertNodes('Keyboards', 'Musical instruments');
		$this->insertNodes('Synthesizer', 'Keyboards');
		$this->hdatahandler->setPrimaryKey('id');
		$this->hdatahandler->enableCrossLink('xlink');
		$this->hdatahandler->addParent(
			 array('field' => 'node', 'value' => 'Piano')
			,array('field' => 'node', 'value' => 'Keyboards')
		);
		$_children = $this->hdatahandler->getDirectChildren('node', 'Keyboards');
		$data = array();
		foreach ($_children as $_child) {
			$data[] = $_child['node'];
		}
		$this->checkResult (true, $data, 5, 'Create a crosslink');
		return $this->returnCodes;
	}

	private function checkResult ($retVal, $data, $step, $stepDescription)
	{
		switch ($retVal) {
			case null:
				$this->returnCodes[] = array(OTK_RESULT_FAIL, 'Table is empty  (but that can\'t happen...)');
				break;
			case false:
				$this->returnCodes[] = array(OTK_RESULT_FAIL, 'Failure while retrieving the results: ' . $data);
				break;
			case true:
				$_expectedResult = $this->getExpected($step);
				if ($data === $_expectedResult) {
					$this->returnCodes[] = array(OTK_RESULT_SUCCESS, $stepDescription . ' succeeded');
				} else {
					$this->returnCodes[] = array(OTK_RESULT_FAIL, $stepDescription . ' failed');

					$this->details .= "<p>The datatree in step $step differed from the expected structure: "
						. OTKHelpers::compareTable($_expectedResult, $data);
				}
				break;
		}
	}

	private function insertNodes ($node, $parent)
	{
		return $this->hdatahandler->insertNode(array('node' => $node), array('field' => 'node', 'value' => $parent));
	}

	public function cleanupTest ()
	{
		return OTK_RESULT_NONE;
	}

	public function getDetails ()
	{
		return $this->details;
	}

	private function getExpected ($step)
	{
		if ($step == 1) {
			return array(
				 array('lval' =>  1,'rval' => 46,'node' => 'Musical instruments','xlink' => null)
				,array('lval' =>  2,'rval' => 21,'node' => 'Wind','xlink' => null)
				,array('lval' =>  3,'rval' => 14,'node' => 'Wood','xlink' => null)
				,array('lval' =>  4,'rval' =>  5,'node' => 'Clarinet','xlink' => null)
				,array('lval' =>  6,'rval' =>  7,'node' => 'Saxophone','xlink' => null)
				,array('lval' =>  8,'rval' =>  9,'node' => 'Oboe','xlink' => null)
				,array('lval' => 10,'rval' => 11,'node' => 'Bassoon','xlink' => null)
				,array('lval' => 12,'rval' => 13,'node' => 'Flute','xlink' => null)
				,array('lval' => 15,'rval' => 20,'node' => 'Brass','xlink' => null)
				,array('lval' => 16,'rval' => 17,'node' => 'Trumpet','xlink' => null)
				,array('lval' => 18,'rval' => 19,'node' => 'Horn','xlink' => null)
				,array('lval' => 22,'rval' => 37,'node' => 'Strings','xlink' => null)
				,array('lval' => 23,'rval' => 28,'node' => 'Plucked','xlink' => null)
				,array('lval' => 24,'rval' => 25,'node' => 'Guitar','xlink' => null)
				,array('lval' => 26,'rval' => 27,'node' => 'Chapman stick','xlink' => null)
				,array('lval' => 29,'rval' => 34,'node' => 'Bowed','xlink' => null)
				,array('lval' => 30,'rval' => 31,'node' => 'Violin','xlink' => null)
				,array('lval' => 32,'rval' => 33,'node' => 'Cello','xlink' => null)
				,array('lval' => 35,'rval' => 36,'node' => 'Piano','xlink' => null)
				,array('lval' => 38,'rval' => 45,'node' => 'Percussion','xlink' => null)
				,array('lval' => 39,'rval' => 40,'node' => 'Cymbal','xlink' => null)
				,array('lval' => 41,'rval' => 44,'node' => 'Tuned','xlink' => null)
				,array('lval' => 42,'rval' => 43,'node' => 'Timpani','xlink' => null)
			);
		}
		if ($step == 2) {
			return array(
				 array('lval' =>  1,'rval' => 50,'node' => 'Musical instruments','xlink' => null)
				,array('lval' =>  2,'rval' => 25,'node' => 'Wind','xlink' => null)
				,array('lval' =>  3,'rval' => 18,'node' => 'Wood','xlink' => null)
				,array('lval' =>  4,'rval' =>  5,'node' => 'Flute','xlink' => null)
				,array('lval' =>  6,'rval' => 11,'node' => 'Single reed','xlink' => null)
				,array('lval' =>  7,'rval' =>  8,'node' => 'Clarinet','xlink' => null)
				,array('lval' =>  9,'rval' => 10,'node' => 'Saxophone','xlink' => null)
				,array('lval' => 12,'rval' => 17,'node' => 'Double reed','xlink' => null)
				,array('lval' => 13,'rval' => 14,'node' => 'Oboe','xlink' => null)
				,array('lval' => 15,'rval' => 16,'node' => 'Bassoon','xlink' => null)
				,array('lval' => 19,'rval' => 24,'node' => 'Brass','xlink' => null)
				,array('lval' => 20,'rval' => 21,'node' => 'Trumpet','xlink' => null)
				,array('lval' => 22,'rval' => 23,'node' => 'Horn','xlink' => null)
				,array('lval' => 26,'rval' => 41,'node' => 'Strings','xlink' => null)
				,array('lval' => 27,'rval' => 32,'node' => 'Plucked','xlink' => null)
				,array('lval' => 28,'rval' => 29,'node' => 'Guitar','xlink' => null)
				,array('lval' => 30,'rval' => 31,'node' => 'Chapman stick','xlink' => null)
				,array('lval' => 33,'rval' => 38,'node' => 'Bowed','xlink' => null)
				,array('lval' => 34,'rval' => 35,'node' => 'Violin','xlink' => null)
				,array('lval' => 36,'rval' => 37,'node' => 'Cello','xlink' => null)
				,array('lval' => 39,'rval' => 40,'node' => 'Piano','xlink' => null)
				,array('lval' => 42,'rval' => 49,'node' => 'Percussion','xlink' => null)
				,array('lval' => 43,'rval' => 44,'node' => 'Cymbal','xlink' => null)
				,array('lval' => 45,'rval' => 48,'node' => 'Tuned','xlink' => null)
				,array('lval' => 46,'rval' => 47,'node' => 'Timpani','xlink' => null)
			);
		}
		if ($step == 3) {
			return array(
				 array('lval' =>  1,'rval' => 48,'node' => 'Musical instruments','xlink' => null)
				,array('lval' =>  2,'rval' => 25,'node' => 'Wind','xlink' => null)
				,array('lval' =>  3,'rval' => 18,'node' => 'Wood','xlink' => null)
				,array('lval' =>  4,'rval' =>  5,'node' => 'Flute','xlink' => null)
				,array('lval' =>  6,'rval' => 11,'node' => 'Single reed','xlink' => null)
				,array('lval' =>  7,'rval' =>  8,'node' => 'Clarinet','xlink' => null)
				,array('lval' =>  9,'rval' => 10,'node' => 'Saxophone','xlink' => null)
				,array('lval' => 12,'rval' => 17,'node' => 'Double reed','xlink' => null)
				,array('lval' => 13,'rval' => 14,'node' => 'Oboe','xlink' => null)
				,array('lval' => 15,'rval' => 16,'node' => 'Bassoon','xlink' => null)
				,array('lval' => 19,'rval' => 24,'node' => 'Brass','xlink' => null)
				,array('lval' => 20,'rval' => 21,'node' => 'Trumpet','xlink' => null)
				,array('lval' => 22,'rval' => 23,'node' => 'Horn','xlink' => null)
				,array('lval' => 26,'rval' => 41,'node' => 'Strings','xlink' => null)
				,array('lval' => 27,'rval' => 32,'node' => 'Plucked','xlink' => null)
				,array('lval' => 28,'rval' => 29,'node' => 'Guitar','xlink' => null)
				,array('lval' => 30,'rval' => 31,'node' => 'Chapman stick','xlink' => null)
				,array('lval' => 33,'rval' => 38,'node' => 'Bowed','xlink' => null)
				,array('lval' => 34,'rval' => 35,'node' => 'Violin','xlink' => null)
				,array('lval' => 36,'rval' => 37,'node' => 'Cello','xlink' => null)
				,array('lval' => 39,'rval' => 40,'node' => 'Piano','xlink' => null)
				,array('lval' => 42,'rval' => 47,'node' => 'Percussion','xlink' => null)
				,array('lval' => 43,'rval' => 44,'node' => 'Cymbal','xlink' => null)
				,array('lval' => 45,'rval' => 46,'node' => 'Timpani','xlink' => null)
			);
		}
		if ($step == 4) {
			return array(
				 array('lval' =>  1,'rval' => 24,'node' => 'Musical instruments','xlink' => null)
				,array('lval' =>  2,'rval' => 17,'node' => 'Strings','xlink' => null)
				,array('lval' =>  3,'rval' =>  8,'node' => 'Plucked','xlink' => null)
				,array('lval' =>  4,'rval' =>  5,'node' => 'Guitar','xlink' => null)
				,array('lval' =>  6,'rval' =>  7,'node' => 'Chapman stick','xlink' => null)
				,array('lval' =>  9,'rval' => 14,'node' => 'Bowed','xlink' => null)
				,array('lval' => 10,'rval' => 11,'node' => 'Violin','xlink' => null)
				,array('lval' => 12,'rval' => 13,'node' => 'Cello','xlink' => null)
				,array('lval' => 15,'rval' => 16,'node' => 'Piano','xlink' => null)
				,array('lval' => 18,'rval' => 23,'node' => 'Percussion','xlink' => null)
				,array('lval' => 19,'rval' => 20,'node' => 'Cymbal','xlink' => null)
				,array('lval' => 21,'rval' => 22,'node' => 'Timpani','xlink' => null)
			);
		}
		if ($step == 5) {
			return array('Piano', 'Synthesizer');
		}
	}
}