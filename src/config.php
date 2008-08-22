<?php
/**
 * \file
 * Configuration basics file for OWL; it stores some fixed configuration in a global
 * data structure 
 * \version $Id: config.php,v 1.2 2008-08-22 12:02:13 oscar Exp $
 */


$GLOBALS['register'] = array (
					  'applications'	=> array()
					, 'classes'			=> array()
				);

//$GLOBALS['config']['logging'] = array (
//			  'multiple_file'	=> false
//			, 'persistant'		=> true
//			, 'filename'		=> '/tmp/owl.log'
//			, 'level'			=> OWL_DEBUG
//			, 'hide_passwords'	=> true
//		);
//
//$GLOBALS['config']['exception'] = array (
//			  'show_in_browser'	=> false
//			, 'show_values'		=> true
//			, 'max_value_len'	=> 15
//		);
//
//$GLOBALS['config']['locale'] = array (
//			  'date'		=> 'd-M-Y'
//			, 'time'		=> 'H:i'
//			, 'log_date'	=> 'd-m-Y'
//			, 'log_time'	=> 'H:i:s.u'
//			, 'lang'		=> 'en-uk'
//		); 

$GLOBALS['config']['configfiles']['owl'] = OWL_ROOT . '/owl_config.cfg';

/*
 *  List some known filetypes.
 */
$GLOBALS['config']['image_types'] = '/gif/jpg/jpeg/png/swf/psd/bmp/';
$GLOBALS['config']['archive_types']  = '/gzip/gz/Z/zip/arj/zoo/tar/bzip2/';
$GLOBALS['config']['webdocument_types'] = '/html/php/';
$GLOBALS['config']['script_types']  = '/php/';


/*
* List the ASCII and Binary file types. Not all types have to be
* listed here; if a type can be both ASCII and Binary (e.g. <filename>.dat),
* don't include /dat/ in either of the lists; the File classe will make an
* attempt to determin what it is, so only types are *always* ASCII or Binary
* should be listed here!
*/
$GLOBALS['config']['binary_files'] = '/zip/gz/tar/arj/gif/jpg/jpeg/png/swf/psd/bmp/pdf/exe/';
$GLOBALS['config']['ascii_files']  = '/txt/html/php/js/asp/phtml/xhtml/';
