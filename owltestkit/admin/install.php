<?php
/**
 * \file
 * This is installer for the OWL Testkit
 * \ingroup OTK_ADMIN
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: install.php,v 1.2 2011-09-26 10:50:19 oscar Exp $
 */

// Toplevel where OWL can be found
define ('OWL_ROOT', '/var/www/owl-php/src');

require (OWL_ROOT . '/OWLinstaller.php');

$_id = OWLinstaller::installApplication('OTK', 'OWLTestKit', 'v0.1', 'Testapplication for OWL-PHP', 'http://localhost', 'Oscar van Eijk');

OWLinstaller::addConfig($_id, 'general', 'debug', 16711935);
OWLinstaller::addConfig($_id, 'database', 'prefix', 'otk_', true);
OWLinstaller::addConfig($_id, 'general', 'js_signal', true);

OWLinstaller::addRights($_id
	,array(
		 'createtestset'=> 'Can create new testsets'
		,'createtestcase'=> 'Can create new testcases in an existing testset'
		,'executetest'=> 'Can execute tests'
	)
);

OWLinstaller::addGroups($_id
	,array(
		 'OTK admin' => 'OWL Testkit Administrators'
		,'OTK Developers' => 'OWL developers'
		,'OTK Testers' => 'OWL testers'
	)
);
OWLinstaller::addGroupRights($_id
	,'OTK admin'
	,array(
		 'createtestset'
		,'createtestcase'
		,'executetest'
	)
);
OWLinstaller::addGroupRights($_id
	,'OTK Developers'
	,array(
		 'createtestcase'
		,'executetest'
	)
);
OWLinstaller::addGroupRights($_id
	,'OTK Testers'
	,array(
		 'executetest'
	)
);

OWLinstaller::enableApplication($_id);

OWLloader::getClass('OWLrundown.php', OWL_ROOT);
