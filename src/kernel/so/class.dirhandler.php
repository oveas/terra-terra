<?php
/**
 * \file
 * This file defines the DirHandler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2013} Oscar van Eijk, Oveas Functionality Provider
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
 * \ingroup TT_SO_LAYER
 * Handle all directories
 * \brief Directory handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version June 25, 2013 -- O van Eijk -- Initial version
 */
class DirHandler extends _TT
{

	private $fullName;	//!< Full filename as stored on the file system
	
	private $objList;	//!< Iterator pointer for a recursive scan

	private $fpointer;	//!< Pointer to the file when opened
	private $opened;	//!< Boolean that's true when the file is opened
	private $writable;	//!< Boolean that's true when the file is writeable


	/**
	 * Object constructor; setup the directory characteristics
	 * \param[in] $name Directoryname
	 * \param[in] $req True if the directory must exist at object create time
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($name, $req = true)
	{
		_TT::init(__FILE__, __LINE__);
		$this->objList = null;
		if ($req === true) {
			$this->fullName = realpath($name);
		} else {
			$this->fullName = $name;
		}
		$this->fileName = basename($this->fullName);

		$this->opened = false;

		if (!file_exists($this->fullName)) {
			if ($req === false) {
				$this->setStatus (DIR_NEWDIR, array ($this->fullName));
			} else {
				$this->setStatus (DIR_NOSUCHDIR, array ($this->fullName));
			}
			return;
		} else {
			if (!is_dir($this->fullName)) {
				$this->setStatus (DIR_NOTADIR, array ($this->fullName));
				return;
			}
		}

		$this->setStatus (TT_STATUS_OK);
	}

	/**
	 * Initialise an iterator for a scan
	 * \param[in] $recursive True when the scan must be recursive
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function startIterator ($recursive = false)
	{
		if ($recursive === true) {
			$this->objList =  new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($this->fullName),
					RecursiveIteratorIterator::CHILD_FIRST
			);
		} else {
			$this->objList = new DirectoryIterator($this->fullName);
		}
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
	 * Create this directory
	 * \param[in] $prot File protection
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function create($prot = 0750)
	{
		if (is_string($prot)) {
			$prot = octdec($prot);
		}
		mkdir($this->fullName, $prot);
		$this->setStatus (DIR_CREATED, array ($this->fullName));
	}
	
	/**
	 * Create a subdirectory
	 * \param[in] $subDir Name of the subdirectory
	 * \param[in] $prot File protection
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function createSub($subDir, $prot = 0750)
	{
		if (is_string($prot)) {
			$prot = octdec($prot);
		}
		mkdir($this->fullName . '/' . $subDir, $prot);
		$this->setStatus (DIR_CREATED, array ($this->fullName));
	}
	

	/**
	 * Remove the directory
	 * \param[in] $includeSubs If true, all contents will be deleted as well
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo It looks like the iterator always returns the directoryname as the last element. If not, we need to
	 * find out when we should reverse iterating (using a temp- array; there's no reverse iterator)
	 */
	public function remove ($includeSubs = false)
	{
		if ($includeSubs === true) {
			while (($_file = $this->scanDir()) !== null) {
				if (is_dir($_file)) {
					if (!rmdir ($_file)) {
						$this->setStatus (DIR_RMDIRERR, array ($_file));
					}
				} else {
					if (!unlink ($_file)) {
						$this->setStatus (DIR_RMFILERR, array ($_file));
					}
				}
				$_tree[] = $_file;
			}
		}
		if (rmdir ($this->fullName)) {
			$this->setStatus (DIR_DELETED, array ($this->fullName));
		} else {
			$this->setStatus (DIR_RMDIRERR, array ($this->fullName));
		}
	}
	
	/**
	 * Method to scan an existing direct tree
	 * \param[in] $includeSubs True (default) when subdirectories must be scanned too
	 * \param[in] $skipDots Don't return directory names '.' and '..' by default. If these must be returned as well,
	 * pass 'false' as parameter
	 * \return The next fileobject, of null when the last name has been returned
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function scanDir($includeSubs = true, $skipDots = true)
	{
		if ($this->objList === null) {
			$this->startIterator($includeSubs);
		}
		while ($this->objList->valid()) {
			$fileObject = $this->objList->current();
			$this->objList->next();
			if (!$this->isDot($fileObject) || $skipDots === false) {
				return ($fileObject->getPathname());
			}
		}
		$this->objList = null;
		return null;
	}

	/**
	 * Check if a file is a directory named '.' or '..'
	 * \param[in] $fileObject SPL File object
	 * \return True when it is a dot-directory
	 */
	private function isDot ($fileObject)
	{
		if ($fileObject->getFilename() === '.' || $fileObject->getFilename() === '..') {
			return true;
		}
		return false;
	}

	/**
	 * Create a new zip archive for this folder
	 * \param[in] $zipFile Name of the file. The file will be created in TT_TEMP
	 * \return File object for the zipfile
	 */
	public function zipFolder ($zipFile)
	{
		if (!TTloader::getClass('filehandler')) {
			trigger_error('Error loading the Filehandler class', E_USER_ERROR);
		}
		if (!preg_match('/\.zip$/i', $zipFile)) {
			$zipFile .= '.zip';
		}

		$_zipHandler = new FileHandler(TT_TEMP . '/' . $zipFile, false);
		$_zip = new ZipArchive;
		if (!$_zip->open($_zipHandler->getFileName(), ZipArchive::CREATE)) {
			$this->setStatus (DIR_ZIPERR, array ($_zipHandler->getFileName()));
			return;
		}
		while (($_file = $this->scanDir()) !== null) {
			$_localFile = substr($_file, strlen(TT_TEMP)+1);
			if (is_dir($_file)) {
				$_localFile .= '/'; // Make sure empty dirs won't be stored as a file
			}
			if (!$_zip->addFile($_file, $_localFile)) {
				$this->setStatus (DIR_ADDZIPERR, array ($_zipHandler->getFileName()), $_localFile);
			}
		}
		$_zip->close();
		return $_zipHandler;
	}
}

/*
 * Register this class and all status codes
*/
Register::registerClass('DirHandler', TT_APPNAME);

//Register::setSeverity (TT_DEBUG);
Register::setSeverity (TT_INFO);
Register::registerCode ('DIR_NEWDIR');

//Register::setSeverity (TT_OK);

Register::setSeverity (TT_SUCCESS);
Register::registerCode ('DIR_CREATED');
Register::registerCode ('DIR_DELETED');

Register::setSeverity (TT_WARNING);
Register::registerCode ('DIR_ADDZIPERR');
Register::registerCode ('DIR_NOSUCHDIR');
Register::registerCode ('DIR_NOTADIR');
Register::registerCode ('DIR_RMDIRERR');
Register::registerCode ('DIR_RMFILERR');



//Register::setSeverity (TT_BUG);

Register::setSeverity (TT_ERROR);
Register::registerCode ('DIR_ZIPERR');

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
