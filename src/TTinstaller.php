<?php
/**
 * \file
 * \ingroup TT_SO_LAYER
 * This file defines the class to install applications
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

/**
 * \ingroup TT_SO_LAYER
 * Abstract class to install applications
 * \brief Application installer
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Apr 19, 2011 -- O van Eijk -- Initial version for OWL-PHP
 */
abstract class TTinstaller
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
	 * Preload TT data that can be used during the application install. Skip this when we're installing TT itself
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public static function construct()
	{
		if (defined('TT__BASE__INSTALL')) {
			return;
		}
		$dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}
		$dataset->setTablename('group');
		$dataset->set('aid', TT_ID);
		$dataset->setKey('aid');
		$dataset->prepare();
		$dataset->db($_data, __LINE__, __FILE__);
		foreach ($_data as $_grp) {
			self::$groups[$_grp['groupname'] . '__AID__' . TT_ID] = $_grp['gid'];
		}
	}

	/**
	 * Register an application in the database
	 * \param[in] $code Application code
	 * \param[in] $url URL (relative from the document root) where the application will be installed. When empty or null, defaults to the lowercase application name
	 * \param[in] $name Name of the application
	 * \param[in] $version Version number of the application
	 * \param[in] $released Optional release date of the application
	 * \param[in] $description Optional description
	 * \param[in] $link Optional link to the applications homepage
	 * \param[in] $author Optional name of the copyright holder
	 * \param[in] $license Optional license
	 * \return The application ID
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \todo Error checking
	 */
	private static function installApplication ($code, $url, $name, $version, $released = null, $description = '', $link = '', $author = '', $license = '')
	{
		OutputHandler::output('Create application ' . $name . ' (' . $code . ')', TT_OUTPUT_NOW);
		$dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}
		if (!$url) {
			$url = strtolower($name);
		}
		$dataset->setTablename('applications');
		$dataset->set('code', $code);
		$dataset->set('url', $url);
		$dataset->set('name', $name);
		$dataset->set('version', $version);
		$dataset->set('released', $released);
		$dataset->set('description', $description);
		$dataset->set('link', $link);
		$dataset->set('author',$author);
		$dataset->set('license', $license);
		$dataset->set('enabled', '0');
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
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}
		$dataset->setTablename('applications');
		$dataset->set('aid', $id);
		$dataset->set('enabled', 1);
		$dataset->setKey('aid');
		$dataset->prepare(DATA_UPDATE);
		$dataset->db($_dummy, __LINE__, __FILE__);
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
			$_line = uncommentLine($_line, TT_COMMENT_SQL);
			if (preg_match('/;\s*$/', $_line)) {
				$statement .= (' ' . $_line);
				TTdbg_add(TTDEBUG_TT_S04, $statement, 'SQL statement');
				$queries[] = self::setTablePrefix($statement);
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
	 * This method adds the tableprefix to all tablenames in an SQL statement as read from the SQL file
	 * \param $statement Complete SQL statements
	 * \return Same SQL statement with tables prefix added
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function setTablePrefix ($statement)
	{
		$_tablePrefix = ConfigHandler::get ('database', 'ttprefix');
		$_checkList = array(
			 '/CREATE\s+(\w+\s+)?(INDEX)\s+(`?\w+`?)\s+([\s\w]+?)?\s*ON\s+(`)?(\w+)(`)?\s+/i' => "CREATE \${1} \${2} \${3} \${4} ON \${5}$_tablePrefix\${6}\${7} "
			,'/DROP\s+TABLE\s+(IF\s+EXISTS\s+)?(`)?(\w+)(`)?/i' => "DROP TABLE \${1} \${2}$_tablePrefix\${3}\${4} "
			,'/CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?(`)?(\w+)(`)?/i' => "CREATE TABLE \${1} \${2}$_tablePrefix\${3}\${4} "
			,'/REFERENCES\s+(`?)(\w+)(`?)\s+/i' => "REFERENCES \${1}$_tablePrefix\${2}\${3} "
			,'/INSERT\s+INTO\s+(`?)(\w+)(`?)\s+/i' => "INSERT INTO \${1}$_tablePrefix\${2}\${3} "
			,'/FROM\s+(`?)(\w+)(`?)\s+/i' => "FROM \${1}$_tablePrefix\${2}\${3} "
		);
		
		foreach ($_checkList as $_pattern => $_replacement) {
			if (preg_match($_pattern, $statement)) {
				$statement = preg_replace($_pattern, $_replacement, $statement);
			}
		}
		return $statement;
	}
	
	/**
	 * Check an SQL statement and look for table names. When a tablename has been found, the
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
	 * \todo Add support for other databases (e.g. Oracle, using quotes iso backticks)
	 * \todo Handle constraints ([...] REFERENCES `tblname` (...))
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 * \deprecated setTablePrefix() is used now
	 */
	private static function addTablePrefix ($q, $prefix)
	{
		// Abuse a datahandler to get the proper db handler
		$_d = new DataHandler();
		if ($prefix !== null) {
			$_d->setPrefix($prefix);
		} else {
			$_d->setPrefix(ConfigHandler::get('database', 'prefix', null, true));
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
		$db = TT::factory('DbHandler');
		foreach ($q as $_qry) {
			// Fix for the MySQL Workbench bug #63956 (http://bugs.mysql.com/bug.php?id=63956)
			if (preg_match('/^\s*CREATE\s*(.*?)INDEX\s*.?fk\_/i', $_qry)) {
				$_qry = preg_replace('/fk_/', 'fk', $_qry);
			}
			
//			if ($prefix !== false) {
//				$_qry = self::addTablePrefix($_qry, $prefix);
//			}
			TTdbg_add(TTDEBUG_TT_LOOP, $_qry, 'SQL statement');
			

			$_logPtrn = '/((create|drop|alter)\s+((temporary|ignore)\s+)?(table)(\s*if\s*(not\s*)?(exists))?)\s+(`)?(\w+)(`)/i';
			if (preg_match($_logPtrn, $_qry, $_m)) {
				OutputHandler::output(ucfirst(strtolower($_m[2])) . ' table ' . $_m[10], TT_OUTPUT_NOW);
			}
				
			$db->setQuery ($_qry);
			$db->write($_dummy, __LINE__, __FILE__);
		}
		return true;
	}

	/**
	 * Set the rights bitvalue for a given group
	 * \param[in] $aid Application ID.
	 * \param[in] $grp Group name. This can be an existing TT group
	 * \param[in] $rights Array with right identifiers
	 * \return Boolean indicating success (true) or any failure (false)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function addGroupRights($aid, $grp, array $rights)
	{
		if (array_key_exists($grp . '__AID__' . $aid, self::$groups)) {
			$_grpID = self::$groups[$grp . '__AID__' . $aid];
		} else {
			if (array_key_exists($grp . '__AID__' . TT_ID, self::$groups)) {
				$_grpID = self::$groups[$grp . '__AID__' . TT_ID];
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
			OutputHandler::output('Adding right ' . $_r . ' to group ' . $grp, TT_OUTPUT_NOW);
			$_val += self::$rights[$_r];
		}
		$dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
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
	 * \param[in] $grps Array of groups in the format (groupname => groupdescription)
	 * \return Boolean indicating success (true) or any failure (false)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function addGroups($aid, array $grps)
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}
		$dataset->setTablename('group');
		foreach ($grps as $_grp => $_desc) {
			OutputHandler::output('Add group ' . $_grp, TT_OUTPUT_NOW);
			$dataset->set('groupname', $_grp);
			$dataset->set('description', $_desc);
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
	private static function addRights($aid, array $rights)
	{
		$dataset = new DataHandler();
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
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
			OutputHandler::output('Create rightsbit ' . $_right, TT_OUTPUT_NOW);
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
	 * \param[in] $section Name of the configuration section
	 * \param[in] $item Name of the configuration item
	 * \param[in] $value Value of the configuration item
	 * \param[in] $protect True if this is a protected item
	 * \param[in] $hide True if this is an hidden item
	 * \param[in] $group An optional groupname to which this item belongs
	 * \return Boolean indicating success (true) or any failure (false)
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function addConfig ($aid, $section, $item, $value, $protect = false, $hide = false, $group = null)
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
		if (ConfigHandler::get ('database', 'tttables', true)) {
			$dataset->setPrefix(ConfigHandler::get ('database', 'ttprefix'));
		}

		OutputHandler::output('Set configuration item [' . $section . ']->' . $item . ' with value ' . $value, TT_OUTPUT_NOW);
		$_secId = ConfigHandler::configSection($section, true);
		$dataset->setTablename('config');
		$dataset->set('aid', $aid);
		$dataset->set('gid', $_grpID);
		$dataset->set('uid', 0);
		$dataset->set('sid', $_secId);
		$dataset->set('name', $item);
		$dataset->set('value', $value);
		$dataset->set('protect', ($protect === true)?1:0);
		$dataset->set('hide', ($hide === true)?1:0);
		$dataset->prepare(DATA_WRITE);
		$dataset->db($_dummy, __LINE__, __FILE__);
		ConfigHandler::set($section, $item, $value, true);
		return (true);
	}

	/**
	 *Add a user for this application
	 * \param[in] $aid Application ID
	 * \param[in] $username Given username
	 * \param[in] $password Given password
	 * \param[in] $email Given username
	 * \param[in] $group Name of the primary group
	 * \param[in] $memberships Array with groupnames for additional memberships
	 * \return True on success
	 * \note The default group and the additional memberships must be part of the application being installed
	 * If no primary group is given, the new user will be member of the default group from the TT configuration
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private static function addUser($aid, $username, $password, $email, $group, $memberships = null)
	{
		$grpObj = new Group();
		$group = $grpObj->getGroupByName($group, $aid);
		if (TTInstallerUser::getReference()->registerUser($aid, $username, $password, $email, $group, $memberships) < 0) {
			return false;
		}
		OutputHandler::output('User ' . $username . ' added in group ' . $group, TT_OUTPUT_NOW);
		return true;
	}
	
	/**
	 * Method to install an application using an XML file
	 * \param[in] unknown $xmlFile
	 * \return New application ID or false on failures
	 * \todo Improved error handling, user input, configuration settings, layout, generate .htaccess for the ttadmin directory, logging to browser and a hell of a lot more....
	 */
	public static function installXMLFile($xmlFile)
	{
		TTloader::getClass('xmlhandler', TT_BO_INC);
		
		$xmlInstaller = new XmlHandler($xmlFile);
		$xmlInstaller->parse();

		if ($xmlInstaller->childExists('sqlfiles')) {
			// Load the SQL installation script(s)
			for ($_cnt = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("sqlfiles/script,$_cnt")) !== null; $_cnt++) {
				$_xmlData = $xmlInstaller->getNodeData($_xmlObj);
				self::installTables(TT_ROOT . $_xmlData['name'], false);
			}
		}

		// Get the application rootnode
		if (($_xmlObj = $xmlInstaller->getNodeByPath()) === null) {
			trigger_error('Error fetching the \'application\' rootnode', E_USER_ERROR);
			return false;
		}

		// Check for presence of all required attributes
		$_xmlData = $xmlInstaller->getNodeData($_xmlObj);
		if (!array_key_exists('code', $_xmlData)) {
			trigger_error('application code not set', E_USER_ERROR);
			return false;
		}
		if (!array_key_exists('url', $_xmlData)) {
			trigger_error('application url not set', E_USER_ERROR);
			return false;
		}
		if (!array_key_exists('name', $_xmlData)) {
			trigger_error('application name not set', E_USER_ERROR);
			return false;
		}
		if (!array_key_exists('version', $_xmlData)) {
			trigger_error('application version not set', E_USER_ERROR);
			return false;
		}

		// Load the application
		$_applicationID = self::installApplication(
				 $_xmlData['code']
				,$_xmlData['url']
				,$_xmlData['name']
				,$_xmlData['version']
				,array_key_exists('released',$_xmlData) ? $_xmlData['released'] : null
				,array_key_exists('description',$_xmlData) ? $_xmlData['description'] : ''
				,array_key_exists('link',$_xmlData) ? $_xmlData['link'] : ''
				,array_key_exists('author',$_xmlData) ? $_xmlData['author'] : ''
				,array_key_exists('license',$_xmlData) ? $_xmlData['license'] : ''
		);
		
		if ($xmlInstaller->childExists('configuration')) {
			// Load the configuration
			for ($_cntC = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("configuration,$_cntC")) !== null; $_cntC++) {
				// configuration itself has no data, continue with the sections
				for ($_cntS = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("configuration,$_cntC/items,$_cntS")) !== null; $_cntS++) {
					// configuration itself has no data, continue with the sections
					$_xmlData = $xmlInstaller->getNodeData($_xmlObj);
					if (!array_key_exists('section', $_xmlData)) {
						trigger_error("No section name in path 'configuration,$_cntC/items,$_cntS'", E_USER_ERROR);
						return false;
					}
					$_sectionName = $_xmlData['section'];

					// Get all items for this section
					for ($_cntI = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("configuration,$_cntC/items,$_cntS/item,$_cntI")) !== null; $_cntI++) {
						$_xmlData = $xmlInstaller->getNodeData($_xmlObj);
						if (!array_key_exists('id', $_xmlData)) {
							trigger_error("No item id in path 'configuration,$_cntC/items,$_cntS'/item,$_cntI", E_USER_ERROR);
							return false;
						}
						// Store this configuration item
						self::addConfig($_applicationID, $_sectionName, $_xmlData[ 'id'], $_xmlData['_TTnodeText']);
					}
				}
			}
		}

		if ($xmlInstaller->childExists('rights')) {
			// Get all application rights
			$_rights = array();
			for ($_cntR = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("rights,$_cntR")) !== null; $_cntR++) {
				// rights element itself has no data
				for ($_cntI = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("rights,$_cntR/right,$_cntI")) !== null; $_cntI++) {
					$_xmlData = $xmlInstaller->getNodeData($_xmlObj);
					if (!array_key_exists('id', $_xmlData)) {
						trigger_error("No rights id in path 'rights,$_cntR/right,$_cntI'", E_USER_ERROR);
						return false;
					}
					$_rights[$_xmlData['id']] = $_xmlData['_TTnodeText'];
				}
			}
			// Store the application rights
			self::addRights($_applicationID, $_rights);
		}

		if ($xmlInstaller->childExists('groups')) {
			// Create groups
			$_groups = array();
			$_groupRights = array();
			for ($_cntG = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("groups,$_cntG")) !== null; $_cntG++) {
				// group element itself has no data
				for ($_cntN = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("groups,$_cntG/group,$_cntN")) !== null; $_cntN++) {
					// Fetch group data
					$_xmlData = $xmlInstaller->getNodeData($_xmlObj);
					if (!array_key_exists('name', $_xmlData)) {
						trigger_error("No groupname in path 'groups,$_cntG/group,$_cntN'", E_USER_ERROR);
						return false;
					}
					$_groupName = $_xmlData['name'];
					$_groups[$_groupName] = (array_key_exists('description', $_xmlData) ? $_xmlData[ 'description'] : '');
			
					// Continue with the grouprights
					$_grpRights = array();
					for ($_cntI = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("groups,$_cntG/group,$_cntN/right,$_cntI")) !== null; $_cntI++) {
						$_xmlData = $xmlInstaller->getNodeData($_xmlObj);
						// TODO check if the right exists, might be from this XML or an already existing right ID
						$_grpRights[] = $_xmlData['_TTnodeText'];
					}
					// Save the grouprights
					$_groupRights[$_groupName] = $_grpRights;
				}
				self::addGroups($_applicationID, $_groups);
			}
			// Now store the grouprights
			foreach ($_groupRights as $_groupName => $_grpRights) {
				self::addGroupRights($_applicationID, $_groupName, $_grpRights);
			}
		}

		if ($xmlInstaller->childExists('users')) {
			// Store the users
			for ($_cntU = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("users,$_cntU")) !== null; $_cntU++) {
				// users element itself has no data
				for ($_cntN = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("users,$_cntU/user,$_cntN")) !== null; $_cntN++) {
					// Fetch user data
					$_xmlData = $xmlInstaller->getNodeData($_xmlObj);
					if (!array_key_exists('name', $_xmlData)) {
						trigger_error("No username in path 'users,$_cntU/user,$_cntN'", E_USER_ERROR);
						return false;
					}
					if (!array_key_exists('group', $_xmlData)) {
						trigger_error("No primary group in path 'groups,$_cntU/user,$_cntN'", E_USER_ERROR);
						return false;
					}
					$_userName = $_xmlData['name'];
					$_userGroup = $_xmlData['group'];
					$_userPassword = (array_key_exists('password', $_xmlData) ? $_xmlData[ 'password'] : '');
					$_userEmail = (array_key_exists('email', $_xmlData) ? $_xmlData[ 'email'] : '');
			
					// Continue with the memberships
					$_memberShips = array();
					for ($_cntM = 0; ($_xmlObj = $xmlInstaller->getNodeByPath("users,$_cntU/user,$_cntN/membership,$_cntM")) !== null; $_cntM++) {
						$_xmlData = $xmlInstaller->getNodeData($_xmlObj);
						// TODO check if the group exists, might be from this XML or an already existing right ID when this installer is generalised
						$_memberShips[] = $_xmlData['_TTnodeText'];
					}
					// Store the users
					self::addUser(
						 $_applicationID
						,$_userName
						,$_userPassword
						,$_userEmail
						,$_userGroup
						,$_memberShips
					);
				}
			}
		}

		return $_applicationID;
	}
}

