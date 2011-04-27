<?php
/**
 * \file
 * This file defines a fileinput formfield element
 * \version $Id: class.formfield.file.php,v 1.2 2011-04-27 11:50:08 oscar Exp $
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
