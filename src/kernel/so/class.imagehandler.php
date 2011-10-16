<?php
/**
 * \file
 * This file defines the FileHandler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.imagehandler.php,v 1.5 2011-10-16 11:11:44 oscar Exp $
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
 * \defgroup FILR_TrimFlags Dataline trim flags
 * These flags define how datalines should be trimmed when read from a file
 * @{
 */
//! Don't trim
define ('FILE_NOTRIM',	0);

//! Trim left part of a line read
define ('FILE_TRIM_L',	1);

//! Trim right part of a line read
define ('FILE_TRIM_R',	2);

//! Replace all multiple spaces in a line read with a single space
define ('FILE_TRIM_C',	3);

//! @}

/**
 * \ingroup OWL_SO_LAYER
 * Handle all images
 * \brief Image handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 15, 2007 -- O van Eijk -- initial version for Terra-Terra (based on an old OFM module)
 * \version Jul 30, 2008 -- O van Eijk -- Modified version for OWL-PHP
 */
class ImageHandler extends FileHandler
{

	/**
	 * Full filename as stored on the file system
	 */
	private $name;
	var $original_name;	// Original filename
//	var $location;		// the file's location on the file system
//	var $file_ext;		// Original file type
//	var $file_type;		// File's MIME type
//	var $file_subtype;	// File's MIME subtype
//	var $ascii;			// True if this is an ascii file
//	var $ascii_type;	// Identifies the OS- type (Un*x/DOS/MAC)
//	var $size;
//	var $px_width;		// Width in pixels (for images)
//	var $px_heigth;		// Heigth in pixels (for images)

	/**
	 * Pointer to te file when opened
	 */
	private $fpointer;

	/**
	 * Boolean that's true when the file is opened
	 */
	private $opened;

//	var $localfile;		// Boolean that indicates a local file when TRUE
//	var $myfile;		// Boolean that indicates a file owned by me when TRUE


	/**
	 * Class constructor; setup the image characteristics
	 * \param[in] $name Filename
	 * \param[in] $req True is the file must exist at object create time
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($name, $req = false)
	{
		_OWL::init();

		$this->name = realpath($name);
		$this->opened = false;

		if (!file_exists($this->name)) {
			if ($req) {
				$this->setStatus (FILE_NEWFILE, array (
					$this->name
				));
			} else {
				$this->setStatus (FILE_NOSUCHFILE, array (
					$this->name
				));
			}
			return;
		}
		$this->size = filesize($this->name);
		$this->localfile = !eregi("^([a-z]+)://", $this->name);
		$this->myfile = (fileowner($this->name) == getmyuid());

		$this->setStatus (OWL_STATUS_OK);
	}
}



/*
 * Register this class and all status codes
 */
Register::registerClass('ImageHandler');

//Register::setSeverity (OWL_DEBUG);
Register::setSeverity (OWL_INFO);
Register::registerCode ('FILE_NEWFILE');

//Register::setSeverity (OWL_OK);

Register::setSeverity (OWL_SUCCESS);
Register::registerCode ('FILE_CREATED');
Register::registerCode ('FILE_OPENED');
Register::registerCode ('FILE_CLOSED');

Register::setSeverity (OWL_WARNING);
Register::registerCode ('FILE_NOSUCHFILE');
Register::registerCode ('FILE_ENDOFFILE');

//Register::setSeverity (OWL_BUG);

Register::setSeverity (OWL_ERROR);
Register::registerCode ('FILE_OPENERR');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
