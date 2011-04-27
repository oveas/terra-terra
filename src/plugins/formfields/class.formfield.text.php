<?php
/**
 * \file
 * This file defines a text-, password or hidden formfield element
 * \version $Id: class.formfield.text.php,v 1.3 2011-04-27 11:50:07 oscar Exp $
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
			$this->setStatus(FORMFIELD_IVVAL, array($size, 'size'));
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
			$this->setStatus(FORMFIELD_IVVAL, array($maxsize, 'maxsize'));
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
			$_htmlCode .= " maxlength='$this->maxsize'";
		}
		$_htmlCode .= $this->getGenericFieldAttributes() . '/>';
		return $_htmlCode;
	}
}


//Register::setSeverity (OWL_DEBUG);

//Register::setSeverity (OWL_INFO);
//Register::setSeverity (OWL_OK);
//Register::setSeverity (OWL_SUCCESS);
//Register::registerCode ('FORM_RETVALUE');

//Register::setSeverity (OWL_WARNING);

//Register::setSeverity (OWL_BUG);

//Register::setSeverity (OWL_ERROR);
//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
