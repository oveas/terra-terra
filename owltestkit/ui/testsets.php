<?php
/**
 * \file
 * \ingroup OTK_UI_LAYER
 * This file creates the area to list testsets
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: testsets.php,v 1.1 2011-05-23 17:56:18 oscar Exp $
 */
if (!OWLloader::getClass('form')) {
	trigger_error('Error loading the Form class', E_USER_ERROR);
}
/**
 * \ingroup ICV_UI_LAYER
 * Setup the contentarea showing all kowledge items
 * \brief List knowledge
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 18, 2011 -- O van Eijk -- initial version
 */
class TestsetsArea extends ContentArea
{
	private $testKit;
	/**
	 * List the Knowledge table
	 * \todo This one must be rewritten using AJAX
	 */
	public function loadArea()
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

		$_html = $this->trn('Available testsets');

		foreach ($sets as $n => $d) {
			$_fld = $form->addField('checkbox', "set[$n]", 1);
			$_lbl = $d . '<br/>';
			$_cntnr = new Container('label', $_lbl, array(), array('for' => &$_fld));
			$form->addToContent($_fld);
			$form->addToContent($_cntnr);
		}

		$_fld = $form->addField('submit', 'act', $this->trn('Start tests'));
		$form->addToContent($_fld);
		
		$this->contentObject = new Container('div', $_html, array('class' => 'testArea'));
		$this->contentObject->addToContent($form);
	}
}