// TT_ROOT must be defined by the application
if (!defined('TT_ROOT')) {
	trigger_error('TT_ROOT must be defined by the application', E_USER_ERROR);
}

// Make sure the loader does not attempt to load the application
define('TT___INSTALLER', 1);
require (TT_ROOT . '/TTloader.php');

/**
 * Helper class to add users during the installation process
 * \brief TTInstallerUser User
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Nov 22, 2011 -- O van Eijk -- initial version
 */
class TTInstallerUser extends User
{
	/**
	 * Self reference
	 */
	private static $instance;

	/**
	 * Object constructor
	 */
	private function __construct()
	{
		parent::construct();
		TTInstallerUser::$instance = $this;
	}
	/**
	 * Instantiate the singleton or return its reference
	 */
	static public function getReference()
	{
		if (!TTInstallerUser::$instance instanceof TTInstallerUser) {
			TTInstallerUser::$instance = new self();
		}
		return TTInstallerUser::$instance;
	}


	/**
	 * Register a new username
	 * \param[in] $aid Application ID
	 * \param[in] $username Given username
	 * \param[in] $password Given password
	 * \param[in] $email Given username
	 * \param[in] $group Primary Group
	 * \param[in] $memberships Array with additional memberships
	 * \return New user ID or -1 on failure
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function registerUser($aid, $username, $password, $email, $group, $memberships)
	{
		if (($_uid = parent::register($username, $email, $password, $password, $group, false)) < 0) {
			return -1;
		}
		if ($memberships !== null) {
			foreach ($memberships as $_grp) {
				if (parent::addMembership($_grp, $aid, $_uid) === false) {
					; // Ignore failures here
				}
			}
		}
		return $_uid;
	}
}

TTinstaller::construct();
if (TTCache::get(TTCACHE_OBJECTS, 'user') === null) {
	TTInstallerUser::getReference(); // Force the user object to exist in cache
}
