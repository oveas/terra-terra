<?php
/**
 * \file
 * Configuration basics file for OWL; it stores some fixed configuration in a global
 * data structure
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: config.php,v 1.9 2011-10-16 11:11:45 oscar Exp $
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


$GLOBALS['register'] = array (
	  'applications'	=> array()
	, 'classes'			=> array()
);


$GLOBALS['config'] = array(
	  'configfiles'			=> array(
				  'owl'	=> OWL_ROOT . '/owl_config.cfg'
				, 'app'	=> array()
	)
	, 'values'				=> array()
	, 'hidden_values'		=> array()
	, 'protected_values'	=> array()
//	Configure the configuration ;)
	, 'config'				=> array(
				  'protect_tag'	=> '(!)'
				, 'hide_tag'	=> '(hide)'
				, 'hide_value'	=> '(hidden)'
	)
);
