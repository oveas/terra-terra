<?php
/**
 * \file
 * This file defines the testset baseclass
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.testset.php,v 1.5 2011-05-30 17:00:19 oscar Exp $
 */

OWLloader::getClass('testcase', OTK_BO);

/**
 * \defgroup Test_Results Test result code
 * These codes define valid return values for the steps in the testcases
 * @{
 */
//! Testcase ended with a failure
define ('OTK_RESULT_FAIL'		, 0);

//! The testscase ended successfully
define ('OTK_RESULT_SUCCESS'	, 1);

//! The testcase ended with a warning
define ('OTK_RESULT_WARNING'	, 2);

//! The testcase was skipped as a result of an earlier failure or warning
define ('OTK_RESULT_SKIPPED'	, 3);

//! The teststep had nothing to do, ignore it. This code is reserved for prepareTest() and cleanupTest()
define ('OTK_RESULT_NONE'		, 3);
// @}

/**
 * \ingroup OTK_BO
 * Baseclass that defines the testsets.
 * Classes that derive from this baseclass must have the name <em>OTK&lt;Location&gt;</em>, where
 * 'location' is the name of the subdirectory in OTK_TESTSETS. The filename must be <em>testset.php</em>.
 *
 * The classes only need to reimplement the static getDescription() method, all other required methods
 * are in this baseclass.
 *
 * The class collects all files belonging to a testset that contain testcases and helper functions. All
 * files must be in the same directory as <em>testset.php</em>.
 *
 * Files that contain testcases must have the name <em>case.&lt;name&gt;.php</em>. Refer to the interface TestCase
 * for more information.
 *
 * Optionally, testset directories can contain a file with the name <em>helper.php</em>. This can be a
 * file containing one or more classes or functions. That file will be included but not called; it is
 * up to the testcases to make the proper calls to these files.
 * In order prevent duplicate class or function names, it is a good practice to include the name of the testset in
 * in all names.
 * \brief Testset
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 19, 2011 -- O van Eijk -- initial version
 */
abstract class TestSet
{
	/**
	 * Name of the testset. It will be taken from the classname (without the leading 'OTK')
	 */
	private $setName;

	/**
	 * Full path to the helper file ('helper.php') if this set contains one
	 */
	private $helper;

	/**
	 * Indexed array holding all testcases found in this set
	 */
	private $testCases;

	/**
	 * Details description of the testresults
	 */
	private $details;

	/**
	 * Indexed array with the results of all steps per testcase
	 */
	private $testResults;

	/**
	 * Total number of teststeps that ended in an error
	 */
	private $errors;

	/**
	 * Total number of teststeps that ended with a warning
	 */
	private $warnings;

	/**
	 * Total number of teststeps that completed successfull
	 */
	private $succeeded;

	/**
	 * Total number of teststeps that have been skipped
	 */
	private $skipped;

