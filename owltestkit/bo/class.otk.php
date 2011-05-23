<?php
class OTK extends _OWL
{
	private $testKit;

	public function __construct()
	{
		parent::init();
		$this->testKit = OWL::factory('testkit', OTK_SO);
	}

	public function selectTestCases ()
	{
		if (($_area = OWLloader::getArea('testsets', OTK_UI)) !== null) {
			$_area->addToDocument($GLOBALS['OTK']['BodyContainer']);
		}
	}

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

	public function showOptions ()
	{
		if (($_reg = OWLloader::getArea('registerlink', ICV_UI . '/user')) !== null) {
			$_reg->addToDocument($GLOBALS['ICV']['HeaderContainer']);
		}
		$dispatcher = OWL::factory('Dispatcher');
		$dispatcher->dispatch('i-cview#ICV_BO#icvknowledge#iCVKnowledge#showAddLink');
		$dispatcher->dispatch('i-cview#ICV_BO#icvknowledge#iCVKnowledge#showListLink');
		
	}
}