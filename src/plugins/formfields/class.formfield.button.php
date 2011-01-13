<?php
/**
 * \file
 * This file defines a button formfield element
 * \version $Id: class.formfield.button.php,v 1.1 2011-01-13 11:05:34 oscar Exp $
 */

/**
 * \ingroup OWL_PLUGINS
 * Formfield button elements
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldButtonPlugin extends FormFieldPlugin
{
	/**
	 * Alternate text (image-type only)
	 * \public
	 */
	public $alt;

	/**
	 * Image src (image-type only)
	 * \public
	 */
	public $src;

	/**
	 * Class constructor; 
	 * \param[in] $type Element type: button (default), image, submit or reset
	 * \public
	 */
	public function __construct ($type = 'button')
	{
		parent::__construct();
		$this->type = strtolower($type);
	}

	/**
	 * Set the alt text for the button
	 * \param[in] $_value text
	 */
	public function setText($_value)
	{
		$this->alt = $_value;
	}

	/**
	 * Set the image source for the button
	 * \param[in] $_value Image source
	 */
	public function setSource($_value)
	{
		$this->src = $_value;
	}

	/**
	 * Return the HTML code to display the form element
	 * \public
	 * \return Textstring with the complete code for the form element
	 */
	public function showElement ()
	{
		$_htmlCode = "<input type='$this->type'" . $this->getGenericFieldAttributes();
		if ($this->type == 'image') {
			if (!empty($this->alt)) {
				$_htmlCode .= " alt='$this->alt'";
			}
			if (!empty($this->src)) {
				$_htmlCode .= " src='$this->src'";
			}
		}
		$_htmlCode .= '/>';
		return $_htmlCode;
	}
}


//Register::set_severity (OWL_DEBUG);

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
//Register::set_severity (OWL_SUCCESS);
//Register::register_code ('FORM_RETVALUE');

//Register::set_severity (OWL_WARNING);

//Register::set_severity (OWL_BUG);

//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
