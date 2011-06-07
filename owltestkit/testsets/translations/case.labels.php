<?php
/**
 * \file
 * This file defines the testcase that checks untranslated labels
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: case.labels.php,v 1.1 2011-06-07 14:06:55 oscar Exp $
 */

/**
 * \ingroup OTK_TESTSETS
 * Check all labels that are called for translation but not in the label file
 * \brief Check labels
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jun 7, 2011 -- O van Eijk -- initial version
 */
class OTKTranslations_Labels implements TestCase
{
	// List of messages
	private $labels;

	// Hold details of the testresults
	private $details;

	// Array with return values
	private $returnCodes;

	public function __construct()
	{
		$this->details = '';
		$this->labels = array();
	}

	public function prepareTest ()
	{
		// Load messages
		$_lang = ConfigHandler::get ('locale|lang');
		if (file_exists (OWL_LIBRARY . '/owl.labels.' . $_lang . '.php')) {
			$file = OWL_LIBRARY . '/owl.labels.' . $_lang . '.php';
		} elseif (file_exists (OWL_LIBRARY . '/owl.labels.php')) {
			$file = OWL_LIBRARY . '/owl.labels.php';
		} else {
			return 'No label file found for language code ' . $_lang . ' and the default file is missing';
		}
		$this->details .= 'Checking label file ' . $file . '<br/>';

		if (!($mFile = fopen($file, 'r'))) {
			return 'Error opening ' . $file;
		}

		while (($line = fgets($mFile, 1024)) !== false) {
			if (preg_match("/\s+,?\s+('|\")(.*?)(\1)\s+=>\s+'(.*?)'/i", $line, $match)) {
				$this->labels[$match[1]] = $match[3];
			}
		}
		fclose($mFile);

		return OTK_RESULT_SUCCESS;
	}

	public function performTest ()
	{
		$this->returnCodes = array();

		$topLocation = OWL_ROOT;
		$this->checkDirectory ($topLocation);
		if (count($this->returnCodes) == 0) {
			$this->returnCodes[] = array(OTK_RESULT_SUCCESS, "All labels have been translated");
		}
		return $this->returnCodes;
	}

	private function checkDirectory ($dir)
	{
		if (($dHandle = opendir($dir)) === false) {
			$this->returnCodes[] = array(OTK_RESULT_FAIL, "Error opening directory $dir for read");
			return;
		}
		while ($file = readdir($dHandle)) {
			if ($file == '.' || $file == '..') {
				continue;
			} elseif (is_dir ("$dir/$file")) {
				$this->checkDirectory("$dir/$file");
			} elseif (preg_match('/\.php$/', $file)) {
				$this->checkFile("$dir/$file");
			} else {
				; // No PHP file of subdirectory - nothing to do
			}
		}
		return;
	}

	private function checkFile ($file)
	{
		if (($fHandle = fopen($file, 'r')) === false) {
			$this->returnCodes[] = array(OTK_RESULT_WARNING, "Error opening file $file for read");
			return;
		}
		$lineCounter = 0;
		while ($line = fgets($fHandle, 1024)) {
			$lineCounter++;
			if (preg_match("/(::translate|->trn)\s*\('(.*?)'\)/i", $line, $matches)) {
				if (!array_key_exists($matches[2], $this->labels)) {
					$this->returnCodes[] = array(OTK_RESULT_WARNING, "Label <u><em>$matches[2]</em></u> has no translation in file $file on line $lineCounter");
				}
			}
		}
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