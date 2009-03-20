<?php
/**
 * \file
 * This file defines the FileHandler class
 * \version $Id: class.filehandler.php,v 1.3 2009-03-20 10:56:30 oscar Exp $
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
 * Handle all files
 * \brief File handler 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 15, 2007 -- O van Eijk -- initial version for Terra-Terra (based on an old OFM module)
 * \version Jul 30, 2008 -- O van Eijk -- Modified version for OWL-PHP
 */
class FileHandler extends _OWL 
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
	 * Class constructor; setup the file characteristics
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

	public function __destruct ()
	{
		$this->close();
	}

	/**
	 * Open the file
	 * \protected
	 * \param[in] $mode Mode in which the file should be opened: 
	 *    - 'r'  	Open for reading only; place the file pointer at the beginning of the file.
	 *    - 'r+' 	Open for reading and writing; place the file pointer at the beginning of the file.
	 *    - 'w' 	Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
	 *    - 'w+' 	Open for reading and writing; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
	 *    - 'a' 	Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
	 *    - 'a+' 	Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it. 
	 */
	protected function open ($mode = 'r')
	{
		if (!$this->opened) {
			if (!($this->fpointer = fopen ($this->name, $mode))) {
				$this->set_status (FILE_OPENERR, array (
					$this->name
				));
			} else {
				$this->set_status (FILE_OPENED, array (
					$this->name
				));
				$this->opened = true;
			}
		}
	}

	/**
	 * Close the file
	 * \protected
	 */
	protected function close ()
	{
		if ($this->opened) {
			fclose($this->fpointer);
			$this->opened = false;
			$this->set_status (FILE_CLOSED, array (
				$this->name
			));
		}
	}

	/**
	 * Read the file contents and return as one dataset
	 * \protected
	 */
	protected function read_data ()
	{
		$this->open ('rb');
		$__data = fread ($this->fpointer, $this->size);
		$this->close ();
		return ($__data);
	}

	/**
	 * Read a single line from the file. File must be opened before
	 * \protected
	 * \param[in] $trim specify how the returned line should be trimmed:
	 *    - FILE_NOTRIM
	 *    - FILE_TRIM_L
	 *    - FILE_TRIM_R
	 *    - FILE_TRIM_C
	 */
	protected function read_line ($trim = FILE_NOTRIM)
	{
		$__data = fgets ($this->fpointer, 4096);
		if (feof($this->fpointer)) {
			$this->set_status (FILE_ENDOFFILE, array (
				$this->name
			));
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
