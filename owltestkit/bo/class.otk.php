<?php
/**
 * \file
 * This file defines OTK mainclass
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.otk.php,v 1.3 2011-10-16 11:11:45 oscar Exp $
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