<?php
/****?* cargobyte/config
 * NAME
 * config.php 
 *
 * SYNOPSIS
 * CargoByte configuration
 * 
 * DESCRIPTION
 * <Long description>
 *
 * AUTHOR
 *  Oscar van Eijk, Oveas Functionality Provider
 *
 * COPYRIGHT
 *  (c) 2007 -- by Oscar van Eijk/Oveas Functionality Provider 
 *
 * HISTORY
 *   date          author         changes
 *   --------------------------------------------------------
 *   Jun 26, 2008  oscar          initial version
 * 
 ***
 * $Id: config.php,v 1.1 2008-08-07 10:21:21 oscar Exp $
 */

$GLOBALS['config']['dbprefix'] = 'owl_';
$GLOBALS['config']['dbserver'] = 'localhost';
$GLOBALS['config']['dbname'] = 'owl';
$GLOBALS['config']['dbuser'] = 'oscar';
$GLOBALS['config']['dbpasswd'] = 'cosyro';

//$GLOBALS['config']['default_signal_level'] = OWL_WARNING;
$GLOBALS['config']['default_signal_level'] = -1;

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

