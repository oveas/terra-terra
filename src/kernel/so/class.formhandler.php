<?php
/**
 * \file
 * This file defines the Formhandler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \name Form parse format flags
 * These flags define how formdata should be returned
 * @{
 */
//! Remove all HTML tags before returning anything
define ('FORMDATA_STRICT',			0);

//! Remove all tags except bold, underline etc, lists, line breaks, headers and such
define ('FORMDATA_BASIC_HTML',		1);

//! Remove all tags except the basic HTML tags, font and table tags, divs, spans, images and anchors
define ('FORMDATA_EXTENDED_HTML',	2);

//! Remove all tags except extended HTML tags, frame, style and form (input) tags
define ('FORMDATA_FULL_HTML',		3);

//! Provide custom arrays for formatting, \see cleanString()
define ('FORMDATA_CUSTOM',			4);

//! Return all data, formatted as HTML code
define ('FORMDATA_HTML_CODE',		5);

//! Return the data unformatted
define ('FORMDATA_RAW',				6);
//! @}

/**
 * \ingroup OWL_SO_LAYER
 * Handler for all incoming formdata.
 * \brief Formhandler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 28, 2008 -- O van Eijk -- initial version
 */
class FormHandler extends _OWL
{
	/**
	 * Create an array which will hold all (formatted) formvalues
	 */
	private $owl_formvalues;

	/**
	 * integer - self reference
	 */
	private static $instance;

