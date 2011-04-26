<?php
/**
 * \file
 * This file defines the Rights class
 * \version $Id: class.rights.php,v 1.3 2011-04-26 11:45:45 oscar Exp $
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
	 * \param[in] $app code for which the bitmap array must be setup
	 * \param[in] $owl By default, the owl bitmap will be setup as well. Set this to false to suppress this
	 */
	public function __construct ($app, $owl = true)
	{
		parent::__construct($app, $owl);
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
