<?php
/**
 * \file
 * Define all output methods
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \defgroup OutputMethods Methods to send output to the output channel
 * @{
 */
//! Raw output - text will be echoed as is
define ('TT_OUTPUT_RAW',	1);
//! Format the text for use with AJAX (reserved)
define ('TT_OUTPUT_AJAX',	2);
//! Format the text as a line
define ('TT_OUTPUT_LINE',	3);
//! Format the text as a paragraph (div)
define ('TT_OUTPUT_PAR',	4);
//! Send the output to the browser without buffering
define ('TT_OUTPUT_NOW',	5);
// @}

/**
 * \ingroup TT_SO_LAYER
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
	 * Boolean set to false when output is not being buffered.
	 */
	static private $outputBuffering = true;

	/**
	 * Entry method for the output class which calls to correct method for the required output,
	 * although these output methods can also be called directly.
	 * \param[in] $text Text that should be send to the output channel. Can contain HTML
	 * \param[in] $method Format method, must be a method as defined in \ref OutputMethods. If anything
	 * else is given, TT_OUTPUT_RAW is assumed.
	 * \param[in] $class Optional CSS class (ignored for TT_OUTPUT_RAW and TT_OUTPUT_AJAX)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function output ($text, $method = TT_OUTPUT_RAW, $class = null)
	{
		switch ($method) {
			case TT_OUTPUT_AJAX :
				self::outputAjax($text);
				break;
			case TT_OUTPUT_LINE :
				self::outputLine($text, $class);
				break;
			case TT_OUTPUT_PAR :
				self::outputPar($text, $class);
				break;
			case TT_OUTPUT_NOW :
				self::outputImmediatly($text, $class);
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
	 * \param[in] $terminate When true, further execution is terminated
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function outputAjax($text, $terminate = false)
	{
		echo $text;
		if ($terminate !== false) {
			exit;
		}
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
	
	/**
	 * Send text to the browser, immediatly flushing all output buffers.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function outputImmediatly($text, $class = null)
	{
		self::outputDisableBuffering();
		self::outputLine($text . "\n", $class);
		@ob_flush();
		@flush();
	}

	/**
	 * Send all buffered output to the browser.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static public function outputNow()
	{
		self::outputDisableBuffering();
		@ob_flush();
		@flush();
	}

	/**
	 * Disable output buffering
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	static private function outputDisableBuffering()
	{
		if (self::$outputBuffering === true) {
			ini_set('zlib.output_compression', '0');
			ob_end_flush();
			self::$outputBuffering = false;
		}
	}
}
