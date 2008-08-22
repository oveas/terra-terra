<?php
/**
 * \file
 * This file defines the FileHandler class
 * \version $Id: class.filehandler.php,v 1.2 2008-08-22 12:02:10 oscar Exp $
 */

define ('FILE_NOTRIM',	0); // Don't trim
define ('FILE_TRIM_L',	1); // Trim left part of a line read
define ('FILE_TRIM_R',	2); // Trim right part of a line read
define ('FILE_TRIM_C',	3); // Replace all multiple spaces in a line read with a single space

require_once ('class._OWL.php');

/**
 * \ingroup OWL_BO_LAYER
 * Handle all files
 * \brief File handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 15, 2007 -- O van Eijk -- initial version for Terra-Terra (based on an old OFM module)
 * \version Jul 30, 2008 -- O van Eijk -- Modified version for OWL-PHP
 */
class FileHandler extends _CargoByte 
{

//	var $name;			// Full filename as stored on the file system
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

//	var $fpointer;
//	var $opened;
//	var $localfile;		// Boolean that indicates a local file when TRUE
//	var $myfile;		// Boolean that indicates a file owned by me when TRUE

	public function __construct ($name = '')
	{
		// Initialize
		//
		_OWL::init();
		$this->type = OBJECT_FILE;
		$this->name = realpath($name);
		$this->opened = false;
		if (empty($this->name)) {
			$this->size = 0;
			$this->localfile = false;
			$this->myfile = false;
		} else {
			if (!file_exists($this->name)) {
				$this->status = FILE_NOSUCHFILE;
				return;
			}
			$this->size = filesize($this->name);
			$this->localfile = !eregi("^([a-z]+)://", $this->name);
			$this->myfile = (fileowner($this->name) == getmyuid());
		}

		$this->status = TT_STATUS_OK;
		$this->revision = parent::get_revision("$Revision");

		parent::declare_destruct ();
	}

	public function __destruct ()
	{
		if ($this->opened) {
			fclose($this->fpointer);
			$this->opened = false;
		}
	}

	public function open ($mode = 'r')
	{
		if ($this->opened) {
			$this->status = FILE_OPENOPENED;
		} else {
			if (!($this->fpointer = fopen ($this->name, $mode))) {
				$this->status = FILE_OPENERR;
			} else {
				$this->opened = true;
			}
		}
	}

	public function close ()
	{
		if (!$this->opened) {
			$this->status = FILE_CLOSECLOSED;
		} else {
			fclose($this->fpointer);
			$this->opened = false;
			if ($this->status == FILE_ENDOFFILE) {
				$this->status = TT_STATUS_OK;
			}
		}
	}

	public function read_data ()
	{
		$this->open ("rb");
		$__data = fread ($this->fpointer, $this->size);
		$this->close ();
		return ($__data);
	}

	public function read_line ($trim = FILE_NOTRIM)
	{
		$__data = fgets ($this->fpointer, 4096);
		if (feof($this->fpointer)) {
			$this->status = FILE_ENDOFFILE;
		}
		if ($trim & FILE_TRIM_L) {
			$__data = rtrim ($__data);
		}
		if ($trim & FILE_TRIM_R) {
			$__data = rtrim ($__data);
		}
		return ($__data);
	}

	public function encode ()
	{
		return (chunk_split(base64_encode($this->read_data())));
	}

	public function download ()
	{
		header('Content-Type: Oveas/CB-download; name="' . $this->original_name . '"' . "\r\n"
		     . 'Content-Length: ' . filesize ($this->location . '/' . $this->name) . "\r\n");
		header('Content-Disposition: attachment; filename="' . $this->original_name . '"' . "\r\n");
		$fp = fopen($this->location . '/' . $this->name, 'r');
		fpassthru($fp);
	}
}



/*
 * Register this class and all status codes
 */
Register::register_class('FileHandler');

//Register::set_severity (OWL_DEBUG);
//Register::set_severity (OWL_INFO);
//Register::set_severity (OWL_OK);

Register::set_severity (OWL_SUCCESS);
Register::register_code ('FILE_OPENOPENED');
Register::register_code ('FILE_CLOSECLOSED');

Register::set_severity (OWL_WARNING);
Register::register_code ('FILE_NOSUCHFILE');
Register::register_code ('FILE_ENDOFFILE');

//Register::set_severity (OWL_BUG);

Register::set_severity (OWL_ERROR);
Register::register_code ('FILE_OPENERR');

//Register::set_severity (OWL_FATAL);
//Register::set_severity (OWL_CRITICAL);
