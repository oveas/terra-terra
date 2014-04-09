<?php
/**
 * \file
 * \ingroup TT_CONTRIB
 * This file provides some helper functions to uncomment lines or textblocks
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2014} Oscar van Eijk, Oveas Functionality Provider
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
 * \defgroup TT_COMMENTTYPES Comment types
 * These constants define the type of comments that are accepted by the uncomment functions
 * @{
 */

//! Custom comment type, must be given as a next paramter
define ('TT_COMMENT_CUSTOM',	0);

//! PHP comment: accepts '//' as line comment and '/* ... */' as block comment. Can be used for
//! other languages as well: C(++), CSS (block comment only) etc.
define ('TT_COMMENT_PHP',		1); //!<  

//! PHP comment: accepts '-- ' as line comment (including the trailing blank!)
define ('TT_COMMENT_SQL',		2);

//! PHP comment: accepts '#' as a line comment
define ('TT_COMMENT_PERL',		3);

//! PHP comment: accepts ';' as a line comment
define ('TT_COMMENT_CFG',		4);

/**
 * @}
 */

/**
 * Remove all comments from a given inputline
 * \param[in] $line Input line that might hold some comments
 * \param[in] $type Linetype, must be one of the defines types in \ref TT_COMMENTTYPES
 * \param[in] $pattern Pattern in regular expression format to describe the comment character(s). Required 
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \return String with the comments removed
 */
function uncommentLine($line, $type, $pattern = null)
{
	$_substitute = '';
	if ($type == TT_COMMENT_PHP) {
		if (strpos($line, '?>') !== false) {
			$_pattern = '(\/\/)(.*?)((\?>)(.*)?)';
			$_substitute = '$3';
		} else {
			$_pattern = '\/\/.*';
		}
	} elseif ($type == TT_COMMENT_PERL) {
		$_pattern = '#.*';
	} elseif ($type == TT_COMMENT_CFG) {
		$_pattern = ';.*';
	} elseif ($type == TT_COMMENT_SQL) {
		$_pattern = '-- .*';
	} elseif ($type == TT_COMMENT_CUSTOM) {
		if ($pattern === null) {
			$_c = ttGetCaller();
			TT::stat(__FILE__, __LINE__, TT_STATUS_BUG, array($_c['file'], $_c['line'], 'Custom comment without a pattern'));
			return $line;
		}
		$_pattern = $pattern . '.*';
	} else {
		$_c = ttGetCaller();
		TT::stat(__FILE__, __LINE__, TT_STATUS_BUG, array($_c['file'], $_c['line'], 'Unknown comment type: ' . $type));
		return $line;
	}
	$line = preg_replace ("/$_pattern/", $_substitute, $line);
	return $line;
}