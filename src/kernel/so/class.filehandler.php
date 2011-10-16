<?php
/**
 * \file
 * This file defines the FileHandler class
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: class.filehandler.php,v 1.9 2011-10-16 11:11:44 oscar Exp $
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
 */
class FileHandler extends _OWL
{

	/**
	 * Full filename as stored on the file system
	 */
	protected $name;
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
	 * Class constructor; setup the file characteristics
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

	public function __destruct ()
	{
		if (parent::__destruct() === false) {
			return false; // Skip the rest
		}
		$this->close();
		return true;
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
	protected function open ($mode = 'r')
	{
		if (!$this->opened) {
			if (!($this->fpointer = fopen ($this->name, $mode))) {
				$this->setStatus (FILE_OPENERR, array (
					$this->name
				));
			} else {
				$this->setStatus (FILE_OPENED, array (
					$this->name
				));
				$this->opened = true;
			}
		}
	}

	/**
	 * Close the file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function close ()
	{
		if ($this->opened) {
			fclose($this->fpointer);
			$this->opened = false;
			$this->setStatus (FILE_CLOSED, array (
				$this->name
			));
		}
	}

	/**
	 * Read the file contents and return as one dataset
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function readData ()
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
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	protected function readLine ($trim = FILE_NOTRIM)
	{
		$__data = fgets ($this->fpointer, 4096);
		if (feof($this->fpointer)) {
			$this->setStatus (FILE_ENDOFFILE, array (
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
		return (chunk_split(base64_encode($this->readData())));
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


class OldOFMStuff
{


	function OFM_FileDetails ($Path, $File) {
/*
 * Get detailed information for the selected file and return it in
 * an indexed array.
 */
   global $DateFormat;
   global $FileSizes;
   global $TotalSize;
   global $TotalFiles;
   global $ShowHidden;

   $f = $Path . "/" . $File;

   $fdet["name"]   = $File;
   $fdet["path"]   = $Path;

   if ($File == "." || $File == "..") {
      // Never list 'Current' or 'Parent'
      //
      return ($fdet);
   }

   if (ereg("^\.", $File) && !$ShowHidden) {
      // If ShowHidden is set to 'false' skip files starting with a '.'
      //
      return ($fdet);
   }


   /*
    * Symbolic link? If so, we don't need to know the rest.
    */
   if (is_link($f)) {
      $fdet["link"] = readlink($f);
      $fdet["alink"] = OFM_TranslateLink ($fdet["path"], $fdet["link"]);
      return ($fdet);
   } else {
      $fdet["link"] = "";
   }

   /*
    * Some file characterisics
    */
   $fdet["dir"]     = is_dir($f);
   $fdet["exec"]    = is_executable($f);
   $fdet["file"]    = is_file($f);
   $fdet["read"]    = is_readable($f);
   $fdet["write"]   = is_writeable($f);
   $fdet["upload"]  = is_uploaded_file($f);

   /*
    * File date information
    */
   $fdet["access"]  = date($DateFormat, fileatime($f));
   $fdet["change"]  = date($DateFormat, filectime($f));
   $fdet["modify"]  = date($DateFormat, filemtime($f));


   /*
    * Owner and group
    */
   $tmpa = posix_getpwuid(fileowner($f));
   $fdet["owner"] = $tmpa["name"];

   $tmpa = posix_getgrgid(filegroup($f));
   $fdet["group"] = $tmpa["name"];

   /*
    * Permissions
    */
   $fperm = fileperms($f);
   // Create the privilege list in *reverse* order!
   //
   $perms = array("x", "w", "r"    // World
                , "x", "w", "r"    // Group
		, "x", "w", "r");  // User (owner)
   $fdet["perm"] = "";
   for ($i = 0; $i <= 8; $i++) {
      if ($fperm & 1) {
         $fdet["perm"] = $perms[$i] . $fdet["perm"];
      } else {
         $fdet["perm"] = "-" . $fdet["perm"];
      }
      $fperm = $fperm >> 1;
   }

   /*
    * File size (in human readable format).
    * The TotalSize is increased and formatted later
    */
   $fdet["size"] = filesize($f);
   $TotalSize += $fdet["size"];

   for ($i = 0; $fdet["size"] > 1024; $i++) {
      $fdet["size"] = $fdet["size"] / 1024;
   }
   $fdet["size"] = round($fdet["size"],2) . " " . $FileSizes[$i];

   /*
    * Get extra information about the file. This is returned
    * in an array, creating a second level here.
    */
   if (!$fdet["dir"]) {
      $fdet["info"] = OFM_FileInfo ($Path, $File);
   }

   /*
    * Increase the nr of files in this directory
    */
   $TotalFiles++;
   return ($fdet);
}

function OFM_FileInfo ($Path, $File) {
/*
 * Return the file type and additional information.
 * The info is returned in an array.
 * The type defines the type of the file, subtype is only filled if
 * OFM can actually do something with the file.
 */
  global $SupportedArchives;
  global $KnownArchives;
  global $KnownImages;
  global $KnownWebDocuments;
  global $KnownScriptSources;

  $f = $Path . "/" . $File;
  $nel = explode (".", $File);
  $FileInf["ext"] = strtolower ($nel[count($nel)-1]);

  $FileInf["ascii"] = -1;

  /*
   * Graphics
   */
  if (ereg("/" . $FileInf["ext"] . "/", $KnownImages)) {
     $FileInf["type"] = "image";
     $FileInf["subtype"] = $FileInf["ext"];
     // Exceptions:
     //
     if ($FileInf["ext"] == "jpeg")  { $FileInf["subtype"] = "jpg"; }

     if ($ImgInf = GetImageSize($f)) {
        $FileInf["imgwidth"]  = $ImgInf[0];
        $FileInf["imgheight"] = $ImgInf[1];
     } else {
        $FileInf["subtype"] = "";
     }
     $FileInf["ascii"] = 0;
   }

  /*
   * Archives
   */
  // Exceptions:
  //
//  if ($FileInf["ext"] == "gz")  { $FileInf["ext"] = "gzip"; }
  if (ereg("/" . $FileInf["ext"] . "/", $KnownArchives)) {
     $FileInf["type"] = "archive";
     $FileInf["subtype"] = $FileInf["ext"];

     if (!ereg("/" . $FileInf["subtype"] . ":", $SupportedArchives)) {
        $FileInf["subtype"] = "";
     }
     $FileInf["ascii"] = 0;
  }


  /*
   * Webdocuments
   */
  if (ereg("/" . $FileInf["ext"] . "/", $KnownWebDocuments)) {
     $FileInf["type"] = "webdoc";
     $FileInf["subtype"] = $FileInf["ext"];
     $FileInf["ascii"] = 1;
   }

  /*
   * WebScripts sources
   */
  if (ereg("/" . $FileInf["ext"] . "/", $KnownScriptSources)) {
     $FileInf["type"] = "webscript";
     $FileInf["subtype"] = $FileInf["ext"];
     $FileInf["ascii"] = 1;
   }



  /*
   * Not a predefined type; find out if it's Ascii or Binary
   */
  if ($FileInf["ascii"] == -1) {
     $FileInf["ascii"] == @OFM_IsAscii ($f, $FileInf["ext"]);
  }

  /*
   * The file is ASCII, now see if it's DOS or UNIX
   */
  if ($FileInf["ascii"]) {
     $FileInf["asciitype"] = "unix";
     if (($fp = @fopen ($f, "r"))) {
        $Line = fread ($fp, 4096);
        fclose ($fp);
        if (ereg("\r\n$", $Line)) { $FileInf["asciitype"] = "dos"; }
        if (ereg("\r$",   $Line)) { $FileInf["asciitype"] = "mac"; }
     }
  }

  return ($FileInf);
}


function OFM_IsAscii ($File, $Type) {
/*
 * Find out if the file if Ascii or Binary.
 * First we check known types from the config file. If the given type is not
 * listed, the first 4096 bytes are checked; if they contain a
 * newline character, the file is asumed to be Ascii.
 */
   global $BinaryFileTypes;
   global $ASCIIFileTypes;

   if (ereg("/" . $Type . "/", $BinaryFileTypes)) {  return (0); }
   if (ereg("/" . $Type . "/", $ASCIIFileTypes))  {  return (1); }

   /*
    * Unknown type; read a line to find out
    */

   // Unreadable; asume binary
   if (!($fp = fopen ($File, "r"))) { return (0); }

   $Line = fread ($fp, 4096);
   fclose ($fp);
   if (ereg("\n", $Line)) { return (1); }
   return (0);
}

/*
sub OFM_convert_file ($$) {
   my ($fname, $ctype) = @_;
   error ('flocked', $fname, $OPLSteering{'Lock_Timeout'}) if (&OPL_lock ($fname, $OPLSteering{'Lock_Timeout'}) == 0);
   my $ftemp = $fname . ".OFM.tmp";
   if (&OPL_lock ($ftemp, $OPLSteering{'Lock_Timeout'}) == 0) {
      OPL_unlock  ($fname);
      error ('flocked', $ftemp, $OPLSteering{'Lock_Timeout'})
   }
   rename ($fname, $ftemp);
   open (FILE, $ftemp);
   open (CONV, ">$fname");
   while (my $line = <FILE>) {
      if ($ctype eq "mac2unix") {
         $line =~ s/\r/\n/g;
	 print CONV $line;
      } else {
         $line =~ s/\n$//;
         $line =~ s/\r$//;
         if ($ctype eq "dos2unix") {
            $line .= "\n";
         } elsif ($ctype eq "unix2dos") {
            $line .= "\r\n";
         } elsif ($ctype eq "unix2mac") {
            $line .= "\r";
         }
	 print CONV $line;
      }
   }
   close FILE;
   close CONF;
   OPL_unlock ($fname);
   OPL_unlock ($ftemp);
   OPL_delete($ftemp);
   my ($a1, $a2) = split (/2/, $ctype);
   $predirect = OFM_predirect("/ofm.php?presp=" .
      signal("2253", $fname . $OPLSteering{'FieldSep'} . $a1 . $OPLSteering{'FieldSep'} . $a2));
}
*/

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

Register::setSeverity (OWL_WARNING);
Register::registerCode ('FILE_NOSUCHFILE');
Register::registerCode ('FILE_ENDOFFILE');

//Register::setSeverity (OWL_BUG);

Register::setSeverity (OWL_ERROR);
Register::registerCode ('FILE_OPENERR');

//Register::setSeverity (OWL_FATAL);
//Register::setSeverity (OWL_CRITICAL);
