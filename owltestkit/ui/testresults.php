<?php
/**
 * \file
 * \ingroup OTK_UI_LAYER
 * This file creates the area to show the testresults
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: testresults.php,v 1.4 2011-10-16 11:11:45 oscar Exp $
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
