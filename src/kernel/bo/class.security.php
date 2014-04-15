<?php
/**
 * \file
 * This file defines the Security base class
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
 * \name Security bit control actions
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
 * \ingroup TT_BO_LAYER
 * This class handles TT security
 * \brief the Terra-Terra security objects
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 16, 2011 -- O van Eijk -- initial version
 */
abstract class Security
{

	/**
	 * Array with security bitmaps bitmaps for the current user
	 */
	protected $bitmap;

	/**
	 * Class constructor
	 * \param[in] $app code for which the bitmap array must be setup
	 * \param[in] $tt By default, the tt bitmap will be setup as well. Set this to false to suppress this
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($app, $tt = true)
	{
		$this->bitmap = array('a'.$app => 0);
		if ($tt === true) {
			$this->bitmap['a'.TT_ID] = 0;
		}
	}

	/**
	 * Magic function for serialize; make sure only the required data is serialized
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __sleep()
	{
		return array('bitmap');
	}

	/**
	 * Get the bitvalue for a given name. This method must be reimplemented
	 * \param[in] $name Name of the bit
	 * \param[in] $aid Application ID to which the bit belongs
	 * \return Integer value
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	abstract public function bitValue($name, $aid);

	/**
	 * (Re)initialise the bitmap for the given application
	 * \param[in] $value Bitmap value
	 * \param[in] $app Application ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function initBitmap($value, $app)
	{
		$this->bitmap['a'.$app] = $value;
	}

	/**
	 * Get the bitmap value for the given application
	 * \param[in] $app Application ID
	 * \return Bitmap value as an integer
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getBitmap($app)
	{
		return $this->bitmap['a'.$app];
	}

	/**
	 * Merge a given gitmap with the current users bitmap
	 * \param[in] $bitmap Rightlist bitmap
	 * \param[in] $app Application the bitmap belongs to
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function mergeBitmaps($bitmap, $app)
	{
		if (array_key_exists('a'.$app, $this->bitmap)) {
			$this->bitmap['a'.$app] = ($this->bitmap['a'.$app] | $bitmap);
		} else {
			$this->bitmap['a'.$app] = $bitmap;
		}
	}

	/**
	 * Check, set or unset a bit in the current users bitmap.
	 * \param[in] $bit Bit that should be checked or (un)set
	 * \param[in] $app Application ID the bit belongs to
	 * \param[in] $controller Controller defining the action, defaults to check
	 * \return True if the bit was set (*before* a set or unset action!)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function controlBitmap ($bit, $app, $controller = BIT_CHECK)
	{
		if (!array_key_exists('a'.$app, $this->bitmap)) {
			$this->bitmap['a'.$app] = 0;
			$_curr = 0;
		} else {
			$_curr = (($this->bitmap['a'.$app] & $bit) == $bit);
		}
		if ($controller == BIT_SET) {
			if (!$_curr) {
				$this->bitmap['a'.$app] = ($this->bitmap['a'.$app] | $_bit);
			}
		} elseif ($controller == BIT_UNSET) {
			if ($_curr) {
				$this->bitmap['a'.$app] = ($this->bitmap['a'.$app] ^ $_bit);
			}
		} elseif ($controller == BIT_TOGGLE) {
			$this->bitmap['a'.$app] = ($this->bitmap['a'.$app] ^ $_bit);
		}
		return (toBool($_curr));
	}
}
Register::registerClass('Security');

Register::setSeverity (TT_WARNING);
//Register::registerCode ('USER_DUPLUSERNAME');
