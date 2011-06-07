<?php
/**
 * \file
 * This file loads all testsets
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.testkit.php,v 1.2 2011-06-07 15:03:31 oscar Exp $
 */
if (!OWLloader::getClass('testset', OTK_BO)) {
	trigger_error('Error loading the TestSet baseclass', E_USER_ERROR);
}

/**
 * \ingroup OTK_SO
 * Singleton class that loads all testsets
 * \brief Testset loader
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 19, 2011 -- O van Eijk -- initial version
 */
class TestKit
{

	/**
	 * Indexed array with all testsets and their descriptions
	 */
	private $sets;

	/**
	 * Self reference
	 */
	private static  $instance;

	/**
	 * Class constructor; scan the testsets directory for subdirectories that can contain testsets,
	 * and load all testset - classfiles that have been found.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function __construct()
	{
		if ($dH = opendir(OTK_TESTSETS)) {
			while (($fName = readdir($dH)) !== false) {
				if ($fName != '.' && $fName != '..' && is_dir(OTK_TESTSETS . '/' . $fName)) {
					if ($this->loadTestSet($fName) === false) {
						// TODO some signalling. Are we gonna use OWL for that?
					}
				}
			}
		}
		closedir($dH);
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \return Object instance ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInstance()
	{
		if (!TestKit::$instance instanceof self) {
			TestKit::$instance = new self();
		}
		return TestKit::$instance;
	}

	/**
	 * Check if a directory contains a testset. If so, load the classfile and add the testset name
	 * and description to the internal array.
	 * \param[in] $name Name of the directory to check
	 * \return null if the directory contains no valid testset, true when successfully loaded, false on errors.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function loadTestSet ($name)
	{
		if (!file_exists(OTK_TESTSETS . '/' . $name . '/testset.php')) {
			return (null);
		}
		require (OTK_TESTSETS . '/' . $name . '/testset.php');
		$testSet = $this->getClassName($name);
		if (!class_exists($testSet)) {
			return (false);
		}
		// Use call_user_func below.
		// $testSet::getDescription() will work since PHP 5.3.0, but my Eclipse PDT still signals a syntax error :-S
		$this->sets[ucfirst($name)] = call_user_func(array($testSet, 'getDescription'));
		return (true);
	}

	/**
	 * Return the available tests
	 * \return Testsets as an indexed array in the format (name => description)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getTestSets()
	{
		return $this->sets;
	}

	/**
	 * Perform the testcases of a given testset
	 * \param[in] $testSet Testset to perform
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function performTests ($testSet)
	{
		$_class = $this->getClassName($testSet);
		$set = new $_class();
		$set->loadTestcases (OTK_TESTSETS.'/'.strtolower($testSet));
		$set->performTests();
		flush(); // Send buffer now
	}

	/**
	 * Translate the setname (taken from the directory name) into a classname in the required format
	 * \param[in] $setName Name of the set and directory
	 * \return Classname
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getClassName ($setName)
	{
		return 'OTK' . ucfirst($setName);
	}
}