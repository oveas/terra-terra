<?php
/**
 * \file
 * This file defines the FileHandler class
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
 * \defgroup FILE_TrimFlags Dataline trim flags
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
 * \todo This one contains a lot of old OFM stuff... porting (or erasing...) of old code in progress...
 */
class FileHandler extends _OWL
{

	private $fullName;	//!< Full filename as stored on the file system
	private $fileName;	//!< Filename without path

	private $size;		//!< File size
	private $type;		//!< File type

	private $fpointer;	//!< Pointer to the file when opened
	private $opened;	//!< Boolean that's true when the file is opened
	private $writable;	//!< Boolean that's true when the file is writeable

	/**
	 * Object constructor; setup the file characteristics
	 * \param[in] $name Filename
	 * \param[in] $req True if the file must exist at object create time
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($name, $req = true)
	{
		_OWL::init();

		if ($req === true) {
			$this->fullName = realpath($name);
		} else {
			$this->fullName = $name;
		}
		
		$this->opened = false;

		if (!file_exists($this->fullName)) {
			if ($req === false) {
				$this->setStatus (FILE_NEWFILE, array (
						$this->fullName
				));
			} else {
				$this->setStatus (FILE_NOSUCHFILE, array (
						$this->fullName
				));
			}
			return;
		}
		$this->getFileInfo();

//		$this->localfile = (preg_match('/^([a-z]+):\/\//i', $this->fullName) === 0);
//		$this->myfile = (fileowner($this->fullName) == getmyuid());

		$this->setStatus (OWL_STATUS_OK);
	}

	/**
	 * Object destructor
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __destruct ()
	{
		if (parent::__destruct() === false) {
			return false; // Skip the rest
		}
		$this->close();
		return true;
	}

	/**
	 * Get the name of the file
	 * \return Full pathname
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getFileName()
	{
		return $this->fullName;
	}
	
	/**
	 * Get information about the file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getFileInfo()
	{
		$this->size = filesize($this->fullName);
		$_finfo = pathinfo($this->fullName);
		$this->type = strtolower($_finfo['extension']);
		$this->fileName = basename($_finfo['basename']);
	}
	
	/**
	 * Open the file
	 * \param[in] $mode Mode in which the file should be opened:
	 *    - 'r'  	Open for reading only; place the file pointer at the beginning of the file.
	 *    - 'r+' 	Open for reading and writing; place the file pointer at the beginning of the file.
	 *    - 'w' 	Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
	 *    - 'w+' 	Open for reading and writing; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
	 *    - 'a' 	Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
	 *    - 'a+' 	Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function open ($mode = 'r')
	{
		if (!$this->opened) {
			if (!($this->fpointer = fopen ($this->fullName, $mode))) {
				$this->setStatus (FILE_OPENERR, array (
						$this->fullName
				));
			} else {
				$this->setStatus (FILE_OPENED, array (
						$this->fullName
				));
				$this->opened = true;
			}
			$this->writable = is_writable($this->fullName);
		}
	}

	/**
	 * Write text to a file that has been opened for write before
	 * \param[in] $text Text to write
	 * \param[in] $addEOL False when no end-of-line character must be added. Default is true
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function write ($text, $addEOL = true)
	{
		if (!$this->opened) {
			$this->setStatus (FILE_NOTOPENED, array (
					$this->fullName
			));
		} else if (!$this->writable) {
			$this->setStatus (FILE_READONLY, array (
					$this->fullName
			));
		} else {
			fwrite($this->fpointer, $text . ($addEOL ? "\n" : ''));
			$this->setStatus (OWL_STATUS_OK);
		}
	}
	/**
	 * Close the file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function close ()
	{
		if ($this->opened) {
			fclose($this->fpointer);
			$this->opened = false;
			$this->setStatus (FILE_CLOSED, array (
					$this->fullName
			));
		}
	}

	/**
	 * Read the file contents and return as one dataset. the file does not have to be opened yet
	 * \return Complete content of the file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function readData ()
	{
		$this->open ('rb');
		$__data = fread ($this->fpointer, $this->size);
		$this->close ();
		return ($__data);
	}

	/**
	 * Read a single line from the file. File must be opened before
	 * \param[in] $trim specify how the returned line should be trimmed:
	 *    - FILE_NOTRIM
	 *    - FILE_TRIM_L
	 *    - FILE_TRIM_R
	 *    - FILE_TRIM_C
	 * \return Line read from the file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function readLine ($trim = FILE_NOTRIM)
	{
		$__data = fgets ($this->fpointer, 4096);
		if (feof($this->fpointer)) {
			$this->setStatus (FILE_ENDOFFILE, array ($this->fullName));
		}
		if ($trim & FILE_TRIM_L) {
			$__data = rtrim ($__data);
		}
		if ($trim & FILE_TRIM_R) {
			$__data = rtrim ($__data);
		}
		return ($__data);
	}

	/**
	 * Encode the contents of the file in base64
	 * \return Encoded file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function encode ()
	{
		return (chunk_split(base64_encode($this->readData())));
	}

	/**
	 * Very basic Mime Type getter based on the file type
	 * \return Mime type
	 * \todo Replace by something using magic.mime that'll work on Windows too
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function getMimeType()
	{
		switch ($this->type) {
			case 'pdf':
				return 'application/pdf';
				break;
			case 'exe':
				return 'application/octet-stream';
				break;
			case 'zip':
				return 'application/zip';
				break;
			case 'doc':
				return 'application/msword';
				break;
			case 'xls':
				return 'application/vnd.ms-excel';
				break;
			case 'ppt':
				return 'application/vnd.ms-powerpoint';
				break;
			case 'gif':
				return 'image/gif';
				break;
			case 'png':
				return 'image/png';
				break;
			case 'jpeg':
			case 'jpg':
				return 'image/jpg';
				break;
			default:
				return  'OWL-PHP/CB-download';
		}
	}
	
	/**
	 * Delete the file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function remove()
	{
		if (unlink($this->fullName)) {
			$this->setStatus (FILE_DELETED, array ($this->fullName));
		} else {
			$this->setStatus (FILE_DELERR, array ($this->fullName));
		}
	}
	
	
	/**
	 * Download the file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function downloadFile()
	{
		if (headers_sent()) {
			$this->setStatus (OWL_HEADERSENT, array ($this->fullName));
			return;
		}
		$this->getFileInfo();

		// Required for some browsers
		if (ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}
	
		if (!file_exists($this->fullName)) {
			$this->setStatus (FILE_NOSUCHFILE, array ($this->fullName));
			return;
		}

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private',false); // required for certain browsers
		header("Content-Type: " . $this->getMimeType());
		header('Content-Disposition: attachment; filename="'.$this->fileName.'";' );
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $this->size);
		ob_clean();
		flush();
		readfile ($this->fullName);
	}
}

/*
 * Register this class and all status codes
*/
Register::registerClass('FileHandler');

//Register::setSeverity (OWL_DEBUG);
Register::setSeverity (OWL_INFO);
Register::registerCode ('FILE_NEWFILE');

//Register::setSeverity (OWL_OK);

Register::setSeverity (OWL_SUCCESS);
Register::registerCode ('FILE_CREATED');
Register::registerCode ('FILE_OPENED');
Register::registerCode ('FILE_CLOSED');
Register::registerCode ('FILE_DELETED');

Register::setSeverity (OWL_WARNING);
Register::registerCode ('FILE_NOSUCHFILE');
Register::registerCode ('FILE_ENDOFFILE');
Register::registerCode ('FILE_READONLY');
Register::registerCode ('FILE_NOTOPENED');
Register::registerCode ('FILE_DELERR');


//Register::setSeverity (OWL_BUG);

Register::setSeverity (OWL_ERROR);
Register::registerCode ('FILE_OPENERR');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
