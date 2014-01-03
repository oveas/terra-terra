<?php
/**
 * \file
 * Configuration basics file for TT; it stores some fixed configuration in a global
 * data structure
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2011} Oscar van Eijk, Oveas Functionality Provider
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

TTCache::set(
		TTCACHE_CONFIG
		, 'files'
		, array(
				'tt'	=> TT_ROOT . '/tt_config.cfg'
				,'app'	=> array()
		)
);
//	Configure the configuration ;)
TTCache::set(
		TTCACHE_CONFIG
		, 'config'
		, array(
				'protect_tag'	=> '(!)'
				,'hide_tag'	=> '(hide)'
				,'hide_value'	=> '(hidden)'
		)
);
