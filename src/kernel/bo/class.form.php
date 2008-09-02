<?php
/**
 * \file
 * This file defines the HTML Form class
 * \version $Id: class.form.php,v 1.1 2008-09-02 05:16:54 oscar Exp $
 */

/**
 * \ingroup OWL_BO_LAYER
 * Define an HTML Form. This class extends the DOMElement base class and implements
 * thr FormElement class
 * \brief Form Element class 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 29, 2008 -- O van Eijk -- initial version
 */
class Form extends DOMElement
{

	/**
	 * Class constructor; 
	 * \public
	 */
	public function __construct ()
	{
		parent::__construct();
	}


}

/*
 * Register this class and all status codes
 */
Register::register_class ('Form');

//Register::set_severity (OWL_DEBUG);

//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);
Register::set_severity (OWL_SUCCESS);
//Register::register_code ('FORM_RETVALUE');

//Register::set_severity (OWL_WARNING);

//Register::set_severity (OWL_BUG);

//Register::set_severity (OWL_ERROR);
//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
