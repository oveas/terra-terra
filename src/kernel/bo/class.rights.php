<?php
/**
 * \file
 * This file defines the Rights class
 * \version $Id: class.rights.php,v 1.5 2011-04-27 11:50:08 oscar Exp $
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
	 * The rightslist is stored in cache, not in the serialized object, so we must retrieve it
	 * again on unserialize()
	 */
	public function __wakeup()
	{
		if (($this->rightslist = OWLCache::get('rights', 'list')) === null) {
			$this->registerRights();
		}
	}

	/**
	 * Get the bitvalue for a given name
	 * \param[in] $name Name of the rights bit
	 * \return Integer value
	 */
	public function bitValue($name)
	{
		return ($this->rightslist[$name]);
	}

	/**
	 * If the rightlist is not yet filled, do so now and store the list in cache
	 */
	private function registerRights ()
	{
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('owltables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->setTablename('rights');
		$dataset->set('aid', array(OWL_ID, APPL_ID));
		$dataset->setKey('appl');
		$dataset->prepare();
		$dataset->db($data, __FILE__, __CLASS__);
		foreach ($data as $_r) {
			$this->rightslist[$_r['name']] = pow(2, $_r['rid']-1);
		}
		OWLCache::set('rights', 'list', $this->rightslist);
	}
}
Register::registerClass('Rights');

Register::setSeverity (OWL_WARNING);
//Register::registerCode ('USER_DUPLUSERNAME');