	/**
	 * Class constructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct()
	{
		$this->setName = get_class($this);
		$this->setName = str_replace('OTK', '', $this->setName);
		$this->testCases = array();
		$this->testResults = array();
		$this->helper = null;
		$this->errors = 0;
		$this->succeeded = 0;
		$this->skipped = 0;
		$this->warnings = 0;
		$this->details = '';
	}

	/**
	 * This is the only required method that should be implemented by Testset classes; it shows
	 * a description of the testset.
	 * \note This method must be reimplemented, but it cannot be declared abstract since it is
	 * a static method. Since it is static, it does belong to the class itself, meaning this
	 * method here will never be called; it is included just for documentation purposes.
	 * \return A textstring (HTML allowed) describing the testset
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function getDescription ()
	{
		return 'Provide testcase description';
	}

	/**
	 * Scan the testset directory for all files holeing either testcases (<em>case.&lt;name&gt;.php</em>)
	 * or helper functions or class (<em>helper.php</em>).
	 * \param[in] $location Directory to scan
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function loadTestcases ($location)
	{
		if ($dH = opendir($location)) {
			while (($fName = readdir($dH)) !== false) {
				if (is_file($location . '/' . $fName)) {
					if ($fName == 'helper.php') {
						$this->helper = $location . '/' . $fName;
					} elseif (preg_match('/^case\.([a-z0-9_-]+?)\.php$/', $fName, $_match)) {
						$this->testCases[$_match[1]] = $location . '/' . $fName;
					} else {
						; // Ignore other files
					}
				}
			}
		}
		closedir($dH);
	}

	/**
	 * Go through all selected testcases and execute them
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function performTests ()
	{
		OWLTimers::startTimer($this->setName);
		if ($this->helper !== null) {
			require $this->helper;
		}
		if (array_key_exists('first', $this->testCases)) {
			$this->doTest ('first', $this->testCases['first']);
		}
		foreach ($this->testCases as $name => $file) {
			if ($name != 'first' && $name != 'last') {
				$this->doTest ($name, $file);
			}
		}
		if (array_key_exists('last', $this->testCases)) {
			$this->doTest ('last', $this->testCases['last']);
		}
		OWLTimers::stopTimer($this->setName);
		$this->showTestResults();
	}

	/**
	 * Execute a single testcase
	 * \param[in] $name Name of the testcase
	 * \param[in] $file Full path to the testcase file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function doTest ($name, $file)
	{
		require $file;
		$this->testResults[$name] = array();
		$_class = 'OTK' . ucfirst($this->setName) . '_' . ucfirst($name);
		$_case = new $_class();

		if (($_r = $_case->prepareTest()) === OTK_RESULT_SUCCESS) {
			$this->testResults[$name][] = array(OTK_RESULT_SUCCESS, 'Test preparation completed successfully');
			$this->succeeded++;
		} elseif ($_r === OTK_RESULT_NONE) {
			$this->testResults[$name][] = array(OTK_RESULT_NONE, '--- will be ignored ---');
		} else {
			$this->testResults[$name][] = array(OTK_RESULT_FAIL, $_r);
			$this->errors++;
			return; // Can't go on...
		}

		$_r = $_case->performTest();
		if (!is_array($_r)) {
			$this->testResults[$name][] = array(OTK_RESULT_WARNING, 'Testcase returned an invalid result - not an array!');
			$this->warnings++;
		} else {
			$_steps = 1;
			foreach ($_r as $_step) {
				if (!is_array($_step)) {
					$this->testResults[$name][] = array(OTK_RESULT_WARNING, "Step $_steps returned an invalid result - not an array!");
					$this->warnings++;
				} elseif (count($_step) != 2 || !is_string($_step[1]) ||
						($_step[0] !== OTK_RESULT_FAIL && $_step[0] !== OTK_RESULT_SUCCESS
							&& $_step[0] !== OTK_RESULT_WARNING && $_step[0] !== OTK_RESULT_SKIPPED)) {
						$this->testResults[$name][] = array(OTK_RESULT_WARNING, "Step $_steps returned an invalid result - incorrect array format!");
					$this->warnings++;
				} else {
					$this->testResults[$name][] = $_step;
					switch ($_step[0]) {
						case OTK_RESULT_SUCCESS:
							$this->succeeded++;
							break;
						case OTK_RESULT_FAIL:
							$this->errors++;
							break;
						case OTK_RESULT_WARNING:
							$this->warnings++;
							break;
						case OTK_RESULT_SKIPPED:
							$this->skipped++;
							break;
						case OTK_RESULT_NONE:
							$this->testResults[$name][] = array(OTK_RESULT_WARNING, "Step $_steps returned OTK_RESULT_NONE which is reserved for prepareTest() and cleanupTest()");
							$this->warnings++;
							break;
					}
				}
			}
		}

		if (($_r = $_case->cleanupTest()) === OTK_RESULT_SUCCESS) {
			$this->testResults[$name][] = array(OTK_RESULT_SUCCESS, 'Test cleanup completed successfully');
			$this->succeeded++;
		} elseif ($_r === OTK_RESULT_NONE) {
			; // Nothing to do
		} else {
			$this->testResults[$name][] = array(OTK_RESULT_WARNING, $_r);
			$this->warnings++;
		}
		if (($_details = $_case->getDetails()) !== null) {
			$this->details .= $_details;
		}
	}

	/**
	 * Create the contentarea to display the results.
	 * To make sure we don't need to wait until all tests completed, the output is echoed immediatly.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function showTestResults ()
	{
		$results = array(
			 'testset' => $this->setName
			,'result' => $this->testResults
			,'details' => $this->details
		);
		if (($_area = OWLloader::getArea('testresults', OTK_UI, $results)) !== null) {
			OutputHandler::outputRaw($_area->getArea());
		}
	}
}