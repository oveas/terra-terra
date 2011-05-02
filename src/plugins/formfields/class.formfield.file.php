<?php
/**
 * \file
 * This file defines a fileinput formfield element
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.formfield.file.php,v 1.3 2011-05-02 12:56:13 oscar Exp $
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
