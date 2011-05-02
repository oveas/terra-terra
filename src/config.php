<?php
/**
 * \file
 * Configuration basics file for OWL; it stores some fixed configuration in a global
 * data structure 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: config.php,v 1.7 2011-05-02 12:56:15 oscar Exp $
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
