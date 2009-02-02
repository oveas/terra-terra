<?php
/**
 * \file
 * Configuration basics file for OWL; it stores some fixed configuration in a global
 * data structure 
 * \version $Id: config.php,v 1.4 2009-02-02 20:13:39 oscar Exp $
 */


$GLOBALS['register'] = array (
					  'applications'	=> array()
					, 'classes'			=> array()
				);


$GLOBALS['config']['configfiles']['owl'] = OWL_ROOT . '/owl_config.cfg';
$GLOBALS['config']['hide']['tag'] = '(hide)';
$GLOBALS['config']['hide']['value'] = '(hidden)';

$GLOBALS['config']['debug'] = false;

