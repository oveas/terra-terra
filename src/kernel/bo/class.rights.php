<?php
/**
 * \file
 * This file defines the Rights class
 * \version $Id: class.rights.php,v 1.1 2011-04-14 14:31:35 oscar Exp $
 */

/**
 * \name Bit control actions
 * These constants define the possible actions that can be performed on a bitmap
 * @{
 */
//! Check if a bit is high in the bitmap
define ('BIT_CHECK',	1);

//! Set a bit high in the bitmap
define ('BIT_SET',		2);

//! Set a bit low in the bitmap
define ('BIT_UNSET',	3);

//! If a bit is high in the bitmap, set it low and vise versa
define ('BIT_TOGGLE',	4);

//! @}

/**
 * \ingroup OWL_BO_LAYER
 * This class handles the Rights bitmaps 
 * \brief the OWL-PHP rights object 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 14, 2011 -- O van Eijk -- initial version
 */
class Rights extends _OWL
{
	/**
	 * Array wit all known right identifiers
	 */
	private $rightslist;

	/**
	 * Rightslist bitmap for tyhe current user
	 */
	private $bitmap;

	/**
	 * Class constructor
	 */
	public function __construct ()
	{
		_OWL::init();
		$this->bitmap = 0; // No bits set by default
		if (($this->rightslist = OWLCache::get('rights', 'list')) === null) {
			$this->registerRights();
		}
	}

	/**
	 * If the rightlist is not yet filled, to so now and store the list in cache
	 */
	private function registerRights ()
	{
		$dataset = new DataHandler ();
		if (ConfigHandler::get ('owltables', true)) {
			$dataset->set_prefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->set_tablename('rights');
		$dataset->set('rid', null);
		$dataset->set('name', null);
		$dataset->set('applic', array('owl', strtolower(APPL_CODE)));
		$dataset->set_key('appl');
		$dataset->prepare();
		$dataset->db($data, __FILE__, __CLASS__);
		foreach ($data as $_r) {
			$this->rightslist[$_r['name']] = pow(2, $_r['rid']-1);
		}
		OWLCache::set('rights', 'list', $this->rightslist);
	}

	/**
	 * Merge a given gitmap with the current users bitmap
	 * \param[in] $bitmap Rightlist bitmap
	 */
	public function mergeBitmaps($bitmap)
	{
		$this->bitmap = ($this->bitmap | $bitmap);
	}

	/**
	 * Check, set or unset a bit in the current users bitmap.
	 * Enter description here ...
	 * \param[in] $bit Bit that should be checked or (un)set
	 * \param[in] $controller Controller defining the action, defaults to check
	 * \return True if the bit was set (*before* a set or unset action!)
	 */
	public function controlBitmap ($bit, $controller = BIT_CHECK)
	{
		$_curr = ($this->bitmap & $bit);
		if ($controller == BIT_SET) {
			if (!$_curr) {
				$this->bitmap = ($this->bitmap | $_bit);
			}
		} elseif ($_act == BIT_UNSET) {
			if ($_curr) {
				$this->bitmap = ($this->bitmap ^ $_bit);
			}
		} elseif ($_act == BIT_TOGGLE) {
			$this->bitmap = ($this->bitmap ^ $_bit);
		}
		return ($_curr);
	}
}
Register::register_class('Rights');

Register::set_severity (OWL_WARNING);
//Register::register_code ('USER_DUPLUSERNAME');
