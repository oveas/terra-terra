<?php
/**
 * \file
 * This file defines the Rights class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OWL-PHP.
 *
 * OWL-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OWL-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OWL-PHP. If not, see http://www.gnu.org/licenses/.
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function bitValue($name)
	{
		return ($this->rightslist[$name]);
	}

	/**
	 * If the rightlist is not yet filled, do so now and store the list in cache
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function registerRights ()
	{
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'owltables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'owlprefix'));
		}
		$dataset->setTablename('rights');
		$dataset->set('aid', array(OWL_ID, OWLloader::getCurrentAppID()));
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
