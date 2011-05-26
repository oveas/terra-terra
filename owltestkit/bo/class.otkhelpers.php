<?php
/**
 * \file
 * This file defines the OWL TestKit helper class
 * \version $Id: class.otkhelpers.php,v 1.1 2011-05-26 12:26:30 oscar Exp $
 */

/**
 * \ingroup OTK_BO_LAYER
 * Abstract class with some general functions.
 * \brief OTK Helper
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 26, 2011 -- O van Eijk -- initial version
 */
abstract class OTKHelpers
{
	/**
	 * Create a table with expected and actual testresults compared side by side
	 * \param[in] $expected The expected testresult
	 * \param[in] $actual The actual testresult
	 * \return HTML code
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function compareTable($expected, $actual)
	{
		if (!OWLloader::getClass('table')) {
			trigger_error('Error loading the Table class', E_USER_ERROR);
		}
		$table = new Table(array('style'=>'border: 1px; width: 100%;'));
		$hdrRow = $table->addRow();
		$hdrRow->setHeader();
		$hdrRow->addCell(ContentArea::translate('Expected result'));
		$hdrRow->addCell(ContentArea::translate('Actual result'));
		$row = $table->addRow();
		if (is_array($expected)) {
			$expected = print_r($expected, 1);
		}
		if (is_array($actual)) {
			$actual = print_r($actual, 1);
		}
		$row->addCell('<pre>'.$expected.'</pre>', array('valign' => 'top'));
		$row->addCell('<pre>'.$actual.'</pre>', array('valign' => 'top'));
		return $table->showElement();
	}
}
