<?php
/**
 * \file
 * \ingroup OWL_SO_LAYER
 * This file defines the class to install applications
 * \version $Id: OWLinstaller.php,v 1.4 2011-05-12 14:37:58 oscar Exp $
 */

/**
 * \ingroup OWL_SO_LAYER
 * Abstract class to install applications
 * \brief Application installer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 19, 2011 -- O van Eijk -- Initial version for OWL-PHP
 */
abstract class OWLinstaller
{
	/**
	 * Array with registered rights and their bitvalues
	 */
	private static $rights = array();

	/**
	 * Array with registered groups and their IDs
	 */
	private static $groups = array();

	/**
	 * Preload OWL data that can be used during the application install
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function construct()
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('owltables', true)) {
				$dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->setTablename('group');
		$dataset->set('aid', OWL_ID);
		$dataset->setKey('aid');
		$dataset->prepare();
		$dataset->db($_data, __LINE__, __FILE__);
		foreach ($_data as $_grp) {
			self::$groups[$_grp['groupname'] . '__AID__' . OWL_ID] = $_grp['gid'];
		}
	}

	/**
	 * Register an application in the database
	 * \param[in] $code Application code
	 * \param[in] $name Name of the application
	 * \param[in] $version Version number of the application
	 * \param[in] $description Optional description
	 * \param[in] $link Optional link to the applications homepage
	 * \param[in] $author Optional name of the copyright holder
	 * \param[in] $license Optional license
	 * \return The application ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo Error checking
	 */
	public static function installApplication ($code, $name, $version, $description = '', $link = '', $author = '', $license = '')
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('owltables', true)) {
				$dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->setTablename('applications');
		$dataset->set('code', $code);
		$dataset->set('name', $name);
		$dataset->set('version', $version);
		$dataset->set('description', $description);
		$dataset->set('link', $link);
		$dataset->set('author',$author);
		$dataset->set('license', $license);
		$dataset->prepare(DATA_WRITE);
		$dataset->db($_dummy, __LINE__, __FILE__);
		return ($dataset->insertedId());
	}

	/**
	 * Enable an application
	 * \param[in] $id Application ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function enableApplication ($id)
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('owltables', true)) {
				$dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->setTablename('applications');
		$dataset->set('aid', $id);
		$dataset->set('enabled', 1);
		$dataset->setKey('aid');
		$dataset->prepare(DATA_UPDATE);
		$dataset->db($_dummy, __LINE__, __FILE__);
	}

	/**
	 * Parse a line from an SQL file, stripping all comments
	 * \param[in] $line Line as read from the SQL file
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \return String with a (part of the) SQL statement
	 */
	private static function uncommentSQL($line)
	{
		$line = preg_replace('/^\s*--\s.*/', '', $line);
		return (trim($line));
	}

	/**
	 * Parse a given SQL file
	 * \param[in] $fname Full path specification of the file containing SQL statements
	 * \return An array with all SQL statements from the file, or null when an error occured
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function parseSQLFile ($fname)
	{
		if (!file_exists($fname)) {
			return null;
		}
		$statement = '';
		$queries = array();
		if (!($_fh = fopen($fname, 'r'))) {
			return null;
		}
		while (($_line = fgets($_fh, 4096)) !== false) {
			$_line = self::uncommentSQL($_line);
			if (preg_match('/;\s*$/', $_line)) {
				$statement .= (' ' . $_line);
				OWLdbg_add(OWLDEBUG_OWL_S04, $statement, 'SQL statement');
				$queries[] = $statement;
				$statement = '';
			} elseif ($_line == '') {
				continue;
			} else {
				$statement .= (' ' . $_line);
			}
		}
		fclose($_fh);
		return ($queries);
	}

	/**
	 * Check an SQL statement and lok for table names. When a tablename has been found, the
	 * table prefix will be added.
	 * 
	 * The following SQL queries are supported by the regular expressions (with and without backticks):
	 * 	- CREATE [TEMPORARY] TABLE [IF NOT EXISTS] `tblname`
	 * 	- ALTER [IGNORE] TABLE `tblname`
	 * 	- DROP [TEMPORARY] TABLE [IF EXISTS] `tblname`
	 * 	- INSERT [insert type] [IGNORE] [INTO] `tblname` (...)
	 * 	- CREATE [type] INDEX [name] [using type] ON `tblname` (...)
	 * \param[in] $q SQL query
	 * \param[in] $prefix Prefix, or null to use the default
	 * \return SQL statement with the prefixed tablename
	 * \todo Add support for UPDATE, DELETE, RENAME and other statements that *might* occur in SQL install files
	 * \todo Handle constraints ([...] REFERENCES `tblname` (...))
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function addTablePrefix ($q, $prefix)
	{
		// Abuse a datahandler to get the proper db handler
		$_d = new DataHandler();
		if ($prefix !== null) {
			$_d->setPrefix($prefix);
		} else {
			$_d->setPrefix(ConfigHandler::get('dbprefix', null, true));
		}
		$db = $_d->getDbLink();
		$tblRegEx = '((create|drop|alter)\s+((temporary|ignore)\s+)?(table)(\s*if\s*(not\s*)?(exists))?)';
		$idxRegEx = '((create)\s+((unique|fulltext|spatial)\s+)?((index\s+)([a-z_`]+\s*)(using\s+[a-z]+\s+)?on))';
		$insRegEx = '((insert)(\s*(low_priority|delayed|high_priority))?(\s*ignore\s*)?(\s*into)?)';

		$tblName = '\s+`?([a-z_]+)`?\s*';
		$_regexp = "/^\s*($tblRegEx|$idxRegEx|$insRegEx)$tblName/i";
		preg_match ($_regexp, $q, $matches);
		if (count($matches) > 24) {
			$_tblName = $db->tablename($matches[24], true);
			$q = preg_replace("/$matches[24]/", $_tblName, $q);
		}
		return $q;
	}

	/**
	 * Execute the queries from a given SQL file to install all tables for this application
	 * \param[in] $sqlFile Full path specification of the file containing SQL statements
	 * \param[in] $prefix Table prefix to add if it's not in the SQL file. Specify 'false' to disable adding the prefix
	 * \return Boolean indicating success (true) or any failure (false)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function installTables($sqlFile, $prefix = null)
	{
		if (($q = self::parseSQLFile($sqlFile)) === null) {
			trigger_error('Error reading SQL file ' . $sqlFile, E_USER_ERROR);
			return false;
		}
		if (count($q) == 0) {
			return true; // Noting to do
		}
		$db = OWL::factory('DbHandler');
		foreach ($q as $_qry) {
			if ($prefix !== false) {
				$_qry = self::addTablePrefix($_qry, $prefix);
			}
			OWLdbg_add(OWLDEBUG_OWL_LOOP, $_qry, 'SQL statement');
			$db->setQuery ($_qry);
			$db->write($_dummy, __LINE__, __FILE__);
		}
		return true;
	}

	/**
	 * Set the rights bitvalue for a given group
	 * \param[in] $aid Application ID.
	 * \param[in] $grp Group name. This can be an existing OWL group
	 * \param[in] $rights Array with right identifiers
	 * \return Boolean indicating success (true) or any failure (false)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function addGroupRights($aid, $grp, array $rights)
	{
		if (array_key_exists($grp . '__AID__' . $aid, self::$groups)) {
			$_grpID = self::$groups[$grp . '__AID__' . $aid];
		} else {
			if (array_key_exists($grp . '__AID__' . OWL_ID, self::$groups)) {
				$_grpID = self::$groups[$grp . '__AID__' . OWL_ID];
			} else {
				trigger_error('Group ' . $grp . ' has not been registered yet', E_USER_ERROR);
				return false;
			}
		}
		$_val = 0;
		foreach ($rights as $_r) {
			if (!array_key_exists($_r, self::$rights)) {
				trigger_error('Rightsbit ' . $_r . ' has not been registered yet', E_USER_ERROR);
				return false;
			}
			$_val += self::$rights[$_r];
		}
		$dataset = new DataHandler();
		if (ConfigHandler::get ('owltables', true)) {
				$dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->setTablename('grouprights');
		$dataset->set('aid', $aid);
		$dataset->set('gid', $_grpID);
		$dataset->set('right', $_val);
		$dataset->prepare(DATA_WRITE);
		$dataset->db($_dummy, __LINE__, __FILE__);
		return (true);
	}

	/**
	 * Add the application specific groups to the database
	 * \param[in] $aid Application ID
	 * \param[in] $grpname Array of groups
	 * \return Boolean indicating success (true) or any failure (false)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function addGroups($aid, $grpname)
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('owltables', true)) {
				$dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->setTablename('group');
		foreach ($grpname as $_grp) {
			$dataset->set('groupname', $_grp);
			$dataset->set('aid', $aid);
			$dataset->prepare(DATA_WRITE);
			$dataset->db($_dummy, __LINE__, __FILE__);
			$_id = $dataset->insertedId();
			self::$groups[$_grp . '__AID__' . $aid] = $_id;
		}
		return true;
	}

	/**
	 * Add the application specific rights to the database
	 * \param[in] $aid Application ID
	 * \param[in] $rights Array of rights in the format name => description
	 * \return Boolean indicating success (true) or any failure (false)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function addRights($aid, array $rights)
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('owltables', true)) {
				$dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->setTablename('rights');
		$dataset->set('aid', $aid);
		$dataset->set('rid', null, null
					,array('function' => array('max')
							,'name' => array('rid'))
					,array('match' => array(DBMATCH_NONE))
		);
		$dataset->prepare();
		$dataset->db($_rid, __LINE__, __FILE__);
		if (count($_rid) == 0) {
			$rid = 1;
		} else {
			$rid = $_rid[0]['rid'] + 1;
		}
		$dataset->reset(DATA_RESET_FULL);

		foreach ($rights as $_right => $_descr) {
			$dataset->set('rid', $rid);
			$dataset->set('name', $_right);
			$dataset->set('aid', $aid);
			$dataset->set('description', $_descr);
			$dataset->prepare(DATA_WRITE);
			$dataset->db($_dummy, __LINE__, __FILE__);
			self::$rights[$_right] = pow(2, $rid-1);
			$rid++;
		}
		return true;
	}

	/**
	 * Add an application specific dynamic config item. It is set or overwritten in the current
	 * configuration immediately.
	 * \param[in] $aid Application ID
	 * \param[in] $item Name of the configuration item in the same format it appears in the configuration file (e.g. 'group|subject|item)
	 * \param[in] $value Value of the configuration item
	 * \param[in] $protect True if this is a protected item
	 * \param[in] $hide True if this is an hidden item
	 * \param[in] $group An optional groupname to which this item belongs
	 * \return Boolean indicating success (true) or any failure (false)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function addConfig ($aid, $item, $value, $protect = false, $hide = false, $group = null)
	{
		if ($group === null) {
			$_grpID = 0;
		} else {
			if (array_key_exists($grp . '__AID__' . $aid, self::$groups)) {
				$_grpID = self::$groups[$grp . '__AID__' . $aid];
			} else {
				trigger_error('Group ' . $grp . ' has not been registered yet', E_USER_ERROR);
				return false;
			}
		}
		$dataset = new DataHandler();
		if (ConfigHandler::get ('owltables', true)) {
				$dataset->setPrefix(ConfigHandler::get ('owlprefix'));
		}
		$dataset->setTablename('config');
		$dataset->set('aid', $aid);
		$dataset->set('gid', $_grpID);
		$dataset->set('uid', 0);
		$dataset->set('name', $item);
		$dataset->set('value', $value);
		$dataset->set('protect', ($protect === true)?1:0);
		$dataset->set('hide', ($hide === true)?1:0);
		$dataset->prepare(DATA_WRITE);
		$dataset->db($_dummy, __LINE__, __FILE__);
		ConfigHandler::set($item, $value);
		return (true);
	}
}

//! OWL_ROOT must be defined by the application
if (!defined('OWL_ROOT')) { trigger_error('OWL_ROOT must be defined by the application', E_USER_ERROR); }

// Make sure the loader does not attempt to load the application
define('OWL___INSTALLER', 1);
require (OWL_ROOT . '/OWLloader.php');

OWLinstaller::construct();