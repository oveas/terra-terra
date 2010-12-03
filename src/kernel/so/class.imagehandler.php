<?php
/**
 * \file
 * This file defines the FileHandler class
 * \version $Id: class.imagehandler.php,v 1.1 2010-12-03 12:07:42 oscar Exp $
 */

/**
 * \name Dataline trim flags
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
	 * \private
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
	 * \private
	 */	
	private $fpointer;

	/**
	 * Boolean that's true when the file is opened
	 * \private
	 */	
	private $opened;

//	var $localfile;		// Boolean that indicates a local file when TRUE
//	var $myfile;		// Boolean that indicates a file owned by me when TRUE


	/**
	 * Class constructor; setup the image characteristics
	 * \public
	 * \param[in] $name Filename
	 * \param[in] $req True is the file must exist at object create time
	 */
	public function __construct ($name, $req = false)
	{
		_OWL::init();

		$this->name = realpath($name);
		$this->opened = false;

		if (!file_exists($this->name)) {
			if ($req) {
				$this->set_status (FILE_NEWFILE, array (
					$this->name
				));
			} else {
				$this->set_status (FILE_NOSUCHFILE, array (
					$this->name
				));
			}
			return;
		}
		$this->size = filesize($this->name);
		$this->localfile = !eregi("^([a-z]+)://", $this->name);
		$this->myfile = (fileowner($this->name) == getmyuid());

		$this->set_status (OWL_STATUS_OK);
	}
}



/*
 * Register this class and all status codes
 */
Register::register_class('ImageHandler');

//Register::set_severity (OWL_DEBUG);
Register::set_severity (OWL_INFO);
Register::register_code ('FILE_NEWFILE');

//Register::set_severity (OWL_OK);

Register::set_severity (OWL_SUCCESS);
Register::register_code ('FILE_CREATED');
Register::register_code ('FILE_OPENED');
Register::register_code ('FILE_CLOSED');

Register::set_severity (OWL_WARNING);
Register::register_code ('FILE_NOSUCHFILE');
Register::register_code ('FILE_ENDOFFILE');

//Register::set_severity (OWL_BUG);

Register::set_severity (OWL_ERROR);
Register::register_code ('FILE_OPENERR');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
