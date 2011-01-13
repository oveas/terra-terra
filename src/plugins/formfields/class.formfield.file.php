<?php
/**
 * \file
 * This file defines a fileinput formfield element
 * \version $Id: class.formfield.file.php,v 1.1 2011-01-13 11:05:34 oscar Exp $
 */

/**
 * \ingroup OWL_PLUGINS
 * Formfield File input elements
 * \brief Formfield 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Oct 19, 2010 -- O van Eijk -- initial version
 */

class FormFieldFilePlugin extends FormFieldPlugin
{
	/**
	 * Class constructor; 
	 * \public
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->type = 'file';
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
