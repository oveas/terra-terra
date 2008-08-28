<?php
/**
 * \file
 * Configuration basics file for OWL; it stores some fixed configuration in a global
 * data structure 
 * \version $Id: config.php,v 1.3 2008-08-28 18:12:52 oscar Exp $
 */


$GLOBALS['register'] = array (
					  'applications'	=> array()
					, 'classes'			=> array()
				);


$GLOBALS['config']['configfiles']['owl'] = OWL_ROOT . '/owl_config.cfg';
$GLOBALS['config']['hide']['tag'] = '(hide)';
$GLOBALS['config']['hide']['value'] = '(hidden)';

