<?php
/**
 * \file
 * Configuration basics file for OWL; it stores some fixed configuration in a global
 * data structure 
 * \version $Id: config.php,v 1.6 2011-01-18 14:24:58 oscar Exp $
 */


$GLOBALS['register'] = array (
	  'applications'	=> array()
	, 'classes'			=> array()
);


$GLOBALS['config'] = array(
	  'configfiles'			=> array(
				  'owl'	=> OWL_ROOT . '/owl_config.cfg'
				, 'app'	=> array()
	)
	, 'hidden_values'		=> array()
	, 'protected_values'	=> array()
//	Configure the configuration ;)
	, 'config'				=> array(
				  'protect_tag'	=> '(!)'
				, 'hide_tag'	=> '(hide)'
				, 'hide_value'	=> '(hidden)'
	)
);
