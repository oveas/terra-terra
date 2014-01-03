<?php
/**
 * \file
 * \ingroup TT_LIBRARY
 * This file defines the Socket handler class
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

/**
 * \name Socket codes
 * Definition of standard socket commands and returncodes
 * @{
 */
//! Response code indicating the socket is available
define ('SOCK_AVAIL',			'220');

//! Response code indicating the socket accepted the last command
define ('SOCK_ACCEPTED',		'250');

//! Response code indicating the socket is ready to receive data
define ('SOCK_DATA_STARTED',	'354');

//! Code to end a line
define ('SOCK_LINE_END',		"\r\n");

//! Command to start sending data
define ('SOCK_DATA_START',		"data\r\n");

//! Commend to end datasend
define ('SOCK_DATA_END',		"\r\n.\r\n");

//! Command to end a session
define ('SOCK_SESSION_END',		"quit\r\n");

//! Size of a netwwork buffer
define ('SOCK_BUFFER_SIZE',		2048);

//! Connect timeout in seconds
define ('SOCK_TIMEOUT',			30);
//! @}

/**
 * \ingroup TT_SO_LAYER
 * Class to support socket programming
 * \brief Socket handler
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version Aug 16, 2003 -- O van Eijk -- Initial version for OWL-PHP
 * \version May 11, 2011 -- O van Eijk -- Ported to Terra-Terra
 */
class SocketHandler extends _TT
{
	/**
	 * Socket ID
	 */
	private $id;

	/**
	 * Name of the remote host to connect to
	 */
	private $host;

	/**
	 * Portnumber on the host to connect with
	 */
	private $port;

	/**
	 * Class constructor, initializes the socket
	 * \param[in] $port Portnumber or service name
	 * \param[in] $host Hostname, defaults to localhost
	 * \param[in] $udp True when an UDP connection should be opened i.s.o. TCP. Only used in combination
	 * with a service name for $port.
	 * \return Severity
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function __construct ($port, $host = 'localhost', $udp = false)
	{
		_TT::init(__FILE__, __LINE__);

		$this->id = 0;
		$this->host = $host;
		$service_port = getservbyname('www', 'tcp');
		if (is_int($port)) {
			$this->port = $port;
			$this->setStatus (__FILE__, __LINE__, TT_STATUS_OK);
		} else {
			if (($this->port = getservbyname($port, ($udp === true ? 'udp' : 'tcp'))) === false) {
				$this->setStatus (__FILE__, __LINE__, SOCKET_NOPORT, array($port, ($udp === true ? 'udp' : 'tcp')));
			} else {
				$this->setStatus (__FILE__, __LINE__, TT_STATUS_OK);
			}
		}
		return ($this->severity);
	}

	/**
	 * Check if the current socket is connected
	 * \return True is the connection is established, false otherwise
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	private function connected ()
	{
		return ($this->id !== 0);
	}

	/**
	 * Open the new socket
	 * \return Severity
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function connect ()
	{
		if ($this->connected() === true) {
			$this->setStatus (__FILE__, __LINE__, SOCKET_CONNECTED);
		} else {
			if (($this->id = fsockopen ($this->host,
							$this->port,
							$_errno,
							$_errmsg,
							SOCK_TIMEOUT)) === false) {
				$this->setStatus (__FILE__, __LINE__, SOCKET_CONNERROR, array($_errno, $_errmsg, $this->host, $this->port));
			} else {
				if (($response = $this->read()) === null) {
					return ($this->severity);
				}
				if (strncmp($response, SOCK_AVAIL, strlen(SOCK_AVAIL)) !== 0) {
					$this->setStatus (__FILE__, __LINE__, SOCKET_CONNERROR, array(0, rtrim($response), $this->host, $this->port));
				} else {
					$this->setStatus (__FILE__, __LINE__, SOCKET_CONNECTOK, array($this->host, $this->port));
				}
				return ($this->severity);
			}
		}
		return ($this->severity);
	}

	/**
	 * Close the socket
	 * \return Severity
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function disconnect ()
	{
		if ($this->connected() === false) {
			$this->setStatus (__FILE__, __LINE__, SOCKET_NOTCONNECTED);
		} else {
			fclose ($this->id);
			$this->id = 0;
		}
		return ($this->severity);
	}

	/**
	 * Write a line to the socket
	 * \param[in] $line
	 * \param[in] $expect An optional response to wait for and validate
	 * \return Severity
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function write ($line, $expect = '')
	{
		if (!$this->connected()) {
			$this->setStatus(__FILE__, __LINE__, SOCKET_NOTCONNECTED);
			return ($this->severity);
		}
		if (fwrite ($this->id, $line . SOCK_LINE_END) === false) {
			$this->setStatus(__FILE__, __LINE__, SOCKET_WRITEERROR);
			return ($this->severity);
		}

		if ($expect !== '') {
			if (($response = $this->read()) === null) {
				return ($this->severity);
			}
			if (strncmp($response, $expect, strlen($expect)) !== 0) {
				$this->setStatus(__FILE__, __LINE__, SOCKET_UNEXPECTED, array(rtrim($response), $expect));
			} else {
				$this->setStatus (__FILE__, __LINE__, SOCKET_EXPECTED, array(rtrim($response)));
			}
		} else {
			$this->setStatus (__FILE__, __LINE__, TT_STATUS_OK);
		}
		return ($this->severity);
	}

	/**
	 * Read a line from the socket
	 * \return The data read, or null when an error occured
	 * \author Oscar van Eijk, Oveas Functionality Provider
	 */
	public function read ()
	{
		if (!$this->connected()) {
			$this->setStatus(__FILE__, __LINE__, SOCKET_NOTCONNECTED);
			return (null);
		}
		if (($response = fgets ($this->id, SOCK_BUFFER_SIZE)) === false) {
			$this->setStatus(__FILE__, __LINE__, SOCKET_READERROR);
			return (null);
		}
		$this->setStatus(__FILE__, __LINE__, SOCKET_READ, array(rtrim($response)));
		return ($response);
	}
}

/*
 * Register this class and all status codes
 */
Register::registerClass ('SocketHandler');

//Register::setSeverity (TT_DEBUG);
//Register::setSeverity (TT_INFO);

Register::setSeverity (TT_OK);
Register::registerCode ('SOCKET_CONNECTED');
Register::registerCode ('SOCKET_NOTCONNECTED');

Register::setSeverity (TT_SUCCESS);
Register::registerCode ('SOCKET_CONNECTOK');
Register::registerCode ('SOCKET_EXPECTED');
Register::registerCode ('SOCKET_READ');

Register::setSeverity (TT_WARNING);
Register::registerCode ('SOCKET_CONNERROR');
Register::registerCode ('SOCKET_READERROR');
Register::registerCode ('SOCKET_UNEXPECTED');
Register::registerCode ('SOCKET_WRITEERROR');

//Register::setSeverity (TT_BUG);

Register::setSeverity (TT_ERROR);
Register::registerCode ('SOCKET_NOPORT');

//Register::setSeverity (TT_FATAL);
//Register::setSeverity (TT_CRITICAL);
