<?php
/**
 * \file
 * This file loads the Terra-Terra Admin application
 * \ingroup TT_TTADMIN
 * \author Oscar van Eijk, Oveas Functionality Provider
 */

define ('TTADMIN_SO', TTloader::getCurrentAppUrl() . '/so');
define ('TTADMIN_BO', TTloader::getCurrentAppUrl() . '/bo');
define ('TTADMIN_UI', TTloader::getCurrentAppUrl() . '/ui');

if (!TTloader::getClass('ttuser', TTADMIN_BO)) {
	trigger_error('Error loading classfile TTUser from ' . TTADMIN_BO, E_USER_ERROR);
}

TTUser::getReference();

