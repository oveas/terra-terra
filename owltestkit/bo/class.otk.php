<?php
/**
 * \file
 * This file defines OTK mainclass
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.otk.php,v 1.2 2011-05-26 12:26:30 oscar Exp $
 */

/**
 * \ingroup OTK_BO
 * Class that contains all methods that will be called by the OWL dispatchers
 * \brief OTK mainclass
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 19, 2011 -- O van Eijk -- initial version
 */class OTK extends _OWL
{
	/**
	 * Reference to the Testkit containerobject
	 */
	private $testKit;

	/**
	 * Class constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		parent::init();
		$this->testKit = OWL::factory('testkit', OTK_SO);
	}

	/**
	 * Load the area from which the testsets can be selected for execution
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function selectTestCases ()
	{
		if (($_area = OWLloader::getArea('testsets', OTK_UI)) !== null) {
			$_area->addToDocument($GLOBALS['OTK']['BodyContainer']);
		}
	}

	/**
	 * Read from the formdata which testsets must be executed and perform those tests.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function doTests()
	{
		$_form = OWL::factory('FormHandler');
		$_sets = $_form->get('set');
		if (count($_sets) > 0) {
			foreach ($_sets as $_set => $_val) {
				if ($_val) {
					$this->testKit->performTests($_set);
				}
			}
		}
	}

}