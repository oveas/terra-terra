<?php
/**
 * \file
 * This file defines the basic PHP maildriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \copyright{2007-2013} Oscar van Eijk, Oveas Functionality Provider
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

/**
 * \ingroup OWL_DRIVERS
 * Class that defines the PHP mail drivers
 * \brief Mail driver
 * \see class MailDriver
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Jul 12, 2013 -- O van Eijk -- initial version
 */
class PHPMail extends MailDefaults implements MailDriver
{

	public function __construct()
	{
	}

	/**
	 * Reimplement _OWL::getLastWarning() to get the last error message either of the socket object or my own
	 * \return null if there was no error (no severity OWL_WARNING or higher), otherwise the error text.
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function getLastWarning()
	{
		$this->mailSocket->signal(OWL_WARNING, $_err);
		if ($_err === false) {
			$this->signal(OWL_WARNING, $_err);
		}
		return (($_err === false) ? null : $_err);
	}

	/**
	 * Open a socket and write the raw mailtext to it.
	 * \param[in] $mail Indexed array defining the mail
	 * \return Boolean, true on success
	 * \see MailDriver::mailSend()
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function mailSend (array $mail)
	{
		$_to = '';
		if (array_key_exists('to', $mail) && count($mail['to']) > 0) {
			$_to = implode(',', $mail['to']);
		}
		$_subj = '';
		if (array_key_exists('subject', $mail)) {
			$_subj = $mail['subject'];
		}
		
		$_hdr  = 'From: ' . $mail['sender'] . "\r\n";

		if (array_key_exists('to', $mail) && count($mail['to']) > 0) {
			$_hdr .= 'To' . implode(',', $mail['to']) . "\r\n";
		}
		if (array_key_exists('cc', $mail) && count($mail['cc']) > 0) {
			$_hdr .= 'Cc' . implode(',', $mail['cc']) . "\r\n";
		}
		if (array_key_exists('bcc', $mail) && count($mail['bcc']) > 0) {
			$_hdr .= 'Bcc' . implode(',', $mail['bcc']) . "\r\n";
		}

		return mail($_to, $_subj, $mail['body'], $_hdr);
	}

}
