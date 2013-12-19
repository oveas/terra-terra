<?php
/**
 * \file
 * This file loads the OWL-PHP Admin application
 * \ingroup OWL_OWLADMIN
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

define ('OWLADMIN_SO', OWLloader::getCurrentAppUrl() . '/so');
define ('OWLADMIN_BO', OWLloader::getCurrentAppUrl() . '/bo');
define ('OWLADMIN_UI', OWLloader::getCurrentAppUrl() . '/ui');

if (!OWLloader::getClass('owluser', OWLADMIN_BO)) {
	trigger_error('Error loading classfile OWLUser from ' . OWLADMIN_BO, E_USER_ERROR);
}

OWLUser::getReference();

