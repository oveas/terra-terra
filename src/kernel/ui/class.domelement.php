<?php
/**
 * \file
 * This file defines the top-level DOMelement class
 * \version $Id: class.domelement.php,v 1.1 2008-09-02 05:16:53 oscar Exp $
 */

/**
 * \ingroup OWL_UI_LAYER
 * Base class for all DOM elements
 * \brief DOM Element base class 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 29, 2008 -- O van Eijk -- initial version
 */
class DOMElement extends _OWL
{

	/**
	 * Class constructor; 
	 * \public
	 */
	public function __construct ()
	{
		parent::init();
	}


}

/*
 * Register this class and all status codes
 */
Register::register_class ('DOMElement');

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
