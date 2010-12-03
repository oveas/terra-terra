<?php
/**
 * \file
 * This file defines the FileHandler class
 * \version $Id: class.filehandler.php,v 1.5 2010-12-03 12:07:42 oscar Exp $
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
	 * \protected
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
		if (parent::__destruct() === false) {
			return false; // Skip the rest
		}
		$this->close();
		return true;
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
