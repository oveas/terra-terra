<?php
/**
 * \file
 * This file defines the Session class
 * \version $Id: class.session.php,v 1.1 2008-08-25 05:30:44 oscar Exp $
 */

require_once (OWL_INCLUDE . '/class._OWL.php');

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the OWL session 
 * \brief the OWL-PHP session object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 13, 2008 -- O van Eijk -- initial version
 */
class Session extends _OWL
{

	/**
	 * Reference to the datahandler object used for this session
	 * \private
	 */
	private $sessiondata;

	/**
	 * Reference to the PHP session handler
	 * \private
	 */
	private $sessionhandler;

	/**
	 * When a new run is initialised, restore an older session or create a new one
	 * \public 
	 */
	public function __construct ()
	{
		$this->sessiondata =& new DataHandler (&$GLOBALS['db']);
		$this->sessionhandler =& new SessionHandler(&$this->sessiondata);
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
	}
}