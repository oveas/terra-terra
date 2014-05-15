<?php
/**
 * \file
 * This file defines the FileHandler class
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
 * \ingroup TT_SO_LAYER
 * Handle all files
 * \brief File handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version May 15, 2007 -- O van Eijk -- initial version for Terra-Terra (based on an old OFM module)
 * \version Jul 30, 2008 -- O van Eijk -- Modified version for Terra-Terra
 */
class FileHandler extends _TT
{

	private $fullName;	//!< Full filename as stored on the file system
	private $fileName;	//!< Filename without path

	private $size;		//!< File size
	private $type;		//!< File type

	private $fpointer;	//!< Pointer to the file when opened
	private $opened;	//!< Boolean that's true when the file is opened
	private $writable;	//!< Boolean that's true when the file is writeable
	private $exists;	//!< Boolean that's true when the file exists

	/**
	 * Object constructor; setup the file characteristics
	 * \param[in] $name Filename
	 * \param[in] $req True if the file must exist at object create time
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($name, $req = true)
	{
		_TT::init(__FILE__, __LINE__);

		if ($req === true) {
			$this->fullName = realpath($name);
		} else {
			$this->fullName = $name;
		}
		
		$this->opened = false;

		if (!file_exists($this->fullName)) {
			if ($req === false) {
				$this->setStatus (__FILE__, __LINE__, FILE_NEWFILE, array (
						$this->fullName
				));
			} else {
				$this->setStatus (__FILE__, __LINE__, FILE_NOSUCHFILE, array (
						$this->fullName
				));
			}
			$this->exists = false;
			return;
		}
		$this->exists = true;
		$this->getFileInfo();

//		$this->localfile = (preg_match('/^([a-z]+):\/\//i', $this->fullName) === 0);
//		$this->myfile = (fileowner($this->fullName) == getmyuid());

		$this->setStatus (__FILE__, __LINE__, TT_STATUS_OK);
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
	 * Method to find out if a file exists
	 * \return True if the file exists, false otherwise
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function exists ()
	{
		return $this->exists;
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
				$this->setStatus (__FILE__, __LINE__, FILE_OPENERR, array (
						$this->fullName
				));
			} else {
				$this->setStatus (__FILE__, __LINE__, FILE_OPENED, array (
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
			$this->setStatus (__FILE__, __LINE__, FILE_NOTOPENED, array (
					$this->fullName
			));
		} else if (!$this->writable) {
			$this->setStatus (__FILE__, __LINE__, FILE_READONLY, array (
					$this->fullName
			));
		} else {
			fwrite($this->fpointer, $text . ($addEOL ? "\n" : ''));
			$this->setStatus (__FILE__, __LINE__, TT_STATUS_OK);
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
			$this->setStatus (__FILE__, __LINE__, FILE_CLOSED, array (
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
			$this->setStatus (__FILE__, __LINE__, FILE_ENDOFFILE, array ($this->fullName));
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
				return  'Terra-Terra/CB-download';
		}
	}
	
	/**
	 * Delete the file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function remove()
	{
		if (unlink($this->fullName)) {
			$this->setStatus (__FILE__, __LINE__, FILE_DELETED, array ($this->fullName));
		} else {
			$this->setStatus (__FILE__, __LINE__, FILE_DELERR, array ($this->fullName));
		}
	}
	
	
	/**
	 * Download the file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function downloadFile()
	{
		if (headers_sent()) {
			$this->setStatus (__FILE__, __LINE__, TT_HEADERSENT);
			$this->setStatus (__FILE__, __LINE__, FILE_DLERROR, array ($this->fullName));
			return;
		}
		$this->getFileInfo();

		// Required for some browsers
		if (ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}
	
		if (!file_exists($this->fullName)) {
			$this->setStatus (__FILE__, __LINE__, FILE_NOSUCHFILE, array ($this->fullName));
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

//Register::setSeverity (TT_DEBUG);
Register::setSeverity (TT_INFO);
Register::registerCode ('FILE_NEWFILE');

//Register::setSeverity (TT_OK);

Register::setSeverity (TT_SUCCESS);
Register::registerCode ('FILE_CREATED');
Register::registerCode ('FILE_OPENED');
Register::registerCode ('FILE_CLOSED');
Register::registerCode ('FILE_DELETED');

Register::setSeverity (TT_WARNING);
Register::registerCode ('FILE_NOSUCHFILE');
Register::registerCode ('FILE_ENDOFFILE');
Register::registerCode ('FILE_READONLY');
Register::registerCode ('FILE_NOTOPENED');
Register::registerCode ('FILE_DELERR');
Register::registerCode ('FILE_DLERROR');


//Register::setSeverity (TT_BUG);

Register::setSeverity (TT_ERROR);
Register::registerCode ('FILE_OPENERR');

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
