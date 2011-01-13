<?php
/**
 * \file
 * This file defines a text-, password or hidden formfield element
 * \version $Id: class.formfield.text.php,v 1.1 2011-01-13 11:05:34 oscar Exp $
 */

/**
 * \ingroup OWL_PLUGINS
 * Formfield text elements
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldTextPlugin extends FormFieldPlugin
{
	/**
	 * Field size
	 * \private
	 */
	private $size;

	/**
	 * Maximum field size
	 * \private
	 */
	private $maxsize;

	/**
	 * Class constructor; 
	 * \param[in] $type Element type: text (default), password or hidden
	 * \public
	 */
	public function __construct ($type = 'text')
	{
		parent::__construct();
		$this->type = strtolower($type);
	}

	/**
	 * Set the Size attribute
	 * \param[in] $size integer
	 */
	public function setSize ($size)
	{
		if (is_int($size)) {
			$this->size = $size;
		} else {
			$this->set_status(FORMFIELD_IVVAL, array($size, 'size'));
		}
	}

	/**
	 * Set the Maxsize attribute
	 * \param[in] $maxsize integer
	 */
	public function setMaxsize ($maxsize)
	{
		if (is_int($maxsize)) {
			$this->maxsize = $maxsize;
		} else {
			$this->set_status(FORMFIELD_IVVAL, array($maxsize, 'maxsize'));
		}
	}

	/**
	 * Return the HTML code to display the form element
	 * \public
	 * \return Textstring with the complete code for the form element
	 */
	public function showElement ()
	{
		$_htmlCode = "<input type='$this->type'";
		if (!empty($this->size)) {
			$_htmlCode .= " size='$this->size'";
		}
		if (!empty($this->maxsize) && ($this->type != 'hidden')) {
			$_htmlCode .= " maxsize='$this->maxsize'";
		}
		$_htmlCode .= $this->getGenericFieldAttributes() . '/>';
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
