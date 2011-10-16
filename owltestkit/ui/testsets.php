<?php
/**
 * \file
 * \ingroup OTK_UI_LAYER
 * This file creates the area to list testsets
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: testsets.php,v 1.4 2011-10-16 11:11:45 oscar Exp $
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
if (!OWLloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
/**
 * \ingroup ICV_UI_LAYER
 * Setup the contentarea showing all testcases
 * \brief List knowledge
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 18, 2011 -- O van Eijk -- initial version
 */
class TestsetsArea extends ContentArea
{
	private $testKit;

	/**
	 * Show the list to select available testcases
	 * \param[in] $arg Not used here but required by ContentArea
	 */
	public function loadArea($arg = null)
	{
		// Create a new form
		$form = new Form(
			  array(
				 'application' => 'testkit'
				,'include_path' => 'OTK_BO'
				,'class_file' => 'otk'
				,'class_name' => 'OTK'
				,'method_name' => 'doTests'
			)
			, array(
				 'name' => 'testsetForm'
			)
		);

		$this->testKit = OWL::factory('testkit', OTK_SO);
		$sets = $this->testKit->getTestSets();

		foreach ($sets as $n => $d) {
			$_fld = $form->addField('checkbox', "set[$n]", 1);
			$_lbl = $d . '<br/>';
			$_cntnr = new Container('label', $_lbl, array(), array('for' => &$_fld));
			$form->addToContent($_fld);
			$form->addToContent($_cntnr);
		}

		$_fld = $form->addField('submit', 'act', $this->trn('Start tests'));
		$form->addToContent($_fld);

		$this->contentObject = new Container('div', $this->trn('Available testsets'), array('class' => 'testArea'));
		$this->contentObject->addToContent($form);
	}
}
