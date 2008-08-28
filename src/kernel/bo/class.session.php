<?php
/**
 * \file
 * This file defines the Session class
 * \version $Id: class.session.php,v 1.2 2008-08-28 18:12:52 oscar Exp $
 */

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the OWL session 
 * \brief the OWL-PHP session object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 13, 2008 -- O van Eijk -- initial version
 */
class Session extends SessionHandler
{
	/**
	 * When a new run is initialised, restore an older session or create a new one
	 * \public 
	 */
	public function __construct ()
	{
		$this->dataset =& new DataHandler (&$GLOBALS['db']);
		parent::__construct ();
		if (session_id() == '') {
			session_start ();
			header ('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); //Fix for IE6
		}
	}
	
	/**
	 * When a run ends, write the sessiondata
	 * \public
	 */
	public function __destruct ()
	{
		session_write_close ();
		if (is_object ($this->dataset)){
			$this->dataset->__destruct();
			unset ($this->dataset);
		}
		parent::__destruct();
	}
}