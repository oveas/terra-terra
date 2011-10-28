<?php
/**
 * \file
 * This is installer for the OWL Testkit
 * \ingroup OTK_ADMIN
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: install.php,v 1.4 2011-10-28 09:32:48 oscar Exp $
 * \copyright{2011} Oscar van Eijk, Oveas Functionality Provider
 * \license
 * This file is part of OTK.
 *
 * OTK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * OTK is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OTK. If not, see http://www.gnu.org/licenses/.
 */

// Toplevel where OWL can be found
define ('OWL_ROOT', '/var/www/owl-php/src');

require (OWL_ROOT . '/OWLinstaller.php');

$_id = OWLinstaller::installApplication('OTK', 'OWLTestKit', 'v0.1', 'Testapplication for OWL-PHP', 'http://localhost', 'Oscar van Eijk', 'LGPL');

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
