<?php
/**
 * \file
 * This file defines the Rights class
 * \version $Id: class.rights.php,v 1.2 2011-04-19 13:00:03 oscar Exp $
 */

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the Rights bitmaps 
 * \brief the OWL-PHP rights object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 14, 2011 -- O van Eijk -- initial version
 */
class Rights extends Security
{
	/**
	 * Array with all known right identifiers
	 */
	private $rightslist;

	/**
	 * Class constructor
	 */
	public function __construct ()
	{
		$this->bitmap = array();
		if (($this->rightslist = OWLCache::get('rights', 'list')) === null) {
			$this->registerRights();
		}
	}

	/**
	 * If the rightlist is not yet filled, do so now and store the list in cache
	 */
	private function registerRights ()
	{
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('owltables', true)) {
			$dataset->set_prefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->set_tablename('rights');
		$dataset->set('aid', array(OWL_APPL_ID, APPL_ID));
		$dataset->set_key('appl');
		$dataset->prepare();
		$dataset->db($data, __FILE__, __CLASS__);
		foreach ($data as $_r) {
			$this->rightslist[$_r['name']] = pow(2, $_r['rid']-1);
		}
		OWLCache::set('rights', 'list', $this->rightslist);
	}

}
Register::register_class('Rights');

Register::set_severity (OWL_WARNING);
//Register::register_code ('USER_DUPLUSERNAME');
