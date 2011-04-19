<?php
/**
 * \file
 * This file defines the Security base class
 * \version $Id: class.security.php,v 1.1 2011-04-19 13:00:03 oscar Exp $
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
 * This class handles OWL security
 * \brief the OWL-PHP security objects 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 16, 2011 -- O van Eijk -- initial version
 */
class Security
{

	/**
	 * Array with security bitmaps bitmaps for the current user
	 */
	protected $bitmap;

	/**
	 * Merge a given gitmap with the current users bitmap
	 * \param[in] $bitmap Rightlist bitmap
	 * \param[in] $app Application the bitmap belongs to
	 */
	public function mergeBitmaps($bitmap, $app = 'owl')
	{
		if (array_key_exists($app, $this->bitmap)) {
			$this->bitmap[$app] = ($this->bitmap[$app] | $bitmap);
		} else {
			$this->bitmap[$app] = $bitmap;
		}
	}

	/**
	 * Check, set or unset a bit in the current users bitmap.
	 * Enter description here ...
	 * \param[in] $bit Bit that should be checked or (un)set
	 * \param[in] $app Application the bit belongs to
	 * \param[in] $controller Controller defining the action, defaults to check
	 * \return True if the bit was set (*before* a set or unset action!)
	 */
	public function controlBitmap ($bit, $app = 'owl', $controller = BIT_CHECK)
	{
		if (!array_key_exists($app, $this->bitmap)) {
			$this->bitmap[$app] = 0;
			$_curr = 0;
		} else {
			$_curr = ($this->bitmap[$app] & $bit);
		}
		if ($controller == BIT_SET) {
			if (!$_curr) {
				$this->bitmap = ($this->bitmap | $_bit);
			}
		} elseif ($_act == BIT_UNSET) {
			if ($_curr) {
				$this->bitmap[$app] = ($this->bitmap[$app] ^ $_bit);
			}
		} elseif ($_act == BIT_TOGGLE) {
			$this->bitmap[$app] = ($this->bitmap[$app] ^ $_bit);
		}
		return ($_curr);
	}
}
Register::register_class('Rights');

Register::set_severity (OWL_WARNING);
//Register::register_code ('USER_DUPLUSERNAME');
