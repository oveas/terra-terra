<?php
/**
 * \file
 * This file defines the Rights class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of Terra-Terra.
 *
 * Terra-Terra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Terra-Terra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Terra-Terra. If not, see http://www.gnu.org/licenses/.
 */

/**
 * \ingroup TT_BO_LAYER
 * This class handles the Rights bitmaps
 * \brief the Terra-Terra rights object
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
	 * \param[in] $tt By default, the tt bitmap will be setup as well. Set this to false to suppress this
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($app, $tt = true)
	{
		parent::__construct($app, $tt);
		if (($this->rightslist = TTCache::get('rights', 'list')) === null) {
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
		if (($this->rightslist = TTCache::get('rights', 'list')) === null || $this->rightslist === array()) {
			$this->registerRights();
		}
	}

	/**
	 * Get the bitvalue for a given name
	 * \param[in] $name Name of the rights bit
	 * \param[in] $aid Application ID to which the bit belongs
	 * \return Integer value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function bitValue($name, $aid)
	{
		if (!array_key_exists('a' . $aid, $this->rightslist) || !array_key_exists($name, $this->rightslist['a' . $aid])) {
			return 0;
		}
		return ($this->rightslist['a' . $aid][$name]);
	}

	/**
	 * If the rightlist is not yet filled, do so now and store the list in cache
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo Make sure only the rightbits for the loaded apps are registered (otoh... what if a new app gets loaded on the next click?
	 * That would make a force reload required: a destroy of the cached list...)
	 * 
	 */
	private function registerRights ()
	{
		$this->rightslist = array();
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}
		$dataset->setTablename('rights');
		$dataset->set('aid', null, null, array('name' => array('aid')), array('match' => array(DBMATCH_NONE)));
		$dataset->set('rid', null, null, array('name' => array('rid')), array('match' => array(DBMATCH_NONE)));
		$dataset->set('name', null, null, array('name' => array('name')), array('match' => array(DBMATCH_NONE)));
//		$dataset->set('aid', TTloader::getLoadedApps());//array(TT_ID, TTloader::getCurrentAppID()));
//		$dataset->setKey('aid');
		$dataset->prepare();
		$dataset->db($data, __FILE__, __CLASS__);
		foreach ($data as $_r) {
			if (!array_key_exists('a' . $_r['aid'], $this->rightslist)) {
				$this->rightslist['a' . $_r['aid']] = array();
			}
			$this->rightslist['a' . $_r['aid']][$_r['name']] = pow(2, $_r['rid']-1);
		}
		TTCache::set('rights', 'list', $this->rightslist);
	}
}
Register::registerClass('Rights', TT_APPNAME);

Register::setSeverity (TT_WARNING);
//Register::registerCode ('USER_DUPLUSERNAME');
