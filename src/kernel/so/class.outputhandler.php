<?php
/**
 * \file
 * Define all output methods
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.outputhandler.php,v 1.3 2011-09-26 16:04:36 oscar Exp $
 */

/**
 * \defgroup OutputMethods Methods to send output to the output channel
 * @{
 */
//! Raw output - text will be echoed as is
define ('OWL_OUTPUT_RAW',	1);
//! Format the text for use with AJAX (reserved)
define ('OWL_OUTPUT_AJAX',	2);
//! Format the text as a line
define ('OWL_OUTPUT_LINE',	3);
//! Format the text as a paragraph (div)
define ('OWL_OUTPUT_PAR',	4);
// @}

/**
 * \ingroup OWL_SO_LAYER
 * This abstract class contains all methods to send output to different channels. Although
 * the normal echo statement can be used, it is adviseable to use the methods from this class.
 * \brief Output handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 30, 2011 -- O van Eijk -- initial version
 */
abstract class OutputHandler
{
	/**
	 * Boolean to keep track if the brow
	 */
	static private $outputStarted = false;

	/**
	 * Entry method for the output class which calls to correct method for the required output,
	 * although these output methods can also be called directly.
	 * \param[in] $text Text that should be send to the output channel. Can contain HTML
	 * \param[in] $method Format method, must be a method as defined in \ref OutputMethods. If anything
	 * else is given, OWL_OUTPUT_RAW is assumed.
	 * \param[in] $class Optional CSS class (ignored for OWL_OUTPUT_RAW and OWL_OUTPUT_AJAX)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function output ($text, $method = OWL_OUTPUT_RAW, $class = null)
	{
		switch ($method) {
			case OWL_OUTPUT_AJAX :
				self::outputAjax($text);
				break;
			case OWL_OUTPUT_LINE :
				self::outputLine($text, $class);
				break;
			case OWL_OUTPUT_PAR :
				self::outputPar($text, $class);
				break;
			default :
				self::outputRaw($text);
				break;
		}
	}

	/**
	 * Send the output unformatted to the standard output channel
	 * \param[in] $text Text that should be send to the output channel. Can contain HTML
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function outputRaw($text)
	{
		self::$outputStarted = true;
		echo $text;
	}

	/**
	 * Send the output to the standard output channel as an array statement parseable by PHP.
	 * This is a developer helper function.
	 * \param[in] $array Array that should be sent to the output channel.
	 * \param[in] $root Name of the array at the current level. By default, starts with 'myArray'
	 * \param[in] $depth Current depth. Starts with 0 and increases on every recursive call
	 * \todo Change this method to support the output in a single define including all sublevels.
	 * \author machuidel (taken from http://php.net/manual/en/function.print-r.php)
	 */
	static public function outputPhpArray(array &$array, $root = '$myArray', $depth = 0)
	{
		$items = array();
		$output = '';
		foreach($array as $key => &$value) {
			if(is_array($value)) {
				$output .= self::outputPhpArray($value, $root . '[\'' . $key . '\']', $depth + 1);
			} else {
				$items[$key] = $value;
			}
		}

		if(count($items) > 0) {
			$output .= $root . ' = array(';
			$prefix = '';
			foreach($items as $key => &$value) {
				$output .= $prefix . '\'' . $key . '\' => \'' . addslashes($value) . '\'';
				$prefix = ', ';
			}
			$output .= ');' . "\n";
		}
		if ($depth === 0) {
			self::$outputStarted = true;
			echo '<pre>'.$output.'</pre>';
		} else {
			return $output;
		}
	}

	/**
	 * Send the output unformatted to output channel for Ajax
	 * \param[in] $text Text that should be send to the output channel. Can contain HTML
	 * \todo Write this method....
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function outputAjax($text)
	{
		// Reserved... just a placeholder now
	}

	/**
	 * Send the output to the standard output channel, formatted with a &lt;span&gt; and terminated
	 * with &lt;br/&gt;
	 * \param[in] $text Text that should be send to the output channel. Can contain HTML
	 * \param[in] $class Optional CSS class used for the span tag
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function outputLine($text, $class = null)
	{
		self::$outputStarted = true;
		$_attr = array();
		if ($class !== null) {
			$_attr['class'] = $class;
		}
		$_output = new Container('span', $text, $_attr);
		echo $_output->showElement() . '<br/>';
	}

	/**
	 * Send the output to the standard output channel, formatted with a &lt;div&gt;
	 * \param[in] $text Text that should be send to the output channel. Can contain HTML
	 * \param[in] $class Optional CSS class used for the div tag
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function outputPar($text, $class = null)
	{
		self::$outputStarted = true;
		$_attr = array();
		if ($class !== null) {
			$_attr['class'] = $class;
		}
		$_output = new Container('div', $text, $_attr);
		echo $_output->showElement();
	}

	/**
	 * Check if output has been sent to the browser already
	 * \return True if output was sent
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function outputStarted()
	{
		return self::$outputStarted;
	}
}