	/**
	 * Class constructor; Parse the incoming formdata.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function __construct ()
	{
		_OWL::init(__FILE__, __LINE__);
		$this->owl_formvalues = array();
		$this->setStatus (__FILE__, __LINE__, FORM_PARSE);
		$this->parseFormdata ($_GET);
		$this->parseFormdata ($_POST);
		$this->setStatus (__FILE__, __LINE__, OWL_STATUS_OK);
	}

	/**
	 * Return a reference to my implementation. If necessary, create that implementation first.
	 * \return Severity level
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function getInstance()
	{
		if (!FormHandler::$instance instanceof self) {
			FormHandler::$instance = new self();
		}
		return FormHandler::$instance;
	}

	/**
	 * Parse a given form and store all data in the parent class, except values that
	 * come from a multiple select; they will be stored locally.
	 * \param[in] $data The formdata array.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function parseFormdata ($data = null)
	{
		if ($data === null || empty($data)) {
			return;
		}
		foreach ($data as $_k => $_v) {

			$this->set($_k, $_v);
		}

	}

	/**
	 * Reimplement the get method; the parent's get will only be called
	 * if the requested variable name is not in the 'local' array where multi-values
	 * are stored.
	 * \param[in] $variable The variable name who's value should be set
	 * \param[in] $value Value for the variable
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function set ($variable, $value)
	{
		if (array_key_exists ($variable, $this->owl_formvalues)) {
			// This field already exists (multi select); make sure it's
			// not overwritten, but written in an array
			if (!is_array($this->owl_formvalues[$variable])) {
				// This is the first one, copy the previously stored value to the
				// multivalue array and add the new value.
				$_val = $this->owl_formvalues[$variable];
				$this->owl_formvalues[$variable] = array ($_val, $value);
			} else {
				$this->owl_formvalues[$variable][] = $value;
			}
		} else {
			$this->owl_formvalues[$variable] = $value;
		}

		if (ConfigHandler::get ('general', 'debug') > 0) {
			$this->setStatus (__FILE__, __LINE__, FORM_STORVALUE,
				array ($variable
						, (
							is_array($this->owl_formvalues[$variable])
								? ('array('.implodeMDArray (',', $this->owl_formvalues[$variable]) . ')')
								: $this->owl_formvalues[$variable]
						)
				)
			);
		}
	}

	/**
	 * Get the value of a given formfield, formatted as specified
	 * \param[in] $variable The variable name who's value should be returned
	 * \param[in] $format Specify how the field should be formatted
	 * \param[in] $allows Array with allowd tags, used for the FORMDATA_CUSTOM format.
	 * \param[in] $content Array with tags to allow completely, used for the FORMDATA_CUSTOM format.
	 * \return Value as taken from the form; single value or array, or null when the field doesn't exist
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function get ($variable, $format = FORMDATA_STRICT, $allows = null, $content = array('script'))
	{
		switch ($format) {
			case FORMDATA_BASIC_HTML:
				$_allow = array('b', 'i', 'strong', 'em', 'u', 'pre', 'tt', 'p', '(o|u)l', 'li', 'h[1-6]', '(b|h)r', 'center', 'small', 'big');
				$_content = array('noframes', 'option', 'select', 'script', 'style');
				break;
			case FORMDATA_EXTENDED_HTML:
				$_allow = array('b', 'i', 'strong', 'em', 'u', 'pre', 'tt', 'p', '(o|u)l', 'li', 'h[1-6]', '(b|h)r', 'center', 'small', 'big'
					, 'font', 'table', 't(h|r|d)', 'a', 'img', 'div', 'span');
				$_content = array('noframes', 'option', 'select', 'script', 'style');
				break;
			case FORMDATA_FULL_HTML:
				$_allow = array('b', 'i', 'strong', 'em', 'u', 'pre', 'tt', 'p', '(o|u)l', 'li', 'h[1-6]', '(b|h)r', 'center', 'small', 'big'
					, 'font', 'table', 't(h|r|d)', 'a', 'img'
					, 'fieldset', 'form', 'frame(set)?', 'input', 'label', 'legend', 'noframes', 'optgroup', 'option', 'select', 'style', 'textarea');
				$_content = array('script');
				break;
			case FORMDATA_CUSTOM:
				$_allow = $allows;
				$_content = $content;
				break;
			case FORMDATA_HTML_CODE:
				break; // Will be handled later
			default:
			case FORMDATA_STRICT:
				$_allow = array();
				$_content = array('noframes', 'option', 'select', 'script', 'style');
				break;
		}

		if (array_key_exists ($variable, $this->owl_formvalues)) {
			$_val = $this->owl_formvalues[$variable];
			if ($format !== FORMDATA_RAW) {
				if (is_array($_val)) {
//					$_cnt = count($_val);
					foreach ($_val as $_HTMLfld => $_HTMLval) {
						if ($format === FORMDATA_HTML_CODE) {
							$_val[$_HTMLfld] = htmlentities($_HTMLval, ENT_COMPAT, ConfigHandler::get('general', 'charset', 'ISO-8859-1'));
						} else {
							$_val[$_HTMLfld] = $this->cleanString($_HTMLval, $_allow, $_content);
						}
					}
/*
					for ($_i = 0; $_i < $_cnt; $_i++) {
						if ($format === FORMDATA_HTML_CODE) {
							$_val[$_i] = htmlentities($_val[$_i], ENT_COMPAT, ConfigHandler::get('general', 'charset', 'ISO-8859-1'));
						} else {
							$_val[$_i] = $this->cleanString($_val[$_i], $_allow, $_content);
						}
					}
 */
				} else {
					if ($format === FORMDATA_HTML_CODE) {
						$_val = htmlentities($_val, ENT_COMPAT, ConfigHandler::get('general', 'charset', 'ISO-8859-1'));
					} else {
						$_val = $this->cleanString($_val, $_allow, $_content);
					}
				}
			}
		} else {
			$_val = null;
			if ($variable != OWL_DISPATCHER_NAME) { // 'Home'
				$this->setStatus (__FILE__, __LINE__, FORM_NOVALUE, $variable);
			}
		}

		if (ConfigHandler::get ('general', 'debug') > 0) {
			$this->setStatus (__FILE__, __LINE__, FORM_RETVALUE, array ($variable, (is_array($_val)?print_r($_val, 1):$_val)));
		}
		return ($_val);
	}

	/**
	 * Remove all tags from an input string
	 * \param[in] $input Input string to clean
	 * \param[in] $allow array with tags that should not be removed. These can be in a regular expression
	 * format, e.g. array('b', 'h[1-6]') to allow the &lt;b&gt; tag and &lt;h1&gt; to &lt;h6&gt;.
	 * \param[in] $content array with tags that should completely be removed, so including all contents
	 * between the opening and closing tag, e.g. '&lt;script&gt;some code&lt;/script&gt;'. Default is array('script','style')
	 * \param[in] $remove_comment When true (default), content blocks will completely be removed. Set to false
	 * to allow the content of a comment block. The tags themselves ('&lt;!--' and '--&gt;') will always be removed.
	 * \return The string with all tags removed
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	function cleanString ($input , $allow = null , $content = null,  $remove_comment = true)
	{
		if (!is_string($input)) {
			return $input;
		}

		if ($allow == null) {
			$allow = array();
		}
		if ($content == null) {
			$content = array('script','style');
		}

		if (count ($allow) > 0) {
			foreach ($allow as $_tag) {
				$_regexp = array(
					 "/<($_tag)/i"
					,"/<\/($_tag)/i"
				);
				$_replace = array(
					 "#_#_#$1"
					,"#_#_#/$1"
				);
				$input = preg_replace($_regexp, $_replace, $input);
			}
		}

		if (!$remove_comment) {
			$input = preg_replace(array('/<!--/','/-->/'), array('',''), $input);
		}

		if (count($content) > 0) {
			foreach ($content as $_tag) {
				$input = preg_replace("/<\s*$_tag.*?\/??>?.*?<??\/??\s*?($_tag)??.*?>/i", '', $input);
			}
		}

		$input = preg_replace("/<.*?>/", '', $input);
		$input = preg_replace("/#_#_#/", '<', $input);
		$input = preg_replace("/\s+/", ' ', $input);
		return $input;
	}

	/**
	 * Get the complete formdata for logging purposes
	 * \return Array with the parsed formdata
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getFormData()
	{
		return ($this->owl_formvalues);
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('FormHandler');

Register::setSeverity (OWL_DEBUG);
Register::registerCode ('FORM_STORVALUE');
Register::registerCode ('FORM_PARSE');

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
Register::setSeverity (OWL_SUCCESS);
Register::registerCode ('FORM_RETVALUE');

Register::setSeverity (OWL_WARNING);
Register::registerCode ('FORM_NOVALUE');

//Register::setSeverity (OWL_BUG);

//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
