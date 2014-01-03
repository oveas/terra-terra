<?php
/**
 * \file
 * Installer script for Terra-Terra
 * \todo This is just a first setup of what an Terra-Terra installer should do. What we need is some proper error checking,
 * configuration settings, layout, generate .htaccess for the ttadmin directory, logging to browser and a hell of a lot more....
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

define ('TT__BASE__INSTALL', true);
error_reporting(E_ALL | E_STRICT);
define ('TT_ROOT', '/var/www/terra-terra');
require (TT_ROOT . '/TTinstaller.php');

TTinstaller::installTables(TT_ROOT . '/tt.tables.sql', false);

$_id = TTinstaller::installApplication('TT', 'ttadmin', 'Terra-Terra', TT_VERSION, 'Oveas Web Library for PHP', 'https://github.com/oveas/terra-terra', 'Oscar van Eijk', 'LGPL');

TTinstaller::addConfig($_id, 'locale', 'date', 'd-M-Y');
TTinstaller::addConfig($_id, 'locale', 'time', 'H:i');
TTinstaller::addConfig($_id, 'locale', 'datetime', 'd-M-Y H:i:s');
TTinstaller::addConfig($_id, 'locale', 'log_date', 'd-m-Y');
TTinstaller::addConfig($_id, 'locale', 'log_time', 'H:i:s.u');
TTinstaller::addConfig($_id, 'locale', 'lang', 'en-UK');
//TTinstaller::addConfig($_id, 'locale', 'timezone', 'Europe/Brussels'); //!< \todo Add timezone as a required config item
TTinstaller::addConfig($_id, 'session', 'lifetime', '1440');
TTinstaller::addConfig($_id, 'session', 'pwd_minstrength', '2');
TTinstaller::addConfig($_id, 'session', 'check_ip', 'true');
TTinstaller::addConfig($_id, 'session', 'default_user', 'anonymous', true);
TTinstaller::addConfig($_id, 'logging', 'log_form_data', 'true');
TTinstaller::addConfig($_id, 'user', 'default_group', 'standard');
TTinstaller::addConfig($_id, 'session', 'default_rights_all', '1', true);
TTinstaller::addConfig($_id, 'mailsend', 'driver', 'RawSMTP');

TTinstaller::addRights($_id
	,array(
		 'readpublic'		=> 'Allowed to see all content that has been either unmarked, or marked as public'
		,'readanonymous'	=> 'Allowed to see anonymous only content'
		,'readregistered'	=> 'Allowed to see all content that has been marked for registered users'
		,'modpassword'		=> 'Allowed to change own password'
		,'modemail'			=> 'Allowed to change own email address'
		,'modusername'		=> 'Allowed to change own username'
		,'moduserconfig'	=> 'Allowed to change own configuration settings'
		,'modgroupconfig'	=> 'Allowed to change configuration settings of the primary group'
		,'modapplconfig'	=> 'Allowed to change application config settings for TT'
		,'addmembers'		=> 'Allowed to add members to the primary group'
		,'managegroupusers'	=> 'Allowed to manage users in the primary group'
		,'managegroups'		=> 'Allowed to manage all groups in TT'
		,'manageusers'		=> 'Allowed to manage all users in TT'
		,'installapps'		=> 'Allowed to install new applications'
		,'ttdeveloper'		=> 'Allowed to use the TT Developer tools'
		,'showconsole'		=> 'Allowed to see the console'
		,'showtrace'		=> 'Allowed to see tracebacks in messages'
	)
);

TTinstaller::addGroups($_id
	,array(
		 'nogroup'		=> 'Default group for anonymous users'
		,'standard'		=> 'Default group for all registered users'
		,'developer'	=> 'Group for TT developers'
		,'groupadmin'	=> 'Group administrators for the primary group'
		,'superadmin'	=> 'TT super administrators'
	)
);

TTinstaller::addGroupRights($_id
	,'nogroup'
	,array(
		 'readpublic'
		,'readanonymous'
	)
);

TTinstaller::addGroupRights($_id
	,'standard'
	,array(
		 'readpublic'
		,'readregistered'
		,'modpassword'
		,'modemail'
		,'moduserconfig'
	)
);

TTinstaller::addGroupRights($_id
		,'developer'
		,array(
			 'ttdeveloper'
			,'showconsole'
			,'showtraces'
		)
);

TTinstaller::addGroupRights($_id
	,'groupadmin'
	,array(
		 'addmembers'
		,'managegroupusers'
		,'modgroupconfig'
	)
);

TTinstaller::addGroupRights($_id
	,'superadmin'
	,array(
		 'installapps'
		,'manageusers'
		,'managegroups'
		,'modapplconfig'
		,'showconsole'
		,'showtraces'
	)
);

TTinstaller::addUser($_id, 'anonymous', '', '', 'nogroup');
TTinstaller::addUser($_id, 'tt', 'ttuser', 'tt@localhost.default', 'standard', array('developer'));
TTinstaller::addUser($_id, 'root', 'ttadmin', 'root@localhost.default', 'superadmin', array('groupadmin', 'standard'));

TTinstaller::enableApplication($_id);
TTloader::getClass('TTrundown.php', TT_ROOT);
