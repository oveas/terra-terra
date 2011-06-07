<?php
/**
 * \file
 * \ingroup OTK_UI_LAYER
 * This file creates the area to show the testresults
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: testresults.php,v 1.3 2011-06-07 14:06:56 oscar Exp $
 */
/**
 * \ingroup ICV_UI_LAYER
 * Setup the contentarea showing all testresults
 * \brief List knowledge
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 18, 2011 -- O van Eijk -- initial version
 */
class TestresultsArea extends ContentArea
{
	private $testKit;

	/**
	 * Show all results for a given testset
	 * \param[in] $arg Array with all results
	 */
	public function loadArea($arg = null)
	{
		$_html = '<h2>' . $this->trn('Testset $p1$', array($arg['testset'])) . '</h2>';

		foreach ($arg['result'] as $_case => $_res) {
			$_html .= '<h3>' . $this->trn('Testcase $p1$', array($_case)) . '</h3>';
			$_counter = 0;
			foreach ($_res as $_step) {
				if (($_code = $_step[0]) === OTK_RESULT_NONE) {
					$_counter++;
					continue;
				}
				$_msg = $_counter++ . '. ' . $_step[1];
				switch ($_code) {
					case OTK_RESULT_FAIL :
						$_class = 'resultFail';
						break;
					case OTK_RESULT_SUCCESS :
						$_class = 'resultSuccess';
						break;
					case OTK_RESULT_WARNING :
						$_class = 'resultWarning';
						break;
					case OTK_RESULT_SKIPPED :
						$_class = 'resultSkipped';
						break;
				}
				$_div = new Container('div', $_msg, array('class' => $_class));
				$_html .= $_div->showElement();
			}
		}
		if ($arg['details']) {
			$_div = new Container('div'
				, '<h3>' . $this->trn('Details') . '</h3>' . $arg['details']
				, array('class' => 'resultDetails')
			);
			$_html .= $_div->showElement();
		}

		$this->contentObject = new Container('div', $_html, array('class' => 'testArea'));
		$this->contentObject->addToContent($form);
	}
}
